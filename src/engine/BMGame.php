<?php

/**
 * BMGame: current status of a game
 *
 * @author james
 *
 * @property      int   $gameId                  Game ID number in the database
 * @property      array $playerIdArray           Array of player IDs
 * @property-read array $nPlayers                Number of players in the game
 * @property-read int   $roundNumber;            Current round number
 * @property      int   $activePlayerIdx         Index of the active player in playerIdxArray
 * @property      int   $playerWithInitiativeIdx Index of the player who won initiative
 * @property      array $buttonArray             Buttons for all players
 * @property-read array $activeDieArrayArray     Active dice for all players
 * @property      array $attack                  array('attackerPlayerIdx',<br>
                                                       'defenderPlayerIdx',<br>
                                                       'attackerAttackDieIdxArray',<br>
                                                       'defenderAttackDieIdxArray',<br>
                                                       'attackType')
 * @property-read int   $attackerPlayerIdx       Index in playerIdxArray of the attacker
 * @property-read int   $defenderPlayerIdx       Index in playerIdxArray of the defender
 * @property-read array $attackerAllDieArray     Array of all attacker's dice
 * @property-read array $defenderAllDieArray     Array of all defender's dice
 * @property-read array $attackerAttackDieArray  Array of attacker's dice used in attack
 * @property-read array $defenderAttackDieArray  Array of defender's dice used in attack
 * @property      array $auxiliaryDieDecisionArrayArray Array storing player decisions about auxiliary dice
 * @property-read array $passStatusArray         Boolean array whether each player passed
 * @property-read array $capturedDieArrayArray   Captured dice for all players
 * @property-read array $roundScoreArray         Current points score in this round
 * @property-read array $gameScoreArrayArray     Number of games W/L/D for all players
 * @property-read array $isPrevRoundWinnerArray  Boolean array whether each player won the previous round
 * @property      int   $maxWins                 The game ends when a player has this many wins
 * @property-read BMGameState $gameState         Current game state as a BMGameState enum
 * @property      array $waitingOnActionArray    Boolean array whether each player needs to perform an action
 * @property-read string $message                Message to be passed to the GUI
 * @property      array $swingRequestArrayArray  Swing requests for all players
 * @property      array $swingValueArrayArray    Swing values for all players
 * @property    boolean $allValuesSpecified      Boolean flag of whether all swing values have been specified
 *
 */
class BMGame {
    // properties -- all accessible, but written as private to enable the use of
    //               getters and setters
    private $gameId;                // game ID number in the database
    private $playerIdArray;         // array of player IDs
    private $nPlayers;              // number of players in the game
    private $roundNumber;           // current round number
    private $activePlayerIdx;       // index of the active player in playerIdxArray
    private $playerWithInitiativeIdx; // index of the player who won initiative
    private $buttonArray;           // buttons for all players
    private $activeDieArrayArray;   // active dice for all players
    private $attack;                // array('attackerPlayerIdx',
                                    //       'defenderPlayerIdx',
                                    //       'attackerAttackDieIdxArray',
                                    //       'defenderAttackDieIdxArray',
                                    //       'attackType')
    private $attackerPlayerIdx;     // index in playerIdxArray of the attacker
    private $defenderPlayerIdx;     // index in playerIdxArray of the defender
    private $attackerAllDieArray;   // array of all attacker's dice
    private $defenderAllDieArray;   // array of all defender's dice
    private $attackerAttackDieArray; // array of attacker's dice used in attack
    private $defenderAttackDieArray; // array of defender's dice used in attack
    private $auxiliaryDieDecisionArrayArray; // array storing player decisions about auxiliary dice
    private $passStatusArray;       // boolean array whether each player passed
    private $capturedDieArrayArray; // captured dice for all players
    private $roundScoreArray;       // current points score in this round
    private $gameScoreArrayArray;   // number of games W/L/D for all players
    private $isPrevRoundWinnerArray;// boolean array whether each player won the previous round
    private $maxWins;               // the game ends when a player has this many wins
    private $gameState;             // current game state as a BMGameState enum
    private $waitingOnActionArray;  // boolean array whether each player needs to perform an action
    private $message;               // message to be passed to the GUI

    public $swingRequestArrayArray;
    public $swingValueArrayArray;

