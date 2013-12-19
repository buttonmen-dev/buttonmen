<?php

class BMSkillShadow extends BMSkill {
    public static $hooked_methods = array('attack_list');

    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];

        foreach (BMSkillShadow::incompatible_attack_types() as $attackType) {
            if (array_key_exists($attackType, $attackTypeArray)) {
                unset($attackTypeArray[$attackType]);
            }
        }

        $attackTypeArray['Shadow'] = 'Shadow';
    }
    
    public static function incompatible_attack_types($args = NULL) {
        return array('Power');
    }
}

?>
