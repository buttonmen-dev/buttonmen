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
 * @property      int   $turnNumberInRound;      Current turn number in current round
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
 * @property-read int   $nRecentPasses           Number of consecutive passes
 * @property-read array $capturedDieArrayArray   Captured dice for all players
 * @property-read array $roundScoreArray         Current points score in this round
 * @property-read array $gameScoreArrayArray     Number of games W/L/D for all players
 * @property-read array $isPrevRoundWinnerArray  Boolean array whether each player won the previous round
 * @property      int   $maxWins                 The game ends when a player has this many wins
 * @property-read BMGameState $gameState         Current game state as a BMGameState enum
 * @property      array $waitingOnActionArray    Boolean array whether each player needs to perform an action
 * @property      array $autopassArray           Boolean array whether each player has enabled autopass
 * @property      array $actionLog               Game actions taken by this BMGame instance
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
    private $turnNumberInRound;     // current turn number in current round
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
    private $nRecentPasses;         // number of consecutive passes
    private $capturedDieArrayArray; // captured dice for all players
    private $roundScoreArray;       // current points score in this round
    private $gameScoreArrayArray;   // number of games W/L/D for all players
    private $isPrevRoundWinnerArray;// boolean array whether each player won the previous round
    private $maxWins;               // the game ends when a player has this many wins
    private $gameState;             // current game state as a BMGameState enum
    private $waitingOnActionArray;  // boolean array whether each player needs to perform an action
    private $autopassArray;         // boolean array whether each player has enabled autopass
    private $actionLog;             // game actions taken by this BMGame instance
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

        $this->debug_message = 'ok';

        $this->run_die_hooks($this->gameState);

        switch ($this->gameState) {
            case BMGameState::startGame:
                // do_next_step is normally never run for BMGameState::startGame
                break;

            case BMGameState::applyHandicaps:
                // ignore for the moment
                $this->gameScoreArrayArray =
                    array_fill(0,
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
                    array_fill(0, $this->nPlayers, array());

                foreach ($this->buttonArray as $buttonIdx => $tempButton) {
                    $tempButton->activate();
                }

                // load swing values that are carried across from a previous round
                if (!isset($this->swingValueArrayArray)) {
                    break;
                }

                foreach ($this->activeDieArrayArray as $playerIdx => &$activeDieArray) {
                    foreach ($activeDieArray as $dieIdx => &$activeDie) {
                        if ($activeDie instanceof BMDieSwing) {
                            if (array_key_exists($activeDie->swingType,
                                                 $this->swingValueArrayArray[$playerIdx])) {
                                $activeDie->swingValue =
                                    $this->swingValueArrayArray[$playerIdx][$activeDie->swingType];
                            }
                        }
                    }
                }
                break;

            case BMGameState::specifyDice:
                $this->waitingOnActionArray =
                    array_fill(0, count($this->playerIdArray), FALSE);

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
                                        $this->message = 'Invalid value submitted for swing die ' . $die->recipe;
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
                            if ($die->needsSwingValue) {
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
                $doesPlayerHaveInitiative = array_fill(0, $nPlayers, TRUE);

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
                $this->turnNumberInRound = 1;
                break;

            case BMGameState::startTurn:
                // deal with autopass
                if (!isset($this->attack) &&
                    $this->autopassArray[$this->activePlayerIdx] &&
                    $this->turnNumberInRound > 1) {
                    $validAttackTypes = $this->valid_attack_types();
                    if (array_search('Pass', $validAttackTypes) &&
                        (1 == count($validAttackTypes))) {
                        $this->attack = array('attackerPlayerIdx' => $this->activePlayerIdx,
                                              'defenderPlayerIdx' => NULL,
                                              'attackerAttackDieIdxArray' => array(),
                                              'defenderAttackDieIdxArray' => array(),
                                              'attackType' => 'Pass');
                    }
                }

                // display dice
                $this->activate_GUI('show_active_dice');

                // while attack has not been set {ask player to select attack}
                while (!isset($this->attack)) {
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
                        &$this->activeDieArrayArray[$this->attack['attackerPlayerIdx']]
                                                   [$attackerAttackDieIdx];
                }
                $defenderAttackDieArray = array();
                foreach ($this->attack['defenderAttackDieIdxArray'] as $defenderAttackDieIdx) {
                    $defenderAttackDieArray[] =
                        &$this->activeDieArrayArray[$this->attack['defenderPlayerIdx']]
                                                   [$defenderAttackDieIdx];
                }

                foreach ($attackerAttackDieArray as $attackDie) {
                    $attack->add_die($attackDie);
                }

                $valid = $attack->validate_attack($this,
                                                  $attackerAttackDieArray,
                                                  $defenderAttackDieArray);

                if (!$valid) {
                    $this->activate_GUI('Invalid attack');
                    $this->waitingOnActionArray[$this->activePlayerIdx] = TRUE;
                    $this->attack = NULL;
                    return;
                }

                $preAttackDice = $this->get_action_log_data(
                  $attackerAttackDieArray, $defenderAttackDieArray
                );

                $attack->commit_attack($this, $attackerAttackDieArray, $defenderAttackDieArray);
                $this->turnNumberInRound += 1;

                $postAttackDice = $this->get_action_log_data(
                  $attackerAttackDieArray, $defenderAttackDieArray
                );
                $this->log_attack($preAttackDice, $postAttackDice);

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
                    $this->log_action('end_draw', 0,
                       'Round ' . ($this->get_roundNumber() - 1) . ' ended in a draw (' .
                       $roundScoreArray[0] . ' vs. ' . $roundScoreArray[1] . ')' );
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
                    $this->log_action('end_winner', $this->playerIdArray[$winnerIdx],
                        'won round ' . ($this->get_roundNumber() - 1) . ' (' .
                        $roundScoreArray[0] . ' vs ' . $roundScoreArray[1] . ')' );
                }
                $this->reset_play_state();
                break;

            case BMGameState::endGame:
                $this->reset_play_state();
                // swingValueArrayArray must be reset to clear entries in the
                // database table game_swing_map
                $this->swingValueArrayArray = NULL;

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
                    $this->nRecentPasses = 0;
                    $this->autopassArray = array_fill(0, $this->nPlayers, FALSE);
                    $this->gameScoreArrayArray = array_fill(0, $this->nPlayers, array(0, 0, 0));
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
                if ((isset($this->attack)) &&
                    FALSE === array_search(TRUE, $this->waitingOnActionArray, TRUE)) {
                    $this->gameState = BMGameState::endTurn;
                }
                break;

            case BMGameState::endTurn:
                $nDice = array_map("count", $this->activeDieArrayArray);
                // check if any player has no dice, or if everyone has passed
                if ((0 === min($nDice)) ||
                    ($this->nPlayers == $this->nRecentPasses)) {
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
                // james: still need to deal with reserve dice
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
            $intermediateGameState = $this->gameState;
            $this->update_game_state();
            $this->do_next_step();

            if (BMGameState::endGame === $this->gameState) {
                break;
            }

            if ($intermediateGameState === $this->gameState) {
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
                array_fill(0, $this->nPlayers, array());
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

    public function valid_attack_types() {
        // james: assume two players at the moment
        $attackerIdx = $this->activePlayerIdx;
        $defenderIdx = ($attackerIdx + 1) % 2;

        $attackTypeArray = BMAttack::possible_attack_types($this->activeDieArrayArray[$attackerIdx]);

        $validAttackTypeArray = array();

        // find out if there are any possible attacks with any combination of
        // the attacker's and defender's dice
        foreach ($attackTypeArray as $idx => $attackType) {
            $this->attack = array('attackerPlayerIdx' => $attackerIdx,
                                  'defenderPlayerIdx' => $defenderIdx,
                                  'attackerAttackDieIdxArray' => range(0, count($this->activeDieArrayArray[$attackerIdx]) - 1),
                                  'defenderAttackDieIdxArray' => range(0, count($this->activeDieArrayArray[$defenderIdx]) - 1),
                                  'attackType' => $attackTypeArray[$idx]);
            $attack = BMAttack::get_instance($attackType);
            foreach ($this->activeDieArrayArray[$attackerIdx] as $attackDie) {
                $attack->add_die($attackDie);
            }
            if ($attack->find_attack($this)) {
                $validAttackTypeArray[$attackType] = $attackType;
            }
        }

        if (empty($validAttackTypeArray)) {
            $validAttackTypeArray['Pass'] = 'Pass';
        }

        return $validAttackTypeArray;
    }

    private function activate_GUI($activation_type, $input_parameters = NULL) {
        // currently acts as a placeholder
        $this->debug_message = $this->debug_message.'\n'.
                         $activation_type.' '.$input_parameters;
    }

    private function reset_play_state() {
        $this->activePlayerIdx = NULL;
        $this->playerWithInitiativeIdx = NULL;
        $this->activeDieArrayArray = NULL;

        $nPlayers = count($this->playerIdArray);
        $this->nRecentPasses = 0;
        $this->turnNumberInRound = 0;
        $this->capturedDieArrayArray = array_fill(0, $nPlayers, array());
        $this->waitingOnActionArray = array_fill(0, $nPlayers, FALSE);
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
        $this->waitingOnActionArray = array_fill(0, $nPlayers, FALSE);
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
        $this->waitingOnActionArray = array_fill(0, $nPlayers, FALSE);
        foreach ($buttonRecipeArray as $buttonIdx => $tempRecipe) {
            if (strlen($tempRecipe) > 0) {
                $tempButton = new BMButton;
                $tempButton->load($tempRecipe);
                $this->buttonArray[$buttonIdx] = $tempButton;
            }
        }
        $this->maxWins = $maxWins;
        $this->isPrevRoundWinnerArray = array_fill(0, $nPlayers, FALSE);
        $this->actionLog = array();
    }

    private function get_roundNumber() {
        return(array_sum($this->gameScoreArrayArray[0]) + 1);
    }

    private function get_roundScoreArray() {
        $roundScoreTimesTenArray = array_fill(0, $this->nPlayers, 0);
        $roundScoreArray = array_fill(0, $this->nPlayers, 0);

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

    // record a game action in the history log
    private function log_action($actionType, $actingPlayerIdx, $message) {
        $this->actionLog[] = array(
            'gameState'  => $this->gameState,
            'actionType' => $actionType,
            'actingPlayerIdx' => $actingPlayerIdx,
            'message'    => $message,
        );
    }

    // empty the action log after its entries have been stored in
    // the database
    public function empty_action_log() {
        $this->actionLog = array();
    }

    // special recording function for logging what changed as the result of an attack
    private function log_attack($preAttackDice, $postAttackDice) {
        $attackType = $this->attack['attackType'];

        // First, what type of attack was this?
        if ($attackType == 'Pass') {
            $this->message = 'passed';
        } else {
            $this->message = 'performed ' . $attackType . ' attack';

            // Add the pre-attack status of all participating dice
            $preAttackAttackers = array();
            $preAttackDefenders = array();
            $attackerOutcomes = array();
            $defenderOutcomes = array();
            foreach ($preAttackDice['attacker'] as $idx => $attackerInfo) {
                $preAttackAttackers[] = $attackerInfo['recipeStatus'];
            }
            foreach ($preAttackDice['defender'] as $idx => $defenderInfo) {
                $preAttackDefenders[] = $defenderInfo['recipeStatus'];
            }
            if (count($preAttackAttackers) > 0) {
                $this->message .= ' using [' . implode(",", $preAttackAttackers) . ']';
            }
            if (count($preAttackDefenders) > 0) {
                $this->message .= ' against [' . implode(",", $preAttackDefenders) . ']';
            }

            // Report what happened to each defending die
            foreach ($preAttackDice['defender'] as $idx => $defenderInfo) {
                $postInfo = $postAttackDice['defender'][$idx];
                $postEvents = array();
                if ($postInfo['captured']) {
                    $postEvents[] = 'was captured';
                } else {
                    $postEvents[] = 'was not captured';
                    if ($defenderInfo['doesReroll']) {
                        $postEvents[] = 'rerolled ' . $defenderInfo['value'] . ' => ' . $postInfo['value'];
                    } else {
                        $postEvents[] = 'does not reroll';
                    }
                }
                if ($defenderInfo['recipe'] != $postInfo['recipe']) {
                    $postEvents[] = 'recipe changed from ' . $defenderInfo['recipe'] . ' to ' . $postInfo['recipe'];
                }
                $this->message .= '; Defender ' . $defenderInfo['recipe'] . ' ' . implode(', ', $postEvents);
            }

            // Report what happened to each attacking die
            foreach ($preAttackDice['attacker'] as $idx => $attackerInfo) {
                $postInfo = $postAttackDice['attacker'][$idx];
                $postEvents = array();
                if ($attackerInfo['doesReroll']) {
                    $postEvents[] = 'rerolled ' . $attackerInfo['value'] . ' => ' . $postInfo['value'];
                } else {
                    $postEvents[] = 'does not reroll';
                }
                if ($attackerInfo['recipe'] != $postInfo['recipe']) {
                    $postEvents[] = 'recipe changed from ' . $attackerInfo['recipe'] . ' to ' . $postInfo['recipe'];
                }
                if (count($postEvents) > 0) {
                    $this->message .= '; Attacker ' . $attackerInfo['recipe'] . ' ' . implode(', ', $postEvents);
                }
            }
        }
        $this->log_action('attack', $this->playerIdArray[$this->attackerPlayerIdx], $this->message);
    }

    // get log-relevant data about the dice involved in an attack
    private function get_action_log_data($attackerDice, $defenderDice) {
        $attackData = array(
            'attacker' => array(),
            'defender' => array(),
        );
        foreach ($attackerDice as $attackerIdx => $attackerDie) {
            $attackData['attacker'][] = $attackerDie->get_action_log_data();
        }
        foreach ($defenderDice as $attackerIdx => $attackerDie) {
            $attackData['defender'][] = $attackerDie->get_action_log_data();
        }
        return $attackData;
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
            case 'turnNumberInRound':
                if (FALSE === filter_var($value,
                                         FILTER_VALIDATE_INT,
                                         array("options"=>
                                               array("min_range"=>0)))) {
                    throw new InvalidArgumentException(
                        'Invalid turn number.');
                }
                $this->turnNumberInRound = $value;
                break;
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
                if (!is_integer($value[0])) {
                    throw new InvalidArgumentException(
                        'The first element in attack must be an integer.');
                }
                if (!is_integer($value[1]) && !is_null($value[1])) {
                    throw new InvalidArgumentException(
                        'The second element in attack must be an integer or a NULL.');
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
                                implode('|', BMSkill::attack_types()).
                                '/', $value[4])) {
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
            case 'nRecentPasses':
                if (FALSE === filter_var($value,
                                         FILTER_VALIDATE_INT,
                                         array("options"=>
                                               array("min_range"=>0,
                                                     "max_range"=>$this->nPlayers)))) {
                    throw new InvalidArgumentException(
                        'nRecentPasses must be an integer between zero and the number of players.');
                }
                $this->nRecentPasses = $value;
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
                    $tempArray[$playerIdx] = array('W' => (int)$value[$playerIdx][0],
                                                   'L' => (int)$value[$playerIdx][1],
                                                   'D' => (int)$value[$playerIdx][2]);
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
                $this->gameState = (int)$value;
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
            case 'autopassArray':
                if (!is_array($value) ||
                    count($value) !== count($this->playerIdArray)) {
                    throw new InvalidArgumentException(
                        'Number of settings must equal the number of players.');
                }
                foreach ($value as $tempValueElement) {
                    if (!is_bool($tempValueElement)) {
                        throw new InvalidArgumentException(
                            'Input must be an array of booleans.');
                    }
                }
                $this->autopassArray = $value;
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

    public function getJsonData($requestingPlayerId) {
        $requestingPlayerIdx = array_search($requestingPlayerId, $this->playerIdArray);

        $wereBothSwingValuesReset = TRUE;
        // james: need to also consider the case of many multiple draws in a row
        foreach ($this->gameScoreArrayArray as $gameScoreArray) {
            if ($gameScoreArray['W'] > 0 || $gameScoreArray['D'] > 0) {
                $wereBothSwingValuesReset = FALSE;
                break;
            }
        }

        foreach ($this->buttonArray as $button) {
            $buttonNameArray[] = $button->name;
        }

        $swingValuesAllSpecified = TRUE;
        if (isset($this->activeDieArrayArray)) {
            $nDieArray = array_map('count', $this->activeDieArrayArray);
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
                    // hide swing information if appropriate
                    $dieValue = $die->value;
                    $dieMax = $die->max;
                    if (is_null($dieMax)) {
                        $swingValuesAllSpecified = FALSE;
                    }

                    if ($wereBothSwingValuesReset &&
                        ($this->gameState <= BMGameState::specifyDice) &&
                        ($playerIdx !== $requestingPlayerIdx)) {
                        $dieValue = NULL;
                        $dieMax = NULL;
                    }
                    $valueArrayArray[$playerIdx][] = $dieValue;
                    $sidesArrayArray[$playerIdx][] = $dieMax;
                    $dieRecipeArrayArray[$playerIdx][] = $die->recipe;
                }
            }
        } else {
            $nDieArray = array_fill(0, $this->nPlayers, 0);
            $valueArrayArray = array_fill(0, $this->nPlayers, array());
            $sidesArrayArray = array_fill(0, $this->nPlayers, array());
            $dieRecipeArrayArray = array_fill(0, $this->nPlayers, array());
            $swingRequestArrayArray = array_fill(0, $this->nPlayers, array());
        }

        if (!$swingValuesAllSpecified) {
                foreach($valueArrayArray as &$valueArray) {
                        foreach($valueArray as &$value) {
                                $value = NULL;
                        }
                }
        }

        // If it's someone's turn to attack, report the valid attack
        // types as part of the game data
        if ($this->gameState == BMGameState::startTurn) {
            $validAttackTypeArray = $this->valid_attack_types();
        } else {
            $validAttackTypeArray = array();
        }

        $dataArray =
            array('gameId'                  => $this->gameId,
                  'gameState'               => $this->gameState,
                  'roundNumber'             => $this->get_roundNumber(),
                  'maxWins'                 => $this->maxWins,
                  'activePlayerIdx'         => $this->activePlayerIdx,
                  'playerWithInitiativeIdx' => $this->playerWithInitiativeIdx,
                  'playerIdArray'           => $this->playerIdArray,
                  'buttonNameArray'         => $buttonNameArray,
                  'waitingOnActionArray'    => $this->waitingOnActionArray,
                  'nDieArray'               => $nDieArray,
                  'valueArrayArray'         => $valueArrayArray,
                  'sidesArrayArray'         => $sidesArrayArray,
                  'dieRecipeArrayArray'     => $dieRecipeArrayArray,
                  'swingRequestArrayArray'  => $swingRequestArrayArray,
                  'validAttackTypeArray'    => $validAttackTypeArray,
                  'roundScoreArray'         => $this->get_roundScoreArray(),
                  'gameScoreArrayArray'     => $this->gameScoreArrayArray);

        return array('status' => 'ok', 'data' => $dataArray);
    }
}

?>
