<?php

class BMSkillPoisonTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillPoison
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillPoison;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillPoison::value
     */
    public function testValue() {
        $die = BMDie::create(4);
        $die->add_skill('Poison');
        $this->assertCount(1, $die->hookList);
        $this->assertEquals(array('scoreValue'), array_keys($die->hookList));
        $this->assertEquals(array('BMSkillPoison'), $die->hookList['scoreValue']);
        $this->assertEquals(-20, $die->get_scoreValueTimesTen());
    }
}

?>
