<?php
/**
 * BMSkillTimeAndSpace: Code specific to the time and space die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the time and space die skill
 */
class BMSkillTimeAndSpace extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('commit_attack');

    /**
     * Hooked method applied when checking if a die should request a swing value
     *
     * @return string
     */
    public static function commit_attack($args) {
        if (!is_array($args)) {
            return;
        }

        if (!array_key_exists('value', $args) ||
            !array_key_exists('game', $args)) {
            return;
        };

        // if odd value, the bitwise AND with 1 will be true
        if ($args['value'] & 1) {
            $args['game']->nextPlayerIdx = $args['game']->activePlayerIdx;
        }
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'If a Time and Space Die is rerolled after it participates in ' .
               'an attack and rolls odd, then the player will take another ' .
               'turn. If multiple Time and Space dice are rerolled and show odd, ' .
               'only one extra turn is given per reroll.';
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
