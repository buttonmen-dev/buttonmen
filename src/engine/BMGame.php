<?php

/**
 * BMGame: current status of a game
 *
 * @author james
 */
class BMGame {
    // properties
    private $playerArray;           // players
    private $activePlayer;          // active player
    private $playerWithInitiative;  // player who won initiative for this round
    private $buttonArray;           // buttons for all players
    private $activeDieArrayArray;   // active dice for all players
    private $capturedDieArrayArray; // captured dice for all players
    private $roundScoreArray;       // current points score in this round
    private $gameScoreArray;        // number of games W/T/L for all players
    private $maxWins;               // the game ends when a player has this many wins
    private $gameState;             // current game state as a BMGameState enum

    // methods

    // utility methods

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            switch ($property) {
                default:
                    return $this->$property;
            }
        }
    }

    public function __set($property, $value)
    {
        switch ($property) {
            default:
                $this->$property = $value;
        }
    }
}

class BMGameState extends SplEnum {
    const __default = self::startGame;

    // pre-game
    const startGame = 10;
    const applyHandicaps = 11;
    const chooseAuxiliaryDice = 12;

    // pre-round
    const loadDice = 20;
    const specifyDice = 21;
    const determineInitiative = 22;

    // start round
    const startRound = 30;

    // turn
    const startTurn = 40;
    const endTurn = 49;

    // end round
    const endRound = 50;

    // end game
    const endGame = 60;
}

?>
