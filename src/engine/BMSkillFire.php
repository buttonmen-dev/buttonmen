<?php

class BMSkillFire extends BMSkill {
    public static $hooked_methods = array('attack_list',
                                          'assist_values');

    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];

        if (array_key_exists('Power', $attackTypeArray)) {
            unset($attackTypeArray['Power']);
        }
    }

    public static function assist_values(&$args) {
        if (!is_array($args)) {
            return;
        }

        if (!array_key_exists('attackType', $args) ||
            !array_key_exists('assistingDie', $args) ||
            !array_key_exists('possibleAssistValues', $args)) {
            return;
        }

        $die = $args['assistingDie'];

        if (!$args['assistingDie']->has_skill('Fire')) {
            return;
        }

        $isValidAttackType = ('Power' == $args['attackType']) ||
                             ('Skill' == $args['attackType']);
        if (!$isValidAttackType) {
            return;
        }

        if ($die->value <= $die->min) {
            return;
        }

        $args['possibleAssistValues'] = range(1, $die->value - $die->min);
    }
}
