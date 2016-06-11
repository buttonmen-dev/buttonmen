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

            $statement = self::$conn->prepare($query);
            $statement->execute($sqlParameters);
            $logEntries = array();
            $playerIdNames = $this->get_player_name_mapping($game);
            while ($row = $statement->fetch()) {
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
                        'timestamp' => (int)$row['action_timestamp'],
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
     * Load the parameters for a single game action log message of type end_draw
     *
     * @param int $action_log_id
     * @return array
     */
    protected function load_params_from_type_log_end_draw($action_log_id) {
        try {
            $query = 'SELECT round_number,round_score FROM game_action_log_type_end_draw ' .
                     'WHERE action_log_id=:action_log_id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':action_log_id' => $action_log_id));
            $row = $statement->fetch();
            return array(
                'roundNumber' => (int)$row['round_number'],
                'roundScore' => (float)$row['round_score'],
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
            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':action_log_id' => $action_log_id,
                ':round_number' => $params['roundNumber'],
                ':round_score' => $params['roundScore']));
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
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':action_log_id' => $action_log_id));
            $row = $statement->fetch();
            return array(
                'roundNumber' => (int)$row['round_number'],
                'winningRoundScore' => (float)$row['winning_round_score'],
                'losingRoundScore' => (float)$row['losing_round_score'],
                'surrendered' => (bool)$row['surrendered'],
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
            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':action_log_id' => $action_log_id,
                ':round_number' => $params['roundNumber'],
                ':winning_round_score' => $params['winningRoundScore'],
                ':losing_round_score' => $params['losingRoundScore'],
                ':surrendered' => $params['surrendered']));
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
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':action_log_id' => $action_log_id));
            $row = $statement->fetch();
            return array(
                'roundNumber' => (int)$row['round_number'],
                'dieRecipe' => (string)$row['die_recipe'],
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
            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':action_log_id' => $action_log_id,
                ':round_number' => $params['roundNumber'],
                ':die_recipe' => $params['dieRecipe']));
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
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':action_log_id' => $action_log_id));
            $turndownDice = array();
            while ($row = $statement->fetch()) {
                $turndownDice[] = array(
                    'recipe'        => (string)$row['recipe'],
                    'origValue'     => (int)$row['orig_value'],
                    'turndownValue' => (int)$row['turndown_value'],
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
                $statement = self::$conn->prepare($query);
                $statement->execute(array(
                    ':action_log_id'  => $action_log_id,
                    ':recipe'         => $die['recipe'],
                    ':orig_value'     => $die['origValue'],
                    ':turndown_value' => $die['turndownValue']));
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
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':action_log_id' => $action_log_id));
            $row = $statement->fetch();
            return array(
                'origRecipe' => (string)$row['orig_recipe'],
                'origValue' => (int)$row['orig_value'],
                'rerollRecipe' => (string)$row['reroll_recipe'],
                'rerollValue' => (int)$row['reroll_value'],
                'gainedInitiative' => (bool)$row['gained_initiative'],
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
            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':action_log_id' => $action_log_id,
                ':orig_recipe' => $params['origRecipe'],
                ':orig_value' => $params['origValue'],
                ':reroll_recipe' => $params['rerollRecipe'],
                ':reroll_value' => $params['rerollValue'],
                ':gained_initiative' => $params['gainedInitiative'],
            ));
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
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':action_log_id' => $action_log_id));
            $row = $statement->fetch();
            return array(
                'dieRecipe' => (string)$row['die_recipe'],
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
            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':action_log_id' => $action_log_id,
                ':die_recipe' => $params['dieRecipe']));
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
     * Helper function which asks the database for the ID of the last inserted row
     *
     * @return int
     */
    protected function last_insert_id() {
        $statement = self::$conn->prepare('SELECT LAST_INSERT_ID()');
        $statement->execute();
        $fetchData = $statement->fetch();
        return (int)$fetchData[0];
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
            $statement = self::$conn->prepare($query);
            $statement->execute($actionArgs);

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
