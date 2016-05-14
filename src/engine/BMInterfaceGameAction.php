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
                     'game_state,action_type,acting_player,message ' .
                     'FROM game_action_log ';
            $query .= $this->build_game_log_query_restrictions($game, FALSE, FALSE, $sqlParameters);

            $statement = self::$conn->prepare($query);
            $statement->execute($sqlParameters);
            $logEntries = array();
            $playerIdNames = $this->get_player_name_mapping($game);
            while ($row = $statement->fetch()) {
                $params = json_decode($row['message'], TRUE);
                if (!($params)) {
                    $params = $row['message'];
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
            $statement = self::$conn->prepare($query);
            $statement->execute(
                array(':game_id'     => $game->gameId,
                      ':game_state' => $gameAction->gameState,
                      ':action_type' => $gameAction->actionType,
                      ':acting_player' => $gameAction->actingPlayerId,
                      ':message'    => json_encode($gameAction->params))
            );
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
