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
            $query = 'SELECT UNIX_TIMESTAMP(action_time) AS action_timestamp, ' .
                     'game_state,action_type,type_log_id,acting_player,message ' .
                     'FROM game_action_log ';
            $query .= $this->build_game_log_query_restrictions($game, FALSE, FALSE, $sqlParameters);

            $statement = self::$conn->prepare($query);
            $statement->execute($sqlParameters);
            $logEntries = array();
            $playerIdNames = $this->get_player_name_mapping($game);
            while ($row = $statement->fetch()) {
                if ($row['type_log_id'] != NULL) {
                    $typefunc = 'load_params_from_type_log_' . $row['action_type'];
                    $params = $this->$typefunc($row['type_log_id']);
                } elseif (in_array($row['action_type'], array('decline_auxiliary'))) {
                    // This action_type does not store any parameters
                    $params = array();
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
     * @param int $row_id
     * @return array
     */
    protected function load_params_from_type_log_end_draw($row_id) {
        try {
            $query = 'SELECT round_number,round_score FROM game_action_log_type_end_draw ' .
                     'WHERE id=:row_id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':row_id' => $row_id));
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
     * @param array $params
     * @return void
     */
    protected function save_params_to_type_log_end_draw($params) {
        try {
            $query = 'INSERT INTO game_action_log_type_end_draw ' .
                     '(round_number, round_score) ' .
                     'VALUES ' .
                     '(:round_number, :round_score)';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':round_number' => $params['roundNumber'],
                ':round_score' => $params['roundScore']));
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_params_to_type_log_end_draw: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading log entries');
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
                 '(game_id, game_state, action_type, type_log_id, acting_player, message) ' .
                 'VALUES ' .
                 '(:game_id, :game_state, :action_type, :type_log_id, :acting_player, :message)';
        foreach ($game->actionLog as $gameAction) {
            $actionArgs = array(
                ':game_id'       => $game->gameId,
                ':game_state'    => $gameAction->gameState,
                ':action_type'   => $gameAction->actionType,
                ':acting_player' => $gameAction->actingPlayerId,
            );
            $typefunc = 'save_params_to_type_log_' . $gameAction->actionType;
            if (method_exists($this, $typefunc)) {
                $this->$typefunc($gameAction->params);
                $actionArgs[':type_log_id'] = $this->last_insert_id();
                $actionArgs[':message'] = NULL;
            } elseif (in_array($gameAction->actionType, array('decline_auxiliary'))) {
                // This action_type does not store any parameters
                $actionArgs[':type_log_id'] = NULL;
                $actionArgs[':message'] = NULL;
            } else {
                $actionArgs[':type_log_id'] = NULL;
                $actionArgs[':message'] = json_encode($gameAction->params);
            }
            $statement = self::$conn->prepare($query);
            $statement->execute($actionArgs);
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
