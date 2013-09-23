<?php

class BMSkillPoison extends BMSkill {

    public static $name = "Poison";
    public static $abbrev = "p";

    public static $hooked_methods = array("scoreValue");

    public static function scoreValue($args) {
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

?>
