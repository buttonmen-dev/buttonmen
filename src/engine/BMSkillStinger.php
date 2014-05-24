<?php

class BMSkillStinger extends BMSkill {
    public static $hooked_methods = array('initiative_value',
                                          'attack_values');

    public static function initiative_value(&$args) {
        if (!is_array($args)) {
            return;
        }

        // stinger dice don't contribute to initiative
        $args['initiativeValue'] = 0;
    }

    public static function attack_values($args) {
        if (!is_array($args) ||
            !array_key_exists('attackType', $args) ||
            !array_key_exists('attackValues', $args)) {
            return;
        }

        if ('Skill' != $args['attackType']) {
            return;
        }

        $args['attackValues'] = range(1, max($args['attackValues']));
    }

}
