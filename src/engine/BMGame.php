<?php

require_once 'BMButton.php';

/**
 * BMGame: current status of a game
 *
 * @author james
 */
class BMGame {
    // properties -- all accessible, but written as private to enable the use of
    //               getters and setters
    private $gameId;                // game ID number in the database
    private $playerIdxArray;        // array of player IDs
    private $activePlayerIdx;       // index of the active player in playerIdxArray
    private $playerWithInitiativeIdx; // index of the player who won initiative
    private $buttonArray;           // buttons for all players
    private $activeDieArrayArray;   // active dice for all players
    private $attack;                // array('attackerPlayerIdx',
                                    //       'defenderPlayerIdx',
                                    //       'attackerDieIdxArray',
                                    //       'defenderDieIdxArray',
                                    //       'attackType')
    private $attackerPlayerIdx;     // index in playerIdxArray of the attacker
    private $defenderPlayerIdx;     // index in playerIdxArary of the defender
    private $attackerAllDieArray;   // array of all attacker's dice
    private $defenderAllDieArray;   // array of all defender's dice
    private $attackerAttackDieArray; // array of attacker's dice used in attack
    private $defenderAttackDieArray; // array of defender's dice used in attack
    private $auxiliaryDieDecisionArrayArray; // array storing player decisions about auxiliary dice
    private $passStatusArray;       // boolean array whether each player passed
    private $capturedDieArrayArray; // captured dice for all players
    private $roundScoreArray;       // current points score in this round
    private $gameScoreArrayArray;   // number of games W/T/L for all players
    private $lastWinnerIdxArray;    // indices of the winners of the last round
    private $maxWins;               // the game ends when a player has this many wins
    private $gameState;             // current game state as a BMGameState enum
    private $waitingOnActionArray;  // boolean array whether each player needs to perform an action

    public $swingrequest;

    public function request_swing_values($die, $swingtype) {
        $this->swingrequest = array($die, $swingtype);
    }

    public $all_values_specified = FALSE;

    public function require_values() {
        if (!$this->all_values_specified) {
            throw new Exception("require_values called");
        }
    }


