<?php

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

class BMDieTesting extends BMDie {
    public $testvar = "";

    public function test() {
        $this->testvar = "";

        $this->run_hooks(__FUNCTION__, array(&$this->testvar));
    }
}

class BMContTesting extends BMContainer {

}

class DummyGame {
    public $dice = array();

    public function add_die($player, $die) {
        $this->dice[] = array($player, $die);
    }

}

?>