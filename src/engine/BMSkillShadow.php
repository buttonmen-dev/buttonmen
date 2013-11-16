<?php

class BMSkillShadow extends BMSkill {
    public static $name = 'Shadow';
    public static $type = 'Shadow';
    public static $abbrev = 's';

    public static $hooked_methods = array('attack_list');

    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        if (count($args) < 1) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];

        if (array_key_exists('Power', $attackTypeArray)) {
            unset($attackTypeArray['Power']);
        }

        $attackTypeArray['Shadow'] = 'Shadow';
    }
}

?>
