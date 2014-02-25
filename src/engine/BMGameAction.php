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
        $this->gameState = $gameState;
        $this->actionType = $actionType;
        $this->actingPlayerId = $actingPlayerId;
        $this->params = $params;
    }

    public function friendly_message($playerIdNames) {
        if (is_array($this->params)) {
            $funcName = 'friendly_message_' . $this->actionType;
            if (method_exists($this, $funcName)) {
                $result = $this->$funcName($playerIdNames);
            } else {
                $result = "";
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

    protected function friendly_message_end_draw($playerIdNames) {
        $message = 'Round ' . $this->params['roundNumber'] .
                   ' ended in a draw (' .
                   $this->params['roundScoreArray'][0] . ' vs. ' .
                   $this->params['roundScoreArray'][1] . ')';
        return $message;
    }

    protected function friendly_message_end_winner($playerIdNames) {
        $message = 'End of round: ' . $playerIdNames[$this->actingPlayerId] .
                   ' won round ' . $this->params['roundNumber'] . ' (' .
                   max($this->params['roundScoreArray']) . ' vs. ' .
                   min($this->params['roundScoreArray']) . ')';
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
