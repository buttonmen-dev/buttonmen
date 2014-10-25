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
    public static $hooked_methods = array('initiative_value');

    public static function initiative_value(&$args) {
        if (!is_array($args)) {
            return;
        }

        // slow dice don't contribute to initiative
        $args['initiativeValue'] = -1;
    }

    protected static function get_description() {
        return 'These dice are not counted for the purposes of initiative.';
    }
}
