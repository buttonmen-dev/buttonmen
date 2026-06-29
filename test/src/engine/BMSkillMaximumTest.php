<?php

class BMSkillMaximumTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillMaximum
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillMaximum;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillMaximum::roll
     */
    public function testRoll_bad_args() {
        $args = NULL;
        $this->assertFalse(BMSkillMaximum::roll($args));
    }

    /**
     * @covers BMSkillMaximum::roll
     */
    public function testRoll() {
        $die = BMDie::create(6);
        $args = array('die' => $die);
        $this->assertTrue(BMSkillMaximum::roll($args));
        $this->assertEquals(6, $die->value);
    }

    /**
     * @covers BMSkillMaximum::roll
     */
    public function testRoll_with_konstant() {
        $die = BMDie::create(6);
        $die->add_skill('Maximum');
        $die->add_skill('Konstant');

        // check that the initial roll occurs correctly
        $die->roll(FALSE);
        $this->assertEquals(6, $die->value);

        $die->value = 3;
        $die->roll(FALSE);
        $this->assertEquals(3, $die->value);

        $die->roll(TRUE);
        $this->assertEquals(3, $die->value);
    }

    /**
     * @covers BMSkillMaximum::roll
     */
    public function testRoll_with_Wildcard() {
        // we need a whole BMGame because the information about the deck
        // is held at the BMPlayer level
        $game = new BMGame;
        $die = BMDie::create_from_recipe('M(C)');
        $die->playerIdx = 1;
        $die->ownerObject = $game;

        // check that the initial roll occurs correctly
        $die->roll(FALSE);
        $this->assertEquals(20, $die->value);

        $die->value = 3;
        $die->roll(FALSE);
        $this->assertEquals(20, $die->value);

        $die->roll(TRUE);
        $this->assertEquals(20, $die->value);
    }
}
