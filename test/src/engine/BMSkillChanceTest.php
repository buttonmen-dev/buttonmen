<?php

class BMSkillChanceTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillChance
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillChance;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillChance::react_to_initiative
     */
    public function testReact_to_initiative()
    {
        $this->assertTrue($this->object->react_to_initiative());
    }
}
