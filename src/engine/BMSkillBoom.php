<?php
/**
 * BMSkillBoom: Code specific to the boom die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the boom die skill
 */
class BMSkillBoom extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('attack_list', 'pre_capture');

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
        $attackTypeArray['Boom'] = 'Boom';
    }

    /**
     * Hooked method applied before capture
     *
     * @param array $args
     */
    public static function pre_capture($args) {
        if ($args['type'] != 'Boom') {
            return;
        }

        assert(array_key_exists('attackers', $args));
        assert(array_key_exists('defenders', $args));

        if (1 != count($args['attackers'])) {
            throw new LogicException('Only one attacker for a boom attack');
        }

        $attacker = &$args['attackers'][0];
        $defender = self::get_single_defender($args['defenders'], TRUE);

        // ensure that attacker doesn't reroll (because it's going out
        // of the game)
        $attacker->doesReroll = FALSE;

        // add attacker to the out-of-game dice, and remove it from the
        // active dice
        $attacker->outOfPlay = TRUE;
        $game = $attacker->ownerObject;
        $attackingPlayer = $game->playerArray[$attacker->playerIdx];
        $attackingPlayer->outOfPlayDieArray[] = $attacker;

        array_splice(
            $attackingPlayer->activeDieArray,
            $attacker->activeDieIdx,
            1
        );

        // reroll defender
        $defender->roll(TRUE);
        $defender->captured = FALSE;
        $defender->remove_flag('WasJustCaptured');
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'Boom Dice are like normal dice with an additional attack, ' .
               'the Boom attack. To make a Boom Attack, remove one of your Boom Dice ' .
               'from play (neither player will score it). Choose one of your opponent\'s ' .
               'dice, and reroll it. Note that the targeted die is never captured, ' .
               'just re-rolled.';
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
            'Stealth' => 'Stealth dice may be targeted by boom attacks',
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
