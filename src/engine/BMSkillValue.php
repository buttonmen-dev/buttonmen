<?php

class BMSkillValue extends BMSkill {

    public static $name = "Value";
    public static $abbrev = "v";

    public static $hooked_methods = array("scoreValue");

    public static function scoreValue($args) {
        assert(array_key_exists('scoreValue', $args));
        assert(array_key_exists('value', $args));

        if (is_null($args['value'])) {
            $args['scoreValue'] = NULL;
        } else {
            $args['scoreValue'] = $args['value'];
        }
    }
}

?>
