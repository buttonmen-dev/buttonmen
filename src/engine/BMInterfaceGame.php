<?php

/**
 * BMInterfaceGame: interface between GUI and BMGame for all game-related requests
 *
 * @author james
 */

/**
 * This class deals with communication between the UI, the game code, and the database
 */

class BMInterfaceGame extends BMInterface {
    /**
     * This magic constant for a random button ID is an obviously invalid
     * (negative) value. It is used by the tournament code to signal that
     * the tournament code needs to randomly select a button when it begins.
     */
    const RANDOM_BUTTON_ID = -999;

    /**
     * Create a game
     *
     * @param array $playerIdArray
     * @param array $buttonNameArray
     * @param int $maxWins
     * @param string $description
     * @param int|NULL $previousGameId
     * @param int|NULL $currentPlayerId
     * @param bool $autoAccept
     * @param array $customRecipeArray
     * @return array|NULL
     */
    public function create_game(
        array $playerIdArray,
        array $buttonNameArray,
        $maxWins = 3,
        $description = '',
        $previousGameId = NULL,
        $currentPlayerId = NULL,
        $autoAccept = TRUE,
        array $customRecipeArray = array(),
        $tournamentId = NULL,
        $tournamentRoundNumber = NULL
    ) {
        $isValidInfo = $this->validate_game_info(
            $playerIdArray,
            $maxWins,
            $previousGameId,
            $buttonNameArray,
            $customRecipeArray
        );
        if (!$isValidInfo) {
            return NULL;
        }

        $buttonIdArray = $this->retrieve_button_ids($playerIdArray, $buttonNameArray);
        // check that the first button has been specified
        if (is_null($buttonIdArray) || empty($buttonIdArray[0])) {
            $this->set_message("The first button needs to be set.");
            return NULL;
        }

        $result = $this->create_game_from_button_ids(
            $playerIdArray,
            $buttonIdArray,
            $buttonNameArray,
            $maxWins,
            $description,
            $previousGameId,
            $currentPlayerId,
            $autoAccept,
            $customRecipeArray,
            $tournamentId,
            $tournamentRoundNumber
        );

        if (is_array($result)) {
            $gameId = $result['gameId'];
            $this->set_message("Game $gameId created successfully.");
        }

        return $result;
    }

