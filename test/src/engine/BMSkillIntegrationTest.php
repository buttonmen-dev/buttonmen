<?php

// Testing the integration between BMDie and the various skills.
// (And, once we have skills that overlap, attempting to test their
// interaction with one another.)

class BMSkillIntegrationTest extends PHPUnit_Framework_TestCase {
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMDie;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }


    public function testShadow()
    {
        $this->object->add_skill('Shadow');

        $arr = $this->object->attack_list();

        $this->assertNotEmpty($arr);
        $this->assertEquals(2, count($arr));
        $this->assertNotContains('Power', $arr);
        $this->assertEquals('Shadow', $arr['Shadow']);
        $this->assertEquals('Skill', $arr['Skill']);
    }

        public function testSpeed()
    {
        $this->object->add_skill('Speed');

        $arr = $this->object->attack_list();

        $this->assertNotEmpty($arr);
        $this->assertEquals(3, count($arr));
        $this->assertEquals('Power', $arr['Power']);
        $this->assertEquals('Skill', $arr['Skill']);
        $this->assertEquals('Speed', $arr['Speed']);
    }
}

