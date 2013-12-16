<?php

class BMSkillKonstant extends BMSkill {
    public static $hooked_methods = array('attack_list', 'make_play_die');

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
}

?>
