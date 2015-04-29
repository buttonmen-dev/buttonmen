<?php
/**
 * BMSkillTurbo: Code specific to the turbo die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the turbo die skill
 */
class BMSkillTurbo extends BMSkill {
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
        return 'After your starting roll, you may change the size of a ' .
               'Turbo Swing die or a Turbo Option die every time you roll it. ' .
               'Decide on a size first that is valid for the Swing or Option '.
               'type, then roll the new die as usual. If a Turbo Die is ' .
               'rerolled by a player other than the player who has the Turbo ' .
               'die (e.g., during a trip attack), then the size cannot be ' .
               'changed.';
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
