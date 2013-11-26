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
            if (file_exists('../test/src/database/mysql.test.inc.php')) {
                require '../test/src/database/mysql.test.inc.php';
            } else {
                require 'test/src/database/mysql.test.inc.php';
            }
        } else {
            require '../database/mysql.inc.php';
        }
        self::$conn = $conn;
    }

    // methods

    public function create_user($username, $password) {
	try {
            // check to see whether this username already exists
            $query = 'SELECT id FROM player WHERE name_ingame = :username';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':username' => $_POST['username']));
            $result = $statement->fetchAll();

            if (count($result) > 0) {
                $user_id = $result[0]['id'];
                $this->message = $username . ' already exists (id=' .
                                 $user_id . ')';
                return NULL;
            }

            // create user
            $query = 'INSERT INTO player (name_ingame, password_hashed)
                      VALUES (:username, :password)';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':username' => $username,
                                      ':password' => crypt($password)));
            $this->message = 'User ' . $username . ' created successfully';
            return array('userName' => $username);
        } catch (Exception $e) {
            $errorData = $statement->errorInfo();
            $this->message = 'User create failed: ' . $errorData[2];
            return NULL;
        }
    }

    public function create_game(array $playerIdArray,
                                array $buttonNameArray,
                                $maxWins = 3) {
        // check for nonunique player ids
        if (count(array_flip($playerIdArray)) < count($playerIdArray)) {
            $this->message = 'Game create failed because a player has been selected more than once.';
            return NULL;
        }

        try {
            // create basic game details
            $query = 'INSERT INTO game '.
                     '(n_players, n_target_wins, n_recent_passes, creator_id) '.
                     'VALUES '.
                     '(:n_players, :n_target_wins, :n_recent_passes, :creator_id)';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':n_players'     => count($playerIdArray),
                                      ':n_target_wins' => $maxWins,
                                      ':n_recent_passes' => 0,
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
            if ($game == null) {
                throw new Exception(
                    "Could not load newly-created game $gameId");
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
                $e->getMessage());
            return NULL;
        }
    }

    // The optional argument $autopassOverride is for testing purposes only!
    public function load_game($gameId, $autopassOverride = NULL) {
        try {
            // check that the gameId exists
            $query = 'SELECT g.*,'.
                     'v.player_id, v.position, v.autopass,'.
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
                    $game->turnNumberInRound = $row['turn_number_in_round'];
                    $game->nRecentPasses = $row['n_recent_passes'];
                    $this->timestamp = new DateTime($row['last_action_time']);
                }

                $pos = $row['position'];
                $playerIdArray[$pos] = $row['player_id'];

                if (is_null($autopassOverride)) {
                    $autopassArray[$pos] = (bool)$row['autopass'];
                } else {
                    assert(is_array($autopassOverride));
                    assert(array_key_exists($pos, $autopassOverride));
                    $autopassArray[$pos] = $autopassOverride[$pos];
                }

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

            // add swing values
            $query = 'SELECT * '.
                     'FROM game_swing_map '.
                     'WHERE game_id = :game_id ';
            $statement2 = self::$conn->prepare($query);
            $statement2->execute(array(':game_id' => $gameId));
            while ($row = $statement2->fetch()) {
                $playerIdx = array_search($row['player_id'], $game->playerIdArray);
                $game->swingValueArrayArray[$playerIdx][$row['swing_type']] = $row['swing_value'];
            }

            // add die attributes
            $query = 'SELECT * '.
                     'FROM die '.
                     'WHERE game_id = :game_id '.
                     'ORDER BY id;';
            $statement3 = self::$conn->prepare($query);
            $statement3->execute(array(':game_id' => $gameId));

            $activeDieArrayArray = array_fill(0, count($playerIdArray), array());
            $capturedDieArrayArray = array_fill(0, count($playerIdArray), array());

            while ($row = $statement3->fetch()) {
                $playerIdx = array_search($row['owner_id'], $game->playerIdArray);

                $die = BMDie::create_from_recipe($row['recipe']);
                $die->value = $row['value'];
                $originalPlayerIdx = array_search($row['original_owner_id'],
                                                  $game->playerIdArray);
                $die->originalPlayerIdx = $originalPlayerIdx;

                if ($die instanceof BMDieSwing) {
                    $game->swingRequestArrayArray[$originalPlayerIdx][$die->swingType][] = $die;

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
            error_log(
                "Caught exception in BMInterface::load_game: " .
                $e->getMessage());
            $this->message = "Game load failed: $e";
            return NULL;
        }
    }

    public function load_game_without_autopass($gameId) {
        return $this->load_game($gameId, array(FALSE, FALSE));
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

            // game
            $query = 'UPDATE game '.
                     'SET game_state = :game_state,'.
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
            $statement->execute(array(':game_state' => $game->gameState,
                                      ':round_number' => $game->roundNumber,
                                      ':turn_number_in_round' => $game->turnNumberInRound,
                                      ':n_recent_passes' => $game->nRecentPasses,
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

            // set swing values
            $query = 'DELETE FROM game_swing_map '.
                     'WHERE game_id = :game_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':game_id' => $game->gameId));

            if (isset($game->swingValueArrayArray)) {
                foreach ($game->playerIdArray as $playerIdx => $playerId) {
                    if (!array_key_exists($playerIdx, $game->swingValueArrayArray)) {
                        continue;
                    }
                    $swingValueArray = $game->swingValueArrayArray[$playerIdx];
                    if (isset($swingValueArray)) {
                        foreach ($swingValueArray as $swingType => $swingValue) {
                            $query = 'INSERT INTO game_swing_map '.
                                     '(game_id, player_id, swing_type, swing_value) '.
                                     'VALUES '.
                                     '(:game_id, :player_id, :swing_type, :swing_value)';
                            $statement = self::$conn->prepare($query);
                            $statement->execute(array(':game_id'     => $game->gameId,
                                                      ':player_id'   => $playerId,
                                                      ':swing_type'  => $swingType,
                                                      ':swing_value' => $swingValue));
                        }
                    }

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
            if (isset($game->activeDieArrayArray)) {
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
            }

            // add captured dice to table 'die'
            if (isset($game->capturedDieArrayArray)) {
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
            }

            // delete dice with a status of "DELETED" for this game
            $query = 'DELETE FROM die '.
                     'WHERE status = "DELETED" '.
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
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::save_game: " .
                $e->getMessage());
            $this->message = "Game save failed: $e";
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

            // Initialize the arrays
            $gameIdArray = array();
            $opponentIdArray = array();
            $opponentNameArray = array();
            $myButtonNameArray = array();
            $opponentButtonNameArray = array();
            $nWinsArray = array();
            $nDrawsArray = array();
            $nLossesArray = array();
            $nTargetWinsArray = array();
            $isAwaitingActionArray = array();
            $gameStateArray = array();
            $statusArray = array();

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
            error_log(
                "Caught exception in BMInterface::get_all_active_games: " .
                $e->getMessage());
            $this->message = 'Game detail get failed.';
            return NULL;
        }
    }

    public function get_all_button_names() {
        try {
            $statement = self::$conn->prepare('SELECT name, recipe FROM button_view');
            $statement->execute();

	    // Look for unimplemented skills in each button definition.
	    // If we get an exception while checking, assume there's
	    // an unimplemented skill
            while ($row = $statement->fetch()) {
                $buttonNameArray[] = $row['name'];
                $recipeArray[] = $row['recipe'];
                try {
                    $button = new BMButton();
                    $button->load($row['recipe'], $row['name']);
                    $hasUnimplementedSkillArray[] = $button->hasUnimplementedSkill;
                } catch (Exception $e) {
                    $hasUnimplementedSkillArray[] = True;
                }
            }
            $this->message = 'All button names retrieved successfully.';
            return array('buttonNameArray'            => $buttonNameArray,
                         'recipeArray'                => $recipeArray,
                         'hasUnimplementedSkillArray' => $hasUnimplementedSkillArray);
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_all_button_names: " .
                $e->getMessage());
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
                . $e->getMessage());
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
            error_log(
                "Caught exception in BMInterface::get_player_names_like: " .
                $e->getMessage());
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
                return($result[0]);
            }
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_player_id_from_name: " .
                $e->getMessage());
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
                return($result[0]);
            }
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_player_name_from_id: " .
                $e->getMessage());
            $this->message = 'Player name get failed.';
        }
    }

    // Check whether a requested action still needs to be taken
    // Note: it might be possible for this to be a protected function
    public function is_action_current(BMGame $game,
                                      $expectedGameState,
                                      $postedTimestamp,
                                      $roundNumber,
                                      $currentPlayerId) {
        $currentPlayerIdx = array_search($currentPlayerId, $game->playerIdArray);
        return (($postedTimestamp == $this->timestamp->format(DATE_RSS)) &&
                ($roundNumber == $game->roundNumber) &&
                ($expectedGameState == $game->gameState) &&
                (TRUE == $game->waitingOnActionArray[$currentPlayerIdx]));
    }

    // Enter recent game actions into the action log
    // Note: it might be possible for this to be a protected function
    public function log_game_actions(BMGame $game) {
        $query = 'INSERT INTO game_action_log ' .
                 '(game_id, game_state, action_type, acting_player, message) ' .
                 'VALUES ' .
                 '(:game_id, :game_state, :action_type, :acting_player, :message)';
        foreach ($game->actionLog as $idx => $gameAction) {
            $statement = self::$conn->prepare($query);
            $statement->execute(
                array(':game_id'     => $game->gameId,
                      ':game_state' => $gameAction['gameState'],
                      ':action_type' => $gameAction['actionType'],
                      ':acting_player' => $gameAction['actingPlayerIdx'],
                      ':message'    => $gameAction['message']));
        }
        $game->empty_action_log();
    }

    public function load_game_action_log(BMGame $game, $n_entries = 5) {
        try {
            $query = 'SELECT action_time,action_type,acting_player,message FROM game_action_log ' .
                     'WHERE game_id = :game_id ORDER BY id DESC LIMIT ' . $n_entries;
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':game_id' => $game->gameId));
            $logEntries = array();
            while ($row = $statement->fetch()) {
                $gameAction = array(
                    'actionType' => $row['action_type'],
                    'actingPlayerIdx' => $row['acting_player'],
                    'message' => $row['message'],
                );
                $logEntries[] = array(
                    'timestamp' => $row['action_time'],
                    'message' => $this->friendly_game_action_log_message($gameAction)
                );
            }
            return $logEntries;
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::load_game_action_log: " .
                $e->getMessage());
            $this->message = 'Internal error while reading log entries';
            return NULL;
        }
    }

    private function friendly_game_action_log_message($gameAction) {
        if ($gameAction['actingPlayerIdx'] != 0) {
            $actingPlayerName = $this->get_player_name_from_id($gameAction['actingPlayerIdx']);
        }
        if ($gameAction['actionType'] == 'attack') {
            $msgstr = $gameAction['message'];
            return (str_replace('completed', 'by ' . $actingPlayerName, $gameAction['message'], $count));
        }
        if ($gameAction['actionType'] == 'end_winner') {
            return ('End of round: ' . $actingPlayerName . ' ' . $gameAction['message']);
        }
        return($gameAction['message']);
    }

    // Create a status message based on recent game actions
    private function load_message_from_game_actions(BMGame $game) {
        $this->message = '';
        foreach ($game->actionLog as $idx => $gameAction) {
            $this->message .= $this->friendly_game_action_log_message($gameAction) . '. ';
        }
    }

    public function submit_swing_values($userId, $gameNumber,
                                        $roundNumber, $submitTimestamp,
                                        $swingValueArray) {
        try {
            $game = $this->load_game($gameNumber);
            $currentPlayerIdx = array_search($userId, $game->playerIdArray);

            // check that the timestamp and the game state are correct, and that
            // the swing values still need to be set
            if (!$this->is_action_current($game,
                                          BMGameState::specifyDice,
                                          $submitTimestamp,
                                          $roundNumber,
                                          $userId)) {
                $this->message = 'Swing dice no longer need to be set';
                return NULL;
            }

            // try to set swing values
            $swingRequestArray = array_keys($game->swingRequestArrayArray[$currentPlayerIdx]);

            if (count($swingRequestArray) != count($swingValueArray)) {
                $this->message = 'Wrong number of swing values submitted';
                return NULL;
            }

            $swingValueArrayWithKeys = array();
            foreach ($swingRequestArray as $swingIdx => $swingRequest) {
                $swingValueArrayWithKeys[$swingRequest] = $swingValueArray[$swingIdx];
            }

            $game->swingValueArrayArray[$currentPlayerIdx] = $swingValueArrayWithKeys;

            $game->proceed_to_next_user_action();

            // check for successful swing value set
            if ((FALSE == $game->waitingOnActionArray[$currentPlayerIdx]) ||
                ($game->gameState > BMGameState::specifyDice) ||
                ($game->roundNumber > $roundNumber)) {
                $this->save_game($game);
                $this->message = 'Successfully set swing values';
                return True;
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
                $e->getMessage());
            $this->message = 'Internal error while setting swing values';
        }
    }

    public function submit_turn($userId, $gameNumber, $roundNumber,
                                $submitTimestamp,
				$dieSelectStatus, $attackType,
				$attackerIdx, $defenderIdx) {
        try {
            $game = $this->load_game($gameNumber);
            if (!$this->is_action_current($game,
                                          BMGameState::startTurn,
                                          $submitTimestamp,
                                          $roundNumber,
                                          $userId)) {
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
                if (filter_var($dieSelectStatus['playerIdx_'.$attackerIdx.'_dieIdx_'.$dieIdx],
                    FILTER_VALIDATE_BOOLEAN)) {
                    $attackers[] = $game->activeDieArrayArray[$attackerIdx][$dieIdx];
                    $attackerDieIdx[] = $dieIdx;
                }
            }

            for ($dieIdx = 0; $dieIdx < $nDefenderDice; $dieIdx++) {
                if (filter_var($dieSelectStatus['playerIdx_'.$defenderIdx.'_dieIdx_'.$dieIdx],
                    FILTER_VALIDATE_BOOLEAN)) {
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

            // validate the attack and output the result
            if ($attack->validate_attack($game, $attackers, $defenders)) {
                $game->proceed_to_next_user_action();
                $this->save_game($game);

                // On success, don't set a message, because one will be set from the action log
                return True;
            } else {
                $this->message = 'Requested attack is not valid';
                return NULL;
            }
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::submit_turn: " .
                $e->getMessage());
            $this->message = 'Internal error while submitting turn';
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
