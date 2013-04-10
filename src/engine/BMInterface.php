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
