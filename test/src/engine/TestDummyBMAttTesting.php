<?php

class TestDummyBMAttTesting extends BMAttack {
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

    public function validate_attack($game, array $attackers, array $defenders) {
        $this->attackLog[] = array($attackers, $defenders);
        return $this->validate;
    }

    public function find_attack($game, $includeOptional = TRUE) {
        return FALSE;
    }

    protected function are_skills_compatible(array $attArray, array $defArray) {
        return TRUE;
    }
}
