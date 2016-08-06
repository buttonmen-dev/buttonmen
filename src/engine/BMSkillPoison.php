<?php
/**
 * BMSkillPoison: Code specific to the poison die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the poison die skill
 */
class BMSkillPoison extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array("score_value");

    /**
     * Hooked method applied when determining the score value of a die
     *
     * @param array $args
     */
    public static function score_value($args) {
        assert(array_key_exists('mult', $args));
        assert(array_key_exists('div', $args));
        assert(array_key_exists('captured', $args));
        $args['mult'] = -$args['mult'];

        if ($args['captured']) {
            $args['div'] = 2;
        } else {
            $args['div'] = 1;
        }
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'These dice are worth negative points. If you keep ' .
               'a Poison Die of your own at the end of a round, subtract ' .
               'its full value from your score. If you capture a Poison Die ' .
               'from someone else, subtract half its value from your score.';
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
            'Null' => 'Dice with both Null and Poison skills are Null',
            'Value' => 'Dice with both Poison and Value skills are ' .
                       'Poison dice that score based on the negative of their ' .
                       'current value rather than on their number of sides',
        );
    }
}
