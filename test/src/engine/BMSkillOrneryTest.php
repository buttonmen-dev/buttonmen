<?php

class BMSkillOrneryTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillOrnery
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillOrnery;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /*
     * @covers BMSkillOrnery::perform_end_of_turn_die_actions
     */
    public function testPerform_end_of_turn_die_actions_invalid_args() {
        $die = BMDie::create(99);
        $die->add_skill('Ornery');
        $die->roll(FALSE);
        $dieValue = $die->value;

        $parArray = NULL;
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertEquals($dieValue, $die->value);

        $parArray = array();
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertEquals($dieValue, $die->value);

        $parArray = array('die' => 1);
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertEquals($dieValue, $die->value);

        $parArray = array('die' => $die);
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertEquals($dieValue, $die->value);

        $parArray = array('attackType' => 'Power');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertEquals($dieValue, $die->value);
    }

    /*
     * @covers BMSkillOrnery::perform_end_of_turn_die_actions
     */
    public function testPerform_end_of_turn_die_actions_pass_attack() {
        $die = BMDie::create(99);
        $die->add_skill('Ornery');
        $die->roll(FALSE);
        $dieValue = $die->value;

        $parArray = array('die' => $die, 'attackType' => 'Pass');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertEquals($dieValue, $die->value);
    }

    /*
     * @covers BMSkillOrnery::perform_end_of_turn_die_actions
     */
    public function testPerform_end_of_turn_die_actions_has_attacked() {
        $die = BMDie::create(99);
        $die->add_skill('Ornery');
         $die->roll(FALSE);
        $die->hasAttacked = TRUE;
        $dieValue = $die->value;

        $parArray = array('die' => $die, 'attackType' => 'Power');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertEquals($dieValue, $die->value);
    }

    /*
     * @covers BMSkillOrnery::perform_end_of_turn_die_actions
     */
    public function testPerform_end_of_turn_die_actions_unavailable() {
        $die = BMDie::create(99);
        $die->add_skill('Ornery');
        $die->roll(FALSE);
        $die->unavailable = TRUE;
        $dieValue = $die->value;

        $parArray = array('die' => $die, 'attackType' => 'Power');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertEquals($dieValue, $die->value);
    }

    /*
     * @covers BMSkillOrnery::perform_end_of_turn_die_actions
     */
    public function testPerform_end_of_turn_die_actions_ornery() {
        $die = BMDie::create(99);
        $die->add_skill('Ornery');
        $die->roll(FALSE);
        $dieValue = $die->value;

        $die1 = clone $die;
        $die2 = clone $die;
        $die3 = clone $die;
        $die4 = clone $die;
        $die5 = clone $die;

        $parArray = array('die' => $die1, 'attackType' => 'Power');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);

        $parArray = array('die' => $die2, 'attackType' => 'Power');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);

        $parArray = array('die' => $die3, 'attackType' => 'Power');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);

        $parArray = array('die' => $die4, 'attackType' => 'Power');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);

        $parArray = array('die' => $die5, 'attackType' => 'Power');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);

        $this->assertNotEquals(
            array_fill(0, 5, $dieValue),
            array($die1->value, $die2->value, $die3->value, $die4->value, $die5->value)
        );
    }
}
