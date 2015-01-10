<?php
/**
 * BMSkillAuxiliary: Code specific to the auxiliary die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the auxiliary die skill
 */
class BMSkillAuxiliary extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('does_skip_swing_request');

    public static function does_skip_swing_request() {
        return 'does_skip_swing_request';
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'These are optional extra dice. Before each game, ' .
               'both players decide whether or not to play with their ' .
               'Auxiliary Dice. Only if both players choose to have them ' .
               'will they be in play.';
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
        return array();
    }
}
