<?php

class BMSkillJoltTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillJolt
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillJolt;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillJolt::capture
     */
    public function testCapture()
    {
        $game = new BMGame;

        $die = BMDie::create(4);
        $die->value = 3;
        $die->add_skill('Jolt');
        $die->playerIdx = 1;
        $die->ownerObject = $game;
        $args = array('attackers' => array($die));

        $except = FALSE;
        try {
            $this->object->capture($args);
        } catch (LogicException $e) {
            $except = TRUE;
        }
        $this->assertTrue($except, '');
        $this->assertFalse(isset($game->nextPlayerIdx));

        $die->add_flag('IsAttacker');
        $this->object->capture($args);
        $this->assertTrue(isset($game->nextPlayerIdx));
        $this->assertEquals(1, $game->nextPlayerIdx);
    }

    /**
     * @covers BMSkillJolt::be_captured
     */
    public function testBeCaptured()
    {
        $game = new BMGame;

        $die0 = BMDie::create(4);
        $die0->value = 3;
        $die0->playerIdx = 0;
        $die1 = BMDie::create(6);
        $die1->value = 2;
        $die1->playerIdx = 1;
        $die0->ownerObject = $game;
        $die1->ownerObject = $game;
        $args = array('attackers' => array($die0), 'defenders' => array($die1));

        $except = FALSE;
        try {
            $this->object->be_captured($args);
        } catch (LogicException $e) {
            $except = TRUE;
        }
        $this->assertTrue($except, "BMDie::capture not called");
        $this->assertFalse(isset($game->nextPlayerIdx));

        $die1->add_skill('Jolt');
        $this->object->be_captured($args);
        $this->assertFalse(isset($game->nextPlayerIdx));

        $die0->add_flag('IsAttacker');
        $this->object->be_captured($args);
        $this->assertFalse(isset($game->nextPlayerIdx));

        $die1->add_flag('WasJustCaptured');
        $this->object->be_captured($args);
        $this->assertTrue(isset($game->nextPlayerIdx));
        $this->assertEquals(0, $game->nextPlayerIdx);
    }
}