    // methods
    public function do_next_step() {
        if (!isset($this->gameState)) {
            throw new LogicException('Game state must be set.');
        }

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
                $this->gameScoreArrayArray =
                    array_pad(array(),
                              count($this->playerIdxArray),
                              array('W' => 0, 'L' => 0, 'T' => 0));
                break;

            case BMGameState::chooseAuxiliaryDice:
                $auxiliaryDice = '';
                // create list of auxiliary dice
                foreach ($this->buttonArray as $tempButton) {
                    if (BMGame::does_recipe_have_auxiliary_dice($tempButton->recipe)) {
                        $tempSplitArray = BMGame::separate_out_auxiliary_dice(
                                              $tempButton->recipe);
                        $auxiliaryDice = $auxiliaryDice.' '.$tempSplitArray[1];
                    }
                }
                $auxiliaryDice = trim($auxiliaryDice);
                // update $auxiliaryDice based on player choices
                $this->activate_GUI('ask_all_players_about_auxiliary_dice', $auxiliaryDice);

                //james: current default is to accept all auxiliary dice

                // update all button recipes and remove auxiliary markers
                if (!empty($auxiliaryDice)) {
                    foreach ($this->buttonArray as $buttonIdx => $tempButton) {
                        $separatedDice = BMGame::separate_out_auxiliary_dice
                                             ($tempButton->recipe);
                        $tempButton->recipe = $separatedDice[0].' '.$auxiliaryDice;
                    }
                }
                $this->save_game_to_database();
                break;

            case BMGameState::loadDiceIntoButtons: // may become loadContainersIntoButtons
                // load clean version of the buttons from their recipes
                // if the player has not just won a round
                foreach ($this->buttonArray as $playerIdx => $tempButton) {
                    if (!$this->lastWinnerIdxArray[$playerIdx]) {
                        $tempButton->reload();
                    }
                }
                break;

            case BMGameState::specifyDice: // may become openContainersIntoButtons
                // james: BMContainer->activate() will probably be used here
                // specify swing, option, and plasma dice
                // update BMButton dieArray
                break;

            case BMGameState::addAvailableDiceToGame;
                // load BMGame activeDieArrayArray from BMButton dieArray
                $this->activeDieArrayArray = array();

                foreach ($this->buttonArray as $buttonIdx => $tempButton) {
                    $this->activeDieArrayArray[$buttonIdx] = array();

                    foreach ($tempButton->dieArray as $tempDie) {
                        $this->activeDieArrayArray[$buttonIdx][] =
                            $tempDie->make_play_die();
                    }
                }
                break;

            case BMGameState::determineInitiative:
                $initiativeArrayArray = array();
                foreach ($this->activeDieArrayArray as $playerIdx => $tempActiveDieArray) {
                    $initiativeArrayArray[] = array();
                    foreach ($tempActiveDieArray as $dieIdx => $tempDie) {
                        // update initiative arrays if die counts for initiative
                        $tempInitiative = $tempDie->initiative_value();
                        if ($tempInitiative > 0) {
                            $initiativeArrayArray[$playerIdx][] = $tempInitiative;
                        }
                    }
                    sort($initiativeArrayArray[$playerIdx]);
                }

                // determine player that has won initiative
                $nPlayers = count($this->playerIdxArray);
                $doesPlayerHaveInitiative = array_pad(array(), $nPlayers, TRUE);

                $dieIdx = 0;
                while (array_sum($doesPlayerHaveInitiative) >= 2) {
                    $dieValues = array();
                    foreach($initiativeArrayArray as $tempInitiativeArray) {
                        if (isset($tempInitiativeArray[$dieIdx])) {
                            $dieValues[] = $tempInitiativeArray[$dieIdx];
                        } else {
                            $dieValues[] = PHP_INT_MAX;
                        }
                    }
                    $minDieValue = min($dieValues);
                    if (PHP_INT_MAX === $minDieValue) {
                        break;
                    }
                    for ($playerIdx = 0; $playerIdx <= $nPlayers - 1; $playerIdx++) {
                        if ($dieValues[$playerIdx] > $minDieValue) {
                            $doesPlayerHaveInitiative[$playerIdx] = FALSE;
                        }
                    }
                    $dieIdx++;
                }
                if (array_sum($doesPlayerHaveInitiative) > 1) {
                    $playersWithInitiative = array();
                    foreach ($doesPlayerHaveInitiative as $playerIdx => $tempHasInitiative) {
                        if ($tempHasInitiative) {
                            $playersWithInitiative[] = $playerIdx;
                        }
                    }
                    $tempPlayerWithInitiativeIdx = array_rand($playersWithInitiative);
                } else {
                    $tempPlayerWithInitiativeIdx =
                        array_search(TRUE, $doesPlayerHaveInitiative, TRUE);
                }

                // james: not yet programmed
                // if there are focus or chance dice, determine if they might make a difference
                if (FALSE) {
                    // if so, then ask player to make decisions
                    $this->activate_GUI('ask_player_about_focus_dice');
                    $this->save_game_to_database();
                    break;
                }

                // if no more decisions, then set BMGame->playerWithInitiativeIdx
                $this->playerWithInitiativeIdx = $tempPlayerWithInitiativeIdx;
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
                $this->activate_GUI('show_active_dice');

                // while invalid attack {ask player to select attack}
                while (!$this->is_valid_attack()) {
                    $this->activate_GUI('wait_for_attack');
                    $this->save_game_to_database();
                    break;
                }

                // perform attack
                // update $this->activeDieArrayArray
                // update $this->attack['attackingDieIdxArray']
                // update $this->attack['targetDieIdxArray']
                // update $isAttackSuccessful
                $isAttackSuccessful = TRUE;


                // reroll all dice involved in the attack that are still active
                $attackerPlayerIdx = $this->attack['attackerPlayerIdx'];
                $attackerDieIdxArray = $this->attack['attackerAttackDieIdxArray'];
                foreach ($attackerDieIdxArray as $dieIdx => $tempAttackerDieIdx) {
                    $this->activeDieArrayArray[$attackerPlayerIdx][
                               $tempAttackerDieIdx]->roll($isAttackSuccessful);
                }

                $defenderPlayerIdx = $this->attack['defenderPlayerIdx'];
                $defenderDieIdxArray = $this->attack['defenderAttackDieIdxArray'];
                foreach ($defenderDieIdxArray as $dieIdx => $tempDefenderDieIdx) {
                    $this->activeDieArrayArray[$defenderPlayerIdx][
                               $tempDefenderDieIdx]->roll($isAttackSuccessful);
                }

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
        }
    }

    public function update_game_state() {
        if (!isset($this->gameState)) {
            throw new LogicException('Game state must be set.');
        }

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
                    foreach($this->gameScoreArrayArray as $tempGameScoreArray) {
                        if ($nWins < $tempGameScoreArray['W']) {
                            $nWins = $tempGameScoreArray['W'];
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
                foreach ($this->gameScoreArrayArray as $tempGameScoreArray) {
                    if ($tempGameScoreArray['W'] >= $this->maxWins) {
                        $this->gameState = BMGameState::endGame;
                        break;
                    }
                }
                break;

            case BMGameState::endGame:
                break;
        }
    }

    public function proceed_to_next_user_action() {
        while (0 === array_sum($this->waitingOnActionArray)) {
            $this->update_game_state();
            $this->do_next_step();
            if (BMGameState::endGame === $this->gameState) {
                break;
            }
        }
    }

    public function add_die($die, $playerIdx) {
        if (!isset($this->activeDieArrayArray)) {
            throw new LogicException(
                'activeDieArrayArray must be set before a die can be added.');
        }

        $this->activeDieArrayArray[$playerIdx][] = $die;
    }

    public function capture_die($die, $newOwnerIdx = NULL) {
        $dieIdx = array_search($die, $this->activeDieArrayArray[
                                                $this->attack['defenderPlayerIdx']], TRUE);
        if (FALSE === $dieIdx) {
            throw new LogicException(
                'Captured die does not exist for the defender.');
        }

        // add captured die to captured die array
        if (is_null($newOwnerIdx)) {
            $newOwnerIdx = $this->attack['attackerPlayerIdx'];
        }
        $defenderPlayerIdx = $this->attack['defenderPlayerIdx'];
        $this->capturedDieArrayArray[$newOwnerIdx][] =
            $this->activeDieArrayArray[$defenderPlayerIdx][$dieIdx];
        // remove captured die from defender's active die array
        array_splice($this->activeDieArrayArray[$defenderPlayerIdx], $dieIdx, 1);
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

        foreach ($dieRecipeArray as $tempDieRecipe) {
            if (FALSE === strpos($tempDieRecipe, '+')) {
                $nonAuxiliaryDice = $nonAuxiliaryDice.$tempDieRecipe.' ';
            } else {
                $strippedDieRecipe = str_replace('+', '', $tempDieRecipe);
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

    private function reset_play_state() {
        unset($this->activePlayerIdx);
        unset($this->playerWithInitiativeIdx);
        unset($this->activeDieArrayArray);
        unset($this->roundScoreArray);

        $nPlayers = count($this->playerIdxArray);
        $this->passStatusArray = array_pad(array(), $nPlayers, FALSE);
        $this->capturedDieArrayArray = array_pad(array(), $nPlayers, array());
        $this->waitingOnActionArray = array_pad(array(), $nPlayers, FALSE);
    }

    private function update_active_player() {
        assert(isset($this->activePlayerIdx));

        // move to the next player
        $this->activePlayerIdx = ($this->activePlayerIdx + 1) %
                                 count($this->playerIdxArray);
    }

    // utility methods
    public function __construct($gameID = 0,
                                array $playerIdxArray = array(0, 0),
                                array $buttonRecipeArray = array('', ''),
                                $maxWins = 3) {
        if (count($playerIdxArray) !== count($buttonRecipeArray)) {
            throw new InvalidArgumentException(
                'Number of buttons must equal the number of players.');
        }

        $nPlayers = count($playerIdxArray);
        $this->gameId = $gameID;
        $this->playerIdxArray = $playerIdxArray;
        $this->gameState = BMGameState::startGame; // james
        $this->waitingOnActionArray = array_pad(array(), $nPlayers, FALSE);
        foreach ($buttonRecipeArray as $tempRecipe) {
            $tempButton = new BMButton;
            $tempButton->load_from_recipe($tempRecipe);
            $this->buttonArray[] = $tempButton;
        }
        $this->maxWins = $maxWins;
        $this->lastWinnerIdxArray = array_pad(array(), $nPlayers, FALSE);
    }

    // to allow array elements to be set directly, change the __get to &__get
    // to return the result by reference
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            switch ($property) {
                case 'attackerPlayerIdx':
                    if (!isset($this->attack)) {
                        return NULL;
                    }
                    return $this->attack['attackerPlayerIdx'];
                case 'defenderPlayerIdx':
                    if (!isset($this->attack)) {
                        return NULL;
                    }
                    return $this->attack['defenderPlayerIdx'];
                case 'attackerAllDieArray':
                    if (!isset($this->attack) ||
                        !isset($this->activeDieArrayArray)) {
                        return NULL;
                    }
                    return $this->activeDieArrayArray[$this->attack['attackerPlayerIdx']];
                case 'defenderAllDieArray':
                    if (!isset($this->attack) ||
                        !isset($this->activeDieArrayArray)) {
                        return NULL;
                    }
                    return $this->activeDieArrayArray[$this->attack['defenderPlayerIdx']];
                case 'attackerAttackDieArray':
                    if (!isset($this->attack)) {
                        return NULL;
                    }
                    $attackerAttackDieArray = array();
                    foreach ($this->attack['attackerAttackDieIdxArray'] as $attackerAttackDieIdx) {
                        $attackerAttackDieArray[] =
                            $this->activeDieArrayArray[$this->attack['attackerPlayerIdx']]
                                                      [$attackerAttackDieIdx];
                    }
                    return $attackerAttackDieArray;
                case 'defenderAttackDieArray':
                    if (!isset($this->attack)) {
                        return NULL;
                    }
                    $defenderAttackDieArray = array();
                    foreach ($this->attack['defenderAttackDieIdxArray'] as $defenderAttackDieIdx) {
                        $defenderAttackDieArray[] =
                            $this->activeDieArrayArray[$this->attack['defenderPlayerIdx']]
                                                      [$defenderAttackDieIdx];
                    }
                    return $defenderAttackDieArray;
                default:
                    return $this->$property;
            }
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
                foreach ($value as $tempValueElement) {
                    if (!($tempValueElement instanceof BMButton)) {
                        throw new InvalidArgumentException(
                            'Input must be an array of BMButtons.');
                    }
                }
                $this->buttonArray = $value;
                break;
            case 'activeDieArrayArray':
                if (!is_array($value)) {
                    throw new InvalidArgumentException(
                        'Active die array array must be an array.');
                }
                foreach ($value as $tempValueElement) {
                    if (!is_array($tempValueElement)) {
                        throw new InvalidArgumentException(
                            'Individual active die arrays must be arrays.');
                    }
                    foreach ($tempValueElement as $die) {
                        if (!($die instanceof BMDie)) {
                            throw new InvalidArgumentException(
                                'Elements of active die arrays must be BMDice.');
                        }
                    }
                }
                $this->activeDieArrayArray = $value;
                break;
            case 'attack':
                if (!is_array($value) || (5 !== count($value))) {
                    throw new InvalidArgumentException(
                        'There must be exactly five elements in attack.');
                }
                if (!is_integer($value[0]) || !is_integer($value[1])) {
                    throw new InvalidArgumentException(
                        'The first and second elements in attack must be integers.');
                }
                if (!is_array($value[2]) || !is_array($value[3])) {
                    throw new InvalidArgumentException(
                        'The third and fourth elements in attack must be arrays.');
                }
                if (($value[2] !== array_filter($value[2], 'is_int')) ||
                    ($value[3] !== array_filter($value[3], 'is_int'))) {
                    throw new InvalidArgumentException(
                        'The third and fourth elements in attack must contain integers.');
                }
                $this->attack = array('attackerPlayerIdx' => $value[0],
                                      'defenderPlayerIdx' => $value[1],
                                      'attackerAttackDieIdxArray' => $value[2],
                                      'defenderAttackDieIdxArray' => $value[3],
                                      'attackType' => $value[4]);
                break;
            case 'attackerAttackDieArray':
                throw new LogicException('
                    BMGame->attackerAttackDieArray is derived from BMGame->attack.');
                break;
            case 'defenderAttackDieArray':
                throw new LogicException('
                    BMGame->defenderAttackDieArray is derived from BMGame->attack.');
                break;
            case 'passStatusArray':
                if ((!is_array($value)) ||
                    (count($this->playerIdxArray) !== count($value))) {
                    throw new InvalidArgumentException(
                        'The number of elements in passStatusArray must be the number of players.');
                }
                // require boolean pass statuses
                foreach ($value as $tempValueElement) {
                    if (!is_bool($tempValueElement)) {
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
                foreach ($value as $tempValueElement) {
                    if (!is_array($tempValueElement)) {
                        throw new InvalidArgumentException(
                            'Individual captured die arrays must be arrays.');
                    }
                    foreach ($tempValueElement as $tempDie) {
                        if (!($tempDie instanceof BMDie)) {
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
                foreach ($value as $tempValueElement) {
                    if (FALSE === filter_var($tempValueElement, FILTER_VALIDATE_FLOAT)) {
                        throw new InvalidArgumentException(
                            'Round scores must be numeric.');
                    }
                }
                if (FALSE === filter_var($value,
                                         FILTER_VALIDATE_INT,
                                         array("options"=>
                                               array("min_range"=>0))))

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
            case 'waitingOnActionArray':
                if (!is_array($value) ||
                    count($value) !== count($this->playerIdxArray)) {
                    throw new InvalidArgumentException(
                        'Number of actions must equal the number of players.');
                }
                foreach ($value as $tempValueElement) {
                    if (!is_bool($tempValueElement)) {
                        throw new InvalidArgumentException(
                            'Input must be an array of booleans.');
                    }
                }
                $this->waitingOnActionArray = $value;
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
