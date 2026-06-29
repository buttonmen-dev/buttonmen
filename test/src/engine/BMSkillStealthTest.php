<?php

class BMSkillStealthTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillStealth
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillStealth;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillStealth::attack_list
     */
    public function testAttack_list_invalid() {
        // check that an illegal input is handled gracefully
        $this->object->attack_list(NULL);
    }

    /**
     * @covers BMSkillStealth::attack_list
     */
    public function testAttack_list_one_attacker() {
        $a = array('Power'   => 'Power',
                   'Skill'   => 'Skill',
                   'Speed'   => 'Speed',
                   'Berserk' => 'Berserk');
        $b = array('attackTypeArray' => &$a,
                   'nAttDice' => 1);
        $this->object->attack_list($b);
        $this->assertEmpty($a);
    }

    /**
     * @covers BMSkillStealth::attack_list
     */
    public function testAttack_list_two_attackers() {
        $a = array('Power' => 'Power',
                   'Skill' => 'Skill',
                   'Speed' => 'Speed',
                   'Berserk' => 'Berserk');
        $b = array('attackTypeArray' => &$a,
                   'nAttDice' => 2);
        $this->object->attack_list($b);
        $this->assertContains('Skill', $a);
        // Only once
        $this->assertEquals(1, count($a));
    }
}
