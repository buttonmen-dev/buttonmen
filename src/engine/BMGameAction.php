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
            $message = $this->outputPlayerIdNames[$this->actingPlayerId] . ' passed';
        } elseif ($attackType == 'Surrender') {
            $message = $this->outputPlayerIdNames[$this->actingPlayerId] . ' surrendered';
        } else {
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
            if (count($preAttackAttackers) > 0) {
                $message .= ' using [' . implode(",", $preAttackAttackers) . ']';
            }
            if (count($preAttackDefenders) > 0) {
                $message .= ' against [' . implode(",", $preAttackDefenders) . ']';
            }

            // Report what happened to each defending die
            foreach ($preAttackDice['defender'] as $idx => $defenderInfo) {
                $postInfo = $postAttackDice['defender'][$idx];
                $postEvents = array();

                if ($defenderRerollsEarly) {
                    if ($defenderInfo['doesReroll']) {
                        $postEvents[] = 'rerolled ' . $defenderInfo['value'] . ' => ' .  $postInfo['value'];
                    } else {
                        $postEvents[] = 'does not reroll';
                    }
                }

                if ($postInfo['captured']) {
                    $postEvents[] = 'was captured';
                } else {
                    $postEvents[] = 'was not captured';
                }
                if ($defenderInfo['recipe'] != $postInfo['recipe']) {
                    $postEvents[] = 'recipe changed from ' . $defenderInfo['recipe'] . ' to ' . $postInfo['recipe'];
                }
                $message .= '; Defender ' . $defenderInfo['recipe'] . ' ' . implode(', ', $postEvents);
            }

            // Report what happened to each attacking die
            foreach ($preAttackDice['attacker'] as $idx => $attackerInfo) {
                $postInfo = $postAttackDice['attacker'][$idx];
                $postEvents = array();
                if ($attackerInfo['max'] != $postInfo['max']) {
                    $postEvents[] = 'changed size from ' . $attackerInfo['max'] . ' to ' . $postInfo['max'] . ' sides';
                }
                if ($attackerInfo['doesReroll']) {
                    $postEvents[] = 'rerolled ' . $attackerInfo['value'] . ' => ' . $postInfo['value'];
                } else {
                    $postEvents[] = 'does not reroll';
                }
                if ($attackerInfo['recipe'] != $postInfo['recipe']) {
                    $postEvents[] = 'recipe changed from ' . $attackerInfo['recipe'] . ' to ' . $postInfo['recipe'];
                }
                if (count($postEvents) > 0) {
                    $message .= '; Attacker ' . $attackerInfo['recipe'] . ' ' . implode(', ', $postEvents);
                }
            }
        }
        return $message;
    }

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

    protected function friendly_message_choose_option() {
        $message = $this->outputPlayerIdNames[$this->actingPlayerId] . ' set option values';

        // If the round is later than the one in which this action
        // log entry was recorded, or we're no longer in option selection
        // state, report the values which were chosen as well
        if (($this->outputRoundNumber != $this->params['roundNumber']) ||
            ($this->outputGameState != BMGameState::SPECIFY_DICE)) {
            $optionStrs = array();
            foreach ($this->params['optionValues'] as $optionValue) {
                $optionStrs[] = $optionValue;
            }
            $message .= ': ' . implode(", ", $optionStrs);
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
