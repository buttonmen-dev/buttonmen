<?php

/**
 * BMGameAction: record of an action which happened during a game
 *
 * @author chaos
 *
 * @property     int    $gameState           BMGameState of the game when the action occurred
 * @property     string $actionType          Type of action which was taken
 * @property     int    $actingPlayerId      Database ID of player who took the action
 * @property     array  $params              Array of information about the action, format depends on actionType
 */

class BMGameAction {

    private $gameState;
    private $actionType;
    private $actingPlayerId;
    private $params;

    public function __construct(
        $gameState,
        $actionType,
        $actingPlayerId,
        $params
    ) {
        if (!$params) {
            throw new Exception("BMGameAction error: params can't be empty");
        }
        $this->gameState = $gameState;
        $this->actionType = $actionType;
        $this->actingPlayerId = $actingPlayerId;
        $this->params = $params;
    }

    public function friendly_message($playerIdNames, $roundNumber, $gameState) {
        $this->outputPlayerIdNames = $playerIdNames;
        $this->outputRoundNumber = $roundNumber;
        $this->outputGameState = $gameState;
        if (is_array($this->params)) {
            $funcName = 'friendly_message_' . $this->actionType;
            if (method_exists($this, $funcName)) {
                $result = $this->$funcName();
            } else {
                $result = "Internal error: could not print action log entry of type: "
                          . $this->actionType;
            }
            return $result;

        } else {
            // Messages should now be arrays, but some old string
            // messages might still be in the DB.  Use the old logic for these
            if ($this->actionType == 'attack') {
                return $playerIdNames[$this->actingPlayerId] . ' ' . $this->params;
            }
            if ($this->actionType == 'end_winner') {
                return ('End of round: ' . $playerIdNames[$this->actingPlayerId] . ' ' . $this->params);
            }
            return($this->params);
        }
    }

    protected function friendly_message_end_draw() {
        $message = 'Round ' . $this->params['roundNumber'] .
                   ' ended in a draw (' .
                   $this->params['roundScoreArray'][0] . ' vs. ' .
                   $this->params['roundScoreArray'][1] . ')';
        return $message;
    }

    protected function friendly_message_end_winner() {
        $message = 'End of round: ' . $this->outputPlayerIdNames[$this->actingPlayerId] .
                   ' won round ' . $this->params['roundNumber'];
        if (array_key_exists('resultForced', $this->params) && ($this->params['resultForced'])) {
            $message .= ' because opponent surrendered';
        } else {
            $message .= ' (' .  max($this->params['roundScoreArray']) . ' vs. ' .
                        min($this->params['roundScoreArray']) . ')';
        }
        return $message;
    }

    protected function friendly_message_attack() {
        $attackType = $this->params['attackType'];
        $preAttackDice = $this->params['preAttackDice'];
        $postAttackDice = $this->params['postAttackDice'];

        // Check for any attack types in which the defender changes
        // in some way we want to report prior to being captured
        if ($attackType == 'Trip') {
            $defenderRerollsEarly = TRUE;
        } else {
            $defenderRerollsEarly = FALSE;
        }

        // First, what type of attack was this?
        if ($attackType == 'Pass') {
            return $this->outputPlayerIdNames[$this->actingPlayerId] . ' passed';
        }

        if ($attackType == 'Surrender') {
            return $this->outputPlayerIdNames[$this->actingPlayerId] . ' surrendered';
        }

        $message = $this->outputPlayerIdNames[$this->actingPlayerId] . ' performed ' . $attackType . ' attack';

        // Add the pre-attack status of all participating dice
        $preAttackAttackers = array();
        $preAttackDefenders = array();
        foreach ($preAttackDice['attacker'] as $idx => $attackerInfo) {
            $preAttackAttackers[] = $attackerInfo['recipeStatus'];
        }
        foreach ($preAttackDice['defender'] as $idx => $defenderInfo) {
            $preAttackDefenders[] = $defenderInfo['recipeStatus'];
        }
        $message .= $this->preAttackMessage($preAttackAttackers, $preAttackDefenders);

        $messageDefender = $this->messageDefender($preAttackDice, $postAttackDice, $defenderRerollsEarly);

        if ($defenderRerollsEarly) {
            // this only triggers for trip attacks, so there can only be one attacker involved
            $midAttackDice = $preAttackDice;

            if (isset($postAttackDice['attacker'][0]['valueAfterTripAttack'])) {
                $midAttackDice['attacker'][0]['value'] =
                    $postAttackDice['attacker'][0]['valueAfterTripAttack'];
            }

            $message .= $this->messageAttacker($preAttackDice, $midAttackDice);
            $message .= $messageDefender;

            // now deal with morphing after trip
            if (isset($postAttackDice['attacker'][0]['hasJustMorphed']) &&
                ($postAttackDice['attacker'][0]['hasJustMorphed'])) {
                $message .= $this->messageAttacker($midAttackDice, $postAttackDice);
            }
        } else {
            $messageAttacker = $this->messageAttacker($preAttackDice, $postAttackDice);
            $message .= $messageDefender.$messageAttacker;
        }

        return $message;
    }

