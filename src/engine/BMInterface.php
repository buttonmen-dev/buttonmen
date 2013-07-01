<?php

/**
 * BMInterface: interface between GUI and BMGame
 *
 * @author james
 *
 * @property-read string $message                Message intended for GUI
 *
 */
class BMInterface {
    // properties
    private $message;               // message intended for GUI
    private static $conn = NULL;    // connection to database

    // constructor
    public function __construct() {
        require '../database/mysql.inc.php';
        self::$conn = $conn;
    }

    // methods
    public function create_game($playerIdArray,
                                $buttonNameArray,
                                $maxWins = 3,
                                $requestedGameId = 0) {
        try {
            if (0 == $requestedGameId) {
                $gameId = mt_rand();
            } else {
                $gameId = $requestedGameId;
            }

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

            $this->message = "Game $gameId created successfully.";
            return $gameId;
        } catch (Exception $e) {
            $errorData = $statement->errorInfo();
            $this->message = 'Game create failed: '.$errorData[2];
        }
    }

    public function load_game($gameId) {
        try {
            // this will be rewritten in the future to use a database instead of a file
            $gamefile = "/var/www/bmgame/$gameId.data";
            $gameInt = file_get_contents($gamefile);
            $game = unserialize($gameInt);
            $this->message = "Loaded data for game $gameId from file $gamefile.";
            return $game;
        } catch (Exception $e) {
            $this->message = 'Game load failed.';
        }
    }

    public function save_game($game) {
        try {
            $query = "INSERT INTO game () ".
                     "VALUES ".
                     "()";
            $statement = self::$conn->prepare($query);
            $statement->execute();

            $statement = self::$conn->prepare('SELECT LAST_INSERT_ID()');
            $gameId = $statement->execute();



            $this->message = "Generated game $game->gameId: caching data in file: $gamefile.";
        } catch (Exception $e) {
            $this->message = 'Game save failed.';
        }
    }

    public function get_all_active_games($playerId) {
        try {
            // the following SQL logic assumes that there are only two players per game
            $sql = 'SELECT v1.game_id,'.
                   'v1.player_id AS opponent_id,'.
                   'v1.player_name AS opponent_name,'.
                   'v2.button_name AS my_button_name,'.
                   'v1.button_name AS opponent_button_name,'.
                   'v2.n_rounds_won AS n_wins,'.
                   'v2.n_rounds_drawn AS n_draws,'.
                   'v1.n_rounds_won AS n_losses,'.
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
            $statement = self::$conn->prepare($sql);
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
