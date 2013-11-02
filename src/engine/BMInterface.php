<?php

require_once 'BMButton.php';
require_once 'BMGame.php';

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

    // constructor
    public function __construct($isTest = FALSE) {
        if ($isTest) {
            require 'test/src/database/mysql.test.inc.php';
        } else {
            require '../database/mysql.inc.php';
        }
        self::$conn = $conn;
    }

    // methods
    public function create_game(array $playerIdArray,
                                array $buttonNameArray,
                                $maxWins = 3) {
        try {
            // create basic game details
            $query = 'INSERT INTO game '.
                     '(n_players, n_target_wins, creator_id) '.
                     'VALUES '.
                     '(:n_players, :n_target_wins, :creator_id)';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':n_players'     => count($playerIdArray),
                                      ':n_target_wins' => $maxWins,
                                      ':creator_id'    => $playerIdArray[0]));

            $statement = self::$conn->prepare('SELECT LAST_INSERT_ID()');
            $statement->execute();
            $fetchData = $statement->fetch();
            $gameId = $fetchData[0];

            foreach ($playerIdArray as $position => $playerId) {
                // get button ID
                $buttonName = $buttonNameArray[$position];
                if (!is_null($buttonName)) {
                    $query = 'SELECT id FROM button '.
                             'WHERE name = :button_name';
                    $statement = self::$conn->prepare($query);
                    $statement->execute(array(':button_name' => $buttonName));
                    $fetchData = $statement->fetch();
                    $buttonId = $fetchData[0];
                } else {
                    $buttonId = NULL;
                }

                // add info to game_player_map
                $query = 'INSERT INTO game_player_map '.
                         '(game_id, player_id, button_id, position) '.
                         'VALUES '.
                         '(:game_id, :player_id, :button_id, :position)';
                $statement = self::$conn->prepare($query);

                $statement->execute(array(':game_id'   => $gameId,
                                          ':player_id' => $playerId,
                                          ':button_id' => $buttonId,
                                          ':position'  => $position));
            }

            // update game state to latest possible
            $game = $this->load_game($gameId);
            $this->save_game($game);

            $this->message = "Game $gameId created successfully.";
            return $gameId;
        } catch (Exception $e) {
            $errorData = $statement->errorInfo();
            $this->message = 'Game create failed: '.$errorData[2];
        }
    }

    public function load_game($gameId) {
        try {
            // check that the gameId exists
            $query = 'SELECT g.*,'.
                     'v.player_id, v.position,'.
                     'v.button_name,'.
                     'v.n_rounds_won, v.n_rounds_lost, v.n_rounds_drawn,'.
                     'v.did_win_initiative,'.
                     'v.is_awaiting_action '.
                     'FROM game AS g '.
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
                    $this->timestamp = new DateTime($row['last_action_time']);
                }

                $pos = $row['position'];
                $playerIdArray[$pos] = $row['player_id'];
                if (1 == $row['did_win_initiative']) {
                    $game->playerWithInitiativeIdx = $pos;
                }

                $gameScoreArrayArray[$pos] = array($row['n_rounds_won'],
                                                   $row['n_rounds_lost'],
                                                   $row['n_rounds_drawn']);

                // load button attributes
                $recipe = $this->get_button_recipe_from_name($row['button_name']);
                if ($recipe) {
                    $button = new BMButton;
                    $button->load($recipe, $row['button_name']);


                    $buttonArray[$pos] = $button;
                } else {
                    throw new InvalidArgumentException('Invalid button name.');
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

                if ($row['current_player_id'] == $row['player_id']) {
                    $game->activePlayerIdx = $pos;
                }

                if ($row['did_win_initiative']) {
                    $game->playerWithInitiativeIdx = $pos;
                }

                // james: currently, mock passStatusArray
                $game->passStatusArray = array(FALSE, FALSE);

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

            // add die attributes
            $query = 'SELECT * '.
                     'FROM die AS d '.
                     'WHERE game_id = :game_id '.
                     'ORDER BY id;';
            $statement2 = self::$conn->prepare($query);
            $statement2->execute(array(':game_id' => $gameId));

            $activeDieArrayArray = array_fill(0, count($playerIdArray), array());
            $capturedDieArrayArray = array_fill(0, count($playerIdArray), array());

            while ($row = $statement2->fetch()) {
                $playerIdx = array_search($row['owner_id'], $game->playerIdArray);

                $die = BMDie::create_from_recipe($row['recipe']);
                $die->value = $row['value'];
                $originalPlayerIdx = array_search($row['original_owner_id'],
                                                  $game->playerIdArray);
                $die->originalPlayerIdx = $originalPlayerIdx;

                if ($die instanceof BMDieSwing) {
                    $game->swingRequestArrayArray[$originalPlayerIdx][$die->swingType][] = $die;
                    $game->swingValueArrayArray[$originalPlayerIdx][$die->swingType] = $row['swing_value'];

                    if (isset($row['swing_value'])) {
                        $swingSetSuccess = $die->set_swingValue($game->swingValueArrayArray[$originalPlayerIdx]);
                        if (!$swingSetSuccess) {
                            throw new LogicException('Swing value set failed.');
                        }
                    }
                }

                switch ($row['status']) {
                    case 'NORMAL':
                        $activeDieArrayArray[$playerIdx][$row['position']] = $die;
                        break;
                    case 'CAPTURED':
                        $die->captured = TRUE;
                        $capturedDieArrayArray[$playerIdx][$row['position']] = $die;
                        break;
                }
            }

            $game->activeDieArrayArray = $activeDieArrayArray;
            $game->capturedDieArrayArray = $capturedDieArrayArray;

            $game->proceed_to_next_user_action();

            $this->message = $this->message."Loaded data for game $gameId.";
            return $game;
        } catch (Exception $e) {
            $this->message = "Game load failed: $e";
            var_dump($this->message);
        }
    }

    public function save_game(BMGame $game) {
        // force game to proceed to the latest possible before saving
        $game->proceed_to_next_user_action();

        if ((count($game->activeDieArrayArray[0]) == 5) &&
            (count($game->activeDieArrayArray[1]) == 5)) {
            $this->message = $game->message.
                             'before save'.
                             'swingvalue1:'.$game->activeDieArrayArray[0][4]->swingValue.
                             'swingvalue2:'.$game->activeDieArrayArray[1][4]->swingValue;
        }

        try {
            if (is_null($game->activePlayerIdx)) {
                $currentPlayerId = NULL;
            } else {
                $currentPlayerId = $game->playerIdArray[$game->activePlayerIdx];
            }

            // game
            $query = 'UPDATE game '.
                     'SET game_state = :game_state,'.
                     '    round_number = :round_number,'.
            //:n_recent_draws
            //:n_recent_passes
                     '    current_player_id = :current_player_id '.
            //:last_winner_id
            //:tournament_id
            //:description
            //:chat
                     'WHERE id = :game_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':game_state' => $game->gameState,
                                      ':round_number' => $game->roundNumber,
                                      ':current_player_id' => $currentPlayerId,
                                      ':game_id' => $game->gameId));

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

            // set player that won initiative
            if (isset($game->playerWithInitiativeIdx)) {
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
                         'AND player_id = :player_id;';
                $statement = self::$conn->prepare($query);
                if ($waitingOnAction) {
                    $is_awaiting_action = 1;
                } else {
                    $is_awaiting_action = 0;
                }
                $statement->execute(array(':is_awaiting_action' => $is_awaiting_action,
                                          ':game_id' => $game->gameId,
                                          ':player_id' => $game->playerIdArray[$playerIdx]));
            }

            // set existing dice to have a status of DELETED and get die ids
            //
            // note that the logic is written this way to make debugging easier
            // in case something fails during the addition of dice
            $query = 'UPDATE die '.
                     'SET status = "DELETED" '.
                     'WHERE game_id = :game_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':game_id' => $game->gameId));

            // add active dice to table 'die'
            foreach ($game->activeDieArrayArray as $playerIdx => $activeDieArray) {
                foreach ($activeDieArray as $dieIdx => $activeDie) {
                    // james: set status, this is currently INCOMPLETE
                    $status = 'NORMAL';

                    $query = 'INSERT INTO die '.
                             '(owner_id, original_owner_id, game_id, status, recipe, swing_value, position, value) '.
                             'VALUES (:owner_id, :original_owner_id, :game_id, :status, :recipe, :swing_value, :position, :value);';
                    $statement = self::$conn->prepare($query);
                    $statement->execute(array(':owner_id' => $game->playerIdArray[$playerIdx],
                                              ':original_owner_id' => $game->playerIdArray[$activeDie->originalPlayerIdx],
                                              ':game_id' => $game->gameId,
                                              ':status' => $status,
                                              ':recipe' => $activeDie->recipe,
                                              ':swing_value' => $activeDie->swingValue,
                                              ':position' => $dieIdx,
                                              ':value' => $activeDie->value));
                }
            }

            // add captured dice to table 'die'
            foreach ($game->capturedDieArrayArray as $playerIdx => $activeDieArray) {
                foreach ($activeDieArray as $dieIdx => $activeDie) {
                    // james: set status, this is currently INCOMPLETE
                    $status = 'CAPTURED';

                    $query = 'INSERT INTO die '.
                             '(owner_id, original_owner_id, game_id, status, recipe, swing_value, position, value) '.
                             'VALUES (:owner_id, :original_owner_id, :game_id, :status, :recipe, :swing_value, :position, :value);';
                    $statement = self::$conn->prepare($query);
                    $statement->execute(array(':owner_id' => $game->playerIdArray[$playerIdx],
                                              ':original_owner_id' => $game->playerIdArray[$activeDie->originalPlayerIdx],
                                              ':game_id' => $game->gameId,
                                              ':status' => $status,
                                              ':recipe' => $activeDie->recipe,
                                              ':swing_value' => $activeDie->swingValue,
                                              ':position' => $dieIdx,
                                              ':value' => $activeDie->value));
                }
            }

            // delete dice with a status of "DELETED" for this game
            $query = 'DELETE FROM die '.
                     'WHERE status = "DELETED" '.
                     'AND game_id = :game_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':game_id' => $game->gameId));

            $this->message = $this->message."Saved game $game->gameId.";
        } catch (Exception $e) {
            $this->message = "Game save failed: $e";
            var_dump($this->message);
        }
    }

    public function get_all_active_games($playerId) {
        try {
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
                     'g.status '.
                     'FROM game_player_view AS v1 '.
                     'LEFT JOIN game_player_view AS v2 '.
                     'ON v1.game_id = v2.game_id '.
                     'LEFT JOIN game AS g '.
                     'ON g.id = v1.game_id '.
                     'WHERE v2.player_id = :player_id '.
                     'AND v1.player_id != v2.player_id '.
                     'AND g.status != "COMPLETE" '.
                     'ORDER BY v1.game_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':player_id' => $playerId));

            while ($row = $statement->fetch()) {
                $gameIdArray[]             = $row['game_id'];
                $opponentIdArray[]         = $row['opponent_id'];
                $opponentNameArray[]       = $row['opponent_name'];
                $myButtonNameArray[]       = $row['my_button_name'];
                $opponentButtonNameArray[] = $row['opponent_button_name'];
                $nWinsArray[]              = $row['n_wins'];
                $nDrawsArray[]             = $row['n_draws'];
                $nLossesArray[]            = $row['n_losses'];
                $nTargetWinsArray[]        = $row['n_target_wins'];
                $isAwaitingActionArray[]   = $row['is_awaiting_action'];
                $gameStateArray[]          = $row['game_state'];
                $statusArray[]             = $row['status'];
            }
            $this->message = 'All game details retrieved successfully.';
            return array('gameIdArray'             => $gameIdArray,
                         'opponentIdArray'         => $opponentIdArray,
                         'opponentNameArray'       => $opponentNameArray,
                         'myButtonNameArray'       => $myButtonNameArray,
                         'opponentButtonNameArray' => $opponentButtonNameArray,
                         'nWinsArray'              => $nWinsArray,
                         'nDrawsArray'             => $nDrawsArray,
                         'nLossesArray'            => $nLossesArray,
                         'nTargetWinsArray'        => $nTargetWinsArray,
                         'isAwaitingActionArray'   => $isAwaitingActionArray,
                         'gameStateArray'          => $gameStateArray,
                         'statusArray'             => $statusArray);
        } catch (Exception $e) {
            $this->message = 'Game detail get failed.';
        }
    }

    public function get_all_button_names() {
        try {
            $statement = self::$conn->prepare('SELECT name, recipe FROM button_view');
            $statement->execute();

            while ($row = $statement->fetch()) {
                $buttonNameArray[] = $row['name'];
                $recipeArray[] = $row['recipe'];
            }
            $this->message = 'All button names retrieved successfully.';
            return array('buttonNameArray' => $buttonNameArray,
                         'recipeArray'     => $recipeArray);
        } catch (Exception $e) {
            $this->message = 'Button name get failed.';
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
            $this->message = 'Button recipe get failed.';
        }
    }

    public function get_player_names_like($input = '') {
        try {
            $query = 'SELECT name_ingame FROM player '.
                     'WHERE name_ingame LIKE :input '.
                     'ORDER BY name_ingame';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':input' => $input.'%'));

            $nameArray = array();
            while ($row = $statement->fetch()) {
                $nameArray[] = $row['name_ingame'];
            }
            $this->message = 'Names retrieved successfully.';
            return array('nameArray' => $nameArray);
        } catch (Exception $e) {
            $this->message = 'Player name get failed.';
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
                return($result[0]);
            }
        } catch (Exception $e) {
            $this->message = 'Player ID get failed.';
        }
    }

    public function get_player_name_from_id($id) {
        try {
            $query = 'SELECT name_ingame FROM player '.
                     'WHERE id = :id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':id' => $id));
            $result = $statement->fetch();
            if (!$result) {
                $this->message = 'Player ID does not exist.';
                return('');
            } else {
                $this->message = 'Player name retrieved successfully.';
                return($result[0]);
            }
        } catch (Exception $e) {
            $this->message = 'Player name get failed.';
        }
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
                    'message can only be read, not written.');
            default:
                $this->$property = $value;
        }
    }
}


?>
