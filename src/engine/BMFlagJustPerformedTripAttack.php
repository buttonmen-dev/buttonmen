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
class BMFlagJustPerformedTripAttack extends BMFlagJustPerformedTypeOfAttack {
    /**
     * Constructor
     *
     * @param int $dieValue
     */
    public function __construct($dieValue) {
        parent::__construct();
        if (isset($dieValue)) {
            $this->postAttackInfo = (int)$dieValue;
        }
    }
}
