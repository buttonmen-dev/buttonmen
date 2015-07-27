<?php
/**
 * BMGame: current status of a game
 *
 * @author james
 */

/**
 * This class contains all the logic to do with games, specified at each game state
 *
 * @property      int   $gameId                  Game ID number in the database
 * @property      array $playerIdArray           Array of player IDs
 * @property-read array $nPlayers                Number of players in the game
 * @property-read int   $roundNumber;            Current round number
 * @property      int   $turnNumberInRound;      Current turn number in current round
 * @property      int   $activePlayerIdx         Index of the active player in playerIdxArray
 * @property      int   $nextPlayerIdx           Index of the next player to take a turn in playerIdxArray
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
 * @property-read array $outOfPlayDieArrayArray  Out-of-play dice for all players
 * @property-read array $roundScoreArray         Current points score in this round
 * @property-read array $gameScoreArrayArray     Number of games W/L/D for all players
 * @property-read array $isPrevRoundWinnerArray  Boolean array whether each player won the previous round
 * @property      int   $maxWins                 The game ends when a player has this many wins
 * @property-read BMGameState $gameState         Current game state as a BMGameState enum
 * @property      array $waitingOnActionArray    Boolean array whether each player needs to perform an action
 * @property      array $autopassArray           Boolean array whether each player has enabled autopass
 * @property-read int   $firingAmount            Amount of firing that has been set by the attacker
 * @property      array $actionLog               Game actions taken by this BMGame instance
 * @property      array $chat                    A chat message submitted by the active player
 * @property      string $description;           Description provided when the game was created
 * @property      int   $previousGameId;         The game whose chat is being continued with this game
 * @property-read string $message                Message to be passed to the GUI
 * @property      array $swingRequestArrayArray  Swing requests for all players
 * @property      array $swingValueArrayArray    Swing values for all players
 * @property      array $prevSwingValueArrayArray Swing values for previous round for all players
 * @property      array $optRequestArrayArray    Option requests for all players
 * @property      array $optValueArrayArray      Option values for current round for all players
 * @property      array $prevOptValueArrayArray  Option values for previous round for all players
 * @property      array $lastActionTimeArray     Times of last actions for each player
 * @property      array $hasPlayerAcceptedGameArray  Boolean array whether each player has accepted this game
 * @property      array $hasPlayerDismissedGameArray    Whether or not each player has dismissed this game
 *
 * @SuppressWarnings(PMD.TooManyFields)
 * @SuppressWarnings(PMD.TooManyMethods)
 * @SuppressWarnings(PMD.UnusedPrivateField)
 */
class BMGame {
    // properties -- all accessible, but written as protected to enable the use of
    //               getters and setters

    /**
     * Game ID number in the database
     *
     * @var int
     */
    protected $gameId;

    /**
     * Array of player IDs
     *
     * @var array
     */
    protected $playerIdArray;

    /**
     * Number of players in the game
     *
     * @var int
     */
    protected $nPlayers;

    /**
     * Current round number
     *
     * @var int
     */
    protected $roundNumber;

    /**
     * Current turn number in current round
     *
     * @var int
     */
    protected $turnNumberInRound;

    /**
     * Index of the active player in playerIdxArray
     *
     * @var int
     */
    protected $activePlayerIdx;

    /**
     * Index of the next player to take a turn in playerIdxArray
     *
     * @var int
     */
    protected $nextPlayerIdx;

    /**
     * Index of the player who won initiative
     *
     * @var int
     */
    protected $playerWithInitiativeIdx;

    /**
     * Buttons for all players
     *
     * @var array
     */
    protected $buttonArray;

    /**
     * Active dice for all players
     *
     * @var type
     */
    protected $activeDieArrayArray;

    /**
     * Details of attack
     *
     * array(
     *   'attackerPlayerIdx',
     *   'defenderPlayerIdx',
     *   'attackerAttackDieIdxArray',
     *   'defenderAttackDieIdxArray',
     *   'attackType'
     * )
     *
     * @var array
     */
    protected $attack;

    /**
     * Index in playerIdxArray of the attacker
     *
     * @var int
     */
    protected $attackerPlayerIdx;

    /**
     * Index in playerIdxArray of the defender
     *
     * @var int
     */
    protected $defenderPlayerIdx;

    /**
     * Array of all attacker's dice
     *
     * @var array
     */
    protected $attackerAllDieArray;

    /**
     * Array of all defender's dice
     *
     * @var array
     */
    protected $defenderAllDieArray;

    /**
     * Array of attacker's dice used in attack
     *
     * @var array
     */
    protected $attackerAttackDieArray;

    /**
     * Array of defender's dice used in attack
     *
     * @var array
     */
    protected $defenderAttackDieArray;

    /**
     * Array storing player decisions about auxiliary dice
     *
     * @var array
     */
    protected $auxiliaryDieDecisionArrayArray;

    /**
     * Number of consecutive passes
     *
     * @var int
     */
    protected $nRecentPasses;

    /**
     * Captured dice for all players
     *
     * @var array
     */
    protected $capturedDieArrayArray;

    /**
     * Out-of-play dice for all players
     *
     * @var array
     */
    protected $outOfPlayDieArrayArray;

    /**
     * Current points score in this round
     *
     * @var array
     */
    protected $roundScoreArray;

    /**
     * Number of games W/L/D for all players
     *
     * @var array
     */
    protected $gameScoreArrayArray;

    /**
     * Boolean array whether each player won the previous round
     *
     * @var array
     */
    protected $isPrevRoundWinnerArray;

    /**
     * The game ends when a player has this many wins
     *
     * @var int
     */
    protected $maxWins;

    /**
     * Current game state as a BMGameState enum
     *
     * @var int
     */
    protected $gameState;

    /**
     * Boolean array whether each player needs to perform an action
     *
     * @var array
     */
    protected $waitingOnActionArray;

    /**
     * Boolean array whether each player has enabled autopass
     *
     * @var array
     */
    protected $autopassArray;

    /**
     * Amount of firing that has been submitted
     *
     * @var int
     */
    protected $firingAmount;

    /**
     * Game actions taken by this BMGame instance
     *
     * @var array
     */
    protected $actionLog;

    /**
     * Chat message submitted by the active player with an attack
     *
     * array(
     *   'playerIdx',
     *   'chat'
     * )
     *
     * @var array
     */
    protected $chat;

    /**
     * Description provided when the game was created
     *
     * @var string
     */
    protected $description;

    /**
     * The game whose chat is being continued with this game
     *
     * @var int
     */
    protected $previousGameId;

    /**
     * Message to be passed to the GUI
     *
     * @var string
     */
    protected $message;

    /**
     * Boolean array whether each player has won the round
     *
     * @var array
     */
    protected $forceRoundResult;

    /**
     * Array of arrays containing swing value requests
     *
     * @var array
     */
    public $swingRequestArrayArray;

    /**
     * Array of arrays containing chosen swing values
     *
     * @var array
     */
    public $swingValueArrayArray;

    /**
     * Array of arrays containing chosen swing values from last round
     *
     * @var array
     */
    public $prevSwingValueArrayArray;

    /**
     * Array of arrays containing option value requests
     *
     * @var array
     */
    public $optRequestArrayArray;

    /**
     * Array of arrays containing chosen option values
     *
     * @var array
     */
    public $optValueArrayArray;

    /**
     * Array of arrays containing chosen option values from last round
     *
     * @var array
     */
    public $prevOptValueArrayArray;

    /**
     * Array holding the times that each player performed an action
     *
     * @var array
     */
    public $lastActionTimeArray;

    /**
     * Used by the database to record whether the choice of the
     * button was random or not
     *
     * @var array
     */
    public $isButtonChoiceRandom;

    /**
     * Used by the database to record whether each player has accepted this game
     *
     * @var array
     */
    public $hasPlayerAcceptedGameArray;

    /**
     * Used by the database to record whether each player has dismissed this game
     *
     * @var array
     */
    public $hasPlayerDismissedGameArray;

    /**
     * Internal cache of fire info, used for logging
     *
     * @var array
     */
    protected $fireCache;

    /**
     * Boolean signalling whether the debug flag is active or not.
     *
     * Used only for debugging
     *
     * @var bool
     */
    protected $debug;

