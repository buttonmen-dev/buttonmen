<?php

//require_once 'BMButton.php';
//require_once 'BMGame.php';

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
            $query = 'INSERT INTO game_details '.
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
                    $query = 'SELECT id FROM button_definitions '.
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

            // this will be rewritten in the future to use a database
//            $button1 = new BMButton;
//            $button2 = new BMButton;
//            $button1->load_from_name($buttonNameArray[0]);
//            $button2->load_from_name($buttonNameArray[1]);

//            $game = new BMGame($gameId,
//                               $playerIdArray,
//                               array('', ''),
//                               $maxWins);
//            $game->buttonArray = array($button1, $button2);
//            $this->save_game($game);
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
            // this will be rewritten in the future to use a database instead of a file
//            $gamefile = "/var/www/bmgame/$game->gameId.data";
//            $gameInt = serialize($game);
//            file_put_contents($gamefile, $gameInt);
            $query = "INSERT INTO game_details () ".
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
            $query = 'SELECT name_ingame FROM player_info '.
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
            // this will be rewritten in the future to use a database instead of a
            // hard-coded array
//            $idArray = array('blackshadowshade' => '1',
//                             'glassonion' => '3',
//                             'jl8e' => '2');
            $query = 'SELECT id FROM player_info '.
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
