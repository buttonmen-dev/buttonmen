<?php

class BMSkillChanceTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillChance
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillChance;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
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
