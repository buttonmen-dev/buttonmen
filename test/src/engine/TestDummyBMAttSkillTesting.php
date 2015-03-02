<?php

class TestDummyBMAttSkillTesting extends BMAttackSkill {
    public function reset() {
        $this->hitTable = NULL;
        $this->validDice = array();
    }

    public function make_hit_table() {
        $this->hitTable = new BMUtilityHitTable($this->validDice);
    }
}
