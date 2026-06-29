<?php

class BMSkillBoomTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillBoom
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillBoom;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillBoom::attack_list
     */
    public function testAttack_list()
    {
        $a = array();
        $b = array('attackTypeArray' => &$a);

        $this->object->attack_list($b);
        // Test adding Boom
        $this->assertNotEmpty($a);
        $this->assertContains('Boom', $a);
        // Only once
        $this->assertEquals(1, count($a));

        // Test adding Boom to a non-empty array
        $a = array('Skill' => 'Skill');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertEquals(2, count($a));
        $this->assertContains('Boom', $a);
        // Confirm other contents intact
        $this->assertContains('Skill', $a);

        // Check adding Boom to an array already containing Boom
        $a = array('Boom' => 'Boom', 'Skill' => 'Skill');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertContains('Boom', $a);
        $this->assertEquals(2, count($a));
    }
}
