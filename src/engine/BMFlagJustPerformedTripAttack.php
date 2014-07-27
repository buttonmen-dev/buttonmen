<?php
/**
 * BMFlagJustPerformedTripAttack: Used to signal that a die has just trip attacked
 *
 * @author: james
 */

/**
 * This class is a flag that signals that a die has just performed a
 * trip attack. It stores the post-attack die value, in case the attacking die
 * changes again afterwards.
 */
class BMFlagJustPerformedTripAttack extends BMFlag {

    // properties
    protected $postAttackValue;

    public function value() {
        return $this->postAttackValue;
    }

    public function __construct($dieValue) {
        $this->postAttackValue = NULL;
        if (isset($dieValue)) {
            $this->postAttackValue = (int)$dieValue;
        }
    }

    public function __toString() {
        $string = $this->type();
        if (is_integer($this->postAttackValue)) {
            $string .= '__' . $this->postAttackValue;
        }

        return $string;
    }
}
