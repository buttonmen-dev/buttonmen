<?php
/**
 * BMSkillBerserk: Code specific to the berserk die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the berserk die skill
 */
class BMSkillBerserk extends BMSkill {
    public static $hooked_methods = array('attack_list', 'capture');

    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];

        foreach (BMSkillBerserk::incompatible_attack_types() as $attackType) {
            if (array_key_exists($attackType, $attackTypeArray)) {
                unset($attackTypeArray[$attackType]);
            }
        }

        $attackTypeArray['Berserk'] = 'Berserk';
    }

    public static function incompatible_attack_types($args = NULL) {
        return array('Skill');
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

        if (1 != count($args['attackers'])) {
            throw new LogicException('There should only be one attacker when applying Berserk.');
        }

        $attacker = $args['attackers'][0];
        $game = $attacker->ownerObject;
        $activeDieArrayArray = $game->activeDieArrayArray;

        $attacker->remove_skill('Berserk');

        // halve number of sides
        $splitDieArray = $attacker->split();
        $newAttackDie = $splitDieArray[0];
        $activeDieArrayArray[$attacker->playerIdx][$attacker->activeDieIdx] = $newAttackDie;
        $args['attackers'][0] = $newAttackDie;
        $game->activeDieArrayArray = $activeDieArrayArray;
    }

    protected static function get_description() {
        return 'These dice cannot participate in Skill Attacks; ' .
               'instead they can make a Berserk Attack. These work exactly ' .
               'like Speed Attacks - one Berserk Die can capture any number ' .
               'of dice which add up exactly to its value. Once a Berserk ' .
               'Die performs a Berserk Attack, it is replaced with a ' .
               'non-berserk die with half the number of sides it previously ' .
               'had, rounding up. It also loses any Swing/Mood Swing/Mad Swing ' .
               'characteristics it may have had.';
    }

    protected static function get_interaction_descriptions() {
        return array(
            'Mighty' => 'Dice with both Berserk and Mighty skills will first ' .
                         'halve in size, and then grow',
            'Radioactive' => 'Dice with both Radioactive and Berserk skills making a berserk attack ' .
                             'targeting a SINGLE die are first replaced with non-berserk dice with half ' .
                             'their previous number of sides, rounding up, and then decay',
            'Speed' => 'Dice with both Berserk and Speed skills may ' .
                       'choose to make either kind of attack',
            'Weak' => 'Dice with both Berserk and Weak skills will first ' .
                         'halve in size, and then shrink',
        );
    }

    public static function prevents_win_determination() {
        return TRUE;
    }
}
