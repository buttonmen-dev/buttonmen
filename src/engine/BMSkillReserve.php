<?php
/**
 * BMSkillReserve: Code specific to the reserve die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the reserve die skill
 */
class BMSkillReserve extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('does_skip_swing_request');

    /**
     * Hooked method applied when checking if a die should request a swing value
     *
     * @return string
     */
    public static function does_skip_swing_request() {
        return 'does_skip_swing_request';
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'These are extra dice which may be brought into play ' .
               'part way through a game. Each time you lose a round you may ' .
               'choose another of your Reserve Dice; it will then be in ' .
               'play for all future rounds.';
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
