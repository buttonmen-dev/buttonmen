<?php

class TestDummyGame {
    public $nPlayers = 2;

    public $dice = array();

    public $attack = array('attackerPlayerIdx' => 0,
                           'defenderPlayerIdx' => 1,
                           'attackerAttackDieIdxArray' => array(),
                           'defenderAttackDieIdxArray' => array(),
                           'attackType' => '');

    public function add_die($die) {
        $this->dice[] = array($die->playerIdx, $die);
    }

    public $swingrequest;

    public function request_swing_values($die, $swingtype) {
        $this->swingrequest = array($die, $swingtype);
    }

    public $all_values_specified = FALSE;

    public function require_values() {
            throw new Exception("require_values called");
    }

    public function attackerAttackDieArray() {
        return $this->attackers;
    }

    public function defenderAttackDieArray() {
        return $this->defenders;
    }

    public $attackerAllDieArray = array();

    public $defenderAllDieArray = array();

    public $activeDieArrayArray = array(array(), array());

    public $captures = array();

    public function capture_die($defender, $player = NULL) {
        $this->captures[] = $defender;
    }

    public function active_player() {
        return 1;
    }
}
