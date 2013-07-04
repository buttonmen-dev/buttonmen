<?php

//require_once "engine/BMDie.php";
//require_once "engine/BMSkill.php";
//require_once "engine/BMContainer.php";
//require_once "engine/BMAttack.php";
//require_once "engine/BMAttackSkill.php";

class TestDummyGame {
    public $dice = array();

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

    public $captures = array();

    public function capture_die($victim, $player = NULL) {
        $this->captures[] = $victim;
    }

    public function active_player() {
        return 1;
    }
}

?>
