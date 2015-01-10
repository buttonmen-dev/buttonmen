<?php
/**
 * BMSkillStealth: Code specific to the stealth die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the stealth die skill
 */
class BMSkillStealth extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('attack_list');

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
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'These dice cannot perform any type of attack other than Multi-die Skill Attacks, meaning two or more ' .
               'dice participating in a Skill Attack. In addition, Stealth Dice cannot be captured by any attack ' .
               'other than a Multi-die Skill Attack.';
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
        return array();
    }
}
