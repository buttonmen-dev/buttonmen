<?php

class BMSkillKonstant extends BMSkill {
    public static $hooked_methods = array('attack_list',
                                          'add_skill',
                                          'attack_values',
                                          'hit_table');

    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];

        if (array_key_exists('Power', $attackTypeArray)) {
            unset($attackTypeArray['Power']);
        }
    }

    public static function add_skill($args) {
        if (!array_key_exists('die', $args)) {
            return;
        }

        $args['die']->doesReroll = FALSE;
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

        $initialAttackValues = $args['attackValues'];
        $negativeAttackValues = array_map(
            function ($val) {
                return (-$val);
            },
            $initialAttackValues
        );

        $args['attackValues'] = array_values(
                                    array_unique(
                                        array_merge($initialAttackValues,
                                                    $negativeAttackValues)));
    }

    public static function hit_table($args) {
        // validate arguments
        assert(array_key_exists('hits', $args) &&
               array_key_exists('dieLetter', $args));

        // remove hits that are the result of single-die skill attacks by
        // konstant dice

        // for each possible hit value
        foreach ($args['hits'] as $val => &$comboArray) {
            // check whether the hit combinations include the required
            // single-die skill attack
            if (array_key_exists($args['dieLetter'], $comboArray)) {
                if (1 == count($comboArray)) {
                    // the hit value can be obtained only via the
                    // single-die skill attack, so unset the hit itself
                    unset($args['hits'][$val]);
                } else {
                    // unset the single-die skill attack option, but
                    // leave the hit, since some other combination can
                    // still achieve it
                    unset($comboArray[$args['dieLetter']]);
                }
            }
        }
    }
}
