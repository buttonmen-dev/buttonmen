<?php

/**
 * BMInterfaceGameAction: interface between GUI and BMGameAction for action-log-related requests
 *
 * @author chaos
 */

/**
 * This class deals with communication between the UI, the game code, and the database
 * pertaining to action-log-related information
 */

class BMInterfaceGameAction extends BMInterface {

    /**
     * Load recent game action log entries from the database
     *
     * @param BMGame $game
     * @param int|NULL $logEntryLimit
     * @return array
     */
    public function load_game_action_log(BMGame $game, $logEntryLimit) {
        try {
            $sqlParameters = array(':game_id' => $game->gameId);
            $query = 'SELECT id,UNIX_TIMESTAMP(action_time) AS action_timestamp, ' .
                     'game_state,action_type,acting_player,message ' .
                     'FROM game_action_log ';
            $query .= $this->build_game_log_query_restrictions($game, FALSE, FALSE, $sqlParameters);
            $columnReturnTypes = array(
                'id' => 'int',
                'action_timestamp' => 'int',
                'game_state' => 'int',
                'action_type' => 'str',
                'acting_player' => 'int',
                'message' => 'str_or_null',
            );
            $rows = self::$db->select_rows($query, $sqlParameters, $columnReturnTypes);

            $logEntries = array();
            $playerIdNames = $this->get_player_name_mapping($game);
            foreach ($rows as $row) {
                $typefunc = 'load_params_from_type_log_' . $row['action_type'];
                if (method_exists($this, $typefunc)) {
                    $params = $this->$typefunc($row['id']);
                } else {
                    $params = json_decode($row['message'], TRUE);
                    if (!($params)) {
                        $params = $row['message'];
                    }
                }
                $gameAction = new BMGameAction(
                    $row['game_state'],
                    $row['action_type'],
                    $row['acting_player'],
                    $params
                );

                // Only add the message to the log if one is returned: friendly_message() may
                // intentionally return no message if providing one would leak information
                $message = $gameAction->friendly_message($playerIdNames, $game->roundNumber, $game->gameState);
                if ($message) {
                    $logEntries[] = array(
                        'timestamp' => $row['action_timestamp'],
                        'player' => $this->get_player_name_from_id($gameAction->actingPlayerId),
                        'message' => $message,
                    );
                }
            }

            $nEntries = count($logEntries);

            if (!is_null($logEntryLimit) &&
                ($nEntries > $logEntryLimit)) {
                $logEntries = array_slice($logEntries, 0, $logEntryLimit);
            }

            return array('logEntries' => $logEntries, 'nEntries' => $nEntries);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_game_action_log: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading log entries');
            return NULL;
        }
    }

    /**
     * Save action log entries generated during the action in progress
     *
     * Any game action entries which were generated should both be loaded into
     * the message so the calling player can see them, and saved into the database
     *
     * @param BMGame $game
     * @return void
     */
    public function save_action_log($game) {
        if (count($game->actionLog) > 0) {
            $this->load_message_from_game_actions($game);
            $this->log_game_actions($game);
        }
    }

    /**
     * Load the parameters for a single game action log message of type create_game
     *
     * @param int $action_log_id
     * @return array
     */
    protected function load_params_from_type_log_create_game($action_log_id) {
        try {
            $query = 'SELECT creator_id FROM game_action_log_type_create_game ' .
                     'WHERE action_log_id=:action_log_id';
            $parameters = array(':action_log_id' => $action_log_id);
            $creatorId = self::$db->select_single_value($query, $parameters, 'int');
            return array(
                'creatorId' => $creatorId,
            );
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_params_from_type_log_create_game: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading log entries');
            return NULL;
        }
    }

    /**
     * Save the parameters for a single game action log message of type create_game
     *
     * @param int $action_log_id
     * @param array $params
     * @return void
     */
    protected function save_params_to_type_log_create_game($action_log_id, $params) {
        try {
            $query = 'INSERT INTO game_action_log_type_create_game ' .
                     '(action_log_id, creator_id) ' .
                     'VALUES ' .
                     '(:action_log_id, :creator_id)';
            $parameters = array(
                ':action_log_id' => $action_log_id,
                ':creator_id' => $params['creatorId'],
            );
            self::$db->update($query, $parameters);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_params_to_type_log_create_game: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while saving log entries');
            return NULL;
        }
    }

