<?php

require_once __DIR__.'/BMInterfaceTestAbstract.php';

class BMInterfaceGameChatTest extends BMInterfaceTestAbstract {

    protected function init() {
        $this->object = new BMInterfaceGameChat(TRUE);
    }

    public function test_dummy() {
        // currently, there are no interface-level tests of game action functionality
    }
}
