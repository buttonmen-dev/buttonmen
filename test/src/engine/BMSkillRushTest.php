<?php

class BMSkillRushTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillRush
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillRush;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillRush::react_to_initiative
     */
    public function testReact_to_initiative()
    {
        $die1 = BMDie::create(6);
        $die2 = BMDie::create(6);
        $die3 = BMDie::create(6);
        $die4 = BMDie::create(6);
        $die1->add_skill('Rush');
        $die2->add_skill('Rush');
        $die4->add_skill('Rush');

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

    /**
     * @covers BMSkillRush::score_value
     */
    public function testScore_value() {
        $die = BMDie::create(4);
        $die->add_skill('Rush');

        $this->assertCount(2, $die->hookList);
        $this->assertEquals(array('react_to_initiative', 'score_value'), array_keys($die->hookList));
        $this->assertEquals(array('BMSkillRush'), $die->hookList['react_to_initiative']);
        $this->assertEquals(array('BMSkillRush'), $die->hookList['score_value']);

        $die->captured = FALSE;
        $this->assertEquals(0, $die->get_scoreValueTimesTen());

        $die->captured = TRUE;
        $this->assertEquals(40, $die->get_scoreValueTimesTen());
    }
}
