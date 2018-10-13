<?php

class BMAttackTripTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMAttackTrip
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMAttackTrip;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_one_on_one() {
        $game = new BMGame;

        $die1 = new BMDie;
        $die1->add_skill('Trip');
        $die1->init(6);
        $die1->value = 6;

        $die2 = new BMDie;
        $die2->add_skill('Trip');
        $die2->init(6);
        $die2->value = 1;

        // Basic error handling
        $this->assertFalse($this->object->validate_attack($game, array(), array()));
        $this->assertFalse($this->object->validate_attack($game, array($die1), array()));
        $this->assertFalse($this->object->validate_attack($game, array(), array($die1)));

        // Basic one-on-one attacks
        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die2)));
        $this->assertTrue($this->object->validate_attack($game, array($die2), array($die1)));

    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_too_many() {
        $game = new BMGame;

        $die1 = new BMDie;
        $die1->add_skill('Trip');
        $die1->init(6);
        $die1->value = 6;

        $die2 = new BMDie;
        $die2->add_skill('Trip');
        $die2->init(6);
        $die2->value = 1;

        $die3 = new BMDie;
        $die3->add_skill('Trip');
        $die3->init(6);
        $die3->value = 1;

        // No many-on-one or one-on-many attacks
        $this->assertFalse($this->object->validate_attack($game, array($die1, $die2), array($die3)));
        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2, $die3)));
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_stealth() {
        $game = new BMGame;

        $die1 = new BMDie;
        $die1->add_skill('Trip');
        $die1->add_skill('Stealth');
        $die1->init(6);
        $die1->value = 6;

        $die2 = new BMDie;
        $die2->add_skill('Trip');
        $die2->init(6);
        $die2->value = 1;

        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));
        $this->assertFalse($this->object->validate_attack($game, array($die2), array($die1)));
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_twin() {
        $game = new BMGame;

        $die1 = new BMDie;
        $die1->add_skill('Trip');
        $die1->init(1);
        $die1->value = 1;

        $die2 = BMDie::create_from_recipe('(1,1)');
        $die2->value = 2;

        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));

        $die3 = BMDie::create_from_recipe('(0,1)');
        $die3->value = 1;

        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die3)));

    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_konstant_targeting_twin() {
        $game = new BMGame;

        $die1 = new BMDie;
        $die1->add_skill('Trip');
        $die1->add_skill('Konstant');
        $die1->init(4);
        $die1->value = 1;

        $die2 = BMDie::create_from_recipe('(1,1)');
        $die2->value = 2;

        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));

        $die1->value = 3;
        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_normal_targeting_konstant() {
        $game = new BMGame;

        $die1 = new BMDie;
        $die1->add_skill('Trip');
        $die1->init(4);
        $die1->value = 1;

        $die2 = new BMDie;
        $die2->add_skill('Konstant');
        $die2->init(8);
        $die2->value = 4;

        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die2)));

        $die2->value = 5;

        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_konstant_targeting_konstant() {
        $game = new BMGame;

        $die1 = new BMDie;
        $die1->add_skill('Trip');
        $die1->add_skill('Konstant');
        $die1->init(4);
        $die1->value = 1;

        $die2 = new BMDie;
        $die2->add_skill('Konstant');
        $die2->init(4);
        $die2->value = 3;

        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));

        $die1->value = 4;

        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_normal_targeting_maximum() {
        $game = new BMGame;

        $die1 = new BMDie;
        $die1->add_skill('Trip');
        $die1->init(4);
        $die1->value = 1;

        $die2 = new BMDie;
        $die2->add_skill('Maximum');
        $die2->init(4);
        $die2->value = 1;  // possible through something like focus

        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die2)));

        $die2->init(8);
        $die2->value = 1;

        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_konstant_targeting_maximum() {
        $game = new BMGame;

        $die1 = new BMDie;
        $die1->add_skill('Trip');
        $die1->add_skill('Konstant');
        $die1->init(4);
        $die1->value = 1;

        $die2 = new BMDie;
        $die2->add_skill('Maximum');
        $die2->init(4);
        $die2->value = 1;  // possible through something like focus

        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_trip_mood_nonswing_large_targeting_konstant() {
        $game = new BMGame;

        $die1 = BMDie::create_from_recipe('t(6)?');
        $die1->value = 6;

        $die2 = BMDie::create_from_recipe('k(6)');
        $die2->value = 1;

        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_trip_mood_nonswing_small_targeting_konstant() {
        $game = new BMGame;

        $die1 = BMDie::create_from_recipe('t(6)?');
        $die1->value = 6;

        $die2 = BMDie::create_from_recipe('k(12)');
        $die2->value = 12;

        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_trip_mood_swing_large_targeting_konstant() {
        $game = new BMGame;

        $die1 = BMDie::create_from_recipe('t(X)?');
        $die1->set_swingValue(array('X' => 6));
        $die1->value = 6;

        $die2 = BMDie::create_from_recipe('k(20)');
        $die2->value = 20;

        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_trip_mood_swing_small_targeting_konstant() {
        $game = new BMGame;

        $die1 = BMDie::create_from_recipe('t(V)?');
        $die1->set_swingValue(array('V' => 6));
        $die1->value = 6;

        $die2 = BMDie::create_from_recipe('k(20)');
        $die2->value = 20;

        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_trip_mad_nonswing_large_targeting_konstant() {
        $game = new BMGame;

        $die1 = BMDie::create_from_recipe('t(6)&');
        $die1->value = 6;

        $die2 = BMDie::create_from_recipe('k(6)');
        $die2->value = 1;

        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_trip_mad_nonswing_small_targeting_konstant() {
        $game = new BMGame;

        $die1 = BMDie::create_from_recipe('t(6)&');
        $die1->value = 6;

        $die2 = BMDie::create_from_recipe('k(12)');
        $die2->value = 12;

        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_trip_mad_swing_large_targeting_konstant() {
        $game = new BMGame;

        $die1 = BMDie::create_from_recipe('t(X)&');
        $die1->set_swingValue(array('X' => 6));
        $die1->value = 6;

        $die2 = BMDie::create_from_recipe('k(20)');
        $die2->value = 20;

        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_trip_mad_swing_small_targeting_konstant() {
        $game = new BMGame;

        $die1 = BMDie::create_from_recipe('t(V)&');
        $die1->set_swingValue(array('V' => 6));
        $die1->value = 6;

        $die2 = BMDie::create_from_recipe('k(20)');
        $die2->value = 20;

        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));
    }
}
