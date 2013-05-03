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

        return $gameId;
    }

    public function load_game($gameId) {
        // this will be rewritten in the future to use a database instead of a file
        $gamefile = "/var/www/bmgame/$gameId.data";
        $gameInt = file_get_contents($gamefile);
        $game = unserialize($gameInt);

        $message = "Loaded data for game $gameId from file $gamefile.  Data:\n";

        return $game;
    }

    public function save_game($game) {
        // this will be rewritten in the future to use a database instead of a file
        $gamefile = "/var/www/bmgame/$game->gameId.data";
        $gameInt = serialize($game);
        file_put_contents($gamefile, $gameInt);

        $message = "Generated game $game->gameId: caching data in file: $gamefile\n";
    }

    public function get_all_button_names() {
        // this will be rewritten in the future to use a database instead of a
        // hard-coded array
        $buttonNameArray = array('Bauer', 'Stark');
        return $buttonNameArray;
    }

    public function get_player_id_from_name($name) {
        // this will be rewritten in the future to use a database instead of a
        // hard-coded array
        $idArray = array('blackshadowshade' => '314159',
                         'cgolubi' => '356995',
                         'jl8e' => '271828');

        if (array_key_exists($name, $idArray)) {
            return $idArray[$name];
        } else {
            return '';
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
