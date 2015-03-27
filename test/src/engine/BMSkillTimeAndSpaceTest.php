<?php

class BMSkillTimeAndSpaceTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillTimeAndSpace
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillTimeAndSpace;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillTimeAndSpace::commit_attack
     */
    public function testCommit_attack()
    {
        $game = new BMGame;
        $game->activePlayerIdx = 1;

        $die = BMDie::create(4);
        $die->value = 2;
        $args = array('value' => $die->value, 'game' => $game);
        $this->object->commit_attack($args);

        $this->assertFalse(isset($game->nextPlayerIdx));

        $die = BMDie::create(4);
        $die->value = 3;
        $args = array('value' => $die->value, 'game' => $game);
        $this->object->commit_attack($args);

        $this->assertTrue(isset($game->nextPlayerIdx));
    }
}
