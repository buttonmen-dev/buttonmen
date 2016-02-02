<?php

class BMSkillNullTest extends PHPUnit_Framework_TestCase {
    /**
     * @var BMSkillNull
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new BMSkillNull;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers BMSkillNull::score_value
     */
    public function testScore_value() {
        $die = BMDie::create(4);
        $die->add_skill('Null');

        $this->assertCount(2, $die->hookList);
        $this->assertEquals(array('score_value', 'capture'), array_keys($die->hookList));
        $this->assertEquals(array('BMSkillNull'), $die->hookList['score_value']);
        $this->assertEquals(array('BMSkillNull'), $die->hookList['capture']);

        $die->captured = FALSE;
        $this->assertEquals(0, $die->get_scoreValueTimesTen());

        $die->captured = TRUE;
        $this->assertEquals(0, $die->get_scoreValueTimesTen());
    }

    /**
     * @covers BMSkillNull::capture
     */
    public function testCapture() {
        // load buttons
        $button1 = new BMButton;
        $button1->load('n(2)', 'Test1');
        $this->assertEquals(array('score_value', 'capture'),
                            array_keys($button1->dieArray[0]->hookList));
        $this->assertEquals(array('BMSkillNull'),
                            $button1->dieArray[0]->hookList['score_value']);
        $this->assertEquals(array('BMSkillNull'),
                            $button1->dieArray[0]->hookList['capture']);

        $button2 = new BMButton;
        $button2->load('(4) (6)', 'Test2');
        $this->assertEquals(array(),
                            array_keys($button2->dieArray[0]->hookList));

        // load game
        $game = new BMGame(535353, array(234, 567), array('', ''), 2);
        $game->hasPlayerAcceptedGameArray = array(TRUE, TRUE);
        $this->assertEquals(BMGameState::START_GAME, $game->gameState);
        $game->buttonArray = array($button1, $button2);

        $game->waitingOnActionArray = array(FALSE, FALSE);
        $game->proceed_to_next_user_action();
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);

        // artificially set initiative and values manually
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 1;
        $dieArrayArray[1][0]->value = 1;
        $dieArrayArray[1][1]->value = 5;
        $game->playerWithInitiativeIdx = 0;
        $game->activePlayerIdx = 0;

        $this->assertEquals(array(0, 5), $game->roundScoreArray);

        // perform attack
        $game->attack = array(0,        // attackerPlayerIdx
                              1,        // defenderPlayerIdx
                              array(0), // attackerAttackDieIdxArray
                              array(0), // defenderAttackDieIdxArray
                              'Power'); // attackType
        $game->proceed_to_next_user_action();

        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertCount(1, $game->activeDieArrayArray[0]);
        $this->assertCount(1, $game->activeDieArrayArray[1]);
        $this->assertCount(1, $game->capturedDieArrayArray[0]);
        $this->assertCount(0, $game->capturedDieArrayArray[1]);
        $this->assertEquals(4, $game->capturedDieArrayArray[0][0]->max);
        $this->assertEquals(1, $game->capturedDieArrayArray[0][0]->value);
        $this->assertEquals(array('score_value', 'capture'),
                            array_keys($game->capturedDieArrayArray[0][0]->hookList));
        $this->assertEquals(array('BMSkillNull'),
                            $game->capturedDieArrayArray[0][0]->hookList['score_value']);
        $this->assertEquals(array('BMSkillNull'),
                            $game->capturedDieArrayArray[0][0]->hookList['capture']);

        // artificially set rolled value manually
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 1;

        $this->assertEquals(array(0, 3), $game->roundScoreArray);
    }
}
