<?php

/**
 * BMInterfaceGameAction: interface between GUI and BMGame for game action-related requests
 *
 * @author james
 */

/**
 * This class deals with communication between the UI, the game code, and the database
 * pertaining to game actions and chat
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

}
