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
    public static $hooked_methods = array('post_roll');

    /**
     * Hooked method applied after rolling a die
     *
     * @param array $args
     * @return string
     */
    public static function post_roll($args) {
        if (!is_array($args)) {
            return;
        }

        if (!array_key_exists('die', $args)) {
            return;
        };

        $die = $args['die'];
        $game = $die->ownerObject;

        if (!($die instanceof BMDie) || !($game instanceof BMGame)) {
            return;
        }

        if (!$die->has_flag('IsAttacker')) {
            return;
        }

        if (!$die->doesReroll) {
            return;
        }

        if ($die->value & 1) {
            $game->nextPlayerIdx = $game->activePlayerIdx;
        }
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'If a Time and Space Die participates in an attack and rerolls ' .
               'an odd number, then the player will take another ' .
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
        return array(
            'Radioactive' => 'Dice with both Radioactive and TimeAndSpace skills lose TimeAndSpace ' .
                             'when they decay',
        );
    }
}
