<?php

class BMSkillSpeedTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillSpeed
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillSpeed;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillSpeed::attack_list
     */
    public function testAttack_list()
    {
        $a = array();
        $b = array('attackTypeArray' => &$a);

        $this->object->attack_list($b);
        // Test adding Speed
        $this->assertNotEmpty($a);
        $this->assertContains('Speed', $a);
        // Only once
        $this->assertEquals(1, count($a));

        // Test adding Speed to a non-empty array
        $a = array('Power' => 'Power', 'Skill' => 'Skill');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertEquals(3, count($a));
        $this->assertContains('Speed', $a);
        // Confirm other contents intact
        $this->assertContains('Power', $a);
        $this->assertContains('Skill', $a);

        // Check adding Speed to an array already containing Speed
        $a = array('Speed' => 'Speed', 'Skill' => 'Skill');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertContains('Speed', $a);
        $this->assertEquals(2, count($a));

    }
}
