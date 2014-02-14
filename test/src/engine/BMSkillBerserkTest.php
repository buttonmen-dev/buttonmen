<?php

class BMSkillBerserkTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillBerserk
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillBerserk;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillBerserk::attack_list
     */
    public function testAttack_list()
    {
        $a = array();
        $b = array('attackTypeArray' => &$a);

        $this->object->attack_list($b);
        // Test adding Berserk
        $this->assertNotEmpty($a);
        $this->assertContains('Berserk', $a);
        // Only once
        $this->assertEquals(1, count($a));

        // Test adding Berserk to a non-empty array
        $a = array('Power' => 'Power', 'Skill' => 'Skill');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertEquals(2, count($a));
        $this->assertContains('Berserk', $a);
        // Confirm other contents intact
        $this->assertContains('Power', $a);
        $this->assertNotContains('Skill', $a);

        // Check adding Berserk to an array already containing Berserk
        $a = array('Power' => 'Power', 'Berserk' => 'Berserk');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertContains('Berserk', $a);
        $this->assertEquals(2, count($a));

    }
}

