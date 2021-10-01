<?php

/**
 * BMInterfaceGameChat: interface between GUI and database for chat-log-related requests
 *
 * @author chaos
 */

/**
 * This class deals with communication between the UI and the database
 * pertaining to chat-log-related information.
 *
 * (N.B. At the moment, there is no BMGameChat module, so BMGame owns
 * the only game-level chat method, add_chat().)
 */

class BMInterfaceGameChat extends BMInterface {

    /**
     * Public API method to submit or update game chat
     *
     * @param int $playerId
     * @param int $gameId
     * @param int $editTimestamp
     * @param string $chat
     * @return bool
     */
    public function submit_chat(
        $playerId,
        $gameId,
        $editTimestamp,
        $chat
    ) {
        try {
            $game = $this->load_game($gameId);
            $currentPlayerIdx = array_search($playerId, $game->playerIdArray);

            foreach ($game->playerArray as $gamePlayer) {
                $playerNameArray[] = $this->get_player_name_from_id($gamePlayer->playerId);
            }
            $chatArray = $this->load_game_chat_log($playerId, $game, 1);
            $lastChatEntryList = $chatArray['chatEntries'];
            $logArray = $this->game_action()->load_game_action_log($game, 1);
            $lastActionEntryList = $logArray['logEntries'];

            if ($editTimestamp) {
                // player is trying to edit a given chat entry -
                // do this if it's valid
                $gameChatEditable = $this->find_editable_chat_timestamp(
                    $game,
                    $currentPlayerIdx,
                    $playerNameArray,
                    $lastChatEntryList,
                    $lastActionEntryList
                );
                if ($editTimestamp == $gameChatEditable) {
                    if (strlen($chat) > 0) {
                        $this->db_update_chat($playerId, $gameId, $editTimestamp, $chat);
                        $this->set_message('Updated previous game message');
                        return TRUE;
                    } else {
                        $this->db_delete_chat($playerId, $gameId, $editTimestamp);
                        $this->set_message('Deleted previous game message');
                        return TRUE;
                    }
                } else {
                    $this->set_message('You can\'t edit the requested chat message now');
                    return FALSE;
                }
            } else {
                // player is trying to insert a new chat entry -
                // do this if it's valid
                $gameChatInsertable = $this->chat_is_insertable(
                    $game,
                    $currentPlayerIdx,
                    $playerNameArray,
                    $lastChatEntryList,
                    $lastActionEntryList
                );
                if ($gameChatInsertable) {
                    if (strlen($chat) > 0) {
                        $this->db_insert_chat($playerId, $gameId, $chat);
                        $this->set_message('Added game message');
                        return TRUE;
                    } else {
                        $this->set_message('No game message added');
                        return TRUE;
                    }
                } else {
                    $this->set_message('You can\'t add a new chat message now');
                    return FALSE;
                }
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::submit_chat: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while updating game chat');
        }
    }

    /**
     * Public API method to update the visibility of game chat
     *
     * @param int $playerId
     * @param int $gameId
     * @param bool $private
     * @return bool
     */
    public function set_chat_visibility(
        $playerId,
        $gameId,
        $private
    ) {
        try {
            $game = $this->load_game($gameId);

            $currentPlayerIdx = array_search($playerId, $game->playerIdArray);
            if ($currentPlayerIdx < 0) {
                $this->set_message("You are not a participant in game " . $gameId . " and cannot modify chat settings");
                return FALSE;
            }

            // Apply the requested database update
            $this->db_set_chat_visibility($playerId, $gameId, $private);

            // Now return a message based on the new and opponent states
            $opponentIsChatPrivate = $game->playerArray[1 - $currentPlayerIdx]->isChatPrivate;
            if ($private) {
                $this->set_message("Set game chat to private");
                return TRUE;
            } else {
                if ($opponentIsChatPrivate) {
                    $this->set_message(
                        "Set game chat preference to public (but game chat is not visible to non-participants " .
                        "because your opponent is requesting private chat)"
                    );
                    return TRUE;
                } else {
                    $this->set_message("Set game chat to public");
                    return TRUE;
                }
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::set_chat_visibility: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while setting chat visibility');
        }
    }

    /**
     * Load previously-stored chat log entries from the database
     *
     * @param int      $playerId       ID of player loading the chat
     * @param BMGame   $game           Game for which chat is being loaded
     * @param int|NULL $logEntryLimit  Maximum number of chat entries to load
     * @return array
     */
    protected function load_game_chat_log($playerId, BMGame $game, $logEntryLimit) {
        try {
            $isParticipant = (array_search($playerId, $game->playerIdArray) !== FALSE);
            $doQueryPreviousGame = (
                $game->gameState < BMGameState::END_GAME &&
                !is_null($game->previousGameId) &&
                $isParticipant);

            $sqlParameters = array(':game_id' => $game->gameId);
            $query =
                'SELECT ' .
                    'UNIX_TIMESTAMP(chat_time) AS chat_timestamp, ' .
                    'chatting_player, ' .
                    'message ' .
                'FROM game_chat_log ';

            $query .= $this->build_game_log_query_restrictions($game, $doQueryPreviousGame, FALSE, $sqlParameters);
            $columnReturnTypes = array(
              'chat_timestamp' => 'int',
              'chatting_player' => 'int',
              'message' => 'str',
            );

            $rows = self::$db->select_rows($query, $sqlParameters, $columnReturnTypes);

            $chatEntries = array();

            // Notify the viewing player that chat is hidden, if necessary
            $canViewChat = $this->can_player_view_game_chat($isParticipant, $game);
            if (!$canViewChat) {
                $chatEntries[] = array(
                    'timestamp' => 0,
                    'player' => '',
                    'message' => "The chat for this game is private",
                );
            }

            foreach ($rows as $row) {
                // Even a player who can't view chat messages should be able to see non-chat entries
                // like continuation messages which are stored in the chat stream
                if ($canViewChat || $row['chatting_player'] == 0) {
                    $chatEntries[] = array(
                        'timestamp' => $row['chat_timestamp'],
                        'player' => $this->get_player_name_from_id($row['chatting_player']),
                        'message' => $row['message'],
                    );
                }
            }

            $nEntries = count($chatEntries);

            if (!is_null($logEntryLimit) &&
                ($nEntries > $logEntryLimit)) {
                $chatEntries = array_slice($chatEntries, 0, $logEntryLimit);
            }

            return array('chatEntries' => $chatEntries, 'nEntries' => $nEntries);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_game_chat_log: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading chat entries');
            return NULL;
        }
    }

    /**
     * Can the current player view the chat for this game?
     *
     * Game chat is visible if the player is a participant in the game
     * or if neither player has set the game chat to private.
     *
     * @param bool     $isParticipant  Is the player loading the chat a participant in the game
     * @param BMGame   $game           Game for which chat is being loaded
     * @return bool
     */
    protected function can_player_view_game_chat($isParticipant, BMGame $game) {
        if (!$isParticipant) {
            foreach ($game->playerArray as $player) {
                if ($player->isChatPrivate) {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    /**
     * Find the timestamp of the existing chat entry which the active player can
     * edit right now, if there is one
     *
     * @param BMGame $game
     * @param int $currentPlayerIdx
     * @param array $playerNameArray
     * @param array $chatLogEntries
     * @param array $actionLogEntries
     * @return mixed
     */
    public function find_editable_chat_timestamp(
        $game,
        $currentPlayerIdx,
        $playerNameArray,
        $chatLogEntries,
        $actionLogEntries
    ) {

        // Completed games can't be modified
        if ($game->gameState >= BMGameState::END_GAME) {
            return FALSE;
        }

        // If there are no chat entries, none can be modified
        if (count($chatLogEntries) == 0) {
            return FALSE;
        }

        // only a player in this game can modify the last chat message
        if (FALSE === $currentPlayerIdx) {
            return FALSE;
        }

        // only the player who chatted last can modify the last chat message
        if ($playerNameArray[$currentPlayerIdx] != $chatLogEntries[0]['player']) {
            return FALSE;
        }

        // only the player who was last active can modify the last chat message,
        // unless the game is in a state where the last activity in the game was
        // an automatic action
        if (('' != $actionLogEntries[0]['player']) &&
            ($playerNameArray[$currentPlayerIdx] != $actionLogEntries[0]['player'])) {
            return FALSE;
        }

        // save_game() saves action log entries before chat log
        // entries.  So, if there are action log entries, and the
        // chat log entry predates the most recent action log entry,
        // it is not current
        if ((count($actionLogEntries) > 0) &&
            ($chatLogEntries[0]['timestamp'] < $actionLogEntries[0]['timestamp'])) {
            return FALSE;
        }

        // The active player can edit the most recent chat entry:
        // return its timestamp so it can be identified later
        return $chatLogEntries[0]['timestamp'];
    }

    /**
     * Can the active player insert a new chat entry (without an attack) right now?
     *
     * @param BMGame $game
     * @param int $currentPlayerIdx
     * @param array $playerNameArray
     * @param array $chatLogEntries
     * @param array $actionLogEntries
     * @return bool
     */
    protected function chat_is_insertable(
        $game,
        $currentPlayerIdx,
        $playerNameArray,
        $chatLogEntries,
        $actionLogEntries
    ) {

        // Completed games can't be modified
        if ($game->gameState >= BMGameState::END_GAME) {
            return FALSE;
        }

        // If the player is not in the game, they can't insert chat
        if (FALSE === $currentPlayerIdx) {
            return FALSE;
        }

        // If the game is awaiting action from a player, that player
        // can't chat without taking an action
        if (TRUE === $game->playerArray[$currentPlayerIdx]->waitingOnAction) {
            return FALSE;
        }

        // If the most recent chat entry was made by the active
        // player, and is current, that player can't insert a new one
        if ((count($chatLogEntries) > 0) &&
            ($playerNameArray[$currentPlayerIdx] == $chatLogEntries[0]['player']) &&
            (count($actionLogEntries) > 0) &&
            ($chatLogEntries[0]['timestamp'] >= $actionLogEntries[0]['timestamp'])) {
            return FALSE;
        }

        // The active player can insert a new chat entry
        return TRUE;
    }

    /**
     * Save recently-added chat message as part of saving game state to the database
     *
     * @param BMGame $game
     * @return void
     */
    protected function save_chat_log($game) {
        if ($game->chat['chat']) {
            $this->db_insert_chat(
                $game->chat['playerIdx'],
                $game->gameId,
                $game->chat['chat']
            );
        }
    }

    /**
     * Insert a new chat message into the database
     *
     * @param int $playerId
     * @param int $gameId
     * @param string $chat
     * @return void
     */
    protected function db_insert_chat($playerId, $gameId, $chat) {

        $query = 'INSERT INTO game_chat_log ' .
                 '(game_id, chatting_player, message) ' .
                 'VALUES ' .
                 '(:game_id, :chatting_player, :message)';
        $parameters = array(':game_id'         => $gameId,
                            ':chatting_player' => $playerId,
                            ':message'         => $chat);
        self::$db->update($query, $parameters);
    }

    /**
     * Modify an existing chat message in the database
     *
     * @param int $playerId
     * @param int $gameId
     * @param int $editTimestamp
     * @param string $chat
     * @return void
     */
    protected function db_update_chat($playerId, $gameId, $editTimestamp, $chat) {
        $query = 'UPDATE game_chat_log ' .
                 'SET message = :message, chat_time = now() ' .
                 'WHERE game_id = :game_id ' .
                 'AND chatting_player = :player_id ' .
                 'AND UNIX_TIMESTAMP(chat_time) = :timestamp ' .
                 'ORDER BY id DESC ' .
                 'LIMIT 1';
        $parameters = array(':message' => $chat,
                            ':game_id' => $gameId,
                            ':player_id' => $playerId,
                            ':timestamp' => $editTimestamp);
        self::$db->update($query, $parameters);
    }

    /**
     * Delete an existing chat message from the database
     *
     * @param int $playerId
     * @param int $gameId
     * @param int $editTimestamp
     * @return void
     */
    protected function db_delete_chat($playerId, $gameId, $editTimestamp) {
        $query = 'DELETE FROM game_chat_log ' .
                 'WHERE game_id = :game_id ' .
                 'AND chatting_player = :player_id ' .
                 'AND UNIX_TIMESTAMP(chat_time) = :timestamp ' .
                 'ORDER BY id DESC ' .
                 'LIMIT 1';
        $parameters = array(':game_id' => $gameId,
                            ':player_id' => $playerId,
                            ':timestamp' => $editTimestamp);
        self::$db->update($query, $parameters);
    }

    /**
     * Set preferred visibility of chat for a player in a game
     *
     * @param int  $playerId
     * @param int  $gameId
     * @param bool $private
     */
    protected function db_set_chat_visibility($playerId, $gameId, $private) {
        $query = 'UPDATE game_player_map ' .
                 'SET is_chat_private = :is_chat_private ' .
                 'WHERE game_id = :game_id ' .
                 'AND player_id = :player_id ';
        $parameters = array(':game_id' => $gameId,
                            ':player_id' => $playerId,
                            ':is_chat_private' => $private);
        self::$db->update($query, $parameters);
    }
}
