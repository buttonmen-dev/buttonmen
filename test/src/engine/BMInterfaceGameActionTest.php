<?php

require_once 'BMInterfaceTestAbstract.php';

class BMInterfaceGameActionTest extends BMInterfaceTestAbstract {

    protected function init() {
        $this->object = new BMInterfaceGameAction(TRUE);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGameAction::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_and_load_new_game() {
        $retval = $this->object->gameAction()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Bauer', 'Stark'),
            4
        );
        $gameId = $retval['gameId'];

        $game = self::load_game($gameId);

        // check player info
        $this->assertCount(2, $game->playerIdArray);
        $this->assertEquals(2, $game->nPlayers);
        $this->assertEquals(self::$userId1WithoutAutopass, $game->playerIdArray[0]);
        $this->assertEquals(self::$userId2WithoutAutopass, $game->playerIdArray[1]);
        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $this->assertFalse(isset($game->activePlayerIdx));
        $this->assertFalse(isset($game->playerWithInitiativeIdx));
        $this->assertFalse(isset($game->attackerPlayerIdx));
        $this->assertFalse(isset($game->defenderPlayerIdx));
        $this->assertEquals(array(FALSE, FALSE), $game->isPrevRoundWinnerArray);

        // check buttons
        $this->assertCount(2, $game->buttonArray);
        $this->assertTrue(is_a($game->buttonArray[0], 'BMButton'));
        $this->assertEquals('Bauer', $game->buttonArray[0]->name);
        $this->assertEquals('(8) (10) (12) (20) (X)', $game->buttonArray[0]->recipe);

        $this->assertTrue(is_a($game->buttonArray[1], 'BMButton'));
        $this->assertEquals('Stark', $game->buttonArray[1]->name);
        $this->assertEquals('(4) (6) (8) (X) (X)', $game->buttonArray[1]->recipe);

        // check dice
        $this->assertCount(2, $game->activeDieArrayArray);

        $expectedRecipes = array(array('(8)', '(10)', '(12)', '(20)', '(X)'),
                                 array('(4)', '(6)', '(8)', '(X)', '(X)'));
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
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);

        // check swing details
        $this->assertTrue(isset($game->swingRequestArrayArray));
        $this->assertCount(2, $game->swingRequestArrayArray);
        $this->assertCount(1, $game->swingRequestArrayArray[0]);
        $this->assertTrue(array_key_exists('X', $game->swingRequestArrayArray[0]));
        $this->assertCount(1, $game->swingRequestArrayArray[0]['X']);
        $this->assertTrue($game->swingRequestArrayArray[0]['X'][0] instanceof BMDieSwing);
        $this->assertTrue($game->activeDieArrayArray[0][4] ===
                          $game->swingRequestArrayArray[0]['X'][0]);

        $this->assertCount(1, $game->swingRequestArrayArray[1]);
        $this->assertTrue(array_key_exists('X', $game->swingRequestArrayArray[1]));
        $this->assertCount(2, $game->swingRequestArrayArray[1]['X']);
        $this->assertTrue($game->swingRequestArrayArray[1]['X'][0] instanceof BMDieSwing);
        $this->assertTrue($game->swingRequestArrayArray[1]['X'][1] instanceof BMDieSwing);
        $this->assertTrue($game->activeDieArrayArray[1][3] ===
                          $game->swingRequestArrayArray[1]['X'][0]);
        $this->assertTrue($game->activeDieArrayArray[1][4] ===
                          $game->swingRequestArrayArray[1]['X'][1]);

        $this->assertTrue(isset($game->swingValueArrayArray));
        $this->assertEquals(array(array('X' => NULL), array('X' => NULL)),
                            $game->swingValueArrayArray);

        // check round info
        $this->assertEquals(1, $game->roundNumber);
        $this->assertEquals(4, $game->maxWins);

