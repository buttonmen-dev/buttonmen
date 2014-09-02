<?php
/**
 * BMSkillAuxiliary: Code specific to the auxiliary die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the auxiliary die skill
 */
class BMSkillAuxiliary extends BMSkill {
    public static $hooked_methods = array('doesSkipSwingRequest');

    public static function doesSkipSwingRequest() {
        return 'doesSkipSwingRequest';
    }

    protected static function get_description() {
        return 'These are optional extra dice. Before each game, ' .
               'both players decide whether or not to play with their ' .
               'Auxiliary Dice. Only if both players choose to have them ' .
               'will they be in play.';
    }

    protected static function get_interaction_descriptions() {
        return array();
    }
}
