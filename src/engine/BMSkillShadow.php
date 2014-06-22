<?php

class BMSkillShadow extends BMSkill {
    public static $hooked_methods = array('attack_list');

    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];

        foreach (BMSkillShadow::incompatible_attack_types() as $attackType) {
            if (array_key_exists($attackType, $attackTypeArray)) {
                unset($attackTypeArray[$attackType]);
            }
        }

        $attackTypeArray['Shadow'] = 'Shadow';
    }
    
    public static function incompatible_attack_types($args = NULL) {
        return array('Power');
    }

    protected static function get_description() {
        return 'These dice are normal in all respects, except that ' .
               'they cannot make Power Attacks. Instead, they make inverted ' .
               'Power Attacks, called "Shadow Attacks." To make a Shadow ' .
               'Attack, Use one of your Shadow Dice to capture one of your ' .
               'opponent\'s dice. The number showing on the die you capture ' .
               'must be greater than or equal to the number showing on your ' .
               'die, but within its range. For example, a shadow 10-sided ' .
               'die showing a 2 can capture a die showing any number from ' .
               '2 to 10.';
    }

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
