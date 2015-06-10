<?php
/**
 * BMFlagJustPerformedBerserkAttack: Used to signal that a die has just berserk attacked
 *
 * @author: james
 */

/**
 * This class is a flag that signals that a die has just performed a
 * berserk attack. It stores the post-attack die recipe, in case the attacking die
 * changes again afterwards.
 */
class BMFlagJustPerformedBerserkAttack extends BMFlagJustPerformedTypeOfAttack {
    /**
     * Constructor
     *
     * @param string $dieRecipe
     */
    public function __construct($dieRecipe) {
        parent::__construct();
        if (isset($dieRecipe)) {
            $this->postAttackInfo = $dieRecipe;
        }
    }
}
