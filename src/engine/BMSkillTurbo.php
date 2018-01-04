<?php
/**
 * BMSkillTurbo: Code specific to the turbo die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the turbo die skill.
 * Note that this supports both turbo swing and turbo option.
 */
class BMSkillTurbo extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('roll');

    /**
     * Hooked method applied while rolling a die
     *
     * @param array $args
     * @return bool
     */
    public static function roll(&$args) {
        if (!($args['die'] instanceof BMDie)) {
            return FALSE;
        }

        $die = $args['die'];

        if (isset($die->value)) {
            if ($die->has_flag('JustPerformedTripAttack')) {
                return TRUE;
            }

            if ($die->has_flag('IsAttacker')) {
                // don't roll Turbo dice that have just attacked, because
                // they must be replaced with a new Turbo die before being rolled
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Determine if a skill abbreviation should appear before the die recipe
     *
     * @return bool
     */
    public static function do_print_skill_preceding() {
        return FALSE;
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'After your starting roll, you may change the size of your own ' .
               'Turbo Swing or Option die every time you roll it as part of your attack. ' .
               'Decide on a size first that is valid for the Swing or Option '.
               'type, then roll the new die as usual.';
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
            'Berserk' => 'Dice with both Berserk and Turbo making a berserk attack will first halve in size ' .
                         'and then change to the size specified by the Turbo skill',
            'Radioactive' => 'Dice with the Turbo skill lose Turbo when they decay due to Radioactive',
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
