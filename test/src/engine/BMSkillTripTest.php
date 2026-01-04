<?php

class BMSkillTripTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillTrip
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillTrip;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillTrip::attack_list
     */
    public function testAttack_list()
    {
        $a = array();
        $b = array('attackTypeArray' => &$a);

        $this->object->attack_list($b);
        // Test adding Trip
        $this->assertNotEmpty($a);
        $this->assertContains('Trip', $a);
        // Only once
        $this->assertEquals(1, count($a));

        // Test adding Trip to a non-empty array
        $a = array('Skill' => 'Skill');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertEquals(2, count($a));
        $this->assertContains('Trip', $a);
        // Confirm other contents intact
        $this->assertContains('Skill', $a);

        // Check adding Trip to an array already containing Trip
        $a = array('Trip' => 'Trip', 'Skill' => 'Skill');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertContains('Trip', $a);
        $this->assertEquals(2, count($a));
    }

    /**
     * @covers BMSkillTrip::initiative_value
     */
    public function testInitiative_value()
    {
        $a = 15;
        $b = array('initiativeValue' => &$a);
        $this->object->initiative_value($b);
        $this->assertEquals(-1, $a);
    }
}
