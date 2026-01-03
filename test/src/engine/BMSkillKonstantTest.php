<?php

class BMSkillKonstantTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillKonstant
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillKonstant;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillKonstant::attack_list
     */
    public function testAttack_list()
    {
        // check for graceful failure
        $this->object->attack_list(NULL);

        // Test Power removal
        $a = array('Power' => 'Power', 'Skill' => 'Skill');
        $b = array('attackTypeArray' => &$a);

        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertNotContains('Power', $a);
        // Check proper behavior not disrupted when removing Power
        $this->assertEquals(1, count($a));
        $this->assertContains('Skill', $a);

        // Check removing Power from the middle of longer lists
        $a = array('Speed' => 'Speed',
                   'Trip'  => 'Trip',
                   'Power' => 'Power',
                   'Skill' => 'Skill');
        $this->object->attack_list($b);
        $this->assertNotEmpty($a);
        $this->assertNotContains('Power', $a);
        $this->assertEquals(3, count($a));
    }

    /**
     * @covers BMSkillKonstant::add_skill
     */
    public function testAdd_skill()
    {
        $die1 = BMDie::create(6);
        $die1->add_skill('Konstant');
        $die2 = $die1->make_play_die();
        $this->assertFalse(FALSE === filter_var($die2->value,
                                                FILTER_VALIDATE_INT,
                                                array("options"=>
                                                      array("min_range"=>1,
                                                            "max_range"=>6))));
        $initialDieValue = $die2->value;
        $this->assertEquals(1, $die2->min);
        $this->assertEquals(6, $die2->max);

        $die2->roll(TRUE);
        $this->assertEquals($initialDieValue, $die2->value);
        $this->assertEquals(1, $die2->min);
        $this->assertEquals(6, $die2->max);

        $die2->roll(FALSE);
        $this->assertEquals($initialDieValue, $die2->value);
        $this->assertEquals(1, $die2->min);
        $this->assertEquals(6, $die2->max);
    }

    /**
     * @covers BMSkillKonstant::attack_values
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
        $this->assertEquals(array(3, -3), $attackValues);

        $attackValues = array(0, 4, 5);
        $this->object->attack_values(array('attackType' => 'Skill',
                                           'attackValues' => &$attackValues));
        $this->assertEquals(array(0, 4, 5, -4, -5), $attackValues);
    }
}
