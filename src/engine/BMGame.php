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
    private $passStatusArray;       // boolean array whether each player passed
    private $capturedDieArrayArray; // captured dice for all players
    private $roundScoreArray;       // current points score in this round
    private $gameScoreArray;        // number of games W/T/L for all players
    private $maxWins;               // the game ends when a player has this many wins
    private $gameState;             // current game state as a BMGameState enum

    // methods
    public function updateGameState () {
        switch ($this->gameState) {
            case BMGameState::startGame:
                $this->gameState = BMGameState::chooseAuxiliaryDice;
                break;

            case BMGameState::applyHandicaps:
                break;

            case BMGameState::chooseAuxiliaryDice:
                $this->gameState = BMGameState::loadDice;
                break;

            case BMGameState::loadDice:
                $this->gameState = BMGameState::specifyDice;
                break;

            case BMGameState::specifyDice:
                $this->gameState = BMGameState::determineInitiative;
                break;

            case BMGameState::determineInitiative:
                if (!is_null($this->playerWithInitiative)) {
                    $this->gameState = BMGameState::startRound;
                }
                break;

            case BMGameState::startRound:
                $this->gameState = BMGameState::startTurn;
                break;

            case BMGameState::startTurn:
                $this->gameState = BMGameState::endTurn;
                break;

            case BMGameState::endTurn:
                $nDice = array_map("count", $this->activeDieArrayArray);
                // check if any player has no dice, or if everyone has passed
                if ((0 === min($nDice)) ||
                    !in_array(FALSE, $this->passStatusArray, TRUE)) {
                    $this->gameState = BMGameState::endRound;
                } else {
                    $this->gameState = BMGameState::startTurn;
                }
                break;

            case BMGameState::endRound:
                // score dice
                // update game score

                unset($this->activePlayer);
                unset($this->playerWithInitiative);
                unset($this->activeDieArrayArray);
                $tempPassStatusArray = array();
                $tempCapturedDiceArray = array();
                for ($playerIdx = 0; $playerIdx < count($this->playerArray); $playerIdx++) {
                    $tempPassStatusArray[] = FALSE;
                    $tempCapturedDiceArray[] = array();
                }
                $this->passStatusArray = $tempPassStatusArray;
                $this->capturedDieArrayArray = $tempCapturedDiceArray;

                $this->gameState = BMGameState::loadDice;
                for ($playerIdx = 0; $playerIdx < count($this->gameScoreArray) ; $playerIdx++) {
                    if ($this->gameScoreArray[$playerIdx]['W'] >= $this->maxWins) {
                        $this->gameState = BMGameState::endGame;
                        break;
                    }
                }
                break;

            case BMGameState::endGame:
                break;

            default:
                throw new LogicException ('An undefined game state cannot be updated.');
                break;
        }
    }

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
            case 'gameScoreArray':
                if (count($this->playerArray) != count($value)) {
                    throw new InvalidArgumentException('Invalid number of W/L/T results provided.');
                }
                $tempArray = array();
                for ($playerIdx = 0; $playerIdx < count($value); $playerIdx++) {
                    // check whether there are three inputs and they are all positive
                    if ((3 !== count($value[$playerIdx])) ||
                        min(array_map('min', $value)) < 0) {
                        throw new InvalidArgumentException('Invalid W/L/T array provided.');
                    }
                    $tempArray[$playerIdx] = array('W' => $value[$playerIdx][0],
                                                   'L' => $value[$playerIdx][1],
                                                   'D' => $value[$playerIdx][2]);
                }
                $this->gameScoreArray = $tempArray;
                break;
            default:
                $this->$property = $value;
        }
    }
}

class BMGameState {
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
