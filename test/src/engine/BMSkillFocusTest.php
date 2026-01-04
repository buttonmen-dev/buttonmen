<?php

class BMSkillFocusTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMSkillFocus
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void
    {
        $this->object = new BMSkillFocus;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void
    {
    }

    /**
     * @covers BMSkillFocus::react_to_initiative
     */
    public function testReact_to_initiative()
    {
        $die1 = BMDie::create(6);
        $die2 = BMDie::create(6);
        $die3 = BMDie::create(6);
        $die4 = BMDie::create(6);
        $die1->add_skill('Focus');
        $die2->add_skill('Focus');
        $die4->add_skill('Focus');

        $activeDieArrayArray = array(array($die1, $die2), array($die3, $die4));
        $playerIdx = 1;

        // can win initiative by looking at min value alone
        $die1->value = 2;
        $die2->value = 3;
        $die3->value = 4;
        $die4->value = 5;

        $this->assertTrue($this->object->react_to_initiative(
                              array('activeDieArrayArray' => $activeDieArrayArray,
                                    'playerIdx' => $playerIdx)));

        // can win initiative by looking at all die values
        $die1->value = 1;
        $die2->value = 3;
        $die3->value = 1;
        $die4->value = 5;

        $this->assertTrue($this->object->react_to_initiative(
                              array('activeDieArrayArray' => $activeDieArrayArray,
                                    'playerIdx' => $playerIdx)));

        // cannot win initiative unambiguously
        $die1->value = 1;
        $die2->value = 1;
        $die3->value = 1;
        $die4->value = 5;

        $this->assertFalse($this->object->react_to_initiative(
                              array('activeDieArrayArray' => $activeDieArrayArray,
                                    'playerIdx' => $playerIdx)));

        // cannot win initiative at all
        $die1->value = 1;
        $die2->value = 3;
        $die3->value = 4;
        $die4->value = 5;

        $this->assertFalse($this->object->react_to_initiative(
                              array('activeDieArrayArray' => $activeDieArrayArray,
                                    'playerIdx' => $playerIdx)));

        // now test with multiple focus dice
        $playerIdx = 0;

        // can win initiative by changing two dice
        $die1->value = 6;
        $die2->value = 6;
        $die3->value = 2;
        $die4->value = 2;

        $this->assertTrue($this->object->react_to_initiative(
                              array('activeDieArrayArray' => $activeDieArrayArray,
                                    'playerIdx' => $playerIdx)));

        // cannot win initiative unambiguously
        $die1->value = 5;
        $die2->value = 5;
        $die3->value = 1;
        $die4->value = 1;

        $this->assertFalse($this->object->react_to_initiative(
                              array('activeDieArrayArray' => $activeDieArrayArray,
                                    'playerIdx' => $playerIdx)));

        // now test with unequal number of dice
        $activeDieArrayArray = array(array($die1, $die2), array($die3));
        $playerIdx = 0;

        // win initiative by having more dice
        $die1->value = 5;
        $die2->value = 5;
        $die3->value = 1;

        $this->assertTrue($this->object->react_to_initiative(
                              array('activeDieArrayArray' => $activeDieArrayArray,
                                    'playerIdx' => $playerIdx)));
    }
}