    /**
     * Load the parameters for a single game action log message of type end_draw
     *
     * @param int $action_log_id
     * @return array
     */
    protected function load_params_from_type_log_end_draw($action_log_id) {
        try {
            $query = 'SELECT round_number,round_score FROM game_action_log_type_end_draw ' .
                     'WHERE action_log_id=:action_log_id';
            $parameters = array(':action_log_id' => $action_log_id);
            $columnReturnTypes = array(
                'round_number' => 'int',
                'round_score' => 'float',
            );
            $rows = self::$db->select_rows($query, $sqlParameters, $columnReturnTypes);
            $row = $rows[0];
            return array(
                'roundNumber' => $row['round_number'],
                'roundScore' => $row['round_score'],
            );
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_params_from_type_log_end_draw: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading log entries');
            return NULL;
        }
    }

    /**
     * Save the parameters for a single game action log message of type end_draw
     *
     * @param int $action_log_id
     * @param array $params
     * @return void
     */
    protected function save_params_to_type_log_end_draw($action_log_id, $params) {
        try {
            $query = 'INSERT INTO game_action_log_type_end_draw ' .
                     '(action_log_id, round_number, round_score) ' .
                     'VALUES ' .
                     '(:action_log_id, :round_number, :round_score)';
            $parameters = array(
                ':action_log_id' => $action_log_id,
                ':round_number' => $params['roundNumber'],
                ':round_score' => $params['roundScore'],
            );
            self::$db->update($query, $parameters);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_params_to_type_log_end_draw: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while saving log entries');
            return NULL;
        }
    }

