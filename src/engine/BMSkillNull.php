<?php

class BMSkillNull extends BMSkill {
    public static $hooked_methods = array("score_value", "capture");

    public static function score_value($args) {
        assert(array_key_exists('mult', $args));

        $args['mult'] = 0;
    }

    public static function capture($args) {
        assert(array_key_exists('defenders', $args));

        foreach ($args['defenders'] as $defender) {
            $defender->add_skill('Null');
        }
    }

    protected static function get_description() {
        return 'When a Null Die participates in any attack, the ' .
               'dice that are captured are worth zero points. Null Dice ' .
               'themselves are worth zero points.';
    }

    protected static function get_interaction_descriptions() {
        return array(
            'Poison' => 'Dice with both Null and Poison skills are Null',
            'Value' => 'Dice with both Null and Value skills are Null',
        );
    }

    public static function prevents_win_determination() {
        return TRUE;
    }
}
