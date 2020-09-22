<?php
/**
 * BMBtnSkillLargo: Code specific to Largo
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of Largo
 */
class BMBtnSkillLargo extends BMBtnSkill {
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

        foreach (self::incompatible_attack_types() as $attackType) {
            if (array_key_exists($attackType, $attackTypeArray)) {
                unset($attackTypeArray[$attackType]);
            }
        }
    }

    /**
     * Attack types incompatible with this skill type
     *
     * @return array
     */
    public static function incompatible_attack_types() {
        return array('Skill');
    }

    /**
     * Description of skill
     *
     * @return string
     */
    public static function get_description() {
        return 'Cannot perform skill attacks.';
    }
}