    // methods
    public function do_next_step() {
        if (!isset($this->gameState)) {
            throw new LogicException('Game state must be set.');
        }

        $this->debug_message = 'ok';

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
                $this->gameState = BMGameState::CHOOSE_JOIN_GAME;
            }
        }
    }

    protected function do_next_step_choose_join_game() {

    }

    protected function update_game_state_choose_join_game() {
        if (isset($this->hasPlayerAcceptedGameArray) &&
            is_array($this->hasPlayerAcceptedGameArray) &&
            in_array(FALSE, $this->hasPlayerAcceptedGameArray)) {
            foreach ($this->hasPlayerAcceptedGameArray as $playerIdx => $hasAccepted) {
                $this->waitingOnActionArray[$playerIdx] = !$hasAccepted;
            }

            return;
        }

        $this->gameState = BMGameState::SPECIFY_RECIPES;
    }

    protected function do_next_step_specify_recipes() {
        if (isset($this->buttonArray)) {
            foreach ($this->buttonArray as $buttonIdx => $button) {
                $oppButtonIdx = ($buttonIdx + 1) % 2;
                $button->run_hooks(
                    'specify_recipes',
                    array('button' => $button,
                          'oppbutton' => $this->buttonArray[$oppButtonIdx])
                );
            }
        }
    }

    protected function update_game_state_specify_recipes() {
        foreach ($this->buttonArray as $button) {
            if (empty($button->recipe)) {
                return;
            }
        }

        $this->gameState = BMGameState::LOAD_DICE_INTO_BUTTONS;
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
        $this->load_swing_values_from_previous_round();
        $this->load_option_values_from_previous_round();
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

    protected function load_swing_values_from_previous_round() {
        if (!isset($this->swingValueArrayArray)) {
            return;
        }

        foreach ($this->activeDieArrayArray as $playerIdx => &$activeDieArray) {
            if (empty($this->swingValueArrayArray[$playerIdx])) {
                continue;
            }

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

    protected function load_option_values_from_previous_round() {
        if (!isset($this->optValueArrayArray)) {
            return;
        }

        foreach ($this->optValueArrayArray as $playerIdx => $optionValueArray) {
            if (!empty($optionValueArray)) {
                $dieIndicesWithoutReserve = $this->die_indices_without_reserve($playerIdx);

                foreach ($optionValueArray as $dieIdx => $optionValue) {
                    $die = $this->activeDieArrayArray[$playerIdx][$dieIndicesWithoutReserve[$dieIdx]];
                    if (!($die instanceof BMDieOption)) {
                        throw new LogicException('Die must be an option die.');
                    }

                    $die->set_optionValue($optionValue);
                }
            }
        }
    }

    protected function die_indices_without_reserve($playerIdx) {
        $activeDieArray = $this->activeDieArrayArray[$playerIdx];
        $hasReserveArray = array_fill(0, count($activeDieArray), FALSE);

        foreach ($activeDieArray as $dieIdx => $die) {
            if ($die->has_skill('Reserve')) {
                $hasReserveArray[$dieIdx] = TRUE;
            }
        }

        $dieIndicesWithoutReserve = array_keys($hasReserveArray, FALSE, TRUE);
        return($dieIndicesWithoutReserve);
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
                if ($die->has_flag('AddAuxiliary')) {
                    $hasChosenAuxDie[$playerIdx] = TRUE;
                    break;
                }
            }
        }

        $useAuxDice = (1 == array_product($hasChosenAuxDie));

        if ($useAuxDice) {
            foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
                foreach ($activeDieArray as $die) {
                    if ($die->has_flag('AddAuxiliary')) {
                        $die->remove_skill('Auxiliary');
                        $die->remove_flag('AddAuxiliary');
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

            $this->update_opt_requests_to_ignore_reserve_dice();
            $this->remove_dice_with_skill('Reserve');
            $this->gameState = BMGameState::SPECIFY_DICE;
        }
    }

    protected function add_selected_reserve_dice() {
        $areAnyDiceAdded = array_fill(0, $this->nPlayers, FALSE);

        if (isset($this->activeDieArrayArray)) {
            foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
                foreach ($activeDieArray as $die) {
                    if ($die->has_flag('AddReserve')) {
                        $die->remove_skill('Reserve');
                        $die->remove_flag('AddReserve');
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

    protected function update_opt_requests_to_ignore_reserve_dice() {
        if (empty($this->optRequestArrayArray)) {
            return;
        }

        $optRequestArrayArray = $this->optRequestArrayArray;

        foreach ($optRequestArrayArray as $playerIdx => $optRequestArray) {
            if (empty($optRequestArray)) {
                continue;
            }

            $newOptRequestArray = array();
            $dieIndicesWithoutReserve = $this->die_indices_without_reserve($playerIdx);

            foreach ($optRequestArray as $dieIdx => $optRequest) {
                $newDieIdx = array_search($dieIdx, $dieIndicesWithoutReserve, TRUE);
                if (FALSE !== $newDieIdx) {
                    $newOptRequestArray[$newDieIdx] = $optRequest;
                }
            }

            $optRequestArrayArray[$playerIdx] = $newOptRequestArray;
        }

        $this->optRequestArrayArray = $optRequestArrayArray;
    }

    protected function do_next_step_specify_dice() {
        $this->waitingOnActionArray =
            array_fill(0, count($this->playerIdArray), FALSE);

        $this->initialise_swing_value_array_array();
        $this->set_option_values();
        $this->set_swing_values();
        $this->roll_active_dice_needing_values();
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

    protected function roll_active_dice_needing_values() {
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

                if (empty($die->value)) {
                    $this->activeDieArrayArray[$playerIdx][$dieIdx] =
                        $die->make_play_die(FALSE);
                }
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
        $response =
            BMGame::does_player_have_initiative_array(
                $this->activeDieArrayArray,
                $this->buttonArray,
                TRUE
            );
        $hasInitiativeArray = $response['hasPlayerInitiative'];
        $actionLogInfo = array(
            'roundNumber' => $this->get__roundNumber(),
            'playerData' => array(),
        );
        foreach ($response['actionLogInfo'] as $playerIdx => $playerActionLogData) {
            $actionLogInfo['playerData'][$this->playerIdArray[$playerIdx]] = $playerActionLogData;
        }

        if (array_sum($hasInitiativeArray) > 1) {
            $playersWithInit = array();
            $actionLogInfo['tiedPlayerIds'] = array();
            foreach ($hasInitiativeArray as $playerIdx => $tempHasInitiative) {
                if ($tempHasInitiative) {
                    $playersWithInit[] = $playerIdx;
                    $actionLogInfo['tiedPlayerIds'][] = $this->playerIdArray[$playerIdx];
                }
            }
            $randIdx = bm_rand(0, count($playersWithInit) - 1);
            $tempInitiativeIdx = $playersWithInit[$randIdx];
        } else {
            $tempInitiativeIdx =
                array_search(TRUE, $hasInitiativeArray, TRUE);
        }

        $this->playerWithInitiativeIdx = $tempInitiativeIdx;
        $actionLogInfo['initiativeWinnerId'] = $this->playerIdArray[$this->playerWithInitiativeIdx];

        // if this is an initiative redetermination following a focus turndown or chance reroll,
        // we don't need to make another log entry.  Inspect any previous log entries made during
        // this player action to find out whether that is the case.
        $initReactSeen = FALSE;
        if (count($this->actionLog) > 0) {
            foreach ($this->actionLog as $prevEntry) {
                if ($prevEntry->actionType == 'turndown_focus' ||
                    $prevEntry->actionType == 'reroll_chance') {
                    $initReactSeen = TRUE;
                }
            }
        }
        if (!$initReactSeen) {
            $this->log_action(
                'determine_initiative',
                0,
                $actionLogInfo
            );
        }
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
                    $activeDie->remove_flag('Disabled');
                }

                if (is_array($hookResultArray) && count($hookResultArray) > 0) {
                    $canDieReact = FALSE;

                    foreach ($hookResultArray as $hookResult) {
                        if ('forceFalse' === $hookResult) {
                            continue 2;
                        }

                        if (TRUE === $hookResult) {
                            $canDieReact = TRUE;
                        }
                    }

                    if ($canDieReact) {
                        $canReactArray[$playerIdx] = TRUE;
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
                                $activeDie->remove_flag('Disabled');
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
                            $activeDie->has_flag('Dizzy')) {
                            $activeDie->remove_flag('Dizzy');
                        }
                    }
                }
            }
        }
    }

    protected function do_next_step_start_turn() {
        $this->firingAmount = NULL;
        $this->perform_autopass();

        $this->waitingOnActionArray = array_fill(0, $this->nPlayers, FALSE);

        if (!isset($this->attack)) {
            $this->waitingOnActionArray[$this->activePlayerIdx] = TRUE;
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

    protected function update_game_state_start_turn() {
        if (FALSE !== array_search(TRUE, $this->waitingOnActionArray, TRUE)) {
            return;
        }

        if (!$this->are_attack_params_reasonable()) {
            $this->attack = NULL;
            return;
        }

        $instance = $this->create_attack_instance();
        if (FALSE === $instance) {
            $this->attack = NULL;
            return;
        }

        $this->add_flag_to_attackers();
        $this->add_flag_to_attack_targets();

        $this->gameState = BMGameState::ADJUST_FIRE_DICE;
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
        if (!isset($this->attack)) {
            $this->regenerate_attack();
        }

        $attack = BMAttack::create($this->attack['attackType']);

        $this->attackerPlayerIdx = $this->attack['attackerPlayerIdx'];
        $this->defenderPlayerIdx = $this->attack['defenderPlayerIdx'];
        $attAttackDieArray = array();
        foreach ($this->attack['attackerAttackDieIdxArray'] as $attackerAttackDieIdx) {
            $attackDie =
                &$this->activeDieArrayArray[$this->attack['attackerPlayerIdx']]
                                           [$attackerAttackDieIdx];
            if ($attackDie->has_flag('Dizzy')) {
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
            $defAttackDieArray,
            $this->firingAmount
        );

        if (!$valid) {
            $this->activate_GUI('Invalid attack');
            $this->waitingOnActionArray[$this->activePlayerIdx] = TRUE;
            $this->attack = NULL;
            return FALSE;
        }

        if ('Default' == $this->attack['attackType']) {
            $attack->resolve_default_attack($this);
            $attack = BMAttack::create($this->attack['attackType']);
        }

        return array('attack' => $attack,
                     'attAttackDieArray' => $attAttackDieArray,
                     'defAttackDieArray' => $defAttackDieArray);
    }

    protected function regenerate_attack() {
        // james: this is currently to regenerate an attack during firing

        $attackerPlayerIdx = NULL;
        $defenderPlayerIdx = NULL;
        $attackerAttackDieIdxArray = array();
        $defenderAttackDieIdxArray = array();
        $attackType = NULL;

        foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
            foreach ($activeDieArray as $dieIdx => $die) {
                if ($die->has_flag('IsAttacker')) {
                    $attackerAttackDieIdxArray[] = $dieIdx;
                    $attackerPlayerIdx = $playerIdx;
                    $attackType = $die->flagList['IsAttacker']->value();
                } elseif ($die->has_flag('IsAttackTarget')) {
                    $defenderAttackDieIdxArray[] = $dieIdx;
                    $defenderPlayerIdx = $playerIdx;
                }
            }
        }

        $this->attack = array(
            'attackerPlayerIdx' => $attackerPlayerIdx,
            'defenderPlayerIdx' => $defenderPlayerIdx,
            'attackerAttackDieIdxArray' => $attackerAttackDieIdxArray,
            'defenderAttackDieIdxArray' => $defenderAttackDieIdxArray,
            'attackType' => $attackType
        );
    }

    protected function add_flag_to_attackers() {
        if (empty($this->attack['attackerAttackDieIdxArray'])) {
            return;
        }

        foreach ($this->attack['attackerAttackDieIdxArray'] as $attackerAttackDieIdx) {
            $attackDie = &$this->activeDieArrayArray[$this->attack['attackerPlayerIdx']]
                                                    [$attackerAttackDieIdx];
            $attackDie->add_flag('IsAttacker', $this->attack['attackType']);
        }
    }

    protected function add_flag_to_attack_targets() {
        if (empty($this->attack['defenderAttackDieIdxArray'])) {
            return;
        }

        foreach ($this->attack['defenderAttackDieIdxArray'] as $defenderAttackDieIdx) {
            $defenderDie = &$this->activeDieArrayArray[$this->attack['defenderPlayerIdx']]
                                                      [$defenderAttackDieIdx];
            $defenderDie->add_flag('IsAttackTarget');
        }
    }

    protected function do_next_step_adjust_fire_dice() {
        if ($this->needs_firing()) {
            $this->waitingOnActionArray[$this->attack['attackerPlayerIdx']] = TRUE;
        }
    }

    protected function needs_firing() {
        $attackType = $this->attack['attackType'];

        if (($attackType != 'Power') &&
            ($attackType != 'Skill')) {
            return FALSE;
        }

        $attackerValueSum = 0;
        $defenderValueSum = 0;

        $attackerDieArray = array();
        $attackerPlayerIdx = $this->attack['attackerPlayerIdx'];
        foreach ($this->attack['attackerAttackDieIdxArray'] as $attackerDieIdx) {
            $attackerDie = $this->activeDieArrayArray[$attackerPlayerIdx][$attackerDieIdx];
            $attackerValueSum += $attackerDie->value;
            $attackerDieArray[] = $attackerDie;
        }

        $defenderDieArray = array();
        $defenderPlayerIdx = $this->attack['defenderPlayerIdx'];
        foreach ($this->attack['defenderAttackDieIdxArray'] as $defenderDieIdx) {
            $defenderDie = $this->activeDieArrayArray[$defenderPlayerIdx][$defenderDieIdx];
            $defenderValueSum += $defenderDie->value;
            $defenderDieArray[] = $defenderDie;
        }

        // check for need for firing:
        // sum of attacker values is less than defender value
        $needsFiring = $attackerValueSum < $defenderValueSum;

        if ($needsFiring) {
            // Do rudimentary sanity check that the attacker actually has fire dice
            // Note that this doesn't actually do a full check that any fire dice are not part
            // of the attack already, nor that the fire dice can be turned down the correct amount
            // since such checks should have been part of find_attack()
            $hasFireDice = FALSE;
            foreach ($this->activeDieArrayArray[$this->attack['attackerPlayerIdx']] as $die) {
                if ($die->has_skill('Fire')) {
                    $hasFireDice = TRUE;
                    break;
                }
            }

            if (!$hasFireDice) {
                throw new LogicException('Needs firing, but attacker has no fire dice');
            }

            $this->log_action(
                'needs_firing',
                $this->playerIdArray[$this->attackerPlayerIdx],
                array(
                    'attackType' => $this->attack['attackType'],
                    'attackDice' => $this->get_action_log_data(
                        $attackerDieArray,
                        $defenderDieArray
                    ),
                )
            );
        }

        return $needsFiring;
    }

    protected function update_game_state_adjust_fire_dice() {
        if (FALSE !== array_search(TRUE, $this->waitingOnActionArray, TRUE)) {
            return;
        }

        $this->gameState = BMGameState::COMMIT_ATTACK;
    }

    // turn_down_fire_dice expects one of the following two input arrays:
    //
    //   1.  array('action' => 'cancel',
    //             'playerIdx' => $playerIdx,
    //             'dieIdxArray' => $dieIdxArray,
    //             'dieValueArray' => $dieValueArray)
    //       where $dieIdxArray and $dieValueArray are the raw inputs to
    //       BMInterface->adjust_fire()
    //
    //   2.  array('action' => 'turndown',
    //             'playerIdx' => $playerIdx,
    //             'fireValueArray' => array($dieIdx1 => $dieValue1,
    //                                       $dieIdx2 => $dieValue2))
    //       where the details of SOME or ALL fire dice are in $fireValueArray
    //
    // It returns a boolean telling whether the reaction has been successful.
    // If it fails, $game->message will say why it has failed.

    public function react_to_firing(array $args) {
        if (BMGameState::ADJUST_FIRE_DICE != $this->gameState) {
            $this->message = 'Wrong game state to react to firing.';
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

        if (!in_array($args['action'], array('turndown', 'cancel'))) {
            throw new InvalidArgumentException(
                'Reaction must be turndown or cancel.'
            );
        }

        $reactFuncName = 'react_to_firing_'.$args['action'];
        $reactResponse = $this->$reactFuncName($args);

        return $reactResponse;
    }

    // $fireValueArray is an associative array, with the keys being the
    // die indices of the attacker die array that are being specified
    protected function react_to_firing_turndown(array $args) {
        if (BMGameState::ADJUST_FIRE_DICE != $this->gameState) {
            $this->message = 'Wrong game state to react to firing.';
            return FALSE;
        }

        $instance = $this->create_attack_instance();
        if (FALSE === $instance) {
            $this->message = 'Invalid attack.';
            return FALSE;
        }

        $attackerIdx = $this->attack['attackerPlayerIdx'];

        $firingAmount = 0;
        foreach ($args['fireValueArray'] as $fireIdx => $newValue) {
            $die = $this->activeDieArrayArray[$attackerIdx][$fireIdx];
            if (!$die->has_skill('Fire')) {
                $this->message = 'Cannot turn down non-fire die.';
                return FALSE;
            }

            if ($newValue > $die->value) {
                $this->message = 'Cannot turn die value up.';
                return FALSE;
            }

            if ($newValue < $die->min) {
                $this->message = 'Cannot turn die value down past minimum.';
                return FALSE;
            }

            $firingAmount += $die->value - $newValue;
        }

        if (!$instance['attack']->validate_attack(
            $this,
            $instance['attAttackDieArray'],
            $instance['defAttackDieArray'],
            $firingAmount
        )) {
            $this->message = $instance['attack']->validationMessage;
            return FALSE;
        }

        $activeDieArrayArray = $this->activeDieArrayArray;
        $fireRecipes = array();
        $oldValues = array();
        $newValues = array();

        foreach ($args['fireValueArray'] as $fireIdx => $newValue) {
            $fireDie = $activeDieArrayArray[$attackerIdx][$fireIdx];

            $fireRecipes[] = $fireDie->recipe;
            $oldValues[] = $fireDie->value;
            $newValues[] = $newValue;

            $fireDie->value = $newValue;
        }

        $this->firingAmount = $firingAmount;
        $this->waitingOnActionArray = array_fill(0, $this->nPlayers, FALSE);

        $this->fireCache = array(
            'fireRecipes' => $fireRecipes,
            'oldValues' => $oldValues,
            'newValues' => $newValues,
        );

        $this->message = 'Successfully turned down fire dice.';
        return TRUE;
    }

    protected function react_to_firing_cancel() {
        $this->attack = NULL;
        $this->gameState = BMGameState::START_TURN;
        $this->waitingOnActionArray = array_fill(0, $this->nPlayers, FALSE);
        $this->waitingOnActionArray[$this->activePlayerIdx] = TRUE;

        foreach ($this->activeDieArrayArray as $activeDieArray) {
            if (empty($activeDieArray)) {
                continue;
            }

            foreach ($activeDieArray as $die) {
                $die->remove_flag('IsAttacker');
                $die->remove_flag('IsAttackTarget');
            }
        }

        $this->log_action(
            'fire_cancel',
            $this->playerIdArray[$this->activePlayerIdx],
            array(
                'action' => 'cancel',
            )
        );

        $this->message = 'Cancelled fire attack.';
        return TRUE;
    }

    protected function do_next_step_commit_attack() {
        // display dice
        $this->activate_GUI('show_active_dice');

        $instance = $this->create_attack_instance();
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

        if (empty($this->fireCache)) {
            $fireCache = NULL;
        } else {
            $fireCache = $this->fireCache;
        }

        $this->log_action(
            'attack',
            $this->playerIdArray[$this->attackerPlayerIdx],
            array(
                'attackType' => $attack->type_for_log(),
                'preAttackDice' => $preAttackDice,
                'postAttackDice' => $postAttackDice,
                'fireCache' => $fireCache,
            )
        );

        if (isset($this->activePlayerIdx)) {
            $this->update_active_player();
        }
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

    protected function update_game_state_commit_attack() {
        if (isset($this->attack) &&
            FALSE === array_search(TRUE, $this->waitingOnActionArray, TRUE)) {
            if (isset($this->activeDieArrayArray) &&
                isset($this->attack['attackerPlayerIdx'])) {
                foreach ($this->activeDieArrayArray[$this->attack['attackerPlayerIdx']] as &$activeDie) {
                    if ($activeDie->has_flag('Dizzy')) {
                            $activeDie->remove_flag('Dizzy');
                    }
                }
            }
        }

        unset($this->fireCache);
        $this->gameState = BMGameState::CHOOSE_TURBO_SWING;
    }

    protected function do_next_step_choose_turbo_swing() {

    }

    protected function update_game_state_choose_turbo_swing() {
        $this->gameState = BMGameState::END_TURN;
    }

    protected function do_next_step_end_turn() {
        $this->perform_end_of_turn_die_actions();
        $this->firingAmount = NULL;
    }

    protected function perform_end_of_turn_die_actions() {
        $preRerollDieInfo = array();
        $postRerollDieInfo = array();
        $hasRerolled = FALSE;

        foreach ($this->get__attackerAllDieArray() as $die) {
            $preRerollDieInfo[] = $die->get_action_log_data();

            $hasRerolled |= $die->run_hooks(
                __FUNCTION__,
                array(
                    'die' => $die,
                    'attackType' => $this->attack['attackType']
                )
            );

            $postRerollDieInfo[] = $die->get_action_log_data();

            $die->remove_flag('IsAttacker');
        }

        if ($hasRerolled) {
            $this->log_action(
                'ornery_reroll',
                $this->playerIdArray[$this->attackerPlayerIdx],
                array(
                    'preRerollDieInfo' => $preRerollDieInfo,
                    'postRerollDieInfo' => $postRerollDieInfo,
                )
            );
        }
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
        $roundScoreArray = $this->get__roundScoreArray();
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
    //       where the details of SOME or ALL focus dice are in $focusValueArray
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

        if ($newInitiativeArray[$playerIdx] && (1 == array_sum($newInitiativeArray))) {
            $this->gameState = BMGameState::DETERMINE_INITIATIVE;
        } else {
            // only need to disable chance dice if the reroll fails to gain initiative
            foreach ($this->activeDieArrayArray[$playerIdx] as &$die) {
                if ($die->has_skill('Chance')) {
                    $die->add_flag('Disabled');
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

                $possChanceAction =
                    $this->possibleChanceAction($die, $dieValueArray, $dieIdxArray);
                $possFocusAction =
                    $this->possibleFocusAction($die, $dieValueArray, $dieIdxArray, $listIdx);

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

    protected function possibleChanceAction($die, $dieValueArray, $dieIdxArray) {
        // check for ANY selected dice, not just a single die
        $possChanceAction = $die->has_skill('Chance') &&
                            !isset($dieValueArray) &&
                            (count($dieIdxArray) >= 1);

        return $possChanceAction;
    }

    protected function possibleFocusAction($die, $dieValueArray, $dieIdxArray, $listIdx) {
        // check for ANY change in die value, also invalid changes
        $possFocusAction = $die->has_skill('Focus') &&
                           is_array($dieValueArray) &&
                           (count($dieIdxArray) == count($dieValueArray)) &&
                           ($die->value != $dieValueArray[$listIdx]);

        return $possFocusAction;
    }

    protected function react_to_initiative_focus($args) {
        $isValid = $this->validateFocusAction($args);

        if (!$isValid) {
            return FALSE;
        }

        $playerIdx = $args['playerIdx'];

        // change specified die values
        $oldDieValueArray = array();
        $preTurndownData = array();
        $postTurndownData = array();
        foreach ($args['focusValueArray'] as $dieIdx => $newDieValue) {
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
                    $this->activeDieArrayArray[$playerIdx][$dieIdx]->add_flag('Dizzy');
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

    protected function validateFocusAction($args) {
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

        return TRUE;
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

        if (!$die->does_skip_swing_request()) {
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

    public static function does_player_have_initiative_array(
        array $activeDieArrayArray,
        $buttonArray = array(),
        $returnActionLogInfo = FALSE
    ) {
        $initiativeArrayArray = array();
        $actionLogInfo = array();
        foreach ($activeDieArrayArray as $playerIdx => $tempActiveDieArray) {
            $initiativeArrayArray[] = array();
            $actionLogInfo[] = array(
                'initiativeDice' => array(),
                'slowButton' => FALSE,
            );
            foreach ($tempActiveDieArray as $tempDie) {
                $actionLogDieInfo = $tempDie->get_action_log_data();
                // update initiative arrays if die counts for initiative
                $tempInitiative = $tempDie->initiative_value();
                if ($tempInitiative >= 0) {
                    $initiativeArrayArray[$playerIdx][] = $tempInitiative;
                    $actionLogDieInfo['included'] = TRUE;
                } else {
                    $actionLogDieInfo['included'] = FALSE;
                }
                $actionLogInfo[$playerIdx]['initiativeDice'][] = $actionLogDieInfo;
            }

            if (!empty($buttonArray)) {
                // add an artificial PHP_INT_MAX - 1 to each array,
                // except if the button is slow
                if (BMGame::is_button_slow($buttonArray[$playerIdx])) {
                    $initiativeArrayArray[$playerIdx] = array();
                    $actionLogInfo[$playerIdx]['slowButton'] = TRUE;
                } else {
                    $initiativeArrayArray[$playerIdx][] = PHP_INT_MAX - 1;
                }
            }

            sort($initiativeArrayArray[$playerIdx]);
        }

        // determine player that has won initiative
        $nPlayers = count($activeDieArrayArray);
        $hasPlayerInitiative = BMGame::compute_initiative_winner_array(
            $nPlayers,
            $initiativeArrayArray
        );

        if ($returnActionLogInfo) {
            return array(
                'hasPlayerInitiative' => $hasPlayerInitiative,
                'actionLogInfo' => $actionLogInfo,
            );
        } else {
            return $hasPlayerInitiative;
        }
    }

    /** tabulate initiative winners based on a die value array
     *
     * This is a helper function which takes an array containing
     * only valid die values to be used in determining initiative,
     * and computes which player's dice include the lowest value,
     * breaking ties by next-lowest value.  If multiple players' relevant
     * dice are actually tied for lowest value, this function reports
     * that they are all entitled to initiative --- it does not break the tie.
     *
     * @return array  For each player, does that player have a minimal initiative value?
     */
    protected static function compute_initiative_winner_array($nPlayers, $initiativeArrayArray) {
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

    protected static function is_button_slow($button) {
        $hookResult = $button->run_hooks(
            __FUNCTION__,
            array('name' => $button->name)
        );

        $isSlow = isset($hookResult['BMBtnSkill'.$button->name]['is_button_slow']) &&
                  $hookResult['BMBtnSkill'.$button->name]['is_button_slow'];

        return $isSlow;
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

        $attackCache = $this->attack;

        // find out if there are any possible obligatory attacks with any
        // combination of the attacker's and defender's dice
        foreach ($attackTypeArray as $attackType) {
            if ($this->does_valid_attack_exist($attackerIdx, $defenderIdx, $attackType, FALSE)) {
                $validAttackTypeArray[$attackType] = $attackType;
            }
        }

        $hasObligatoryAttack = !empty($validAttackTypeArray);

        // Now find optional attack types

        // Currently, the only optional attacks are skill attacks
        // involving Warrior dice, so the code is streamlined to search
        // specifically for this. However, this could easily be generalised
        // if other optional attack types become available.
        $doWarriorDiceExist = FALSE;

        foreach ($this->activeDieArrayArray[$attackerIdx] as $activeDie) {
            if ($activeDie->has_skill('Warrior')) {
                $doWarriorDiceExist = TRUE;
                break;
            }
        }

        if ($doWarriorDiceExist) {
            if ($this->does_valid_attack_exist($attackerIdx, $defenderIdx, 'Skill', TRUE)) {
                $validAttackTypeArray['Skill'] = 'Skill';
            }
        }

        if (!$hasObligatoryAttack) {
            $validAttackTypeArray['Pass'] = 'Pass';
        }

        uksort($validAttackTypeArray, 'BMAttack::display_cmp');

        $this->attack = $attackCache;

        // james: deliberately ignore Default and Surrender attacks here,
        //        so that they do not appear in the list of attack types

        return $validAttackTypeArray;
    }

    protected function does_valid_attack_exist(
        $attackerIdx,
        $defenderIdx,
        $attackType,
        $includeOptional
    ) {
        $this->attack = array('attackerPlayerIdx' => $attackerIdx,
                              'defenderPlayerIdx' => $defenderIdx,
                              'attackerAttackDieIdxArray' =>
                                  range(0, count($this->activeDieArrayArray[$attackerIdx]) - 1),
                              'defenderAttackDieIdxArray' =>
                                  range(0, count($this->activeDieArrayArray[$defenderIdx]) - 1),
                              'attackType' => $attackType);
        $attack = BMAttack::create($attackType);
        foreach ($this->activeDieArrayArray[$attackerIdx] as $attackDie) {
            $attack->add_die($attackDie);
        }
        return $attack->find_attack($this, $includeOptional);
    }

    protected function activate_GUI($activation_type, $input_parameters = NULL) {
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
        $this->outOfPlayDieArrayArray = array_fill(0, $nPlayers, array());
        $this->waitingOnActionArray = array_fill(0, $nPlayers, FALSE);
        $this->swingRequestArrayArray = array_fill(0, $nPlayers, array());
        $this->optRequestArrayArray = array_fill(0, $nPlayers, array());
        unset($this->forceRoundResult);
    }

    protected function update_active_player() {
        if (!isset($this->activePlayerIdx)) {
            throw new LogicException(
                'Active player must be set before it can be updated.'
            );
        }

        $nPlayers = count($this->playerIdArray);
        // move to the next player
        if (isset($this->nextPlayerIdx)) {
            if ($this->nextPlayerIdx === $this->activePlayerIdx) {
                // james: currently, the only reason that this would be true is TimeAndSpace,
                //        so hard code it for the moment
                $this->log_action(
                    'play_another_turn',
                    $this->playerIdArray[$this->activePlayerIdx],
                    array('cause' => 'TimeAndSpace')
                );
            }

            $this->activePlayerIdx = $this->nextPlayerIdx;
            $this->nextPlayerIdx = NULL;
        } else {
            $this->activePlayerIdx = ($this->activePlayerIdx + 1) % $nPlayers;
        }

        // currently not waiting on anyone
        $this->waitingOnActionArray = array_fill(0, $nPlayers, FALSE);
    }

    // utility methods
    /**
     * Constructor
     *
     * @param integer $gameID
     * @param array $playerIdArray
     * @param array $buttonRecipeArray
     * @param integer $maxWins
     */
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

    /**
     * Round number of previous round
     *
     * After a round has ended, get the number of the round which just ended
     * This is simpler than the logic in get__roundNumber(), because
     * the behavior is the same in both the endgame and during-game cases
     *
     * @return int
     */
    protected function get_prevRoundNumber() {
        return array_sum($this->gameScoreArrayArray[0]);
    }

    /**
     * Array of relative side scores
     *
     * @return array
     */
    protected function get_sideScoreArray() {
        $roundScoreArray = $this->get__roundScoreArray();

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

    /**
     * Get log-relevant data about the dice involved in an attack
     *
     * @param array $attackerDice
     * @param array $defenderDice
     * @return array
     */
    protected function get_action_log_data($attackerDice, $defenderDice) {
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

    /**
     * Getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        $funcName = 'get__'.$property;
        if (method_exists($this, $funcName)) {
            return $this->$funcName();
        } elseif (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    /**
     * Attacker player index
     *
     * @return null|int
     */
    protected function get__attackerPlayerIdx() {
        if (!isset($this->attack)) {
            return NULL;
        }
        return $this->attack['attackerPlayerIdx'];
    }

    /**
     * Defender player index
     *
     * @return null|int
     */
    protected function get__defenderPlayerIdx() {
        if (!isset($this->attack)) {
            return NULL;
        }
        return $this->attack['defenderPlayerIdx'];
    }

    /**
     * Array of all active dice of the attacker
     *
     * @return null|array
     */
    protected function get__attackerAllDieArray() {
        if (!isset($this->attack) ||
            !isset($this->activeDieArrayArray)) {
            return NULL;
        }
        return $this->activeDieArrayArray[$this->attack['attackerPlayerIdx']];
    }

    /**
     * Array of all active dice of the defender
     *
     * @return null|array
     */
    protected function get__defenderAllDieArray() {
        if (!isset($this->attack) ||
            !isset($this->activeDieArrayArray)) {
            return NULL;
        }
        return $this->activeDieArrayArray[$this->attack['defenderPlayerIdx']];
    }

    /**
     * Array of attacking dice of the attacker
     *
     * @return null|array
     */
    protected function get__attackerAttackDieArray() {
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
    }

    /**
     * Array of target dice of the defender
     *
     * @return null|array
     */
    protected function get__defenderAttackDieArray() {
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
    }

    /**
     * Current round number
     *
     * @return int
     */
    protected function get__roundNumber() {
        $roundNumber = array_sum($this->gameScoreArrayArray[0]) + 1;

        if (max($this->gameScoreArrayArray[0]['W'], $this->gameScoreArrayArray[0]['L']) >=
            $this->maxWins) {
            $roundNumber--;
        }

        return $roundNumber;
    }

    /**
     * Current round score
     *
     * @return array
     */
    protected function get__roundScoreArray() {
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

    /**
     * Setter
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) {
        $funcName = 'set__'.$property;
        if (method_exists($this, $funcName)) {
            $this->$funcName($value);
        } else {
            $this->$property = $value;
        }
    }

    /**
     * Prevent setting the number of players in the game
     */
    protected function set__nPlayers() {
        throw new LogicException(
            'nPlayers is derived from BMGame->playerIdArray'
        );
    }

    /**
     * Allow setting the turn number in the round
     *
     * @param int $value
     */
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

    /**
     * Allow setting the game ID
     *
     * @param int $value
     */
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

    /**
     * Allow setting the player ID array
     *
     * @param array $value
     */
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

    /**
     * Allow setting the active player index
     *
     * @param int $value
     */
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

    /**
     * Allow setting the index of the player with initiative
     *
     * @param int $value
     */
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

    /**
     * Allow setting the button array
     *
     * @param array $value
     */
    protected function set__buttonArray($value) {
        $this->validateButtonArray($value);

        $this->buttonArray = $value;
        foreach ($this->buttonArray as $playerIdx => $button) {
            if ($button instanceof BMButton) {
                $button->playerIdx = $playerIdx;
                $button->ownerObject = $this;
            }
        }
    }

    protected function validateButtonArray($value) {
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
    }

    /**
     * Allow setting the array of arrays of active dice
     *
     * @param array $value
     */
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

    /**
     * Allow setting the attack
     *
     * @param array $value
     */
    protected function set__attack($value) {
        $value = array_values($value);
        $this->validateAttackFormat($value);

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

    protected function validateAttackFormat($value) {
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
    }

    /**
     * Prevent setting the attacker's attack dice
     */
    protected function set__attackerAttackDieArray() {
        throw new LogicException(
            'BMGame->attackerAttackDieArray is derived from BMGame->attack.'
        );
    }

    /**
     * Prevent setting the defender's target dice
     */
    protected function set__defenderAttackDieArray() {
        throw new LogicException(
            'BMGame->defenderAttackDieArray is derived from BMGame->attack.'
        );
    }

    /**
     * Allow setting the number of recent consecutive passes
     *
     * @param int $value
     */
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

    /**
     * Allow setting the array of arrays of captured dice
     *
     * @param array $value
     */
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

    /**
     * Prevent setting of the round number
     */
    protected function set__roundNumber() {
        throw new LogicException(
            'BMGame->roundNumber is derived automatically from BMGame.'
        );
    }

    /**
     * Prevent setting of the round score
     */
    protected function set__roundScoreArray() {
        throw new LogicException(
            'BMGame->roundScoreArray is derived automatically from BMGame.'
        );
    }

    /**
     * Allow setting the array of arrays of game scores
     *
     * @param array $value
     */
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

    /**
     * Allow setting the maximum number of wins
     *
     * @param int $value
     */
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

    /**
     * Allow setting the game state
     *
     * @param int $value
     */
    protected function set__gameState($value) {
        BMGameState::validate_game_state($value);
        $this->gameState = (int)$value;
    }

    /**
     * Allow setting the array of which players are being waited upon
     *
     * @param array $value
     */
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

    /**
     * Allow setting the array of whether autopass is allowed
     *
     * @param array $value
     */
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

    /**
     * Prevent setting the firing amount
     */
    protected function set__firingAmount() {
        throw new LogicException(
            'firingAmount is set exclusively via BMGame->turn_down_fire_dice().'
        );
    }

    /**
     * Allow the round result to be forced explicitly
     *
     * @param array $value
     */
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

    /**
     * Define behaviour of isset()
     *
     * @param string $property
     * @return boolean
     */
    public function __isset($property) {
        return isset($this->$property);
    }

    /**
     * Define behaviour of unset()
     *
     * @param string $property
     * @return boolean
     */
    public function __unset($property) {
        if (isset($this->$property)) {
            unset($this->$property);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    // Sets an individual entry in an array that's stored in a property on this
    // object, because "$game->$property[$key] = $value;" is illegal.
    //
    // shadowshade suggests
    // indicating awkward code
    // with verse from the East
    public function setArrayPropEntry($property, $key, $value) {
        $array = $this->$property;
        if (!is_array($array)) {
            throw new InvalidArgumentException("$property does not refer to an array");
        }
        $array[$key] = $value;
        $this->__set($property, $array);
    }

    public function getJsonData($requestingPlayerId) {
        $requestingPlayerIdx = array_search($requestingPlayerId, $this->playerIdArray);

        $dataArray = array(
            'gameId'                     => $this->gameId,
            'gameState'                  => BMGameState::as_string($this->gameState),
            'activePlayerIdx'            => $this->activePlayerIdx,
            'playerWithInitiativeIdx'    => $this->playerWithInitiativeIdx,
            'roundNumber'                => $this->get__roundNumber(),
            'maxWins'                    => $this->maxWins,
            'description'                => $this->description,
            'previousGameId'             => $this->previousGameId,
            'validAttackTypeArray'       => $this->get_validAttackTypeArray(),
            'gameSkillsInfo'             => $this->get_gameSkillsInfo(),
            'playerDataArray'            => $this->get_playerDataArray($requestingPlayerIdx),
        );
        return $dataArray;
    }

    /**
     * Array of player data
     *
     * @param int $requestingPlayerIdx
     * @return array
     */
    protected function get_playerDataArray($requestingPlayerIdx) {
        $playerDataArray = array();

        // helper arrays of data that BMGame naturally produces in array form
        $roundScoreArray = $this->get__roundScoreArray();
        $sideScoreArray = $this->get_sideScoreArray();
        $canStillWinArray = $this->get_canStillWinArray();

        foreach ($this->playerIdArray as $playerIdx => $playerId) {
            $playerData = array(
                'playerId'            => $playerId,
                'button'              => $this->get_buttonInfo($playerIdx),
                'activeDieArray'      => $this->get_activeDieArray($playerIdx, $requestingPlayerIdx),
                'capturedDieArray'    => $this->get_capturedDieArray($playerIdx),
                'outOfPlayDieArray'   => $this->get_outOfPlayDieArray($playerIdx),
                'swingRequestArray'   => $this->get_swingRequestArray($playerIdx),
                'optRequestArray'     => $this->get_optRequestArray($playerIdx),
                'prevSwingValueArray' => $this->get_prevSwingValueArray($playerIdx),
                'prevOptValueArray'   => $this->get_prevOptValueArray($playerIdx),
                'waitingOnAction'     => $this->get_waitingOnActionArray($playerIdx, $requestingPlayerIdx),
                'roundScore'          => $roundScoreArray[$playerIdx],
                'sideScore'           => $sideScoreArray[$playerIdx],
                'gameScoreArray'      => $this->gameScoreArrayArray[$playerIdx],
                'lastActionTime'      => $this->lastActionTimeArray[$playerIdx],
                'hasDismissedGame'    => $this->hasPlayerDismissedGameArray[$playerIdx],
                'canStillWin'         => $canStillWinArray[$playerIdx],
            );

            $playerDataArray[] = $playerData;
        }
        return $playerDataArray;
    }

    protected function clone_activeDieArrayArray() {
        // create a deep clone of the original activeDieArrayArray so that changes
        // don't propagate back into the real game data
        $activeDieArrayArray = array_fill(0, $this->nPlayers, array());

        foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
            if (count($activeDieArray) > 0) {
                foreach ($activeDieArray as $dieIdx => $activeDie) {
                    $activeDieArrayArray[$playerIdx][$dieIdx] = clone $activeDie;
                }
            }
        }

        return $activeDieArrayArray;
    }

    /**
     * Array of button info
     *
     * @param int $playerIdx
     * @return array
     */
    protected function get_buttonInfo($playerIdx) {
        $buttonInfo = array(
            'name' => '',
            'recipe' => '',
            'artFilename' => '',
        );
        if (count($this->buttonArray) > $playerIdx) {
            $button = $this->buttonArray[$playerIdx];
            if ($button instanceof BMButton) {
                $buttonInfo['name'] = $button->name;
                $buttonInfo['recipe'] = $button->recipe;
                $buttonInfo['artFilename'] = $button->artFilename;
            }
        }
        return $buttonInfo;
    }

    /**
     * Array of information about active dice
     *
     * @param int $playerIdx
     * @param int $requestingPlayerIdx
     * @return array
     */
    protected function get_activeDieArray($playerIdx, $requestingPlayerIdx) {
        // this should be refactored to duplicate less effort, but
        // right now e.g. nulling of die values is done across all
        // dice at once, so it will take some refactoring to actually
        // request dice one at a time
        $valueArrayArray = $this->get_valueArrayArray($requestingPlayerIdx);
        $sidesArrayArray = $this->get_sidesArrayArray($requestingPlayerIdx);
        $subdieArrayArray = $this->get_subdieArrayArray($requestingPlayerIdx);
        $dieSkillsArrayArray = $this->get_dieSkillsArrayArray();
        $diePropertiesArrayArray = $this->get_diePropsArrayArray($requestingPlayerIdx);
        $dieRecipeArrayArray = $this->get_dieRecipeArrayArray();
        $dieDescriptionArrayArray = $this->get_dieDescriptionArrayArray($requestingPlayerIdx);

        $activeDieArray = array();
        for ($dieIdx = 0; $dieIdx <= (count($valueArrayArray[$playerIdx]) - 1); $dieIdx++) {
            $activeDieDescription = array(
                'value'       => $valueArrayArray[$playerIdx][$dieIdx],
                'sides'       => $sidesArrayArray[$playerIdx][$dieIdx],
                'skills'      => $dieSkillsArrayArray[$playerIdx][$dieIdx],
                'properties'  => $diePropertiesArrayArray[$playerIdx][$dieIdx],
                'recipe'      => $dieRecipeArrayArray[$playerIdx][$dieIdx],
                'description' => $dieDescriptionArrayArray[$playerIdx][$dieIdx],
            );

            if (isset($subdieArrayArray) &&
                isset($subdieArrayArray[$playerIdx][$dieIdx])) {
                $activeDieDescription['subdieArray'] =
                    $subdieArrayArray[$playerIdx][$dieIdx];
            }

            $activeDieArray[] = $activeDieDescription;
        }
        return $activeDieArray;
    }

    /**
     * Array of info about captured dice
     *
     * @param int $playerIdx
     * @return array
     */
    protected function get_capturedDieArray($playerIdx) {
        $capturedDieArray = array();
        if (isset($this->capturedDieArrayArray)) {
            foreach ($this->capturedDieArrayArray[$playerIdx] as $die) {
                $capturedDieArray[] = array(
                    'value' => $die->value,
                    'sides' => $die->max,
                    'recipe' => $die->recipe,
                    'properties' => $this->get_dieProps($die),
                );
            }
        }
        return $capturedDieArray;
    }

    /**
     * Array of info about out of play dice
     *
     * @param int $playerIdx
     * @return array
     */
    protected function get_outOfPlayDieArray($playerIdx) {
        $outOfPlayDieArray = array();
        if (isset($this->outOfPlayDieArrayArray)) {
            foreach ($this->outOfPlayDieArrayArray[$playerIdx] as $die) {
                $outOfPlayDieArray[] = array(
                    'value' => $die->value,
                    'sides' => $die->max,
                    'recipe' => $die->recipe,
                    'properties' => $this->get_dieProps($die),
                );
            }
        }
        return $outOfPlayDieArray;
    }

    /**
     * Array of arrays of die values
     *
     * @param int $requestingPlayerIdx
     * @return array
     */
    protected function get_valueArrayArray($requestingPlayerIdx) {
        $valueArrayArray = array_fill(0, $this->nPlayers, array());
        $swingValsSpecified = TRUE;

        if (isset($this->activeDieArrayArray)) {
            $activeDieArrayArray = $this->clone_activeDieArrayArray();

            foreach ($activeDieArrayArray as $playerIdx => $activeDieArray) {
                foreach ($activeDieArray as $dieIdx => $die) {
                    // hide swing information if appropriate
                    if (is_null($die->max)) {
                        $swingValsSpecified = FALSE;
                    }

                    if ($this->shouldDieDataBeHidden($playerIdx, $requestingPlayerIdx)) {
                        $die->value = NULL;
                    }
                    $valueArrayArray[$playerIdx][$dieIdx] = $die->value;
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

        return $valueArrayArray;
    }

    /**
     * Array of arrays of die sides
     *
     * @param int $requestingPlayerIdx
     * @return array
     */
    protected function get_sidesArrayArray($requestingPlayerIdx) {
        $sidesArrayArray = array_fill(0, $this->nPlayers, array());

        if (isset($this->activeDieArrayArray)) {
            $activeDieArrayArray = $this->clone_activeDieArrayArray();

            foreach ($activeDieArrayArray as $playerIdx => $activeDieArray) {
                foreach ($activeDieArray as $dieIdx => $die) {
                    if ($this->shouldDieDataBeHidden($playerIdx, $requestingPlayerIdx)) {
                        if ($die instanceof BMDieSwing) {
                            $die->max = NULL;
                        }

                        if ($die instanceof BMDieTwin) {
                            foreach ($die->dice as $subdie) {
                                if ($subdie instanceof BMDieSwing) {
                                    $subdie->max = NULL;
                                    $die->max = NULL;
                                }
                            }
                        }

                        if ($die instanceof BMDieOption) {
                            $die->max = NULL;
                        }
                    }
                    $sidesArrayArray[$playerIdx][$dieIdx] = $die->max;
                }
            }
        }

        return $sidesArrayArray;
    }

    /**
     * Array of arrays of possible arrays of subdie properties
     *
     * @param int $requestingPlayerIdx
     * @return array
     */
    protected function get_subdieArrayArray($requestingPlayerIdx) {
        if (!isset($this->activeDieArrayArray)) {
            return NULL;
        }

        $areAllPropertiesNull = TRUE;
        $subdieArrayArray = array_fill(0, $this->nPlayers, array());
        $activeDieArrayArray = $this->clone_activeDieArrayArray();

        foreach ($activeDieArrayArray as $playerIdx => $activeDieArray) {
            foreach ($activeDieArray as $dieIdx => $die) {
                if (isset($die->dice) && is_array($die->dice)) {

                    foreach ($die->dice as $subdieIdx => $subdie) {
                        if (($subdie instanceof BMDieSwing) &&
                            $this->shouldDieDataBeHidden($playerIdx, $requestingPlayerIdx)) {
                            continue;
                        }

                        $areAllPropertiesNull &= $this->assignSubdice(
                            $subdieArrayArray,
                            $playerIdx,
                            $dieIdx,
                            $subdieIdx,
                            $subdie
                        );
                    }
                }

            }
        }

        if ($areAllPropertiesNull) {
            return NULL;
        }

        return $subdieArrayArray;
    }

    protected function isGameStateBeforeSpecifyingDice() {
        return $this->gameState <= BMGameState::SPECIFY_DICE;
    }

    protected function shouldDieDataBeHidden($playerIdx, $requestingPlayerIdx) {
        return ($this->wereSwingOrOptionValuesReset() &&
                $this->isGameStateBeforeSpecifyingDice() &&
                ($playerIdx !== $requestingPlayerIdx));
    }

    protected function assignSubdice(
        &$subdieArrayArray,
        $playerIdx,
        $dieIdx,
        $subdieIdx,
        $subdie
    ) {
        $areAllPropertiesNull = TRUE;

        if (isset($subdie->max)) {
            $subdieArrayArray[$playerIdx][$dieIdx][$subdieIdx]['sides'] = $subdie->max;
            $areAllPropertiesNull = FALSE;
        }

        if (isset($subdie->value) &&
            !$this->isGameStateBeforeSpecifyingDice()) {
            $subdieArrayArray[$playerIdx][$dieIdx][$subdieIdx]['value'] = $subdie->value;
            $areAllPropertiesNull = FALSE;
        }

        return $areAllPropertiesNull;
    }

    /**
     * Array of arrays of die skills
     *
     * @return array
     */
    protected function get_dieSkillsArrayArray() {
        $dieSkillsArrayArray = array();

        if (isset($this->activeDieArrayArray)) {
            foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
                if (count($activeDieArray) > 0) {
                    $dieSkillsArrayArray[$playerIdx] =
                        array_fill(0, count($activeDieArray), array());
                }

                foreach ($activeDieArray as $dieIdx => $die) {
                    if (count($die->skillList) > 0) {
                        foreach (array_keys($die->skillList) as $skillType) {
                            $dieSkillsArrayArray[$playerIdx][$dieIdx][] = $skillType;
                        }
                    }
                }
            }
        }

        return $dieSkillsArrayArray;
    }

    /**
     * Array of arrays of die properties
     *
     * @param int $requestingPlayerIdx
     * @return array
     */
    protected function get_diePropsArrayArray($requestingPlayerIdx) {
        $diePropsArrayArray = array();

        if (isset($this->activeDieArrayArray)) {
            foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
                if (count($activeDieArray) > 0) {
                    $diePropsArrayArray[$playerIdx] =
                        array_fill(0, count($activeDieArray), array());
                }

                foreach ($activeDieArray as $dieIdx => $die) {
                    if (!empty($die->flagList)) {
                        foreach (array_keys($die->flagList) as $flag) {
                            // actively lie about auxiliary choices to avoid leaking info
                            if (('AddAuxiliary' == $flag) &&
                                ($requestingPlayerIdx !== $playerIdx)) {
                                continue;
                            }
                            $diePropsArrayArray[$playerIdx][$dieIdx][] = $flag;
                        }
                    }
                }
            }
        }

        return $diePropsArrayArray;
    }

    /**
     * Array of arrays of die recipes
     *
     * @return array
     */
    protected function get_dieRecipeArrayArray() {
        $dieRecipeArrayArray = array_fill(0, $this->nPlayers, array());

        if (isset($this->activeDieArrayArray)) {
            foreach ($this->activeDieArrayArray as $playerIdx => $activeDieArray) {
                foreach ($activeDieArray as $dieIdx => $die) {
                    $dieRecipeArrayArray[$playerIdx][$dieIdx] = $die->recipe;
                }
            }
        }

        return $dieRecipeArrayArray;
    }

    /**
     * Array of arrays of die descriptions
     *
     * @param int $requestingPlayerIdx
     * @return array
     */
    protected function get_dieDescriptionArrayArray($requestingPlayerIdx) {
        $dieDescArrayArray = array();

        if (isset($this->activeDieArrayArray)) {
            $activeDieArrayArray = $this->clone_activeDieArrayArray();

            foreach ($activeDieArrayArray as $playerIdx => $activeDieArray) {
                foreach ($activeDieArray as $dieIdx => $die) {
                    if ($this->wereSwingOrOptionValuesReset() &&
                        ($this->gameState <= BMGameState::SPECIFY_DICE) &&
                        ($playerIdx !== $requestingPlayerIdx)) {

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
                    $dieDescArrayArray[$playerIdx][$dieIdx] = $die->describe(FALSE);
                }
            }
        }

        return $dieDescArrayArray;
    }

    /**
     * Array of die properties
     *
     * @param BMDie $die
     * @return array
     */
    protected function get_dieProps($die) {
        $dieProps = array();
        if (!empty($die->flagList)) {
            foreach (array_keys($die->flagList) as $flag) {
                $dieProps[] = $flag;
            }
        }
        return $dieProps;
    }

    /**
     * Array of swing requests
     *
     * @param int $playerIdx
     * @return array
     */
    protected function get_swingRequestArray($playerIdx) {
        $swingRequestArray = array();

        if (($this->gameState == BMGameState::CHOOSE_AUXILIARY_DICE) &&
            $this->waitingOnActionArray[$playerIdx]) {
            $swingRequestArrayArray = $this->get_all_swing_requests(TRUE);
        } elseif ($this->gameState <= BMGameState::CHOOSE_RESERVE_DICE) {
            $swingRequestArrayArray = $this->get_all_swing_requests(FALSE);
        } else {
            $swingRequestArrayArray = $this->swingRequestArrayArray;
        }

        if (isset($this->activeDieArrayArray) &&
            isset($swingRequestArrayArray[$playerIdx])) {
            foreach ($swingRequestArrayArray[$playerIdx] as $swingtype => $swingdice) {
                if ($swingdice[0] instanceof BMDieTwin) {
                    if ($swingdice[0]->dice[0] instanceof BMDieSwing) {
                        $swingdie = $swingdice[0]->dice[0];
                    } elseif ($swingdice[0]->dice[1] instanceof BMDieSwing) {
                        $swingdie = $swingdice[0]->dice[1];
                    } else {
                        throw new LogicException(
                            'At least one of the subdice of a twin swing die should be a swing die'
                        );
                    }
                } else {
                    $swingdie = $swingdice[0];
                }
                if ($swingdie instanceof BMDieSwing) {
                    $validRange = $swingdie->swing_range($swingtype);
                } else {
                    throw new LogicException(
                        'Tried to put die in swingRequestArray which is not a swing die: ' . $swingdie
                    );
                }
                $swingRequestArray[$swingtype] = array($validRange[0], $validRange[1]);
            }
        }

        return $swingRequestArray;
    }

    protected function get_all_swing_requests($includeCourtesyDice = FALSE) {
        $swingRequestArrayArray = array_fill(0, $this->nPlayers, array());

        if (!isset($this->buttonArray)) {
            return $swingRequestArrayArray;
        }

        $courtesySwingArray = array();

        foreach ($this->buttonArray as $playerIdx => $button) {
            if (isset($button->dieArray)) {
                foreach ($button->dieArray as $die) {
                    if (isset($die->swingType)) {
                        $swingRequestArrayArray[$playerIdx][$die->swingType][] = $die;

                        if ($includeCourtesyDice &&
                            $die->has_skill('Auxiliary')) {
                            $courtesySwingArray[$die->swingType][] = $die;
                        }
                    }
                }
            }
        }

        if ($includeCourtesyDice) {
            foreach ($swingRequestArrayArray as &$swingRequestArray) {
                foreach ($courtesySwingArray as $courtesySwingType => $swingDie) {
                    if (!array_key_exists($courtesySwingType, $swingRequestArray)) {
                        $swingRequestArray[$courtesySwingType] = $swingDie;
                    }
                }
            }
        }

        return $swingRequestArrayArray;
    }

    /**
     * Array of option requests
     *
     * @param int $playerIdx
     * @return array
     */
    protected function get_optRequestArray($playerIdx) {
        if (is_null($this->optRequestArrayArray)) {
            $optRequestArray = array();
        } else {
            $optRequestArray = $this->optRequestArrayArray[$playerIdx];
        }

        return $optRequestArray;
    }

    /**
     * Array of previous choice of swing values
     *
     * @param int $playerIdx
     * @return array
     */
    protected function get_prevSwingValueArray($playerIdx) {
        if (empty($this->prevSwingValueArrayArray)) {
            $prevSwingValueArray = array();
        } else {
            $prevSwingValueArray = $this->prevSwingValueArrayArray[$playerIdx];
        }

        return $prevSwingValueArray;
    }

    /**
     * Array of previous choice of option values
     *
     * @param type $playerIdx
     * @return type
     */
    protected function get_prevOptValueArray($playerIdx) {
        if (empty($this->prevOptValueArrayArray)) {
            $prevOptValueArray = array();
        } else {
            $prevOptValueArray = $this->prevOptValueArrayArray[$playerIdx];
        }

        return $prevOptValueArray;
    }

    protected function get_waitingOnActionArray($playerIdx, $requestingPlayerIdx) {
        // actively lie about whether a player has chosen auxiliary dice
        // to avoid leaking information
        if ((BMGameState::CHOOSE_AUXILIARY_DICE == $this->gameState) &&
            $requestingPlayerIdx !== $playerIdx) {
            return TRUE;
        }

        return $this->waitingOnActionArray[$playerIdx];
    }

    /**
     * Array of valid attack types
     *
     * @return array
     */
    protected function get_validAttackTypeArray() {
        // If it's someone's turn to attack, report the valid attack
        // types as part of the game data
        if ($this->gameState == BMGameState::START_TURN) {
            $validAttackTypeArray = array_keys($this->valid_attack_types());
        } else {
            $validAttackTypeArray = array();
        }

        return $validAttackTypeArray;
    }

    /**
     * Were the swing or option values reset?
     *
     * @return boolean
     */
    protected function wereSwingOrOptionValuesReset() {
        // james: need to also consider the case of many multiple draws in a row
        foreach ($this->gameScoreArrayArray as $gameScoreArray) {
            if ($gameScoreArray['W'] > 0 || $gameScoreArray['D'] > 0) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Return an array of all skills appearing in die recipes in this game
     *
     * This returns all skills appearing on any die which is in a
     * button recipe in this game, whether or not that die is currently
     * in play.
     *
     * @return array   Array of skill information, indexed by skill name
     */
    protected function get_gameSkillsInfo() {
        $gameSkillsWithKeysList = array();

        if (isset($this->buttonArray)) {
            foreach ($this->buttonArray as $playerButton) {
                if (!is_null($playerButton) && count($playerButton->dieArray) > 0) {
                    foreach ($playerButton->dieArray as $buttonDie) {
                        if (count($buttonDie->skillList) > 0) {
                            $gameSkillsWithKeysList += $buttonDie->skillList;
                        }
                    }
                }
            }
        }

        $gameSkillsList = array_keys($gameSkillsWithKeysList);
        sort($gameSkillsList);

        $gameSkillsInfo = array();
        foreach ($gameSkillsList as $skillType) {
            $gameSkillsInfo[$skillType] = BMSkill::describe($skillType, $gameSkillsList);
        }
        return $gameSkillsInfo;
    }

    /**
     * Array of whether each player can still win
     *
     * @return array
     */
    protected function get_canStillWinArray() {
        $canStillWinArray = array_fill(0, $this->nPlayers, NULL);

        if ($this->has_skill_that_prevents_win_determination() ||
            ($this->gameState <= BMGameState::SPECIFY_DICE)) {
            return $canStillWinArray;
        }

        $sideScoreArray = $this->get_sideScoreArray();
        $sidesArray = $this->get_sidesArrayArray(0);

        for ($playerIdx = 0; $playerIdx < $this->nPlayers; $playerIdx++) {
            $opponentIdx = ($playerIdx + 1) % 2;
            $canStillWinArray[$playerIdx] =
                ($sideScoreArray[$playerIdx] + array_sum($sidesArray[$opponentIdx])) >= 0;
        }

        return $canStillWinArray;
    }

    protected function has_skill_that_prevents_win_determination() {
        if (empty($this->activeDieArrayArray)) {
            return FALSE;
        }

        foreach ($this->activeDieArrayArray as $activeDieArray) {
            if (empty($activeDieArray)) {
                continue;
            }

            foreach ($activeDieArray as $activeDie) {
                if (empty($activeDie->skillList)) {
                    continue;
                }

                foreach ($activeDie->skillList as $skill) {
                    if ($skill::prevents_win_determination()) {
                        return TRUE;
                    }
                }
            }
        }

        return FALSE;
    }
}