    public function create_game_from_button_ids(
        array $playerIdArray,
        array $buttonIdArray,
        array $buttonNameArray,
        $maxWins,
        $description,
        $previousGameId,
        $currentPlayerId,
        $autoAccept,
        $customRecipeArray,
        $tournamentId,
        $tournamentRoundNumber
    ) {
        try {
            if (!isset($currentPlayerId)) {
                throw new LogicException(
                    "$currentPlayerId must be set"
                );
            }

            $gameId = $this->insert_new_game(
                $playerIdArray,
                $maxWins,
                $description,
                $previousGameId,
                $currentPlayerId
            );

            foreach ($playerIdArray as $position => $playerId) {
                $hasAcceptedGame = ($playerId === $currentPlayerId) ||
                                   $autoAccept ||
                                   $this->retrieve_player_autoaccept($playerId);
                $isChatPrivate = TRUE;

                $this->add_player_to_new_game(
                    $gameId,
                    $playerId,
                    $buttonIdArray[$position],
                    $position,
                    $hasAcceptedGame,
                    $isChatPrivate
                );
            }

            $this->set_random_button_flags($gameId, $buttonIdArray);
            if (!$this->set_custom_recipes($gameId, $buttonNameArray, $customRecipeArray)) {
                return NULL;
            }

            // update game state to latest possible
            $game = $this->load_game($gameId, NULL, TRUE);
            if (!($game instanceof BMGame)) {
                throw new UnexpectedValueException(
                    "Could not load newly-created game $gameId"
                );
            }
            $game->log_action(
                'create_game',
                0,
                array(
                    'creatorId' => $currentPlayerId,
                )
            );
            if ($previousGameId) {
                $chatNotice = '[i]Continued from [game=' . $previousGameId . '][i]';
                $game->add_chat(-1, $chatNotice);
            }
            if ($tournamentId) {
                $game->tournamentId = $tournamentId;
                $game->tournamentRoundNumber = $tournamentRoundNumber;
            }
            $this->save_game($game);

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

    /**
     * Insert a new game
     *
     * @param array $playerIdArray
     * @param int $maxWins
     * @param string $description
     * @param int|NULL $previousGameId
     * @param int|NULL $creatorId
     * @return int|NULL
     */
    protected function insert_new_game(
        array $playerIdArray,
        $maxWins = 3,
        $description = '',
        $previousGameId = NULL,
        $creatorId = NULL
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
            $parameters = array(':status'        => 'OPEN',
                                ':n_players'     => count($playerIdArray),
                                ':n_target_wins' => $maxWins,
                                ':n_recent_passes' => 0,
                                ':creator_id'    => $creatorId,
                                ':start_time' => time(),
                                ':description' => $description,
                                ':previous_game_id' => $previousGameId);
            self::$db->update($query, $parameters);

            $gameId = self::$db->select_single_value('SELECT LAST_INSERT_ID()', array(), 'int');
            return $gameId;
        } catch (Exception $e) {
            $this->set_message('Game create failed: ' . $e->getMessage());
            error_log(
                'Caught exception in BMInterface::insert_new_game: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Add player to new game
     *
     * @param int $gameId
     * @param int $playerId
     * @param int $buttonId
     * @param int $position
     * @param bool $hasAccepted
     */
    protected function add_player_to_new_game($gameId, $playerId, $buttonId, $position, $hasAccepted, $isChatPrivate) {
        // add info to game_player_map
        $query = 'INSERT INTO game_player_map '.
                 '(game_id, player_id, button_id, position, has_player_accepted, is_chat_private) '.
                 'VALUES '.
                 '(:game_id, :player_id, :button_id, :position, :has_player_accepted, :is_chat_private)';
        $parameters = array(':game_id'   => $gameId,
                           ':player_id' => $playerId,
                           ':button_id' => $buttonId,
                           ':position'  => $position,
                           ':has_player_accepted' => $hasAccepted,
                           ':is_chat_private' => $isChatPrivate);
        self::$db->update($query, $parameters);
    }

    /**
     * Set flags indicating whether each button has been chosen randomly
     *
     * @param int $gameId
     * @param array $buttonIdArray
     */
    protected function set_random_button_flags($gameId, array $buttonIdArray) {
        foreach ($buttonIdArray as $position => $buttonId) {
            if (self::RANDOM_BUTTON_ID == $buttonId) {
                $query = 'UPDATE game_player_map '.
                         'SET is_button_random = 1 '.
                         'WHERE game_id = :game_id '.
                         'AND position = :position;';
                $parameters = array(':game_id'   => $gameId,
                                    ':position'  => $position);
                self::$db->update($query, $parameters);
            }
        }
    }

    /**
     * Set custom recipes
     *
     * @param int $gameId
     * @param array $buttonNameArray
     * @param array $customRecipeArray
     */
    protected function set_custom_recipes(
        $gameId,
        array $buttonNameArray,
        array $customRecipeArray
    ) {
        if (!in_array('CustomBM', $buttonNameArray)) {
            return TRUE;
        }

        if (empty($customRecipeArray)) {
            return FALSE;
        }

        // james: the custom recipe has theoretically already been checked for validity
        // in create_game()->validate_game_info(), but we include it here again in case
        // we call set_custom_recipes() from somewhere else
        if (!$this->are_custom_recipes_valid($buttonNameArray, $customRecipeArray)) {
            return FALSE;
        }

        foreach ($buttonNameArray as $position => $buttonName) {
            if ('CustomBM' == $buttonName) {
                $query = 'UPDATE game_player_map '.
                         'SET alt_recipe = :custom_recipe '.
                         'WHERE game_id = :game_id '.
                         'AND position = :position;';
                $parameters = array(':custom_recipe' => trim($customRecipeArray[$position]),
                                    ':game_id' => $gameId,
                                    ':position' => $position);
                self::$db->update($query, $parameters);
            }
        }

        return TRUE;
    }

    protected function are_custom_recipes_valid(
        array $buttonNameArray,
        array $customRecipeArray
    ) {
        foreach ($buttonNameArray as $position => $buttonName) {
            if ('CustomBM' == $buttonName) {
                if (($this->get_config('site_type') != 'development') &&
                    ($this->get_config('site_type') != 'staging')) {
                    $this->set_message('Custom recipes can only be used on development or staging sites.');
                    return FALSE;
                }

                if (empty($customRecipeArray)) {
                    $this->set_message('customRecipeArray must be supplied');
                    return FALSE;
                }

                $customRecipe = trim($customRecipeArray[$position]);
                if (empty($customRecipe)) {
                    $this->set_message('Custom recipe cannot be empty.');
                    return FALSE;
                }
                $tempButton = new BMButton;
                try {
                    $tempButton->load($customRecipe, 'CustomBM', TRUE, TRUE, TRUE);
                } catch (BMExceptionDieRecipe $e) {
                    $eInner = $e;
                    while ($eInner->getPrevious()) {
                        $eInner = $eInner->getPrevious();
                    }
                    $this->set_message($eInner->getMessage());
                    return FALSE;
                } catch (BMExceptionButtonRecipe $e) {
                    $eInner = $e;
                    while ($eInner->getPrevious()) {
                        $eInner = $eInner->getPrevious();
                    }
                    $this->set_message($eInner->getMessage());
                    return FALSE;
                } catch (InvalidArgumentException $e) {
                    $this->set_message('Custom recipe ' . $customRecipe .
                                       ' is invalid.');
                    return FALSE;
                } catch (Exception $e) {
                    return FALSE;
                }

                if ($tempButton->hasUnimplementedSkill) {
                    $this->set_message('Custom recipe ' . $customRecipe .
                                       ' is not allowed to contain unimplemented skills.');
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    /**
     * Validate game parameters for a new game
     *
     * @param array $playerIdArray
     * @param int $maxWins
     * @param int|NULL $previousGameId
     * @return bool
     */
    protected function validate_game_info(
        array $playerIdArray,
        $maxWins,
        $previousGameId,
        array $buttonNameArray,
        array $customRecipeArray
    ) {
        if (!$this->validate_player_id_array($playerIdArray)) {
            return FALSE;
        }

        if (!$this->validate_max_wins($maxWins)) {
            return FALSE;
        }

        // Check that players match those from previous game, if specified
        $arePreviousPlayersValid =
            $this->validate_previous_game_players($previousGameId, $playerIdArray);
        if (!$arePreviousPlayersValid) {
            return FALSE;
        }

        // check that the first button has been specified
        if (empty($buttonNameArray[0])) {
            $this->set_message("The first button needs to be set.");
            return FALSE;
        }

        if (!$this->are_custom_recipes_valid($buttonNameArray, $customRecipeArray)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Validate player ID array that required players are specified, unique, and valid
     *
     * @param array $playerIdArray
     * @return bool
     */
    protected function validate_player_id_array(array $playerIdArray) {
        // check that the game has at least two players
        if (count($playerIdArray) < 2) {
            $this->set_message('Game create failed because there are not enough players.');
            return FALSE;
        }

        // check that the first player has been specified
        if (is_null($playerIdArray[0])) {
            $this->set_message('Game create failed because the first player was not specified.');
            return FALSE;
        }

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

        foreach ($playerIdArray as $playerId) {
            if (!(is_null($playerId) || is_int($playerId))) {
                $this->set_message('Game create failed because player ID is not valid.');
                return FALSE;
            }
        }

        return TRUE;
    }

    public function validate_max_wins($maxWins) {
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

        return TRUE;
    }

    /**
     * Validate that the players in the previous game were the same as the current game
     *
     * @param int $previousGameId
     * @param array $playerIdArray
     * @return bool
     */
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
            $parameters = array(':previous_game_id' => $previousGameId);
            $columnReturnTypes = array(
                'player_id' => 'int',
                'status' => 'str',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);

            $previousPlayerIds = array();
            foreach ($rows as $row) {
                if (($row['status'] != 'COMPLETE') &&
                    ($row['status'] != 'CANCELLED')) {
                    $this->set_message(
                        'Game create failed because the previous game has not been completed yet.'
                    );
                    return FALSE;
                }
                $previousPlayerIds[] = $row['player_id'];
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

    /**
     * Retrieve button IDs
     *
     * @param array $playerIdArray
     * @param array $buttonNameArray
     * @return array
     */
    public function retrieve_button_ids($playerIdArray, $buttonNameArray) {
        $buttonIdArray = array();
        foreach (array_keys($playerIdArray) as $position) {
            // get button ID
            $buttonName = $buttonNameArray[$position];

            if ('__random' == $buttonName) {
                $buttonIdArray[] = self::RANDOM_BUTTON_ID;
            } elseif (!empty($buttonName)) {
                try {
                    $query = 'SELECT id FROM button '.
                             'WHERE name = :button_name';
                    $parameters = array(':button_name' => $buttonName);
                    $buttonIdArray[] = self::$db->select_single_value($query, $parameters, 'int');
                } catch (BMExceptionDatabase $e) {
                    $this->set_message('Game create failed because a button name was not valid.');
                    return NULL;
                } catch (Exception $e) {
                    error_log(
                        'Caught exception in BMInterface::retrieve_button_ids: ' .
                        $e->getMessage()
                    );
                    return NULL;
                }
            } else {
                $buttonIdArray[] = NULL;
            }
        }

        return $buttonIdArray;
    }

    /**
     * Retrieve button names
     *
     * @param array $buttonIdArray
     * @return array
     */
    public function retrieve_button_names($buttonIdArray) {
        $buttonNameArray = array();
        foreach ($buttonIdArray as $buttonId) {
            try {
                $query = 'SELECT name FROM button '.
                         'WHERE id = :button_id';
                $parameters = array(':button_id' => $buttonId);
                $buttonNameArray[] = self::$db->select_single_value($query, $parameters, 'str');
            } catch (BMExceptionDatabase $e) {
                $this->set_message('Button ID was not valid');
                return NULL;
            } catch (Exception $e) {
                error_log(
                    'Caught exception in BMInterface::retrieve_button_names: ' .
                    $e->getMessage()
                );
                return NULL;
            }
        }

        return $buttonNameArray;
    }

    /**
     * Retrieve the autoaccept setting for a player
     *
     * @param int $playerId
     * @return bool
     */
    protected function retrieve_player_autoaccept($playerId) {
        try {
            $query = 'SELECT autoaccept FROM player '.
                     'WHERE id = :player_id';
            $parameters = array(':player_id' => $playerId);
            return self::$db->select_single_value($query, $parameters, 'bool');
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Game create failed because a player id was not valid.');
            return NULL;
        }
    }

    /**
     * Save decision about whether or not to join a game
     *
     * @param int $playerId
     * @param int $gameId
     * @param string $decision
     * @return bool
     */
    public function save_join_game_decision($playerId, $gameId, $decision) {
        if (('accept' != $decision) && ('reject' != $decision)) {
            $this->set_message('decision must be either accept or reject');
            return;
        }

        $game = $this->load_game($gameId);

        if (BMGameState::CHOOSE_JOIN_GAME != $game->gameState) {
            if (('reject' == $decision) &&
                ($playerId == $game->playerArray[0]->playerId)) {
                $decision = 'withdraw';
            }
            $this->set_message(
                'Your decision to ' .
                $decision .
                ' the game failed because the game has been updated ' .
                'since you loaded the page'
            );
            return;
        }

        $playerIdx = array_search($playerId, $game->playerIdArray);

        if ($playerIdx !== FALSE) {
            $player = $game->playerArray[$playerIdx];
            $player->waitingOnAction = FALSE;
            $decisionFlag = ('accept' == $decision);
            $player->hasPlayerAcceptedGame = $decisionFlag;
        } elseif (($playerId === $game->creatorId) &&
                  ('reject' === $decision)) {
            foreach ($game->playerArray as $player) {
                $player->waitingOnAction = FALSE;
                $decisionFlag = FALSE;
            }
        } else {
            return;
        }

        if (!$decisionFlag) {
            $game->gameState = BMGameState::CANCELLED;
        }

        $this->save_game($game);

        if ($decisionFlag) {
            $this->set_message("Joined game $gameId");
        } elseif ($playerIdx !== FALSE) {
            $this->set_message("Rejected game $gameId");
        } else {
            $this->set_message("Cancelled game $gameId");
        }

        return TRUE;
    }

    /**
     * Join an open game
     *
     * @param int $currentPlayerId
     * @param int $gameId
     * @return bool
     */
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
            $parameters = array(':game_id'   => $gameId,
                                ':player_id' => $currentPlayerId,
                                ':position'  => $emptyPlayerIdx);
            self::$db->update($query, $parameters);

            $query = 'UPDATE game SET start_time = FROM_UNIXTIME(:start_time) '.
                     'WHERE id = :id';
            $parameters = array(':start_time' => time(),
                                ':id'         => $gameId);
            self::$db->update($query, $parameters);

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

    /**
     * Cancel an open game
     *
     * @param int $currentPlayerId
     * @param int $gameId
     * @return bool
     */
    public function cancel_open_game($currentPlayerId, $gameId) {
        try {
            $game = $this->load_game($gameId);

            // check that the current player is involved in this game
            $isPlayerInThisGame = ($game->playerArray[0]->playerId == $currentPlayerId);

            if (!$isPlayerInThisGame) {
                $this->set_message('You are not involved in this game.');
                return FALSE;
            }

            // check that the game state is appropriate
            if ($game->gameState == BMGameState::CANCELLED) {
                $this->set_message('This game has already been cancelled.');
                return FALSE;
            }

            if ($game->gameState > BMGameState::CHOOSE_JOIN_GAME) {
                $this->set_message('This game has already started.');
                return FALSE;
            }

            $game->gameState = BMGameState::CANCELLED;

            $this->save_game($game);
            $this->set_message("Successfully cancelled game $gameId");

            return TRUE;
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::cancel_open_game: ".
                $e->getMessage()
            );
            $this->set_message('Internal error while cancelling open game');
        }
    }

    /**
     * Select a button
     *
     * @param int $playerId
     * @param int $gameId
     * @param string $buttonName
     * @return bool
     */
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
                $parameters = array(':game_id'   => $gameId,
                                    ':player_id' => $playerId);
                self::$db->update($query, $parameters);
            } else {
                try {
                    $query = 'SELECT id FROM button '.
                             'WHERE name = :button_name';
                    $parameters = array(':button_name' => $buttonName);
                    $buttonId = self::$db->select_single_value($query, $parameters, 'int');
                } catch (BMExceptionDatabase $e) {
                    $this->set_message('Button select failed because button name was not valid.');
                    return FALSE;
                }

                $query = 'UPDATE game_player_map SET button_id = :button_id '.
                         'WHERE game_id = :game_id '.
                         'AND player_id = :player_id';
                $parameters = array(':game_id'   => $gameId,
                                    ':player_id' => $playerId,
                                    ':button_id' => $buttonId);
                self::$db->update($query, $parameters);
            }

            $query = 'UPDATE game SET start_time = FROM_UNIXTIME(:start_time) '.
                     'WHERE id = :id';
            $parameters = array(':start_time' => time(),
                                ':id'         => $gameId);
            self::$db->update($query, $parameters);

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

    /**
     * Submit swing and option values
     *
     * @param int $playerId
     * @param int $gameId
     * @param int $roundNumber
     * @param array $swingValueArray
     * @param array $optionValueArray
     * @return bool
     */
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

            // Create the action log entries for choosing die values
            // now, so they will happen before any initiative actions.
            // If the swing/option selection is unsuccessful,
            // save_game() won't be called, so this action log entry
            // will simply be dropped.
            $swingLogArray = array();
            foreach ($swingValueArray as $swingType => $swingValue) {
                $swingLogArray[] = array(
                    'swingType'  => $swingType,
                    'swingValue' => $swingValue,
                );
            }

            $optionLogArray = array();
            foreach ($optionValueArray as $dieIdx => $optionValue) {
                $dieRecipe = $game->playerArray[$currentPlayerIdx]->activeDieArray[$dieIdx]->recipe;
                $optionLogArray[] = array(
                    'recipe'      => $dieRecipe,
                    'optionValue' => $optionValue,
                );
            }

            $game->log_action(
                'choose_die_values',
                $game->playerArray[$currentPlayerIdx]->playerId,
                array(
                    'roundNumber' => $game->roundNumber,
                    'swingValues' => $swingLogArray,
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

    /**
     * Set swing values
     *
     * @param array $swingValueArray
     * @param int $currentPlayerIdx
     * @param BMGame $game
     * @return bool
     */
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

    /**
     * Set option values
     *
     * @param array $optionValueArray
     * @param int $currentPlayerIdx
     * @param BMGame $game
     */
    protected function set_option_values($optionValueArray, $currentPlayerIdx, $game) {
        if (is_array($optionValueArray)) {
            $player = $game->playerArray[$currentPlayerIdx];
            foreach ($optionValueArray as $dieIdx => $optionValue) {
                $player->optValueArray[$dieIdx] = $optionValue;
            }
        }
    }

    /**
     * Submit a turn
     *
     * @param array $args
     * @return bool
     *
     * $args contains the following parameters:
     *   int playerId
     *   int game
     *   int roundNumber
     *   int Timestamp
     *   array dieSelectStatus
     *   string attackType
     *   int attackerIdx
     *   int defenderIdx
     *   string chat
     *   array turboSizeArray
     */
    public function submit_turn($args) {
        try {
            $game = $this->load_game($args['game']);

            if (!$this->is_action_current(
                $game,
                BMGameState::START_TURN,
                $args['timestamp'],
                $args['roundNumber'],
                $args['playerId']
            )) {
                $this->set_message('It is not your turn to attack right now');
                return NULL;
            }

            if (($args['attackType'] != 'Default') &&
                ($args['attackType'] != 'Surrender')) {
                $validAttackTypeArray = $game->valid_attack_types();
                if (!in_array($args['attackType'], $validAttackTypeArray)) {
                    $this->set_message(
                        'Attack type ' . $args['attackType'] . ' is not valid right now.'
                    );
                    return NULL;
                }
            }

            $attackers = array();
            $defenders = array();
            $attackerDieIdx = array();
            $defenderDieIdx = array();

            $this->retrieve_dice_in_attack(
                $game,
                $args,
                $attackers,
                $defenders,
                $attackerDieIdx,
                $defenderDieIdx
            );

            // populate BMAttack object for the specified attack
            $game->attack = array($args['attackerIdx'], $args['defenderIdx'],
                                  $attackerDieIdx, $defenderDieIdx,
                                  $args['attackType']);
            $attack = BMAttack::create($args['attackType']);

            foreach ($attackers as $attackDie) {
                $attack->add_die($attackDie);
            }

            $game->add_chat($args['playerId'], $args['chat']);

            // validate the attack and output the result
            if ($attack->validate_attack(
                $game,
                $attackers,
                $defenders,
                array('turboVals' => $args['turboVals'])
            )) {
                $game->proceed_to_next_user_action(array('turboVals' => $args['turboVals']));

                if (!$this->set_turbo_sizes(
                    $args['playerId'],
                    $game,
                    $args['roundNumber'],
                    $args['timestamp'],
                    $args['turboVals']
                )) {
                    return NULL;
                }

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
            $this->set_message('Internal error while submitting turn');
        }
    }

    /**
     * Retrieve attacking and defending dice involved in the current attack
     *
     * Params $attackers, $defenders, $attackerDieIdxArray, and
     * $defenderDieIdxArray are output parameters.
     *
     * @param BMGame $game
     * @param array $args
     * @param array $attackers
     * @param array $defenders
     * @param array $attackerDieIdxArray
     * @param array $defenderDieIdxArray
     */
    protected function retrieve_dice_in_attack(
        BMGame $game,
        array $args,
        array &$attackers,
        array &$defenders,
        array &$attackerDieIdxArray,
        array &$defenderDieIdxArray
    ) {
        if (!empty($attackers) ||
            !empty($defenders) ||
            !empty($attackerDieIdxArray) ||
            !empty($defenderDieIdxArray)) {
            throw new LogicException('The last four parameters are designed to be out parameters');
        }

        // dieSelectStatus should contain boolean values of whether each
        // die is selected, starting with attacker dice and concluding with
        // defender dice
        //
        // attacker and defender indices are provided in POST

        // divide selected dice up into attackers and defenders
        $nAttackerDice = count($game->playerArray[$args['attackerIdx']]->activeDieArray);
        $nDefenderDice = count($game->playerArray[$args['defenderIdx']]->activeDieArray);

        for ($dieIdx = 0; $dieIdx < $nAttackerDice; $dieIdx++) {
            if (filter_var(
                $args['dieSelectStatus']["playerIdx_{$args['attackerIdx']}_dieIdx_{$dieIdx}"],
                FILTER_VALIDATE_BOOLEAN
            )) {
                $attackers[] = $game->playerArray[$args['attackerIdx']]->activeDieArray[$dieIdx];
                $attackerDieIdxArray[] = $dieIdx;
            }
        }

        for ($dieIdx = 0; $dieIdx < $nDefenderDice; $dieIdx++) {
            if (filter_var(
                $args['dieSelectStatus']["playerIdx_{$args['defenderIdx']}_dieIdx_{$dieIdx}"],
                FILTER_VALIDATE_BOOLEAN
            )) {
                $defenders[] = $game->playerArray[$args['defenderIdx']]->activeDieArray[$dieIdx];
                $defenderDieIdxArray[] = $dieIdx;
            }
        }
    }

    /**
     * Cache selected turbo values in the BMGame
     *
     * @param BMGame $game
     * @param array $turboVals
     */
    protected function cache_turbo_vals(BMGame $game, $turboVals) {
        if (!empty($turboVals)) {
            $game->turboCache = $turboVals;
        }
    }

    /**
     * Perform an auxiliary action
     *
     * react_to_auxiliary expects the following inputs:
     *
     *   $action:
     *       One of {'add', 'decline'}.
     *
     *   $dieIdx:
     *       (i)  If this is an 'add' action, then this is the die index of the
     *            die to be added.
     *       (ii) If this is a 'decline' action, then this will be ignored.
     *
     * The function returns a boolean telling whether the reaction has been
     * successful.
     * If it fails, $this->message will say why it has failed.
     *
     * @param int $playerId
     * @param int $gameId
     * @param string $action
     * @param int $dieIdx
     * @return bool
     */
    public function react_to_auxiliary(
        $playerId,
        $gameId,
        $action,
        $dieIdx = NULL
    ) {
        try {
            $game = $this->load_game($gameId);
            if (!$game) {
                $this->set_message('Invalid game');
                return FALSE;
            }
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

                    if (!self::can_button_add_this_aux_die($player->button, $die)) {
                        $this->set_message('This button cannot add this auxiliary die');
                        return FALSE;
                    }

                    $die->add_flag('AddAuxiliary');
                    $player->waitingOnAction = FALSE;
                    $game->log_action(
                        'add_auxiliary',
                        $player->playerId,
                        array(
                            'roundNumber' => $game->roundNumber,
                            'dieRecipe' => $die->get_recipe(TRUE),
                        )
                    );
                    $this->set_message('Chose to add auxiliary die');
                    break;
                case 'decline':
                    $game->setAllToNotWaiting();
                    $game->log_action(
                        'decline_auxiliary',
                        $player->playerId,
                        array()
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

    /**
     * Can this button add this auxiliary die?
     *
     * @param BMButton $button
     * @param BMDie $die
     * @return bool
     */
    protected static function can_button_add_this_aux_die(BMButton $button, BMDie $die) {
        $hookResult = $button->run_hooks(
            __FUNCTION__,
            array('die' => $die)
        );

        return (!isset($hookResult['BMBtnSkill'.$button->name][__FUNCTION__]) ||
                $hookResult['BMBtnSkill'.$button->name][__FUNCTION__]);
    }

    /**
     * Perform a reserve action
     *
     * react_to_reserve expects the following inputs:
     *
     *   $action:
     *       One of {'add', 'decline'}.
     *
     *   $dieIdx:
     *       (i)  If this is an 'add' action, then this is the die index of the
     *            die to be added.
     *       (ii) If this is a 'decline' action, then this will be ignored.
     *
     * The function returns a boolean telling whether the reaction has been
     * successful.
     * If it fails, $this->message will say why it has failed.
     *
     * @param int $playerId
     * @param int $gameId
     * @param string $action
     * @param int $dieIdx
     * @return bool
     */
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
                        array( 'dieRecipe' => $die->get_recipe(TRUE), )
                    );
                    $this->set_message('Reserve die chosen successfully');
                    break;
                case 'decline':
                    $player->waitingOnAction = FALSE;
                    $game->log_action(
                        'decline_reserve',
                        $player->playerId,
                        array()
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

    /**
     * Perform an initiative action
     *
     * react_to_initiative expects the following inputs:
     *
     *   $action:
     *       One of {'chance', 'focus', 'decline'}.
     *
     *   $dieIdxArray:
     *       (i)   If this is a 'chance' action, then an array containing the
     *             index of the chance die that is being rerolled.
     *       (ii)  If this is a 'focus' action, then this is the nonempty array
     *             of die indices corresponding to the die values in
     *             dieValueArray. This can be either the indices of ALL focus
     *             dice OR just a subset.
     *       (iii) If this is a 'decline' action, then this will be ignored.
     *
     *   $dieValueArray:
     *       This is only used for the 'focus' action. It is a nonempty array
     *       containing the values of the focus dice that have been chosen by
     *       the user. The die indices of the dice being specified are given in
     *       $dieIdxArray.
     *
     * The function returns a boolean telling whether the reaction has been
     * successful.
     * If it fails, $this->message will say why it has failed.
     *
     * @param int $playerId
     * @param int $gameId
     * @param int $roundNumber
     * @param int $submitTimestamp
     * @param string $action
     * @param array $dieIdxArray
     * @param array $dieValueArray
     * @return bool
     */
    public function react_to_initiative(
        $playerId,
        $gameId,
        $roundNumber,
        $submitTimestamp,
        $action,
        $dieIdxArray = NULL,
        $dieValueArray = NULL
    ) {
        try {
            $game = $this->load_game($gameId);
            if (!$this->is_action_current(
                $game,
                BMGameState::REACT_TO_INITIATIVE,
                $submitTimestamp,
                $roundNumber,
                $playerId
            )) {
                return FALSE;
            }

            $playerIdx = array_search($playerId, $game->playerIdArray);

            $argArray = array('action' => $action,
                              'playerIdx' => $playerIdx);

            switch ($action) {
                case 'chance':
                    if (1 != count($dieIdxArray)) {
                        $this->set_message('Only one chance die can be rerolled');
                        return FALSE;
                    }
                    $argArray['rerolledDieIdx'] = $dieIdxArray[0];
                    break;
                case 'focus':
                    if (count($dieIdxArray) != count($dieValueArray)) {
                        $this->set_message('Mismatch in number of indices and values');
                        return FALSE;
                    }
                    $argArray['focusValueArray'] = array();
                    foreach ($dieIdxArray as $tempIdx => $dieIdx) {
                        $argArray['focusValueArray'][$dieIdx] = $dieValueArray[$tempIdx];
                    }
                    break;
                case 'decline':
                    $argArray['dieIdxArray'] = $dieIdxArray;
                    $argArray['dieValueArray'] = $dieValueArray;
                    break;
                default:
                    $this->set_message('Invalid action to respond to initiative.');
                    return FALSE;
            }

            $isSuccessful = $game->react_to_initiative($argArray);
            if ($isSuccessful) {
                $this->save_game($game);

                if ($isSuccessful['gainedInitiative']) {
                    $this->set_message('Successfully gained initiative');
                } else {
                    $this->set_message('Failed to gain initiative');
                }
            } else {
                $this->set_message($game->message);
            }

            return $isSuccessful;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::react_to_initiative: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reacting to initiative');
            return FALSE;
        }
    }

    /**
     * Perform a fire action
     *
     * adjust_fire expects the following inputs:
     *
     *   $action:
     *       One of {'turndown', 'no_turndown', 'cancel'}.
     *
     *   $dieIdxArray:
     *       (i)   If this is a 'turndown' action, then this is the nonempty array
     *             of die indices corresponding to the die values in
     *             dieValueArray. This can be either the indices of ALL fire
     *             dice OR just a subset.
     *       (ii)  If this is a 'no_turndown' action, then this will be ignored.
     *       (iii) If this is a 'cancel' action, then this will be ignored.
     *
     *   $dieValueArray:
     *       This is only used for the 'turndown' action. It is a nonempty array
     *       containing the values of the fire dice that have been chosen by
     *       the user. The die indices of the dice being specified are given in
     *       $dieIdxArray.
     *
     * The function returns a boolean telling whether the reaction has been
     * successful.
     * If it fails, $this->message will say why it has failed.
     *
     * @param int $playerId
     * @param int $gameId
     * @param int $roundNumber
     * @param int $submitTimestamp
     * @param string $action
     * @param array $dieIdxArray
     * @param array $dieValueArray
     * @return bool
     */
    public function adjust_fire(
        $playerId,
        $gameId,
        $roundNumber,
        $submitTimestamp,
        $action,
        $dieIdxArray = NULL,
        $dieValueArray = NULL
    ) {
        try {
            $game = $this->load_game($gameId);
            if (!$this->is_action_current(
                $game,
                BMGameState::ADJUST_FIRE_DICE,
                $submitTimestamp,
                $roundNumber,
                $playerId
            )) {
                return FALSE;
            }

            $playerIdx = array_search($playerId, $game->playerIdArray);

            $argArray = array('action' => $action,
                              'playerIdx' => $playerIdx);

            switch ($action) {
                case 'turndown':
                    if (0 == count($dieIdxArray)) {
                        $this->set_message('At least one fire value must be turned down for a turndown action');
                        return FALSE;
                    }

                    if (count($dieIdxArray) != count($dieValueArray)) {
                        $this->set_message('Mismatch in number of indices and values');
                        return FALSE;
                    }

                    $argArray['fireValueArray'] = array();
                    foreach ($dieIdxArray as $tempIdx => $dieIdx) {
                        $argArray['fireValueArray'][$dieIdx] = $dieValueArray[$tempIdx];
                    }
                    break;
                case 'no_turndown':
                    $argArray['dieIdxArray'] = $dieIdxArray;
                    $argArray['dieValueArray'] = $dieValueArray;
                    break;
                case 'cancel':
                    $argArray['dieIdxArray'] = $dieIdxArray;
                    $argArray['dieValueArray'] = $dieValueArray;
                    unset($game->turboCache);
                    break;
                default:
                    $this->set_message('Invalid action to adjust fire dice.');
                    return FALSE;
            }

            $isSuccessful = $game->react_to_firing($argArray);
            if ($isSuccessful) {
                $game->proceed_to_next_user_action();

                if (!is_null($game->turboCache)) {
                    if (!$this->set_turbo_sizes(
                        $playerId,
                        $game,
                        $roundNumber,
                        'ignore',
                        $game->turboCache
                    )) {
                        $this->set_message(
                            'Setting turbo dice failed.  Cancel Fire turndown and retry the initial attack.'
                        );
                        return NULL;
                    }
                }

                $this->save_game($game);
            } else {
                $this->set_message('Invalid fire turndown');
            }

            return $isSuccessful;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::adjust_fire: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while adjusting fire dice');
            return FALSE;
        }
    }

    /**
     * Set turbo die sizes
     *
     * @param int $playerId
     * @param BMGame $game
     * @param int $roundNumber
     * @param int $submitTimestamp
     * @param array $turboSizeArray
     * @return bool
     */
    public function set_turbo_sizes(
        $playerId,
        $game,
        $roundNumber,
        $submitTimestamp,
        $turboSizeArray
    ) {
        try {
            if (BMGameState::ADJUST_FIRE_DICE == $game->gameState) {
                $this->cache_turbo_vals($game, $turboSizeArray);
                return TRUE;
            }

            // if we're not in the right game state, simply do nothing silently
            if ((BMGameState::CHOOSE_TURBO_SWING != $game->gameState) &&
                (BMGameState::CHOOSE_TURBO_SWING_FOR_TRIP != $game->gameState)) {
                return TRUE;
            }

            if (!$this->is_action_current(
                $game,
                BMGameState::CHOOSE_TURBO_SWING,
                $submitTimestamp,
                $roundNumber,
                $playerId
            ) && !$this->is_action_current(
                $game,
                BMGameState::CHOOSE_TURBO_SWING_FOR_TRIP,
                $submitTimestamp,
                $roundNumber,
                $playerId
            )) {
                $this->set_message('Turbo die sizes cannot be set');
                return FALSE;
            }

            if (!$game->set_turbo_sizes($turboSizeArray)) {
                $this->set_message($game->message);
                return FALSE;
            }

            return TRUE;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::set_turbo_sizes: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while setting turbo sizes');
            return FALSE;
        }
    }

    /**
     * Dismiss game link from overview page
     *
     * @param int $playerId
     * @param int $gameId
     * @return bool
     */
    public function dismiss_game($playerId, $gameId) {
        try {
            $query =
                'SELECT s.name AS "status", m.was_game_dismissed ' .
                'FROM game AS g ' .
                'INNER JOIN game_status AS s ON s.id = g.status_id ' .
                    'INNER JOIN game_player_map AS m ' .
                    'ON m.game_id = g.id AND m.player_id = :player_id ' .
                'WHERE g.id = :game_id';
            $parameters = array(
                ':player_id' => $playerId,
                ':game_id' => $gameId,
            );
            $columnReturnTypes = array(
                 'status' => 'str',
                 'was_game_dismissed' => 'int',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);

            if (count($rows) == 0) {
                $this->set_message("Game $gameId does not exist, or you are not a participant");
                return NULL;
            }
            if (($rows[0]['status'] != 'COMPLETE') &&
                ($rows[0]['status'] != 'CANCELLED')) {
                $this->set_message("Game $gameId isn't complete");
                return NULL;
            }
            if ($rows[0]['was_game_dismissed'] == 1) {
                $this->set_message("You have already dismissed game $gameId");
                return NULL;
            }

            if ($rows[0]['was_game_dismissed'] !== NULL) {
                $query =
                    'UPDATE game_player_map ' .
                    'SET was_game_dismissed = 1 ' .
                    'WHERE player_id = :player_id AND game_id = :game_id';
                $parameters = array(
                    ':player_id' => $playerId,
                    ':game_id' => $gameId,
                );
                self::$db->update($query, $parameters);
            }

            $this->set_message('Dismissing game succeeded');
            return TRUE;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::dismiss_game: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while dismissing a game');
            return FALSE;
        }
    }

    /**
     * Check whether a requested action still needs to be taken.
     * If the time stamp is not important, use the string 'ignore'
     * for $postedTimestamp.
     * @param BMGame $game
     * @param int $expectedGameState
     * @param int $postedTimestamp
     * @param int $roundNumber
     * @param int $currentPlayerId
     * @return bool
     */
    protected function is_action_current(
        BMGame $game,
        $expectedGameState,
        $postedTimestamp,
        $roundNumber,
        $currentPlayerId
    ) {
        $currentPlayerIdx = array_search($currentPlayerId, $game->playerIdArray);

        if (FALSE === $currentPlayerIdx) {
            $this->set_message('You are not a participant in this game');
            return FALSE;
        }

        if (FALSE === $game->playerArray[$currentPlayerIdx]->waitingOnAction) {
            $this->set_message('You are not the active player');
            return FALSE;
        }

        $doesTimeStampAgree =
            ('ignore' === $postedTimestamp) ||
            ($postedTimestamp == $this->timestamp);
        $doesRoundNumberAgree =
            ('ignore' === $roundNumber) ||
            ($roundNumber == $game->roundNumber);
        $doesGameStateAgree = $expectedGameState == $game->gameState;

        $isGameStateCurrent =
            $doesTimeStampAgree &&
            $doesRoundNumberAgree &&
            $doesGameStateAgree;

        if (!$isGameStateCurrent) {
            $this->set_message('Game state is not current');
        }

        return $isGameStateCurrent;
    }

    public function isGameComplete($gameId) {
        $query = 'SELECT g.game_state '.
                 'FROM game AS g '.
                 'WHERE g.id = :game_id;';
        $parameters = array(':game_id' => $gameId);

        try {
            $gameState = self::$db->select_single_value($query, $parameters, 'int');
            return $gameState >= BMGameState::END_GAME;
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Game ID was not valid');
            return NULL;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterfaceGame::isGameComplete: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }
}
