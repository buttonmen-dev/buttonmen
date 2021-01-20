<?php
/**
 * BMSkillKonstant: Code specific to the konstant die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the konstant die skill
 */
class BMSkillKonstant extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('attack_list',
                                          'add_skill',
                                          'attack_values',
                                          'hit_table');

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

        if (array_key_exists('Power', $attackTypeArray)) {
            unset($attackTypeArray['Power']);
        }
    }

    /**
     * Hooked method applied when adding the skill
     *
     * @param array $args
     */
    public static function add_skill($args) {
        if (!array_key_exists('die', $args)) {
            return;
        }

        $args['die']->doesReroll = FALSE;
    }

    /**
     * Hooked method applied when determining the attack values of a die
     *
     * @param array $args
     */
    public static function attack_values($args) {
        if (!is_array($args) ||
            !array_key_exists('attackType', $args) ||
            !array_key_exists('attackValues', $args)) {
            return;
        }

        if ('Skill' != $args['attackType']) {
            return;
        }

        $initialAttackValues = $args['attackValues'];
        $negativeAttackValues = array_map(
            function ($val) {
                return (-$val);
            },
            $initialAttackValues
        );

        $args['attackValues'] = array_values(
            array_unique(
                array_merge(
                    $initialAttackValues,
                    $negativeAttackValues
                )
            )
        );
    }

    /**
     * Hooked method applied when calculating a skill attack hit table
     *
     * @param array $args
     */
    public static function hit_table($args) {
        // validate arguments
        assert(
            array_key_exists('hits', $args) &&
            array_key_exists('dieLetter', $args)
        );

        // remove hits that are the result of single-die skill attacks by
        // konstant dice

        // for each possible hit value
        foreach ($args['hits'] as $val => &$comboArray) {
            // check whether the hit combinations include the required
            // single-die skill attack
            if (array_key_exists($args['dieLetter'], $comboArray)) {
                if (1 == count($comboArray)) {
                    // the hit value can be obtained only via the
                    // single-die skill attack, so unset the hit itself
                    unset($args['hits'][$val]);
                } else {
                    // unset the single-die skill attack option, but
                    // leave the hit, since some other combination can
                    // still achieve it
                    unset($comboArray[$args['dieLetter']]);
                }
            }
        }
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'These dice do not reroll after an attack; they keep ' .
               'their current value. Konstant dice can not Power Attack, ' .
               'and cannot perform a Skill Attack by themselves, but they ' .
               'can add OR subtract their value in a multi-dice Skill ' .
               'Attack. If another skill causes a Konstant die to reroll ' .
               '(e.g., Chance, Trip, Ornery), ' .
               'it continues to show the same value. If another skill ' .
               'causes the die to change its value without rerolling ' .
               '(e.g., Focus, Fire), the die\'s value does change and then ' .
               'continues to show that new value.';
    }

    /**
     * Descriptions of interactions between this skill and other skills
     *
     * An array, indexed by other skill name, whose values are descriptions of
     * interactions between the relevant skills
     *
     * @return array
     */
    protected static function get_interaction_descriptions() {
        return array(
            'Chance' => 'Dice with both Chance and Konstant skills retain their current value ' .
                        'when rerolled due to Chance',
            'Focus' => 'Dice with both Focus and Konstant skills may be turned down to gain initiative',
            'Maximum' => 'Dice with both Konstant and Maximum retain their current value when rerolled',
            'Ornery' => 'Dice with both Konstant and Ornery skills retain their current value when rerolled',
            'TimeAndSpace' => 'Attacking Konstant TimeAndSpace dice do not ' .
                              'trigger the TimeAndSpace skill because they do not reroll',
            'Trip' => 'Dice with both Konstant and Trip skills retain their current value when rerolled',
            'Warrior' => 'A Konstant Warrior die can only add a positive value to a skill attack'
        );
    }
}
