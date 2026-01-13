<?php

class BMAttackPassTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMAttackPass
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMAttackPass;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMAttackPass::validate_attack
     */
    public function testValidate_attack()
    {
        $game = new TestDummyGame;

        $die1 = new BMDie;
        $die1->init(6);
        $die1->value = 6;

        $die2 = new BMDie;
        $die2->init(6);
        $die2->value = 1;

        // Basic error handling
        $this->assertTrue($this->object->validate_attack($game, array(), array()));
        $this->assertFalse($this->object->validate_attack($game, array($die1), array()));
        $this->assertFalse($this->object->validate_attack($game, array(), array($die1)));
        $this->assertFalse($this->object->validate_attack($game, array($die1), array($die2)));
    }
}
