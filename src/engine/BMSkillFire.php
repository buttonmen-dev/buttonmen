<?php
/**
 * BMSkillFire: Code specific to the fire die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the fire die skill
 */
class BMSkillFire extends BMSkill {
    public static $hooked_methods = array('attack_list',
                                          'assist_values');

    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];

        if (array_key_exists('Power', $attackTypeArray)) {
            unset($attackTypeArray['Power']);
        }
    }

    public static function assist_values(&$args) {
        if (!is_array($args)) {
            return;
        }

        if (!array_key_exists('attackType', $args) ||
            !array_key_exists('assistingDie', $args) ||
            !array_key_exists('possibleAssistValues', $args)) {
            return;
        }

        $die = $args['assistingDie'];

        if (!$args['assistingDie']->has_skill('Fire')) {
            return;
        }

        $isValidAttackType = ('Power' == $args['attackType']) ||
                             ('Skill' == $args['attackType']);
        if (!$isValidAttackType) {
            return;
        }

        if ($die->value <= $die->min) {
            return;
        }

        $args['possibleAssistValues'] = range(1, $die->value - $die->min);
    }

    protected static function get_description() {
        return 'Fire Dice cannot make Power Attacks. Instead, they can assist '.
               'other Dice in making Skill and Power Attacks. Before making a '.
               'Skill or Power Attack, you may increase the value showing on '.
               'any of the attacking dice, and decrease the values showing on '.
               'one or more of your Fire Dice by the same amount. For example, '.
               'if you wish to increase the value of an attacking die by 5 points, '.
               'you can take 5 points away from one or more of your Fire Dice. '.
               'Turn the Fire Dice to show the adjusted values, and then make the '.
               'attack as normal. Dice can never be increased or decreased outside '.
               'their normal range, i.e., a 10-sided die can never show a number '.
               'lower than 1 or higher than 10. Also, Fire Dice cannot assist other '.
               'dice in making attacks other than normal Skill and Power Attacks.';
    }

    protected static function get_interaction_descriptions() {
        return array(
            'Mighty' => 'Dice with both Fire and Mighty skills do not grow ' .
                      'when firing, only when actually rolling',
            'Weak' => 'Dice with both Fire and Weak skills do not shrink ' .
                      'when firing, only when actually rolling',
        );
    }
}
