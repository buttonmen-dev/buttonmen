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

        $expectedRecipes = array(array('8', '10', '12', '20', 'X'),
                                 array('4', '6', '8', 'X', 'X'));
        $expectedSizes = array(array(8, 10, 12, 20, NAN),
                               array(4, 6, 8, NAN, NAN));
        foreach ($game->activeDieArrayArray as $playerIdx => $activeDieArray) {
            $this->assertEquals(count($expectedRecipes[$playerIdx]),
                                count($activeDieArray));
            for ($dieIdx = 0; $dieIdx <= 4; $dieIdx++) {
                $this->assertEquals($expectedRecipes[$playerIdx][$dieIdx],
                                    $activeDieArray[$dieIdx]->recipe);
                if (is_nan($expectedSizes[$playerIdx][$dieIdx])) {
                    $this->assertFalse(isset($activeDieArray[$dieIdx]->max));
                    $this->assertFalse(isset($activeDieArray[$dieIdx]->value));
                } else {
                    $this->assertEquals($expectedSizes[$playerIdx][$dieIdx],
                                        $activeDieArray[$dieIdx]->max);
                    $this->assertTrue(isset($activeDieArray[$dieIdx]->value));
                }
            }
        }

        $this->assertFalse(isset($game->attackerAllDieArray));
        $this->assertFalse(isset($game->defenderAllDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->auxiliaryDieDecisionArrayArray));
//        $this->assertFalse(isset($game->capturedDieArrayArray));

        // check swing details
        $this->assertTrue(isset($game->swingRequestArrayArray));
        $this->assertEquals(2, count($game->swingRequestArrayArray));
        $this->assertEquals(1, count($game->swingRequestArrayArray[0]));
        $this->assertTrue(array_key_exists('X', $game->swingRequestArrayArray[0]));
        $this->assertEquals(1, count($game->swingRequestArrayArray[0]['X']));
        $this->assertTrue($game->swingRequestArrayArray[0]['X'][0] instanceof BMDieSwing);
        $this->assertTrue($game->activeDieArrayArray[0][4] ===
                          $game->swingRequestArrayArray[0]['X'][0]);

        $this->assertEquals(1, count($game->swingRequestArrayArray[1]));
        $this->assertTrue(array_key_exists('X', $game->swingRequestArrayArray[1]));
        $this->assertEquals(2, count($game->swingRequestArrayArray[1]['X']));
        $this->assertTrue($game->swingRequestArrayArray[1]['X'][0] instanceof BMDieSwing);
        $this->assertTrue($game->swingRequestArrayArray[1]['X'][1] instanceof BMDieSwing);
        $this->assertTrue($game->activeDieArrayArray[1][3] ===
                          $game->swingRequestArrayArray[1]['X'][0]);
        $this->assertTrue($game->activeDieArrayArray[1][4] ===
                          $game->swingRequestArrayArray[1]['X'][1]);

        $this->assertTrue(isset($game->swingValueArrayArray));
        $this->assertEquals(array(array('X' => NULL), array('X' => NULL)),
                            $game->swingValueArrayArray);
        $this->assertFalse($game->allValuesSpecified);

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
