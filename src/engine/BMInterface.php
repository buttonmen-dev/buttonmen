<?php

/**
 * BMInterface: interface between GUI and BMGame
 *
 * @author james
 *
 * @property-read string $message                Message intended for GUI
 * @property-read DateTime $timestamp            Timestamp of last game action
 *
 */
class BMInterface {
    // properties
    private $message;               // message intended for GUI
    private $timestamp;             // timestamp of last game action
    private static $conn = NULL;    // connection to database

    private $isTest;         // indicates if the interface is for testing



    // constructor
    public function __construct($isTest = FALSE) {
        if (!is_bool($isTest)) {
            throw new InvalidArgumentException('isTest must be boolean.');
        }

        $this->isTest = $isTest;

        if ($isTest) {
            if (file_exists('../test/src/database/mysql.test.inc.php')) {
                require_once '../test/src/database/mysql.test.inc.php';
            } else {
                require_once 'test/src/database/mysql.test.inc.php';
            }
        } else {
            require_once '../database/mysql.inc.php';
        }
        self::$conn = conn();
    }

    // methods

    public function get_player_info($playerId) {
        try {
            $query = 'SELECT *, ' .
                     'UNIX_TIMESTAMP(p.last_action_time) AS last_action_timestamp, ' .
                     'UNIX_TIMESTAMP(p.creation_time) AS creation_timestamp ' .
                     'FROM player p ' .
                     'WHERE id = :id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':id' => $playerId));
            $result = $statement->fetchAll();

            if (0 == count($result)) {
                return NULL;
            }
        } catch (Exception $e) {
            $errorData = $statement->errorInfo();
            $this->message = 'Player info get failed: ' . $errorData[2];
            return NULL;
        }

        $infoArray = $result[0];

        // set the values we want to actually return
        $playerInfoArray = array(
            'id' => (int)$infoArray['id'],
            'name_ingame' => $infoArray['name_ingame'],
            'name_irl' => $infoArray['name_irl'],
            'email' => $infoArray['email'],
            'status' => $infoArray['status'],
            'dob' => $infoArray['dob'],
            'autopass' => (bool)$infoArray['autopass'],
            'image_path' => $infoArray['image_path'],
            'comment' => $infoArray['comment'],
            'last_action_time' => (int)$infoArray['last_action_timestamp'],
            'creation_time' => (int)$infoArray['creation_timestamp'],
            'fanatic_button_id' => (int)$infoArray['fanatic_button_id'],
            'n_games_won' => (int)$infoArray['n_games_won'],
            'n_games_lost' => (int)$infoArray['n_games_lost'],
        );

        return $playerInfoArray;
    }

    public function set_player_info($playerId, array $infoArray) {
        $infoArray['autopass'] = (int)($infoArray['autopass']);
        foreach ($infoArray as $infoType => $info) {
            try {
                $query = 'UPDATE player '.
                         "SET $infoType = :info ".
                         'WHERE id = :player_id;';

                $statement = self::$conn->prepare($query);
                $statement->execute(array(':info' => $info,
                                          ':player_id' => $playerId));
                $this->message = "Player info updated successfully.";
                return array('playerId' => $playerId);
            } catch (Exception $e) {
                $this->message = 'Player info update failed: '.$e->getMessage();
            }
        }

    }

    public function create_game(
        array $playerIdArray,
        array $buttonNameArray,
        $maxWins = 3
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
            $this->message = 'Game create failed because a player has been selected more than once.';
            return NULL;
        }

