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
 * @property      array $chat                    A chat message submitted by the active player
 * @property-read string $message                Message to be passed to the GUI
 * @property      array $swingRequestArrayArray  Swing requests for all players
 * @property      array $swingValueArrayArray    Swing values for all players
 * @property      array $prevSwingValueArrayArray Swing values for previous round for all players
 * @property      array $optRequestArrayArray    Option requests for all players
 * @property      array $prevOptValueArrayArray  Option values for previous round for all players
 * @property      array $lastActionTimeArray     Times of last actions for each player
 *
 * @SuppressWarnings(PMD.TooManyFields)
 * @SuppressWarnings(PMD.TooManyMethods)
 * @SuppressWarnings(PMD.UnusedPrivateField)
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
    private $chat;                  // chat message submitted by the active player with an attack
    private $message;               // message to be passed to the GUI

    private $forceRoundResult;      // boolean array whether each player has won the round

    public $swingRequestArrayArray;
    public $swingValueArrayArray;
    public $prevSwingValueArrayArray;
    public $optRequestArrayArray;
    public $optValueArrayArray;
    public $prevOptValueArrayArray;

    public $lastActionTimeArray;

    // methods
    public function do_next_step() {
        if (!isset($this->gameState)) {
            throw new LogicException('Game state must be set.');
        }

        $this->debug_message = 'ok';

        $this->run_die_hooks($this->gameState);

        $funcName = 'do_next_step_'.
                    strtolower(BMGameState::as_string($this->gameState));
        $this->$funcName();
    }

    public function update_game_state() {
        if (!isset($this->gameState)) {
            throw new LogicException('Game state must be set.');
        }

        $funcName = 'update_game_state_'.
                    strtolower(BMGameState::as_string($this->gameState));
        $this->$funcName();
    }

    protected function do_next_step_start_game() {
    }

    protected function update_game_state_start_game() {
        $this->reset_play_state();

        $nPlayers = count($this->playerIdArray);
        $allPlayersSet = TRUE;

        // if player is unspecified, wait for player to accept game
        for ($playerIdx = 0;
             $playerIdx <= $nPlayers - 1;
             $playerIdx++) {
            if (!isset($this->playerIdArray[$playerIdx])) {
                $this->waitingOnActionArray[$playerIdx] = TRUE;
                $allPlayersSet = FALSE;
                $this->activate_GUI('Prompt for player ID', $playerIdx);
            }
        }

        if (!$allPlayersSet) {
            return;
        }

        $allButtonsSet = TRUE;

        // if button is unspecified, allow player to choose buttons
        for ($playerIdx = 0;
             $playerIdx <= $nPlayers - 1;
             $playerIdx++) {
            if (!isset($this->buttonArray[$playerIdx])) {
                $this->waitingOnActionArray[$playerIdx] = TRUE;
                $allButtonsSet = FALSE;
                $this->activate_GUI('Prompt for button ID', $playerIdx);
            }
        }

        if (!$allButtonsSet) {
            return;
        }

        $this->gameState = BMGameState::APPLY_HANDICAPS;
        $this->nRecentPasses = 0;
        $this->autopassArray = array_fill(0, $this->nPlayers, FALSE);
        $this->gameScoreArrayArray = array_fill(0, $this->nPlayers, array(0, 0, 0));
    }

    protected function do_next_step_apply_handicaps() {
        // ignore for the moment
        $this->gameScoreArrayArray =
            array_fill(
                0,
                count($this->playerIdArray),
                array('W' => 0, 'L' => 0, 'D' => 0)
            );
    }

    protected function update_game_state_apply_handicaps() {
        if (!isset($this->maxWins)) {
            throw new LogicException(
                'maxWins must be set before applying handicaps.'
            );
        };
        if (isset($this->gameScoreArrayArray)) {
            $nWins = 0;
            foreach ($this->gameScoreArrayArray as $tempGameScoreArray) {
                if ($nWins < $tempGameScoreArray['W']) {
                    $nWins = $tempGameScoreArray['W'];
                }
            }
            if ($nWins >= $this->maxWins) {
                $this->gameState = BMGameState::END_GAME;
            } else {
                $this->gameState = BMGameState::LOAD_DICE_INTO_BUTTONS;
            }
        }
    }



    protected function do_next_step_load_dice_into_buttons() {
        // james: this is currently carried out either by manually setting
        // $this->buttonArray, or by BMInterface
    }

    protected function update_game_state_load_dice_into_buttons() {
        if (!isset($this->buttonArray)) {
            throw new LogicException(
                'Button array must be set before loading dice into buttons.'
            );
        }

        $buttonsHaveDice = TRUE;
        foreach ($this->buttonArray as $tempButton) {
            if (!isset($tempButton->dieArray)) {
                $buttonsHaveDice = FALSE;
                break;
            }
        }
        if ($buttonsHaveDice) {
            $this->gameState = BMGameState::ADD_AVAILABLE_DICE_TO_GAME;
        }
    }

    protected function do_next_step_add_available_dice_to_game() {
        // load BMGame activeDieArrayArray from BMButton dieArray
        $this->activeDieArrayArray =
            array_fill(0, $this->nPlayers, array());

        foreach ($this->buttonArray as $tempButton) {
            $tempButton->activate();
        }

        $this->offer_courtesy_auxiliary_dice();

        // load swing values that are carried across from a previous round
        if (!isset($this->swingValueArrayArray)) {
            return;
        }

        foreach ($this->activeDieArrayArray as $playerIdx => &$activeDieArray) {
            foreach ($activeDieArray as &$activeDie) {
                if ($activeDie instanceof BMDieSwing) {
                    if (array_key_exists(
                        $activeDie->swingType,
                        $this->swingValueArrayArray[$playerIdx]
                    )) {
                        $activeDie->swingValue =
                            $this->swingValueArrayArray[$playerIdx][$activeDie->swingType];
                    }
                }
            }
        }
    }

    protected function offer_courtesy_auxiliary_dice() {
        $havePlayersAuxDice = $this->do_players_have_dice_with_skill('Auxiliary');

        if (array_sum($havePlayersAuxDice) > 0) {
            $auxiliaryDice = $this->get_all_auxiliary_dice();

            // add auxiliary dice to players who do not have any
            foreach ($havePlayersAuxDice as $playerIdx => $hasAuxDice) {
                if (!$hasAuxDice) {
                    foreach ($auxiliaryDice as $die) {
                        $newdie = clone $die;
                        $newdie->playerIdx = $playerIdx;
                        $newdie->originalPlayerIdx = $playerIdx;
                        $this->activeDieArrayArray[$playerIdx][] = $newdie;
                    }
                }
            }
        }
    }

    protected function do_players_have_dice_with_skill($skill) {
        $hasDiceWithSkill = array_fill(0, $this->nPlayers, FALSE);

        foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
            foreach ($activeDieArray as $die) {
                if ($die->has_skill($skill)) {
                    $hasDiceWithSkill[$playerIdx] = TRUE;
                    break;
                }
            }
        }

        return $hasDiceWithSkill;
    }

    protected function get_all_auxiliary_dice() {
        $auxiliaryDice = array();

        foreach ($this->activeDieArrayArray as $activeDieArray) {
            foreach ($activeDieArray as $die) {
                if ($die->has_skill('Auxiliary')) {
                    $auxiliaryDice[] = $die;
                }
            }
        }

        return $auxiliaryDice;
    }

    protected function update_game_state_add_available_dice_to_game() {
        if (isset($this->activeDieArrayArray)) {
            $this->gameState = BMGameState::CHOOSE_AUXILIARY_DICE;
            $this->waitingOnActionArray =
                $this->do_players_have_dice_with_skill('Auxiliary');
        }
    }

    protected function do_next_step_choose_auxiliary_dice() {

    }

    protected function update_game_state_choose_auxiliary_dice() {
        // if all decisions on auxiliary dice have been made
        if (0 == array_sum($this->waitingOnActionArray)) {
            $areAnyDiceAdded = $this->add_selected_auxiliary_dice();
            $areAnyDiceRemoved = $this->remove_dice_with_skill('Auxiliary');

            if (array_sum($areAnyDiceAdded) + array_sum($areAnyDiceRemoved) > 0) {
                // update button recipes
                for ($playerIdx = 0; $playerIdx < $this->nPlayers; $playerIdx++) {
                    if ($areAnyDiceAdded[$playerIdx] ||
                        $areAnyDiceRemoved[$playerIdx]) {
                        $this->buttonArray[$playerIdx]->update_button_recipe();
                    }
                }

            }

            $this->gameState = BMGameState::CHOOSE_RESERVE_DICE;
        }
    }

    protected function add_selected_auxiliary_dice() {
        $hasChosenAuxDie = array_fill(0, $this->nPlayers, FALSE);

        foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
            foreach ($activeDieArray as $die) {
                if ($die->selected) {
                    $hasChosenAuxDie[$playerIdx] = TRUE;
                    break;
                }
            }
        }

        $useAuxDice = (1 == array_product($hasChosenAuxDie));

        if ($useAuxDice) {
            foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
                foreach ($activeDieArray as $die) {
                    if ($die->selected) {
                        $die->remove_skill('Auxiliary');
                        $die->selected = FALSE;
                    }
                }
            }
        }

        return array_fill(0, $this->nPlayers, $useAuxDice);
    }

    protected function remove_dice_with_skill($skill) {
        $areAnyDiceRemoved = array_fill(0, $this->nPlayers, FALSE);

        // remove all remaining auxiliary dice
        foreach ($this->activeDieArrayArray as $playerIdx => &$activeDieArray) {
            foreach ($activeDieArray as $dieIdx => &$die) {
                if ($die->has_skill($skill)) {
                    $areAnyDiceRemoved[$playerIdx] = TRUE;
                    unset($activeDieArray[$dieIdx]);
                }
            }
            if ($areAnyDiceRemoved[$playerIdx]) {
                $this->activeDieArrayArray[$playerIdx] = array_values($activeDieArray);
            }
        }

        return $areAnyDiceRemoved;
    }

    protected function do_next_step_choose_reserve_dice() {
        $waitingOnActionArray = array_fill(0, $this->nPlayers, FALSE);

        if (array_sum($this->isPrevRoundWinnerArray) > 0) {
            $haveReserveDice = $this->do_players_have_dice_with_skill('Reserve');

            if (array_sum($haveReserveDice) > 0) {
                foreach ($waitingOnActionArray as $playerIdx => &$waitingOnAction) {
                    if (!$this->isPrevRoundWinnerArray[$playerIdx] &&
                        $haveReserveDice[$playerIdx]) {
                        $waitingOnAction = TRUE;
                    }
                }
            }
        }

        $this->waitingOnActionArray = $waitingOnActionArray;
    }

    protected function update_game_state_choose_reserve_dice() {
        // if all decisions on reserve dice have been made
        if (0 == array_sum($this->waitingOnActionArray)) {
            $areAnyDiceAdded = $this->add_selected_reserve_dice();

            if (array_sum($areAnyDiceAdded) > 0) {
                // update button recipes
                for ($playerIdx = 0; $playerIdx < $this->nPlayers; $playerIdx++) {
                    if ($areAnyDiceAdded[$playerIdx]) {
                        $this->buttonArray[$playerIdx]->update_button_recipe();
                    }
                }

            }

            $this->remove_dice_with_skill('Reserve');
            $this->gameState = BMGameState::SPECIFY_DICE;
        }
    }

    protected function add_selected_reserve_dice() {
        $areAnyDiceAdded = array_fill(0, $this->nPlayers, FALSE);

        if (isset($this->activeDieArrayArray)) {
            foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
                foreach ($activeDieArray as $die) {
                    if ($die->selected) {
                        $die->remove_skill('Reserve');
                        $die->selected = FALSE;
                        if ($die instanceof BMDieSwing) {
                            $this->request_swing_values(
                                $die,
                                $die->swingType,
                                $die->playerIdx
                            );
                            $die->valueRequested = TRUE;
                        }
                        $areAnyDiceAdded[$playerIdx] = TRUE;
                    }
                }
            }
        }

        return $areAnyDiceAdded;
    }

    protected function do_next_step_specify_dice() {
        $this->waitingOnActionArray =
            array_fill(0, count($this->playerIdArray), FALSE);

        $this->initialise_swing_value_array_array();
        $this->set_option_values();
        $this->set_swing_values();
        $this->roll_active_dice();
    }

    protected function initialise_swing_value_array_array() {
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
        }
    }

    protected function set_option_values() {
        if (!isset($this->optRequestArrayArray)) {
            return;
        }

        foreach ($this->optRequestArrayArray as $playerIdx => $optionRequestArray) {
//            var_dump($optionRequestArray);
            foreach (array_keys($optionRequestArray) as $dieIdx) {
                if (isset($this->optValueArrayArray[$playerIdx]) &&
                    (count($this->optValueArrayArray[$playerIdx]) > 0)) {
                    $optValue = $this->optValueArrayArray[$playerIdx][$dieIdx];
                    if (isset($optValue)) {
                        $this->activeDieArrayArray[$playerIdx][$dieIdx]->set_optionValue($optValue);
                    }
                }

                if (!isset($this->activeDieArrayArray[$playerIdx][$dieIdx]->max)) {
                    $this->waitingOnActionArray[$playerIdx] = TRUE;
                    continue 2;
                }
            }
        }
    }

    protected function set_swing_values() {
        if (isset($this->swingRequestArrayArray)) {
            foreach ($this->waitingOnActionArray as $playerIdx => $waitingOnAction) {
                if ($waitingOnAction) {
                    $this->activate_GUI('Waiting on player action.', $playerIdx);
                } else {

                    // apply swing values
                    foreach ($this->activeDieArrayArray[$playerIdx] as $die) {
                        if (isset($die->swingType)) {
                            $isSetSuccessful = $die->set_swingValue(
                                $this->swingValueArrayArray[$playerIdx]
                            );
                            // act appropriately if the swing values are invalid
                            if (!$isSetSuccessful) {
                                $this->message = 'Invalid value submitted for swing die ' . $die->recipe;
                                $this->swingValueArrayArray[$playerIdx] = array();
                                $this->waitingOnActionArray[$playerIdx] = TRUE;
                                return;
                            }
                        }
                    }
                }
            }
        }
    }

    protected function roll_active_dice() {
        foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
            foreach ($activeDieArray as $dieIdx => $die) {
                if ($die instanceof BMDieSwing) {
                    if ($die->needsSwingValue) {
                        // swing value has not yet been set
                        continue;
                    }
                }

                if ($die instanceof BMDieOption) {
                    if ($die->needsOptionValue) {
                        // option value has not yet been set
                        continue;
                    }
                }

                $this->activeDieArrayArray[$playerIdx][$dieIdx] =
                    $die->make_play_die(FALSE);
            }
        }
    }

    protected function update_game_state_specify_dice() {
        if (0 == array_sum($this->waitingOnActionArray)) {
            $this->prevSwingValueArrayArray = NULL;
            $this->prevOptValueArrayArray = NULL;
            $this->gameState = BMGameState::DETERMINE_INITIATIVE;
        }
    }

    protected function do_next_step_determine_initiative() {
        $hasInitiativeArray =
            BMGame::does_player_have_initiative_array($this->activeDieArrayArray);

        if (array_sum($hasInitiativeArray) > 1) {
            $playersWithInit = array();
            foreach ($hasInitiativeArray as $playerIdx => $tempHasInitiative) {
                if ($tempHasInitiative) {
                    $playersWithInit[] = $playerIdx;
                }
            }
            $tempInitiativeIdx = array_rand($playersWithInit);
        } else {
            $tempInitiativeIdx =
                array_search(TRUE, $hasInitiativeArray, TRUE);
        }

        $this->playerWithInitiativeIdx = $tempInitiativeIdx;
    }

    protected function update_game_state_determine_initiative() {
        if (isset($this->playerWithInitiativeIdx)) {
            $this->gameState = BMGameState::REACT_TO_INITIATIVE;
        }
    }

    protected function do_next_step_react_to_initiative() {
        $canReactArray = array_fill(0, $this->nPlayers, FALSE);

        foreach ($this->activeDieArrayArray as $playerIdx => &$activeDieArray) {
            // do nothing if a player has won initiative
            if ($this->playerWithInitiativeIdx == $playerIdx) {
                continue;
            }

            foreach ($activeDieArray as &$activeDie) {
                // find out if any of the dice have the ability to react
                // when the player loses initiative
                $hookResultArray =
                    $activeDie->run_hooks(
                        'react_to_initiative',
                        array('activeDieArrayArray' => $this->activeDieArrayArray,
                              'playerIdx' => $playerIdx)
                    );

                // re-enable all disabled chance dice for non-active players
                if ($activeDie->has_skill('Chance')) {
                    unset($activeDie->disabled);
                }

                if (is_array($hookResultArray) && count($hookResultArray) > 0) {
                    foreach ($hookResultArray as $hookResult) {
                        if (TRUE === $hookResult) {
                            $canReactArray[$playerIdx] = TRUE;
                            continue;
                        }
                    }
                }
            }
        }

        $this->waitingOnActionArray = $canReactArray;
    }

    protected function update_game_state_react_to_initiative() {
        // if everyone is out of actions, reactivate chance dice
        if (0 == array_sum($this->waitingOnActionArray)) {
            $this->gameState = BMGameState::START_ROUND;
            if (isset($this->activeDieArrayArray)) {
                foreach ($this->activeDieArrayArray as &$activeDieArray) {
                    if (isset($activeDieArray)) {
                        foreach ($activeDieArray as &$activeDie) {
                            if ($activeDie->has_skill('Chance')) {
                                unset($activeDie->disabled);
                            }
                        }
                    }
                }
            }
        }
    }

    protected function do_next_step_start_round() {
        if (!isset($this->playerWithInitiativeIdx)) {
            throw new LogicException(
                'Player that has won initiative must already have been determined.'
            );
        }
        // set BMGame activePlayerIdx
        $this->activePlayerIdx = $this->playerWithInitiativeIdx;
        $this->turnNumberInRound = 1;
    }

    protected function update_game_state_start_round() {
        if (isset($this->activePlayerIdx)) {
            $this->gameState = BMGameState::START_TURN;

            // re-enable focus dice for everyone apart from the active player
            foreach ($this->activeDieArrayArray as $playerIdx => &$activeDieArray) {
                if ($this->activePlayerIdx == $playerIdx) {
                    continue;
                }
                if (count($activeDieArray) > 0) {
                    foreach ($activeDieArray as &$activeDie) {
                        if ($activeDie->has_skill('Focus') &&
                            isset($activeDie->dizzy)) {
                            unset($activeDie->dizzy);
                        }
                    }
                }
            }
        }
    }

    protected function do_next_step_start_turn() {
        $this->perform_autopass();

        // display dice
        $this->activate_GUI('show_active_dice');

        if (!$this->are_attack_params_reasonable()) {
            return;
        }

        $instance = $this->create_attack_instance();
        if (FALSE === $instance) {
            return;
        }

        $attack = $instance['attack'];
        $attAttackDieArray = $instance['attAttackDieArray'];
        $defAttackDieArray = $instance['defAttackDieArray'];

        $this->remove_all_flags();

        $preAttackDice = $this->get_action_log_data(
            $attAttackDieArray,
            $defAttackDieArray
        );

        $this->turnNumberInRound++;
        $attack->commit_attack($this, $attAttackDieArray, $defAttackDieArray);

        $postAttackDice = $this->get_action_log_data(
            $attAttackDieArray,
            $defAttackDieArray
        );
        $this->log_action(
            'attack',
            $this->playerIdArray[$this->attackerPlayerIdx],
            array(
                'attackType' => $attack->type,
                'preAttackDice' => $preAttackDice,
                'postAttackDice' => $postAttackDice,
            )
        );

        if (isset($this->activePlayerIdx)) {
            $this->update_active_player();
        }
    }

    protected function perform_autopass() {
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
    }

    protected function are_attack_params_reasonable() {
        // if attack has not been set, ask player to select attack
        if (!isset($this->attack)) {
            $this->activate_GUI('wait_for_attack');
            $this->waitingOnActionArray[$this->activePlayerIdx] = TRUE;
            return FALSE;
        }

        // validate attacker player idx
        if ($this->activePlayerIdx !== $this->attack['attackerPlayerIdx']) {
            $this->message = 'Attacker must be current active player.';
            $this->attack = NULL;
            return FALSE;
        }

        // validate defender player idx
        if ($this->attack['attackerPlayerIdx'] === $this->attack['defenderPlayerIdx']) {
            $this->message = 'Attacker must be different to defender.';
            $this->attack = NULL;
            return FALSE;
        }

        return TRUE;
    }

    protected function create_attack_instance() {
        $attack = BMAttack::get_instance($this->attack['attackType']);

        $this->attackerPlayerIdx = $this->attack['attackerPlayerIdx'];
        $this->defenderPlayerIdx = $this->attack['defenderPlayerIdx'];
        $attAttackDieArray = array();
        foreach ($this->attack['attackerAttackDieIdxArray'] as $attackerAttackDieIdx) {
            $attackDie =
                &$this->activeDieArrayArray[$this->attack['attackerPlayerIdx']]
                                           [$attackerAttackDieIdx];
            if ($attackDie->dizzy) {
                $this->message = 'Attempting to attack with a dizzy die.';
                $this->attack = NULL;
                return FALSE;
            }
            $attAttackDieArray[] = $attackDie;
        }
        $defAttackDieArray = array();
        foreach ($this->attack['defenderAttackDieIdxArray'] as $defenderAttackDieIdx) {
            $defAttackDieArray[] =
                &$this->activeDieArrayArray[$this->attack['defenderPlayerIdx']]
                                           [$defenderAttackDieIdx];
        }

        foreach ($attAttackDieArray as $attackDie) {
            $attack->add_die($attackDie);
        }

        $valid = $attack->validate_attack(
            $this,
            $attAttackDieArray,
            $defAttackDieArray
        );

        if (!$valid) {
            $this->activate_GUI('Invalid attack');
            $this->waitingOnActionArray[$this->activePlayerIdx] = TRUE;
            $this->attack = NULL;
            return FALSE;
        }

        return array('attack' => $attack,
                     'attAttackDieArray' => $attAttackDieArray,
                     'defAttackDieArray' => $defAttackDieArray);
    }

    protected function remove_all_flags() {
        foreach ($this->activeDieArrayArray as $activeDieArray) {
            if (empty($activeDieArray)) {
                continue;
            }

            foreach ($activeDieArray as $die) {
                $die->remove_all_flags();
            }
        }

        foreach ($this->capturedDieArrayArray as $capturedDieArray) {
            if (empty($capturedDieArray)) {
                continue;
            }

            foreach ($capturedDieArray as $die) {
                $die->remove_all_flags();
            }
        }
    }

    protected function update_game_state_start_turn() {
        if ((isset($this->attack)) &&
            FALSE === array_search(TRUE, $this->waitingOnActionArray, TRUE)) {
            $this->gameState = BMGameState::END_TURN;
            if (isset($this->activeDieArrayArray) &&
                isset($this->attack['attackerPlayerIdx'])) {
                foreach ($this->activeDieArrayArray[$this->attack['attackerPlayerIdx']] as &$activeDie) {
                    if ($activeDie->dizzy) {
                            unset($activeDie->dizzy);
                    }
                }
            }
        }
    }

    protected function do_next_step_end_turn() {
    }

    protected function update_game_state_end_turn() {
        $nDice = array_map('count', $this->activeDieArrayArray);
        // check if any player has no dice, or if everyone has passed
        if ((0 === min($nDice)) ||
            ($this->nPlayers == $this->nRecentPasses) ||
            isset($this->forceRoundResult)) {
            $this->gameState = BMGameState::END_ROUND;
        } else {
            $this->gameState = BMGameState::START_TURN;
            $this->waitingOnActionArray[$this->activePlayerIdx] = TRUE;
        }
        $this->attack = NULL;
    }

    protected function do_next_step_end_round() {
        $roundScoreArray = $this->get_roundScoreArray();
        if (isset($this->forceRoundResult)) {
            $this->isPrevRoundWinnerArray = $this->forceRoundResult;
            $isDraw = FALSE;
        } else {
            $this->isPrevRoundWinnerArray = array_fill(0, $this->nPlayers, FALSE);

            // check for draw currently assumes only two players
            $isDraw = $roundScoreArray[0] == $roundScoreArray[1];
        }

        if ($isDraw) {
            for ($playerIdx = 0; $playerIdx < $this->nPlayers; $playerIdx++) {
                $this->gameScoreArrayArray[$playerIdx]['D']++;
                // james: currently there is no code for three draws in a row
            }
            $this->log_action(
                'end_draw',
                0,
                array(
                    'roundNumber' => $this->get_prevRoundNumber(),
                    'roundScoreArray' => $roundScoreArray,
                )
            );
        } else {
            if (isset($this->forceRoundResult)) {
                $winnerIdx = array_search(TRUE, $this->forceRoundResult);
                $forceRoundResult = $this->forceRoundResult;
            } else {
                $winnerIdx = array_search(max($roundScoreArray), $roundScoreArray);
                $forceRoundResult = FALSE;
            }

            $this->prevSwingValueArrayArray = $this->swingValueArrayArray;
            $this->prevOptValueArrayArray = $this->optValueArrayArray;
            $this->optRequestArrayArray = array_fill(0, $this->nPlayers, array());

            for ($playerIdx = 0; $playerIdx < $this->nPlayers; $playerIdx++) {
                if ($playerIdx == $winnerIdx) {
                    $this->gameScoreArrayArray[$playerIdx]['W']++;
                    $this->isPrevRoundWinnerArray[$playerIdx] = TRUE;
                } else {
                    $this->gameScoreArrayArray[$playerIdx]['L']++;
                    $this->swingValueArrayArray[$playerIdx] = array();
                    $this->optValueArrayArray[$playerIdx] = array();
                }
            }
            $this->log_action(
                'end_winner',
                $this->playerIdArray[$winnerIdx],
                array(
                    'roundNumber' => $this->get_prevRoundNumber(),
                    'roundScoreArray' => $roundScoreArray,
                    'resultForced' => $forceRoundResult,
                )
            );
        }
        $this->reset_play_state();
    }

    protected function update_game_state_end_round() {
        if (isset($this->activePlayerIdx)) {
            return;
        }

        $this->gameState = BMGameState::LOAD_DICE_INTO_BUTTONS;
        foreach ($this->gameScoreArrayArray as $tempGameScoreArray) {
            if ($tempGameScoreArray['W'] >= $this->maxWins) {
                $this->gameState = BMGameState::END_GAME;
                return;
            }
        }
    }

    protected function do_next_step_end_game() {
        $this->reset_play_state();

        // swingValueArrayArray must be reset to clear entries in the
        // database table game_swing_map
        $this->swingValueArrayArray = array_fill(0, $this->nPlayers, array());
        $this->prevSwingValueArrayArray = NULL;

        // optValueArrayArray must be reset to clear entries in the
        // database table game_option_map
        $this->optValueArrayArray = array_fill(0, $this->nPlayers, array());
        $this->prevOptRequestArrayArray = NULL;

        $this->activate_GUI('Show end-of-game screen.');
    }

    protected function update_game_state_end_game() {
    }

    // The variable $gameStateBreakpoint is used for debugging purposes only.
    // If used, the game will stop as soon as the game state becomes

    public function proceed_to_next_user_action($gameStateBreakpoint = NULL) {
        $repeatCount = 0;
        $initialGameState = $this->gameState;
        $this->update_game_state();

        if (isset($gameStateBreakpoint) &&
            ($gameStateBreakpoint == $this->gameState) &&
            ($initialGameState != $this->gameState)) {
            return;
        }

        $this->do_next_step();

        while (0 === array_sum($this->waitingOnActionArray)) {
            $tempGameState = $this->gameState;
            $this->update_game_state();

            if (isset($gameStateBreakpoint) &&
                ($gameStateBreakpoint == $this->gameState)) {
                return;
            }

            $this->do_next_step();

            if (BMGameState::END_GAME === $this->gameState) {
                return;
            }

            if ($tempGameState === $this->gameState) {
                $repeatCount++;
            } else {
                $repeatCount = 0;
            }
            if ($repeatCount >= 20) {
                $finalGameState = BMGameState::as_string($this->gameState);
                throw new LogicException(
                    'Infinite loop detected when advancing game state. '.
                    "Final game state: $finalGameState"
                );
            }
        }
    }

    // react_to_initiative expects one of the following three input arrays:
    //
    //   1.  array('action' => 'chance',
    //             'playerIdx => $playerIdx,
    //             'rerolledDieIdx' => $rerolledDieIdx)
    //       where the index of the rerolled chance die is in $rerolledDieIdx
    //
    //   2.  array('action' => 'decline',
    //             'playerIdx' => $playerIdx,
    //             'dieIdxArray' => $dieIdxArray,
    //             'dieValueArray' => $dieValueArray)
    //       where $dieIdxArray and $dieValueArray are the raw inputs to
    //       BMInterface->react_to_initiative()
    //
    //   3.  array('action' => 'focus',
    //             'playerIdx' => $playerIdx,
    //             'focusValueArray' => array($dieIdx1 => $dieValue1,
    //                                        $dieIdx2 => $dieValue2))
    //       where the details of ALL focus dice are in $focusValueArray
    //
    // It returns a boolean telling whether the reaction has been successful.
    // If it fails, $game->message will say why it has failed.

    public function react_to_initiative(array $args) {
        if (BMGameState::REACT_TO_INITIATIVE != $this->gameState) {
            $this->message = 'Wrong game state to react to initiative.';
            return FALSE;
        }

        if (!array_key_exists('action', $args) ||
            !array_key_exists('playerIdx', $args)) {
            $this->message = 'Missing action or player index.';
            return FALSE;
        }

        $playerIdx = $args['playerIdx'];
        $waitingOnActionArray = &$this->waitingOnActionArray;
        $waitingOnActionArray[$playerIdx] = FALSE;

        if (!in_array($args['action'], array('chance', 'decline', 'focus'))) {
            throw new InvalidArgumentException(
                'Reaction must be chance, decline or focus.'
            );
        }

        $reactFuncName = 'react_to_initiative_'.$args['action'];
        $reactResponse = $this->$reactFuncName($args);

        if (FALSE === $reactResponse) {
            return FALSE;
        }

        $gainedInitiative = $reactResponse['gainedInitiative'];

        if ($gainedInitiative) {
            $this->do_next_step();
        }

        return array('gainedInitiative' => $gainedInitiative);
    }

    protected function react_to_initiative_chance($args) {
        if (!array_key_exists('rerolledDieIdx', $args)) {
            $this->message = 'rerolledDieIdx must exist.';
            return FALSE;
        }

        $playerIdx = $args['playerIdx'];

        if (FALSE ===
            filter_var(
                $args['rerolledDieIdx'],
                FILTER_VALIDATE_INT,
                array("options"=>
                      array("min_range"=>0,
                            "max_range"=>count($this->activeDieArrayArray[$playerIdx]) - 1))
            )) {
            $this->message = 'Invalid die index.';
            return FALSE;
        }

        $die = $this->activeDieArrayArray[$playerIdx][$args['rerolledDieIdx']];
        if (FALSE === array_search('BMSkillChance', $die->skillList)) {
            $this->message = 'Can only apply chance action to chance die.';
            return FALSE;
        }

        $preRerollData = $die->get_action_log_data();
        $die->roll(FALSE);

        if (isset($args['TESTrerolledDieValue'])) {
            $die->value = $args['TESTrerolledDieValue'];
        }

        $postRerollData = $die->get_action_log_data();

        $newInitiativeArray = BMGame::does_player_have_initiative_array(
            $this->activeDieArrayArray
        );

        if ($newInitiativeArray[$playerIdx]) {
            $this->gameState = BMGameState::DETERMINE_INITIATIVE;
        } else {
            // only need to disable chance dice if the reroll fails to gain initiative
            foreach ($this->activeDieArrayArray[$playerIdx] as &$die) {
                if ($die->has_skill('Chance')) {
                    $die->disabled = TRUE;
                }
            }
        }

        $gainedInitiative = $newInitiativeArray[$playerIdx] &&
                            (1 == array_sum($newInitiativeArray));

        $this->log_action(
            'reroll_chance',
            $this->playerIdArray[$playerIdx],
            array(
                'preReroll' => $preRerollData,
                'postReroll' => $postRerollData,
                'gainedInitiative' => $gainedInitiative,
            )
        );

        return array('gainedInitiative' => $gainedInitiative);
    }

    protected function react_to_initiative_decline($args) {
        // if there is die information sent, check that it signals
        // no change
        if (!array_key_exists('dieIdxArray', $args) ||
            !array_key_exists('dieValueArray', $args)) {
            $this->message = 'dieIdxArray and dieValueArray must exist.';
            return FALSE;
        }

        $playerIdx = $args['playerIdx'];
        $dieIdxArray = $args['dieIdxArray'];
        $dieValueArray = $args['dieValueArray'];

        // validate decline action
        if (is_array($dieIdxArray) &&
            count($dieIdxArray) > 0) {
            foreach ($dieIdxArray as $listIdx => $dieIdx) {
                $die = $this->activeDieArrayArray[$playerIdx][$dieIdx];

                // check for ANY selected dice, not just a single die
                $possChanceAction = $die->has_skill('Chance') &&
                                    !isset($dieValueArray) &&
                                    (count($dieIdxArray) >= 1);
                // check for ANY change in die value, also invalid changes
                $possFocusAction = $die->has_skill('Focus') &&
                                   is_array($dieValueArray) &&
                                   (count($dieIdxArray) == count($dieValueArray)) &&
                                   ($die->value != $dieValueArray[$listIdx]);
                if ($possChanceAction || $possFocusAction) {
                    return FALSE;
                }
            }
        }

        $this->log_action(
            'init_decline',
            $this->playerIdArray[$playerIdx],
            array('initDecline' => TRUE)
        );

        if (0 == array_sum($this->waitingOnActionArray)) {
            $this->gameState = BMGameState::START_ROUND;
        }

        return array('gainedInitiative' => FALSE);
    }

    protected function react_to_initiative_focus($args) {
        if (!array_key_exists('focusValueArray', $args)) {
            $this->message = 'focusValueArray must exist.';
            return FALSE;
        }

        // check new die values
        $focusValueArray = $args['focusValueArray'];

        if (!is_array($focusValueArray) || (0 == count($focusValueArray))) {
            $this->message = 'focusValueArray must be a non-empty array.';
            return FALSE;
        }

        $playerIdx = $args['playerIdx'];

        // focusValueArray should have the form array($dieIdx1 => $dieValue1, ...)
        foreach ($focusValueArray as $dieIdx => $newDieValue) {
            if (FALSE ===
                filter_var(
                    $dieIdx,
                    FILTER_VALIDATE_INT,
                    array("options"=>
                          array("min_range"=>0,
                                "max_range"=>count($this->activeDieArrayArray[$playerIdx]) - 1))
                )) {
                $this->message = 'Invalid die index.';
                return FALSE;
            }

            $die = $this->activeDieArrayArray[$playerIdx][$dieIdx];

            if (FALSE ===
                filter_var(
                    $newDieValue,
                    FILTER_VALIDATE_INT,
                    array("options"=>
                          array("min_range"=>$die->min,
                                "max_range"=>$die->value))
                )) {
                $this->message = 'Invalid value for focus die.';
                return FALSE;
            }

            if (FALSE === array_search('BMSkillFocus', $die->skillList)) {
                $this->message = 'Can only apply focus action to focus die.';
                return FALSE;
            }
        }

        // change specified die values
        $oldDieValueArray = array();
        $preTurndownData = array();
        $postTurndownData = array();
        foreach ($focusValueArray as $dieIdx => $newDieValue) {
            $preTurndownData[] = $this->activeDieArrayArray[$playerIdx][$dieIdx]->get_action_log_data();
            $oldDieValueArray[$dieIdx] = $this->activeDieArrayArray[$playerIdx][$dieIdx]->value;
            $this->activeDieArrayArray[$playerIdx][$dieIdx]->value = $newDieValue;
            $postTurndownData[] = $this->activeDieArrayArray[$playerIdx][$dieIdx]->get_action_log_data();
        }
        $newInitiativeArray = BMGame::does_player_have_initiative_array(
            $this->activeDieArrayArray
        );

        // if the change is successful, disable focus dice that changed
        // value
        if ($newInitiativeArray[$playerIdx] &&
            1 == array_sum($newInitiativeArray)) {
            foreach ($oldDieValueArray as $dieIdx => $oldDieValue) {
                if ($oldDieValue >
                    $this->activeDieArrayArray[$playerIdx][$dieIdx]->value) {
                    $this->activeDieArrayArray[$playerIdx][$dieIdx]->dizzy = TRUE;
                }
            }
        } else {
            // if the change does not gain initiative unambiguously, it is
            // invalid, so reset die values to original values
            foreach ($oldDieValueArray as $dieIdx => $oldDieValue) {
                $this->activeDieArrayArray[$playerIdx][$dieIdx]->value = $oldDieValue;
            }
            $this->message = 'You did not turn your focus dice down far enough to gain initiative.';
            return FALSE;
        }

        $this->log_action(
            'turndown_focus',
            $this->playerIdArray[$playerIdx],
            array(
                'preTurndown' => $preTurndownData,
                'postTurndown' => $postTurndownData,
            )
        );

        $this->gameState = BMGameState::DETERMINE_INITIATIVE;
        return array('gainedInitiative' => TRUE);
    }

    protected function run_die_hooks($gameState, array $args = array()) {
        $args['activePlayerIdx'] = $this->activePlayerIdx;

        if (!empty($this->activeDieArrayArray)) {
            foreach ($this->activeDieArrayArray as $activeDieArray) {
                foreach ($activeDieArray as $activeDie) {
                    $activeDie->run_hooks_at_game_state($gameState, $args);
                }
            }
        }

        if (!empty($this->capturedDieArrayArray)) {
            foreach ($this->capturedDieArrayArray as $capturedDieArray) {
                foreach ($capturedDieArray as $capturedDie) {
                    $capturedDie->run_hooks_at_game_state($gameState, $args);
                }
            }
        }
    }

    public function add_die($die) {
        if (!isset($this->activeDieArrayArray)) {
            throw new LogicException(
                'activeDieArrayArray must be set before a die can be added.'
            );
        }

        $this->activeDieArrayArray[$die->playerIdx][] = $die;
    }

    public function capture_die($die, $newOwnerIdx = NULL) {
        if (!isset($this->activeDieArrayArray)) {
            throw new LogicException(
                'activeDieArrayArray must be set before capturing dice.'
            );
        }

        foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
            $dieIdx = array_search($die, $activeDieArray, TRUE);
            if (FALSE !== $dieIdx) {
                break;
            }
        }

        if (FALSE === $dieIdx) {
            throw new LogicException('Captured die does not exist.');
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

        if (!$die->doesSkipSwingRequest()) {
            $this->swingRequestArrayArray[$playerIdx][$swingtype][] = $die;
        }
    }

    public function request_option_values($die, $optionArray, $playerIdx) {
        if (!isset($this->optRequestArrayArray)) {
            $this->optRequestArrayArray =
                array_fill(0, $this->nPlayers, array());
        }

        $dieIdx = array_search($die, $this->activeDieArrayArray[$playerIdx], TRUE);
        assert(FALSE !== $dieIdx);
        $this->optRequestArrayArray[$playerIdx][$dieIdx] = $optionArray;
    }

    public static function does_player_have_initiative_array(array $activeDieArrayArray) {
        $initiativeArrayArray = array();
        foreach ($activeDieArrayArray as $playerIdx => $tempActiveDieArray) {
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
        $nPlayers = count($activeDieArrayArray);
        $hasPlayerInitiative = array_fill(0, $nPlayers, TRUE);

        $dieIdx = 0;
        while (array_sum($hasPlayerInitiative) >= 2) {
            $dieValues = array();
            foreach ($initiativeArrayArray as $tempInitiativeArray) {
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
                    $hasPlayerInitiative[$playerIdx] = FALSE;
                }
            }
            $dieIdx++;
        }

        return $hasPlayerInitiative;
    }

    public static function is_die_specified($die) {
        // A die can be unspecified if it is swing, option, or plasma.

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
                                  'attackerAttackDieIdxArray' =>
                                      range(0, count($this->activeDieArrayArray[$attackerIdx]) - 1),
                                  'defenderAttackDieIdxArray' =>
                                      range(0, count($this->activeDieArrayArray[$defenderIdx]) - 1),
                                  'attackType' => $attackTypeArray[$idx]);
            $attack = BMAttack::get_instance($attackType);
            foreach ($this->activeDieArrayArray[$attackerIdx] as $attackDie) {
                $attack->add_die($attackDie);
            }
            if ($attack->find_attack($this)) {
                $validAttackTypeArray[$attackType] = $attackType;
            }
        }

        $this->attack = NULL;

        if (empty($validAttackTypeArray)) {
            $validAttackTypeArray['Pass'] = 'Pass';
        }

        // james: deliberately ignore Surrender attacks here, so that it
        //        does not appear in the list of attack types

        return $validAttackTypeArray;
    }

    private function activate_GUI($activation_type, $input_parameters = NULL) {
        // currently acts as a placeholder
        $this->debug_message .= "\n{$activation_type} {$input_parameters}";
    }

    public function reset_play_state() {
        $this->activePlayerIdx = NULL;
        $this->playerWithInitiativeIdx = NULL;
        $this->activeDieArrayArray = NULL;
        $this->attack = NULL;

        $nPlayers = count($this->playerIdArray);
        $this->nRecentPasses = 0;
        $this->turnNumberInRound = 0;
        $this->capturedDieArrayArray = array_fill(0, $nPlayers, array());
        $this->waitingOnActionArray = array_fill(0, $nPlayers, FALSE);
        $this->optRequestArrayArray = array_fill(0, $nPlayers, array());
        unset($this->forceRoundResult);
    }

    private function update_active_player() {
        if (!isset($this->activePlayerIdx)) {
            throw new LogicException(
                'Active player must be set before it can be updated.'
            );
        }

        $nPlayers = count($this->playerIdArray);
        // move to the next player
        $this->activePlayerIdx = ($this->activePlayerIdx + 1) % $nPlayers;

        // currently not waiting on anyone
        $this->waitingOnActionArray = array_fill(0, $nPlayers, FALSE);
    }

    // utility methods
    public function __construct(
        $gameID = 0,
        array $playerIdArray = array(NULL, NULL),
        array $buttonRecipeArray = array('', ''),
        $maxWins = 3
    ) {
        if (count($playerIdArray) !== count($buttonRecipeArray)) {
            throw new InvalidArgumentException(
                'Number of buttons must equal the number of players.'
            );
        }

        $nPlayers = count($playerIdArray);
        $this->nPlayers = $nPlayers;
        $this->gameId = $gameID;
        $this->playerIdArray = $playerIdArray;
        $this->gameState = BMGameState::START_GAME;
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
        $roundNumber = array_sum($this->gameScoreArrayArray[0]) + 1;

        if (max($this->gameScoreArrayArray[0]['W'], $this->gameScoreArrayArray[0]['L']) >=
            $this->maxWins) {
            $roundNumber--;
        }

        return $roundNumber;
    }

    // After a round has ended, get the number of the round which just ended
    // This is simpler than the logic in get_roundNumber(), because
    // the behavior is the same in both the endgame and during-game cases
    private function get_prevRoundNumber() {
        return array_sum($this->gameScoreArrayArray[0]);
    }

    private function get_roundScoreArray() {
        if ($this->gameState <= BMGameState::SPECIFY_DICE) {
            return array_fill(0, $this->nPlayers, NULL);
        }

        $roundScoreX10Array = array_fill(0, $this->nPlayers, 0);
        $roundScoreArray = array_fill(0, $this->nPlayers, 0);

        foreach ((array)$this->activeDieArrayArray as $playerIdx => $activeDieArray) {
            $activeDieScoreX10 = 0;
            foreach ($activeDieArray as $activeDie) {
                $activeDieScoreX10 += $activeDie->get_scoreValueTimesTen();
            }
            $roundScoreX10Array[$playerIdx] = $activeDieScoreX10;
        }

        foreach ((array)$this->capturedDieArrayArray as $playerIdx => $capturedDieArray) {
            $capturedDieScoreX10 = 0;
            foreach ($capturedDieArray as $capturedDie) {
                $capturedDieScoreX10 += $capturedDie->get_scoreValueTimesTen();
            }
            $roundScoreX10Array[$playerIdx] += $capturedDieScoreX10;
        }

        foreach ($roundScoreX10Array as $playerIdx => $roundScoreX10) {
            $roundScoreArray[$playerIdx] = $roundScoreX10/10;
        }

        return $roundScoreArray;
    }

    private function get_sideScoreArray() {
        $roundScoreArray = $this->get_roundScoreArray();

        if (2 != count($roundScoreArray) ||
            is_null($roundScoreArray[0]) ||
            is_null($roundScoreArray[1])) {
            return array_fill(0, $this->nPlayers, NULL);
        }

        $sideDifference = round(2/3 * ($roundScoreArray[0] - $roundScoreArray[1]), 1);
        return array($sideDifference, -$sideDifference);
    }

    // record a game action in the history log
    public function log_action($actionType, $actingPlayerIdx, $params) {
        $this->actionLog[] = new BMGameAction(
            $this->gameState,
            $actionType,
            $actingPlayerIdx,
            $params
        );
    }

    // empty the action log after its entries have been stored in
    // the database
    public function empty_action_log() {
        $this->actionLog = array();
    }

    // N.B. The chat text has not been sanitized at this point, so don't use it for anything
    public function add_chat($playerIdx, $chat) {
        $this->chat = array('playerIdx' => $playerIdx, 'chat' => $chat);
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
                    $attAttackDieArray = array();
                    foreach ($this->attack['attackerAttackDieIdxArray'] as $attAttackDieIdx) {
                        $attAttackDieArray[] =
                            $this->activeDieArrayArray[$this->attack['attackerPlayerIdx']]
                                                      [$attAttackDieIdx];
                    }
                    return $attAttackDieArray;
                case 'defenderAttackDieArray':
                    if (!isset($this->attack)) {
                        return NULL;
                    }
                    $defAttackDieArray = array();
                    foreach ($this->attack['defenderAttackDieIdxArray'] as $defAttackDieIdx) {
                        $defAttackDieArray[] =
                            $this->activeDieArrayArray[$this->attack['defenderPlayerIdx']]
                                                      [$defAttackDieIdx];
                    }
                    return $defAttackDieArray;
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
        $funcName = 'set__'.$property;
        if (method_exists($this, $funcName)) {
            $this->$funcName($value);
        } else {
            $this->$property = $value;
        }
    }

    protected function set__nPlayers($value) {
        throw new LogicException(
            'nPlayers is derived from BMGame->playerIdArray'
        );
    }

    protected function set__turnNumberInRound($value) {
        if (FALSE ===
            filter_var(
                $value,
                FILTER_VALIDATE_INT,
                array("options"=> array("min_range"=>0))
            )) {
            throw new InvalidArgumentException(
                'Invalid turn number.'
            );
        }
        $this->turnNumberInRound = $value;
    }

    protected function set__gameId($value) {
        if (FALSE ===
            filter_var(
                $value,
                FILTER_VALIDATE_INT,
                array("options"=> array("min_range"=>0))
            )) {
            throw new InvalidArgumentException(
                'Invalid game ID.'
            );
        }
        $this->gameId = (int)$value;
    }

    protected function set__playerIdArray($value) {
        if (!is_array($value) ||
            count($value) !== count($this->playerIdArray)) {
            throw new InvalidArgumentException(
                'The number of players cannot be changed during a game.'
            );
        }
        foreach ($value as &$playerId) {
            if (!is_null($playerId)) {
                $playerId = intval($playerId);
            }
        }
        $this->playerIdArray = $value;
    }

    protected function set__activePlayerIdx($value) {
        // require a valid index
        if (FALSE ===
            filter_var(
                $value,
                FILTER_VALIDATE_INT,
                array("options"=>
                      array("min_range"=>0,
                            "max_range"=>count($this->playerIdArray)))
            )) {
            throw new InvalidArgumentException(
                'Invalid player index.'
            );
        }
        $this->activePlayerIdx = (int)$value;
    }

    protected function set__playerWithInitiativeIdx($value) {
        // require a valid index
        if (FALSE ===
            filter_var(
                $value,
                FILTER_VALIDATE_INT,
                array("options"=>
                    array("min_range"=>0,
                          "max_range"=>count($this->playerIdArray)))
            )) {
            throw new InvalidArgumentException(
                'Invalid player index.'
            );
        }
        $this->playerWithInitiativeIdx = (int)$value;
    }

    protected function set__buttonArray($value) {
        if (!is_array($value) ||
            count($value) !== count($this->playerIdArray)) {
            throw new InvalidArgumentException(
                'Number of buttons must equal the number of players.'
            );
        }
        foreach ($value as $tempValueElement) {
            if (!($tempValueElement instanceof BMButton) &&
                !is_null($tempValueElement)) {
                throw new InvalidArgumentException(
                    'Input must be an array of BMButtons.'
                );
            }
        }
        $this->buttonArray = $value;
        foreach ($this->buttonArray as $playerIdx => $button) {
            if ($button instanceof BMButton) {
                $button->playerIdx = $playerIdx;
                $button->ownerObject = $this;
            }
        }
        foreach ($this->buttonArray as $playerIdx => &$button) {
            if ($button instanceof BMButton) {
                $oppIdx = ($playerIdx + 1) % 2;
                $oppButton = $this->buttonArray[$oppIdx];
                if ($oppButton instanceof BMButton) {
                    $oppButtonName = $oppButton->name;
                    $oppButtonRecipe = $oppButton->recipe;
                } else {
                    $oppButtonName = '';
                    $oppButtonRecipe = '';
                }
                $hookResult = $button->run_hooks(
                    'load_buttons',
                    array('name' => $button->name,
                          'recipe' => $button->recipe,
                          'oppname' => $oppButtonName,
                          'opprecipe' => $oppButtonRecipe)
                );
                if (isset($hookResult) && (FALSE !== $hookResult)) {
                    $button->recipe = $hookResult['BMBtnSkill'.$button->name]['recipe'];
                    $button->hasAlteredRecipe = TRUE;
                }
            }
        }
    }

    protected function set__activeDieArrayArray($value) {
        if (!is_array($value)) {
            throw new InvalidArgumentException(
                'Active die array array must be an array.'
            );
        }
        foreach ($value as $tempValueElement) {
            if (!is_array($tempValueElement)) {
                throw new InvalidArgumentException(
                    'Individual active die arrays must be arrays.'
                );
            }
            foreach ($tempValueElement as $die) {
                if (!($die instanceof BMDie)) {
                    throw new InvalidArgumentException(
                        'Elements of active die arrays must be BMDice.'
                    );
                }
            }
        }
        $this->activeDieArrayArray = $value;
    }

    protected function set__attack($value) {
        $value = array_values($value);
        if (!is_array($value) || (5 !== count($value))) {
            throw new InvalidArgumentException(
                'There must be exactly five elements in attack.'
            );
        }
        if (!is_integer($value[0])) {
            throw new InvalidArgumentException(
                'The first element in attack must be an integer.'
            );
        }
        if (!is_integer($value[1]) && !is_null($value[1])) {
            throw new InvalidArgumentException(
                'The second element in attack must be an integer or a NULL.'
            );
        }
        if (!is_array($value[2]) || !is_array($value[3])) {
            throw new InvalidArgumentException(
                'The third and fourth elements in attack must be arrays.'
            );
        }
        if (($value[2] !== array_filter($value[2], 'is_int')) ||
            ($value[3] !== array_filter($value[3], 'is_int'))) {
            throw new InvalidArgumentException(
                'The third and fourth elements in attack must contain integers.'
            );
        }

        if (!preg_match(
            '/'.
            implode('|', BMSkill::attack_types()).
            '/',
            $value[4]
        )) {
            throw new InvalidArgumentException(
                'Invalid attack type.'
            );
        }

        if (count($value[2]) > 0 &&
            (max($value[2]) >
                 (count($this->activeDieArrayArray[$value[0]]) - 1) ||
             min($value[2]) < 0)) {
            throw new LogicException(
                'Invalid attacker attack die indices.'
            );
        }

        if (count($value[3]) > 0 &&
            (max($value[3]) >
                 (count($this->activeDieArrayArray[$value[1]]) - 1) ||
             min($value[3]) < 0)) {
            throw new LogicException(
                'Invalid defender attack die indices.'
            );
        }

        $this->attack = array('attackerPlayerIdx' => $value[0],
                              'defenderPlayerIdx' => $value[1],
                              'attackerAttackDieIdxArray' => $value[2],
                              'defenderAttackDieIdxArray' => $value[3],
                              'attackType' => $value[4]);
    }

    protected function set__attackerAttackDieArray($value) {
        throw new LogicException(
            'BMGame->attackerAttackDieArray is derived from BMGame->attack.'
        );
    }

    protected function set__defenderAttackDieArray($value) {
        throw new LogicException(
            'BMGame->defenderAttackDieArray is derived from BMGame->attack.'
        );
    }

    protected function set__nRecentPasses($value) {
        if (FALSE ===
            filter_var(
                $value,
                FILTER_VALIDATE_INT,
                array("options"=> array("min_range"=>0,
                                        "max_range"=>$this->nPlayers))
            )) {
            throw new InvalidArgumentException(
                'nRecentPasses must be an integer between zero and the number of players.'
            );
        }
        $this->nRecentPasses = $value;
    }

    protected function set__capturedDieArrayArray($value) {
        if (!is_array($value)) {
            throw new InvalidArgumentException(
                'Captured die array array must be an array.'
            );
        }
        foreach ($value as $tempValueElement) {
            if (!is_array($tempValueElement)) {
                throw new InvalidArgumentException(
                    'Individual captured die arrays must be arrays.'
                );
            }
            foreach ($tempValueElement as $tempDie) {
                if (!($tempDie instanceof BMDie)) {
                    throw new InvalidArgumentException(
                        'Elements of captured die arrays must be BMDice.'
                    );
                }
            }
        }
        $this->capturedDieArrayArray = $value;
    }

    protected function set__roundNumber($value) {
        throw new LogicException(
            'BMGame->roundNumber is derived automatically from BMGame.'
        );
    }

    protected function set__roundScoreArray($value) {
        throw new LogicException(
            'BMGame->roundScoreArray is derived automatically from BMGame.'
        );
    }

    protected function set__gameScoreArrayArray($value) {
        $value = array_values($value);
        if (!is_array($value) ||
            count($this->playerIdArray) !== count($value)) {
            throw new InvalidArgumentException(
                'There must be one game score for each player.'
            );
        }
        $tempArray = array();
        for ($playerIdx = 0; $playerIdx < count($value); $playerIdx++) {
            // check whether there are three inputs and they are all positive
            if ((3 !== count($value[$playerIdx])) ||
                min(array_map('min', $value)) < 0) {
                throw new InvalidArgumentException(
                    'Invalid W/L/T array provided.'
                );
            }
            if (array_key_exists('W', $value[$playerIdx]) &&
                array_key_exists('L', $value[$playerIdx]) &&
                array_key_exists('D', $value[$playerIdx])) {
                $tempArray[$playerIdx] = array('W' => (int)$value[$playerIdx]['W'],
                                               'L' => (int)$value[$playerIdx]['L'],
                                               'D' => (int)$value[$playerIdx]['D']);
            } else {
                $tempArray[$playerIdx] = array('W' => (int)$value[$playerIdx][0],
                                               'L' => (int)$value[$playerIdx][1],
                                               'D' => (int)$value[$playerIdx][2]);
            }
        }
        $this->gameScoreArrayArray = $tempArray;
    }

    protected function set__maxWins($value) {
        if (FALSE ===
            filter_var(
                $value,
                FILTER_VALIDATE_INT,
                array("options"=> array("min_range"=>1))
            )) {
            throw new InvalidArgumentException(
                'maxWins must be a positive integer.'
            );
        }
        $this->maxWins = (int)$value;
    }

    protected function set__gameState($value) {
        BMGameState::validate_game_state($value);
        $this->gameState = (int)$value;
    }

    protected function set__waitingOnActionArray($value) {
        if (!is_array($value) ||
            count($value) !== count($this->playerIdArray)) {
            throw new InvalidArgumentException(
                'Number of actions must equal the number of players.'
            );
        }
        foreach ($value as $tempValueElement) {
            if (!is_bool($tempValueElement)) {
                throw new InvalidArgumentException(
                    'Input must be an array of booleans.'
                );
            }
        }
        $this->waitingOnActionArray = $value;
    }

    protected function set__autopassArray($value) {
        if (!is_array($value) ||
            count($value) !== count($this->playerIdArray)) {
            throw new InvalidArgumentException(
                'Number of settings must equal the number of players.'
            );
        }
        foreach ($value as $tempValueElement) {
            if (!is_bool($tempValueElement)) {
                throw new InvalidArgumentException(
                    'Input must be an array of booleans.'
                );
            }
        }
        $this->autopassArray = $value;
    }

    protected function set__forceRoundResult($value) {
        if (!is_array($value)) {
            throw new InvalidArgumentException('Input must be an array.');
        }
        if ($this->nPlayers != count($value)) {
            throw new InvalidArgumentException(
                'Input must have the same number of elements as the number of players.'
            );
        }
        foreach ($value as $tempValueElement) {
            if (!is_bool($tempValueElement)) {
                throw new InvalidArgumentException(
                    'Input must be an array of booleans.'
                );
            }
        }
        $this->forceRoundResult = $value;
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

        foreach ($this->buttonArray as $button) {
            $buttonName = '';
            $buttonRecipe = '';
            if ($button instanceof BMButton) {
                $buttonName = $button->name;
                $buttonRecipe = $button->recipe;
            }
            $buttonNameArray[] = $buttonName;
            $buttonRecipeArray[] = $buttonRecipe;
        }

        $swingValsSpecified = TRUE;
        $dieSkillsArrayArray = array();
        $diePropsArrayArray = array();
        $dieDescArrayArray = array();

        if (isset($this->activeDieArrayArray)) {
            // create a deep clone of the original activeDieArrayArray so that changes
            // don't propagate back into the real game data
            $activeDieArrayArray = array_fill(0, $this->nPlayers, array());

            foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
                if (count($activeDieArray) > 0) {
                    foreach ($activeDieArray as $dieIdx => $activeDie) {
                        $activeDieArrayArray[$playerIdx][$dieIdx] = clone $activeDie;
                    }
                    $dieSkillsArrayArray[$playerIdx] =
                        array_fill(0, count($activeDieArray), array());
                    $diePropsArrayArray[$playerIdx] =
                        array_fill(0, count($activeDieArray), array());
                }
            }

            $nDieArray = array_map('count', $this->activeDieArrayArray);
            foreach ($activeDieArrayArray as $playerIdx => $activeDieArray) {
                $valueArrayArray[] = array();
                $sidesArrayArray[] = array();
                $dieRecipeArrayArray[] = array();
                $dieDescArrayArray[] = array();

                $swingRequestArray = array();
                if (isset($this->swingRequestArrayArray[$playerIdx])) {
                    foreach ($this->swingRequestArrayArray[$playerIdx] as $swingtype => $swingdice) {
                        if ($swingdice[0] instanceof BMDieTwin) {
                            $swingdie = $swingdice[0]->dice[0];
                        } else {
                            $swingdie = $swingdice[0];
                        }
                        if ($swingdie instanceof BMDieSwing) {
                            $validRange = $swingdie->swing_range($swingtype);
                        } else {
                            throw new LogicException(
                                "Tried to put die in swingRequestArrayArray which is not a swing die: " . $swingdie
                            );
                        }
                        $swingRequestArray[$swingtype] = array($validRange[0], $validRange[1]);
                    }
                }
                $swingReqArrayArray[] = $swingRequestArray;

                foreach ($activeDieArray as $dieIdx => $die) {
                    // hide swing information if appropriate
                    if (is_null($die->max)) {
                        $swingValsSpecified = FALSE;
                    }

                    if ($this->wereSwingOrOptionValuesReset() &&
                        ($this->gameState <= BMGameState::SPECIFY_DICE) &&
                        ($playerIdx !== $requestingPlayerIdx)) {
                        $die->value = NULL;

                        if ($die instanceof BMDieSwing) {
                            $die->swingValue = NULL;
                            $die->max = NULL;
                        }

                        if ($die instanceof BMDieTwin) {
                            foreach ($die->dice as $subdie) {
                                if ($subdie instanceof BMDieSwing) {
                                    $subdie->swingValue = NULL;
                                    $subdie->max = NULL;
                                    $die->max = NULL;
                                }
                            }
                        }

                        if ($die instanceof BMDieOption) {
                            $die->max = NULL;
                        }
                    }
                    $valueArrayArray[$playerIdx][] = $die->value;
                    $sidesArrayArray[$playerIdx][] = $die->max;
                    $dieRecipeArrayArray[$playerIdx][] = $die->recipe;
                    $dieDescArrayArray[$playerIdx][] = $die->describe(FALSE);
                    if (count($die->skillList) > 0) {
                        foreach (array_keys($die->skillList) as $skillType) {
                            $dieSkillsArrayArray[$playerIdx][$dieIdx][$skillType] = TRUE;
                        }
                    }
                    if ($die->disabled) {
                        $diePropsArrayArray[$playerIdx][$dieIdx]['disabled'] = TRUE;
                    }
                    if ($die->dizzy) {
                        $diePropsArrayArray[$playerIdx][$dieIdx]['dizzy'] = TRUE;
                    }

                    if (!empty($die->flagList)) {
                        foreach (array_keys($die->flagList) as $flag) {
                            $diePropsArrayArray[$playerIdx][$dieIdx][$flag] = TRUE;
                        }
                    }
                }
            }
        } else {
            $nDieArray = array_fill(0, $this->nPlayers, 0);
            $valueArrayArray = array_fill(0, $this->nPlayers, array());
            $sidesArrayArray = array_fill(0, $this->nPlayers, array());
            $dieRecipeArrayArray = array_fill(0, $this->nPlayers, array());
            $swingReqArrayArray = array_fill(0, $this->nPlayers, array());
        }

        if (is_null($this->optRequestArrayArray)) {
            $optRequestArrayArray = array_fill(0, $this->nPlayers, array());
        } else {
            $optRequestArrayArray = $this->optRequestArrayArray;
        }

        if (empty($this->prevSwingValueArrayArray)) {
            $prevSwingValueArrayArray = array_fill(0, $this->nPlayers, array());
        } else {
            $prevSwingValueArrayArray = $this->prevSwingValueArrayArray;
        }

        if (empty($this->prevOptValueArrayArray)) {
            $prevOptValueArrayArray = array_fill(0, $this->nPlayers, array());
        } else {
            $prevOptValueArrayArray = $this->prevOptValueArrayArray;
        }

        $nCapturedDieArray = array_fill(0, $this->nPlayers, 0);
        $captValueArrayArray = array_fill(0, $this->nPlayers, array());
        $captSidesArrayArray = array_fill(0, $this->nPlayers, array());
        $captRecipeArrayArray = array_fill(0, $this->nPlayers, array());
        $captDiePropsArrayArray = array_fill(0, $this->nPlayers, array());

        if (isset($this->capturedDieArrayArray)) {
            $nCapturedDieArray = array_map('count', $this->capturedDieArrayArray);
            foreach ($this->capturedDieArrayArray as $playerIdx => $capturedDieArray) {
                foreach ($capturedDieArray as $dieIdx => $die) {
                    // hide swing information if appropriate
                    $dieValue = $die->value;
                    $dieMax = $die->max;

                    if ($this->wereSwingOrOptionValuesReset() &&
                        ($this->gameState <= BMGameState::SPECIFY_DICE) &&
                        ($playerIdx !== $requestingPlayerIdx)) {
                        $dieValue = NULL;
                        $dieMax = NULL;
                    }
                    $captValueArrayArray[$playerIdx][] = $dieValue;
                    $captSidesArrayArray[$playerIdx][] = $dieMax;
                    $captRecipeArrayArray[$playerIdx][] = $die->recipe;

                    if (!empty($die->flagList)) {
                        foreach (array_keys($die->flagList) as $flag) {
                            $captDiePropsArrayArray[$playerIdx][$dieIdx][$flag] = TRUE;
                        }
                    }
                }
            }
        }

        if (!$swingValsSpecified) {
            foreach ($valueArrayArray as &$valueArray) {
                foreach ($valueArray as &$value) {
                    $value = NULL;
                }
            }
        }

        // If it's someone's turn to attack, report the valid attack
        // types as part of the game data
        if ($this->gameState == BMGameState::START_TURN) {
            $validAttackTypeArray = $this->valid_attack_types();
        } else {
            $validAttackTypeArray = array();
        }

        $dataArray =
            array('gameId'                   => $this->gameId,
                  'gameState'                => BMGameState::as_string($this->gameState),
                  'roundNumber'              => $this->get_roundNumber(),
                  'maxWins'                  => $this->maxWins,
                  'activePlayerIdx'          => $this->activePlayerIdx,
                  'playerWithInitiativeIdx'  => $this->playerWithInitiativeIdx,
                  'playerIdArray'            => $this->playerIdArray,
                  'buttonNameArray'          => $buttonNameArray,
                  'buttonRecipeArray'        => $buttonRecipeArray,
                  'waitingOnActionArray'     => $this->waitingOnActionArray,
                  'nDieArray'                => $nDieArray,
                  'valueArrayArray'          => $valueArrayArray,
                  'sidesArrayArray'          => $sidesArrayArray,
                  'dieSkillsArrayArray'      => $dieSkillsArrayArray,
                  'diePropertiesArrayArray'  => $diePropsArrayArray,
                  'dieRecipeArrayArray'      => $dieRecipeArrayArray,
                  'dieDescriptionArrayArray' => $dieDescArrayArray,
                  'nCapturedDieArray'        => $nCapturedDieArray,
                  'capturedValueArrayArray'  => $captValueArrayArray,
                  'capturedSidesArrayArray'  => $captSidesArrayArray,
                  'capturedRecipeArrayArray' => $captRecipeArrayArray,
                  'capturedDiePropsArrayArray'   => $captDiePropsArrayArray,
                  'swingRequestArrayArray'   => $swingReqArrayArray,
                  'optRequestArrayArray'     => $optRequestArrayArray,
                  'prevSwingValueArrayArray'     => $prevSwingValueArrayArray,
                  'prevOptValueArrayArray'       => $prevOptValueArrayArray,
                  'validAttackTypeArray'     => $validAttackTypeArray,
                  'roundScoreArray'          => $this->get_roundScoreArray(),
                  'sideScoreArray'           => $this->get_sideScoreArray(),
                  'gameScoreArrayArray'      => $this->gameScoreArrayArray,
                  'lastActionTimeArray'      => $this->lastActionTimeArray);

        return array('status' => 'ok', 'data' => $dataArray);
    }

    protected function wereSwingOrOptionValuesReset() {
        // james: need to also consider the case of many multiple draws in a row
        foreach ($this->gameScoreArrayArray as $gameScoreArray) {
            if ($gameScoreArray['W'] > 0 || $gameScoreArray['D'] > 0) {
                return FALSE;
            }
        }

        return TRUE;
    }
}
