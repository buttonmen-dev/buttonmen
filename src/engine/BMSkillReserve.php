<?php

class BMSkillReserve extends BMSkill {
    public static $hooked_methods = array('doesSkipSwingRequest');

    public static function doesSkipSwingRequest() {
        return 'doesSkipSwingRequest';
    }

    protected function get_description() {
        return 'These are extra dice which may be brought into play ' .
               'part way through a game. Each time you lose a round you may ' .
               'choose another of your Reserve Dice; it will then be in ' .
               'play for all future rounds.';
    }

    protected function get_interaction_descriptions() {
        return array();
    }
}
