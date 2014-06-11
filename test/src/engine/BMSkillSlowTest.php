<?php

class BMSkillSlowTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillSlow
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillSlow;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillStinger::initiative_value
     */
    public function testInitiative_value()
    {
        $a = 15;
        $b = array('initiativeValue' => &$a);
        $this->object->initiative_value($b);
        $this->assertEquals(0, $a);
    }
}
