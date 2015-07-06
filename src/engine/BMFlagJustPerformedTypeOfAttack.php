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
     * Post-attack information, stored in flag
     *
     * @var int
     */
    protected $postAttackInfo;

    /**
     * Value stored in flag
     *
     * @return mixed
     */
    public function value() {
        return $this->postAttackInfo;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->postAttackInfo = NULL;
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString() {
        $string = $this->type();
        $string .= '__' . $this->postAttackInfo;

        return $string;
    }
}
