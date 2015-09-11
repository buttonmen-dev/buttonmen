<?php

require_once 'BMInterfaceTestAbstract.php';

class BMInterfaceForumTest extends BMInterfaceTestAbstract {

    protected function init() {
        $this->object = new BMInterfaceForum(TRUE);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceForum::
     */

}
