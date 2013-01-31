<?php

require_once "engine/BMDie.php";
require_once "engine/BMSkill.php";
require_once "engine/BMContainer.php";
require_once "engine/BMAttack.php";

// well defined skill classes with which to test
class BMSkillTesting extends BMSkill {
    public static $hooked_methods = array("test");

    public static function test($args) {
        $args[0] .= "testing";
    }
}

class BMSkillTesting2 extends BMSkill {
    public static $hooked_methods = array("test");

    public static function test($args) {
        $args[0] .= "still testing";
    }

}

class BMSkillAVTesting extends BMSkill {
    public static $hooked_methods = array("assist_values");

    public static function assist_values($args) {
        $args[3] = array(-1, 1);
    }

}

class BMSkillCaptureCatcher extends BMSkill {
    public static $hooked_methods = array("capture", "be_captured");

    public static function capture($args) {
        throw new Exception("capture called");
    }

    public static function be_captured($args) {
        throw new Exception("be_captured called");
    }

}

class BMSkillRollCatcher extends BMSkill {
    public static $hooked_methods = array("roll");

    public static function roll($args) {
        throw new Exception("roll called");
    }
}

class BMSkillTestStinger extends BMSkill {
    public static $hooked_methods = array("attack_values");

    public static function attack_values($args) {
        $alist = &$args[1];

        for ($i = $alist[0] - 1; $i > 0; $i--) {
            $alist[] = $i;
        }
    }
}

class BMDieTesting extends BMDie {
    public $testvar = "";

    public function test() {
        $this->testvar = "";

        $this->run_hooks(__FUNCTION__, array(&$this->testvar));
    }
}

class BMContTesting extends BMContainer {

}

class BMAttTesting extends BMAttack {
    public function test_ovm_helper($game, $one, $many, $compare) {
        return $this->search_ovm_helper($game, $one, $many, $compare);
    }

    public function test_ovo($game, $att, $def) {
        return $this->search_onevone($game, $att, $def);
    }

    public function test_ovm($game, $att, $def) {
        return $this->search_onevmany($game, $att, $def);
    }

    public function test_mvo($game, $att, $def) {
        return $this->search_manyvone($game, $att, $def);
    }

    public function test_collect_helpers($game, $att, $def) {
        return $this->collect_helpers($game, $att, $def);
    }

    public function clear_dice() {
        $this->validDice = array();
    }

    public function clear_log() {
        $this->attackLog = array();
    }
    public $attackLog = array();

    public $validate = FALSE;

    public function validate_attack($game, $attackers, $defenders) {
        $this->attackLog[] = array($attackers, $defenders);
        return $this->validate;
    }
}

class DummyGame {
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