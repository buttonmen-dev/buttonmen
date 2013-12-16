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

//    /**
//     * @covers BMSkillSpeed::attack_list
//     */
//    public function testAttack_list()
//    {
//        $a = array();
//        $b = array('attackTypeArray' => &$a);
//
//        $this->object->attack_list($b);
//        // Test adding Speed
//        $this->assertNotEmpty($a);
//        $this->assertContains('Speed', $a);
//        // Only once
//        $this->assertEquals(1, count($a));
//
//        // Test adding Speed to a non-empty array
//        $a = array('Power' => 'Power', 'Skill' => 'Skill');
//        $this->object->attack_list($b);
//        $this->assertNotEmpty($a);
//        $this->assertEquals(3, count($a));
//        $this->assertContains('Speed', $a);
//        // Confirm other contents intact
//        $this->assertContains('Power', $a);
//        $this->assertContains('Skill', $a);
//
//        // Check adding Speed to an array already containing Speed
//        $a = array('Speed' => 'Speed', 'Skill' => 'Skill');
//        $this->object->attack_list($b);
//        $this->assertNotEmpty($a);
//        $this->assertContains('Speed', $a);
//        $this->assertEquals(2, count($a));
//
//    }

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