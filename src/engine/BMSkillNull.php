<?php

class BMSkillNull extends BMSkill {
    public static $hooked_methods = array("score_value", "capture");

    public static function score_value($args) {
        assert(array_key_exists('mult', $args));

        $args['mult'] = 0;
    }

    public static function capture($args) {
        assert(array_key_exists('victims', $args));

        foreach($args['victims'] as $victim) {
            $victim->add_skill('Null');
        }
    }
}

?>
