<?php

class BMInterfaceTest extends PHPUnit_Framework_TestCase {

    /**
     * @var BMInterface
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        require 'src/database/mysql.test.inc.php';
        $this->object = new BMInterface(TRUE);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @covers BMInterface::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_and_load_new_game() {
        $gameId = $this->object->create_game(array(1, 2), array('Bauer', 'Stark'), 4);
        $game = $this->object->load_game($gameId);

        // check player info
        $this->assertEquals(2, count($game->playerIdArray));
        $this->assertEquals(2, $game->nPlayers);
        $this->assertEquals(1, $game->playerIdArray[0]);
        $this->assertEquals(2, $game->playerIdArray[1]);
        $this->assertEquals(BMGameState::startGame, $game->gameState);
        $this->assertFalse(isset($game->activePlayerIdx));
        $this->assertFalse(isset($game->playerWithInitiativeIdx));
        $this->assertFalse(isset($game->attackerPlayerIdx));
        $this->assertFalse(isset($game->defenderPlayerIdx));
        $this->assertEquals(array(FALSE, FALSE), $game->isPrevRoundWinnerArray);

        // check buttons
        $this->assertEquals(2, count($game->buttonArray));
        $this->assertTrue(is_a($game->buttonArray[0], 'BMButton'));
        $this->assertEquals('Bauer', $game->buttonArray[0]->name);
        $this->assertEquals('(8) (10) (12) (20) (X)', $game->buttonArray[0]->recipe);

        $this->assertTrue(is_a($game->buttonArray[1], 'BMButton'));
        $this->assertEquals('Stark', $game->buttonArray[1]->name);
        $this->assertEquals('(4) (6) (8) (X) (X)', $game->buttonArray[1]->recipe);

        // check dice
        $this->assertFalse(isset($game->activeDieArrayArray));
        $this->assertFalse(isset($game->attackerAllDieArray));
        $this->assertFalse(isset($game->defenderAllDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->auxiliaryDieDecisionArrayArray));
        $this->assertFalse(isset($game->capturedDieArrayArray));
        $this->assertFalse(isset($game->swingRequestArrayArray));
        $this->assertFalse(isset($game->swingValueArrayArray));
        $this->assertFalse($game->allValuesSpecified);

        // check swing details
        $this->assertFalse(isset($game->swingRequestArrayArray));
        $this->assertFalse(isset($game->swingValueArrayArray));
        $this->assertFalse($game->allValuesSpecified);

        // check round info
        $this->assertEquals(1, $game->roundNumber);
        $this->assertEquals(4, $game->maxWins);

        // check action info
        $this->assertFalse(isset($game->attack));
        $this->assertFalse(isset($game->passStatusArray));
        $this->assertEquals(array(FALSE, FALSE), $game->waitingOnActionArray);

        // check score
        $this->assertFalse(isset($game->roundScoreArray));
        $this->assertEquals(2, count($game->gameScoreArrayArray));
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['W']);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['L']);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['D']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['W']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['L']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['D']);
    }

    /**
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_load_game_waiting_for_swing_values() {
        $gameId = $this->object->create_game(array(1, 2), array('Bauer', 'Stark'), 4);
        $game = $this->object->load_game($gameId);
        $game->proceed_to_next_user_action();
        $this->assertEquals(BMGameState::specifyDice, $game->gameState);
        $this->object->save_game($game);

        $game = $this->object->load_game($gameId);

        // check player info
        $this->assertEquals(2, count($game->playerIdArray));
        $this->assertEquals(2, $game->nPlayers);
        $this->assertEquals(1, $game->playerIdArray[0]);
        $this->assertEquals(2, $game->playerIdArray[1]);
        $this->assertEquals(BMGameState::specifyDice, $game->gameState);
        $this->assertFalse(isset($game->activePlayerIdx));
        $this->assertFalse(isset($game->playerWithInitiativeIdx));
        $this->assertFalse(isset($game->attackerPlayerIdx));
        $this->assertFalse(isset($game->defenderPlayerIdx));
        $this->assertEquals(array(FALSE, FALSE), $game->isPrevRoundWinnerArray);

        // check buttons
        $this->assertEquals(2, count($game->buttonArray));
        $this->assertTrue(is_a($game->buttonArray[0], 'BMButton'));
        $this->assertEquals('Bauer', $game->buttonArray[0]->name);
        $this->assertEquals('(8) (10) (12) (20) (X)', $game->buttonArray[0]->recipe);

        $this->assertTrue(is_a($game->buttonArray[1], 'BMButton'));
        $this->assertEquals('Stark', $game->buttonArray[1]->name);
        $this->assertEquals('(4) (6) (8) (X) (X)', $game->buttonArray[1]->recipe);

        // check dice
        $this->assertTrue(isset($game->activeDieArrayArray));
        $this->assertEquals(2, count($game->activeDieArrayArray));

        $this->assertEquals(5, count($game->activeDieArrayArray[0]));

        $this->assertEquals(8, $game->activeDieArrayArray[0][0]->max);
        $this->assertEquals('8', $game->activeDieArrayArray[0][0]->recipe);
        $this->assertTrue(isset($game->activeDieArrayArray[0][0]->value));

        $this->assertEquals(10, $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals('10', $game->activeDieArrayArray[0][1]->recipe);
        $this->assertTrue(isset($game->activeDieArrayArray[0][1]->value));

        $this->assertEquals(12, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals('12', $game->activeDieArrayArray[0][2]->recipe);
        $this->assertTrue(isset($game->activeDieArrayArray[0][2]->value));

        $this->assertEquals(20, $game->activeDieArrayArray[0][3]->max);
        $this->assertEquals('20', $game->activeDieArrayArray[0][3]->recipe);
        $this->assertTrue(isset($game->activeDieArrayArray[0][3]->value));

        $this->assertFalse(isset($game->activeDieArrayArray[0][4]->max));
        $this->assertEquals('X', $game->activeDieArrayArray[0][4]->recipe);
        $this->assertFalse(isset($game->activeDieArrayArray[0][4]->value));

        $this->assertEquals(5, count($game->activeDieArrayArray[1]));

        $this->assertEquals(4, $game->activeDieArrayArray[1][0]->max);
        $this->assertEquals('4', $game->activeDieArrayArray[1][0]->recipe);
        $this->assertTrue(isset($game->activeDieArrayArray[1][0]->value));

        $this->assertEquals(6, $game->activeDieArrayArray[1][1]->max);
        $this->assertEquals('6', $game->activeDieArrayArray[1][1]->recipe);
        $this->assertTrue(isset($game->activeDieArrayArray[1][1]->value));

        $this->assertEquals(8, $game->activeDieArrayArray[1][2]->max);
        $this->assertEquals('8', $game->activeDieArrayArray[1][2]->recipe);
        $this->assertTrue(isset($game->activeDieArrayArray[1][2]->value));

        $this->assertFalse(isset($game->activeDieArrayArray[1][3]->max));
        $this->assertEquals('X', $game->activeDieArrayArray[1][3]->recipe);
        $this->assertFalse(isset($game->activeDieArrayArray[1][3]->value));

        $this->assertFalse(isset($game->activeDieArrayArray[1][4]->max));
        $this->assertEquals('X', $game->activeDieArrayArray[1][4]->recipe);
        $this->assertFalse(isset($game->activeDieArrayArray[1][4]->value));

        $this->assertFalse(isset($game->attackerAllDieArray));
        $this->assertFalse(isset($game->defenderAllDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->auxiliaryDieDecisionArrayArray));
//        $this->assertFalse(isset($game->capturedDieArrayArray));

        // check swing details
//        $this->assertTrue(isset($game->swingRequestArrayArray));
//        $this->assertFalse(isset($game->swingValueArrayArray));
//        $this->assertFalse($game->allValuesSpecified);

        // check round info
        $this->assertEquals(1, $game->roundNumber);
        $this->assertEquals(4, $game->maxWins);

        // check action info
        $this->assertFalse(isset($game->attack));
        $this->assertFalse(isset($game->passStatusArray));
//        $this->assertEquals(array(FALSE, FALSE), $game->waitingOnActionArray);

        // check score
        $this->assertFalse(isset($game->roundScoreArray));
        $this->assertEquals(2, count($game->gameScoreArrayArray));
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['W']);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['L']);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['D']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['W']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['L']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['D']);
    }
}
