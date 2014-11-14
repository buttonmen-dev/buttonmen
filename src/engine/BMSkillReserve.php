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
    public static $hooked_methods = array('does_skip_swing_request');

    public static function does_skip_swing_request() {
        return 'does_skip_swing_request';
    }

    protected static function get_description() {
        return 'These are extra dice which may be brought into play ' .
               'part way through a game. Each time you lose a round you may ' .
               'choose another of your Reserve Dice; it will then be in ' .
               'play for all future rounds.';
    }

    protected static function get_interaction_descriptions() {
        return array();
    }
}
