<?php

class BMSkillStingerTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillStinger
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillStinger;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillStinger::initiative_value
     */
    public function testInitiative_value()
    {
        $a = 15;
        $b = array('initiativeValue' => &$a);
        $this->object->initiative_value($b);
        $this->assertEquals(-1, $a);
    }

    /**
     * @covers BMSkillStinger::attack_values
     */
    public function testAttack_values()
    {
        $attackValues = array(3);

        // check for graceful failure
        $this->object->attack_values(NULL);
        $this->assertEquals(array(3), $attackValues);

        $this->object->attack_values(array('attackType' => 'Skill'));
        $this->assertEquals(array(3), $attackValues);

        $this->object->attack_values(array('attackValues' => &$attackValues));
        $this->assertEquals(array(3), $attackValues);

        $this->object->attack_values(array('attackType' => 'Power',
                                           'attackValues' => &$attackValues));
        $this->assertEquals(array(3), $attackValues);

        // check that konstant die can have a negative value for a skill attack
        $this->object->attack_values(array('attackType' => 'Skill',
                                           'attackValues' => &$attackValues));
        $this->assertEquals(array(1, 2, 3), $attackValues);

        $attackValues = array(1, 4, 5);
        $this->object->attack_values(array('attackType' => 'Skill',
                                           'attackValues' => &$attackValues));
        $this->assertEquals(array(1, 2, 3, 4, 5), $attackValues);
    }
}
