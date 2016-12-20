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
    public static $hooked_methods = array();

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
        return 'After your starting roll, you may change the size of a ' .
               'Turbo Swing or Option die every time you roll it. ' .
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
            'Boom' => 'If a Turbo Die is rerolled because it is the target ' .
                      'of a Boom attack, then the size cannot be changed.',
            'Trip' => 'If a Turbo Die is rerolled because it is the target ' .
                      'of a Trip attack, then the size cannot be changed.',
        );
    }
}
