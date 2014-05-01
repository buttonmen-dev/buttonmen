<?php

class BMSkillTrip extends BMSkill {
    public static $hooked_methods = array('attack_list',
                                          'initiative_value',
                                          'capture');

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

    public static function capture(&$args) {
        if ($args['type'] != 'Trip') {
            return;
        }

        assert(1 == count($args['attackers']));
        assert(1 == count($args['defenders']));

        $attacker = &$args['attackers'][0];
        $attacker->roll(TRUE);

        $defender = &$args['defenders'][0];
        $defender->roll(TRUE);

        $defender->captured = ($defender->value <= $attacker->value);
    }
}
