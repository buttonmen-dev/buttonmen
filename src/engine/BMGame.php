<?php

/**
 * BMGame: current status of a game
 *
 * @author james
 */
class BMGame {
    // properties
    private $gameId;                // game ID number in the database
    private $playerIdxArray;        // array of player IDs
    private $activePlayerIdx;       // index of the active player in playerIdxArray
    private $playerWithInitiativeIdx; // index of the player who won initiative
    private $buttonArray;           // buttons for all players
    private $activeDieArrayArray;   // active dice for all players
    private $attack;                // array(attacking_die_idx, target_die_idx, attack type)
    private $passStatusArray;       // boolean array whether each player passed
    private $capturedDieArrayArray; // captured dice for all players
    private $roundScoreArray;       // current points score in this round
    private $gameScoreArray;        // number of games W/T/L for all players
    private $maxWins;               // the game ends when a player has this many wins
    private $gameState;             // current game state as a BMGameState enum

    // methods
    public function do_next_step() {
        switch ($this->gameState) {
            case BMGameState::startGame:

                break;

            case BMGameState::applyHandicaps:

                break;

            case BMGameState::chooseAuxiliaryDice:

                break;

            case BMGameState::loadDiceIntoButtons:

                break;

            case BMGameState::specifyDice:

                break;

            case BMGameState::addAvailableDiceToGame;

                break;

            case BMGameState::determineInitiative:

                break;

            case BMGameState::startRound:

                break;

            case BMGameState::startTurn:

                break;

            case BMGameState::endTurn:

                break;

            case BMGameState::endRound:
                // score dice
                // update game score

                break;

            case BMGameState::endGame:
                break;

            default:
                throw new LogicException ('An undefined game state cannot be updated.');
                break;
        }
    }

