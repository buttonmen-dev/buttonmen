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

    protected static function get_description() {
        return '';
    }

    protected static function get_interaction_descriptions() {
        return array(

        );
    }
}
