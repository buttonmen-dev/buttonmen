<?php
/**
 * BMSkillWeak: Code specific to the weak die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the weak die skill
 */
class BMSkillWeak extends BMSkill {
    public static $hooked_methods = array('pre_roll');

    public static function pre_roll($args) {
        $die = $args['die'];

        // don't trigger skill when initially rolling the die into the button
        if (!($die->ownerObject instanceof BMGame)) {
            return;
        }

        // don't trigger skill when rolling the die into the beginning of the round
        if (!isset($die->value) &&
            ($die->ownerObject->turnNumberInRound <= 1)) {
            return;
        }

        $die->shrink();
    }

    protected static function get_description() {
        return 'When a Weak Die rerolls for any reason, ' .
               'it first shrinks from its current size to the ' .
               'next larger size in the list of "standard" ' .
               'die sizes (1, 2, 4, 6, 8, 10, 12, 16, 20, 30).';
    }

    protected static function get_interaction_descriptions() {
        return array(
            'Berserk' => 'Dice with both Berserk and Weak skills will first ' .
                         'halve in size, and then shrink',
            'Fire' => 'Dice with both Fire and Weak skills do not shrink ' .
                      'when firing, only when actually rolling',
        );
    }

    public static function prevents_win_determination() {
        return TRUE;
    }
}
