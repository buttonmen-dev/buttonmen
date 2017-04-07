<?php
/**
 * BMSkillInsult: Code specific to the insult die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the insult die skill
 */
class BMSkillInsult extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('');

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'Cannot be attacked by skill attacks.';
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
