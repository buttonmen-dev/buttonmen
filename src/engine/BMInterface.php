<?php

//require_once 'BMButton.php';
require_once 'BMGame.php';

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

            // this will be rewritten in the future to use a database
            $button1 = new BMButton;
            $button2 = new BMButton;
            $button1->load_from_name($buttonNameArray[0]);
            $button2->load_from_name($buttonNameArray[1]);

            $game = new BMGame($gameId,
                               $playerIdArray,
                               array('', ''),
                               $maxWins);
            $game->buttonArray = array($button1, $button2);
            $this->save_game($game);
            $this->message = "Game $gameId created successfully.";
            return $gameId;
        } catch (Exception $e) {
            $this->message = 'Game create failed.';
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
            $gamefile = "/var/www/bmgame/$game->gameId.data";
            $gameInt = serialize($game);
            file_put_contents($gamefile, $gameInt);
            $this->message = "Generated game $game->gameId: caching data in file: $gamefile.";
        } catch (Exception $e) {
            $this->message = 'Game save failed.';
        }
    }

    public function get_all_button_names() {
        require_once('../database/mysql.inc.php');
        try {
            $statement = $conn->prepare('SELECT name, recipe FROM button_view');
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
        require_once('../database/mysql.inc.php');
        try {
            $sql = "SELECT name_ingame FROM player_info WHERE name_ingame LIKE :input ORDER BY name_ingame";
            $statement = $conn->prepare($sql);
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
            $idArray = array('blackshadowshade' => '314159',
                             'cgolubi' => '356995',
                             'jl8e' => '271828');

            if (array_key_exists($name, $idArray)) {
                $this->message = 'Player ID retrieved successfully.';
                return $idArray[$name];
            } else {
                $this->message = 'Player name does not exist.';
                return '';
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
