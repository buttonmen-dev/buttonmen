<?php

class BMSkillAuxiliary extends BMSkill {
    public static $hooked_methods = array('doesSkipSwingRequest');

    public static function doesSkipSwingRequest() {
        return 'doesSkipSwingRequest';
    }

    protected function get_description() {
        return 'These are optional extra dice. Before each game, ' .
               'both players decide whether or not to play with their ' .
               'Auxiliary Dice. Only if both players choose to have them ' .
               'will they be in play.';
    }

    protected function get_interaction_descriptions() {
        return array();
    }
}
