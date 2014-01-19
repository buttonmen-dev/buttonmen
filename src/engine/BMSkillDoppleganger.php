<?php

class BMSkillDoppleganger extends BMSkill {
    public static $hooked_methods = array('capture');

    public static function capture(&$args) {
        if (!self::are_dice_in_attack_valid($args)) {
            return;
        }

        if (!('Power' == $args['type'])) {
            return;
        }

        $attBackup = clone $args['caller'];
        $att = clone $args['defenders'][0];
        $att->captured = FALSE;
        $att->ownerObject = $attBackup->ownerObject;
        $att->playerIdx = $attBackup->playerIdx;
        $att->originalPlayerIdx = $attBackup->originalPlayerIdx;
        $att->hasAttacked = TRUE;
        $att->roll(TRUE);

        return $att;
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
