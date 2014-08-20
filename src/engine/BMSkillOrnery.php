<?php
/**
 * BMSkillOrnery: Code specific to the ornery die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the ornery die skill
 */
class BMSkillOrnery extends BMSkill {
    public static $hooked_methods = array('perform_end_of_turn_die_actions');

    public static function perform_end_of_turn_die_actions(&$args) {
        if (!is_array($args) ||
            !array_key_exists('die', $args) ||
            !array_key_exists('attackType', $args)) {
            return;
        }

        if ('Pass' == $args['attackType']) {
            return;
        }

        $die = $args['die'];

        if ($die->hasAttacked) {
            return;
        }

        if ($die->unavailable) {
            return;
        }

        $die->roll(FALSE);
    }

    protected static function get_description() {
        return 'At the end of every turn, if the player does not pass, ' .
               'then the attacker\'s ornery dice reroll, even if they ' .
               'did not attack in that turn. Ornery dice do not reroll a ' .
               'second time if they attacked.';
    }

    protected static function get_interaction_descriptions() {
        return array();
    }
}
