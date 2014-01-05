<?php

class BMSkillValue extends BMSkill {
    public static $hooked_methods = array("score_value", "capture");

    public static function score_value($args) {
        assert(array_key_exists('scoreValue', $args));
        assert(array_key_exists('value', $args));

        if (is_null($args['value'])) {
            $args['scoreValue'] = NULL;
        } else {
            $args['scoreValue'] = $args['value'];
        }
    }

    public static function capture($args) {
        assert(array_key_exists('defenders', $args));

        foreach ($args['defenders'] as $defender) {
            $defender->add_skill('Value');
        }
    }
}
