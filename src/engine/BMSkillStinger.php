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
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('initiative_value',
                                          'attack_values');

    /**
     * Hooked method applied when determining the initiative value of a die
     *
     * @param array $args
     */
    public static function initiative_value(&$args) {
        if (!is_array($args)) {
            return;
        }

        // stinger dice don't contribute to initiative
        $args['initiativeValue'] = -1;
    }

    /**
     * Hooked method applied when determining the attack values of a die
     *
     * @param array $args
     */
    public static function attack_values($args) {
        if (!is_array($args) ||
            !array_key_exists('attackType', $args) ||
            !array_key_exists('attackValues', $args) ||
            !array_key_exists('minValue', $args)) {
            return;
        }

        if ('Skill' != $args['attackType']) {
            return;
        }

        $args['attackValues'] = range($args['minValue'], max($args['attackValues']));
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'When a Stinger Die participates in a Skill Attack, it can be used as any number between its minimum ' .
               'possible value and the value it currently shows. Thus, a normal die showing 4 and a Stinger Die ' .
               'showing 6 can make a Skill Attack on any die showing 5 through 10. Two Stinger Dice showing 10 can ' .
               'Skill Attack any die between 2 and 20. IMPORTANT: Stinger Dice do not count for determining who ' .
               'goes first.';
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
            'Shadow' => 'Dice with both Shadow and Stinger skills can singly attack with any value from the min to ' .
                        'the max of the die (making a shadow attack against a die whose value is greater than or ' .
                        'equal to their own, or a skill attack against a die whose value is lower than or equal to ' .
                        'their own)',
            'Warrior' => 'A Warrior can\'t use the Stinger skill to add ' .
                         'less than the full value of the die, because ' .
                         'the die isn\'t in play yet',
        );
    }
}
