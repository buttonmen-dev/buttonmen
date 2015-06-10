<?php
/**
 * BMFlagJustPerformedTypeOfAttack: Used to signal that a die has just performed a type of attack
 *
 * @author: james
 */

/**
 * This class is a flag that signals that a die has just performed a certain
 * type of attack. It stores the post-attack die value, in case the attacking die
 * changes again afterwards.
 */
class BMFlagJustPerformedTypeOfAttack extends BMFlag {
    /**
     * Post-trip-attack die value, stored in flag
     *
     * @var int
     */
    protected $postAttackValue;

    /**
     * Value stored in flag
     *
     * @return mixed
     */
    public function value() {
        return $this->postAttackValue;
    }

    /**
     * Constructor
     *
     * @param int $dieValue
     */
    public function __construct($dieValue) {
        $this->postAttackValue = NULL;
        if (isset($dieValue)) {
            $this->postAttackValue = (int)$dieValue;
        }
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString() {
        $string = $this->type();
        if (is_integer($this->postAttackValue)) {
            $string .= '__' . $this->postAttackValue;
        }

        return $string;
    }
}
