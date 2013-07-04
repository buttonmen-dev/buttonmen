<?php

require_once "engine/BMDie.php";
require_once "engine/BMSkill.php";
require_once "engine/BMContainer.php";
require_once "engine/BMAttack.php";
require_once "engine/BMAttackSkill.php";

class TestDummyBMAttSkillTesting extends BMAttackSkill {
    public function reset() {
        $this->hit_table = NULL;
        $this->validDice = array();
    }

    public function make_hit_table() {
        $this->hit_table = new BMUtilityHitTable($this->validDice);
    }
}

?>
