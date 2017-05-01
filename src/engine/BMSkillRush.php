<?php
/**
 * BMSkillRush: Code specific to the rush die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the rush die skill
 */
class BMSkillRush extends BMSkillFocus {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods =
        array('react_to_initiative', 'score_value');

    /**
     * Hooked method applied when determining the score value of a die
     *
     * @param array $args
     */
    public static function score_value($args) {
        assert(array_key_exists('scoreValue', $args));
        assert(array_key_exists('captured', $args));

        if (!$args['captured']) {
            $args['mult'] = 0;
        }
    }

    /**
     * Does this skill prevent the determination of whether a player can win?
     *
     * @return bool
     */
    public static function prevents_win_determination() {
        return TRUE;
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'Rush Dice are exactly like Focus Dice but are worth zero points if you keep them.';
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
            'Chance' => 'Dice with both Chance and Rush skills may choose either skill to gain initiative',
            'Konstant' => 'Dice with both Rush and Konstant skills may be turned down to gain initiative',
        );
    }
}
