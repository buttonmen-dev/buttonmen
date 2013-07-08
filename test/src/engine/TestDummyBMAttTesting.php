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

    public function validate_attack($game, $attackers, $defenders) {
        $this->attackLog[] = array($attackers, $defenders);
        return $this->validate;
    }
}

?>
