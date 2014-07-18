<?php

class BMAttackDefaultTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMAttackDefault
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = BMAttackDefault::get_instance();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMAttackDefault::validate_attack
     */
    public function testValidate_attack()
    {
        $game = new BMGame;
        $game->activePlayerIdx = 0;

        $die1 = new BMDie;
        $die1->init(6);
        $die1->value = 6;

        $die2 = new BMDie;
        $die2->init(6);
        $die2->value = 1;

        $die3 = new BMDie;
        $die3->init(6);
        $die3->value = 6;

        $die4 = new BMDie;
        $die4->init(8);
        $die4->value = 7;

        $die5 = new BMDie;
        $die5->init(10);
        $die5->value = 10;
        $die5->add_skill('Shadow');

        $this->object->add_die($die1);
        $game->activeDieArrayArray = array(array($die1), array($die2));
        $game->attack = array(0, 1, array(0), array(0), 'Default');
        $this->assertTrue($this->object->validate_attack($game, array($die1), array($die2)));
        $this->assertEquals('Power', $this->object->resolvedType);

        $game->activeDieArrayArray = array(array($die1), array($die3));
        $game->attack = array(0, 1, array(0), array(0), 'Default');
        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die3)));

        $game->activeDieArrayArray = array(array($die1), array($die5));
        $game->attack = array(0, 1, array(0), array(0), 'Default');
        $this->assertTrue($this->object->validate_attack($game, array(), array()));
        $this->assertEquals('Pass', $this->object->resolvedType);

        $this->object->add_die($die2);
        $game->activeDieArrayArray = array(array($die1, $die2), array($die4));
        $game->attack = array(0, 1, array(0, 1), array(0), 'Default');
        $this->assertTrue($this->object->validate_attack($game, array($die1, $die2), array($die4)));
        $this->assertEquals('Skill', $this->object->resolvedType);
    }
}
