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
 * @property      array $playerArray             Array of BMPlayer objects
 * @property-read int   $nPlayers                Number of players in the game
 * @property-read int   $roundNumber;            Current round number
 * @property      int   $turnNumberInRound;      Current turn number in current round
 * @property      int   $activePlayerIdx         Index of the active player in playerIdxArray
 * @property      int   $nextPlayerIdx           Index of the next player to take a turn in playerIdxArray
 * @property      int   $playerWithInitiativeIdx Index of the player who won initiative
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
 * @property-read int   $nRecentPasses           Number of consecutive passes
 * @property      int   $maxWins                 The game ends when a player has this many wins
 * @property-read BMGameState $gameState         Current game state as a BMGameState enum
 * @property-read int   $firingAmount            Amount of firing that has been set by the attacker
 * @property      array $actionLog               Game actions taken by this BMGame instance
 * @property      array $chat                    A chat message submitted by the active player
 * @property      string $description;           Description provided when the game was created
 * @property      int   $previousGameId;         The game whose chat is being continued with this game
 * @property-read string $message                Message to be passed to the GUI
 * @property      int   $logEntryLimit           Number of log entries to display
 *
 * Convenience accessors for BMPlayer properties:
 * @property      array $playerIdArray           Array of player IDs
 * @property      array $buttonArray             Buttons for all players
 * @property      array $activeDieArrayArray     Active dice for all players
 * @property      array $capturedDieArrayArray   Captured dice for all players
 * @property      array $outOfPlayDieArrayArray  Out-of-play dice for all players
 * @property      array $waitingOnActionArray    Boolean array whether each player needs to perform an action
 * @property      array $isPrevRoundWinnerArray  Boolean array whether each player won the previous round
 * @property-read array $roundScoreArray         Current points score in this round
 * @property      array $gameScoreArrayArray     Number of games W/L/D for all players
 * @property      array $swingRequestArrayArray  Swing requests for all players
 * @property      array $swingValueArrayArray    Swing values for all players
 * @property      array $prevSwingValueArrayArray Swing values for previous round for all players
 * @property      array $optRequestArrayArray    Option requests for all players
 * @property      array $optValueArrayArray      Option values for current round for all players
 * @property      array $prevOptValueArrayArray  Option values for previous round for all players
 * @property      array $autopassArray           Boolean array whether each player has enabled autopass
 * @property      array $fireOvershootingArray   Boolean array whether each player has enabled fire overshooting
 * @property      array $hasPlayerAcceptedGameArray   Whether each player has accepted this game
 * @property      array $hasPlayerDismissedGameArray  Whether each player has dismissed this game
 * @property      array $isButtonChoiceRandomArray    Whether each button was chosen randomly
 * @property      array $lastActionTimeArray     Times of last actions for each player
 *
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 * @SuppressWarnings(PMD.TooManyFields)
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
     * Array of BMPlayer objects
     *
     * @var array
     */
    protected $playerArray;

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
     * Number of consecutive passes
     *
     * @var int
     */
    protected $nRecentPasses;

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
     * Used by BMInterface to store how many log entries to display
     *
     * @var int
     */
    public $logEntryLimit;

    /**
     * Internal cache of fire info, used for logging
     *
     * @var array
     */
    protected $fireCache;

    // methods
    /**
     * This is a generic caller function that calls each
     * do_next_step_*() function, based on the current game state.
     */
    public function do_next_step() {
        if (!isset($this->gameState)) {
            throw new LogicException('Game state must be set.');
        }

        $funcName = 'do_next_step_'.
                    strtolower(BMGameState::as_string($this->gameState));
        $this->$funcName();
    }

    /**
     * This is a generic caller function that calls each
     * update_game_state_*() function, based on the current game state.
     */
    public function update_game_state() {
        if (!isset($this->gameState)) {
            throw new LogicException('Game state must be set.');
        }

        $funcName = 'update_game_state_'.
                    strtolower(BMGameState::as_string($this->gameState));
        $this->$funcName();
    }

    /**
     * Perform the logic required at BMGameState::start_game
     */
    protected function do_next_step_start_game() {
    }

    /**
     * Update game state from BMGameState::start_game if necessary
     */
    protected function update_game_state_start_game() {
        $this->reset_play_state();
        $allPlayersSet = TRUE;

        // if player is unspecified, wait for player to accept game
        foreach ($this->playerArray as $player) {
            if (!isset($player->playerId)) {
                $player->waitingOnAction = TRUE;
                $allPlayersSet = FALSE;
            }
        }

        if (!$allPlayersSet) {
            return;
        }

        $allButtonsSet = TRUE;

        // if button is unspecified, allow player to choose buttons
        foreach ($this->playerArray as $player) {
            if (!isset($player->button)) {
                $player->waitingOnAction = TRUE;
                $allButtonsSet = FALSE;
            }
        }

        if (!$allButtonsSet) {
            return;
        }

        $this->gameState = BMGameState::APPLY_HANDICAPS;
        $this->nRecentPasses = 0;

        foreach ($this->playerArray as $player) {
            $player->gameScoreArray = array('W' => 0, 'L' => 0, 'D' => 0);
        }
    }

    /**
     * Perform the logic required at BMGameState::apply_handicaps
     */
    protected function do_next_step_apply_handicaps() {
        // ignore for the moment
        foreach ($this->playerArray as $player) {
            $player->gameScoreArray = array('W' => 0, 'L' => 0, 'D' => 0);
        }
    }

    /**
     * Update game state from BMGameState::apply_handicaps if necessary
     */
    protected function update_game_state_apply_handicaps() {
        if (!isset($this->maxWins)) {
            throw new LogicException(
                'maxWins must be set before applying handicaps.'
            );
        };

        $nWins = 0;

        foreach ($this->playerArray as $player) {
            $nWins = max($nWins, $player->gameScoreArray['W']);
        }

        if ($nWins >= $this->maxWins) {
            $this->gameState = BMGameState::END_GAME;
        } else {
            $this->gameState = BMGameState::CHOOSE_JOIN_GAME;
        }
    }

    /**
     * Perform the logic required at BMGameState::choose_join_game
     */
    protected function do_next_step_choose_join_game() {

    }

    /**
     * Update game state from BMGameState::choose_join_game if necessary
     */
    protected function update_game_state_choose_join_game() {
        $allPlayersHaveAccepted = TRUE;

        foreach ($this->playerArray as $player) {
            $player->waitingOnAction = !$player->hasPlayerAcceptedGame;
            $allPlayersHaveAccepted &= $player->hasPlayerAcceptedGame;
        }

        if ($allPlayersHaveAccepted) {
            $this->gameState = BMGameState::SPECIFY_RECIPES;
        }
    }

    /**
     * Perform the logic required at BMGameState::specify_recipes
     */
    protected function do_next_step_specify_recipes() {
        if (isset($this->playerArray)) {
            foreach ($this->playerArray as $playerIdx => $player) {
                $button = $player->button;
                if ($button instanceof BMButton) {
                    $oppPlayerIdx = ($playerIdx + 1) % 2;
                    $button->run_hooks(
                        'specify_recipes',
                        array('button' => $button,
                              'oppbutton' => $this->playerArray[$oppPlayerIdx]->button)
                    );
                }
            }
        }
    }

    /**
     * Update game state from BMGameState::specify_recipes if necessary
     */
    protected function update_game_state_specify_recipes() {
        foreach ($this->playerArray as $player) {
            if (empty($player->button->recipe)) {
                return;
            }
        }

        // cache recipes
        foreach ($this->playerArray as $player) {
            $player->button->originalRecipe = $player->button->recipe;
        }

        $this->gameState = BMGameState::LOAD_DICE_INTO_BUTTONS;
    }

    /**
     * Perform the logic required at BMGameState::load_dice_into_buttons
     */
    protected function do_next_step_load_dice_into_buttons() {
        // james: this is currently carried out either by manually setting
        // $this->playerArray[...]->button, or by BMInterface
    }

    /**
     * Update game state from BMGameState::load_dice_into_buttons if necessary
     */
    protected function update_game_state_load_dice_into_buttons() {
        foreach ($this->playerArray as $player) {
            if (empty($player->button)) {
                throw new LogicException(
                    'Buttons must be set before loading dice into buttons.'
                );
            }

            if (empty($player->button->dieArray)) {
                return;
            }
        }

        $this->gameState = BMGameState::ADD_AVAILABLE_DICE_TO_GAME;
    }

    /**
     * Perform the logic required at BMGameState::add_available_dice_to_game
     */
    protected function do_next_step_add_available_dice_to_game() {
        foreach ($this->playerArray as $player) {
            $player->button->activate();
        }

        $this->offer_courtesy_auxiliary_dice();
        $this->load_swing_values_from_previous_round();
        $this->load_option_values_from_previous_round();
    }

    /**
     * Add courtesy auxiliary dice if a player has no auxiliary dice, but the
     * opponent does
     */
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
                        $this->playerArray[$playerIdx]->activeDieArray[] = $newdie;
                    }
                }
            }
        }
    }

    /**
     * Check whether each player has dice with a certain skill
     *
     * @param string $skill
     * @return array
     */
    protected function do_players_have_dice_with_skill($skill) {
        $hasDiceWithSkill = array_fill(0, $this->nPlayers, FALSE);

        foreach ($this->playerArray as $playerIdx => $player) {
            if (!empty($player->activeDieArray)) {
                foreach ($player->activeDieArray as $die) {
                    if ($die->has_skill($skill)) {
                        $hasDiceWithSkill[$playerIdx] = TRUE;
                        break;
                    }
                }
            }
        }

        return $hasDiceWithSkill;
    }

    /**
     * Retrieve all auxiliary dice from all players
     *
     * @return array
     */
    protected function get_all_auxiliary_dice() {
        $auxiliaryDice = array();

        foreach ($this->playerArray as $player) {
            foreach ($player->activeDieArray as $die) {
                if ($die->has_skill('Auxiliary')) {
                    $auxiliaryDice[] = $die;
                }
            }
        }

        return $auxiliaryDice;
    }

    /**
     * Set swing values for swing dice if there are swing values
     * carried across from the previous round
     */
    protected function load_swing_values_from_previous_round() {
        foreach ($this->playerArray as $player) {
            if (empty($player->swingValueArray)) {
                continue;
            }

            foreach ($player->activeDieArray as &$activeDie) {
                if ($activeDie instanceof BMDieSwing) {
                    if (array_key_exists(
                        $activeDie->swingType,
                        $player->swingValueArray
                    )) {
                        $activeDie->swingValue =
                            $player->swingValueArray[$activeDie->swingType];
                    }
                }
            }
        }
    }

    /**
     * Set option values for option dice if there are option values
     * carried across from the previous round
     */
    protected function load_option_values_from_previous_round() {
        foreach ($this->playerArray as $player) {
            if (empty($player->optValueArray)) {
                continue;
            }

            $dieIndicesWithoutReserve = $player->die_indices_without_reserve();

            foreach ($player->optValueArray as $dieIdx => $optionValue) {
                $die = $player->activeDieArray[$dieIndicesWithoutReserve[$dieIdx]];
                if (!($die instanceof BMDieOption)) {
                    throw new LogicException('Die must be an option die.');
                }

                $die->set_optionValue($optionValue);
            }
        }
    }

    /**
     * Update game state from BMGameState::add_available_dice_to_game if necessary
     */
    protected function update_game_state_add_available_dice_to_game() {
        $this->gameState = BMGameState::CHOOSE_AUXILIARY_DICE;
        $waitingOnActionArray =
            $this->do_players_have_dice_with_skill('Auxiliary');
        foreach ($this->playerArray as $playerIdx => $player) {
            $player->waitingOnAction = $waitingOnActionArray[$playerIdx];
        }
    }

    /**
     * Perform the logic required at BMGameState::choose_auxiliary_dice
     */
    protected function do_next_step_choose_auxiliary_dice() {

    }

    /**
     * Update game state from BMGameState::choose_auxiliary_dice if necessary
     */
    protected function update_game_state_choose_auxiliary_dice() {
        // if all decisions on auxiliary dice have been made
        if (!$this->isWaitingOnAnyAction()) {
            $areAnyDiceAdded = $this->add_selected_auxiliary_dice();
            $areAnyDiceRemoved = $this->remove_dice_with_skill('Auxiliary');

            if (array_sum($areAnyDiceAdded) + array_sum($areAnyDiceRemoved) > 0) {
                // update button recipes
                foreach ($this->playerArray as $playerIdx => $player) {
                    if ($areAnyDiceAdded[$playerIdx] ||
                        $areAnyDiceRemoved[$playerIdx]) {
                        $player->button->update_button_recipe();
                    }
                }
            }

            $this->gameState = BMGameState::CHOOSE_RESERVE_DICE;
        }
    }

    /**
     * Determine whether each player has decided to add an auxiliary die,
     * and if all players have decided to do so, then convert these selected
     * auxiliary dice to normal dice
     *
     * @return array
     */
    protected function add_selected_auxiliary_dice() {
        $hasChosenAuxDie = array_fill(0, $this->nPlayers, FALSE);

        foreach ($this->playerArray as $playerIdx => $player) {
            foreach ($player->activeDieArray as $die) {
                if ($die->has_flag('AddAuxiliary')) {
                    $hasChosenAuxDie[$playerIdx] = TRUE;
                    break;
                }
            }
        }

        $useAuxDice = (1 == array_product($hasChosenAuxDie));

        if ($useAuxDice) {
            foreach ($this->playerArray as $playerIdx => $player) {
                foreach ($player->activeDieArray as $die) {
                    if ($die->has_flag('AddAuxiliary')) {
                        $die->remove_skill('Auxiliary');
                        $die->remove_flag('AddAuxiliary');
                    }
                }
            }
        }

        return array_fill(0, $this->nPlayers, $useAuxDice);
    }

    /**
     * Remove all dice with a certain skill from all players
     *
     * @param string $skill
     * @return bool
     */
    protected function remove_dice_with_skill($skill) {
        $areAnyDiceRemoved = array_fill(0, $this->nPlayers, FALSE);

        // remove all remaining auxiliary dice
        foreach ($this->playerArray as $playerIdx => $player) {
            foreach ($player->activeDieArray as $dieIdx => $die) {
                if ($die->has_skill($skill)) {
                    $areAnyDiceRemoved[$playerIdx] = TRUE;
                    unset($player->activeDieArray[$dieIdx]);
                }
            }
            if ($areAnyDiceRemoved[$playerIdx]) {
                $player->activeDieArray = array_values($player->activeDieArray);
            }
        }

        return $areAnyDiceRemoved;
    }

    /**
     * Perform the logic required at BMGameState::choose_reserve_dice
     */
    protected function do_next_step_choose_reserve_dice() {
        $this->setAllToNotWaiting();

        if (array_sum($this->getBMPlayerProps('isPrevRoundWinner')) > 0) {
            $haveReserveDice = $this->do_players_have_dice_with_skill('Reserve');

            if (array_sum($haveReserveDice) > 0) {
                foreach ($this->playerArray as $playerIdx => $player) {
                    if (!$player->isPrevRoundWinner &&
                        $haveReserveDice[$playerIdx]) {
                        $player->waitingOnAction = TRUE;
                    }
                }
            }
        }
    }

    /**
     * Update game state from BMGameState::choose_reserve_dice if necessary
     */
    protected function update_game_state_choose_reserve_dice() {
        // if all decisions on reserve dice have been made
        if (!$this->isWaitingOnAnyAction()) {
            $this->update_prevOptValueArray();
            $areAnyDiceAdded = $this->add_selected_reserve_dice();

            if (array_sum($areAnyDiceAdded) > 0) {
                // update button recipes
                foreach ($this->playerArray as $playerIdx => $player) {
                    if ($areAnyDiceAdded[$playerIdx]) {
                        $player->button->update_button_recipe();
                    }
                }
            }

            $this->update_opt_requests_to_ignore_reserve_dice();
            $this->remove_dice_with_skill('Reserve');
            $this->gameState = BMGameState::SPECIFY_DICE;
        }
    }

    /**
     * Update option request array when reserve option dice are added
     */
    protected function update_prevOptValueArray() {
        foreach ($this->playerArray as $player) {
            if (!empty($player->prevOptValueArray)) {
                foreach (array_reverse(array_keys($player->activeDieArray)) as $dieIdx) {
                    if ($player->activeDieArray[$dieIdx]->has_flag('AddReserve')) {
                        // count how many dice don't have reserve on the left of the added die
                        $dieIndicesWithoutReserve = $player->die_indices_without_reserve();
                        $nDiceOnLeft = 0;
                        foreach ($dieIndicesWithoutReserve as $leftDieIdx) {
                            if ($leftDieIdx < $dieIdx) {
                                $nDiceOnLeft++;
                            }
                        }

                        // this code theoretically accommodates the ability to add multiple reserve
                        // option dice at once
                        foreach (array_reverse(array_keys($player->prevOptValueArray)) as $optIdx) {
                            // increment all keys for prevOptValueArray that aren't left of the new die
                            if ($optIdx >= $nDiceOnLeft) {
                                $player->prevOptValueArray[$optIdx + 1] = $player->prevOptValueArray[$optIdx];
                                unset($player->prevOptValueArray[$optIdx]);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Convert selected reserve dice to normal dice and then add them
     *
     * @return bool
     */
    protected function add_selected_reserve_dice() {
        $areAnyDiceAdded = array_fill(0, $this->nPlayers, FALSE);

        foreach ($this->playerArray as $playerIdx => $player) {
            foreach ($player->activeDieArray as $die) {
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

        return $areAnyDiceAdded;
    }

    /**
     * Stop reserve dice from requesting option values
     */
    protected function update_opt_requests_to_ignore_reserve_dice() {
        foreach ($this->playerArray as $player) {
            if (empty($player->optRequestArray)) {
                continue;
            }

            $newOptRequestArray = array();
            $dieIndicesWithoutReserve = $player->die_indices_without_reserve();

            foreach ($player->optRequestArray as $dieIdx => $optRequest) {
                $newDieIdx = array_search($dieIdx, $dieIndicesWithoutReserve, TRUE);
                if (FALSE !== $newDieIdx) {
                    $newOptRequestArray[$newDieIdx] = $optRequest;
                }
            }

            $player->optRequestArray = $newOptRequestArray;
        }
    }

    /**
     * Perform the logic required at BMGameState::specify_dice
     */
    protected function do_next_step_specify_dice() {
        $this->setAllToNotWaiting();
        $this->initialise_swing_value_array_array();
        $this->set_option_values();
        $this->set_swing_values();
        $this->roll_active_dice_needing_values();
    }

    /**
     * Copy specified swing requests to the swing value array if this
     * swing type hasn't yet been specified.
     */
    protected function initialise_swing_value_array_array() {
        foreach ($this->playerArray as $player) {
            if (empty($player->swingRequestArray)) {
                continue;
            }

            $keyArray = array_keys($player->swingRequestArray);

            foreach ($keyArray as $key) {
                // copy swing request keys to swing value keys if they
                // do not already exist
                if (!array_key_exists($key, $player->swingValueArray)) {
                    $player->swingValueArray[$key] = NULL;
                }

                // set waitingOnActionArray based on if there are
                // unspecified swing dice for that player
                if (is_null($player->swingValueArray[$key])) {
                    $player->waitingOnAction = TRUE;
                }
            }
        }
    }

    /**
     * Copy specified option requests to option dice if the
     * option value hasn't yet been specified.
     */
    protected function set_option_values() {
        foreach ($this->playerArray as $player) {
            if (empty($player->optRequestArray)) {
                continue;
            }

            foreach (array_keys($player->optRequestArray) as $dieIdx) {
                if (!empty($player->optValueArray)) {
                    $optValue = $player->optValueArray[$dieIdx];
                    if (isset($optValue)) {
                        $player->activeDieArray[$dieIdx]
                               ->set_optionValue($optValue);
                    }
                }

                if (!isset($player->activeDieArray[$dieIdx]->max)) {
                    $player->waitingOnAction = TRUE;
                    continue 2;
                }
            }
        }
    }

    /**
     * Copy specified swing values to swing dice.
     */
    protected function set_swing_values() {
        foreach ($this->playerArray as $player) {
            if (!$player->waitingOnAction) {
                // apply swing values
                foreach ($player->activeDieArray as $die) {
                    if (isset($die->swingType)) {
                        $isSetSuccessful = $die->set_swingValue($player->swingValueArray);
                        // act appropriately if the swing values are invalid
                        if (!$isSetSuccessful) {
                            $this->message = 'Invalid value submitted for swing die ' . $die->recipe;
                            $player->swingValueArray = array();
                            $player->waitingOnAction = TRUE;
                            return;
                        }
                    }
                }
            }
        }
    }

    /**
     * Roll active dice that are fully specified except for their value
     */
    protected function roll_active_dice_needing_values() {
        foreach ($this->playerArray as $player) {
            foreach ($player->activeDieArray as $dieIdx => $die) {
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

                if (!isset($die->value)) {
                    $player->activeDieArray[$dieIdx] = $die->make_play_die(FALSE);
                }
            }
        }
    }

    /**
     * Update game state from BMGameState::specify_dice if necessary
     */
    protected function update_game_state_specify_dice() {
        if (!$this->isWaitingOnAnyAction()) {
            foreach ($this->playerArray as $player) {
                $player->prevSwingValueArray = array();
                $player->prevOptValueArray = array();
            }
            $this->gameState = BMGameState::DETERMINE_INITIATIVE;
        }
    }

    /**
     * Perform the logic required at BMGameState::determine_initiative
     */
    protected function do_next_step_determine_initiative() {
        $response =
            BMGame::does_player_have_initiative_array(
                $this->getBMPlayerProps('activeDieArray'),
                $this->getBMPlayerProps('button'),
                TRUE
            );
        $hasInitiativeArray = $response['hasPlayerInitiative'];
        $actionLogInfo = array(
            'roundNumber' => $this->get__roundNumber(),
            'playerData' => array(),
        );
        foreach ($response['actionLogInfo'] as $playerIdx => $playerActionLogData) {
            $actionLogInfo['playerData'][$this->playerArray[$playerIdx]->playerId] = $playerActionLogData;
        }

        if (array_sum($hasInitiativeArray) > 1) {
            $playersWithInit = array();
            $actionLogInfo['tiedPlayerIds'] = array();
            foreach ($hasInitiativeArray as $playerIdx => $tempHasInitiative) {
                if ($tempHasInitiative) {
                    $playersWithInit[] = $playerIdx;
                    $actionLogInfo['tiedPlayerIds'][] = $this->playerArray[$playerIdx]->playerId;
                }
            }
            $randIdx = bm_rand(0, count($playersWithInit) - 1);
            $tempInitiativeIdx = $playersWithInit[$randIdx];
        } else {
            $tempInitiativeIdx =
                array_search(TRUE, $hasInitiativeArray, TRUE);
        }

        $this->playerWithInitiativeIdx = $tempInitiativeIdx;
        $actionLogInfo['initiativeWinnerId'] = $this->playerArray[$this->playerWithInitiativeIdx]->playerId;

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

    /**
     * Update game state from BMGameState::determine_initiative if necessary
     */
    protected function update_game_state_determine_initiative() {
        if (isset($this->playerWithInitiativeIdx)) {
            $this->gameState = BMGameState::REACT_TO_INITIATIVE;
        }
    }

    /**
     * Perform the logic required at BMGameState::react_to_initiative
     */
    protected function do_next_step_react_to_initiative() {
        foreach ($this->playerArray as $playerIdx => $player) {
            // do nothing if a player has won initiative
            if ($this->playerWithInitiativeIdx == $playerIdx) {
                continue;
            }

            $activeDieArray = $player->activeDieArray;
            foreach ($activeDieArray as &$activeDie) {
                // find out if any of the dice have the ability to react
                // when the player loses initiative
                $hookResultArray =
                    $activeDie->run_hooks(
                        'react_to_initiative',
                        array('activeDieArrayArray' => $this->getBMPlayerProps('activeDieArray'),
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
                        $player->waitingOnAction = TRUE;
                    }
                }
            }
        }
    }

    /**
     * Update game state from BMGameState::react_to_initiative if necessary
     */
    protected function update_game_state_react_to_initiative() {
        // if everyone is out of actions, reactivate chance dice
        if (!$this->isWaitingOnAnyAction()) {
            $this->gameState = BMGameState::START_ROUND;
            foreach ($this->playerArray as $player) {
                if (!empty($player->activeDieArray)) {
                    foreach ($player->activeDieArray as &$activeDie) {
                        if ($activeDie->has_skill('Chance')) {
                            $activeDie->remove_flag('Disabled');
                        }
                    }
                }
            }
        }
    }

    /**
     * Perform the logic required at BMGameState::start_round
     */
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

    /**
     * Update game state from BMGameState::start_round if necessary
     */
    protected function update_game_state_start_round() {
        if (isset($this->activePlayerIdx)) {
            $this->gameState = BMGameState::START_TURN;

            // re-enable focus dice for everyone apart from the active player
            foreach ($this->playerArray as $playerIdx => $player) {
                if ($this->activePlayerIdx == $playerIdx) {
                    continue;
                }
                if (count($player->activeDieArray) > 0) {
                    $activeDieArray = $player->activeDieArray;
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

    /**
     * Perform the logic required at BMGameState::start_turn
     */
    protected function do_next_step_start_turn() {
        $this->firingAmount = NULL;
        $this->perform_autopass();
        $this->setAllToNotWaiting();

        if (!isset($this->attack)) {
            $this->playerArray[$this->activePlayerIdx]->waitingOnAction = TRUE;
        }
    }

    /**
     * Set the attack to a pass attack if:
     * (i) the only valid attack is a pass attack, and
     * (ii) the player has the autopass preference set
     */
    protected function perform_autopass() {
        if (!isset($this->attack) &&
            $this->playerArray[$this->activePlayerIdx]->autopass &&
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

    /**
     * Update game state from BMGameState::start_turn if necessary
     */
    protected function update_game_state_start_turn() {
        if ($this->isWaitingOnAnyAction()) {
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

    /**
     * Validate the attack parameters
     *
     * @return bool
     */
    protected function are_attack_params_reasonable() {
        // if attack has not been set, ask player to select attack
        if (!isset($this->attack)) {
            $this->playerArray[$this->activePlayerIdx]->waitingOnAction = TRUE;
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

    /**
     * Create a BMAttack instance that corresponds to the attack
     * specified in $this->attack
     *
     * @return array|FALSE
     */
    protected function create_attack_instance() {
        if (!isset($this->attack)) {
            $this->regenerate_attack();
        }

        $attack = BMAttack::create($this->attack['attackType']);

        $this->attackerPlayerIdx = $this->attack['attackerPlayerIdx'];
        $this->defenderPlayerIdx = $this->attack['defenderPlayerIdx'];
        $attAttackDieArray = array();
        foreach ($this->attack['attackerAttackDieIdxArray'] as $attackerAttackDieIdx) {
            $activeDieArray = $this->playerArray[$this->attack['attackerPlayerIdx']]
                                   ->activeDieArray;
            $attackDie = &$activeDieArray[$attackerAttackDieIdx];
            if ($attackDie->has_flag('Dizzy')) {
                $this->message = 'Attempting to attack with a dizzy die.';
                $this->attack = NULL;
                return FALSE;
            }
            $attAttackDieArray[] = $attackDie;
        }
        $defAttackDieArray = array();
        foreach ($this->attack['defenderAttackDieIdxArray'] as $defenderAttackDieIdx) {
            $defAttackDieArray[] = &$this->playerArray[$this->attack['defenderPlayerIdx']]
                                         ->activeDieArray[$defenderAttackDieIdx];
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
            $this->playerArray[$this->activePlayerIdx]->waitingOnAction = TRUE;
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

    /**
     * This is intended to regenerate an attack during firing
     */
    protected function regenerate_attack() {
        $attackerPlayerIdx = NULL;
        $defenderPlayerIdx = NULL;
        $attackerAttackDieIdxArray = array();
        $defenderAttackDieIdxArray = array();
        $attackType = NULL;

        foreach ($this->playerArray as $playerIdx => $player) {
            foreach ($player->activeDieArray as $dieIdx => $die) {
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

    /**
     * Add the IsAttacker flag to all attacking dice
     */
    protected function add_flag_to_attackers() {
        if (empty($this->attack['attackerAttackDieIdxArray'])) {
            return;
        }

        foreach ($this->attack['attackerAttackDieIdxArray'] as $attackerAttackDieIdx) {
            $attackDie = &$this->playerArray[$this->attack['attackerPlayerIdx']]
                               ->activeDieArray[$attackerAttackDieIdx];
            $attackDie->add_flag('IsAttacker', $this->attack['attackType']);
        }
    }

    /**
     * Add the IsAttackTarget flag to all dice being targeted in the attack
     */
    protected function add_flag_to_attack_targets() {
        if (empty($this->attack['defenderAttackDieIdxArray'])) {
            return;
        }

        foreach ($this->attack['defenderAttackDieIdxArray'] as $defenderAttackDieIdx) {
            $defenderDie = &$this->playerArray[$this->attack['defenderPlayerIdx']]
                                 ->activeDieArray[$defenderAttackDieIdx];
            $defenderDie->add_flag('IsAttackTarget');
        }
    }

    /**
     * Perform the logic required at BMGameState::adjust_fire_dice
     */
    protected function do_next_step_adjust_fire_dice() {
        if ($this->needs_firing() || $this->allows_fire_overshooting()) {
            $this->playerArray[$this->attack['attackerPlayerIdx']]->waitingOnAction = TRUE;
        }
    }

    /**
     * Checks whether the attack requires fire assistance to be successful
     *
     * @return bool
     */
    protected function needs_firing() {
        $attackType = $this->attack['attackType'];

        if (($attackType != 'Power') &&
            ($attackType != 'Skill')) {
            return FALSE;
        }

        $attackerDieArray = array();
        $attackerPlayerIdx = $this->attack['attackerPlayerIdx'];
        foreach ($this->attack['attackerAttackDieIdxArray'] as $attackerDieIdx) {
            $attackerDie = $this->playerArray[$attackerPlayerIdx]
                                ->activeDieArray[$attackerDieIdx];
            $attackerDieArray[] = $attackerDie;
        }

        $defenderDieArray = array();
        $defenderPlayerIdx = $this->attack['defenderPlayerIdx'];
        foreach ($this->attack['defenderAttackDieIdxArray'] as $defenderDieIdx) {
            $defenderDie = $this->playerArray[$defenderPlayerIdx]
                                ->activeDieArray[$defenderDieIdx];
            $defenderDieArray[] = $defenderDie;
        }

        // check for need for firing:
        $attack = BMAttack::create($attackType);

        foreach ($attackerDieArray as $attackDie) {
            $attack->add_die($attackDie);
        }

        $needsFiring = !($attack->validate_attack(
            $this,
            $attackerDieArray,
            $defenderDieArray,
            0
        ));

        if ($needsFiring) {
            // Do rudimentary sanity check that the attacker actually has fire dice
            // Note that this doesn't actually do a full check that any fire dice are not part
            // of the attack already, nor that the fire dice can be turned down the correct amount
            // since such checks should have been part of find_attack()
            $hasFireDice = FALSE;
            foreach ($this->playerArray[$this->attack['attackerPlayerIdx']]->activeDieArray as $die) {
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
                $this->playerArray[$this->attackerPlayerIdx]->playerId,
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

    /**
     * This checks for the special case of a Power attack where the attacker is
     * already able to capture the defender without help, but where there is
     * fire support possible, and the attacker is not yet at maximum value.
     *
     * @return bool
     */
    protected function allows_fire_overshooting() {
        if ('Power' != $this->attack['attackType']) {
            return FALSE;
        }

        $attackerPlayerIdx = $this->attack['attackerPlayerIdx'];

        if (!$this->playerArray[$attackerPlayerIdx]->fireOvershooting) {
            return FALSE;
        }

        $attackerDieIdx = $this->attack['attackerAttackDieIdxArray'][0];
        $attackerDie = $this->playerArray[$attackerPlayerIdx]
                            ->activeDieArray[$attackerDieIdx];
        $attackerDieArray = array($attackerDie);

        $defenderPlayerIdx = $this->attack['defenderPlayerIdx'];
        $defenderDieIdx = $this->attack['defenderAttackDieIdxArray'][0];
        $defenderDie = $this->playerArray[$defenderPlayerIdx]
                            ->activeDieArray[$defenderDieIdx];
        $defenderDieArray = array($defenderDie);

        $attack = BMAttack::create('Power');

        $bounds = $attack->help_bounds_specific($this, $attackerDieArray, $defenderDieArray);
        if (0 == $bounds[1]) {
            return FALSE;
        }

        if ($attack->validate_attack(
            $this,
            $attackerDieArray,
            $defenderDieArray,
            max(1, $defenderDie->value - $attackerDie->value + 1)
        )) {
            $this->log_action(
                'allows_firing',
                $this->playerArray[$this->attackerPlayerIdx]->playerId,
                array(
                    'attackType' => $this->attack['attackType'],
                    'attackDice' => $this->get_action_log_data(
                        $attackerDieArray,
                        $defenderDieArray
                    ),
                )
            );

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Update game state from BMGameState::adjust_fire_dice if necessary
     */
    protected function update_game_state_adjust_fire_dice() {
        if ($this->isWaitingOnAnyAction()) {
            return;
        }

        $this->gameState = BMGameState::COMMIT_ATTACK;
    }

    /**
     * React with fire dice.
     *
     * react_to_firing expects one of the following input arrays:
     *
     *   1.  array('action' => 'cancel',
     *             'playerIdx' => $playerIdx,
     *             'dieIdxArray' => $dieIdxArray,
     *             'dieValueArray' => $dieValueArray)
     *       where $dieIdxArray and $dieValueArray are the raw inputs to
     *       BMInterface->adjust_fire()
     *
     *   2.  array('action' => 'no_turndown',
     *             'playerIdx' => $playerIdx,
     *             'dieIdxArray' => $dieIdxArray,
     *             'dieValueArray' => $dieValueArray)
     *       where $dieIdxArray and $dieValueArray are the raw inputs to
     *       BMInterface->adjust_fire()
     *
     *   3.  array('action' => 'turndown',
     *             'playerIdx' => $playerIdx,
     *             'fireValueArray' => array($dieIdx1 => $dieValue1,
     *                                       $dieIdx2 => $dieValue2))
     *       where the details of SOME or ALL fire dice are in $fireValueArray
     *
     * It returns a boolean telling whether the reaction has been successful.
     * If it fails, $game->message will say why it has failed.
     *
     * @param array $args
     * @return string|FALSE
     */
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
        $this->playerArray[$playerIdx]->waitingOnAction = FALSE;

        if (!in_array($args['action'], array('turndown', 'no_turndown', 'cancel'))) {
            throw new InvalidArgumentException(
                'Reaction must be turndown, no_turndown, or cancel.'
            );
        }

        $reactFuncName = 'react_to_firing_'.$args['action'];
        $reactResponse = $this->$reactFuncName($args);

        return $reactResponse;
    }

    /**
     * Attempt to turn down fire dice
     *
     * $fireValueArray is an associative array, with the keys being the
     * die indices of the attacker die array that are being specified
     *
     * @param array $args
     * @return bool
     */
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
            $die = $this->playerArray[$attackerIdx]->activeDieArray[$fireIdx];
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

        $fireRecipes = array();
        $oldValues = array();
        $newValues = array();

        foreach ($args['fireValueArray'] as $fireIdx => $newValue) {
            $fireDie = $this->playerArray[$attackerIdx]->activeDieArray[$fireIdx];

            $fireRecipes[] = $fireDie->recipe;
            $oldValues[] = $fireDie->value;
            $newValues[] = $newValue;

            $fireDie->value = $newValue;
        }

        $this->firingAmount = $firingAmount;
        $this->setAllToNotWaiting();

        $this->fireCache = array(
            'fireRecipes' => $fireRecipes,
            'oldValues' => $oldValues,
            'newValues' => $newValues,
        );

        return TRUE;
    }

    /**
     * Attempt to not turn down fire dice
     *
     * @return bool
     */
    protected function react_to_firing_no_turndown() {
        if (BMGameState::ADJUST_FIRE_DICE != $this->gameState) {
            $this->message = 'Wrong game state to react to firing.';
            return FALSE;
        }

        $instance = $this->create_attack_instance();
        if (FALSE === $instance) {
            $this->message = 'Invalid attack.';
            return FALSE;
        }

        if (!$instance['attack']->validate_attack(
            $this,
            $instance['attAttackDieArray'],
            $instance['defAttackDieArray'],
            0
        )) {
            $this->message = $instance['attack']->validationMessage;
            return FALSE;
        }

        $this->firingAmount = 0;
        $this->setAllToNotWaiting();

        $this->fireCache = array(
            'action' => 'zero_firing',
        );

        return TRUE;
    }

    /**
     * Cancel the current attack
     *
     * @return bool
     */
    protected function react_to_firing_cancel() {
        $this->attack = NULL;
        $this->gameState = BMGameState::START_TURN;
        $this->setAllToNotWaiting();
        $this->playerArray[$this->activePlayerIdx]->waitingOnAction = TRUE;

        foreach ($this->playerArray as $player) {
            if (empty($player->activeDieArray)) {
                continue;
            }

            foreach ($player->activeDieArray as $die) {
                $die->remove_flag('IsAttacker');
                $die->remove_flag('IsAttackTarget');
            }
        }

        $this->log_action(
            'fire_cancel',
            $this->playerArray[$this->activePlayerIdx]->playerId,
            array()
        );

        return TRUE;
    }

    /**
     * Perform the logic required at BMGameState::commit_attack
     */
    protected function do_next_step_commit_attack() {
        // display dice
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
            $this->playerArray[$this->attackerPlayerIdx]->playerId,
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

    /**
     * Remove all flags from all dice for all players
     */
    protected function remove_all_flags() {
        foreach ($this->playerArray as $player) {
            if (empty($player->activeDieArray)) {
                continue;
            }

            foreach ($player->activeDieArray as $die) {
                $die->remove_all_flags();
            }
        }

        foreach ($this->playerArray as $player) {
            if (empty($player->capturedDieArray)) {
                continue;
            }

            foreach ($player->capturedDieArray as $die) {
                $die->remove_all_flags();
            }
        }
    }

    /**
     * Update game state from BMGameState::commit_attack if necessary
     */
    protected function update_game_state_commit_attack() {
        if (isset($this->attack) &&
            !$this->isWaitingOnAnyAction()) {
            if (isset($this->attack['attackerPlayerIdx'])) {
                foreach ($this->playerArray[$this->attack['attackerPlayerIdx']]
                              ->activeDieArray as &$activeDie) {
                    if ($activeDie->has_flag('Dizzy')) {
                        $activeDie->remove_flag('Dizzy');
                    }
                }
            }
        }

        unset($this->fireCache);
        $this->gameState = BMGameState::CHOOSE_TURBO_SWING;
    }

    /**
     * Perform the logic required at BMGameState::choose_turbo_swing
     */
    protected function do_next_step_choose_turbo_swing() {

    }

    /**
     * Update game state from BMGameState::choose_turbo_swing if necessary
     */
    protected function update_game_state_choose_turbo_swing() {
        $this->gameState = BMGameState::END_TURN;
    }

    /**
     * Perform the logic required at BMGameState::end_turn
     */
    protected function do_next_step_end_turn() {
        $this->perform_end_of_turn_die_actions();
        $this->firingAmount = NULL;
    }

    /**
     * Perform die actions that occur at the end of each turn
     */
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
                $this->playerArray[$this->attackerPlayerIdx]->playerId,
                array(
                    'preRerollDieInfo' => $preRerollDieInfo,
                    'postRerollDieInfo' => $postRerollDieInfo,
                )
            );
        }
    }

    /**
     * Update game state from BMGameState::end_turn if necessary
     */
    protected function update_game_state_end_turn() {
        $nDice = array_map('count', $this->getBMPlayerProps('activeDieArray'));
        // check if any player has no dice, or if everyone has passed
        if ((0 === min($nDice)) ||
            ($this->nPlayers == $this->nRecentPasses) ||
            isset($this->forceRoundResult)) {
            $this->gameState = BMGameState::END_ROUND;
        } else {
            $this->gameState = BMGameState::START_TURN;
            $this->playerArray[$this->activePlayerIdx]->waitingOnAction = TRUE;
        }
        $this->attack = NULL;
    }

    /**
     * Perform the logic required at BMGameState::end_round
     */
    protected function do_next_step_end_round() {
        // stop degenerate games from running forever
        if ($this->get__roundNumber() >= 200) {
            $this->gameState = BMGameState::CANCELLED;
            return;
        }

        $roundScoreArray = array();
        foreach ($this->playerArray as $player) {
            $roundScoreArray[] = $player->roundScore;
        }

        if (isset($this->forceRoundResult)) {
            foreach ($this->playerArray as $playerIdx => $player) {
                $player->isPrevRoundWinner = $this->forceRoundResult[$playerIdx];
            }
            $isDraw = FALSE;
        } else {
            foreach ($this->playerArray as $player) {
                $player->isPrevRoundWinner = FALSE;
            }

            // check for draw currently assumes only two players
            $isDraw = $roundScoreArray[0] == $roundScoreArray[1];
        }

        if ($isDraw) {
            foreach ($this->playerArray as $player) {
                $player->gameScoreArray['D']++;
            }

            // james: currently there is no code for three draws in a row

            $this->log_action(
                'end_draw',
                0,
                array(
                    'roundNumber' => $this->get_prevRoundNumber(),
                    'roundScore'  => $roundScoreArray[0],
                )
            );
        } else {
            if (isset($this->forceRoundResult)) {
                $winnerIdx = array_search(TRUE, $this->forceRoundResult);
                $surrendered = TRUE;
            } else {
                $winnerIdx = array_search(max($roundScoreArray), $roundScoreArray);
                $surrendered = FALSE;
            }

            foreach ($this->playerArray as $playerIdx => $player) {
                $player->prevSwingValueArray = $player->swingValueArray;
                $player->prevOptValueArray = $player->optValueArray;
                if ($playerIdx == $winnerIdx) {
                    $player->gameScoreArray['W']++;
                    $player->isPrevRoundWinner = TRUE;
                } else {
                    $player->gameScoreArray['L']++;
                    $player->swingValueArray = array();
                    $player->optValueArray = array();
                }
            }
            $this->log_action(
                'end_winner',
                $this->playerArray[$winnerIdx]->playerId,
                array(
                    'roundNumber' => $this->get_prevRoundNumber(),
                    'winningRoundScore' => max($roundScoreArray),
                    'losingRoundScore' => min($roundScoreArray),
                    'surrendered' => $surrendered,
                )
            );
        }
        $this->reset_play_state();
    }

    /**
     * Update game state from BMGameState::end_round if necessary
     */
    protected function update_game_state_end_round() {
        if (isset($this->activePlayerIdx)) {
            return;
        }

        $this->gameState = BMGameState::LOAD_DICE_INTO_BUTTONS;
        foreach ($this->playerArray as $player) {
            if ($player->gameScoreArray['W'] >= $this->maxWins) {
                $this->gameState = BMGameState::END_GAME;
                return;
            }
        }
    }

    /**
     * Perform the logic required at BMGameState::end_game
     */
    protected function do_next_step_end_game() {
        $this->reset_play_state();

        foreach ($this->playerArray as $player) {
            // swingValueArray must be reset to clear entries in the
            // database table game_swing_map
            $player->swingValueArray = array();
            $player->prevSwingValueArray = array();

            // optValueArray must be reset to clear entries in the
            // database table game_option_map
            $player->optValueArray = array();
            $player->prevOptRequestArray = array();
        }
    }

    /**
     * Update game state from BMGameState::end_game if necessary
     */
    protected function update_game_state_end_game() {
    }

    /**
     * Perform the logic required at BMGameState::CANCELLED
     */
    protected function do_next_step_cancelled() {
        foreach ($this->playerArray as $player) {
            $player->waitingOnAction = FALSE;
        }
    }

    /**
     * Update game state from BMGameState::CANCELLED if necessary
     */
    protected function update_game_state_cancelled() {
    }

    /**
     * Carry out all automated game actions until a player needs to do something
     *
     * The idea here is to call update_game_state() and do_next_step() one after
     * another until:
     * (i) the game requires a player action, or
     * (ii) the game reaches a game state that signals that it has ended
     *
     * The variable $gameStateBreakpoint is used for debugging purposes only.
     * If used, the game will stop as soon as the game state becomes
     * the value of $gameStateBreakpoint.
     *
     * @param int $gameStateBreakpoint
     */
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

        while (!$this->isWaitingOnAnyAction()) {
            $tempGameState = $this->gameState;
            $this->update_game_state();

            if (isset($gameStateBreakpoint) &&
                ($gameStateBreakpoint == $this->gameState)) {
                return;
            }

            $this->do_next_step();

            if ($this->gameState >= BMGameState::END_GAME) {
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

    /**
     * React to an initiative decision
     *
     * react_to_initiative expects one of the following three input arrays:
     *
     *   1.  array('action' => 'chance',
     *             'playerIdx => $playerIdx,
     *             'rerolledDieIdx' => $rerolledDieIdx)
     *       where the index of the rerolled chance die is in $rerolledDieIdx
     *
     *   2.  array('action' => 'decline',
     *             'playerIdx' => $playerIdx,
     *             'dieIdxArray' => $dieIdxArray,
     *             'dieValueArray' => $dieValueArray)
     *       where $dieIdxArray and $dieValueArray are the raw inputs to
     *       BMInterface->react_to_initiative()
     *
     *   3.  array('action' => 'focus',
     *             'playerIdx' => $playerIdx,
     *             'focusValueArray' => array($dieIdx1 => $dieValue1,
     *                                        $dieIdx2 => $dieValue2))
     *       where the details of SOME or ALL focus dice are in $focusValueArray
     *
     * It returns a boolean telling whether the reaction has been successful.
     * If it fails, $game->message will say why it has failed.
     *
     * @param array $args
     * @return bool
     */
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
        $this->playerArray[$playerIdx]->waitingOnAction = FALSE;

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

    /**
     * React to an initiative decision by attempting to reroll a chance die
     *
     * @param array $args
     * @return bool
     */
    protected function react_to_initiative_chance($args) {
        if (!array_key_exists('rerolledDieIdx', $args)) {
            $this->message = 'rerolledDieIdx must exist.';
            return FALSE;
        }

        $playerIdx = $args['playerIdx'];
        $player = $this->playerArray[$playerIdx];

        if (FALSE ===
            filter_var(
                $args['rerolledDieIdx'],
                FILTER_VALIDATE_INT,
                array("options"=>
                      array("min_range"=>0,
                            "max_range"=>count($player->activeDieArray) - 1))
            )) {
            $this->message = 'Invalid die index.';
            return FALSE;
        }

        $die = $player->activeDieArray[$args['rerolledDieIdx']];
        if (FALSE === array_search('BMSkillChance', $die->skillList)) {
            $this->message = 'Can only apply chance action to chance die.';
            return FALSE;
        }

        $preRerollRecipe = $die->get_recipe(TRUE);
        $preRerollValue = $die->value;
        $die->roll(FALSE);

        $postRerollRecipe = $die->get_recipe(TRUE);
        $postRerollValue = $die->value;

        $newInitiativeArray = BMGame::does_player_have_initiative_array(
            $this->getBMPlayerProps('activeDieArray')
        );

        if ($newInitiativeArray[$playerIdx] && (1 == array_sum($newInitiativeArray))) {
            $this->gameState = BMGameState::DETERMINE_INITIATIVE;
        } else {
            // only need to disable chance dice if the reroll fails to gain initiative
            foreach ($player->activeDieArray as &$die) {
                if ($die->has_skill('Chance')) {
                    $die->add_flag('Disabled');
                }
            }
        }

        $gainedInitiative = $newInitiativeArray[$playerIdx] &&
                            (1 == array_sum($newInitiativeArray));

        $this->log_action(
            'reroll_chance',
            $player->playerId,
            array(
                'origRecipe' => $preRerollRecipe,
                'origValue' => $preRerollValue,
                'rerollRecipe' => $postRerollRecipe,
                'rerollValue' => $postRerollValue,
                'gainedInitiative' => $gainedInitiative,
            )
        );

        return array('gainedInitiative' => $gainedInitiative);
    }

    /**
     * React to an initiative decision by declining
     *
     * @param array $args
     * @return bool
     */
    protected function react_to_initiative_decline($args) {
        // if there is die information sent, check that it signals
        // no change
        if (!array_key_exists('dieIdxArray', $args) ||
            !array_key_exists('dieValueArray', $args)) {
            $this->message = 'dieIdxArray and dieValueArray must exist.';
            return FALSE;
        }

        $playerIdx = $args['playerIdx'];
        $player = $this->playerArray[$playerIdx];
        $dieIdxArray = $args['dieIdxArray'];
        $dieValueArray = $args['dieValueArray'];

        // validate decline action
        if (is_array($dieIdxArray) &&
            count($dieIdxArray) > 0) {
            foreach ($dieIdxArray as $listIdx => $dieIdx) {
                $die = $player->activeDieArray[$dieIdx];

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
            $player->playerId,
            array()
        );

        if (!$this->isWaitingOnAnyAction()) {
            $this->gameState = BMGameState::START_ROUND;
        }

        return array('gainedInitiative' => FALSE);
    }

    /**
     * Check if any valid chance action has been selected
     *
     * @param BMDie $die
     * @param array $dieValueArray
     * @param array $dieIdxArray
     * @return bool
     */
    protected function possibleChanceAction($die, $dieValueArray, $dieIdxArray) {
        // check for ANY selected dice, not just a single die
        $possChanceAction = $die->has_skill('Chance') &&
                            !isset($dieValueArray) &&
                            (count($dieIdxArray) >= 1);

        return $possChanceAction;
    }

    /**
     * Check if any valid focus action has been selected
     *
     * @param BMDie $die
     * @param array $dieValueArray
     * @param array $dieIdxArray
     * @param int $listIdx
     * @return bool
     */
    protected function possibleFocusAction($die, $dieValueArray, $dieIdxArray, $listIdx) {
        // check for ANY change in die value, also invalid changes
        $possFocusAction = $die->has_skill('Focus') &&
                           is_array($dieValueArray) &&
                           (count($dieIdxArray) == count($dieValueArray)) &&
                           ($die->value != $dieValueArray[$listIdx]);

        return $possFocusAction;
    }

    /**
     * React to an initiative decision by attempting to turn down a focus die
     *
     * @param array $args
     * @return bool
     */
    protected function react_to_initiative_focus($args) {
        $isValid = $this->validateFocusAction($args);

        if (!$isValid) {
            return FALSE;
        }

        $playerIdx = $args['playerIdx'];
        $player = $this->playerArray[$playerIdx];

        // change specified die values
        $oldDieValueArray = array();
        foreach ($args['focusValueArray'] as $dieIdx => $newDieValue) {
            $oldDieValueArray[$dieIdx] = $player->activeDieArray[$dieIdx]->value;
            $player->activeDieArray[$dieIdx]->value = $newDieValue;
        }
        $newInitiativeArray = BMGame::does_player_have_initiative_array(
            $this->getBMPlayerProps('activeDieArray')
        );

        // if the change is successful, disable focus dice that changed
        // value
        $turndownDiceLogInfo = array();
        if ($newInitiativeArray[$playerIdx] &&
            1 == array_sum($newInitiativeArray)) {
            foreach ($oldDieValueArray as $dieIdx => $oldDieValue) {
                if ($oldDieValue >
                    $player->activeDieArray[$dieIdx]->value) {
                    $player->activeDieArray[$dieIdx]->add_flag('Dizzy');
                    $turndownDiceLogInfo[] = array(
                        'recipe'        => $player->activeDieArray[$dieIdx]->get_recipe(TRUE),
                        'origValue'     => $oldDieValue,
                        'turndownValue' => $player->activeDieArray[$dieIdx]->value,
                    );
                }
            }
        } else {
            // if the change does not gain initiative unambiguously, it is
            // invalid, so reset die values to original values
            foreach ($oldDieValueArray as $dieIdx => $oldDieValue) {
                $player->activeDieArray[$dieIdx]->value = $oldDieValue;
            }
            $this->message = 'You did not turn your focus dice down far enough to gain initiative.';
            return FALSE;
        }

        $this->log_action(
            'turndown_focus',
            $player->playerId,
            array(
                'turndownDice' => $turndownDiceLogInfo,
            )
        );

        $this->gameState = BMGameState::DETERMINE_INITIATIVE;
        return array('gainedInitiative' => TRUE);
    }

    /**
     * Validate proposed focus action
     *
     * @param array $args
     * @return bool
     */
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
        $player = $this->playerArray[$playerIdx];

        // focusValueArray should have the form array($dieIdx1 => $dieValue1, ...)
        foreach ($focusValueArray as $dieIdx => $newDieValue) {
            if (FALSE ===
                filter_var(
                    $dieIdx,
                    FILTER_VALIDATE_INT,
                    array("options"=>
                          array("min_range"=>0,
                                "max_range"=>count($player->activeDieArray) - 1))
                )) {
                $this->message = 'Invalid die index.';
                return FALSE;
            }

            $die = $player->activeDieArray[$dieIdx];

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

    /**
     * Add die to active die array of its owning player
     *
     * @param BMDie $die
     */
    public function add_die($die) {
        $this->playerArray[$die->playerIdx]->activeDieArray[] = $die;
    }

    /**
     * Capture die and transfer its ownership
     *
     * @param BMDie $die
     * @param int $newOwnerIdx
     */
    public function capture_die($die, $newOwnerIdx = NULL) {
        $dieIdx = FALSE;
        foreach ($this->playerArray as $player) {
            if (empty($player->activeDieArray)) {
                break;
            }
            $dieIdx = array_search($die, $player->activeDieArray, TRUE);
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
        $this->playerArray[$newOwnerIdx]->capturedDieArray[] = $player->activeDieArray[$dieIdx];
        // remove captured die from active die array
        array_splice($player->activeDieArray, $dieIdx, 1);
    }

    /**
     * Add swing requests of a die to the swing request array
     *
     * @param BMDie $die
     * @param string $swingtype
     * @param int $playerIdx
     */
    public function request_swing_values($die, $swingtype, $playerIdx) {
        if (!$die->does_skip_swing_request()) {
            $this->playerArray[$playerIdx]->swingRequestArray[$swingtype][] = $die;
        }
    }

    /**
     * Add option requests of a die to the option request array
     *
     * @param BMDie $die
     * @param array $optionArray
     * @param int $playerIdx
     */
    public function request_option_values($die, $optionArray, $playerIdx) {
        $player = $this->playerArray[$playerIdx];
        $dieIdx = array_search($die, $player->activeDieArray, TRUE);
        assert(FALSE !== $dieIdx);
        $player->optRequestArray[$dieIdx] = $optionArray;
    }

    /**
     * Checks whether each player has the initiative
     *
     * If $returnActionLogInfo is TRUE, then action log info is also returned
     *
     * @param array $activeDieArrayArray
     * @param array $buttonArray
     * @param bool $returnActionLogInfo
     * @return mixed
     */
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

            if (!empty($buttonArray) &&
                !empty($buttonArray[$playerIdx])) {
                // add an artificial PHP_INT_MAX - 1 to each array,
                // except if the button is slow
                if (self::is_button_slow($buttonArray[$playerIdx])) {
                    $initiativeArrayArray[$playerIdx] = array();
                    $actionLogInfo[$playerIdx]['slowButton'] = TRUE;
                } else {
                    $initiativeArrayArray[$playerIdx][] = PHP_INT_MAX - 1;
                }
            }

            sort($initiativeArrayArray[$playerIdx]);
        }

        // determine player that has won initiative
        $hasPlayerInitiative = BMGame::compute_initiative_winner_array(
            count($activeDieArrayArray),
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
     * @param int $nPlayers
     * @param array $initiativeArrayArray
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

    /**
     * Is this button slow?
     *
     * @param BMButton $button
     * @return bool
     */
    protected static function is_button_slow($button) {
        $hookResult = $button->run_hooks(
            __FUNCTION__,
            array('name' => $button->name)
        );

        $isSlow = isset($hookResult['BMBtnSkill'.$button->name]['is_button_slow']) &&
                  $hookResult['BMBtnSkill'.$button->name]['is_button_slow'];

        return $isSlow;
    }

    /**
     * Is the die fully specified?
     *
     * At the moment, this actually just checks whether the die has a value
     *
     * @param BMDie $die
     * @return bool
     */
    public static function is_die_specified($die) {
        // A die can be unspecified if it is swing, option, or plasma.

        // If plasma, then it is unspecified if the skills are unclear.
        // james: not written yet

        return (isset($die->max));
    }

    /**
     * Find all valid attack types, excluding Default and Surrender
     *
     * @return array
     */
    public function valid_attack_types() {
        // james: assume two players at the moment
        $attackerIdx = $this->activePlayerIdx;
        $defenderIdx = ($attackerIdx + 1) % 2;

        $attackTypeArray = BMAttack::possible_attack_types($this->playerArray[$attackerIdx]->activeDieArray);

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

        foreach ($this->playerArray[$attackerIdx]->activeDieArray as $activeDie) {
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

    /**
     * Check whether a valid attack exists, given:
     * - which player is the attacker
     * - which player is the defender
     * - the attack type
     * - whether optional attacks should be included
     *
     * @param int $attackerIdx
     * @param int $defenderIdx
     * @param string $attackType
     * @param bool $includeOptional
     * @return bool
     */
    protected function does_valid_attack_exist(
        $attackerIdx,
        $defenderIdx,
        $attackType,
        $includeOptional
    ) {
        $this->attack = array('attackerPlayerIdx' => $attackerIdx,
                              'defenderPlayerIdx' => $defenderIdx,
                              'attackerAttackDieIdxArray' =>
                                  range(0, count($this->playerArray[$attackerIdx]->activeDieArray) - 1),
                              'defenderAttackDieIdxArray' =>
                                  range(0, count($this->playerArray[$defenderIdx]->activeDieArray) - 1),
                              'attackType' => $attackType);
        $attack = BMAttack::create($attackType);
        foreach ($this->playerArray[$attackerIdx]->activeDieArray as $attackDie) {
            $attack->add_die($attackDie);
        }
        return $attack->find_attack($this, $includeOptional);
    }

    /**
     * Clear most of the information in the BMGame
     *
     * This is used at the end of each round and at the end of the game
     */
    public function reset_play_state() {
        $this->activePlayerIdx = NULL;
        $this->playerWithInitiativeIdx = NULL;
        foreach ($this->playerArray as $player) {
            $player->activeDieArray = array();
            $player->capturedDieArray = array();
            $player->outOfPlayDieArray = array();
            $player->swingRequestArray = array();
            $player->optRequestArray = array();
        }
        $this->attack = NULL;

        $this->nRecentPasses = 0;
        $this->turnNumberInRound = 0;
        $this->setAllToNotWaiting();
        unset($this->forceRoundResult);
    }

    /**
     * Update the active player
     */
    protected function update_active_player() {
        if (!isset($this->activePlayerIdx)) {
            throw new LogicException(
                'Active player must be set before it can be updated.'
            );
        }

        // move to the next player
        if (isset($this->nextPlayerIdx)) {
            if ($this->nextPlayerIdx === $this->activePlayerIdx) {
                // james: currently, the only reason that this would be true is TimeAndSpace,
                //        so hard code it for the moment
                $this->log_action(
                    'play_another_turn',
                    $this->playerArray[$this->activePlayerIdx]->playerId,
                    array('cause' => 'TimeAndSpace')
                );
            }

            $this->activePlayerIdx = $this->nextPlayerIdx;
            $this->nextPlayerIdx = NULL;
        } else {
            $this->activePlayerIdx = ($this->activePlayerIdx + 1) % $this->nPlayers;
        }

        $this->setAllToNotWaiting();
    }

    // utility methods
    /**
     * Constructor
     *
     * @param int $gameID
     * @param array $playerIdArray
     * @param array $buttonRecipeArray
     * @param int $maxWins
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

        $this->nPlayers = count($playerIdArray);
        $this->gameId = $gameID;

        $playerArray = array_fill(0, $this->nPlayers, NULL);
        foreach ($playerArray as $playerIdx => &$player) {
            $player = new BMPlayer(
                $playerIdArray[$playerIdx],
                $buttonRecipeArray[$playerIdx],
                $playerIdx
            );
            $player->ownerObject = $this;
        }
        $this->playerArray = $playerArray;

        $this->gameState = BMGameState::START_GAME;
        $this->maxWins = $maxWins;
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
        return array_sum($this->playerArray[0]->gameScoreArray);
    }

    /**
     * Array of relative side scores
     *
     * @return array
     */
    protected function get_sideScoreArray() {
        if (2 != $this->nPlayers ||
            is_null($this->playerArray[0]->roundScore) ||
            is_null($this->playerArray[1]->roundScore)) {
            return array_fill(0, $this->nPlayers, NULL);
        }

        $sideDifference = round(
            2/3 * ($this->playerArray[0]->roundScore - $this->playerArray[1]->roundScore),
            1
        );
        return array($sideDifference, -$sideDifference);
    }

    /**
     * Record a game action in the history log
     *
     * @param string $actionType
     * @param int $actingPlayerIdx
     * @param array $params
     */
    public function log_action($actionType, $actingPlayerIdx, $params) {
        $this->actionLog[] = new BMGameAction(
            $this->gameState,
            $actionType,
            $actingPlayerIdx,
            $params
        );
    }

    /**
     * Empty the action log after its entries have been stored in the database
     */
    public function empty_action_log() {
        $this->actionLog = array();
    }

    /**
     * Add chat to chat log
     *
     * N.B. The chat text has not been sanitized at this point, so don't
     *      use it for anything
     *
     * @param int $playerIdx
     * @param string $chat
     */
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
        // support explicit accessor methods
        $funcName = 'get__'.$property;
        if (method_exists($this, $funcName)) {
            return $this->$funcName();
        }

        // support direct access to explicitly defined properties
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        // support accessors for arrays of BMPlayer properties
        if ('Array' != substr($property, -5)) {
            return;
        }

        $subProperty = substr($property, 0, strlen($property) - 5);

        if (property_exists('BMPlayer', $subProperty)) {
            return $this->getBMPlayerProps($subProperty);
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
        if (!isset($this->attack)) {
            return NULL;
        }
        return $this->playerArray[$this->attack['attackerPlayerIdx']]->activeDieArray;
    }

    /**
     * Array of all active dice of the defender
     *
     * @return null|array
     */
    protected function get__defenderAllDieArray() {
        if (!isset($this->attack)) {
            return NULL;
        }
        return $this->playerArray[$this->attack['defenderPlayerIdx']]->activeDieArray;
    }

    /**
     * Array of attacking dice of the attacker
     *
     * @return null|array
     */
    protected function get__attackerAttackDieArray() {
        if (!isset($this->attack)) {
            return NULL;
        }
        $attAttackDieArray = array();
        foreach ($this->attack['attackerAttackDieIdxArray'] as $attAttackDieIdx) {
            $attAttackDieArray[] =
                $this->playerArray[$this->attack['attackerPlayerIdx']]
                     ->activeDieArray[$attAttackDieIdx];
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
                $this->playerArray[$this->attack['defenderPlayerIdx']]
                     ->activeDieArray[$defAttackDieIdx];
        }
        return $defAttackDieArray;
    }

    /**
     * Current round number
     *
     * @return int
     */
    protected function get__roundNumber() {
        $roundNumber = array_sum($this->playerArray[0]->gameScoreArray) + 1;
        $playerScoreArray = $this->playerArray[0]->gameScoreArray;

        if (max($playerScoreArray['W'], $playerScoreArray['L']) >= $this->maxWins) {
            $roundNumber--;
        }

        return $roundNumber;
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
            return;
        }

        if (property_exists('BMGame', $property)) {
            $this->$property = $value;
            return;
        }

        // support setters for arrays of BMPlayer properties
        if ('Array' != substr($property, -5)) {
            return;
        }

        $subProperty = substr($property, 0, strlen($property) - 5);
        // now explicitly look for arrays like activeDieArrayArray
        $isDieArray = ('DieArray' == substr($subProperty, -8));

        if (property_exists('BMPlayer', $subProperty)) {
            if ('gameScoreArray' == $subProperty) {
                foreach ($this->playerArray as $playerIdx => $player) {
                    $player->set_gameScoreArray($value[$playerIdx]);
                }
            } elseif ($isDieArray) {
                $this->setBMPlayerProps($subProperty, $value, 'BMDie');
            } else {
                $this->setBMPlayerProps($subProperty, $value);
            }
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
     * Allow setting the player array
     *
     * @param array $value
     */
    protected function set__playerArray($value) {
        if (!is_array($value)) {
            throw new InvalidArgumentException(
                'playerArray must be an array.'
            );
        }

        foreach ($value as $player) {
            if (!($player instanceof BMPlayer)) {
                throw new InvalidArgumentException(
                    'playerArray must be an array of BMPlayer objects.'
                );
            }
        }

        $this->playerArray = $value;
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
                            "max_range"=>$this->nPlayers))
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
                          "max_range"=>$this->nPlayers))
            )) {
            throw new InvalidArgumentException(
                'Invalid player index.'
            );
        }
        $this->playerWithInitiativeIdx = (int)$value;
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
                 (count($this->playerArray[$value[0]]->activeDieArray) - 1) ||
             min($value[2]) < 0)) {
            throw new LogicException(
                'Invalid attacker attack die indices.'
            );
        }

        if (count($value[3]) > 0 &&
            (max($value[3]) >
                 (count($this->playerArray[$value[1]]->activeDieArray) - 1) ||
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

    /**
     * Validate the format of the attack array
     *
     * @param array $value
     */
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
     * Prevent setting of the round number
     */
    protected function set__roundNumber() {
        throw new LogicException(
            'BMGame->roundNumber is derived automatically from BMGame.'
        );
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
     * @return bool
     */
    public function __isset($property) {
        return isset($this->$property);
    }

    /**
     * Define behaviour of unset()
     *
     * @param string $property
     * @return bool
     */
    public function __unset($property) {
        if (isset($this->$property)) {
            unset($this->$property);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Get the JSON data corresponding to this game
     *
     * @param int $requestingPlayerId
     * @return array
     */
    public function getJsonData($requestingPlayerId) {
        $requestingPlayerIdx = array_search($requestingPlayerId, $this->getBMPlayerProps('playerId'));

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
        $sideScoreArray = $this->get_sideScoreArray();
        $canStillWinArray = $this->get_canStillWinArray();

        foreach ($this->playerArray as $playerIdx => $player) {
            $playerData = array(
                'playerId'            => $player->playerId,
                'button'              => $this->get_buttonInfo($playerIdx),
// BMGame sometimes hides swing information that would be present in the active dice
                'activeDieArray'      => $this->get_activeDieArray($playerIdx, $requestingPlayerIdx),
                'capturedDieArray'    => $this->get_capturedDieArray($playerIdx),
                'outOfPlayDieArray'   => $this->get_outOfPlayDieArray($playerIdx),
// BMGame only shows swing requests to the owner of the swing dice
                'swingRequestArray'   => $this->get_swingRequestArray($playerIdx, $requestingPlayerIdx),
                'optRequestArray'     => $player->optRequestArray,
                'prevSwingValueArray' => $player->prevSwingValueArray,
                'prevOptValueArray'   => $player->prevOptValueArray,
// BMGame may lie about who's actually waiting
                'waitingOnAction'     => $this->isWaitingOnAction($playerIdx, $requestingPlayerIdx),
                'roundScore'          => $player->roundScore,
                'sideScore'           => $sideScoreArray[$playerIdx],
                'gameScoreArray'      => $player->gameScoreArray,
                'lastActionTime'      => $player->lastActionTime,
                'hasDismissedGame'    => $player->hasPlayerDismissedGame,
                'canStillWin'         => $canStillWinArray[$playerIdx],
            );

            $playerDataArray[] = $playerData;
        }
        return $playerDataArray;
    }

    /**
     * Perform a deep clone of the active die array array
     *
     * This is used so that changes don't propagate back into the
     * real game data
     *
     * @return array
     */
    protected function clone_activeDieArrayArray() {
        $activeDieArrayArray = array_fill(0, $this->nPlayers, array());

        foreach ($this->playerArray as $playerIdx => $player) {
            if (count($player->activeDieArray) > 0) {
                foreach ($player->activeDieArray as $dieIdx => $activeDie) {
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
            'originalRecipe' => '',
            'artFilename' => '',
        );
        $button = $this->playerArray[$playerIdx]->button;
        if ($button instanceof BMButton) {
            $buttonInfo['name'] = $button->name;
            $buttonInfo['recipe'] = $button->recipe;
            $buttonInfo['originalRecipe'] = $button->originalRecipe;
            $buttonInfo['artFilename'] = $button->artFilename;
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
        if (!empty($this->playerArray[$playerIdx]->capturedDieArray)) {
            foreach ($this->playerArray[$playerIdx]->capturedDieArray as $die) {
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
        if (!empty($this->playerArray[$playerIdx]->outOfPlayDieArray)) {
            foreach ($this->playerArray[$playerIdx]->outOfPlayDieArray as $die) {
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

        return $sidesArrayArray;
    }

    /**
     * Array of arrays of possible arrays of subdie properties
     *
     * @param int $requestingPlayerIdx
     * @return array
     */
    protected function get_subdieArrayArray($requestingPlayerIdx) {
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

    /**
     * Check whether the current game state is before BMGameState::SPECIFY_DICE
     *
     * @return bool
     */
    protected function isGameStateBeforeSpecifyingDice() {
        return $this->gameState <= BMGameState::SPECIFY_DICE;
    }

    /**
     * Check whether die data should be hidden for the requesting player
     *
     * @param int $playerIdx
     * @param int $requestingPlayerIdx
     * @return bool
     */
    protected function shouldDieDataBeHidden($playerIdx, $requestingPlayerIdx) {
        return ($this->wereSwingOrOptionValuesReset() &&
                $this->isGameStateBeforeSpecifyingDice() &&
                ($playerIdx !== $requestingPlayerIdx));
    }

    /**
     * Copy subdie properties into a format that can be exported to the UI
     *
     * Returns whether all the properties are NULL
     *
     * @param array $subdieArrayArray
     * @param int $playerIdx
     * @param int $dieIdx
     * @param int $subdieIdx
     * @param BMDie $subdie
     * @return bool
     */
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

        foreach ($this->playerArray as $playerIdx => $player) {
            if (count($player->activeDieArray) > 0) {
                $dieSkillsArrayArray[$playerIdx] =
                    array_fill(0, count($player->activeDieArray), array());
            }

            foreach ($player->activeDieArray as $dieIdx => $die) {
                if (count($die->skillList) > 0) {
                    foreach (array_keys($die->skillList) as $skillType) {
                        $dieSkillsArrayArray[$playerIdx][$dieIdx][] = $skillType;
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

        foreach ($this->playerArray as $playerIdx => $player) {
            if (count($player->activeDieArray) > 0) {
                $diePropsArrayArray[$playerIdx] =
                    array_fill(0, count($player->activeDieArray), array());
            }

            foreach ($player->activeDieArray as $dieIdx => $die) {
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

        return $diePropsArrayArray;
    }

    /**
     * Array of arrays of die recipes
     *
     * @return array
     */
    protected function get_dieRecipeArrayArray() {
        $dieRecipeArrayArray = array_fill(0, $this->nPlayers, array());

        foreach ($this->playerArray as $playerIdx => $player) {
            if (!empty($player->activeDieArray)) {
                foreach ($player->activeDieArray as $dieIdx => $die) {
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
     * Swing requests, taking into account whether these should be hidden
     * because the requesting player shouldn't see them
     *
     * @param int $playerIdx
     * @param int $requestingPlayerIdx
     * @return array
     */
    protected function get_swingRequestArray($playerIdx, $requestingPlayerIdx) {
        // do not show swing requests unless this information is actually necessary
        if ($this->gameState > BMGameState::SPECIFY_DICE) {
            return array();
        }

        $swingRequestArray = array();

        if ($this->gameState == BMGameState::CHOOSE_AUXILIARY_DICE) {
            $swingRequestArrayArray = $this->get_all_swing_requests(TRUE);
            // only show true swingRequestArrayArray if the player is requesting his/her own
            // information
            if (!$this->playerArray[$playerIdx]->waitingOnAction &&
                ($playerIdx == $requestingPlayerIdx)) {
                $swingRequestArrayArray[$playerIdx] =
                    $this->playerArray[$playerIdx]->swingRequestArray;
            }
        } elseif ($this->gameState <= BMGameState::CHOOSE_RESERVE_DICE) {
            $swingRequestArrayArray = $this->get_all_swing_requests(FALSE);
        } else {
            $swingRequestArrayArray = $this->swingRequestArrayArray;
        }

        if (isset($swingRequestArrayArray[$playerIdx])) {
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

    /**
     * Get all swing requests
     *
     * @param bool $includeCourtesyDice
     * @return array
     */
    protected function get_all_swing_requests($includeCourtesyDice = FALSE) {
        $swingRequestArrayArray = array_fill(0, $this->nPlayers, array());

        if (!$this->are_buttons_specified()) {
            return $swingRequestArrayArray;
        }

        $courtesySwingArray = array();

        foreach ($this->playerArray as $playerIdx => $player) {
            if (isset($player->button->dieArray)) {
                foreach ($player->button->dieArray as $die) {
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
     * Check whether all buttons are specified
     *
     * @return bool
     */
    protected function are_buttons_specified() {
        foreach ($this->playerArray as $player) {
            if (empty($player->button)) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Check whether a player is required to act, also taking into account
     * whether the requesting player is allowed to know.
     *
     * @param int $playerIdx
     * @param int $requestingPlayerIdx
     * @return bool
     */
    protected function isWaitingOnAction($playerIdx, $requestingPlayerIdx) {
        // actively lie about whether a player has chosen auxiliary dice
        // to avoid leaking information
        if ((BMGameState::CHOOSE_AUXILIARY_DICE == $this->gameState) &&
            $requestingPlayerIdx !== $playerIdx) {
            return TRUE;
        }

        return $this->playerArray[$playerIdx]->waitingOnAction;
    }

    /**
     * Array of valid attack types
     *
     * @return array
     */
    protected function get_validAttackTypeArray() {
        // If it's someone's turn to attack, report the valid attack
        // types as part of the game data
        if (BMGameState::START_TURN == $this->gameState) {
            $validAttackTypeArray = array_keys($this->valid_attack_types());
        } elseif (BMGameState::ADJUST_FIRE_DICE == $this->gameState) {
            $attackType = '';

            // need to reconstruct attack type from die flag BMFlagIsAttacker
            foreach ($this->playerArray as $player) {
                foreach ($player->activeDieArray as $die) {
                    if ($die->has_flag('IsAttacker')) {
                        foreach ($die->flagList as $flag) {
                            if ($flag instanceof BMFlagIsAttacker) {
                                $attackType = $flag->value();
                                break 3;
                            }
                        }
                    }
                }
            }
            $validAttackTypeArray = array($attackType);
        } else {
            $validAttackTypeArray = array();
        }

        return $validAttackTypeArray;
    }

    /**
     * Were the swing or option values reset?
     *
     * @return bool
     */
    protected function wereSwingOrOptionValuesReset() {
        // james: need to also consider the case of many multiple draws in a row
        foreach ($this->playerArray as $player) {
            if ($player->gameScoreArray['W'] > 0 || $player->gameScoreArray['D'] > 0) {
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
        $gameBtnSkillsWithKeysList = array();

        foreach ($this->playerArray as $player) {
            if (!is_null($player->button)) {
                $gameBtnSkillsWithKeysList += $player->button->skillList;

                if (!empty($player->button->dieArray)) {
                    foreach ($player->button->dieArray as $buttonDie) {
                        if (count($buttonDie->skillList) > 0) {
                            $gameSkillsWithKeysList += $buttonDie->skillList;
                        }
                    }
                }
            }
        }

        $gameSkillsList = array_keys($gameSkillsWithKeysList);
        sort($gameSkillsList);

        $gameBtnSkillsList = array_keys($gameBtnSkillsWithKeysList);
        sort($gameBtnSkillsList);

        $gameSkillsInfo = array();
        if (!empty($gameBtnSkillsList)) {
            foreach ($gameBtnSkillsList as $btnSkillType) {
                $btnSkillDescription = BMBtnSkill::describe($btnSkillType, FALSE);
                if (!empty($btnSkillDescription)) {
                    $gameSkillsInfo[$btnSkillType] = $btnSkillDescription;
                }
            }
        }
        if (!empty($gameSkillsList)) {
            foreach ($gameSkillsList as $skillType) {
                $gameSkillsInfo[$skillType] = BMSkill::describe($skillType, $gameSkillsList);
            }
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

    /**
     * Check whether there is a skill remaining in the game that makes it
     * impossible to determine whether there is already a winner
     *
     * @return bool
     */
    protected function has_skill_that_prevents_win_determination() {
        foreach ($this->playerArray as $player) {
            if (empty($player->activeDieArray)) {
                continue;
            }

            foreach ($player->activeDieArray as $activeDie) {
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

    // convenience getters to make refactoring to BMPlayer easier

    /**
     * This is the generic getter to access BMPlayer properties
     *
     * @param string $property
     * @return array
     */
    protected function getBMPlayerProps($property) {
        $resultArray = array();

        if (!empty($this->playerArray)) {
            foreach ($this->playerArray as $player) {
                $resultArray[] = $player->$property;
            }
        }

        return $resultArray;
    }

    /**
     * Is the game waiting on any player actions?
     *
     * @return bool
     */
    protected function isWaitingOnAnyAction() {
        return array_sum($this->getBMPlayerProps('waitingOnAction')) > 0;
    }


    // convenience setters to make refactoring to BMPlayer easier

    /**
     * This is the generic setter to set BMPlayer properties
     *
     * $subtype allows type checking of the contents of subArrays.
     *
     * @param string $property
     * @param array  $value
     * @param string $subclass
     */
    protected function setBMPlayerProps($property, array $value, $subclass = '') {
        if (empty($value) ||
            (count($value) != $this->nPlayers)) {
            throw new InvalidArgumentException(
                "Array length must equal the number of players"
            );
        }

        if ('Array' == substr($property, -5)) {
            foreach ($value as $subArray) {
                if (!is_array($subArray)) {
                    throw new InvalidArgumentException(
                        'Subarrays must be arrays.'
                    );
                }

                if (!empty($subArray) && ('' != $subclass)) {
                    foreach ($subArray as $obj) {
                        if (!($obj instanceof $subclass)) {
                            throw new InvalidArgumentException(
                                "Subarrays must be made up of $subclass objects."
                            );
                        }
                    }
                }
            }
        }

        if (!empty($this->playerArray)) {
            foreach ($this->playerArray as $playerIdx => $player) {
                $player->$property = $value[$playerIdx];
            }
        }
    }

    /**
     * Set all players' waitingOnAction statuses to FALSE
     */
    public function setAllToNotWaiting() {
        foreach ($this->playerArray as $player) {
            $player->waitingOnAction = FALSE;
        }
    }
}
