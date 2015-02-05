<?php
/**
 * BMSkillQueer: Code specific to the queer die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the queer die skill
 */
class BMSkillQueer extends BMSkill {
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
        $value = $args['value'];

        if (!is_int($value)) {
            return;
        }

        if (0 == $value % 2) {
            $attackTypeArray['Power'] = 'Power';
        } else {
            $attackTypeArray['Shadow'] = 'Shadow';
        }

        foreach (BMSkillQueer::incompatible_attack_types(array('value' => $value)) as $attackType) {
            if (array_key_exists($attackType, $attackTypeArray)) {
                unset($attackTypeArray[$attackType]);
            }
        }
    }

    /**
     * Attack types incompatible with this skill type
     *
     * @param array $args
     * @return array
     */
    public static function incompatible_attack_types($args = NULL) {
        if (!isset($args) || !array_key_exists('value', $args)) {
            return array();
        }

        if (FALSE ===
            filter_var(
                $args['value'],
                FILTER_VALIDATE_INT,
                array("options"=>
                      array("min_range"=>1))
            )) {
            throw new InvalidArgumentException('Invalid value.');
        }

        if (0 == $args['value'] % 2) {
            return array('Shadow');
        } else {
            return array('Power');
        }
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'These dice behave like normal dice when they show ' .
               'an even number, and like Shadow Dice when they show an odd ' .
               'number.';
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
            'Trip' => 'Dice with both Queer and Trip skills always ' .
                      'determine their success or failure at Trip Attacking ' .
                      'via a Power Attack',
        );
    }
}
