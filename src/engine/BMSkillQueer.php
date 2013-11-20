<?php

class BMSkillQueer extends BMSkill {
    public static $hooked_methods = array('attack_list');

    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];
        $value = $args['value'];

        if (!is_int($value)) {
            return;
        }

        if (0 == $value % 2) {
            if (array_key_exists('Shadow', $attackTypeArray)) {
                unset($attackTypeArray['Shadow']);
            }

            $attackTypeArray['Power'] = 'Power';
        } else {
            if (array_key_exists('Power', $attackTypeArray)) {
                unset($attackTypeArray['Power']);
            }

            $attackTypeArray['Shadow'] = 'Shadow';
        }
    }
}

?>
