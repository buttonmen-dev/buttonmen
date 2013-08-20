<?php

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