        // validate all inputs
        foreach ($playerIdArray as $playerId) {
            if (!(is_null($playerId) || is_int($playerId))) {
                $this->message = 'Game create failed because player ID is not valid.';
                return NULL;
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
            $this->message = 'Game create failed because the maximum number of wins was invalid.';
            return NULL;
        }

        $buttonIdArray = array();
        foreach (array_keys($playerIdArray) as $position) {
            // get button ID
            $buttonName = $buttonNameArray[$position];
            if (!empty($buttonName)) {
                $query = 'SELECT id FROM button '.
                         'WHERE name = :button_name';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':button_name' => $buttonName));
                $fetchData = $statement->fetch();
                if (FALSE === $fetchData) {
                    $this->message = 'Game create failed because a button name was not valid.';
                    return NULL;
                }
                $buttonIdArray[] = $fetchData[0];
            } else {
                $buttonIdArray[] = NULL;
            }
        }

        try {
            // create basic game details
            $query = 'INSERT INTO game '.
                     '    (status_id, '.
                     '     n_players, '.
                     '     n_target_wins, '.
                     '     n_recent_passes, '.
                     '     creator_id) '.
                     'VALUES '.
                     '    ((SELECT id FROM game_status WHERE name = :status), '.
                     '     :n_players, '.
                     '     :n_target_wins, '.
                     '     :n_recent_passes, '.
                     '     :creator_id)';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':status'        => 'OPEN',
                                      ':n_players'     => count($playerIdArray),
                                      ':n_target_wins' => $maxWins,
                                      ':n_recent_passes' => 0,
                                      ':creator_id'    => $playerIdArray[0]));

            $statement = self::$conn->prepare('SELECT LAST_INSERT_ID()');
            $statement->execute();
            $fetchData = $statement->fetch();
            $gameId = (int)$fetchData[0];

            foreach ($playerIdArray as $position => $playerId) {
                // add info to game_player_map
                $query = 'INSERT INTO game_player_map '.
                         '(game_id, player_id, button_id, position) '.
                         'VALUES '.
                         '(:game_id, :player_id, :button_id, :position)';
                $statement = self::$conn->prepare($query);

                $statement->execute(array(':game_id'   => $gameId,
                                          ':player_id' => $playerId,
                                          ':button_id' => $buttonIdArray[$position],
                                          ':position'  => $position));
            }

            // update game state to latest possible
            $game = $this->load_game($gameId);
            if (!($game instanceof BMGame)) {
                throw new Exception(
                    "Could not load newly-created game $gameId"
                );
            }
            $this->save_game($game);

            $this->message = "Game $gameId created successfully.";
            return array('gameId' => $gameId);
        } catch (Exception $e) {
            // Failure might occur on DB insert or on the subsequent load
            $errorData = $statement->errorInfo();
            if ($errorData[2]) {
                $this->message = 'Game create failed: ' . $errorData[2];
            } else {
                $this->message = 'Game create failed: ' . $e->getMessage();
            }
            error_log(
                "Caught exception in BMInterface::create_game: " .
                $e->getMessage()
            );
            return NULL;
        }
    }

    public function load_api_game_data($playerId, $gameId, $logEntryLimit) {
        $game = $this->load_game($gameId, $logEntryLimit);
        if ($game) {
            $currentPlayerIdx = array_search($playerId, $game->playerIdArray);

            foreach ($game->playerIdArray as $gamePlayerId) {
                $playerNameArray[] = $this->get_player_name_from_id($gamePlayerId);
            }

            // load_game will decide if the logEntryLimit should be overridden
            // (e.g. if chat is private or for completed games)
            $logEntryLimit = $game->logEntryLimit;

            $data = array(
                'currentPlayerIdx' => $currentPlayerIdx,
                'gameData' => $game->getJsonData($playerId),
                'playerNameArray' => $playerNameArray,
                'timestamp' => $this->timestamp,
                'gameActionLog' => $this->load_game_action_log($game, $logEntryLimit),
                'gameChatLog' => $this->load_game_chat_log($game, $logEntryLimit),
            );
            $data['gameChatEditable'] = $this->find_editable_chat_timestamp(
                $game,
                $currentPlayerIdx,
                $playerNameArray,
                $data['gameChatLog'],
                $data['gameActionLog']
            );
            return $data;
        }
        return NULL;
    }

    public function load_game($gameId, $logEntryLimit = NULL) {
        try {
            // check that the gameId exists
            $query = 'SELECT g.*,'.
                     'UNIX_TIMESTAMP(g.last_action_time) AS last_action_timestamp, '.
                     's.name AS status_name,'.
                     'v.player_id, v.position, v.autopass,'.
                     'v.button_name, v.alt_recipe,'.
                     'v.n_rounds_won, v.n_rounds_lost, v.n_rounds_drawn,'.
                     'v.did_win_initiative,'.
                     'v.is_awaiting_action '.
                     'FROM game AS g '.
                     'LEFT JOIN game_status AS s '.
                     'ON s.id = g.status_id '.
                     'LEFT JOIN game_player_view AS v '.
                     'ON g.id = v.game_id '.
                     'WHERE game_id = :game_id '.
                     'ORDER BY game_id;';
            $statement1 = self::$conn->prepare($query);
            $statement1->execute(array(':game_id' => $gameId));

            while ($row = $statement1->fetch()) {
                // load game attributes
                if (!isset($game)) {
                    $game = new BMGame;
                    $game->gameId    = $gameId;
                    $game->gameState = $row['game_state'];
                    $game->maxWins   = $row['n_target_wins'];
                    $game->turnNumberInRound = $row['turn_number_in_round'];
                    $game->nRecentPasses = $row['n_recent_passes'];
                    $this->timestamp = (int)$row['last_action_timestamp'];

                     // initialise all temporary arrays
                    $nPlayers = $row['n_players'];
                    $playerIdArray = array_fill(0, $nPlayers, NULL);
                    $gameScoreArrayArray = array_fill(0, $nPlayers, array(0, 0, 0));
                    $buttonArray = array_fill(0, $nPlayers, NULL);
                    $waitingOnActionArray = array_fill(0, $nPlayers, FALSE);
                    $autopassArray = array_fill(0, $nPlayers, FALSE);
                }

                $pos = $row['position'];
                if (isset($pos)) {
                    $playerIdArray[$pos] = $row['player_id'];
                    $autopassArray[$pos] = (bool)$row['autopass'];
                }

                if (1 == $row['did_win_initiative']) {
                    $game->playerWithInitiativeIdx = $pos;
                }

                $gameScoreArrayArray[$pos] = array($row['n_rounds_won'],
                                                   $row['n_rounds_lost'],
                                                   $row['n_rounds_drawn']);

                if ($game->gameState == BMGameState::END_GAME) {
                    $game->logEntryLimit = NULL;
                } else {
                    $game->logEntryLimit = $logEntryLimit;
                }

                // load button attributes
                if (isset($row['button_name'])) {
                    if (isset($row['alt_recipe'])) {
                        $recipe = $row['alt_recipe'];
                    } else {
                        $recipe = $this->get_button_recipe_from_name($row['button_name']);
                    }
                    if (isset($recipe)) {
                        $button = new BMButton;
                        $button->load($recipe, $row['button_name']);
                        if (isset($row['alt_recipe'])) {
                            $button->hasAlteredRecipe = TRUE;
                        }
                        $buttonArray[$pos] = $button;
                    } else {
                        throw new InvalidArgumentException('Invalid button name.');
                    }
                }

                // load player attributes
                switch ($row['is_awaiting_action']) {
                    case 1:
                        $waitingOnActionArray[$pos] = TRUE;
                        break;
                    case 0:
                        $waitingOnActionArray[$pos] = FALSE;
                        break;
                }

                if (isset($row['current_player_id']) &&
                    isset($row['player_id']) &&
                    ($row['current_player_id'] === $row['player_id'])) {
                    $game->activePlayerIdx = $pos;
                }

                if ($row['did_win_initiative']) {
                    $game->playerWithInitiativeIdx = $pos;
                }
            }

            // check whether the game exists
            if (!isset($game)) {
                $this->message = "Game $gameId does not exist.";
                return FALSE;
            }

            // fill up the game object with the database data
            $game->playerIdArray = $playerIdArray;
            $game->gameScoreArrayArray = $gameScoreArrayArray;
            $game->buttonArray = $buttonArray;
            $game->waitingOnActionArray = $waitingOnActionArray;
            $game->autopassArray = $autopassArray;

            // add swing values from last round
            $game->prevSwingValueArrayArray = array_fill(0, $game->nPlayers, array());
            $query = 'SELECT * '.
                     'FROM game_swing_map '.
                     'WHERE game_id = :game_id '.
                     'AND is_expired = :is_expired';
            $statement2 = self::$conn->prepare($query);
            $statement2->execute(array(':game_id' => $gameId,
                                       ':is_expired' => 1));
            while ($row = $statement2->fetch()) {
                $playerIdx = array_search($row['player_id'], $game->playerIdArray);
                $game->prevSwingValueArrayArray[$playerIdx][$row['swing_type']] = $row['swing_value'];
            }

            // add swing values
            $game->swingValueArrayArray = array_fill(0, $game->nPlayers, array());
            $query = 'SELECT * '.
                     'FROM game_swing_map '.
                     'WHERE game_id = :game_id '.
                     'AND is_expired = :is_expired';
            $statement2 = self::$conn->prepare($query);
            $statement2->execute(array(':game_id' => $gameId,
                                       ':is_expired' => 0));
            while ($row = $statement2->fetch()) {
                $playerIdx = array_search($row['player_id'], $game->playerIdArray);
                $game->swingValueArrayArray[$playerIdx][$row['swing_type']] = $row['swing_value'];
            }

            // add option values from last round
            $game->prevOptValueArrayArray = array_fill(0, $game->nPlayers, array());
            $query = 'SELECT * '.
                     'FROM game_option_map '.
                     'WHERE game_id = :game_id '.
                     'AND is_expired = :is_expired';
            $statement2 = self::$conn->prepare($query);
            $statement2->execute(array(':game_id' => $gameId,
                                       ':is_expired' => 1));
            while ($row = $statement2->fetch()) {
                $playerIdx = array_search($row['player_id'], $game->playerIdArray);
                $game->prevOptValueArrayArray[$playerIdx][$row['die_idx']] = $row['option_value'];
            }

            // add option values
            $game->optValueArrayArray = array_fill(0, $game->nPlayers, array());
            $query = 'SELECT * '.
                     'FROM game_option_map '.
                     'WHERE game_id = :game_id '.
                     'AND is_expired = :is_expired';
            $statement2 = self::$conn->prepare($query);
            $statement2->execute(array(':game_id' => $gameId,
                                       ':is_expired' => 0));
            while ($row = $statement2->fetch()) {
                $playerIdx = array_search($row['player_id'], $game->playerIdArray);
                $game->optValueArrayArray[$playerIdx][$row['die_idx']] = $row['option_value'];
            }

            // add die attributes
            $query = 'SELECT d.*,'.
                     '       s.name AS status '.
                     'FROM die AS d '.
                     'LEFT JOIN die_status AS s '.
                     'ON d.status_id = s.id '.
                     'WHERE game_id = :game_id '.
                     'ORDER BY id;';

            $statement3 = self::$conn->prepare($query);
            $statement3->execute(array(':game_id' => $gameId));

            $activeDieArrayArray = array_fill(0, count($playerIdArray), array());
            $captDieArrayArray = array_fill(0, count($playerIdArray), array());

            while ($row = $statement3->fetch()) {
                $playerIdx = array_search($row['owner_id'], $game->playerIdArray);

                $die = BMDie::create_from_recipe($row['recipe']);
                $die->playerIdx = $playerIdx;
                if (isset($row['value'])) {
                    $die->value = (int)$row['value'];
                }
                $originalPlayerIdx = array_search(
                    $row['original_owner_id'],
                    $game->playerIdArray
                );
                $die->originalPlayerIdx = $originalPlayerIdx;
                $die->ownerObject = $game;

                if (isset($die->swingType)) {
                    $game->request_swing_values($die, $die->swingType, $originalPlayerIdx);
                    $die->set_swingValue($game->swingValueArrayArray[$originalPlayerIdx]);

                    if (isset($row['actual_max'])) {
                        $die->max = $row['actual_max'];
                    }
                }

                if ($die instanceof BMDieTwin &&
                    (($die->dice[0] instanceof BMDieSwing) ||
                     ($die->dice[1] instanceof BMDieSwing))) {

                    foreach ($die->dice as $subdie) {
                        if ($subdie instanceof BMDieSwing) {
                            $swingType = $subdie->swingType;
                            $subdie->set_swingValue($game->swingValueArrayArray[$originalPlayerIdx]);

                            if (isset($row['actual_max'])) {
                                $subdie->max = (int)($row['actual_max']/2);
                            }
                        }
                    }

                    $game->request_swing_values($die, $swingType, $originalPlayerIdx);
                }

                if ($die instanceof BMDieOption) {
                    if (isset($row['actual_max'])) {
                        $die->max = $row['actual_max'];
                        $die->needsOptionValue = FALSE;
                    } else {
                        $die->needsOptionValue = TRUE;
                    }
                }

                if (!is_null($row['flags'])) {
                    $die->load_flags_from_string($row['flags']);
                }

                switch ($row['status']) {
                    case 'NORMAL':
                        $activeDieArrayArray[$playerIdx][$row['position']] = $die;
                        break;
                    case 'SELECTED':
                        $die->selected = TRUE;
                        $activeDieArrayArray[$playerIdx][$row['position']] = $die;
                        break;
                    case 'DISABLED':
                        $die->disabled = TRUE;
                        $activeDieArrayArray[$playerIdx][$row['position']] = $die;
                        break;
                    case 'DIZZY':
                        $die->dizzy = TRUE;
                        $activeDieArrayArray[$playerIdx][$row['position']] = $die;
                        break;
                    case 'CAPTURED':
                        $die->captured = TRUE;
                        $captDieArrayArray[$playerIdx][$row['position']] = $die;
                        break;
                }
            }

            $game->activeDieArrayArray = $activeDieArrayArray;
            $game->capturedDieArrayArray = $captDieArrayArray;

            // recreate $game->optRequestArrayArray
            foreach ($game->activeDieArrayArray as $activeDieArray) {
                foreach ($activeDieArray as $activeDie) {
                    if ($activeDie instanceof BMDieOption) {
                        $game->request_option_values(
                            $activeDie,
                            $activeDie->optionValueArray,
                            $activeDie->playerIdx
                        );
                    }
                }
            }

            if (!isset($game->swingRequestArrayArray)) {
                $game->swingValueArrayArray = NULL;
            }

            if (!isset($game->optRequestArrayArray)) {
                $game->optValueArrayArray = NULL;
            }

            $this->message = $this->message."Loaded data for game $gameId.";

            return $game;
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::load_game: " .
                $e->getMessage()
            );
            $this->message = "Game load failed: $e";
            return NULL;
        }
    }

    public function save_game(BMGame $game) {
        // force game to proceed to the latest possible before saving
        $game->proceed_to_next_user_action();

        try {
            if (is_null($game->activePlayerIdx)) {
                $currentPlayerId = NULL;
            } else {
                $currentPlayerId = $game->playerIdArray[$game->activePlayerIdx];
            }

            if (BMGameState::END_GAME == $game->gameState) {
                $status = 'COMPLETE';
            } elseif (in_array(NULL, $game->playerIdArray) ||
                      in_array(NULL, $game->buttonArray)) {
                $status = 'OPEN';
            } else {
                $status = 'ACTIVE';
            }

            // game
            $query = 'UPDATE game '.
                     'SET last_action_time = NOW(),'.
                     '    status_id = '.
                     '        (SELECT id FROM game_status WHERE name = :status),'.
                     '    game_state = :game_state,'.
                     '    round_number = :round_number,'.
                     '    turn_number_in_round = :turn_number_in_round,'.
            //:n_recent_draws
                     '    n_recent_passes = :n_recent_passes,'.
                     '    current_player_id = :current_player_id '.
            //:last_winner_id
            //:tournament_id
            //:description
            //:chat
                     'WHERE id = :game_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':status' => $status,
                                      ':game_state' => $game->gameState,
                                      ':round_number' => $game->roundNumber,
                                      ':turn_number_in_round' => $game->turnNumberInRound,
                                      ':n_recent_passes' => $game->nRecentPasses,
                                      ':current_player_id' => $currentPlayerId,
                                      ':game_id' => $game->gameId));

            // button recipes if altered
            if (isset($game->buttonArray)) {
                foreach ($game->buttonArray as $playerIdx => $button) {
                    if (($button instanceof BMButton) &&
                        ($button->hasAlteredRecipe)) {
                        $query = 'UPDATE game_player_map '.
                                 'SET alt_recipe = :alt_recipe '.
                                 'WHERE game_id = :game_id '.
                                 'AND player_id = :player_id;';
                        $statement = self::$conn->prepare($query);
                        $statement->execute(array(':alt_recipe' => $button->recipe,
                                                  ':game_id' => $game->gameId,
                                                  ':player_id' => $game->playerIdArray[$playerIdx]));
                    }
                }
            }

            // set round scores
            if (isset($game->gameScoreArrayArray)) {
                foreach ($game->playerIdArray as $playerIdx => $playerId) {
                    $query = 'UPDATE game_player_map '.
                             'SET n_rounds_won = :n_rounds_won,'.
                             '    n_rounds_lost = :n_rounds_lost,'.
                             '    n_rounds_drawn = :n_rounds_drawn '.
                             'WHERE game_id = :game_id '.
                             'AND player_id = :player_id;';
                    $statement = self::$conn->prepare($query);
                    $statement->execute(array(':n_rounds_won' => $game->gameScoreArrayArray[$playerIdx]['W'],
                                              ':n_rounds_lost' => $game->gameScoreArrayArray[$playerIdx]['L'],
                                              ':n_rounds_drawn' => $game->gameScoreArrayArray[$playerIdx]['D'],
                                              ':game_id' => $game->gameId,
                                              ':player_id' => $playerId));
                }
            }

            // clear swing values
            $query = 'DELETE FROM game_swing_map '.
                     'WHERE game_id = :game_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':game_id' => $game->gameId));

            // clear option values
            $query = 'DELETE FROM game_option_map '.
                     'WHERE game_id = :game_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':game_id' => $game->gameId));

            // store swing values from previous round
            if (isset($game->prevSwingValueArrayArray)) {
                foreach ($game->playerIdArray as $playerIdx => $playerId) {
                    if (!array_key_exists($playerIdx, $game->prevSwingValueArrayArray)) {
                        continue;
                    }
                    $swingValueArray = $game->prevSwingValueArrayArray[$playerIdx];
                    if (!empty($swingValueArray)) {
                        foreach ($swingValueArray as $swingType => $swingValue) {
                            $query = 'INSERT INTO game_swing_map '.
                                     '(game_id, player_id, swing_type, swing_value, is_expired) '.
                                     'VALUES '.
                                     '(:game_id, :player_id, :swing_type, :swing_value, :is_expired)';
                            $statement = self::$conn->prepare($query);
                            $statement->execute(array(':game_id'     => $game->gameId,
                                                      ':player_id'   => $playerId,
                                                      ':swing_type'  => $swingType,
                                                      ':swing_value' => $swingValue,
                                                      ':is_expired'  => TRUE));
                        }
                    }
                }
            }

            // store swing values
            if (isset($game->swingValueArrayArray)) {
                foreach ($game->playerIdArray as $playerIdx => $playerId) {
                    if (!array_key_exists($playerIdx, $game->swingValueArrayArray)) {
                        continue;
                    }
                    $swingValueArray = $game->swingValueArrayArray[$playerIdx];
                    if (!empty($swingValueArray)) {
                        foreach ($swingValueArray as $swingType => $swingValue) {
                            $query = 'INSERT INTO game_swing_map '.
                                     '(game_id, player_id, swing_type, swing_value, is_expired) '.
                                     'VALUES '.
                                     '(:game_id, :player_id, :swing_type, :swing_value, :is_expired)';
                            $statement = self::$conn->prepare($query);
                            $statement->execute(array(':game_id'     => $game->gameId,
                                                      ':player_id'   => $playerId,
                                                      ':swing_type'  => $swingType,
                                                      ':swing_value' => $swingValue,
                                                      ':is_expired'  => FALSE));
                        }
                    }
                }
            }

            // store option values from previous round
            if (isset($game->prevOptValueArrayArray)) {
                foreach ($game->playerIdArray as $playerIdx => $playerId) {
                    if (!array_key_exists($playerIdx, $game->prevOptValueArrayArray)) {
                        continue;
                    }
                    $optValueArray = $game->prevOptValueArrayArray[$playerIdx];
                    if (isset($optValueArray)) {
                        foreach ($optValueArray as $dieIdx => $optionValue) {
                            $query = 'INSERT INTO game_option_map '.
                                     '(game_id, player_id, die_idx, option_value, is_expired) '.
                                     'VALUES '.
                                     '(:game_id, :player_id, :die_idx, :option_value, :is_expired)';
                            $statement = self::$conn->prepare($query);
                            $statement->execute(array(':game_id'   => $game->gameId,
                                                      ':player_id' => $playerId,
                                                      ':die_idx'   => $dieIdx,
                                                      ':option_value' => $optionValue,
                                                      ':is_expired' => TRUE));
                        }
                    }
                }
            }

            // store option values
            if (isset($game->optValueArrayArray)) {
                foreach ($game->playerIdArray as $playerIdx => $playerId) {
                    if (!array_key_exists($playerIdx, $game->optValueArrayArray)) {
                        continue;
                    }
                    $optValueArray = $game->optValueArrayArray[$playerIdx];
                    if (isset($optValueArray)) {
                        foreach ($optValueArray as $dieIdx => $optionValue) {
                            $query = 'INSERT INTO game_option_map '.
                                     '(game_id, player_id, die_idx, option_value, is_expired) '.
                                     'VALUES '.
                                     '(:game_id, :player_id, :die_idx, :option_value, :is_expired)';
                            $statement = self::$conn->prepare($query);
                            $statement->execute(array(':game_id'   => $game->gameId,
                                                      ':player_id' => $playerId,
                                                      ':die_idx'   => $dieIdx,
                                                      ':option_value' => $optionValue,
                                                      ':is_expired' => FALSE));
                        }
                    }
                }
            }

            // set player that won initiative
            if (isset($game->playerWithInitiativeIdx)) {
                // set all players to not having initiative
                $query = 'UPDATE game_player_map '.
                         'SET did_win_initiative = 0 '.
                         'WHERE game_id = :game_id;';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':game_id' => $game->gameId));

                // set player that won initiative
                $query = 'UPDATE game_player_map '.
                         'SET did_win_initiative = 1 '.
                         'WHERE game_id = :game_id '.
                         'AND player_id = :player_id;';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':game_id' => $game->gameId,
                                          ':player_id' => $game->playerIdArray[$game->playerWithInitiativeIdx]));
            }


            // set players awaiting action
            foreach ($game->waitingOnActionArray as $playerIdx => $waitingOnAction) {
                $query = 'UPDATE game_player_map '.
                         'SET is_awaiting_action = :is_awaiting_action '.
                         'WHERE game_id = :game_id '.
                         'AND position = :position;';
                $statement = self::$conn->prepare($query);
                if ($waitingOnAction) {
                    $is_awaiting_action = 1;
                } else {
                    $is_awaiting_action = 0;
                }
                $statement->execute(array(':is_awaiting_action' => $is_awaiting_action,
                                          ':game_id' => $game->gameId,
                                          ':position' => $playerIdx));
            }

            // set existing dice to have a status of DELETED and get die ids
            //
            // note that the logic is written this way to make debugging easier
            // in case something fails during the addition of dice
            $query = 'UPDATE die '.
                     'SET status_id = '.
                     '    (SELECT id FROM die_status WHERE name = "DELETED") '.
                     'WHERE game_id = :game_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':game_id' => $game->gameId));

            // add active dice to table 'die'
            if (isset($game->activeDieArrayArray)) {
                foreach ($game->activeDieArrayArray as $playerIdx => $activeDieArray) {
                    foreach ($activeDieArray as $dieIdx => $activeDie) {
                        // james: set status, this is currently INCOMPLETE
                        $status = 'NORMAL';
                        if ($activeDie->selected) {
                            $status = 'SELECTED';
                        } elseif ($activeDie->disabled) {
                            $status = 'DISABLED';
                        } elseif ($activeDie->dizzy) {
                            $status = 'DIZZY';
                        }

                        $this->db_insert_die($game, $playerIdx, $activeDie, $status, $dieIdx);
                    }
                }
            }

            // add captured dice to table 'die'
            if (isset($game->capturedDieArrayArray)) {
                foreach ($game->capturedDieArrayArray as $playerIdx => $activeDieArray) {
                    foreach ($activeDieArray as $dieIdx => $activeDie) {
                        // james: set status, this is currently INCOMPLETE
                        $status = 'CAPTURED';

                        $this->db_insert_die($game, $playerIdx, $activeDie, $status, $dieIdx);
                    }
                }
            }

            // delete dice with a status of "DELETED" for this game
            $query = 'DELETE FROM die '.
                     'WHERE status_id = '.
                     '    (SELECT id FROM die_status WHERE name = "DELETED") '.
                     'AND game_id = :game_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':game_id' => $game->gameId));

            // If any game action entries were generated, load them
            // into the message so the calling player can see them,
            // then save them to the historical log
            if (count($game->actionLog) > 0) {
                $this->load_message_from_game_actions($game);
                $this->log_game_actions($game);
            }
            // If the player sent a chat message, insert it now
            // then save them to the historical log
            if ($game->chat['chat']) {
                $this->log_game_chat($game);
            }

        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::save_game: " .
                $e->getMessage()
            );
            $this->message = "Game save failed: $e";
        }
    }

    // Actually insert a die into the database - all error checking to be done by caller
    protected function db_insert_die($game, $playerIdx, $activeDie, $status, $dieIdx) {
        $gameId = $game->gameId;
        $playerId = $game->playerIdArray[$playerIdx];

        $query = 'INSERT INTO die '.
                 '    (owner_id, '.
                 '     original_owner_id, '.
                 '     game_id, '.
                 '     status_id, '.
                 '     recipe, '.
                 '     actual_max, '.
                 '     position, '.
                 '     value, '.
                 '     flags)'.
                 'VALUES '.
                 '    (:owner_id, '.
                 '     :original_owner_id, '.
                 '     :game_id, '.
                 '     (SELECT id FROM die_status WHERE name = :status), '.
                 '     :recipe, '.
                 '     :actual_max, '.
                 '     :position, '.
                 '     :value, '.
                 '     :flags);';
        $statement = self::$conn->prepare($query);

        $flags = $activeDie->flags_as_string();
        if (empty($flags)) {
            $flags = NULL;
        }

        $actualMax = NULL;

        if ($activeDie->has_skill('Mood') ||
            $activeDie->has_skill('Mad') ||
            ($activeDie instanceof BMDieOption)) {
            $actualMax = $activeDie->max;
        }

        $statement->execute(array(':owner_id' => $playerId,
                                  ':original_owner_id' => $game->playerIdArray[$activeDie->originalPlayerIdx],
                                  ':game_id' => $gameId,
                                  ':status' => $status,
                                  ':recipe' => $activeDie->recipe,
                                  ':actual_max' => $actualMax,
                                  ':position' => $dieIdx,
                                  ':value' => $activeDie->value,
                                  ':flags' => $flags));
    }

    // Get all player games (either active or inactive) from the database
    // No error checking - caller must do it
    protected function get_all_games($playerId, $getActiveGames) {

        // the following SQL logic assumes that there are only two players per game
        $query = 'SELECT v1.game_id,'.
                 'v1.player_id AS opponent_id,'.
                 'v1.player_name AS opponent_name,'.
                 'v2.button_name AS my_button_name,'.
                 'v1.button_name AS opponent_button_name,'.
                 'v2.n_rounds_won AS n_wins,'.
                 'v2.n_rounds_drawn AS n_draws,'.
                 'v1.n_rounds_won AS n_losses,'.
                 'v1.n_target_wins,'.
                 'v2.is_awaiting_action,'.
                 'g.game_state,'.
                 's.name AS status, '.
                 'UNIX_TIMESTAMP(g.last_action_time) AS last_action_timestamp '.
                 'FROM game_player_view AS v1 '.
                 'LEFT JOIN game_player_view AS v2 '.
                 'ON v1.game_id = v2.game_id '.
                 'LEFT JOIN game AS g '.
                 'ON g.id = v1.game_id '.
                 'LEFT JOIN game_status AS s '.
                 'ON g.status_id = s.id '.
                 'WHERE v2.player_id = :player_id '.
                 'AND v1.player_id != v2.player_id ';
        if ($getActiveGames) {
            $query .= 'AND s.name = "ACTIVE" ';
        } else {
            $query .= 'AND s.name = "COMPLETE" ';
        }
        $query .= 'ORDER BY g.last_action_time ASC;';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':player_id' => $playerId));

        // Initialize the arrays
        $gameIdArray = array();
        $opponentIdArray = array();
        $opponentNameArray = array();
        $myButtonNameArray = array();
        $oppButtonNameArray = array();
        $nWinsArray = array();
        $nDrawsArray = array();
        $nLossesArray = array();
        $nTargetWinsArray = array();
        $isToActArray = array();
        $gameStateArray = array();
        $statusArray = array();
        $inactivityArray = array();

        // Ensure that the inactivity time for all games is relative to the
        // same moment
        $now = strtotime('now');

        while ($row = $statement->fetch()) {
            $gameIdArray[]        = (int)$row['game_id'];
            $opponentIdArray[]    = (int)$row['opponent_id'];
            $opponentNameArray[]  = $row['opponent_name'];
            $myButtonNameArray[]  = $row['my_button_name'];
            $oppButtonNameArray[] = $row['opponent_button_name'];
            $nWinsArray[]         = (int)$row['n_wins'];
            $nDrawsArray[]        = (int)$row['n_draws'];
            $nLossesArray[]       = (int)$row['n_losses'];
            $nTargetWinsArray[]   = (int)$row['n_target_wins'];
            $isToActArray[]       = (int)$row['is_awaiting_action'];
            $gameStateArray[]     = BMGameState::as_string($row['game_state']);
            $statusArray[]        = $row['status'];
            $inactivityArray[]    =
                $this->get_friendly_time_span((int)$row['last_action_timestamp'], $now);
        }

        return array('gameIdArray'             => $gameIdArray,
                     'opponentIdArray'         => $opponentIdArray,
                     'opponentNameArray'       => $opponentNameArray,
                     'myButtonNameArray'       => $myButtonNameArray,
                     'opponentButtonNameArray' => $oppButtonNameArray,
                     'nWinsArray'              => $nWinsArray,
                     'nDrawsArray'             => $nDrawsArray,
                     'nLossesArray'            => $nLossesArray,
                     'nTargetWinsArray'        => $nTargetWinsArray,
                     'isAwaitingActionArray'   => $isToActArray,
                     'gameStateArray'          => $gameStateArray,
                     'statusArray'             => $statusArray,
                     'inactivityArray'         => $inactivityArray);
    }

    public function get_all_active_games($playerId) {
        try {
            $this->message = 'All game details retrieved successfully.';
            return $this->get_all_games($playerId, TRUE);
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_all_active_games: " .
                $e->getMessage()
            );
            $this->message = 'Game detail get failed.';
            return NULL;
        }
    }

    public function get_all_completed_games($playerId) {
        try {
            $this->message = 'All game details retrieved successfully.';
            return $this->get_all_games($playerId, FALSE);
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_all_active_games: " .
                $e->getMessage()
            );
            $this->message = 'Game detail get failed.';
            return NULL;
        }
    }

    public function get_all_open_games($currentPlayerId) {
        try {
            // Get all the colors the current player has set in his or her
            // preferences
            $playerColors = $this->load_player_colors($currentPlayerId);

            $query =
                'SELECT ' .
                    'g.id AS game_id, ' .
                    'v_challenger.player_id AS challenger_id, ' .
                    'v_challenger.player_name AS challenger_name, ' .
                    'v_challenger.button_name AS challenger_button, ' .
                    'v_victim.button_name AS victim_button, ' .
                    'g.n_target_wins AS target_wins ' .
                'FROM game AS g ' .
                    'INNER JOIN game_status AS s ON s.id = g.status_id ' .
                    // For the time being, I'm assuming there are only two
                    // players. If we later implement 3+ player games, this
                    // will need to be updated.
                    'INNER JOIN game_player_view AS v_challenger ' .
                        'ON v_challenger.game_id = g.id AND v_challenger.player_id IS NOT NULL ' .
                    'INNER JOIN game_player_view AS v_victim ' .
                        'ON v_victim.game_id = g.id AND v_victim.player_id IS NULL ' .
                'WHERE s.name = "OPEN"' .
                'ORDER BY g.id ASC;';

            $statement = self::$conn->prepare($query);
            $statement->execute();

            $games = array();

            while ($row = $statement->fetch()) {
                $gameColors = $this->determine_game_colors(
                    $currentPlayerId,
                    $playerColors,
                    -1, // There is no other player yet
                    (int)$row['challenger_id']
                );

                $games[] = array(
                    'gameId' => (int)$row['game_id'],
                    'challengerId' => (int)$row['challenger_id'],
                    'challengerName' => $row['challenger_name'],
                    'challengerButton' => $row['challenger_button'],
                    'challengerColor' => $gameColors['playerB'],
                    'victimButton' => $row['victim_button'],
                    'targetWins' => (int)$row['target_wins'],
                );
            }

            $this->message = 'Open games retrieved successfully.';
            return array('games' => $games);
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_all_open_games: " .
                $e->getMessage()
            );
            $this->message = 'Game detail get failed.';
            return NULL;
        }
    }

    public function get_next_pending_game($playerId, $skippedGames) {
        try {
            $parameters = array(':player_id' => $playerId);

            $query = 'SELECT gpm.game_id '.
                     'FROM game_player_map AS gpm '.
                        'LEFT JOIN game AS g ON g.id = gpm.game_id '.
                     'WHERE gpm.player_id = :player_id '.
                        'AND gpm.is_awaiting_action = 1 ';
            foreach ($skippedGames as $index => $skippedGameId) {
                $parameterName = ':skipped_game_id_' . $index;
                $query = $query . 'AND gpm.game_id <> ' . $parameterName . ' ';
                $parameters[$parameterName] = $skippedGameId;
            };
            $query = $query .
                     'ORDER BY g.last_action_time ASC '.
                     'LIMIT 1';

            $statement = self::$conn->prepare($query);
            $statement->execute($parameters);
            $result = $statement->fetch();
            if (!$result) {
                $this->message = 'Player has no pending games.';
                return array('gameId' => NULL);
            } else {
                $gameId = ((int)$result[0]);
                $this->message = 'Next game ID retrieved successfully.';
                return array('gameId' => $gameId);
            }
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_next_pending_game: " .
                $e->getMessage()
            );
            $this->message = 'Game ID get failed.';
            return NULL;
        }
    }

    public function get_all_button_names() {
        try {
            // if the site is production, don't report unimplemented buttons at all
            $site_type = $this->get_config('site_type');

            $statement = self::$conn->prepare('SELECT name, recipe, btn_special FROM button_view');
            $statement->execute();

            // Look for unimplemented skills in each button definition.
            // If we get an exception while checking, assume there's
            // an unimplemented skill
            while ($row = $statement->fetch()) {
                try {
                    $button = new BMButton();
                    $button->load($row['recipe'], $row['name']);

                    $standardName = preg_replace('/[^a-zA-Z0-9]/', '', $button->name);
                    if ((1 == $row['btn_special']) &&
                        !class_exists('BMBtnSkill'.$standardName)) {
                        $button->hasUnimplementedSkill = TRUE;
                    }

                    $hasUnimplSkill = $button->hasUnimplementedSkill;
                } catch (Exception $e) {
                    $hasUnimplSkill = TRUE;
                }

                if (($site_type != 'production') || (!($hasUnimplSkill))) {
                    $buttonNameArray[] = $row['name'];
                    $recipeArray[] = $row['recipe'];
                    $hasUnimplSkillArray[] = $hasUnimplSkill;
                }
            }
            $this->message = 'All button names retrieved successfully.';
            return array('buttonNameArray'            => $buttonNameArray,
                         'recipeArray'                => $recipeArray,
                         'hasUnimplementedSkillArray' => $hasUnimplSkillArray);
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_all_button_names: " .
                $e->getMessage()
            );
            $this->message = 'Button name get failed.';
            return NULL;
        }
    }

    public function get_button_recipe_from_name($name) {
        try {
            $query = 'SELECT recipe FROM button_view '.
                     'WHERE name = :name';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':name' => $name));

            $row = $statement->fetch();
            return($row['recipe']);
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_button_recipe_from_name: "
                . $e->getMessage()
            );
            $this->message = 'Button recipe get failed.';
        }
    }

    public function get_player_names_like($input = '') {
        try {
            $query = 'SELECT name_ingame,status FROM player '.
                     'WHERE name_ingame LIKE :input '.
                     'ORDER BY name_ingame';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':input' => $input.'%'));

            $nameArray = array();
            $statusArray = array();
            while ($row = $statement->fetch()) {
                $nameArray[] = $row['name_ingame'];
                $statusArray[] = $row['status'];
            }
            $this->message = 'Names retrieved successfully.';
            return array('nameArray' => $nameArray, 'statusArray' => $statusArray);
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_player_names_like: " .
                $e->getMessage()
            );
            $this->message = 'Player name get failed.';
            return NULL;
        }
    }

    public function get_player_id_from_name($name) {
        try {
            $query = 'SELECT id FROM player '.
                     'WHERE name_ingame = :input';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':input' => $name));
            $result = $statement->fetch();
            if (!$result) {
                $this->message = 'Player name does not exist.';
                return('');
            } else {
                $this->message = 'Player ID retrieved successfully.';
                return((int)$result[0]);
            }
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_player_id_from_name: " .
                $e->getMessage()
            );
            $this->message = 'Player ID get failed.';
        }
    }

    public function get_player_name_from_id($playerId) {
        try {
            if (is_null($playerId)) {
                return('');
            }

            $query = 'SELECT name_ingame FROM player '.
                     'WHERE id = :id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':id' => $playerId));
            $result = $statement->fetch();
            if (!$result) {
                $this->message = 'Player ID does not exist.';
                return('');
            } else {
                return($result[0]);
            }
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_player_name_from_id: " .
                $e->getMessage()
            );
            $this->message = 'Player name get failed.';
        }
    }

    public function get_player_name_mapping($game) {
        $idNameMapping = array();
        foreach ($game->playerIdArray as $playerId) {
            $idNameMapping[$playerId] = $this->get_player_name_from_id($playerId);
        }
        return $idNameMapping;
    }

    // Check whether a requested action still needs to be taken.
    // If the time stamp is not important, use the string 'ignore'
    // for $postedTimestamp.
    protected function is_action_current(
        BMGame $game,
        $expectedGameState,
        $postedTimestamp,
        $roundNumber,
        $currentPlayerId
    ) {
        $currentPlayerIdx = array_search($currentPlayerId, $game->playerIdArray);

        if (FALSE === $currentPlayerIdx) {
            $this->message = 'You are not a participant in this game';
            return FALSE;
        }

        if (FALSE === $game->waitingOnActionArray[$currentPlayerIdx]) {
            $this->message = 'You are not the active player';
            return FALSE;
        };

        $doesTimeStampAgree =
            ('ignore' === $postedTimestamp) ||
            ($postedTimestamp == $this->timestamp);
        $doesRoundNumberAgree =
            ('ignore' === $roundNumber) ||
            ($roundNumber == $game->roundNumber);
        $doesGameStateAgree = $expectedGameState == $game->gameState;

        $this->message = 'Game state is not current';
        return ($doesTimeStampAgree &&
                $doesRoundNumberAgree &&
                $doesGameStateAgree);
    }

    // Enter recent game actions into the action log
    // Note: it might be possible for this to be a protected function
    public function log_game_actions(BMGame $game) {
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

    public function load_game_action_log(BMGame $game, $logEntryLimit) {
        try {
            $query = 'SELECT UNIX_TIMESTAMP(action_time) AS action_timestamp, ' .
                     'game_state,action_type,acting_player,message ' .
                     'FROM game_action_log ' .
                     'WHERE game_id = :game_id ORDER BY id DESC';
            if (!is_null($logEntryLimit)) {
                $query = $query . ' LIMIT ' . $logEntryLimit;
            }

            $statement = self::$conn->prepare($query);
            $statement->execute(array(':game_id' => $game->gameId));
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
                        'message' => $message,
                    );
                }
            }
            return $logEntries;
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::load_game_action_log: " .
                $e->getMessage()
            );
            $this->message = 'Internal error while reading log entries';
            return NULL;
        }
    }

    // Create a status message based on recent game actions
    private function load_message_from_game_actions(BMGame $game) {
        $this->message = '';
        $playerIdNames = $this->get_player_name_mapping($game);
        foreach ($game->actionLog as $gameAction) {
            $this->message .= $gameAction->friendly_message(
                $playerIdNames,
                $game->roundNumber,
                $game->gameState
            ) . '. ';
        }
    }

    protected function sanitize_chat($message) {
        // if the string is too long, truncate it
        if (strlen($message) > 1020) {
            $message = substr($message, 0, 1020);
        }
        return $message;
    }

    protected function log_game_chat(BMGame $game) {
        $this->db_insert_chat(
            $game->chat['playerIdx'],
            $game->gameId,
            $game->chat['chat']
        );
    }

    // Insert a new chat message into the database
    protected function db_insert_chat($playerId, $gameId, $chat) {

        // We're going to display this in user browsers, so first clean up all HTML tags
        $mysqlchat = $this->sanitize_chat($chat);

        $query = 'INSERT INTO game_chat_log ' .
                 '(game_id, chatting_player, message) ' .
                 'VALUES ' .
                 '(:game_id, :chatting_player, :message)';
        $statement = self::$conn->prepare($query);
        $statement->execute(
            array(':game_id'         => $gameId,
                  ':chatting_player' => $playerId,
                  ':message'         => $mysqlchat)
        );
    }

    // Modify an existing chat message in the database
    protected function db_update_chat($playerId, $gameId, $editTimestamp, $chat) {
        $mysqlchat = $this->sanitize_chat($chat);
        $query = 'UPDATE game_chat_log ' .
                 'SET message = :message, chat_time = now() ' .
                 'WHERE game_id = :game_id ' .
                 'AND chatting_player = :player_id ' .
                 'AND UNIX_TIMESTAMP(chat_time) = :timestamp ' .
                 'ORDER BY id DESC ' .
                 'LIMIT 1';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':message' => $mysqlchat,
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

    public function load_game_chat_log(BMGame $game, $logEntryLimit) {
        try {
            $query = 'SELECT UNIX_TIMESTAMP(chat_time) AS chat_timestamp, ' .
                     'chatting_player,message ' .
                     'FROM game_chat_log ' .
                     'WHERE game_id = :game_id ORDER BY id DESC';
            if (!is_null($logEntryLimit)) {
                $query = $query . ' LIMIT ' . $logEntryLimit;
            }

            $statement = self::$conn->prepare($query);
            $statement->execute(array(':game_id' => $game->gameId));
            $chatEntries = array();
            while ($row = $statement->fetch()) {
                $chatEntries[] = array(
                    'timestamp' => (int)$row['chat_timestamp'],
                    'player' => $this->get_player_name_from_id($row['chatting_player']),
                    'message' => $row['message'],
                );
            }
            return $chatEntries;
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::load_game_chat_log: " .
                $e->getMessage()
            );
            $this->message = 'Internal error while reading chat entries';
            return NULL;
        }
    }

   // Can the active player edit the most recent chat entry in this game?
    protected function find_editable_chat_timestamp(
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

        // Only the most recent chat entry can be modified --- was
        // it made by the active player?
        if ((FALSE === $currentPlayerIdx) ||
            ($playerNameArray[$currentPlayerIdx] != $chatLogEntries[0]['player'])) {
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
        if (TRUE === $game->waitingOnActionArray[$currentPlayerIdx]) {
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

            foreach ($game->playerIdArray as $gamePlayerId) {
                $playerNameArray[] = $this->get_player_name_from_id($gamePlayerId);
            }
            $lastChatEntryList = $this->load_game_chat_log($game, 1);
            $lastActionEntryList = $this->load_game_action_log($game, 1);

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
                        $this->message = 'Updated previous game message';
                        return TRUE;
                    } else {
                        $this->db_delete_chat($playerId, $gameId, $editTimestamp);
                        $this->message = 'Deleted previous game message';
                        return TRUE;
                    }
                } else {
                    $this->message = 'You can\'t edit the requested chat message now';
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
                        $this->message = 'Added game message';
                        return TRUE;
                    } else {
                        $this->message = 'No game message specified';
                        return FALSE;
                    }
                } else {
                    $this->message = 'You can\'t add a new chat message now';
                    return FALSE;
                }
            }

        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::submit_chat: " .
                $e->getMessage()
            );
            $this->message = 'Internal error while updating game chat';
        }
    }

    public function join_open_game($currentPlayerId, $gameId) {
        try {
            $game = $this->load_game($gameId);

            // check that there are still unspecified players and
            // that the player is not already part of the game
            $emptyPlayerIdx = NULL;
            $isPlayerPartOfGame = FALSE;

            foreach ($game->playerIdArray as $playerIdx => $playerId) {
                if (is_null($playerId) && is_null($emptyPlayerIdx)) {
                    $emptyPlayerIdx = $playerIdx;
                } elseif ($currentPlayerId == $playerId) {
                    $isPlayerPartOfGame = TRUE;
                    break;
                }
            }

            if ($isPlayerPartOfGame) {
                $this->message = 'You are already playing in this game.';
                return FALSE;
            }

            if (is_null($emptyPlayerIdx)) {
                $this->message = 'No empty player slots in game '.$gameId.'.';
                return FALSE;
            }

            $query = 'UPDATE game_player_map SET player_id = :player_id '.
                     'WHERE game_id = :game_id '.
                     'AND position = :position';
            $statement = self::$conn->prepare($query);

            $statement->execute(array(':game_id'   => $gameId,
                                      ':player_id' => $currentPlayerId,
                                      ':position'  => $emptyPlayerIdx));

            $game = $this->load_game($gameId);
            $this->save_game($gameId);

            return TRUE;
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::join_open_game: ".
                $e->getMessage()
            );
            $this->message = 'Internal error while joining open game';
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
                $this->message = 'Player is not a participant in game.';
                return FALSE;
            }

            if (!is_null($game->buttonArray[$playerIdx])) {
                $this->message = 'Button has already been selected.';
                return FALSE;
            }

            $query = 'SELECT id FROM button '.
                     'WHERE name = :button_name';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':button_name' => $buttonName));
            $fetchData = $statement->fetch();
            if (FALSE === $fetchData) {
                $this->message = 'Button select failed because button name was not valid.';
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

            $game = $this->load_game($gameId);
            $this->save_game($gameId);

            return TRUE;

        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::select_button: ".
                $e->getMessage()
            );
            $this->message = 'Internal error while selecting button';
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
            // the swing values still need to be set
            if (!$this->is_action_current(
                $game,
                BMGameState::SPECIFY_DICE,
                'ignore',
                $roundNumber,
                $playerId
            )) {
                $this->message = 'Dice sizes no longer need to be set';
                return NULL;
            }

            // try to set swing values
            $swingRequestArray = $game->swingRequestArrayArray[$currentPlayerIdx];
            if (is_array($swingRequestArray)) {
                $swingRequested = array_keys($game->swingRequestArrayArray[$currentPlayerIdx]);
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

            if ($swingRequested != $swingSubmitted) {
                $this->message = 'Wrong swing values submitted: expected ' . implode(',', $swingRequested);
                return NULL;
            }

            $game->swingValueArrayArray[$currentPlayerIdx] = $swingValueArray;

            // try to set option values
            if (is_array($optionValueArray)) {
                foreach ($optionValueArray as $dieIdx => $optionValue) {
                    $game->optValueArrayArray[$currentPlayerIdx][$dieIdx] = $optionValue;
                }
            }

            $game->proceed_to_next_user_action();

            // check for successful swing value set
            if ((FALSE == $game->waitingOnActionArray[$currentPlayerIdx]) ||
                ($game->gameState > BMGameState::SPECIFY_DICE) ||
                ($game->roundNumber > $roundNumber)) {
                $optionLogArray = array();
                foreach ($optionValueArray as $dieIdx => $optionValue) {
                    $dieRecipe = $game->activeDieArrayArray[$currentPlayerIdx][$dieIdx]->recipe;
                    $optionLogArray[$dieRecipe] = $optionValue;
                }
                $game->log_action(
                    'choose_die_values',
                    $game->playerIdArray[$currentPlayerIdx],
                    array(
                        'roundNumber' => $game->roundNumber,
                        'swingValues' => $swingValueArray,
                        'optionValues' => $optionLogArray,
                    )
                );
                $this->save_game($game);
                $this->message = 'Successfully set die sizes';
                return TRUE;
            } else {
                if ($game->message) {
                    $this->message = $game->message;
                } else {
                    $this->message = 'Failed to set die sizes';
                }
                return NULL;
            }
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::submit_die_values: " .
                $e->getMessage()
            );
            $this->message = 'Internal error while setting die sizes';
        }
    }

    public function submit_swing_values(
        $playerId,
        $gameId,
        $roundNumber,
        $swingValueArray
    ) {
        try {
            $game = $this->load_game($gameId);
            $currentPlayerIdx = array_search($playerId, $game->playerIdArray);

            // check that the timestamp and the game state are correct, and that
            // the swing values still need to be set
            if (!$this->is_action_current(
                $game,
                BMGameState::SPECIFY_DICE,
                'ignore',
                $roundNumber,
                $playerId
            )) {
                $this->message = 'Swing dice no longer need to be set';
                return NULL;
            }

            // try to set swing values
            $swingRequestArray = $game->swingRequestArrayArray[$currentPlayerIdx];
            if (is_array($swingRequestArray)) {
                $swingRequested = array_keys($game->swingRequestArrayArray[$currentPlayerIdx]);
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

            if ($swingRequested != $swingSubmitted) {
                $this->message = 'Wrong swing values submitted: expected ' . implode(',', $swingRequested);
                return NULL;
            }

            $game->swingValueArrayArray[$currentPlayerIdx] = $swingValueArray;

            $game->proceed_to_next_user_action();

            // check for successful swing value set
            if ((FALSE == $game->waitingOnActionArray[$currentPlayerIdx]) ||
                ($game->gameState > BMGameState::SPECIFY_DICE) ||
                ($game->roundNumber > $roundNumber)) {
                $game->log_action(
                    'choose_swing',
                    $game->playerIdArray[$currentPlayerIdx],
                    array(
                        'roundNumber' => $game->roundNumber,
                        'swingValues' => $swingValueArray,
                    )
                );
                $this->save_game($game);
                $this->message = 'Successfully set swing values';
                return TRUE;
            } else {
                if ($game->message) {
                    $this->message = $game->message;
                } else {
                    $this->message = 'Failed to set swing values';
                }
                return NULL;
            }
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::submit_swing_values: " .
                $e->getMessage()
            );
            $this->message = 'Internal error while setting swing values';
        }
    }

    public function submit_option_values(
        $playerId,
        $gameId,
        $roundNumber,
        $optionValueArray
    ) {
        try {
            $game = $this->load_game($gameId);
            $currentPlayerIdx = array_search($playerId, $game->playerIdArray);

            // check that the timestamp and the game state are correct, and that
            // the option values still need to be set
            if (!$this->is_action_current(
                $game,
                BMGameState::SPECIFY_DICE,
                'ignore',
                $roundNumber,
                $playerId
            )) {
                $this->message = 'Option dice no longer need to be set';
                return NULL;
            }

            // try to set option values
            foreach ($optionValueArray as $dieIdx => $optionValue) {
                $game->optValueArrayArray[$currentPlayerIdx][$dieIdx] = $optionValue;
            }
            $game->proceed_to_next_user_action();

            // check for successful option value set
            if ((FALSE == $game->waitingOnActionArray[$currentPlayerIdx]) ||
                ($game->gameState > BMGameState::SPECIFY_DICE) ||
                ($game->roundNumber > $roundNumber)) {
                $game->log_action(
                    'choose_option',
                    $game->playerIdArray[$currentPlayerIdx],
                    array(
                        'roundNumber' => $game->roundNumber,
                        'optionValues' => $optionValueArray,
                    )
                );
                $this->save_game($game);
                $this->message = 'Successfully set option values';
                return TRUE;
            } else {
                if ($game->message) {
                    $this->message = $game->message;
                } else {
                    $this->message = 'Failed to set option values';
                }
                return NULL;
            }
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::submit_option_values: " .
                $e->getMessage()
            );
            $this->message = 'Internal error while setting option values';
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
                $this->message = 'It is not your turn to attack right now';
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
            $nAttackerDice = count($game->activeDieArrayArray[$attackerIdx]);
            $nDefenderDice = count($game->activeDieArrayArray[$defenderIdx]);

            for ($dieIdx = 0; $dieIdx < $nAttackerDice; $dieIdx++) {
                if (filter_var(
                    $dieSelectStatus["playerIdx_{$attackerIdx}_dieIdx_{$dieIdx}"],
                    FILTER_VALIDATE_BOOLEAN
                )) {
                    $attackers[] = $game->activeDieArrayArray[$attackerIdx][$dieIdx];
                    $attackerDieIdx[] = $dieIdx;
                }
            }

            for ($dieIdx = 0; $dieIdx < $nDefenderDice; $dieIdx++) {
                if (filter_var(
                    $dieSelectStatus["playerIdx_{$defenderIdx}_dieIdx_{$dieIdx}"],
                    FILTER_VALIDATE_BOOLEAN
                )) {
                    $defenders[] = $game->activeDieArrayArray[$defenderIdx][$dieIdx];
                    $defenderDieIdx[] = $dieIdx;
                }
            }

            // populate BMAttack object for the specified attack
            $game->attack = array($attackerIdx, $defenderIdx,
                                  $attackerDieIdx, $defenderDieIdx,
                                  $attackType);
            $attack = BMAttack::get_instance($attackType);

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
                    $this->message = 'Requested attack is not valid';
                } else {
                    $this->message = $attack->validationMessage;
                }
                return NULL;
            }
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::submit_turn: " .
                $e->getMessage()
            );
            $this->message = 'Internal error while submitting turn';
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

            switch ($action) {
                case 'add':
                    if (!array_key_exists($dieIdx, $game->activeDieArrayArray[$playerIdx]) ||
                        !$game->activeDieArrayArray[$playerIdx][$dieIdx]->has_skill('Auxiliary')) {
                        $this->message = 'Invalid auxiliary choice';
                        return FALSE;
                    }
                    $die = $game->activeDieArrayArray[$playerIdx][$dieIdx];
                    $die->selected = TRUE;
                    $waitingOnActionArray = $game->waitingOnActionArray;
                    $waitingOnActionArray[$playerIdx] = FALSE;
                    $game->waitingOnActionArray = $waitingOnActionArray;
                    $game->log_action(
                        'add_auxiliary',
                        $game->playerIdArray[$playerIdx],
                        array(
                            'roundNumber' => $game->roundNumber,
                            'die' => $die->get_action_log_data(),
                        )
                    );
                    $this->message = 'Auxiliary die chosen successfully';
                    break;
                case 'decline':
                    $game->waitingOnActionArray = array_fill(0, $game->nPlayers, FALSE);
                    $game->log_action(
                        'decline_auxiliary',
                        $game->playerIdArray[$playerIdx],
                        array('declineAuxiliary' => TRUE)
                    );
                    $this->message = 'Declined auxiliary dice';
                    break;
                default:
                    $this->message = 'Invalid response to auxiliary choice.';
                    return FALSE;
            }
            $this->save_game($game);

            return TRUE;
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::react_to_auxiliary: " .
                $e->getMessage()
            );
            $this->message = 'Internal error while making auxiliary decision';
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

            switch ($action) {
                case 'add':
                    if (!array_key_exists($dieIdx, $game->activeDieArrayArray[$playerIdx]) ||
                        !$game->activeDieArrayArray[$playerIdx][$dieIdx]->has_skill('Reserve')) {
                        $this->message = 'Invalid reserve choice';
                        return FALSE;
                    }
                    $die = $game->activeDieArrayArray[$playerIdx][$dieIdx];
                    $die->selected = TRUE;
                    $waitingOnActionArray = $game->waitingOnActionArray;
                    $waitingOnActionArray[$playerIdx] = FALSE;
                    $game->waitingOnActionArray = $waitingOnActionArray;
                    $game->log_action(
                        'add_reserve',
                        $game->playerIdArray[$playerIdx],
                        array( 'die' => $die->get_action_log_data(), )
                    );
                    $this->message = 'Reserve die chosen successfully';
                    break;
                case 'decline':
                    $waitingOnActionArray = $game->waitingOnActionArray;
                    $waitingOnActionArray[$playerIdx] = FALSE;
                    $game->waitingOnActionArray = $waitingOnActionArray;
                    $game->log_action(
                        'decline_reserve',
                        $game->playerIdArray[$playerIdx],
                        array('declineReserve' => TRUE)
                    );
                    $this->message = 'Declined reserve dice';
                    break;
                default:
                    $this->message = 'Invalid response to reserve choice.';
                    return FALSE;
            }

            $this->save_game($game);


            return TRUE;
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::react_to_reserve: " .
                $e->getMessage()
            );
            $this->message = 'Internal error while making reserve decision';
            return FALSE;
        }
    }

    // react_to_initiative expects the following inputs:
    //
    //   $action:
    //       One of {'chance', 'focus', 'decline'}.
    //
    //   $dieIdxArray:
    //       (i)   If this is a 'chance' action, then an array containing the
    //             index of the chance die that is being rerolled.
    //       (ii)  If this is a 'focus' action, then this is the nonempty array
    //             of die indices corresponding to the die values in
    //             dieValueArray. This can be either the indices of ALL focus
    //             dice OR just a subset.
    //       (iii) If this is a 'decline' action, then this will be ignored.
    //
    //   $dieValueArray:
    //       This is only used for the 'focus' action. It is a nonempty array
    //       containing the values of the focus dice that have been chosen by
    //       the user. The die indices of the dice being specified are given in
    //       $dieIdxArray.
    //
    // The function returns a boolean telling whether the reaction has been
    // successful.
    // If it fails, $this->message will say why it has failed.

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
                        $this->message = 'Only one chance die can be rerolled';
                        return FALSE;
                    }
                    $argArray['rerolledDieIdx'] = (int)$dieIdxArray[0];
                    break;
                case 'focus':
                    if (count($dieIdxArray) != count($dieValueArray)) {
                        $this->message = 'Mismatch in number of indices and values';
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
                    $this->message = 'Invalid action to respond to initiative.';
                    return FALSE;
            }

            $isSuccessful = $game->react_to_initiative($argArray);
            if ($isSuccessful) {
                $this->save_game($game);
                $this->message = 'Successfully gained initiative';
            } else {
                $this->message = $game->message;
            }

            return $isSuccessful;
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::react_to_initiative: " .
                $e->getMessage()
            );
            $this->message = 'Internal error while reacting to initiative';
            return FALSE;
        }
    }

    protected function get_config($conf_key) {
        try {
            $query = 'SELECT conf_value FROM config WHERE conf_key = :conf_key';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':conf_key' => $conf_key));
            $fetchResult = $statement->fetchAll();

            if (count($fetchResult) != 1) {
                error_log("Wrong number of config values with key " . $conf_key);
                return NULL;
            }
            return $fetchResult[0]['conf_value'];
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_config: " .
                $e->getMessage()
            );
            return NULL;
        }
    }

    // Calculates the difference between two (unix-style) timespans and formats
    // the result as a friendly approximation like '7 days' or '12 minutes'.
    protected function get_friendly_time_span($firstTime, $secondTime) {
        $seconds = (int)($secondTime - $firstTime);
        if ($seconds < 0) {
            $seconds *= -1;
        }

        if ($seconds < 60) {
            return $this->count_noun($seconds, 'second');
        }

        $minutes = (int)($seconds / 60);
        if ($minutes < 60) {
            return $this->count_noun($minutes, 'minute');
        }

        $hours = (int)($minutes / 60);
        if ($hours < 24) {
            return $this->count_noun($hours, 'hour');
        }

        $days = (int)($hours / 24);
        return $this->count_noun($days, 'day');
    }

    // Turns a number (like 5) and a noun (like 'golden ring') into a phrase
    // like '5 golden rings', pluralizing if needed.
    // Note: does not handle funky plurals.
    protected function count_noun($count, $noun) {
        if ($count == 1) {
            return $count . ' ' . $noun;
        }
        if (substr($noun, -1) == 's') {
            return $count . ' ' . $noun . 'es';
        }
        return $count . ' ' . $noun . 's';
    }

    // Retrieves the colors that the user has saved in their preferences
    protected function load_player_colors($currentPlayerId) {
        $playerInfoArray = $this->get_player_info($currentPlayerId);
        // Ultimately, these values should come from the database, but that
        // hasn't been implemented yet, so we'll just hard code them for now
        $colors = array(
            'player' => '#DD99DD',
            'opponent' => '#DDFFDD',
            'neutralA' => '#CCCCCC',
            'neutralB' => '#DDDDDD',
            // Itself an associative array of player ID's => color strings
            'battleBuddies' => array(),
        );
        return $colors;
    }

    // Determines which colors to use for the two players in a game.
    // $currentPlayerId is the player this is being displayed to.
    // $playerColors are the colors they've chosen as their preferences
    // (as returned by load_player_colors())
    // $gamePlayerIdA and $gamePlayerIdB are the two players in the game
    protected function determine_game_colors($currentPlayerId, $playerColors, $gamePlayerIdA, $gamePlayerIdB) {
        $gameColors = array();

        if ($gamePlayerIdA == $currentPlayerId) {
            $gameColors['playerA'] = $playerColors['player'];
            if (isset($playerColors['battleBuddies'][$gamePlayerIdB])) {
                $gameColors['playerB'] = $playerColors['battleBuddies'][$gamePlayerIdB];
            } else {
                $gameColors['playerB'] = $playerColors['opponent'];
            }
            return $gameColors;
        }

        if ($gamePlayerIdB == $currentPlayerId) {
            $gameColors['playerB'] = $playerColors['player'];
            if (isset($playerColors['battleBuddies'][$gamePlayerIdA])) {
                $gameColors['playerA'] = $playerColors['battleBuddies'][$gamePlayerIdA];
            } else {
                $gameColors['playerA'] = $playerColors['opponent'];
            }
            return $gameColors;
        }

        if (isset($playerColors['battleBuddies'][$gamePlayerIdA])) {
            $gameColors['playerA'] = $playerColors['battleBuddies'][$gamePlayerIdA];
        } else {
            $gameColors['playerA'] = $playerColors['neutralA'];
        }

        if (isset($playerColors['battleBuddies'][$gamePlayerIdB])) {
            $gameColors['playerB'] = $playerColors['battleBuddies'][$gamePlayerIdB];
        } else {
            $gameColors['playerB'] = $playerColors['neutralB'];
        }

        return $gameColors;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            switch ($property) {
                default:
                    return $this->$property;
            }
        }
    }

    public function __set($property, $value) {
        switch ($property) {
            case 'message':
                throw new LogicException(
                    'message can only be read, not written.'
                );
            default:
                $this->$property = $value;
        }
    }
}
