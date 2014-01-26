<?php

class BMSkillStealth extends BMSkill {
    public static $hooked_methods = array('attack_list');

        public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];
        foreach (array_keys($attackTypeArray) as $attackType) {
            if ('Skill' == $attackType) {
                if (1 == $args['nAttDice']) {
                    unset($attackTypeArray[$attackType]);
                }
            } else {
                unset($attackTypeArray[$attackType]);
            }
        }
    }
}
