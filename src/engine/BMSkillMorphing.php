<?php

class BMSkillMorphing extends BMSkill {
    public static $hooked_methods = array('capture');

    public static function capture($args) {
        if (!BMSkillMorphing::are_dice_in_attack_valid($args)) {
            return;
        }

        $attackers = $args['attackers'];
        $defenders = $args['defenders'];

        foreach ($attackers as &$att) {
            $att->max = $defenders[0]->max;
        }
    }

    protected static function are_dice_in_attack_valid($args) {
        if (!is_array($args['attackers']) ||
            (0 == count($args['attackers'])) ||
            !is_array($args['defenders']) ||
            (0 == count($args['defenders']))) {
            return FALSE;
        }

        return TRUE;
    }
}
