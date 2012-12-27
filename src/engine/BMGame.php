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
    private $auxiliaryDieDecisionArrayArray; // array storing player decisions about auxiliary dice
    private $passStatusArray;       // boolean array whether each player passed
    private $capturedDieArrayArray; // captured dice for all players
    private $roundScoreArray;       // current points score in this round
    private $gameScoreArrayArray;   // number of games W/T/L for all players
    private $maxWins;               // the game ends when a player has this many wins
    private $gameState;             // current game state as a BMGameState enum

    // methods
    public function do_next_step() {
        switch ($this->gameState) {
            case BMGameState::startGame:
                // first player must always be specified
                if (0 === $this->playerIdxArray[0]) {
                    throw new UnexpectedValueException(
                        'First player must be specified before game can be advanced.');
                }

                // if other players are unspecified, resolve this first
                if (in_array(0, array_slice($this->playerIdxArray, 1))) {
                    $this->activate_GUI('Prompt for player ID');
                    return;
                }

                // if buttons are unspecified, allow players to choose buttons
                foreach ($this->buttonArray as $tempButton) {
                    $this->activate_GUI('Prompt for button ID');
                }

                break;

            case BMGameState::applyHandicaps:
                // ignore for the moment
                break;

            case BMGameState::chooseAuxiliaryDice:
                $auxiliaryDice = '';
                // create list of auxiliary dice
                foreach ($this->buttonArray as $tempButton) {
                    if (BMGame::does_recipe_have_auxiliary_dice($tempButton->recipe)) {
                        $auxiliaryDice = $auxiliaryDice.' '.
                                         (BMGame::separate_out_auxiliary_dice(
                                              $tempButton->recipe)[1]);
                    }
                }
                $auxiliaryDice = trim($auxiliaryDice);
                // update $auxiliaryDice based on player choices
                $this->activate_GUI('ask_all_players_about_auxiliary_dice', $auxiliaryDice);

                //james: current default is to accept all auxiliary dice

                // update all button recipes and remove auxiliary markers
                if (!empty($auxiliaryDice)) {
                    for ($buttonIdx = 0;
                         $buttonIdx <= (count($this->buttonArray) - 1);
                         $buttonIdx++) {
                        $separatedDice = BMGame::separate_out_auxiliary_dice
                                             ($this->buttonArray[$buttonIdx]->recipe);
                        $this->buttonArray[$buttonIdx]->recipe =
                            $separatedDice[0].' '.$auxiliaryDice;
                    }
                }
                $this->save_game_to_database();
                break;

            case BMGameState::loadDiceIntoButtons:
                // currently, the BMButton activeDieArrays are automatically loaded
                // when the BMButton recipes are changed
                break;

            case BMGameState::specifyDice:
                // specify swing, option, and plasma dice
                // update BMButton dieArray
                break;

            case BMGameState::addAvailableDiceToGame;
                // load BMGame activeDieArrayArray from BMButton dieArray
                $this->activeDieArrayArray = array();
                foreach ($this->buttonArray as $tempButton) {
                    $this->activeDieArrayArray[] = $tempButton->dieArray;
                }
                break;

            case BMGameState::determineInitiative:
                // roll all dice using BMDie->first_roll()
                // determine initiative relevant dice by using BMDie->initiative_value()
                // determine player that has won initiative
                // if there are focus or chance dice, determine if they might make a difference
                // if so, then ask player to make decisions
                // if no more decisions, then set BMGame->playerWithInitiativeIdx
                break;

            case BMGameState::startRound:
                if (!isset($this->playerWithInitiativeIdx)) {
                    throw new LogicException(
                        'Player that has won initiative must already have been determined.');
                }
                // set BMGame activePlayerIdx
                $this->activePlayerIdx = $this->playerWithInitiativeIdx;
                break;

            case BMGameState::startTurn:
                // display dice
                // while invalid attack {ask player to select attack}
                // perform attack by updating BMGame activeDieArrayArray
                // reroll appropriate dice
                $this->update_active_player();
                break;

            case BMGameState::endTurn:
                break;

            case BMGameState::endRound:
                // score dice using BMDie->scoreValue()
                // update game score
                $this->reset_play_state();
                $this->save_game_to_database();
                break;

            case BMGameState::endGame:
                if (isset($this->activePlayerIdx)) {
                    // write stats to overall stats table
                    // i.e. update win/loss records for players and buttons
                    $this->reset_play_state();
                }
                $this->activate_GUI('Show end-of-game screen.');
                break;

            default:
                throw new LogicException ('An undefined game state cannot be performed.');
                break;
        }
    }

    public function update_game_state() {
        switch ($this->gameState) {
            case BMGameState::startGame:
                // require both players and buttons to be specified
                if (!in_array(0, $this->playerIdxArray) &&
                    isset($this->buttonArray)) {
                    $this->gameState = BMGameState::applyHandicaps;
                    $this->passStatusArray = array(FALSE, FALSE);
                    $this->gameScoreArrayArray = array(array(0, 0, 0), array(0, 0, 0));
                }
                break;

            case BMGameState::applyHandicaps:
                if (!isset($this->maxWins)) {
                    throw new LogicException(
                        'maxWins must be set before applying handicaps.');
                };
                if (isset($this->gameScoreArrayArray)) {
                    $nWins = 0;
                    foreach($this->gameScoreArrayArray as $gameScoreArray) {
                        if ($nWins < $gameScoreArray['W']) {
                            $nWins = $gameScoreArray['W'];
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
                }
                break;

            case BMGameState::endRound:
                if (isset($this->activePlayerIdx)) {
                    break;
                }
                // deal with reserve dice
                $this->gameState = BMGameState::loadDiceIntoButtons;
                foreach ($this->gameScoreArrayArray as $gameScoreArray) {
                    if ($gameScoreArray['W'] >= $this->maxWins) {
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

    public static function separate_out_auxiliary_dice($recipe) {
        $dieRecipeArray = explode(' ', $recipe);

        $nonAuxiliaryDice = '';
        $auxiliaryDice = '';

        foreach ($dieRecipeArray as $dieRecipe) {
            if (FALSE === strpos($dieRecipe, '+')) {
                $nonAuxiliaryDice = $nonAuxiliaryDice.$dieRecipe.' ';
            } else {
                $strippedDieRecipe = str_replace('+', '', $dieRecipe);
                $auxiliaryDice = $auxiliaryDice.$strippedDieRecipe.' ';
            }
        }

        $nonAuxiliaryDice = trim($nonAuxiliaryDice);
        $auxiliaryDice = trim($auxiliaryDice);

        return array($nonAuxiliaryDice, $auxiliaryDice);
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

    private function activate_GUI($activation_type, $input_parameters = NULL) {
        // currently acts as a placeholder
        $this->save_game_to_database();
    }

    private function save_game_to_database() {
        // currently acts as a placeholder
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
        $this->capturedDieArrayArray = $tempCapturedDiceArray;
        $this->passStatusArray = $tempPassStatusArray;
        unset($this->roundScoreArray);
    }

    private function update_active_player() {
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

    // to allow array elements to be set directly, change the __get to &__get
    // to return the result by reference
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value) {
        switch ($property) {
            case 'gameId':
                if (FALSE === filter_var($value,
                                         FILTER_VALIDATE_INT,
                                         array("options"=>
                                               array("min_range"=>0)))) {
                    throw new InvalidArgumentException(
                        'Invalid game ID.');
                }
                $this->gameId = $value;
                break;
            case 'playerIdxArray':
                if (!is_array($value) ||
                    count($value) !== count($this->playerIdxArray)) {
                    throw new InvalidArgumentException(
                        'The number of players cannot be changed during a game.');
                }
                $this->playerIdxArray = $value;
                break;
            case 'activePlayerIdx':
                // require a valid index
                if (FALSE ===
                    filter_var($value,
                               FILTER_VALIDATE_INT,
                               array("options"=>
                                     array("min_range"=>0,
                                           "max_range"=>count($this->playerIdxArray))))) {
                    throw new InvalidArgumentException(
                        'Invalid player index.');
                }
                $this->activePlayerIdx = $value;
                break;
            case 'playerWithInitiativeIdx':
                // require a valid index
                if (FALSE ===
                    filter_var($value,
                               FILTER_VALIDATE_INT,
                               array("options"=>
                                     array("min_range"=>0,
                                           "max_range"=>count($this->playerIdxArray))))) {
                    throw new InvalidArgumentException(
                        'Invalid player index.');
                }
                $this->playerWithInitiativeIdx = $value;
                break;
            case 'buttonArray':
                if (!is_array($value) ||
                    count($value) !== count($this->playerIdxArray)) {
                    throw new InvalidArgumentException(
                        'Number of buttons must equal the number of players.');
                }
                $this->buttonArray = $value;
                break;
            case 'activeDieArrayArray':
                if (!is_array($value)) {
                    throw new InvalidArgumentException(
                        'Active die array array must be an array.');
                }
                foreach ($value as $valueElement) {
                    if (!is_array($valueElement)) {
                        throw new InvalidArgumentException(
                            'Individual active die arrays must be arrays.');
                    }
                    foreach ($valueElement as $die) {
                        if (!is_a($die, 'BMDie')) {
                            throw new InvalidArgumentException(
                                'Elements of active die arrays must be BMDice.');
                        }
                    }
                }
                $this->activeDieArrayArray = $value;
                break;
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
            case 'passStatusArray':
                if ((!is_array($value)) ||
                    (count($this->playerIdxArray) !== count($value))) {
                    throw new InvalidArgumentException(
                        'The number of elements in passStatusArray must be the number of players.');
                }
                // require boolean pass statuses
                foreach ($value as $valueElement) {
                    if (!is_bool($valueElement)) {
                        throw new InvalidArgumentException(
                            'Pass statuses must be booleans.');
                    }
                }
                $this->passStatusArray = $value;
                break;
            case 'capturedDieArrayArray':
                if (!is_array($value)) {
                    throw new InvalidArgumentException(
                        'Captured die array array must be an array.');
                }
                foreach ($value as $valueElement) {
                    if (!is_array($valueElement)) {
                        throw new InvalidArgumentException(
                            'Individual captured die arrays must be arrays.');
                    }
                    foreach ($valueElement as $die) {
                        if (!is_a($die, 'BMDie')) {
                            throw new InvalidArgumentException(
                                'Elements of captured die arrays must be BMDice.');
                        }
                    }
                }
                $this->capturedDieArrayArray = $value;
                break;
            case 'roundScoreArray':
                if (!is_array($value) ||
                    (count($this->playerIdxArray) !== count($value))) {
                    throw new InvalidArgumentException(
                        'There must be one round score for each player.');
                }
                $this->roundScoreArray = $value;
                break;
            case 'gameScoreArrayArray':
                if (!is_array($value) ||
                    count($this->playerIdxArray) !== count($value)) {
                    throw new InvalidArgumentException(
                        'There must be one game score for each player.');
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
                $this->gameScoreArrayArray = $tempArray;
                break;
            case 'maxWins':
                if (FALSE === filter_var($value,
                                         FILTER_VALIDATE_INT,
                                         array("options"=>
                                               array("min_range"=>1)))) {
                    throw new InvalidArgumentException(
                        'maxWins must be a positive integer.');
                }
                $this->maxWins = $value;
                break;
            case 'gameState':
                BMGameState::validate_game_state($value);
                $this->gameState = $value;
                break;
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

    public static function validate_game_state($value) {
        if (FALSE === filter_var($value, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException(
                'Game state must be an integer.');
        }
        if (!in_array($value, array(BMGameState::startGame,
                                    BMGameState::applyHandicaps,
                                    BMGameState::chooseAuxiliaryDice,
                                    BMGameState::loadDiceIntoButtons,
                                    BMGameState::specifyDice,
                                    BMGameState::addAvailableDiceToGame,
                                    BMGameState::determineInitiative,
                                    BMGameState::startRound,
                                    BMGameState::startTurn,
                                    BMGameState::endTurn,
                                    BMGameState::endRound,
                                    BMGameState::endGame))) {
            throw new InvalidArgumentException(
                'Invalid game state.');
        }
    }
}

?>
