<?php

class BMSkillBerserk extends BMSkill {
    public static $hooked_methods = array('attack_list', 'capture');

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

    public static function capture(&$args) {
        if (!is_array($args)) {
            return;
        }

        if (!array_key_exists('type', $args)) {
            return;
        }

        if ('Berserk' != $args['type']) {
            return;
        }

        if (!array_key_exists('attackers', $args)) {
            return;
        }

        assert(1 == count($args['attackers']));

        $attacker = $args['attackers'][0];

        // james: which other skills need to be lost after a Berserk attack?
        $attacker->remove_skill('Berserk');

        // force removal of swing, twin die, and option status
        $splitDieArray = $attacker->split();
        $newAttacker = $splitDieArray[0];
        $args['attackers'][0] = $newAttacker;
    }
}

?>
