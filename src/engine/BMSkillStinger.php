<?php
/**
 * BMSkillStinger: Code specific to the stinger die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the stinger die skill
 */
class BMSkillStinger extends BMSkill {
    public static $hooked_methods = array('initiative_value',
                                          'attack_values');

    public static function initiative_value(&$args) {
        if (!is_array($args)) {
            return;
        }

        // stinger dice don't contribute to initiative
        $args['initiativeValue'] = 0;
    }

    public static function attack_values($args) {
        if (!is_array($args) ||
            !array_key_exists('attackType', $args) ||
            !array_key_exists('attackValues', $args)) {
            return;
        }

        if ('Skill' != $args['attackType']) {
            return;
        }

        $args['attackValues'] = range(1, max($args['attackValues']));
    }

    protected static function get_description() {
        return 'When a Stinger Die participates in a Skill Attack, it can be used as any number between 1 and the ' .
               'value it shows. Thus, a normal die showing 4 and a Stinger Die showing 6 can make a Skill Attack on ' .
               'any die showing 5 through 10. Two Stinger Dice showing 10 can Skill Attack any die between 2 and 20. ' .
               'IMPORTANT: Stinger Dice do not count for determining who goes first.';
    }

    protected static function get_interaction_descriptions() {
        return array(
            'Shadow' => 'Dice with both Shadow and Stinger skills can singly attack with any value from the min to ' .
                        'the max of the die (making a shadow attack against a die whose value is greater than or ' .
                        'equal to their own, or a skill attack against a die whose value is lower than or equal to ' .
                        'their own)',
        );
    }
}
