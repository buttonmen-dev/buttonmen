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
