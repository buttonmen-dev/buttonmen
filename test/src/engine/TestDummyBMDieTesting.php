<?php

class TestDummyBMDieTesting extends BMDie {
    public $testvar = "";

    public function test() {
        $this->testvar = "";

        $this->run_hooks(__FUNCTION__, array(&$this->testvar));
    }
}

?>
