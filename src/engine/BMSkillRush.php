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

        $attackTypeArray['Speed'] = 'Speed';
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
    protected static function get_description() {
        return 'Rush Dice cannot perform Skill attacks, but can perform Speed attacks';
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
