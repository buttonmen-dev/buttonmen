<?php

class BMSkillDoppelganger extends BMSkillMorphing {
    public static $hooked_methods = array('capture');

    public static function capture(&$args) {
        if (!self::are_dice_in_attack_valid($args)) {
            return;
        }

        if (!('Power' == $args['type'])) {
            return;
        }

        $att = self::create_morphing_clone_target($args['caller'], $args['defenders'][0]);
        $att->roll(TRUE);

        return $att;
    }
}
