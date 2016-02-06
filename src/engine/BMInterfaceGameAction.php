<?php

/**
 * BMInterfaceGameAction: interface between GUI and BMGame for game action-related requests
 *
 * @author james
 */

/**
 * This class deals with communication between the UI, the game code, and the database
 * pertaining to player-instigated game actions and chat
 */

class BMInterfaceGameAction extends BMInterface {
    public function create_game(
        array $playerIdArray,
        array $buttonNameArray,
        $maxWins = 3,
        $description = '',
        $previousGameId = NULL,
        $currentPlayerId = NULL,
        $autoAccept = TRUE
    ) {
        $isValidInfo =
            $this->validate_game_info(
                $playerIdArray,
                $maxWins,
                $currentPlayerId,
                $previousGameId
            );
        if (!$isValidInfo) {
            return NULL;
        }

        $buttonIdArray = $this->retrieve_button_ids($playerIdArray, $buttonNameArray);
        if (is_null($buttonIdArray)) {
            return NULL;
        }

        try {
            $gameId = $this->insert_new_game($playerIdArray, $maxWins, $description, $previousGameId);

            foreach ($playerIdArray as $position => $playerId) {
                $this->add_player_to_new_game(
                    $gameId,
                    $playerId,
                    $buttonIdArray[$position],
                    $position,
                    (0 == $position) || $autoAccept || $this->retrieve_player_autoaccept($playerId)
                );
            }
            $this->set_random_button_flags($gameId, $buttonNameArray);

            // update game state to latest possible
            $game = $this->load_game($gameId);
            if (!($game instanceof BMGame)) {
                throw new Exception(
                    "Could not load newly-created game $gameId"
                );
            }
            if ($previousGameId) {
                $chatNotice = '[i]Continued from [game=' . $previousGameId . '][i]';
                $game->add_chat(-1, $chatNotice);
            }
            $this->save_game($game);

            $this->set_message("Game $gameId created successfully.");
            return array('gameId' => $gameId);
        } catch (Exception $e) {
            $this->set_message('Game create failed: ' . $e->getMessage());
            error_log(
                'Caught exception in BMInterface::create_game: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    protected function insert_new_game(
        array $playerIdArray,
        $maxWins = 3,
        $description = '',
        $previousGameId = NULL
    ) {
        try {
            // create basic game details
            $query = 'INSERT INTO game '.
                     '    (status_id, '.
                     '     n_players, '.
                     '     n_target_wins, '.
                     '     n_recent_passes, '.
                     '     creator_id, '.
                     '     start_time, '.
                     '     description, '.
                     '     previous_game_id) '.
                     'VALUES '.
                     '    ((SELECT id FROM game_status WHERE name = :status), '.
                     '     :n_players, '.
                     '     :n_target_wins, '.
                     '     :n_recent_passes, '.
                     '     :creator_id, '.
                     '     FROM_UNIXTIME(:start_time), '.
                     '     :description, '.
                     '     :previous_game_id)';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':status'        => 'OPEN',
                                      ':n_players'     => count($playerIdArray),
                                      ':n_target_wins' => $maxWins,
                                      ':n_recent_passes' => 0,
                                      ':creator_id'    => $playerIdArray[0],
                                      ':start_time' => time(),
                                      ':description' => $description,
                                      ':previous_game_id' => $previousGameId));

            $statement = self::$conn->prepare('SELECT LAST_INSERT_ID()');
            $statement->execute();
            $fetchData = $statement->fetch();
            $gameId = (int)$fetchData[0];
            return $gameId;
        } catch (Exception $e) {
            // Failure might occur on DB insert or afterward
            $errorData = $statement->errorInfo();
            if ($errorData[2]) {
                $this->set_message('Game create failed: ' . $errorData[2]);
            } else {
                $this->set_message('Game create failed: ' . $e->getMessage());
            }
            error_log(
                'Caught exception in BMInterface::insert_new_game: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    protected function add_player_to_new_game($gameId, $playerId, $buttonId, $position, $hasAccepted) {
        // add info to game_player_map
        $query = 'INSERT INTO game_player_map '.
                 '(game_id, player_id, button_id, position, has_player_accepted) '.
                 'VALUES '.
                 '(:game_id, :player_id, :button_id, :position, :has_player_accepted)';
        $statement = self::$conn->prepare($query);

        $statement->execute(array(':game_id'   => $gameId,
                                  ':player_id' => $playerId,
                                  ':button_id' => $buttonId,
                                  ':position'  => $position,
                                  ':has_player_accepted' => $hasAccepted));
    }

    protected function set_random_button_flags($gameId, array $buttonNameArray) {
        foreach ($buttonNameArray as $position => $buttonName) {
            if ('__random' == $buttonName) {
                $query = 'UPDATE game_player_map '.
                         'SET is_button_random = 1 '.
                         'WHERE game_id = :game_id '.
                         'AND position = :position;';
                $statement = self::$conn->prepare($query);

                $statement->execute(array(':game_id'   => $gameId,
                                          ':position'  => $position));
            }
        }
    }

    protected function validate_game_info(
        array $playerIdArray,
        $maxWins,
        $currentPlayerId,
        $previousGameId
    ) {
        $areAllPlayersPresent = TRUE;
        // check for the possibility of unspecified players
        foreach ($playerIdArray as $playerId) {
            if (is_null($playerId)) {
                $areAllPlayersPresent = FALSE;
            }
        }

        // check for nonunique player ids
        if ($areAllPlayersPresent &&
            count(array_flip($playerIdArray)) < count($playerIdArray)) {
            $this->set_message('Game create failed because a player has been selected more than once.');
            return FALSE;
        }

        // validate all inputs
        foreach ($playerIdArray as $playerId) {
            if (!(is_null($playerId) || is_int($playerId))) {
                $this->set_message('Game create failed because player ID is not valid.');
                return FALSE;
            }
        }

        // force first player ID to be the current player ID, if specified
        if (!is_null($currentPlayerId)) {
            if ($currentPlayerId !== $playerIdArray[0]) {
                $this->set_message('Game create failed because you must be the first player.');
                error_log(
                    'validate_game_info() failed because currentPlayerId (' . $currentPlayerId .
                    ') does not match playerIdArray[0] (' . $playerIdArray[0] . ')'
                );
                return FALSE;
            }
        }

        if (FALSE ===
            filter_var(
                $maxWins,
                FILTER_VALIDATE_INT,
                array('options'=>
                      array('min_range' => 1,
                            'max_range' => 5))
            )) {
            $this->set_message('Game create failed because the maximum number of wins was invalid.');
            return FALSE;
        }

        // Check that players match those from previous game, if specified
        $arePreviousPlayersValid =
            $this->validate_previous_game_players($previousGameId, $playerIdArray);
        if (!$arePreviousPlayersValid) {
            return FALSE;
        }

        return TRUE;
    }

    protected function validate_previous_game_players($previousGameId, array $playerIdArray) {
        // If there was no previous game, then there's nothing to worry about
        if ($previousGameId == NULL) {
            return TRUE;
        }

        try {
            $query =
                'SELECT pm.player_id, s.name AS status ' .
                'FROM game g ' .
                    'INNER JOIN game_player_map pm ON pm.game_id = g.id ' .
                    'INNER JOIN game_status s ON s.id = g.status_id ' .
                'WHERE g.id = :previous_game_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':previous_game_id' => $previousGameId));

            $previousPlayerIds = array();
            while ($row = $statement->fetch()) {
                if (($row['status'] != 'COMPLETE') &&
                    ($row['status'] != 'REJECTED')) {
                    $this->set_message(
                        'Game create failed because the previous game has not been completed yet.'
                    );
                    return FALSE;
                }
                $previousPlayerIds[] = (int)$row['player_id'];
            }

            if (count($previousPlayerIds) == 0) {
                $this->set_message(
                    'Game create failed because the previous game was not found.'
                );
                return FALSE;
            }

            foreach ($playerIdArray as $newPlayerId) {
                if (!in_array($newPlayerId, $previousPlayerIds)) {
                    $this->set_message(
                        'Game create failed because the previous game does not contain the same players.'
                    );
                    return FALSE;
                }
            }
            foreach ($previousPlayerIds as $oldPlayerId) {
                if (!in_array($oldPlayerId, $playerIdArray)) {
                    $this->set_message(
                        'Game create failed because the previous game does not contain the same players.'
                    );
                    return FALSE;
                }
            }

            return TRUE;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::validate_previous_game_players: ' .
                $e->getMessage()
            );
            $this->set_message('Game create failed because of an error.');
            return FALSE;
        }
    }

    protected function retrieve_button_ids($playerIdArray, $buttonNameArray) {
        $buttonIdArray = array();
        foreach (array_keys($playerIdArray) as $position) {
            // get button ID
            $buttonName = $buttonNameArray[$position];

            if ('__random' == $buttonName) {
                $buttonIdArray[] = NULL;
            } elseif (!empty($buttonName)) {
                $query = 'SELECT id FROM button '.
                         'WHERE name = :button_name';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':button_name' => $buttonName));
                $fetchData = $statement->fetch();
                if (FALSE === $fetchData) {
                    $this->set_message('Game create failed because a button name was not valid.');
                    return NULL;
                }
                $buttonIdArray[] = $fetchData[0];
            } else {
                $buttonIdArray[] = NULL;
            }
        }

