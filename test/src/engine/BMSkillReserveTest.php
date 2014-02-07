<?php

class BMSkillReserveTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillReserve
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillReserve;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    // this is to satisfy the PHPUnit audit
    public function testDummy()
    {
    }
}

?>