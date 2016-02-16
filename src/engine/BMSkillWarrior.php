<?php
/**
 * BMSkillWarrior: Code specific to the warrior die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the warrior die skill
 */
class BMSkillWarrior extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('initiative_value',
                                          'attack_list',
                                          'attack_values',
                                          'capture',
                                          'post_roll',
                                          'score_value',
                                          'react_to_initiative');

    /**
     * Hooked method applied when determining initiative
     *
     * @param array $args
     */
    public static function initiative_value(&$args) {
        if (!is_array($args)) {
            return;
        }

        // warrior dice don't contribute to initiative
        $args['initiativeValue'] = -1;
    }

    /**
     * Hooked method applied when determining possible attack types
     *
     * @param array $args
     */
    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];
        foreach (array_keys($attackTypeArray) as $attackType) {
            if ('Skill' == $attackType) {
                if (1 == $args['nAttDice']) {
                    unset($attackTypeArray[$attackType]);
                }
            } else {
                unset($attackTypeArray[$attackType]);
            }
        }
    }

    /**
     * Hooked method applied when determining possible attack values
     *
     * @param array $args
     */
    public static function attack_values($args) {
        if (!is_array($args) ||
            !array_key_exists('attackValues', $args) ||
            !array_key_exists('value', $args)) {
            return;
        }

        $args['attackValues'] = array($args['value']);
    }

    /**
     * Hooked method applied during capture
     *
     * @param array $args
     */
    public static function capture(&$args) {
        if (!is_array($args)) {
            return;
        }

        if (!array_key_exists('type', $args)) {
            return;
        }

        if ('Skill' != $args['type']) {
            return;
        }

        if (!array_key_exists('attackers', $args)) {
            return;
        }

        if (count($args['attackers']) <= 1) {
            throw new LogicException('There must be more than one attacker when bringing a Warrior die into play.');
        }

        // check that there is only one warrior die present
        $nWarrior = 0;
        foreach ($args['attackers'] as $dieIdx => $attacker) {
            if ($attacker->has_skill('Warrior')) {
                $nWarrior++;
                $warriorIdx = $dieIdx;
            }
        }

        if (1 != $nWarrior) {
            throw new LogicException('Only one Warrior die can be brought into play at a time');
        }

        $warriorAttacker = $args['attackers'][$warriorIdx];
        $warriorAttacker->remove_skill('Warrior');
    }

    /**
     * Hooked method applied after rolling a die
     *
     * @param array $args
     * @return bool
     */
    public static function post_roll(&$args) {
        if (!($args['die'] instanceof BMDie)) {
            return FALSE;
        }

        $die = $args['die'];
        $die->value = $die->max;
        return TRUE;
    }

    /**
     * Hooked method applied when determining the score value of a die
     *
     * @param array $args
     */
    public static function score_value($args) {
        assert(array_key_exists('mult', $args));

        $args['mult'] = 0;
    }

    /**
     * Hooked method applied when determining whether a die can react to initiative
     */
    public static function react_to_initiative() {
        return 'forceFalse';
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'These are extra dice which may be brought into play during ' .
               'a round, by using one of them to a Skill Attack. Once a ' .
               'Warrior die is brought into play, it loses the Warrior skill ' .
               'for the rest of the round. After the round, the die regains ' .
               'the Warrior skill to start the next round. While they are ' .
               'out of play, Warrior dice are completely out of play: They ' .
               'aren\'t part of your starting dice, they don\'t count for ' .
               'initiative, they can\'t be attacked, none of their other ' .
               'skills can be used, etc. The only thing they can do is ' .
               'participate in a Skill attack. ' .
               'Only one Warrior Die may be used in any given Skill Attack, ' .
               'and that Skill Attack must include one or more dice that are ' .
               'already in play as well (i.e. you can\'t make a single-die ' .
               'Skill Attack with a Warrior die). The Warrior die adds its full ' .
               'value to the Skill Attack. After the target die is captured, ' .
               'the Warrior loses the Warrior skill, any other skills on the ' .
               'die become active, and the former Warrior die is treated ' .
               'exactly like a regular die for the remainder of the round. ' .
               'Adding a Warrior die to a Skill Attack is always optional; ' .
               'even if you have no other legal attack, you can choose to ' .
               'pass rather than using a Warrior die, if you prefer.';
    }

    /**
     * Descriptions of interactions between this skill and other skills
     *
     * @return array
     */
    protected static function get_interaction_descriptions() {
        return array();
    }

    /**
     * Does this skill prevent the determination of whether a player can win?
     *
     * @return bool
     */
    public static function prevents_win_determination() {
        return TRUE;
    }
}