        return $buttonIdArray;
    }

    protected function retrieve_player_autoaccept($playerId) {
        $query = 'SELECT autoaccept FROM player '.
                 'WHERE id = :player_id';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':player_id' => $playerId));
        $fetchData = $statement->fetch();
        if (FALSE === $fetchData) {
            $this->set_message('Game create failed because a player id was not valid.');
            return NULL;
        }
        return $fetchData[0];
    }

    public function join_open_game($currentPlayerId, $gameId) {
        try {
            $game = $this->load_game($gameId);

            // check that there are still unspecified players and
            // that the player is not already part of the game
            $emptyPlayerIdx = NULL;
            $isPlayerPartOfGame = FALSE;

            foreach ($game->playerArray as $playerIdx => $player) {
                if (is_null($player->playerId) && is_null($emptyPlayerIdx)) {
                    $emptyPlayerIdx = $playerIdx;
                } elseif ($currentPlayerId == $player->playerId) {
                    $isPlayerPartOfGame = TRUE;
                    break;
                }
            }

            if ($isPlayerPartOfGame) {
                $this->set_message('You are already playing in this game.');
                return FALSE;
            }

            if (is_null($emptyPlayerIdx)) {
                $this->set_message('No empty player slots in game '.$gameId.'.');
                return FALSE;
            }

            $query = 'UPDATE game_player_map SET player_id = :player_id '.
                     'WHERE game_id = :game_id '.
                     'AND position = :position';
            $statement = self::$conn->prepare($query);

            $statement->execute(array(':game_id'   => $gameId,
                                      ':player_id' => $currentPlayerId,
                                      ':position'  => $emptyPlayerIdx));

            $query = 'UPDATE game SET start_time = FROM_UNIXTIME(:start_time) '.
                     'WHERE id = :id';
            $statement = self::$conn->prepare($query);

            $statement->execute(array(':start_time' => time(),
                                      ':id'         => $gameId));

            $game = $this->load_game($gameId);
            $player = $game->playerArray[$emptyPlayerIdx];
            $player->hasPlayerAcceptedGame = TRUE;
            $this->save_game($game);
            $this->set_message('Successfully joined game ' . $gameId);

            return TRUE;
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::join_open_game: ".
                $e->getMessage()
            );
            $this->set_message('Internal error while joining open game');
        }
    }

    public function select_button(
        $playerId,
        $gameId,
        $buttonName
    ) {
        try {
            if (empty($buttonName)) {
                return FALSE;
            }

            $game = $this->load_game($gameId);

            $playerIdx = array_search($playerId, $game->playerIdArray);

            if (FALSE === $playerIdx) {
                $this->set_message('Player is not a participant in game.');
                return FALSE;
            }

            if (!is_null($game->playerArray[$playerIdx]->button)) {
                $this->set_message('Button has already been selected.');
                return FALSE;
            }

            if ('__random' == $buttonName) {
                $query = 'UPDATE game_player_map SET is_button_random = 1 '.
                         'WHERE game_id = :game_id '.
                         'AND player_id = :player_id';

                $statement = self::$conn->prepare($query);

                $statement->execute(array(':game_id'   => $gameId,
                                          ':player_id' => $playerId));
            } else {
                $query = 'SELECT id FROM button '.
                         'WHERE name = :button_name';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':button_name' => $buttonName));
                $fetchData = $statement->fetch();
                if (FALSE === $fetchData) {
                    $this->set_message('Button select failed because button name was not valid.');
                    return FALSE;
                }
                $buttonId = $fetchData[0];

                $query = 'UPDATE game_player_map SET button_id = :button_id '.
                         'WHERE game_id = :game_id '.
                         'AND player_id = :player_id';

                $statement = self::$conn->prepare($query);

                $statement->execute(array(':game_id'   => $gameId,
                                          ':player_id' => $playerId,
                                          ':button_id' => $buttonId));
            }

            $query = 'UPDATE game SET start_time = FROM_UNIXTIME(:start_time) '.
                     'WHERE id = :id';
            $statement = self::$conn->prepare($query);

            $statement->execute(array(':start_time' => time(),
                                      ':id'         => $gameId));

            $game = $this->load_game($gameId);
            $this->save_game($game);

            return TRUE;
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::select_button: ".
                $e->getMessage()
            );
            $this->set_message('Internal error while selecting button');
        }
    }

    public function submit_die_values(
        $playerId,
        $gameId,
        $roundNumber,
        $swingValueArray,
        $optionValueArray
    ) {
        try {
            $game = $this->load_game($gameId);
            $currentPlayerIdx = array_search($playerId, $game->playerIdArray);

            // check that the timestamp and the game state are correct, and that
            // the die values still need to be set
            if (!$this->is_action_current(
                $game,
                BMGameState::SPECIFY_DICE,
                'ignore',
                $roundNumber,
                $playerId
            )) {
                $this->set_message('Dice sizes no longer need to be set');
                return NULL;
            }

            $isSwingSetSuccessful = $this->set_swing_values($swingValueArray, $currentPlayerIdx, $game);
            if (!$isSwingSetSuccessful) {
                return NULL;
            }

            $this->set_option_values($optionValueArray, $currentPlayerIdx, $game);

            // Create the action log entry for choosing die values
            // now, so it will happen before any initiative actions.
            // If the swing/option selection is unsuccessful,
            // save_game() won't be called, so this action log entry
            // will simply be dropped.
            $optionLogArray = array();
            foreach ($optionValueArray as $dieIdx => $optionValue) {
                $dieRecipe = $game->playerArray[$currentPlayerIdx]->activeDieArray[$dieIdx]->recipe;
                $optionLogArray[$dieRecipe] = $optionValue;
            }
            $game->log_action(
                'choose_die_values',
                $game->playerArray[$currentPlayerIdx]->playerId,
                array(
                    'roundNumber' => $game->roundNumber,
                    'swingValues' => $swingValueArray,
                    'optionValues' => $optionLogArray,
                )
            );

            $game->proceed_to_next_user_action();
            // check for successful swing value set
            if ((FALSE == $game->playerArray[$currentPlayerIdx]->waitingOnAction) ||
                ($game->gameState > BMGameState::SPECIFY_DICE) ||
                ($game->roundNumber > $roundNumber)) {
                $this->save_game($game);
                $this->set_message('Successfully set die sizes');
                return TRUE;
            } else {
                if ($game->message) {
                    $this->set_message($game->message);
                } else {
                    $this->set_message('Failed to set die sizes');
                }
                return NULL;
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::submit_die_values: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while setting die sizes');
        }
    }

    protected function set_swing_values($swingValueArray, $currentPlayerIdx, $game) {
        $player = $game->playerArray[$currentPlayerIdx];
        $player->swingValueArray = $swingValueArray;
        $swingRequestArray = $player->swingRequestArray;
        if (is_array($swingRequestArray)) {
            $swingRequested = array_keys($player->swingRequestArray);
            sort($swingRequested);
        } else {
            $swingRequested = array();
        }

        if (is_array($swingValueArray)) {
            $swingSubmitted = array_keys($swingValueArray);
            sort($swingSubmitted);
        } else {
            $swingSubmitted = array();
        }

        $isSwingSetSuccessful = ($swingRequested == $swingSubmitted);

        if (!$isSwingSetSuccessful) {
            $this->set_message('Wrong swing values submitted: expected ' . implode(',', $swingRequested));
        }

        return $isSwingSetSuccessful;
    }

    protected function set_option_values($optionValueArray, $currentPlayerIdx, $game) {
        if (is_array($optionValueArray)) {
            $player = $game->playerArray[$currentPlayerIdx];
            foreach ($optionValueArray as $dieIdx => $optionValue) {
                $player->optValueArray[$dieIdx] = $optionValue;
            }
        }
    }

    public function submit_turn(
        $playerId,
        $gameId,
        $roundNumber,
        $submitTimestamp,
        $dieSelectStatus,
        $attackType,
        $attackerIdx,
        $defenderIdx,
        $chat
    ) {
        try {
            $game = $this->load_game($gameId);
            if (!$this->is_action_current(
                $game,
                BMGameState::START_TURN,
                $submitTimestamp,
                $roundNumber,
                $playerId
            )) {
                $this->set_message('It is not your turn to attack right now');
                return NULL;
            }

            // N.B. dieSelectStatus should contain boolean values of whether each
            // die is selected, starting with attacker dice and concluding with
            // defender dice

            // attacker and defender indices are provided in POST
            $attackers = array();
            $defenders = array();
            $attackerDieIdx = array();
            $defenderDieIdx = array();

            // divide selected dice up into attackers and defenders
            $nAttackerDice = count($game->playerArray[$attackerIdx]->activeDieArray);
            $nDefenderDice = count($game->playerArray[$defenderIdx]->activeDieArray);

            for ($dieIdx = 0; $dieIdx < $nAttackerDice; $dieIdx++) {
                if (filter_var(
                    $dieSelectStatus["playerIdx_{$attackerIdx}_dieIdx_{$dieIdx}"],
                    FILTER_VALIDATE_BOOLEAN
                )) {
                    $attackers[] = $game->playerArray[$attackerIdx]->activeDieArray[$dieIdx];
                    $attackerDieIdx[] = $dieIdx;
                }
            }

            for ($dieIdx = 0; $dieIdx < $nDefenderDice; $dieIdx++) {
                if (filter_var(
                    $dieSelectStatus["playerIdx_{$defenderIdx}_dieIdx_{$dieIdx}"],
                    FILTER_VALIDATE_BOOLEAN
                )) {
                    $defenders[] = $game->playerArray[$defenderIdx]->activeDieArray[$dieIdx];
                    $defenderDieIdx[] = $dieIdx;
                }
            }

            // populate BMAttack object for the specified attack
            $game->attack = array($attackerIdx, $defenderIdx,
                                  $attackerDieIdx, $defenderDieIdx,
                                  $attackType);
            $attack = BMAttack::create($attackType);

            foreach ($attackers as $attackDie) {
                $attack->add_die($attackDie);
            }

            $game->add_chat($playerId, $chat);

            // validate the attack and output the result
            if ($attack->validate_attack($game, $attackers, $defenders)) {
                $this->save_game($game);

                // On success, don't set a message, because one will be set from the action log
                return TRUE;
            } else {
                if (empty($attack->validationMessage)) {
                    $this->set_message('Requested attack is not valid');
                } else {
                    $this->set_message($attack->validationMessage);
                }
                return NULL;
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::submit_turn: ' .
                $e->getMessage()
            );
            var_dump($e->getMessage());
            $this->set_message('Internal error while submitting turn');
        }
    }

    // Insert a new chat message into the database
    protected function db_insert_chat($playerId, $gameId, $chat) {

        $query = 'INSERT INTO game_chat_log ' .
                 '(game_id, chatting_player, message) ' .
                 'VALUES ' .
                 '(:game_id, :chatting_player, :message)';
        $statement = self::$conn->prepare($query);
        $statement->execute(
            array(':game_id'         => $gameId,
                  ':chatting_player' => $playerId,
                  ':message'         => $chat)
        );
    }

    // Modify an existing chat message in the database
    protected function db_update_chat($playerId, $gameId, $editTimestamp, $chat) {
        $query = 'UPDATE game_chat_log ' .
                 'SET message = :message, chat_time = now() ' .
                 'WHERE game_id = :game_id ' .
                 'AND chatting_player = :player_id ' .
                 'AND UNIX_TIMESTAMP(chat_time) = :timestamp ' .
                 'ORDER BY id DESC ' .
                 'LIMIT 1';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':message' => $chat,
                                  ':game_id' => $gameId,
                                  ':player_id' => $playerId,
                                  ':timestamp' => $editTimestamp));
    }

    // Delete an existing chat message in the database
    protected function db_delete_chat($playerId, $gameId, $editTimestamp) {
        $query = 'DELETE FROM game_chat_log ' .
                 'WHERE game_id = :game_id ' .
                 'AND chatting_player = :player_id ' .
                 'AND UNIX_TIMESTAMP(chat_time) = :timestamp ' .
                 'ORDER BY id DESC ' .
                 'LIMIT 1';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':game_id' => $gameId,
                                  ':player_id' => $playerId,
                                  ':timestamp' => $editTimestamp));
    }

    // Can the active player insert a new chat entry (without an attack) right now?
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
            $chatArray = $this->load_game_chat_log($game, 1);
            $lastChatEntryList = $chatArray['chatEntries'];
            $logArray = $this->load_game_action_log($game, 1);
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
                        $this->set_message('No game message specified');
                        return FALSE;
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

    // react_to_auxiliary expects the following inputs:
    //
    //   $action:
    //       One of {'add', 'decline'}.
    //
    //   $dieIdx:
    //       (i)  If this is an 'add' action, then this is the die index of the
    //            die to be added.
    //       (ii) If this is a 'decline' action, then this will be ignored.
    //
    // The function returns a boolean telling whether the reaction has been
    // successful.
    // If it fails, $this->message will say why it has failed.

    public function react_to_auxiliary(
        $playerId,
        $gameId,
        $action,
        $dieIdx = NULL
    ) {
        try {
            $game = $this->load_game($gameId);
            if (!$this->is_action_current(
                $game,
                BMGameState::CHOOSE_AUXILIARY_DICE,
                'ignore',
                'ignore',
                $playerId
            )) {
                return FALSE;
            }

            $playerIdx = array_search($playerId, $game->playerIdArray);
            $player = $game->playerArray[$playerIdx];

            switch ($action) {
                case 'add':
                    if (!array_key_exists($dieIdx, $player->activeDieArray) ||
                        !$player->activeDieArray[$dieIdx]->has_skill('Auxiliary')) {
                        $this->set_message('Invalid auxiliary choice');
                        return FALSE;
                    }
                    $die = $player->activeDieArray[$dieIdx];
                    $die->add_flag('AddAuxiliary');
                    $player->waitingOnAction = FALSE;
                    $game->log_action(
                        'add_auxiliary',
                        $player->playerId,
                        array(
                            'roundNumber' => $game->roundNumber,
                            'die' => $die->get_action_log_data(),
                        )
                    );
                    $this->set_message('Chose to add auxiliary die');
                    break;
                case 'decline':
                    $game->setAllToNotWaiting();
                    $game->log_action(
                        'decline_auxiliary',
                        $player->playerId,
                        array('declineAuxiliary' => TRUE)
                    );
                    $this->set_message('Declined auxiliary dice');
                    break;
                default:
                    $this->set_message('Invalid response to auxiliary choice.');
                    return FALSE;
            }
            $this->save_game($game);
            return TRUE;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::react_to_auxiliary: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while making auxiliary decision');
            return FALSE;
        }
    }

    // react_to_reserve expects the following inputs:
    //
    //   $action:
    //       One of {'add', 'decline'}.
    //
    //   $dieIdx:
    //       (i)  If this is an 'add' action, then this is the die index of the
    //            die to be added.
    //       (ii) If this is a 'decline' action, then this will be ignored.
    //
    // The function returns a boolean telling whether the reaction has been
    // successful.
    // If it fails, $this->message will say why it has failed.

    public function react_to_reserve(
        $playerId,
        $gameId,
        $action,
        $dieIdx = NULL
    ) {
        try {
            $game = $this->load_game($gameId);
            if (!$this->is_action_current(
                $game,
                BMGameState::CHOOSE_RESERVE_DICE,
                'ignore',
                'ignore',
                $playerId
            )) {
                return FALSE;
            }

            $playerIdx = array_search($playerId, $game->playerIdArray);
            $player = $game->playerArray[$playerIdx];

            switch ($action) {
                case 'add':
                    if (!array_key_exists($dieIdx, $player->activeDieArray) ||
                        !$player->activeDieArray[$dieIdx]->has_skill('Reserve')) {
                        $this->set_message('Invalid reserve choice');
                        return FALSE;
                    }
                    $die = $player->activeDieArray[$dieIdx];
                    $die->add_flag('AddReserve');
                    $player->waitingOnAction = FALSE;
                    $game->log_action(
                        'add_reserve',
                        $player->playerId,
                        array( 'die' => $die->get_action_log_data(), )
                    );
                    $this->set_message('Reserve die chosen successfully');
                    break;
                case 'decline':
                    $player->waitingOnAction = FALSE;
                    $game->log_action(
                        'decline_reserve',
                        $player->playerId,
                        array('declineReserve' => TRUE)
                    );
                    $this->set_message('Declined reserve dice');
                    break;
                default:
                    $this->set_message('Invalid response to reserve choice.');
                    return FALSE;
            }

            $this->save_game($game);

            return TRUE;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::react_to_reserve: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while making reserve decision');
            return FALSE;
        }
    }
}
