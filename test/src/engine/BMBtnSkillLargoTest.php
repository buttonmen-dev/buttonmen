<?php

class BMBtnSkillLargoTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMBtnSkillLargo
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMBtnSkillLargo;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMBtnSkillTheFlyingSquirrel::attack_list
     */
    public function testAttack_list()
    {
        $a = array();
        $b = array('attackTypeArray' => &$a);

        $this->object->attack_list($b);
        // Test adding Largo button skill
        $this->assertEmpty($a);

        // Test adding Largo button skill to a non-empty array
        $a = array('Power' => 'Power');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertEquals(1, count($a));
        // Confirm other contents intact
        $this->assertContains('Power', $a);

        // Test Skill removal
        $a = array('Power' => 'Power', 'Skill' => 'Skill');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertNotContains('Skill', $a);
        // Check proper behavior not disrupted when removing Power
        $this->assertEquals(1, count($a));
        $this->assertContains('Power', $a);

        // Check removing Skill from the middle of longer lists
        $a = array('Speed' => 'Speed',
                   'Trip'  => 'Trip',
                   'Skill' => 'Skill',
                   'Power' => 'Power'
                   );
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertNotContains('Skill', $a);
        $this->assertEquals(3, count($a));
    }
}

