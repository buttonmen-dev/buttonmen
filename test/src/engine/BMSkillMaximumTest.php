<?php

class BMSkillMaximumTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillMaximum
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillMaximum;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillMaximum::post_roll
     */
    public function testPre_roll_bad_args() {
        $args = NULL;
        $this->assertFalse(BMSkillMaximum::post_roll($args));
    }

    /**
     * @covers BMSkillMaximum::post_roll
     */
    public function testPost_roll() {
        $die = BMDie::create(6);
        $args = array('die' => $die,
                      'isTriggeredByAttack' => TRUE);
        $this->assertTrue(BMSkillMaximum::post_roll($args));
        $this->assertEquals(6, $die->value);
    }
}
