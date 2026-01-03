<?php

class BMSkillPoisonTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillPoison
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillPoison;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillPoison::score_value
     */
    public function testScore_value() {
        $die = BMDie::create(4);
        $die->add_skill('Poison');
        $this->assertCount(1, $die->hookList);
        $this->assertEquals(array('score_value'), array_keys($die->hookList));
        $this->assertEquals(array('BMSkillPoison'), $die->hookList['score_value']);
        $this->assertEquals(-40, $die->get_scoreValueTimesTen());

        $die->captured = TRUE;
        $this->assertEquals(-20, $die->get_scoreValueTimesTen());
    }
}
