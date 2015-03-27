<?php
/**
 * BMSkillTimeAndSpace: Code specific to the time and space die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the time and space die skill
 */
class BMSkillTimeAndSpace extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array();

//    /**
//     * Hooked method applied when checking if a die should request a swing value
//     *
//     * @return string
//     */
//    public static function does_skip_swing_request() {
//        return 'does_skip_swing_request';
//    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'If a Time and Space Die is rerolled after it participates in ' .
               'an attack and rolls odd, then the player will take another ' .
               'turn. If multiple Time and Space dice are rerolled and show odd, ' .
               'only one extra turn is given per reroll.';
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
