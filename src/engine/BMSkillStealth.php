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

    protected static function get_description() {
        return 'These dice cannot perform any type of attack other than Multi-die Skill Attacks, meaning two or more ' .
               'dice participating in a Skill Attack. In addition, Stealth Dice cannot be captured by any attack ' .
               'other than a Multi-die Skill Attack.';
    }

    protected static function get_interaction_descriptions() {
        return array();
    }
}
