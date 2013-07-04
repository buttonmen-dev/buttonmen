<?php

require_once "engine/BMDie.php";
require_once "engine/BMSkill.php";
require_once "engine/BMContainer.php";
require_once "engine/BMAttack.php";
require_once "engine/BMAttackSkill.php";

class TestDummyBMDieTesting extends BMDie {
    public $testvar = "";

    public function test() {
        $this->testvar = "";

        $this->run_hooks(__FUNCTION__, array(&$this->testvar));
    }
}

?>
