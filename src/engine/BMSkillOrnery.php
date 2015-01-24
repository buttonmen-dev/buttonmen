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
        if (!is_array($args)) {
            return;
        }

        if (!array_key_exists('die', $args)) {
            return;
        }

        if (!($args['die'] instanceof BMDie)) {
            return;
        }

        if (!array_key_exists('attackType', $args)) {
            return;
        }

        if ('Pass' == $args['attackType']) {
            return;
        }

        $die = $args['die'];

        if ($die->has_flag('IsAttacker')) {
            return;
        }

        if ($die->has_skill('Warrior')) {
            return;
        }

        $die->roll(FALSE);
        $die->add_flag('HasJustRerolledOrnery');
        return TRUE;
    }

    protected static function get_description() {
        return 'Ornery dice reroll every time the player makes any attack - ' .
               'whether the Ornery dice participated in it or not. The only time ' .
               'they don\'t reroll is if the player passes, making no attack whatsoever.';
    }

    protected static function get_interaction_descriptions() {
        return array(
            'Mad' => 'Dice with both Ornery and Mad Swing have their sizes randomized during ornery rerolls',
            'Mood' => 'Dice with both Ornery and Mood Swing have their sizes randomized during ornery rerolls',
        );
    }
}
