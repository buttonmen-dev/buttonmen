<?php

class BMSkillRageTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillRage
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillRage;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillRage::initiative_value
     */
    public function testInitiative_value() {
        $a = 15;
        $b = array('initiativeValue' => &$a);
        $this->object->initiative_value($b);
        $this->assertEquals(-1, $a);
    }

    public function testCapture() {
        $att1 = BMDie::create(6);
        $att1->add_skill('Rage');
        $att1->value = 5;

        $att2 = BMDie::create(4);
        $att2->value = 2;

        $att3 = BMDie::create(2);
        $att3->add_skill('Rage');
        $att3->value = 1;

        $def = BMDie::create(10);
        $def->value = 8;

        $attackers = array($att1, $att2, $att3);
        $defenders = array($def);

        $args = array('type' => 'Skill',
                      'attackers' => &$attackers,
                      'defenders' => &$defenders,
                      'caller' => $att3);

        $this->assertTrue($att1->has_skill('Rage'));
        $this->assertFalse($att2->has_skill('Rage'));
        $this->assertTrue($att3->has_skill('Rage'));
        $this->object->capture($args);
        $this->assertTrue($att1->has_skill('Rage'));
        $this->assertFalse($att2->has_skill('Rage'));
        $this->assertFalse($att3->has_skill('Rage'));
    }
}
