<?php

class BMSkillValueTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillValue
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillValue;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillValue::score_value
     */
    public function testScore_value() {
        $die = BMDie::create(4);
        $die->add_skill('Value');
        $this->assertNull($die->value);

        $this->assertCount(2, $die->hookList);
        $this->assertEquals(array('score_value', 'capture'), array_keys($die->hookList));
        $this->assertEquals(array('BMSkillValue'), $die->hookList['score_value']);
        $this->assertEquals(array('BMSkillValue'), $die->hookList['capture']);
        $this->assertNull($die->get_scoreValueTimesTen());
        $die->captured = TRUE;
        $this->assertNull($die->get_scoreValueTimesTen());

        $die->captured = FALSE;
        $die->value = 3;
        $this->assertEquals(15, $die->get_scoreValueTimesTen());

        $die->captured = TRUE;
        $this->assertEquals(30, $die->get_scoreValueTimesTen());
    }

    /**
     * @covers BMSkillValue::capture
     */
    public function testCapture() {
        // load buttons
        $button1 = new BMButton;
        $button1->load('v(2)', 'Test1');
        $this->assertEquals(array('score_value', 'capture'),
                            array_keys($button1->dieArray[0]->hookList));
        $this->assertEquals(array('BMSkillValue'),
                            $button1->dieArray[0]->hookList['score_value']);
        $this->assertEquals(array('BMSkillValue'),
                            $button1->dieArray[0]->hookList['capture']);

        $button2 = new BMButton;
        $button2->load('(4) (6)', 'Test2');
        $this->assertEquals(array(),
                            array_keys($button2->dieArray[0]->hookList));

        // load game
        $game = new BMGame(535353, array(234, 567), array('', ''), 2);
        $this->assertEquals(BMGameState::startGame, $game->gameState);
        $game->buttonArray = array($button1, $button2);

        $game->waitingOnActionArray = array(FALSE, FALSE);
        $game->proceed_to_next_user_action();
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);
        $this->assertEquals(BMGameState::startTurn, $game->gameState);

        // artificially set initiative and values manually
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 1;
        $dieArrayArray[1][0]->value = 1;
        $dieArrayArray[1][1]->value = 5;
        $game->playerWithInitiativeIdx = 0;
        $game->activePlayerIdx = 0;

        $this->assertEquals(array(0.5, 5), $game->roundScoreArray);

        // perform attack
        $game->attack = array(0,        // attackerPlayerIdx
                              1,        // defenderPlayerIdx
                              array(0), // attackerAttackDieIdxArray
                              array(0), // defenderAttackDieIdxArray
                              'power'); // attackType
        $game->proceed_to_next_user_action();

        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::startTurn, $game->gameState);
        $this->assertCount(1, $game->activeDieArrayArray[0]);
        $this->assertCount(1, $game->activeDieArrayArray[1]);
        $this->assertCount(1, $game->capturedDieArrayArray[0]);
        $this->assertCount(0, $game->capturedDieArrayArray[1]);
        $this->assertEquals(4, $game->capturedDieArrayArray[0][0]->max);
        $this->assertEquals(1, $game->capturedDieArrayArray[0][0]->value);
        $this->assertEquals(array('score_value', 'capture'),
                            array_keys($game->capturedDieArrayArray[0][0]->hookList));
        $this->assertEquals(array('BMSkillValue'),
                            $game->capturedDieArrayArray[0][0]->hookList['score_value']);
        $this->assertEquals(array('BMSkillValue'),
                            $game->capturedDieArrayArray[0][0]->hookList['capture']);

        // artificially set rolled value manually
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 1;

        $this->assertEquals(array(1.5, 3), $game->roundScoreArray);
    }
}

?>
