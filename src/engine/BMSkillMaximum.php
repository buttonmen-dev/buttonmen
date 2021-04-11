<?php
/**
 * BMSkillMaximum: Code specific to the maximum die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the maximum die skill
 */
class BMSkillMaximum extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('roll');

    /**
     * Hooked method applied while rolling a die
     *
     * @param array $args
     * @return bool
     */
    public static function roll(&$args) {
        if (!isset($args['die']) || !($args['die'] instanceof BMDie)) {
            return FALSE;
        }

        $die = $args['die'];

        if (!$die->doesReroll && !is_null($die->value)) {
            return FALSE;
        }

        $die->set_value($die->max);
        return TRUE;
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'Maximum dice always roll their maximum value.';
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
            'Konstant' => 'Dice with both Konstant and Maximum retain their current value when rerolled',
        );
    }
}