    public $allValuesSpecified = FALSE;

    public function require_values() {
        if (!$this->allValuesSpecified) {
            throw new Exception("require_values called");
        }
    }


    // methods
    public function do_next_step() {
        if (!isset($this->gameState)) {
            throw new LogicException('Game state must be set.');
        }

        $this->message = 'ok';

        $this->run_die_hooks($this->gameState);

        switch ($this->gameState) {
            case BMGameState::startGame:
                // do_next_step is normally never run for BMGameState::startGame
                break;

            case BMGameState::applyHandicaps:
                // ignore for the moment
                $this->gameScoreArrayArray =
                    array_pad(array(),
                              count($this->playerIdArray),
                              array('W' => 0, 'L' => 0, 'D' => 0));
                break;

            case BMGameState::chooseAuxiliaryDice:
                // james: this game state will probably move to after loadDiceIntoButtons
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
                break;

            case BMGameState::loadDiceIntoButtons:
            //james: this will be replaced with a call to the database
                // load clean version of the buttons from their recipes
                // if the player has not just won a round
//                foreach ($this->buttonArray as $playerIdx => $tempButton) {
//                    if (!$this->isPrevRoundWinnerArray[$playerIdx]) {
//                        $tempButton->reload();
//                    }
//                }
                break;

            case BMGameState::addAvailableDiceToGame;
                // load BMGame activeDieArrayArray from BMButton dieArray
                $this->activeDieArrayArray =
                    array_pad(array(), $this->nPlayers, array());

                foreach ($this->buttonArray as $buttonIdx => $tempButton) {
                    $tempButton->activate();
                }
                break;

            case BMGameState::specifyDice:
                $this->waitingOnActionArray =
                    array_pad(array(), count($this->playerIdArray), FALSE);

                if (isset($this->swingRequestArrayArray)) {
                    foreach ($this->swingRequestArrayArray as $playerIdx => $swingRequestArray) {
                        $keyArray = array_keys($swingRequestArray);

                        // initialise swingValueArrayArray if necessary
                        if (!isset($this->swingValueArrayArray[$playerIdx])) {
                            $this->swingValueArrayArray[$playerIdx] = array();
                        }

                        foreach ($keyArray as $key) {
                            // copy swing request keys to swing value keys if they
                            // do not already exist
                            if (!array_key_exists($key, $this->swingValueArrayArray[$playerIdx])) {
                                $this->swingValueArrayArray[$playerIdx][$key] = NULL;
                            }

                            // set waitingOnActionArray based on if there are
                            // unspecified swing dice for that player
                            if (is_null($this->swingValueArrayArray[$playerIdx][$key])) {
                                $this->waitingOnActionArray[$playerIdx] = TRUE;
                            }
                        }
                    }

                    foreach ($this->waitingOnActionArray as $playerIdx => $waitingOnAction) {
                        if ($waitingOnAction) {
                            $this->activate_GUI('Waiting on player action.', $playerIdx);
                        } else {
                            // apply swing values
                            foreach ($this->activeDieArrayArray[$playerIdx] as $die) {
                                if ($die instanceof BMDieSwing) {
                                    $isSetSuccessful = $die->set_swingValue(
                                        $this->swingValueArrayArray[$playerIdx]);
                                    // act appropriately if the swing values are invalid
                                    if (!$isSetSuccessful) {
                                        $this->activate_GUI('Incorrect swing values chosen.', $playerIdx);
                                        $this->swingValueArrayArray[$playerIdx] = array();
                                        $this->waitingOnActionArray[$playerIdx] = TRUE;
                                        break 3;
                                    }
                                }
                            }
                        }
                    }
                }

                // roll dice
                foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
                    foreach ($activeDieArray as $dieIdx => $die) {
                        if ($die instanceof BMDieSwing) {
                            if ($die->needsValue) {
                                // swing value has not yet been set
                                continue;
                            }
                        }
                        $this->activeDieArrayArray[$playerIdx][$dieIdx] =
                            $die->make_play_die(FALSE);
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
                $nPlayers = count($this->playerIdArray);
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
                    $this->waitingOnActionArray[$this->activePlayerIdx] = TRUE;
                    return;
                }

                // perform attack
                $attack = BMAttack::get_instance($this->attack['attackType']);

                $this->attackerPlayerIdx = $this->attack['attackerPlayerIdx'];
                $this->defenderPlayerIdx = $this->attack['defenderPlayerIdx'];
                $attackerAttackDieArray = array();
                foreach ($this->attack['attackerAttackDieIdxArray'] as $attackerAttackDieIdx) {
                    $attackerAttackDieArray[] =
                        $this->activeDieArrayArray[$this->attack['attackerPlayerIdx']]
                                                  [$attackerAttackDieIdx];
                }
                $defenderAttackDieArray = array();
                foreach ($this->attack['defenderAttackDieIdxArray'] as $defenderAttackDieIdx) {
                    $defenderAttackDieArray[] =
                        $this->activeDieArrayArray[$this->attack['defenderPlayerIdx']]
                                                  [$defenderAttackDieIdx];
                }

                foreach ($attackerAttackDieArray as $attackDie) {
                    $attack->add_die($attackDie);
                }

                $possible = $attack->find_attack($this);
                if ($possible) {
                    $valid = $attack->validate_attack($this,
                                                      $attackerAttackDieArray,
                                                      $defenderAttackDieArray);
                } else {
                    $valid = FALSE;
                }

                if (!$valid) {
                    $this->activate_GUI('Invalid attack');
                    $this->waitingOnActionArray[$this->activePlayerIdx] = TRUE;
                    $this->attack = NULL;
                    return;
                }

                $attack->commit_attack($this, $attackerAttackDieArray, $defenderAttackDieArray);
                $this->update_active_player();
                break;

            case BMGameState::endTurn:
                break;

            case BMGameState::endRound:
                $roundScoreArray = $this->get_roundScoreArray();

                // check for draw currently assumes only two players
                $isDraw = $roundScoreArray[0] == $roundScoreArray[1];

                if ($isDraw) {
                    for ($playerIdx = 0; $playerIdx < $this->nPlayers; $playerIdx++) {
                        $this->gameScoreArrayArray[$playerIdx]['D']++;
                        // james: currently there is no code for three draws in a row
                    }
                } else {
                    $winnerIdx = array_search(max($roundScoreArray), $roundScoreArray);

                    for ($playerIdx = 0; $playerIdx < $this->nPlayers; $playerIdx++) {
                        if ($playerIdx == $winnerIdx) {
                            $this->gameScoreArrayArray[$playerIdx]['W']++;
                        } else {
                            $this->gameScoreArrayArray[$playerIdx]['L']++;
                            $this->swingValueArrayArray[$playerIdx] = array();
                        }
                    }
                }
                $this->reset_play_state();
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
                $this->reset_play_state();

                // if buttons are unspecified, allow players to choose buttons
                for ($playerIdx = 0, $nPlayers = count($this->playerIdArray);
                     $playerIdx <= $nPlayers - 1;
                     $playerIdx++) {
                    if (!isset($this->buttonArray[$playerIdx])) {
                        $this->waitingOnActionArray[$playerIdx] = TRUE;
                        $this->activate_GUI('Prompt for button ID', $playerIdx);
                    }
                }

                // require both players and buttons to be specified
                $allButtonsSet = count($this->playerIdArray) === count($this->buttonArray);

                if (!in_array(0, $this->playerIdArray) &&
                    $allButtonsSet) {
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
                if (!isset($this->buttonArray)) {
                    throw new LogicException(
                        'Button array must be set before loading dice into buttons.');
                }

                $buttonsLoadedWithDice = TRUE;
                foreach ($this->buttonArray as $tempButton) {
                    if (!isset($tempButton->dieArray)) {
                        $buttonsLoadedWithDice = FALSE;
                        break;
                    }
                }
                if ($buttonsLoadedWithDice) {
                    $this->gameState = BMGameState::addAvailableDiceToGame;
                }
                break;

            case BMGameState::addAvailableDiceToGame;
                if (isset($this->activeDieArrayArray)) {
                    $this->gameState = BMGameState::specifyDice;
                }
                break;

            case BMGameState::specifyDice:
                $areAllDiceSpecified = TRUE;
                foreach ($this->activeDieArrayArray as $activeDieArray) {
                    foreach ($activeDieArray as $tempDie) {
                        if (!$this->is_die_specified($tempDie)) {
                            $areAllDiceSpecified = FALSE;
                            break 2;
                        }
                    }
                }
                if ($areAllDiceSpecified) {
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
                if ($this->is_valid_attack() &&
                    FALSE === array_search(TRUE, $this->waitingOnActionArray, TRUE)) {
                    $this->gameState = BMGameState::endTurn;
                }
                break;

            case BMGameState::endTurn:
                $nDice = array_map("count", $this->activeDieArrayArray);
                // check if any player has no dice, or if everyone has passed
                if ((0 === min($nDice)) ||
                    !in_array(FALSE, $this->passStatusArray, TRUE)) {
                    $this->gameState = BMGameState::endRound;
                } else {
                    $this->gameState = BMGameState::startTurn;
                    $this->waitingOnActionArray[$this->activePlayerIdx] = TRUE;
                }
                $this->attack = NULL;
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
        $repeatCount = 0;
        $this->update_game_state();
        $this->do_next_step();

        while (0 === array_sum($this->waitingOnActionArray)) {
            $startGameState = $this->gameState;
            $this->update_game_state();
            $this->do_next_step();
            if (BMGameState::endGame === $this->gameState) {
                break;
            }

            if ($startGameState === $this->gameState) {
                $repeatCount++;
            } else {
                $repeatCount = 0;
            }
            if ($repeatCount >= 100) {
                throw new LogicException(
                    'Infinite loop detected when advancing game state.');
            }
        }
    }

    protected function run_die_hooks($gameState) {
        if (!empty($this->activeDieArrayArray)) {
            foreach ($this->activeDieArrayArray as $activeDieArray) {
                foreach ($activeDieArray as $activeDie) {
                    $activeDie->run_hooks_at_game_state($gameState, $this->attackerPlayerIdx);
                }
            }
        }

        if (!empty($this->capturedDieArrayArray)) {
            foreach ($this->capturedDieArrayArray as $capturedDieArray) {
                foreach ($capturedDieArray as $capturedDie) {
                    $capturedDie->run_hooks_at_game_state($gameState, $this->attackerPlayerIdx);
                }
            }
        }
    }

    public function add_die($die) {
        if (!isset($this->activeDieArrayArray)) {
            throw new LogicException(
                'activeDieArrayArray must be set before a die can be added.');
        }

        $this->activeDieArrayArray[$die->playerIdx][] = $die;
    }

    public function capture_die($die, $newOwnerIdx = NULL) {
        if (!isset($this->activeDieArrayArray)) {
            throw new LogicException(
                'activeDieArrayArray must be set before capturing dice.');
        }

        foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
            $dieIdx = array_search($die, $activeDieArray, TRUE);
            if (FALSE !== $dieIdx) {
                break;
            }
        }

        if (FALSE === $dieIdx) {
            throw new LogicException(
                'Captured die does not exist.');
        }

        // add captured die to captured die array
        if (is_null($newOwnerIdx)) {
            $newOwnerIdx = $this->attack['attackerPlayerIdx'];
        }
        $this->capturedDieArrayArray[$newOwnerIdx][] =
            $this->activeDieArrayArray[$playerIdx][$dieIdx];
        // remove captured die from active die array
        array_splice($this->activeDieArrayArray[$playerIdx], $dieIdx, 1);
    }

    public function request_swing_values($die, $swingtype, $playerIdx) {
        if (!isset($this->swingRequestArrayArray)) {
            $this->swingRequestArrayArray =
                array_pad(array(), $this->nPlayers, array());
        }
        $this->swingRequestArrayArray[$playerIdx][$swingtype][] = $die;
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

//        $sides = $die->max;

//        if (strlen(preg_replace('#[^[:alpha:]/]#', '', $sides)) > 0) {
//            return FALSE;
//        }

        // If plasma, then it is unspecified if the skills are unclear.
        // james: not written yet

        return (isset($die->max));
    }

    private function activate_GUI($activation_type, $input_parameters = NULL) {
        // currently acts as a placeholder
        $this->message = $this->message.'\n'.
                         $activation_type.' '.$input_parameters;
    }

    private function is_valid_attack() {
        if (isset($this->attack)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    private function reset_play_state() {
        $this->activePlayerIdx = NULL;
        $this->playerWithInitiativeIdx = NULL;
        $this->activeDieArrayArray = NULL;

        $nPlayers = count($this->playerIdArray);
        $this->passStatusArray = array_pad(array(), $nPlayers, FALSE);
        $this->capturedDieArrayArray = array_pad(array(), $nPlayers, array());
        $this->waitingOnActionArray = array_pad(array(), $nPlayers, FALSE);
    }

    private function update_active_player() {
        if (!isset($this->activePlayerIdx)) {
            throw new LogicException(
                'Active player must be set before it can be updated.');
        }

        $nPlayers = count($this->playerIdArray);
        // move to the next player
        $this->activePlayerIdx = ($this->activePlayerIdx + 1) % $nPlayers;

        // currently not waiting on anyone
        $this->waitingOnActionArray = array_pad(array(), $nPlayers, FALSE);
    }

    // utility methods
    public function __construct($gameID = 0,
                                array $playerIdArray = array(0, 0),
                                array $buttonRecipeArray = array('', ''),
                                $maxWins = 3) {
        if (count($playerIdArray) !== count($buttonRecipeArray)) {
            throw new InvalidArgumentException(
                'Number of buttons must equal the number of players.');
        }

        $nPlayers = count($playerIdArray);
        $this->nPlayers = $nPlayers;
        $this->gameId = $gameID;
        $this->playerIdArray = $playerIdArray;
        $this->gameState = BMGameState::startGame;
        $this->waitingOnActionArray = array_pad(array(), $nPlayers, FALSE);
        foreach ($buttonRecipeArray as $buttonIdx => $tempRecipe) {
            if (strlen($tempRecipe) > 0) {
                $tempButton = new BMButton;
                $tempButton->load($tempRecipe);
                $this->buttonArray[$buttonIdx] = $tempButton;
            }
        }
        $this->maxWins = $maxWins;
        $this->isPrevRoundWinnerArray = array_pad(array(), $nPlayers, FALSE);
    }

    private function get_roundNumber() {
        return(array_sum($this->gameScoreArrayArray[0]) + 1);
    }

    private function get_roundScoreArray() {
        $roundScoreTimesTenArray = array_pad(array(), $this->nPlayers, 0);
        $roundScoreArray = array_pad(array(), $this->nPlayers, 0);

        foreach ((array)$this->activeDieArrayArray as $playerIdx => $activeDieArray) {
            $activeDieScoreTimesTen = 0;
            foreach ($activeDieArray as $activeDie) {
                $activeDieScoreTimesTen += $activeDie->get_scoreValueTimesTen();
            }
            $roundScoreTimesTenArray[$playerIdx] = $activeDieScoreTimesTen;
        }

        foreach ((array)$this->capturedDieArrayArray as $playerIdx => $capturedDieArray) {
            $capturedDieScoreTimesTen = 0;
            foreach ($capturedDieArray as $capturedDie) {
                $capturedDieScoreTimesTen += $capturedDie->get_scoreValueTimesTen();
            }
            $roundScoreTimesTenArray[$playerIdx] += $capturedDieScoreTimesTen;
        }

        foreach ($roundScoreTimesTenArray as $playerIdx => $roundScoreTimesTen) {
            $roundScoreArray[$playerIdx] = $roundScoreTimesTen/10;
        }

        return $roundScoreArray;
    }

    // to allow array elements to be set directly, change the __get to &__get
    // to return the result by reference
    public function __get($property) {
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
                    if (!isset($this->attack) ||
                        !isset($this->activeDieArrayArray)) {
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
                case 'roundNumber':
                    return $this->get_roundNumber();
                case 'roundScoreArray':
                    return $this->get_roundScoreArray();
                default:
                    return $this->$property;
            }
        }
    }

    public function __set($property, $value) {
        switch ($property) {
            case 'nPlayers':
                throw new LogicException(
                    'nPlayers is derived from BMGame->playerIdArray');
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
            case 'playerIdArray':
                if (!is_array($value) ||
                    count($value) !== count($this->playerIdArray)) {
                    throw new InvalidArgumentException(
                        'The number of players cannot be changed during a game.');
                }
                $this->playerIdArray = $value;
                break;
            case 'activePlayerIdx':
                // require a valid index
                if (FALSE ===
                    filter_var($value,
                               FILTER_VALIDATE_INT,
                               array("options"=>
                                     array("min_range"=>0,
                                           "max_range"=>count($this->playerIdArray))))) {
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
                                           "max_range"=>count($this->playerIdArray))))) {
                    throw new InvalidArgumentException(
                        'Invalid player index.');
                }
                $this->playerWithInitiativeIdx = $value;
                break;
            case 'buttonArray':
                if (!is_array($value) ||
                    count($value) !== count($this->playerIdArray)) {
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
                foreach ($this->buttonArray as $playerIdx => $button) {
                    $button->playerIdx = $playerIdx;
                    $button->ownerObject = $this;
                }
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
                $value = array_values($value);
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

                if (!preg_match('/'.
                                'power'.'|'.
                                'skill'.'|'.
                                'shadow'.'|'.
                                'pass'.'/', $value[4])) {
                    throw new InvalidArgumentException(
                        'Invalid attack type.');
                }

                if (count($value[2]) > 0 &&
                    (max($value[2]) >
                         (count($this->activeDieArrayArray[$value[0]]) - 1) ||
                     min($value[2]) < 0)) {
                    throw new LogicException(
                        'Invalid attacker attack die indices.');
                }

                if (count($value[3]) > 0 &&
                    (max($value[3]) >
                         (count($this->activeDieArrayArray[$value[1]]) - 1) ||
                     min($value[3]) < 0)) {
                    throw new LogicException(
                        'Invalid defender attack die indices.');
                }

                $this->$property = array('attackerPlayerIdx' => $value[0],
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
                    (count($this->playerIdArray) !== count($value))) {
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
            case 'roundNumber':
                throw new LogicException('
                    BMGame->roundNumber is derived automatically from BMGame.');
                break;
            case 'roundScoreArray':
                throw new LogicException('
                    BMGame->roundScoreArray is derived automatically from BMGame.');
                break;
            case 'gameScoreArrayArray':
                $value = array_values($value);
                if (!is_array($value) ||
                    count($this->playerIdArray) !== count($value)) {
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
                $this->gameState = (int) $value;
                break;
            case 'waitingOnActionArray':
                if (!is_array($value) ||
                    count($value) !== count($this->playerIdArray)) {
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

    public function getJsonData() {
        foreach ($this->buttonArray as $button) {
            $buttonNameArray[] = $button->name;
        }

        foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
            $valueArrayArray[] = array();
            $sidesArrayArray[] = array();
            $dieRecipeArrayArray[] = array();
            if (empty($this->swingRequestArrayArray[$playerIdx])) {
                $swingRequestArrayArray[] = array();
            } else {
                $swingRequestArrayArray[] = array_keys($this->swingRequestArrayArray[$playerIdx]);
            }
            foreach ($activeDieArray as $die) {
                $valueArrayArray[$playerIdx][] = $die->value;
                $sidesArrayArray[$playerIdx][] = $die->max;
                $dieRecipeArrayArray[$playerIdx][] = $die->recipe;
            }
        }

        $dataArray =
            array('gameId'                  => $this->gameId,
                  'gameState'               => $this->gameState,
                  'roundNumber'             => $this->get_roundNumber(),
                  'activePlayerIdx'         => $this->activePlayerIdx,
                  'playerWithInitiativeIdx' => $this->playerWithInitiativeIdx,
                  'playerIdArray'           => $this->playerIdArray,
                  'buttonNameArray'         => $buttonNameArray,
                  'nDieArray'               => array_map('count', $this->activeDieArrayArray),
                  'valueArrayArray'         => $valueArrayArray,
                  'sidesArrayArray'         => $sidesArrayArray,
                  'dieRecipeArrayArray'     => $dieRecipeArrayArray,
                  'swingRequestArrayArray'  => $swingRequestArrayArray,
                  'roundScoreArray'         => $this->get_roundScoreArray(),
                  'gameScoreArrayArray'     => $this->gameScoreArrayArray);

        return array('status' => 'ok', 'data' => $dataArray);
    }
}

?>
