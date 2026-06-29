<?php

class BMSkillTimeAndSpaceTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillTimeAndSpace
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillTimeAndSpace;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillTimeAndSpace::post_roll
     */
    public function testPost_roll()
    {
        $game = new BMGame;

        $die = BMDie::create(4);
        $die->value = 3;
        $die->add_skill('TimeAndSpace');
        $die->playerIdx = 1;
        $die->ownerObject = $game;
        $args = array('die' => $die);
        $this->object->post_roll($args);

        $this->assertTrue(is_null($game->nextPlayerIdx));

        $die->value = 2;
        $die->add_flag('IsAttacker');
        $this->assertTrue(is_null($game->nextPlayerIdx));

        $die->value = 3;
        $this->object->post_roll($args);

        $this->assertFalse(is_null($game->nextPlayerIdx));
        $this->assertEquals(1, $game->nextPlayerIdx);
    }
}
