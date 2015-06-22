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
    public static $hooked_methods = array('attack_list', 'capture');

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
     * Hooked method applied during capture
     *
     * @param array $args
     */
    public static function capture($args) {
        if ($args['type'] != 'Boom') {
            return;
        }

        assert(array_key_exists('attackers', $args));
        assert(array_key_exists('defenders', $args));
        assert(1 == count($args['attackers']));
        assert(1 == count($args['defenders']));

        $att = &$args['attackers'][0];
        $def = &$args['defenders'][0];

        // ensure that attacker doesn't reroll (because it's going out
        // of the game)
        $att->doesReroll = FALSE;

        // add attacker to the out-of-game dice, and remove it from the
        // active dice
        $att->outOfPlay = TRUE;
        $game = $att->ownerObject;
        $activeDieArrayArray = $game->activeDieArrayArray;
        $outOfPlayDieArrayArray = $game->outOfPlayDieArrayArray;

        $outOfPlayDieArrayArray[$att->playerIdx][] = $att;

        array_splice(
            $activeDieArrayArray[$att->playerIdx],
            $att->activeDieIdx,
            1
        );

        $game->activeDieArrayArray = $activeDieArrayArray;
        $game->outOfPlayDieArrayArray = $outOfPlayDieArrayArray;

        // reroll defender
        $def->roll(TRUE);
        $def->captured = FALSE;
        $def->remove_flag('WasJustCaptured');
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
