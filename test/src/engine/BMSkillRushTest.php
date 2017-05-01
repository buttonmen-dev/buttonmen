<?php

class BMSkillRushTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillRush
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillRush;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillRush::attack_list
     */
    public function testAttack_list()
    {
        $a = array();
        $b = array('attackTypeArray' => &$a);

        $this->object->attack_list($b);
        // Test adding Rush
        $this->assertNotEmpty($a);
        $this->assertContains('Speed', $a);
        // Only once
        $this->assertEquals(1, count($a));

        // Test adding Rush to a non-empty array
        $a = array('Power' => 'Power', 'Skill' => 'Skill');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertEquals(2, count($a));
        $this->assertContains('Speed', $a);
        // Confirm other contents intact
        $this->assertContains('Power', $a);
        $this->assertNotContains('Skill', $a);

        // Check adding Rush to an array already containing Speed
        $a = array('Power' => 'Power', 'Speed' => 'Speed');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertContains('Speed', $a);
        $this->assertEquals(2, count($a));
    }
}

