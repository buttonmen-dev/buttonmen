<?php

class TestDummyBMAttSkillTesting extends BMAttackSkill {
    public function reset() {
        $this->hitTable = NULL;
        $this->validDice = array();
    }

    public function make_hit_table() {
        self::strip_excess_plain_zeros($this->validDice);
        $this->hitTable = new BMUtilityHitTable($this->validDice);
    }
}
