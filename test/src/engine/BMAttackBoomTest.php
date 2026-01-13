<?php

class BMAttackBoomTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMAttackBoom
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMAttackBoom;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMAttackTrip::validate_attack
     */
    public function testValidate_attack_one_on_one() {
        $game = new BMGame;

        $die1 = new BMDie;
        $die1->add_skill('Boom');
        $die1->init(6);
        $die1->value = 6;

        $die2 = new BMDie;
        $die2->add_skill('Boom');
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
     * @covers BMAttackBoom::validate_attack
     */
    public function testValidate_attack_too_many() {
        $game = new BMGame;

        $die1 = new BMDie;
        $die1->add_skill('Boom');
        $die1->init(6);
        $die1->value = 6;

        $die2 = new BMDie;
        $die2->add_skill('Boom');
        $die2->init(6);
        $die2->value = 1;

        $die3 = new BMDie;
        $die3->add_skill('Boom');
        $die3->init(6);
        $die3->value = 1;

        // No many-on-one or one-on-many attacks
        $this->assertFalse($this->object->validate_attack($game, array($die1, $die2), array($die3)));
        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2, $die3)));
    }

    /**
     * @covers BMAttackBoom::validate_attack
     */
    public function testValidate_attack_stealth() {
        $game = new BMGame;

        $die1 = new BMDie;
        $die1->add_skill('Boom');
        $die1->add_skill('Stealth');
        $die1->init(6);
        $die1->value = 6;

        $die2 = new BMDie;
        $die2->add_skill('Boom');
        $die2->init(6);
        $die2->value = 1;

        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));
        $this->assertTrue($this->object->validate_attack($game, array($die2), array($die1)));
    }
}
