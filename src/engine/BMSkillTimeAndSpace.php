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

        if (is_null($die->value)) {
            return;
        }

        if ($die->value & 1) {
            $game->nextPlayerIdx = $die->playerIdx;
            $game->nextPlayerCause = 'TimeAndSpace';
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
            'Jolt' => 'If a die with both the Jolt and TimeAndSpace skills rerolls to an odd number, ' .
                      'it still only gives one extra turn',
            'Radioactive' => 'Dice with the TimeAndSpace skill lose TimeAndSpace ' .
                             'when they decay due to Radioactive',
            'Konstant' => 'Attacking Konstant TimeAndSpace dice do not ' .
                          'trigger the TimeAndSpace skill because they do not reroll',
        );
    }
}
