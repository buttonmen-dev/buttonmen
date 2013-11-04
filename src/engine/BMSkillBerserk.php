<?php

class BMSkillBerserk extends BMSkill {
    public static $name = 'Berserk';
    public static $type = 'Berserk';
    public static $abbrev = 'B';

    public static $hooked_methods = array('attack_list');

    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];

        if (array_key_exists('Skill', $attackTypeArray)) {
            unset($attackTypeArray['Skill']);
        }

        $attackTypeArray['Berserk'] = 'Berserk';
    }

    // what else? something to do with changing the originial die to half the size
}

?>
