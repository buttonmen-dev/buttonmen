<?php

class BMSkillOrneryTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillOrnery
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillOrnery;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
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
        $this->assertFalse($die->has_flag('HasJustRerolledOrnery'));

        $parArray = array();
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertEquals($dieValue, $die->value);
        $this->assertFalse($die->has_flag('HasJustRerolledOrnery'));

        $parArray = array('die' => 1);
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertEquals($dieValue, $die->value);
        $this->assertFalse($die->has_flag('HasJustRerolledOrnery'));

        $parArray = array('die' => $die);
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertEquals($dieValue, $die->value);
        $this->assertFalse($die->has_flag('HasJustRerolledOrnery'));

        $parArray = array('attackType' => 'Power');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertEquals($dieValue, $die->value);
        $this->assertFalse($die->has_flag('HasJustRerolledOrnery'));
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
        $this->assertFalse($die->has_flag('HasJustRerolledOrnery'));
    }

    /*
     * @covers BMSkillOrnery::perform_end_of_turn_die_actions
     */
    public function testPerform_end_of_turn_die_actions_has_attacked() {
        $die = BMDie::create(99);
        $die->add_skill('Ornery');
        $die->roll(FALSE);
        $die->add_flag('IsAttacker');
        $dieValue = $die->value;

        $parArray = array('die' => $die, 'attackType' => 'Power');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertEquals($dieValue, $die->value);
        $this->assertFalse($die->has_flag('HasJustRerolledOrnery'));
    }

    /*
     * @covers BMSkillOrnery::perform_end_of_turn_die_actions
     */
    public function testPerform_end_of_turn_die_actions_warrior() {
//        $die = BMDie::create(99);
//        $die->add_skill('Ornery');
//        $die->roll(FALSE);
//
        // james: needs to be reactivated when Warrior skill is added
//        $die->add_skill('Warrior');
//        $dieValue = $die->value;
//
//        $parArray = array('die' => $die, 'attackType' => 'Power');
//        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
//        $this->assertEquals($dieValue, $die->value);
//        $this->assertFalse($die->has_flag('HasJustRerolledOrnery'));
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
        $this->assertTrue($die1->has_flag('HasJustRerolledOrnery'));

        $parArray = array('die' => $die2, 'attackType' => 'Power');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertTrue($die2->has_flag('HasJustRerolledOrnery'));

        $parArray = array('die' => $die3, 'attackType' => 'Power');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertTrue($die3->has_flag('HasJustRerolledOrnery'));

        $parArray = array('die' => $die4, 'attackType' => 'Power');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertTrue($die4->has_flag('HasJustRerolledOrnery'));

        $parArray = array('die' => $die5, 'attackType' => 'Power');
        BMSkillOrnery::perform_end_of_turn_die_actions($parArray);
        $this->assertTrue($die5->has_flag('HasJustRerolledOrnery'));

        $this->assertNotEquals(
            array_fill(0, 5, $dieValue),
            array($die1->value, $die2->value, $die3->value, $die4->value, $die5->value)
        );
    }
}
