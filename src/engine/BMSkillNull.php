<?php
/**
 * BMSkillNull: Code specific to the null die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the null die skill
 */
class BMSkillNull extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array("score_value", "capture");

    /**
     * Hooked method applied when determining the score value of a die
     *
     * @param array $args
     */
    public static function score_value($args) {
        assert(array_key_exists('mult', $args));

        $args['mult'] = 0;
    }

    /**
     * Hooked method applied during capture
     *
     * @param array $args
     */
    public static function capture($args) {
        assert(array_key_exists('attackers', $args));
        assert(array_key_exists('defenders', $args));

        foreach ($args['attackers'] as $attacker) {
            if ($attacker->has_flag('JustPerformedUnsuccessfulAttack')) {
                return;
            }
        }

        foreach ($args['defenders'] as $defender) {
            if ($defender->captured) {
                $defender->add_skill('Null');
            }
        }
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'When a Null Die participates in any attack, the ' .
               'dice that are captured are worth zero points. Null Dice ' .
               'themselves are worth zero points.';
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
            'Poison' => 'Dice with both Null and Poison skills are Null',
            'Value' => 'Dice with both Null and Value skills are Null',
        );
    }

    /**
     * Does this skill prevent the determination of whether a player can win?
     *
     * @return bool
     */
    public static function prevents_win_determination() {
        return TRUE;
    }
}
