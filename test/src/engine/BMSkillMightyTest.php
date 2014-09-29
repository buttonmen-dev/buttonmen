<?php

class BMSkillMightyTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillMighty
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillMighty;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testPre_roll()
    {
        $die = new BMDie;
        $die->init(7);
        $args = array('die' => $die);
        $this->object->pre_roll($args);

        $this->assertEquals(8, $die->max);
    }
}

