<?php

class BMAttackDefaultTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMAttackDefault
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMAttackDefault;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMAttackDefault::validate_attack
     */
    public function testValidate_attack_power() {
        $game = new BMGame;
        $game->activePlayerIdx = 0;

        $die1 = new BMDie;
        $die1->init(6);
        $die1->value = 6;

        $die2 = new BMDie;
        $die2->init(6);
        $die2->value = 1;

        $this->object->add_die($die1);
        $game->activeDieArrayArray = array(array($die1), array($die2));
        $game->attack = array(0, 1, array(0), array(0), 'Default');
        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die2)));
        $this->assertEquals('Power', $this->object->resolvedType);
    }

    /**
     * @covers BMAttackDefault::validate_attack
     */
    public function testValidate_attack_skill() {
        $game = new BMGame;
        $game->activePlayerIdx = 0;

        $die1 = new BMDie;
        $die1->init(6);
        $die1->value = 6;

        $die2 = new BMDie;
        $die2->init(6);
        $die2->value = 1;

        $die3 = new BMDie;
        $die3->init(8);
        $die3->value = 7;

        $this->object->add_die($die1);
        $this->object->add_die($die2);
        $game->activeDieArrayArray = array(array($die1, $die2), array($die3));
        $game->attack = array(0, 1, array(0, 1), array(0), 'Default');
        $this->assertTrue($this->object->validate_attack($game, array($die1, $die2), array($die3)));
        $this->assertEquals('Skill', $this->object->resolvedType);
    }

    /**
     * @covers BMAttackDefault::validate_attack
     */
    public function testValidate_attack_pass() {
        $game = new BMGame;
        $game->activePlayerIdx = 0;

        $die1 = new BMDie;
        $die1->init(6);
        $die1->value = 6;

        $die2 = new BMDie;
        $die2->init(10);
        $die2->value = 10;
        $die2->add_skill('Shadow');

        $this->object->add_die($die1);
        $game->activeDieArrayArray = array(array($die1), array($die2));
        $game->attack = array(0, 1, array(0), array(0), 'Default');
        $this->assertTrue($this->object->validate_attack($game, array(), array()));
        $this->assertEquals('Pass', $this->object->resolvedType);
    }

    /**
     * @covers BMAttackDefault::validate_attack
     */
    public function testValidate_attack_invalid() {
        $game = new BMGame;
        $game->activePlayerIdx = 0;

        $die1 = new BMDie;
        $die1->init(6);
        $die1->value = 6;

        $die2 = new BMDie;
        $die2->init(10);
        $die2->value = 3;
        $die2->add_skill('Shadow');

        $die3 = new BMDie;
        $die3->init(10);
        $die3->value = 1;

        $this->object->add_die($die2);
        $game->activeDieArrayArray = array(array($die1, $die2), array($die3));
        $game->attack = array(0, 1, array(1), array(0), 'Default');
        $this->assertFalse($this->object->validate_attack($game, array($die2), array($die3)));
    }

    /**
     * @covers BMAttackDefault::validate_attack
     * @covers BMAttackDefault::is_one_on_one_no_frills_attack
     */
    public function testValidate_attack_one_on_one_no_frills() {
        $game = new BMGame;
        $game->activePlayerIdx = 0;

        $die1 = new BMDie;
        $die1->init(6);
        $die1->value = 6;

        $die2 = clone $die1;

        $this->object->add_die($die1);
        $game->activeDieArrayArray = array(array($die1), array($die2));
        $game->attack = array(0, 1, array(0), array(0), 'Default');
        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die2)));
        $this->assertEquals('Power', $this->object->resolvedType);
    }

    /**
     * @covers BMAttackDefault::validate_attack
     * @covers BMAttackDefault::is_one_on_one_no_frills_attack
     */
    public function testValidate_attack_one_on_one_with_trip() {
        $game = new BMGame;
        $game->activePlayerIdx = 0;

        $die1 = new BMDie;
        $die1->init(6);
        $die1->value = 6;
        $die1->add_skill('Trip');

        $die2 = clone $die1;

        $this->object->add_die($die1);
        $game->activeDieArrayArray = array(array($die1), array($die2));
        $game->attack = array(0, 1, array(0), array(0), 'Default');
        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackDefault::validate_attack
     * @covers BMAttackDefault::is_one_on_one_no_frills_attack
     */
    public function testValidate_attack_one_on_one_with_berserk() {
        $game = new BMGame;
        $game->activePlayerIdx = 0;

        $die1 = new BMDie;
        $die1->init(6);
        $die1->value = 6;
        $die1->add_skill('Berserk');

        $die2 = clone $die1;

        $this->object->add_die($die1);
        $game->activeDieArrayArray = array(array($die1), array($die2));
        $game->attack = array(0, 1, array(0), array(0), 'Default');
        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackDefault::validate_attack
     * @covers BMAttackDefault::is_one_on_one_no_frills_attack
     */
    public function testValidate_attack_one_on_one_with_doppelganger_and_power_is_possible() {
        $game = new BMGame;
        $game->activePlayerIdx = 0;

        $die1 = new BMDie;
        $die1->init(6);
        $die1->value = 6;
        $die1->add_skill('Doppelganger');

        $die2 = clone $die1;

        $this->object->add_die($die1);
        $game->activeDieArrayArray = array(array($die1), array($die2));
        $game->attack = array(0, 1, array(0), array(0), 'Default');
        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackDefault::validate_attack
     * @covers BMAttackDefault::is_one_on_one_no_frills_attack
     */
    public function testValidate_attack_one_on_one_with_doppelganger_and_power_is_not_possible() {
        $game = new BMGame;
        $game->activePlayerIdx = 0;

        $die1 = new BMDie;
        $die1->init(6);
        $die1->value = 6;
        $die1->add_skill('Doppelganger');
        $die1->add_skill('Shadow');

        $die2 = clone $die1;

        $this->object->add_die($die1);
        $game->activeDieArrayArray = array(array($die1), array($die2));
        $game->attack = array(0, 1, array(0), array(0), 'Default');
        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die2)));
    }

    /**
     * @covers BMAttackDefault::validate_attack
     * @covers BMAttackDefault::is_one_on_one_no_frills_attack
     */
    public function testValidate_attack_one_on_one_with_fire() {
        $game = new BMGame;
        $game->activePlayerIdx = 0;

        $die1 = new BMDie;
        $die1->init(8);
        $die1->value = 6;

        $die2 = new BMDie;
        $die2->init(4);
        $die2->value = 4;
        $die2->add_skill('Fire');

        $die3 = clone $die1;

        $this->object->add_die($die1);
        $game->activeDieArrayArray = array(array($die1, $die2), array($die3));
        $game->attack = array(0, 1, array(0), array(0), 'Default');
        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die3)));
    }

    /**
     * @covers BMAttackDefault::validate_attack
     * @covers BMAttackDefault::is_one_on_one_no_frills_attack
     */
    public function testValidate_attack_one_on_one_equal_values_with_fire_and_shadow() {
        $game = new BMGame;
        $game->activePlayerIdx = 0;

        $die1 = new BMDie;
        $die1->init(8);
        $die1->value = 6;
        $die1->add_skill('Shadow');

        $die2 = new BMDie;
        $die2->init(4);
        $die2->value = 4;
        $die2->add_skill('Fire');

        $die3 = clone $die1;

        $this->object->add_die($die1);
        $game->activeDieArrayArray = array(array($die1, $die2), array($die3));
        $game->attack = array(0, 1, array(0), array(0), 'Default');
        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die3)));
    }

    /**
     * @covers BMAttackDefault::validate_attack
     * @covers BMAttackDefault::is_one_on_one_no_frills_attack
     */
    public function testValidate_attack_one_on_one_unequal_values_with_fire_and_shadow() {
        $game = new BMGame;
        $game->activePlayerIdx = 0;

        $die1 = new BMDie;
        $die1->init(8);
        $die1->value = 6;
        $die1->add_skill('Shadow');

        $die2 = new BMDie;
        $die2->init(4);
        $die2->value = 4;
        $die2->add_skill('Fire');

        $die3 = clone $die1;
        $die3->value = 7;

        $this->object->add_die($die1);
        $game->activeDieArrayArray = array(array($die1, $die2), array($die3));
        $game->attack = array(0, 1, array(0), array(0), 'Default');
        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die3)));
    }

    /**
     * @covers BMAttackDefault::validate_attack
     * @covers BMAttackDefault::is_one_on_one_no_frills_attack
     */
    public function testValidate_attack_ambiguous_berserk_speed() {
        $game = new BMGame;
        $game->activePlayerIdx = 0;

        $die1 = new BMDie;
        $die1->init(6);
        $die1->value = 6;
        $die1->add_skill('Berserk');
        $die1->add_skill('Speed');

        $die2 = new BMDie;
        $die2->init(6);
        $die2->value = 4;

        $die3 = new BMDie;
        $die3->init(6);
        $die3->value = 2;

        $this->object->add_die($die1);
        $game->activeDieArrayArray = array(array($die1), array($die2, $die3));
        $game->attack = array(0, 1, array(0), array(0, 1), 'Default');
        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2, $die3)));
    }
}
