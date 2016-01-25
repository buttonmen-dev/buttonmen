<?php
/**
 * BMSkillShadow: Code specific to the shadow die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the shadow die skill
 */
class BMSkillShadow extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('attack_list');

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

        foreach (self::incompatible_attack_types() as $attackType) {
            if (array_key_exists($attackType, $attackTypeArray)) {
                unset($attackTypeArray[$attackType]);
            }
        }

        $attackTypeArray['Shadow'] = 'Shadow';
    }

    /**
     * Attack types incompatible with this skill type
     *
     * @return array
     */
    public static function incompatible_attack_types() {
        return array('Power');
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'These dice are normal in all respects, except that ' .
               'they cannot make Power Attacks. Instead, they make inverted ' .
               'Power Attacks, called "Shadow Attacks." To make a Shadow ' .
               'Attack, use one of your Shadow Dice to capture one of your ' .
               'opponent\'s dice. The number showing on the die you capture ' .
               'must be greater than or equal to the number showing on your ' .
               'die, but within its range. For example, a shadow 10-sided ' .
               'die showing a 2 can capture a die showing any number from ' .
               '2 to 10.';
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
            'Stinger' => 'Dice with both Shadow and Stinger skills ' .
                         'can singly attack with any value from the min to the ' .
                         'max of the die (making a shadow attack against a die ' .
                         'whose value is greater than or equal to their own, or ' .
                         'a skill attack against a die whose value is lower than ' .
                         'or equal to their own)',
            'Trip' => 'Dice with both Shadow and Trip skills always ' .
                      'determine their success or failure at Trip Attacking ' .
                      'via a Power Attack',
        );
    }
}