    protected function preAttackMessage($preAttackAttackers, $preAttackDefenders) {
        $message = '';

        if (count($preAttackAttackers) > 0) {
            $message .= ' using [' . implode(",", $preAttackAttackers) . ']';
        }

        if (count($preAttackDefenders) > 0) {
            $message .= ' against [' . implode(",", $preAttackDefenders) . ']';
        }

        return $message;
    }

    protected function messageDefender($preAttackDice, $postAttackDice, $defenderRerollsEarly) {
        $messageDefender = '';
        // Report what happened to each defending die
        foreach ($preAttackDice['defender'] as $idx => $defenderInfo) {
            $postInfo = $postAttackDice['defender'][$idx];
            $postEventsDefender = array();

            if ($defenderRerollsEarly) {
                if ($defenderInfo['doesReroll']) {
                    $postEventsDefender[] = 'rerolled ' . $defenderInfo['value'] . ' => ' .  $postInfo['value'];
                } else {
                    $postEventsDefender[] = 'does not reroll';
                }
            }

            if ($defenderInfo['recipe'] != $postInfo['recipe']) {
                $postEventsDefender[] = 'recipe changed from ' . $defenderInfo['recipe'] . ' to ' . $postInfo['recipe'];
            }
            if ($postInfo['captured']) {
                $postEventsDefender[] = 'was captured';
            } else {
                $postEventsDefender[] = 'was not captured';
            }
            $messageDefender .= '; Defender ' . $defenderInfo['recipe'] . ' ' . implode(', ', $postEventsDefender);
        }

        return $messageDefender;
    }

    protected function messageAttacker($preAttackDice, $postAttackDice) {
        $messageAttacker = '';
        // Report what happened to each attacking die
        foreach ($preAttackDice['attacker'] as $idx => $attackerInfo) {
            $postInfo = $postAttackDice['attacker'][$idx];
            $postEventsAttacker = array();

            if ($attackerInfo['max'] != $postInfo['max']) {
                $postEventsAttacker[] = 'changed size from ' . $attackerInfo['max'] . ' to ' .
                                        $postInfo['max'] . ' sides';
            } elseif (array_key_exists('forceReportDieSize', $attackerInfo) &&
                      $attackerInfo['forceReportDieSize']) {
                $postEventsAttacker[] = 'remained the same size';
            }
            if ($attackerInfo['recipe'] != $postInfo['recipe']) {
                $postEventsAttacker[] = 'recipe changed from ' . $attackerInfo['recipe'] . ' to ' . $postInfo['recipe'];
            }
            if ($attackerInfo['doesReroll']) {
                $postEventsAttacker[] = 'rerolled ' . $attackerInfo['value'] . ' => ' . $postInfo['value'];
            } else {
                $postEventsAttacker[] = 'does not reroll';
            }
            if (count($postEventsAttacker) > 0) {
                $messageAttacker .= '; Attacker ' . $attackerInfo['recipe'] . ' ' . implode(', ', $postEventsAttacker);
            }
        }

        return $messageAttacker;
    }

    protected function friendly_message_choose_die_values() {
        $message = $this->outputPlayerIdNames[$this->actingPlayerId] . ' set';

        // If the round is later than the one in which this action
        // log entry was recorded, or we're no longer in swing selection
        // state, report the values which were chosen as well
        if (($this->outputRoundNumber != $this->params['roundNumber']) ||
            ($this->outputGameState != BMGameState::SPECIFY_DICE)) {
            $dieMessages = array();
            if (count($this->params['swingValues']) > 0) {
                $swingStrs = array();
                foreach ($this->params['swingValues'] as $swingType => $swingValue) {
                    $swingStrs[] = $swingType . '=' . $swingValue;
                }
                $dieMessages[] = 'swing values: ' . implode(", ", $swingStrs);
            }
            if (count($this->params['optionValues']) > 0) {
                $optionStrs = array();
                foreach ($this->params['optionValues'] as $dieRecipe => $optionValue) {
                    $optionStrs[] = str_replace(')', '=' . $optionValue . ')', $dieRecipe);
                }
                $dieMessages[] = 'option dice: ' . implode(", ", $optionStrs);
            }
            $message .= ' ' . implode(" and ", $dieMessages);
        } else {
            $message .= ' die sizes';
        }
        return $message;
    }

    // Since the addition of option dice, new choose_swing log
    // entries are no longer added to the DB.  However, this code
    // must be retained to parse old log entries until/unless those
    // are converted.
    protected function friendly_message_choose_swing() {
        $message = $this->outputPlayerIdNames[$this->actingPlayerId] . ' set swing values';

        // If the round is later than the one in which this action
        // log entry was recorded, or we're no longer in swing selection
        // state, report the values which were chosen as well
        if (($this->outputRoundNumber != $this->params['roundNumber']) ||
            ($this->outputGameState != BMGameState::SPECIFY_DICE)) {
            $swingStrs = array();
            foreach ($this->params['swingValues'] as $swingType => $swingValue) {
                $swingStrs[] = $swingType . '=' . $swingValue;
            }
            $message .= ': ' . implode(", ", $swingStrs);
        }
        return $message;
    }

