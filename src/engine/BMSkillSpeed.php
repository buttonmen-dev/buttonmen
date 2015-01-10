<?php
/**
 * BMSkillSpeed: Code specific to the speed die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the speed die skill
 */
class BMSkillSpeed extends BMSkill {
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
        $attackTypeArray['Speed'] = 'Speed';
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'These dice can also make Speed Attacks, which are ' .
               'the equivalent of inverted Skill Attacks. In a Speed Attack, ' .
               'one Speed Die can capture any number of dice which add up ' .
               'exactly to its value.';
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
            'Berserk' => 'Dice with both Berserk and Speed skills may choose to make either kind of attack',
        );
    }
}