    public function update_game_state() {
        switch ($this->gameState) {
            case BMGameState::startGame:
                if (!in_array(0, $this->playerIdxArray) &&
                    isset($this->buttonArray)) {
                    $this->gameState = BMGameState::applyHandicaps;
                    $this->passStatusArray = array(FALSE, FALSE);
                    $this->gameScoreArray = array(array(0, 0, 0), array(0, 0, 0));
                }
                break;

            case BMGameState::applyHandicaps:
                assert(isset($this->maxWins));
                if (isset($this->gameScoreArray)) {
                    $nWins = 0;
                    foreach($this->gameScoreArray as $gameScore) {
                        if ($nWins < $gameScore['W']) {
                            $nWins = $gameScore['W'];
                        }
                    }
                    if ($nWins >= $this->maxWins) {
                        $this->gameState = BMGameState::endGame;
                    } else {
                        $this->gameState = BMGameState::chooseAuxiliaryDice;
                    }
                }
                break;

            case BMGameState::chooseAuxiliaryDice:
                $containsAuxiliaryDice = FALSE;
                foreach ($this->buttonArray as $tempButton) {
                    if ($this->does_recipe_have_auxiliary_dice($tempButton->recipe)) {
                        $containsAuxiliaryDice = TRUE;
                        break;
                    }
                }
                if (!$containsAuxiliaryDice) {
                    $this->gameState = BMGameState::loadDiceIntoButtons;
                }
                break;

            case BMGameState::loadDiceIntoButtons:
                assert(isset($this->buttonArray));
                $buttonsLoadedWithDice = TRUE;
                foreach ($this->buttonArray as $tempButton) {
                    if (!isset($tempButton->dieArray)) {
                        $buttonsLoadedWithDice = FALSE;
                        break;
                    }
                }
                if ($buttonsLoadedWithDice) {
                    $this->gameState = BMGameState::specifyDice;
                }
                break;

            case BMGameState::specifyDice:
                $areAllDiceSpecified = TRUE;
                foreach ($this->buttonArray as $tempButton) {
                    foreach ($tempButton->dieArray as $tempDie) {
                        if (!$this->is_die_specified($tempDie)) {
                            $areAllDiceSpecified = FALSE;
                            break 2;
                        }
                    }
                }
                if ($areAllDiceSpecified) {
                    $this->gameState = BMGameState::addAvailableDiceToGame;
                }
                break;

            case BMGameState::addAvailableDiceToGame;
                if (isset($this->activeDieArrayArray)) {
                    $this->gameState = BMGameState::determineInitiative;
                }
                break;

            case BMGameState::determineInitiative:
                if (isset($this->playerWithInitiativeIdx)) {
                    $this->gameState = BMGameState::startRound;
                }
                break;

            case BMGameState::startRound:
                if (isset($this->activePlayerIdx)) {
                    $this->gameState = BMGameState::startTurn;
                }
                break;

            case BMGameState::startTurn:
                if ($this->is_valid_attack()) {
                    $this->gameState = BMGameState::endTurn;
                    // james: this needs to be moved into the stage running code
                    //$this->perform_attack();
                }
                break;

            case BMGameState::endTurn:
                $nDice = array_map("count", $this->activeDieArrayArray);
                // check if any player has no dice, or if everyone has passed
                if ((0 === min($nDice)) ||
                    !in_array(FALSE, $this->passStatusArray, TRUE)) {
                    $this->gameState = BMGameState::endRound;
                    unset($this->activeDieArrayArray);
                } else {
                    $this->gameState = BMGameState::startTurn;
                    $this->change_active_player();
                }
                break;

            case BMGameState::endRound:
                // score dice
                // update game score
                $this->reset_play_state();

                $this->gameState = BMGameState::loadDiceIntoButtons;
                foreach ($this->gameScoreArray as $gameScore) {
                    if ($gameScore['W'] >= $this->maxWins) {
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

    public static function does_recipe_have_auxiliary_dice($recipe) {
        if (FALSE === strpos($recipe, '+')) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // james: parts of this function needs to be moved to the BMDie class
    public static function is_die_specified($die) {
        // A die can be unspecified if it is swing, option, or plasma.

        // If swing or option, then it is unspecified if the sides are unclear.
        // check for swing letter or option '/' inside the brackets
        // remove everything before the opening parenthesis
        $sides = $die->mSides;

        if (strlen(preg_replace('#[^[:alpha:]/]#', '', $sides)) > 0) {
            return FALSE;
        }

        // If plasma, then it is unspecified if the skills are unclear.
        // james: not written yet

        return TRUE;
    }

    private function is_valid_attack() {
        if (isset($this->attack)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function perform_attack() {
        // currently just a placeholder
    }

    private function reset_play_state() {
        unset($this->activePlayerIdx);
        unset($this->playerWithInitiativeIdx);
        unset($this->activeDieArrayArray);
        $tempPassStatusArray = array();
        $tempCapturedDiceArray = array();
        foreach ($this->playerIdxArray as $playerIdx) {
            $tempPassStatusArray[] = FALSE;
            $tempCapturedDiceArray[] = array();
        }
        $this->passStatusArray = $tempPassStatusArray;
        $this->capturedDieArrayArray = $tempCapturedDiceArray;
        unset($this->roundScoreArray);
    }

    private function change_active_player() {
        assert(isset($this->activePlayerIdx));

        // move to the next player
        $this->activePlayerIdx = ($this->activePlayerIdx + 1) %
                                 count($this->playerIdxArray);
    }

    // utility methods
    public function __construct($gameID = 0,
                                $playerIdxArray = array(0, 0),
                                $buttonRecipeArray = array('', ''),
                                $maxWins = 3) {
        if (count($playerIdxArray) !== count($buttonRecipeArray)) {
            throw new InvalidArgumentException(
                'Number of buttons must equal the number of players.');
        }
        $this->gameId = $gameID;
        $this->playerIdxArray = $playerIdxArray;
        foreach ($buttonRecipeArray as $recipe) {
            $tempButton = new BMButton;
            $tempButton->load_from_recipe($recipe);
            $this->buttonArray[] = $tempButton;
        }
        $this->maxWins = $maxWins;
    }

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
// james: need to validate properties

        switch ($property) {
//    private $gameId;                // game ID number in the database
//    private $playerIdxArray;        // array of player IDs
//    private $activePlayerIdx;       // index of the active player in playerIdxArray
//    private $playerWithInitiativeIdx; // index of the player who won initiative
//    private $buttonArray;           // buttons for all players
//    private $activeDieArrayArray;   // active dice for all players
            case 'attack':
                if (!is_array($value) || (3 !== count($value))) {
                    throw new InvalidArgumentException(
                        'There must be exactly three elements in attack.');
                }
                if (!is_array($value[0]) || !is_array($value[1])) {
                    throw new InvalidArgumentException(
                        'The first two elements in attack must be arrays.');
                }
                $this->attack = $value;
                break;
//    private $passStatusArray;       // boolean array whether each player passed
//    private $capturedDieArrayArray; // captured dice for all players
//    private $roundScoreArray;       // current points score in this round
            case 'gameScoreArray':
                if (count($this->playerIdxArray) != count($value)) {
                    throw new InvalidArgumentException(
                        'Invalid number of W/L/T results provided.');
                }
                $tempArray = array();
                for ($playerIdx = 0; $playerIdx < count($value); $playerIdx++) {
                    // check whether there are three inputs and they are all positive
                    if ((3 !== count($value[$playerIdx])) ||
                        min(array_map('min', $value)) < 0) {
                        throw new InvalidArgumentException(
                            'Invalid W/L/T array provided.');
                    }
                    $tempArray[$playerIdx] = array('W' => $value[$playerIdx][0],
                                                   'L' => $value[$playerIdx][1],
                                                   'D' => $value[$playerIdx][2]);
                }
                $this->gameScoreArray = $tempArray;
                break;
//    private $maxWins;               // the game ends when a player has this many wins
//    private $gameState;             // current game state as a BMGameState enum
            default:
                $this->$property = $value;
        }
    }

    public function __isset($property) {
        return isset($this->$property);
    }

    public function __unset($property) {
        if (isset($this->$property)) {
            unset($this->$property);
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

class BMGameState {
    // pre-game
    const startGame = 10;
    const applyHandicaps = 11;
    const chooseAuxiliaryDice = 12;

    // pre-round
    const loadDiceIntoButtons = 20;
    const specifyDice = 21;
    const addAvailableDiceToGame = 22;
    const determineInitiative = 29;

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
