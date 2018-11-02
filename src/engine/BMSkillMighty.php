<?php
/**
 * BMSkillMighty: Code specific to the mighty die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the mighty die skill
 */
class BMSkillMighty extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('pre_roll');

    /**
     * Hooked method applied before die roll
     *
     * @param array $args Array of arguments to hooked method
     * @return bool
     */
    public static function pre_roll($args) {
        $die = $args['die'];

        // don't trigger skill when initially rolling the die into the button
        if (!($die->ownerObject instanceof BMGame)) {
            return;
        }

        // don't trigger skill when rolling the die into the beginning of the round
        if (is_null($die->value) &&
            ($die->ownerObject->turnNumberInRound <= 1)) {
            return;
        }

        // don't trigger skill when the die has just performed a trip attack
        if ($die->has_flag('JustPerformedTripAttack')) {
            return;
        }

        // don't trigger skill if the die is a rage replacement die
        if ($die->has_flag('IsRageTargetReplacement')) {
            return;
        }

        // don't trigger skill if the die has already left play
        if ($die->outOfPlay) {
            return;
        }

        if (!$die->doesReroll) {
            return;
        }

        $die->grow();
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'When a Mighty Die rerolls for any reason, ' .
               'it first grows from its current size to the ' .
               'next larger size in the list of "standard" ' .
               'die sizes (1, 2, 4, 6, 8, 10, 12, 16, 20, 30).';
    }

    /**
     * Descriptions of interactions between this skill and other skills
     *
     * An array, indexed by other skill name, whose values are descriptions of
     * interactions between the relevant skills
     *
     * @return array
     */
    protected static function get_interaction_descriptions() {
        return array(
            'Berserk' => 'Dice with both Berserk and Mighty skills will first ' .
                         'halve in size, and then grow',
            'Chance' => 'A reroll of a Chance Mighty die due to the Chance skill triggers the Mighty skill',
            'Fire' => 'Dice with both Fire and Mighty skills do not grow ' .
                      'when firing, only when actually rolling',
        );
    }

    /**
     * Does this skill prevent the determination of whether a player can win?
     *
     * @return bool
     */
    public static function prevents_win_determination() {
        return TRUE;
    }
}
