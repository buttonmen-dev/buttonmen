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
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('attack_list',
                                          'assist_values');

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
     * Hooked method applied when determining fire assist values
     *
     * @param array $args
     */
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

        if (($die instanceof BMDieWildcard) && ($die->value == 20)) {
            // Wildcard can only "turn down" to a value of 13 or lower
            $args['possibleAssistValues'] = range(7, $die->value - $die->min);
        } else {
            $args['possibleAssistValues'] = range(1, $die->value - $die->min);
        }
    }

    /**
     * Description of skill
     *
     * @return string
     */
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
            'Mighty' => 'Dice with both Fire and Mighty skills do not grow ' .
                      'when firing, only when actually rolling',
            'Weak' => 'Dice with both Fire and Weak skills do not shrink ' .
                      'when firing, only when actually rolling',
        );
    }
}
