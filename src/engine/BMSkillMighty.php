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
    public static $hooked_methods = array('pre_roll');

    public static function pre_roll($args) {
        $die = $args['die'];
        $die->grow();
    }

    protected static function get_description() {
        return 'When a Mighty Die rerolls for any reason, it becomes larger. ' .
               'A 1 sided die grows to a 2 sided die, then up to a ' .
               '4, 6, 8, 10, 12, 16, 20, and finally a 30 sided die.';
    }

    protected static function get_interaction_descriptions() {
        return array();
    }

    public static function prevents_win_determination() {
        return TRUE;
    }
}
