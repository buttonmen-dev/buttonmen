<?php
/**
 * BMSkillDoppelganger: Code specific to the doppelganger die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the doppelganger die skill
 */
class BMSkillDoppelganger extends BMSkillMorphing {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('capture');

    /**
     * Hooked method applied during capture
     *
     * @param array $args
     */
    public static function capture(&$args) {
        if (!self::are_dice_in_attack_valid($args)) {
            return;
        }

        if (!('Power' == $args['type'])) {
            return;
        }

        $attacker = $args['caller'];
        $defender = self::get_single_defender($args['defenders'], TRUE);

        // replace the attacking die here in place to allow radioactive to trigger correctly
        $game = $attacker->ownerObject;
        $activeDieArrayArray = $game->activeDieArrayArray;

        $newAttackDie = self::create_morphing_clone_target($args['caller'], $defender);
        // give the copy that mood/mad trigger afterwards
        $newAttackDie->value = $defender->value;

        $activeDieArrayArray[$attacker->playerIdx][$attacker->activeDieIdx] = $newAttackDie;
        $args['attackers'][0] = $newAttackDie;
        $game->activeDieArrayArray = $activeDieArrayArray;
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'When a Doppelganger Die performs a Power Attack on ' .
               'another die, the Doppelganger Die becomes an exact copy of ' .
               'the die it captured. The newly copied die is then rerolled, ' .
               'and has all the abilities of the captured die. For instance, ' .
               'if a Doppelganger Die copies a Turbo Swing Die, then it may ' .
               'change its size as per the rules of Turbo Swing Dice. Usually ' .
               'a Doppelganger Die will lose its Doppelganger ability when ' .
               'it copies another die, unless that die is itself a Doppelganger ' .
               'Die.';
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
            'Radioactive' => 'Dice with both Radioactive and Doppelganger first decay, then ' .
                             'each of the "decay products" are replaced by exact copies of the ' .
                             'die they captured',
            'Rage' => 'A Doppelganger die that captures a Rage die with a Power attack will ' .
                      'retain Rage after it transforms',
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