    /**
     * Load the parameters for a single game action log message of type end_winner
     *
     * @param int $action_log_id
     * @return array
     */
    protected function load_params_from_type_log_end_winner($action_log_id) {
        try {
            $query = 'SELECT round_number,winning_round_score,losing_round_score,surrendered ' .
                     'FROM game_action_log_type_end_winner ' .
                     'WHERE action_log_id=:action_log_id';
            $parameters = array(':action_log_id' => $action_log_id);
            $columnReturnTypes = array(
                'round_number' => 'int',
                'winning_round_score' => 'float',
                'losing_round_score' => 'float',
                'surrendered' => 'bool',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
            $row = $rows[0];
            return array(
                'roundNumber' => $row['round_number'],
                'winningRoundScore' => $row['winning_round_score'],
                'losingRoundScore' => $row['losing_round_score'],
                'surrendered' => $row['surrendered'],
            );
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_params_from_type_log_end_winner: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading log entries');
            return NULL;
        }
    }

    /**
     * Save the parameters for a single game action log message of type end_winner
     *
     * @param int $action_log_id
     * @param array $params
     * @return void
     */
    protected function save_params_to_type_log_end_winner($action_log_id, $params) {
        try {
            $query = 'INSERT INTO game_action_log_type_end_winner ' .
                     '(action_log_id, round_number, winning_round_score, losing_round_score, surrendered) ' .
                     'VALUES ' .
                     '(:action_log_id, :round_number, :winning_round_score, :losing_round_score, :surrendered)';
            $parameters = array(
                ':action_log_id' => $action_log_id,
                ':round_number' => $params['roundNumber'],
                ':winning_round_score' => $params['winningRoundScore'],
                ':losing_round_score' => $params['losingRoundScore'],
                ':surrendered' => $params['surrendered'],
            );
            self::$db->update($query, $parameters);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_params_to_type_log_end_winner: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while saving log entries');
            return NULL;
        }
    }

    /**
     * Load the parameters for a single game action log message of type add_auxiliary
     *
     * @param int $action_log_id
     * @return array
     */
    protected function load_params_from_type_log_add_auxiliary($action_log_id) {
        try {
            $query = 'SELECT round_number,die_recipe FROM game_action_log_type_add_auxiliary ' .
                     'WHERE action_log_id=:action_log_id';
            $parameters = array(':action_log_id' => $action_log_id);
            $columnReturnTypes = array(
                'round_number' => 'int',
                'die_recipe' => 'str',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
            $row = $rows[0];
            return array(
                'roundNumber' => $row['round_number'],
                'dieRecipe' => $row['die_recipe'],
            );
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_params_from_type_log_add_auxiliary: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading log entries');
            return NULL;
        }
    }

    /**
     * Save the parameters for a single game action log message of type add_auxiliary
     *
     * @param int $action_log_id
     * @param array $params
     * @return void
     */
    protected function save_params_to_type_log_add_auxiliary($action_log_id, $params) {
        try {
            $query = 'INSERT INTO game_action_log_type_add_auxiliary ' .
                     '(action_log_id, round_number, die_recipe) ' .
                     'VALUES ' .
                     '(:action_log_id, :round_number, :die_recipe)';
            $parameters = array(
                ':action_log_id' => $action_log_id,
                ':round_number' => $params['roundNumber'],
                ':die_recipe' => $params['dieRecipe'],
            );
            self::$db->update($query, $parameters);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_params_to_type_log_add_auxiliary: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while saving log entries');
            return NULL;
        }
    }

    /**
     * Load the parameters for a single game action log message of type decline_auxiliary
     *
     * @return array
     */
    protected function load_params_from_type_log_decline_auxiliary() {
        // decline_auxiliary has no secondary parameters
        return array();
    }

    /**
     * Save the parameters for a single game action log message of type decline_auxiliary
     *
     * @return void
     */
    protected function save_params_to_type_log_decline_auxiliary() {
        // decline_auxiliary has no secondary parameters
        return;
    }

    /**
     * Load the parameters for a single game action log message of type turndown_focus
     *
     * @param int $action_log_id
     * @return array
     */
    protected function load_params_from_type_log_turndown_focus($action_log_id) {
        try {
            // turndown_focus has one set of secondary parameters for each die which was turned down
            $query = 'SELECT recipe,orig_value,turndown_value FROM game_action_log_type_turndown_focus_die ' .
                     'WHERE action_log_id=:action_log_id';
            $parameters = array(':action_log_id' => $action_log_id);
            $columnReturnTypes = array(
                'recipe' => 'str',
                'orig_value' => 'int',
                'turndown_value' => 'int',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
            $turndownDice = array();
            foreach ($rows as $row) {
                $turndownDice[] = array(
                    'recipe'        => $row['recipe'],
                    'origValue'     => $row['orig_value'],
                    'turndownValue' => $row['turndown_value'],
                );
            }
            return array(
                'turndownDice' => $turndownDice,
            );
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_params_from_type_log_turndown_focus: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading log entries');
            return NULL;
        }
    }

    /**
     * Save the parameters for a single game action log message of type turndown_focus
     *
     * @param int $action_log_id
     * @param array $params
     * @return void
     */
    protected function save_params_to_type_log_turndown_focus($action_log_id, $params) {
        try {
            // turndown_focus has one set of secondary parameters for each die which was turned down
            $query = 'INSERT INTO game_action_log_type_turndown_focus_die ' .
                     '(action_log_id, recipe, orig_value, turndown_value) ' .
                     'VALUES ' .
                     '(:action_log_id, :recipe, :orig_value, :turndown_value)';
            foreach ($params['turndownDice'] as $die) {
                $parameters = array(
                    ':action_log_id'  => $action_log_id,
                    ':recipe'         => $die['recipe'],
                    ':orig_value'     => $die['origValue'],
                    ':turndown_value' => $die['turndownValue'],
                );
                self::$db->update($query, $parameters);
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_params_to_type_log_turndown_focus: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while saving log entries');
            return NULL;
        }
    }

    /**
     * Load the parameters for a single game action log message of type reroll_chance
     *
     * @param int $action_log_id
     * @return array
     */
    protected function load_params_from_type_log_reroll_chance($action_log_id) {
        try {
            $query = 'SELECT orig_recipe,orig_value,reroll_recipe,reroll_value,gained_initiative ' .
                     'FROM game_action_log_type_reroll_chance ' .
                     'WHERE action_log_id=:action_log_id';
            $parameters = array(':action_log_id' => $action_log_id);
            $columnReturnTypes = array(
                'orig_recipe' => 'str',
                'orig_value' => 'int',
                'reroll_recipe' => 'str',
                'reroll_value' => 'int',
                'gained_initiative' => 'bool',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
            $row = $rows[0];
            return array(
                'origRecipe' => $row['orig_recipe'],
                'origValue' => $row['orig_value'],
                'rerollRecipe' => $row['reroll_recipe'],
                'rerollValue' => $row['reroll_value'],
                'gainedInitiative' => $row['gained_initiative'],
            );
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_params_from_type_log_reroll_chance: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading log entries');
            return NULL;
        }
    }

    /**
     * Save the parameters for a single game action log message of type reroll_chance
     *
     * @param int $action_log_id
     * @param array $params
     * @return void
     */
    protected function save_params_to_type_log_reroll_chance($action_log_id, $params) {
        try {
            $query = 'INSERT INTO game_action_log_type_reroll_chance ' .
                     '(action_log_id, orig_recipe, orig_value, reroll_recipe, reroll_value, gained_initiative) ' .
                     'VALUES ' .
                     '(:action_log_id, :orig_recipe, :orig_value, :reroll_recipe, :reroll_value, :gained_initiative)';
            $parameters = array(
                ':action_log_id' => $action_log_id,
                ':orig_recipe' => $params['origRecipe'],
                ':orig_value' => $params['origValue'],
                ':reroll_recipe' => $params['rerollRecipe'],
                ':reroll_value' => $params['rerollValue'],
                ':gained_initiative' => $params['gainedInitiative'],
            );
            self::$db->update($query, $parameters);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_params_to_type_log_reroll_chance: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while saving log entries');
            return NULL;
        }
    }

    /**
     * Load the parameters for a single game action log message of type init_decline
     *
     * @return array
     */
    protected function load_params_from_type_log_init_decline() {
        // init_decline has no secondary parameters
        return array();
    }

    /**
     * Save the parameters for a single game action log message of type init_decline
     *
     * @return void
     */
    protected function save_params_to_type_log_init_decline() {
        // init_decline has no secondary parameters
        return;
    }

    /**
     * Load the parameters for a single game action log message of type decline_reserve
     *
     * @return array
     */
    protected function load_params_from_type_log_decline_reserve() {
        // decline_reserve has no secondary parameters
        return array();
    }

    /**
     * Save the parameters for a single game action log message of type decline_reserve
     *
     * @return void
     */
    protected function save_params_to_type_log_decline_reserve() {
        // decline_reserve has no secondary parameters
        return;
    }

    /**
     * Load the parameters for a single game action log message of type add_reserve
     *
     * @param int $action_log_id
     * @return array
     */
    protected function load_params_from_type_log_add_reserve($action_log_id) {
        try {
            $query = 'SELECT die_recipe FROM game_action_log_type_add_reserve ' .
                     'WHERE action_log_id=:action_log_id';
            $parameters = array(':action_log_id' => $action_log_id);
            $dieRecipe = self::$db->select_single_value($query, $parameters, 'str');
            return array(
                'dieRecipe' => $dieRecipe,
            );
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_params_from_type_log_add_reserve: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading log entries');
            return NULL;
        }
    }

    /**
     * Save the parameters for a single game action log message of type add_reserve
     *
     * @param int $action_log_id
     * @param array $params
     * @return void
     */
    protected function save_params_to_type_log_add_reserve($action_log_id, $params) {
        try {
            $query = 'INSERT INTO game_action_log_type_add_reserve ' .
                     '(action_log_id, die_recipe) ' .
                     'VALUES ' .
                     '(:action_log_id, :die_recipe)';
            $parameters = array(
                ':action_log_id' => $action_log_id,
                ':die_recipe' => $params['dieRecipe'],
            );
            self::$db->update($query, $parameters);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_params_to_type_log_add_reserve: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while saving log entries');
            return NULL;
        }
    }

    /**
     * Load the parameters for a single game action log message of type play_another_turn
     *
     * @param int $action_log_id
     * @return array
     */
    protected function load_params_from_type_log_play_another_turn($action_log_id) {
        try {
            $query = 'SELECT cause FROM game_action_log_type_play_another_turn ' .
                     'WHERE action_log_id=:action_log_id';
            $parameters = array(':action_log_id' => $action_log_id);
            $cause = self::$db->select_single_value($query, $parameters, 'str');
            return array(
                'cause' => $cause,
            );
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_params_from_type_log_play_another_turn: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading log entries');
            return NULL;
        }
    }

    /**
     * Save the parameters for a single game action log message of type play_another_turn
     *
     * @param int $action_log_id
     * @param array $params
     * @return void
     */
    protected function save_params_to_type_log_play_another_turn($action_log_id, $params) {
        try {
            $query = 'INSERT INTO game_action_log_type_play_another_turn ' .
                     '(action_log_id, cause) ' .
                     'VALUES ' .
                     '(:action_log_id, :cause)';
            $parameters = array(
                ':action_log_id' => $action_log_id,
                ':cause' => $params['cause'],
            );
            self::$db->update($query, $parameters);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_params_to_type_log_play_another_turn: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while saving log entries');
            return NULL;
        }
    }

    /**
     * Load the parameters for a single game action log message of type fire_cancel
     *
     * @return array
     */
    protected function load_params_from_type_log_fire_cancel() {
        // fire_cancel has no secondary parameters
        return array();
    }

    /**
     * Save the parameters for a single game action log message of type fire_cancel
     *
     * @return void
     */
    protected function save_params_to_type_log_fire_cancel() {
        // fire_cancel has no secondary parameters
        return;
    }

    /**
     * Load the parameters for a single game action log message of type choose_die_values
     *
     * @param int $action_log_id
     * @return array
     */
    protected function load_params_from_type_log_choose_die_values($action_log_id) {
        try {
            // choose_die_values has a base set of secondary parameters,
            // plus secondary parameters for each swing or option choice.
            // * $optionValues needs to be an array of arrays rather than a hash
            //   because a button may have multiple option dice with identical recipes.
            // * $swingValues doesn't need to be an array of arrays, but make it one
            //   anyway so the code will be consistent.

            // load the base params
            $query = 'SELECT round_number FROM game_action_log_type_choose_die_values ' .
                     'WHERE action_log_id=:action_log_id';
            $parameters = array(':action_log_id' => $action_log_id);
            $roundNumber = self::$db->select_single_value($query, $parameters, 'int');

            // load swing die settings
            $swingValues = array();
            $query = 'SELECT swing_type,swing_value FROM game_action_log_type_choose_die_values_swing ' .
                     'WHERE action_log_id=:action_log_id';
            $parameters = array(':action_log_id' => $action_log_id);
            $columnReturnTypes = array(
                'swing_type' => 'str',
                'swing_value' => 'int',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
            foreach ($rows as $row) {
                $swingValues[] = array(
                    'swingType'  => $row['swing_type'],
                    'swingValue' => $row['swing_value'],
                );
            }

            // load option die settings
            $optionValues = array();
            $query = 'SELECT recipe,option_value FROM game_action_log_type_choose_die_values_option ' .
                     'WHERE action_log_id=:action_log_id';
            $parameters = array(':action_log_id' => $action_log_id);
            $columnReturnTypes = array(
                'recipe' => 'str',
                'option_value' => 'int',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
            foreach ($rows as $row) {
                $optionValues[] = array(
                    'recipe'      => $row['recipe'],
                    'optionValue' => $row['option_value'],
                );
            }

            return array(
                'roundNumber'  => $roundNumber,
                'swingValues'  => $swingValues,
                'optionValues' => $optionValues,
            );
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_params_from_type_log_choose_die_values: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading log entries');
            return NULL;
        }
    }

    /**
     * Save the parameters for a single game action log message of type choose_die_values
     *
     * @param int $action_log_id
     * @param array $params
     * @return void
     */
    protected function save_params_to_type_log_choose_die_values($action_log_id, $params) {
        try {
            // save the base params
            $query = 'INSERT INTO game_action_log_type_choose_die_values ' .
                     '(action_log_id, round_number) ' .
                     'VALUES ' .
                     '(:action_log_id, :round_number)';
            $parameters = array(
                ':action_log_id' => $action_log_id,
                ':round_number'  => $params['roundNumber'],
            );
            self::$db->update($query, $parameters);

            // choose_die_values has one set of secondary parameters for each swing type which was set
            $query = 'INSERT INTO game_action_log_type_choose_die_values_swing ' .
                     '(action_log_id, swing_type, swing_value) ' .
                     'VALUES ' .
                     '(:action_log_id, :swing_type, :swing_value)';
            foreach ($params['swingValues'] as $swing) {
                $parameters = array(
                    ':action_log_id'  => $action_log_id,
                    ':swing_type'     => $swing['swingType'],
                    ':swing_value'    => $swing['swingValue'],
                );
                self::$db->update($query, $parameters);
            }

            // choose_die_values has one set of secondary parameters for each option die which was set
            $query = 'INSERT INTO game_action_log_type_choose_die_values_option ' .
                     '(action_log_id, recipe, option_value) ' .
                     'VALUES ' .
                     '(:action_log_id, :recipe, :option_value)';
            foreach ($params['optionValues'] as $option) {
                $parameters = array(
                    ':action_log_id'  => $action_log_id,
                    ':recipe'         => $option['recipe'],
                    ':option_value'   => $option['optionValue'],
                );
                self::$db->update($query, $parameters);
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_params_to_type_log_choose_die_values: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while saving log entries');
            return NULL;
        }
    }

    /**
     * Load the parameters for a single game action log message of type determine_initiative
     *
     * @param int $action_log_id
     * @return array
     */
    protected function load_params_from_type_log_determine_initiative($action_log_id) {
        try {
            // determine_initiative has a base set of secondary parameters,
            // plus two other types of secondary parameters:
            // * information about slow buttons in the game
            // * dieData containing data about each die present at the time of the initiative decision

            // load the base params
            $query = 'SELECT winner_id,round_number,is_tie FROM game_action_log_type_determine_initiative ' .
                     'WHERE action_log_id=:action_log_id';
            $parameters = array(':action_log_id' => $action_log_id);
            $columnReturnTypes = array(
                'winner_id' => 'int',
                'round_number' => 'int',
                'is_tie' => 'bool',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
            $row = $rows[0];
            $initiativeWinnerId = $row['winner_id'];
            $roundNumber = $row['round_number'];
            $isInitiativeTie = $row['is_tie'];

            // load relevant player data
            $slowButtonPlayers = array();
            $query = 'SELECT player_id FROM game_action_log_type_determine_initiative_slow_button ' .
                     'WHERE action_log_id=:action_log_id';
            $columnReturnTypes = array(
                'player_id' => 'int',
            );
            $parameters = array(':action_log_id' => $action_log_id);
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
            foreach ($rows as $row) {
                $slowButtonPlayers[] = $row['player_id'];
            }

            // load relevant die data
            $initiativeDice = array();
            $query = 'SELECT player_id,recipe_status,recipe,included ' .
                     'FROM game_action_log_type_determine_initiative_die ' .
                     'WHERE action_log_id=:action_log_id';
            $parameters = array(':action_log_id' => $action_log_id);
            $columnReturnTypes = array(
                'player_id' => 'int',
                'recipe_status' => 'str',
                'recipe' => 'str',
                'included' => 'bool',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
            foreach ($rows as $row) {
                if (!(array_key_exists($row['player_id'], $initiativeDice))) {
                    $initiativeDice[$row['player_id']] = array();
                }
                $initiativeDice[$row['player_id']][] = array(
                    'recipeStatus' => $row['recipe_status'],
                    'recipe'       => $row['recipe'],
                    'included'     => $row['included'],
                );
            }

            return array(
                'initiativeWinnerId' => $initiativeWinnerId,
                'roundNumber'        => $roundNumber,
                'isInitiativeTie'    => $isInitiativeTie,
                'slowButtonPlayers'  => $slowButtonPlayers,
                'initiativeDice'     => $initiativeDice,
            );
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_params_from_type_log_determine_initiative: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading log entries');
            return NULL;
        }
    }

    /**
     * Save the parameters for a single game action log message of type determine_initiative
     *
     * @param int $action_log_id
     * @param array $params
     * @return void
     */
    protected function save_params_to_type_log_determine_initiative($action_log_id, $params) {
        try {
            // save the base params
            $query = 'INSERT INTO game_action_log_type_determine_initiative ' .
                     '(action_log_id, winner_id, round_number, is_tie) ' .
                     'VALUES ' .
                     '(:action_log_id, :winner_id, :round_number, :is_tie)';
            $parameters = array(
                ':action_log_id' => $action_log_id,
                ':winner_id'    => $params['initiativeWinnerId'],
                ':round_number' => $params['roundNumber'],
                ':is_tie'       => $params['isInitiativeTie'],
            );
            self::$db->update($query, $parameters);

            // determine_initiative has one set of secondary parameters for slow buttons
            $query = 'INSERT INTO game_action_log_type_determine_initiative_slow_button ' .
                     '(action_log_id, player_id) ' .
                     'VALUES ' .
                     '(:action_log_id, :player_id)';
            foreach ($params['slowButtonPlayers'] as $player_id) {
                $parameters = array(
                    ':action_log_id'  => $action_log_id,
                    ':player_id'      => $player_id,
                );
                self::$db->update($query, $parameters);
            }

            // determine_initiative has one set of secondary parameters for each die present at the time
            $query = 'INSERT INTO game_action_log_type_determine_initiative_die ' .
                     '(action_log_id, player_id, recipe_status, recipe, included) ' .
                     'VALUES ' .
                     '(:action_log_id, :player_id, :recipe_status, :recipe, :included)';
            foreach ($params['initiativeDice'] as $playerId => $playerDice) {
                foreach ($playerDice as $playerDie) {
                    $parameters = array(
                        ':action_log_id'  => $action_log_id,
                        ':player_id'      => $playerId,
                        ':recipe_status'  => $playerDie['recipeStatus'],
                        ':recipe'         => $playerDie['recipe'],
                        ':included'       => $playerDie['included'],
                    );
                    self::$db->update($query, $parameters);
                }
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_params_to_type_log_determine_initiative: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while saving log entries');
            return NULL;
        }
    }

    /**
     * Helper function which asks the database for the ID of the last inserted row
     *
     * @return int
     */
    protected function last_insert_id() {
        return self::$db->select_single_value('SELECT LAST_INSERT_ID()', array(), 'int');
    }

    /**
     * Save new game action log entries into the database
     *
     * @param BMGame $game
     * @return void
     */
    protected function log_game_actions(BMGame $game) {
        $query = 'INSERT INTO game_action_log ' .
                 '(game_id, game_state, action_type, acting_player, message) ' .
                 'VALUES ' .
                 '(:game_id, :game_state, :action_type, :acting_player, :message)';
        foreach ($game->actionLog as $gameAction) {
            $actionArgs = array(
                ':game_id'       => $game->gameId,
                ':game_state'    => $gameAction->gameState,
                ':action_type'   => $gameAction->actionType,
                ':acting_player' => $gameAction->actingPlayerId,
            );
            $typefunc = 'save_params_to_type_log_' . $gameAction->actionType;
            $useActionTypeFunc = method_exists($this, $typefunc);
            if ($useActionTypeFunc) {
                $actionArgs[':message'] = NULL;
            } else {
                $actionArgs[':message'] = json_encode($gameAction->params);
            }
            self::$db->update($query, $actionArgs);

            if ($useActionTypeFunc) {
                $log_id = $this->last_insert_id();
                $this->$typefunc($log_id, $gameAction->params);
            }
        }
        $game->empty_action_log();
    }

    /**
     * Create a status message based on new game action log entries
     *
     * @param BMGame $game
     * @return void
     */
    protected function load_message_from_game_actions(BMGame $game) {
        $message = '';
        $playerIdNames = $this->get_player_name_mapping($game);
        foreach ($game->actionLog as $gameAction) {
            $messagePart = $gameAction->friendly_message(
                $playerIdNames,
                $game->roundNumber,
                $game->gameState
            );

            if (!empty($messagePart)) {
                if ('.' == substr($messagePart, -1)) {
                    $message .= $messagePart . ' ';
                } else {
                    $message .= $messagePart . '. ';
                }
            }
        }

        if (!empty($message)) {
            $this->set_message($message);
        }
    }
}
