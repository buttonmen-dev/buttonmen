<?php
/**
 * BMSkillSlow: Code specific to the slow die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the slow die skill
 */
class BMSkillSlow extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('initiative_value');

    /**
     * Hooked method applied when determining the initiative value of a die
     *
     * @param array $args
     */
    public static function initiative_value(&$args) {
        if (!is_array($args)) {
            return;
        }

        // slow dice don't contribute to initiative
        $args['initiativeValue'] = -1;
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'These dice are not counted for the purposes of initiative.';
    }
}
