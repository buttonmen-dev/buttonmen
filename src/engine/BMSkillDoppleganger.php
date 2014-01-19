<?php

class BMSkillDoppleganger extends BMSkillMorphing {
    public static $hooked_methods = array('capture');

    public static function capture(&$args) {
        if (!self::are_dice_in_attack_valid($args)) {
            return;
        }

        if (!('Power' == $args['type'])) {
            return;
        }

        $attBackup = clone $args['caller'];
        $att = self::create_morphing_clone_target($args['defenders'][0]);
        $att->ownerObject = $attBackup->ownerObject;
        $att->playerIdx = $attBackup->playerIdx;
        $att->originalPlayerIdx = $attBackup->originalPlayerIdx;
        $att->hasAttacked = TRUE;
        $att->roll(TRUE);

        return $att;
    }
}
