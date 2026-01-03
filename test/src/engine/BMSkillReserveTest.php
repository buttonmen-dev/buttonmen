<?php

class BMSkillReserveTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillReserve
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillReserve;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    // this is to satisfy the PHPUnit audit
    public function testDummy()
    {
    }
}
