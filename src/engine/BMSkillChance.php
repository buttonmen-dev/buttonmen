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
    public static $hooked_methods = array("react_to_initiative");

    public static function react_to_initiative() {
        return TRUE;
    }

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

    protected static function get_interaction_descriptions() {
        return array(
            'Focus' => 'Dice with both Chance and Focus skills may choose either skill to gain initiative',
            'Konstant' => 'Dice with both Chance and Konstant skills always retain their current value',
        );
    }
}
