<?php

class BMSkillKonstant extends BMSkill {
    public static $hooked_methods = array('attack_list',
                                          'make_play_die',
                                          'attack_values');

    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];

        if (array_key_exists('Power', $attackTypeArray)) {
            unset($attackTypeArray['Power']);
        }
    }

    public static function make_play_die($args) {
        $die = $args['die'];
        $die->min = $die->value;
        $die->max = $die->value;
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
        $negativeAttackValues = array_map(function ($val) {return (-$val);},
                                          $initialAttackValues);

        $args['attackValues'] = array_values(
                                    array_unique(
                                        array_merge($initialAttackValues,
                                                    $negativeAttackValues)));
    }
}

?>
