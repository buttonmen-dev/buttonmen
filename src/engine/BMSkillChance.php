<?php
/**
 * BMSkillChance: Code specific to the chance die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the chance die skill
 */
class BMSkillChance extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array("react_to_initiative");

    /**
     * Hooked method applied when checking if it is possible to react to initiative
     *
     * @param array $args
     * @return bool
     */
    public static function react_to_initiative() {
        return TRUE;
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'If you do not have the initiative at the start of ' .
               'a round you may re-roll one of your Chance Dice. If this ' .
               'results in you gaining the initiative, your opponent may ' .
               're-roll one of their Chance Dice. This can continue with ' .
               'each player re-rolling Chance Dice, even re-rolling the ' .
               'same die, until one person fails to gain the initiative or ' .
               'lets their opponent go first. Re-rolling Chance Dice is not ' .
               'only a way to gain the initiative; it can also be useful ' .
               'in protecting your larger dice, or otherwise improving your ' .
               'starting roll. Unlike Focus Dice, Chance Dice can be ' .
               'immediately re-used in an attack even if you do gain the ' .
               'initiative with them.';
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
            'Focus' => 'Dice with both Chance and Focus skills may choose either skill to gain initiative',
            'Konstant' => 'Dice with both Chance and Konstant skills retain their current value ' .
                          'when rerolled due to Chance',
            'Mighty' => 'A reroll of a Chance Mighty die due to the Chance skill triggers the Mighty skill',
            'Weak' => 'A reroll of a Chance Weak die due to the Chance skill triggers the Weak skill',
        );
    }
}
