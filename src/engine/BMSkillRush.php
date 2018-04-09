<?php
/**
 * BMSkillRush: Code specific to the rush die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the rush die skill
 */
class BMSkillRush extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array();

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'A Rush Die can perform a Rush Attack, in which it can ' .
               'capture two enemy dice with values that add up exactly to ' .
               'its value. However, Rush Dice are also vulnerable to the ' .
               'same kind of attack. Any die can make a Rush Attack if ' .
               'the target dice include at least one Rush Die.';
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