        // check action info
        $this->assertFalse(isset($game->attack));
        $this->assertEquals(0, $game->nRecentPasses);
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);

        // check score
        $this->assertFalse(isset($game->roundScoreArray));
        $this->assertCount(2, $game->gameScoreArrayArray);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['W']);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['L']);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['D']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['W']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['L']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['D']);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGameAction::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_ornery_mood_swing() {
        $retval = $this->object->gameAction()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Skeeve', 'Skeeve'),
            4
        );
        $gameId = $retval['gameId'];

        $game = self::load_game($gameId);

        // check player info
        $this->assertCount(2, $game->playerIdArray);
        $this->assertEquals(2, $game->nPlayers);
        $this->assertEquals(self::$userId1WithoutAutopass, $game->playerIdArray[0]);
        $this->assertEquals(self::$userId2WithoutAutopass, $game->playerIdArray[1]);
        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $this->assertFalse(isset($game->activePlayerIdx));
        $this->assertFalse(isset($game->playerWithInitiativeIdx));
        $this->assertFalse(isset($game->attackerPlayerIdx));
        $this->assertFalse(isset($game->defenderPlayerIdx));
        $this->assertEquals(array(FALSE, FALSE), $game->isPrevRoundWinnerArray);

        // check buttons
        $this->assertCount(2, $game->buttonArray);
        $this->assertTrue(is_a($game->buttonArray[0], 'BMButton'));
        $this->assertEquals('Skeeve', $game->buttonArray[0]->name);
        $this->assertEquals('o(V)? o(W)? o(X)? o(Y)? o(Z)?', $game->buttonArray[0]->recipe);

        $this->assertTrue(is_a($game->buttonArray[1], 'BMButton'));
        $this->assertEquals('Skeeve', $game->buttonArray[1]->name);
        $this->assertEquals('o(V)? o(W)? o(X)? o(Y)? o(Z)?', $game->buttonArray[1]->recipe);

        // check dice
        $this->assertCount(2, $game->activeDieArrayArray);

        $expectedRecipes = array(array('o(V)?', 'o(W)?', 'o(X)?', 'o(Y)?', 'o(Z)?'),
                                 array('o(V)?', 'o(W)?', 'o(X)?', 'o(Y)?', 'o(Z)?'));
        foreach ($game->activeDieArrayArray as $playerIdx => $activeDieArray) {
            $this->assertEquals(count($expectedRecipes[$playerIdx]),
                                count($activeDieArray));
            for ($dieIdx = 0; $dieIdx <= 4; $dieIdx++) {
                $this->assertEquals($expectedRecipes[$playerIdx][$dieIdx],
                                    $activeDieArray[$dieIdx]->recipe);
                $this->assertFalse(isset($activeDieArray[$dieIdx]->max));
                $this->assertFalse(isset($activeDieArray[$dieIdx]->value));
            }
        }

        $this->assertFalse(isset($game->attackerAllDieArray));
        $this->assertFalse(isset($game->defenderAllDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->auxiliaryDieDecisionArrayArray));
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);

        // check swing details
        $this->assertTrue(isset($game->swingRequestArrayArray));
        $this->assertCount(2, $game->swingRequestArrayArray);
        $this->assertCount(5, $game->swingRequestArrayArray[0]);
        $this->assertTrue(array_key_exists('V', $game->swingRequestArrayArray[0]));
        $this->assertTrue(array_key_exists('W', $game->swingRequestArrayArray[0]));
        $this->assertTrue(array_key_exists('X', $game->swingRequestArrayArray[0]));
        $this->assertTrue(array_key_exists('Y', $game->swingRequestArrayArray[0]));
        $this->assertTrue(array_key_exists('Z', $game->swingRequestArrayArray[0]));
        $this->assertCount(1, $game->swingRequestArrayArray[0]['V']);
        $this->assertCount(1, $game->swingRequestArrayArray[0]['W']);
        $this->assertCount(1, $game->swingRequestArrayArray[0]['X']);
        $this->assertCount(1, $game->swingRequestArrayArray[0]['Y']);
        $this->assertCount(1, $game->swingRequestArrayArray[0]['Z']);
        $this->assertTrue($game->swingRequestArrayArray[0]['V'][0] instanceof BMDieSwing);
        $this->assertTrue($game->swingRequestArrayArray[0]['W'][0] instanceof BMDieSwing);
        $this->assertTrue($game->swingRequestArrayArray[0]['X'][0] instanceof BMDieSwing);
        $this->assertTrue($game->swingRequestArrayArray[0]['Y'][0] instanceof BMDieSwing);
        $this->assertTrue($game->swingRequestArrayArray[0]['Z'][0] instanceof BMDieSwing);
        $this->assertTrue($game->activeDieArrayArray[0][0] ===
                          $game->swingRequestArrayArray[0]['V'][0]);
        $this->assertTrue($game->activeDieArrayArray[0][1] ===
                          $game->swingRequestArrayArray[0]['W'][0]);
        $this->assertTrue($game->activeDieArrayArray[0][2] ===
                          $game->swingRequestArrayArray[0]['X'][0]);
        $this->assertTrue($game->activeDieArrayArray[0][3] ===
                          $game->swingRequestArrayArray[0]['Y'][0]);
        $this->assertTrue($game->activeDieArrayArray[0][4] ===
                          $game->swingRequestArrayArray[0]['Z'][0]);

        $this->assertCount(5, $game->swingRequestArrayArray[1]);
        $this->assertTrue(array_key_exists('V', $game->swingRequestArrayArray[1]));
        $this->assertTrue(array_key_exists('W', $game->swingRequestArrayArray[1]));
        $this->assertTrue(array_key_exists('X', $game->swingRequestArrayArray[1]));
        $this->assertTrue(array_key_exists('Y', $game->swingRequestArrayArray[1]));
        $this->assertTrue(array_key_exists('Z', $game->swingRequestArrayArray[1]));
        $this->assertCount(1, $game->swingRequestArrayArray[1]['V']);
        $this->assertCount(1, $game->swingRequestArrayArray[1]['W']);
        $this->assertCount(1, $game->swingRequestArrayArray[1]['X']);
        $this->assertCount(1, $game->swingRequestArrayArray[1]['Y']);
        $this->assertCount(1, $game->swingRequestArrayArray[1]['Z']);
        $this->assertTrue($game->swingRequestArrayArray[1]['V'][0] instanceof BMDieSwing);
        $this->assertTrue($game->swingRequestArrayArray[1]['W'][0] instanceof BMDieSwing);
        $this->assertTrue($game->swingRequestArrayArray[1]['X'][0] instanceof BMDieSwing);
        $this->assertTrue($game->swingRequestArrayArray[1]['Y'][0] instanceof BMDieSwing);
        $this->assertTrue($game->swingRequestArrayArray[1]['Z'][0] instanceof BMDieSwing);
        $this->assertTrue($game->activeDieArrayArray[1][0] ===
                          $game->swingRequestArrayArray[1]['V'][0]);
        $this->assertTrue($game->activeDieArrayArray[1][1] ===
                          $game->swingRequestArrayArray[1]['W'][0]);
        $this->assertTrue($game->activeDieArrayArray[1][2] ===
                          $game->swingRequestArrayArray[1]['X'][0]);
        $this->assertTrue($game->activeDieArrayArray[1][3] ===
                          $game->swingRequestArrayArray[1]['Y'][0]);
        $this->assertTrue($game->activeDieArrayArray[1][4] ===
                          $game->swingRequestArrayArray[1]['Z'][0]);

        $this->assertTrue(isset($game->swingValueArrayArray));
        $this->assertEquals(array(
                                array('V' => NULL, 'W' => NULL, 'X' => NULL, 'Y' => NULL, 'Z' => NULL),
                                array('V' => NULL, 'W' => NULL, 'X' => NULL, 'Y' => NULL, 'Z' => NULL)
                            ),
                            $game->swingValueArrayArray);

        // check that swing values are set correctly
        $this->object->submit_die_values(
            self::$userId1WithoutAutopass,
            $game->gameId,
            1,
            array('V' => 6, 'W' => 7, 'X' => 8, 'Y' => 9, 'Z' => 10),
            array()
        );

        $game = self::load_game($game->gameId);

        $this->assertEquals(array(
                                array('V' => 6,    'W' => 7,    'X' => 8,    'Y' => 9,    'Z' => 10),
                                array('V' => NULL, 'W' => NULL, 'X' => NULL, 'Y' => NULL, 'Z' => NULL)
                            ),
                            $game->swingValueArrayArray);

        // check round info
        $this->assertEquals(1, $game->roundNumber);
        $this->assertEquals(4, $game->maxWins);

        // check action info
        $this->assertFalse(isset($game->attack));
        $this->assertEquals(0, $game->nRecentPasses);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGameAction::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_one_random_button() {
        $retval = $this->object->gameAction()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Coil', '__random'),
            4
        );
        $gameId = $retval['gameId'];

        $game = self::load_game($gameId);
        $this->assertFalse(empty($game->buttonArray[0]));
        $this->assertEquals('Coil', $game->buttonArray[0]->name);
        $this->assertFalse(empty($game->buttonArray[1]));
        $this->assertNotEquals('__random', $game->buttonArray[1]->name);
        $this->assertGreaterThan(BMGameState::START_GAME, $game->gameState);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGameAction::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_one_random_button_and_one_unspecified() {
        $retval = $this->object->gameAction()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('__random', NULL),
            4
        );
        $gameId = $retval['gameId'];

        $game = self::load_game($gameId);
        $this->assertTrue(empty($game->buttonArray[0]));
        $this->assertTrue($game->isButtonChoiceRandom[0]);
        $this->assertTrue(empty($game->buttonArray[1]));
        $this->assertFalse($game->isButtonChoiceRandom[1]);

        self::save_game($game);
        $game = self::load_game($gameId);
        $this->assertTrue(empty($game->buttonArray[0]));
        $this->assertTrue($game->isButtonChoiceRandom[0]);
        $this->assertTrue(empty($game->buttonArray[1]));
        $this->assertFalse($game->isButtonChoiceRandom[1]);
        $this->assertEquals(BMGameState::START_GAME, $game->gameState);

        $retval = $this->object->select_button(self::$userId2WithoutAutopass, $gameId, '__random');
        $this->assertTrue($retval);
        $game = self::load_game($gameId);
        $this->assertFalse(empty($game->buttonArray[0]));
        $this->assertTrue($game->isButtonChoiceRandom[0]);
        $this->assertFalse(empty($game->buttonArray[1]));
        $this->assertTrue($game->isButtonChoiceRandom[1]);
        $this->assertGreaterThan(BMGameState::START_GAME, $game->gameState);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGameAction::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_two_random_buttons() {
        $retval = $this->object->gameAction()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('__random', '__random'),
            4
        );
        $gameId = $retval['gameId'];

        $game = self::load_game($gameId);
        $this->assertFalse(empty($game->buttonArray[0]->name));
        $this->assertNotEquals('__random', $game->buttonArray[0]->name);
        $this->assertFalse(empty($game->buttonArray[1]->name));
        $this->assertNotEquals('__random', $game->buttonArray[1]->name);
        $this->assertGreaterThan(BMGameState::START_GAME, $game->gameState);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGameAction::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_two_random_buttons_with_one_unspecified_player() {
        $retval = $this->object->gameAction()->create_game(
            array(self::$userId1WithoutAutopass, NULL),
            array('__random', '__random'),
            4
        );
        $gameId = $retval['gameId'];

        $game = self::load_game($gameId);
        $this->assertTrue(empty($game->buttonArray[0]));
        $this->assertTrue($game->isButtonChoiceRandom[0]);
        $this->assertTrue(empty($game->buttonArray[1]));
        $this->assertTrue($game->isButtonChoiceRandom[1]);
        $this->assertEquals(BMGameState::START_GAME, $game->gameState);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGameAction::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_and_load_new_game_with_empty_opponent() {
        $retval = $this->object->gameAction()->create_game(
            array(self::$userId1WithoutAutopass, NULL),
            array('Bauer', 'Stark'),
            4
        );
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        // check player info
        $this->assertCount(2, $game->playerIdArray);
        $this->assertEquals(2, $game->nPlayers);
        $this->assertEquals(self::$userId1WithoutAutopass, $game->playerIdArray[0]);
        $this->assertNull($game->playerIdArray[1]);
        $this->assertEquals(BMGameState::START_GAME, $game->gameState);
        $this->assertFalse(isset($game->activePlayerIdx));
        $this->assertFalse(isset($game->playerWithInitiativeIdx));
        $this->assertFalse(isset($game->attackerPlayerIdx));
        $this->assertFalse(isset($game->defenderPlayerIdx));
        $this->assertEquals(array(FALSE, FALSE), $game->isPrevRoundWinnerArray);

        // check buttons
        $this->assertCount(2, $game->buttonArray);
        $this->assertTrue(is_a($game->buttonArray[0], 'BMButton'));
        $this->assertEquals('Bauer', $game->buttonArray[0]->name);
        $this->assertEquals('(8) (10) (12) (20) (X)', $game->buttonArray[0]->recipe);

        $this->assertTrue(is_a($game->buttonArray[1], 'BMButton'));
        $this->assertEquals('Stark', $game->buttonArray[1]->name);
        $this->assertEquals('(4) (6) (8) (X) (X)', $game->buttonArray[1]->recipe);

        // check dice
        $this->assertCount(2, $game->activeDieArrayArray);
        $this->assertEquals(array(array(), array()), $game->activeDieArrayArray);

        $this->assertFalse(isset($game->attackerAllDieArray));
        $this->assertFalse(isset($game->defenderAllDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->auxiliaryDieDecisionArrayArray));
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);

        // check swing details
        $this->assertFalse(isset($game->swingRequestArrayArray));
        $this->assertEquals(array(array(), array()), $game->swingValueArrayArray);

        // check round info
        $this->assertEquals(1, $game->roundNumber);
        $this->assertEquals(4, $game->maxWins);

        // check action info
        $this->assertFalse(isset($game->attack));
        $this->assertEquals(0, $game->nRecentPasses);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);

        // check score
        $this->assertFalse(isset($game->roundScoreArray));
        $this->assertCount(2, $game->gameScoreArrayArray);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['W']);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['L']);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['D']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['W']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['L']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['D']);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGameAction::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_and_load_new_game_with_empty_button() {
        $retval = $this->object->gameAction()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Bauer', NULL),
            4
        );
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        // check player info
        $this->assertCount(2, $game->playerIdArray);
        $this->assertEquals(2, $game->nPlayers);
        $this->assertEquals(self::$userId1WithoutAutopass, $game->playerIdArray[0]);
        $this->assertEquals(self::$userId2WithoutAutopass, $game->playerIdArray[1]);
        $this->assertEquals(BMGameState::START_GAME, $game->gameState);
        $this->assertFalse(isset($game->activePlayerIdx));
        $this->assertFalse(isset($game->playerWithInitiativeIdx));
        $this->assertFalse(isset($game->attackerPlayerIdx));
        $this->assertFalse(isset($game->defenderPlayerIdx));
        $this->assertEquals(array(FALSE, FALSE), $game->isPrevRoundWinnerArray);

        // check buttons
        $this->assertCount(2, $game->buttonArray);
        $this->assertTrue(is_a($game->buttonArray[0], 'BMButton'));
        $this->assertEquals('Bauer', $game->buttonArray[0]->name);
        $this->assertEquals('(8) (10) (12) (20) (X)', $game->buttonArray[0]->recipe);

        $this->assertNull($game->buttonArray[1]);

        // check dice
        $this->assertCount(2, $game->activeDieArrayArray);
        $this->assertEquals(array(array(), array()), $game->activeDieArrayArray);

        $this->assertFalse(isset($game->attackerAllDieArray));
        $this->assertFalse(isset($game->defenderAllDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->auxiliaryDieDecisionArrayArray));
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);

        // check swing details
        $this->assertFalse(isset($game->swingRequestArrayArray));
        $this->assertEquals(array(array(), array()), $game->swingValueArrayArray);

        // check round info
        $this->assertEquals(1, $game->roundNumber);
        $this->assertEquals(4, $game->maxWins);

        // check action info
        $this->assertFalse(isset($game->attack));
        $this->assertEquals(0, $game->nRecentPasses);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);

        // check score
        $this->assertFalse(isset($game->roundScoreArray));
        $this->assertCount(2, $game->gameScoreArrayArray);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['W']);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['L']);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['D']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['W']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['L']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['D']);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGameAction::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_and_load_new_game_with_empty_opponent_and_opponent_button() {
        $retval = $this->object->gameAction()->create_game(
            array(self::$userId1WithoutAutopass, NULL),
            array('Bauer', NULL),
            4
        );
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        // check player info
        $this->assertCount(2, $game->playerIdArray);
        $this->assertEquals(2, $game->nPlayers);
        $this->assertEquals(self::$userId1WithoutAutopass, $game->playerIdArray[0]);
        $this->assertNull($game->playerIdArray[1]);
        $this->assertEquals(BMGameState::START_GAME, $game->gameState);
        $this->assertFalse(isset($game->activePlayerIdx));
        $this->assertFalse(isset($game->playerWithInitiativeIdx));
        $this->assertFalse(isset($game->attackerPlayerIdx));
        $this->assertFalse(isset($game->defenderPlayerIdx));
        $this->assertEquals(array(FALSE, FALSE), $game->isPrevRoundWinnerArray);

        // check buttons
        $this->assertCount(2, $game->buttonArray);
        $this->assertTrue(is_a($game->buttonArray[0], 'BMButton'));
        $this->assertEquals('Bauer', $game->buttonArray[0]->name);
        $this->assertEquals('(8) (10) (12) (20) (X)', $game->buttonArray[0]->recipe);

        $this->assertNull($game->buttonArray[1]);

        // check dice
        $this->assertCount(2, $game->activeDieArrayArray);
        $this->assertEquals(array(array(), array()), $game->activeDieArrayArray);

        $this->assertFalse(isset($game->attackerAllDieArray));
        $this->assertFalse(isset($game->defenderAllDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->auxiliaryDieDecisionArrayArray));
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);

        // check swing details
        $this->assertFalse(isset($game->swingRequestArrayArray));
        $this->assertEquals(array(array(), array()), $game->swingValueArrayArray);

        // check round info
        $this->assertEquals(1, $game->roundNumber);
        $this->assertEquals(4, $game->maxWins);

        // check action info
        $this->assertFalse(isset($game->attack));
        $this->assertEquals(0, $game->nRecentPasses);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);

        // check score
        $this->assertFalse(isset($game->roundScoreArray));
        $this->assertCount(2, $game->gameScoreArrayArray);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['W']);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['L']);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['D']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['W']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['L']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['D']);
    }

}
