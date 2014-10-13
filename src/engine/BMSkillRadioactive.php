<?php
/**
 * BMSkillRadioactive: Code specific to the radioactive die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the radioactive die skill
 */
class BMSkillRadioactive extends BMSkill {
    public static $hooked_methods = array('capture', 'be_captured');

    public static function capture($args) {

    }

    public static function be_captured($args) {

    }

    public static function incompatible_attack_types($args = NULL) {
        return array();
    }

    protected static function get_description() {
        return '';
    }

    protected static function get_interaction_descriptions() {
        return array(

        );
    }

    public static function prevents_win_determination() {
        return TRUE;
    }
}
