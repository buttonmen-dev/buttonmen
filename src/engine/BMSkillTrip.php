<?php

class BMSkillTrip extends BMSkill {
    public static $hooked_methods = array('attack_list', 'initiative_value');

    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];
        $attackTypeArray['Trip'] = 'Trip';
    }

    public static function initiative_value(&$args) {
        if (!is_array($args)) {
            return;
        }

        // trip dice don't contribute to initiative
        $args['initiativeValue'] = 0;
    }
}

?>
