<?php

require_once __DIR__.'/BMInterfaceTestAbstract.php';

class BMInterfaceHistoryTest extends BMInterfaceTestAbstract {

    protected function init() {
        $this->object = new BMInterfacePlayer(TRUE);
    }

    public function test_dummy() {
        // currently, there are no tests of history functionality
    }
}
