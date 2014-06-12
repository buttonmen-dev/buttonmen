<?php

class BMSkillSpeed extends BMSkill {
    public static $hooked_methods = array('attack_list');

    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];
        $attackTypeArray['Speed'] = 'Speed';
    }

    protected static function get_description() {
        return 'These dice can also make Speed Attacks, which are ' .
               'the equivalent of inverted Skill Attacks. In a Speed Attack, ' .
               'one Speed Die can capture any number of dice which add up ' .
               'exactly to its value.';
    }

    protected static function get_interaction_descriptions() {
        return array(
            'Berserk' => 'Dice with both Berserk and Speed skills may choose to make either kind of attack',
        );
    }
}