    protected function friendly_message_reroll_chance() {
        $message = $this->outputPlayerIdNames[$this->actingPlayerId] . ' rerolled a chance die';
        if ($this->params['gainedInitiative']) {
            $message .= ' and gained initiative';
        } else {
            $message .= ', but did not gain initiative';
        }
        $message .= ': ' . $this->params['preReroll']['recipe'] . ' rerolled ' .
                    $this->params['preReroll']['value'] . ' => ' . $this->params['postReroll']['value'];
        return $message;
    }

    protected function friendly_message_turndown_focus() {
        $message = $this->outputPlayerIdNames[$this->actingPlayerId] . ' gained initiative by turning down focus dice';
        $focusStrs = array();
        foreach ($this->params['preTurndown'] as $idx => $die) {
            $focusStrs[] = $die['recipe'] . ' from ' . $die['value'] . ' to ' .
                           $this->params['postTurndown'][$idx]['value'];
        }
        $message .= ': ' . implode(", ", $focusStrs);
        return $message;
    }

    protected function friendly_message_init_decline() {
        $message = $this->outputPlayerIdNames[$this->actingPlayerId] .
                   ' chose not to try to gain initiative using chance or focus dice';
        return $message;
    }

    protected function friendly_message_add_reserve() {
        $message = $this->outputPlayerIdNames[$this->actingPlayerId] .
                   ' added a reserve die: ' . $this->params['die']['recipe'];
        return $message;
    }

    protected function friendly_message_decline_reserve() {
        $message = $this->outputPlayerIdNames[$this->actingPlayerId] .
                   ' chose not to add a reserve die';
        return $message;
    }

    protected function friendly_message_add_auxiliary() {
        // If the round is later than the one in which this action
        // log entry was recorded, or we're no longer in auxiliary selection
        // state, report the action
        if (($this->outputRoundNumber != $this->params['roundNumber']) ||
            ($this->outputGameState != BMGameState::CHOOSE_AUXILIARY_DICE)) {
            $message = $this->outputPlayerIdNames[$this->actingPlayerId] .
                       ' chose to use auxiliary die ' . $this->params['die']['recipe'] .
                       ' in this game';
        } else {
            // Otherwise, return nothing - the fact that this player has made a choice
            // leaks information, so suppress the log entry entirely for now.
            $message = '';
        }
        return $message;
    }

    protected function friendly_message_decline_auxiliary() {
        $message = $this->outputPlayerIdNames[$this->actingPlayerId] .
                   ' chose not to use auxiliary dice in this game: ' .
                   'neither player will get an auxiliary die';
        return $message;
    }

    protected function friendly_message_determine_initiative() {

        // Summary first: who won initiative
        $message = $this->outputPlayerIdNames[$this->params['initiativeWinnerId']] .
                   ' won initiative for round ' . $this->params['roundNumber'] . '.';

        // Now report all the initial die rolls without commentary
        $message .= ' Initial die values: ';
        $dieRollStrs = array();
        $slowButtonPlayers = array();
        $slowDice = array();
        foreach ($this->params['playerData'] as $playerId => $playerData) {
            $dieVals = array();
            $slowDice[$playerId] = array();
            foreach ($playerData['initiativeDice'] as $initDie) {
                $dieVals[] = $initDie['recipeStatus'];
                if (!$initDie['included']) {
                    $slowDice[$playerId][] = $initDie['recipe'];
                }
            }
            $dieRollStrs[] = $this->outputPlayerIdNames[$playerId] . ' rolled [' .
                             implode(', ', $dieVals) . ']';
            if ($playerData['slowButton']) {
                $slowButtonPlayers[] = $playerId;
            }
        }
        $message .= implode(', ', $dieRollStrs) . '.';

        // Now report on slow buttons and dice: assume a 2-player game for now
        if (count($slowButtonPlayers) == 2) {
            $message .= ' Both buttons have the "slow" button special, and cannot win initiative normally.';
        } elseif (count($slowButtonPlayers) == 1) {
            $message .= ' ' . $this->outputPlayerIdNames[$slowButtonPlayers[0]] .
                        '\'s button has the "slow" button special, and cannot win initiative normally.';
        } else {
            foreach ($slowDice as $playerId => $playerSlowDice) {
                if (count($playerSlowDice) > 0) {
                    $message .= ' ' . $this->outputPlayerIdNames[$playerId] .
                                ' has dice which are not counted for initiative due to die skills: [' .
                                implode(', ', $playerSlowDice) . '].';
                }
            }
        }

        // Last, if initiative was resolved by coin flip, say that.
        if (array_key_exists('tiedPlayerIds', $this->params)) {
            $message .= ' Initiative was determined by a coin flip.';
        }

        return $message;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            switch ($property) {
                default:
                    return $this->$property;
            }
        }
    }

    public function __set($property, $value) {
        switch ($property) {
            default:
                $this->$property = $value;
        }
    }
}
