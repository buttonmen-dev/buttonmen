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

    protected function get_description() {
        return 'When a Doppelganger Die performs a Power Attack on ' .
               'another die, the Doppelganger Die becomes an exact copy of ' .
               'the die it captured. The newly copied die is then rerolled, ' .
               'and has all the abilities of the captured die. For instance, ' .
               'if a Doppelganger Die copies a Turbo Swing Die, then it may ' .
               'change its size as per the rules of Turbo Swing Dice. Usually ' .
               'a Doppelganger Die will lose its Doppelganger ability when ' .
               'it copies another die, unless that die is itself a Doppelganger ' .
               'Die.';
    }

    protected function get_interaction_descriptions() {
        return array();
    }
}
