<?php

class BMSkillStingerTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillStinger
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillStinger;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
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
    public function testAttack_values_validation()
    {
        $attackValues = array(3);

        // check for graceful failure
        $this->object->attack_values(NULL);
        $this->assertEquals(array(3), $attackValues);

        $this->object->attack_values(array('attackType' => 'Skill'));
        $this->assertEquals(array(3), $attackValues);

        $this->object->attack_values(array('attackValues' => &$attackValues));
        $this->assertEquals(array(3), $attackValues);

        $this->object->attack_values(array('minValue' => 1));
        $this->assertEquals(array(3), $attackValues);

        $this->object->attack_values(array('attackType' => 'Power',
                                           'attackValues' => &$attackValues,
                                           'minValue' => 1));
        $this->assertEquals(array(3), $attackValues);
    }

    /**
     * @covers BMSkillStinger::attack_values
     */
    public function testAttack_values_standard()
    {
        $attackValues = array(3);

        $this->object->attack_values(array('attackType' => 'Skill',
                                           'attackValues' => &$attackValues,
                                           'minValue' => 1));
        $this->assertEquals(array(1, 2, 3), $attackValues);

        $attackValues = array(1, 4, 5);
        $this->object->attack_values(array('attackType' => 'Skill',
                                           'attackValues' => &$attackValues,
                                           'minValue' => 1));
        $this->assertEquals(array(1, 2, 3, 4, 5), $attackValues);
    }

    /**
     * @covers BMSkillStinger::attack_values
     */
    public function testAttack_values_twin()
    {
        $attackValues = array(4);

        $this->object->attack_values(array('attackType' => 'Skill',
                                           'attackValues' => &$attackValues,
                                           'minValue' => 2));
        $this->assertEquals(array(2, 3, 4), $attackValues);
    }

    /**
     * @covers BMSkillStinger::attack_values
     */
    public function testAttack_values_zero()
    {
        $attackValues = array(0);

        $this->object->attack_values(array('attackType' => 'Skill',
                                           'attackValues' => &$attackValues,
                                           'minValue' => 0));
        $this->assertEquals(array(0), $attackValues);
    }

    /**
     * @covers BMSkillStinger::attack_values
     */
    public function testAttack_values_negative()
    {
        $attackValues = array(1);

        $this->object->attack_values(array('attackType' => 'Skill',
                                           'attackValues' => &$attackValues,
                                           'minValue' => -1));
        $this->assertEquals(array(-1, 0, 1), $attackValues);
    }
}
