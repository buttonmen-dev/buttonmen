<?php

class BMSkillKonstantTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillKonstant
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillKonstant;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillKonstant::attack_list
     */
    public function testAttack_list()
    {
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
     * @covers BMSkillKonstant::make_play_die
     */
    public function testMake_play_die()
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
        $this->assertEquals($initialDieValue, $die2->min);
        $this->assertEquals($initialDieValue, $die2->max);

        $die2->roll(TRUE);
        $this->assertEquals($initialDieValue, $die2->value);
        $this->assertEquals($initialDieValue, $die2->min);
        $this->assertEquals($initialDieValue, $die2->max);
        
        $die2->roll(FALSE);
        $this->assertEquals($initialDieValue, $die2->value);
        $this->assertEquals($initialDieValue, $die2->min);
        $this->assertEquals($initialDieValue, $die2->max);
    }
}

?>