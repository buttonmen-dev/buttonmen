<?php

require_once 'BMInterfaceTestAbstract.php';

class BMInterfaceGameActionTest extends BMInterfaceTestAbstract {

    protected function init() {
        $this->object = new BMInterfaceGameAction(TRUE);
    }

    public function test_dummy() {
        // currently, there are no interface-level tests of game action functionality
    }
}
