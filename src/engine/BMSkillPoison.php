<?php

class BMSkillPoison extends BMSkill {
    public static $hooked_methods = array("score_value");

    public static function score_value($args) {
        assert(array_key_exists('mult', $args));
        assert(array_key_exists('div', $args));
        assert(array_key_exists('captured', $args));
        $args['mult'] = -$args['mult'];

        if ($args['captured']) {
            $args['div'] = 2;
        } else {
            $args['div'] = 1;
        }
    }
}
