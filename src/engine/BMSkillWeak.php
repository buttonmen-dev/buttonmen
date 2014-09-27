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
//        $die = $args['die'];


    }

    protected static function get_description() {
        return '';
//        return 'These dice cannot participate in Skill Attacks; ' .
//               'instead they can make a Berserk Attack. These work exactly ' .
//               'like Speed Attacks - one Berserk Die can capture any number ' .
//               'of dice which add up exactly to its value. Once a Berserk ' .
//               'Die performs a Berserk Attack, it is replaced with a ' .
//               'non-berserk die with half the number of sides it previously ' .
//               'had, rounding up. It also loses any Swing/Mood Swing/Mad Swing ' .
//               'characteristics it may have had.';
    }

    protected static function get_interaction_descriptions() {
        return array(
//            'Speed' => 'Dice with both Berserk and Speed skills may ' .
//                       'choose to make either kind of attack',
        );
    }

    public static function prevents_win_determination() {
        return TRUE;
    }
}
