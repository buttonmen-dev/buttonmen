<?php
/**
 * BMSkillMighty: Code specific to the mighty die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the mighty die skill
 */
class BMSkillMighty extends BMSkill {
    public static $hooked_methods = array('pre_roll');

    public static function pre_roll($args) {
        $die = $args['die'];
        if (isset($die->value)) {
            $die->grow();
        }
    }

    protected static function get_description() {
        return 'When a Mighty Die rerolls for any reason, ' .
               'it first grows from its current size to the ' .
               'next larger size in the list of "standard" ' .
               'die sizes (1, 2, 4, 6, 8, 10, 12, 16, 20, 30).';
    }

    protected static function get_interaction_descriptions() {
        return array(
            'Berserk' => 'Dice with both Berserk and Mighty skills will first ' .
                         'halve in size, and then grow',
            'Fire' => 'Dice with both Fire and Mighty skills do not grow ' .
                      'when firing, only when actually rolling',
        );
    }

    public static function prevents_win_determination() {
        return TRUE;
    }
}
