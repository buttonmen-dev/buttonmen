<?php

require_once 'BMInterfaceTestAbstract.php';

class BMInterfaceGameTest extends BMInterfaceTestAbstract {

    protected function init() {
        $this->object = new BMInterfaceGame(TRUE);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGame::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_and_load_new_game() {
        $retval = $this->object->game()->create_game(
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
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);

        // check swing details
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
     * @covers BMInterfaceGame::create_game
     */
    public function test_create_self_game() {
        // attempt to create a game with the same player on both sides
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId1WithoutAutopass),
            array('Bauer', 'Stark'),
            4
        );
        $this->assertNull($retval);
        $this->assertEquals('Game create failed because a player has been selected more than once.',
                            $this->object->message);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGame::create_game
     */
    public function test_create_game_with_invalid_parameters() {
        // attempt to create a game with a non-integer number of max wins
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Bauer', 'Stark'),
            4.5
        );
        $this->assertNull($retval);
        $this->assertEquals('Game create failed because the maximum number of wins was invalid.',
                            $this->object->message);

        // attempt to create a game with a zero number of max wins
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Bauer', 'Stark'),
            0
        );
        $this->assertNull($retval);
        $this->assertEquals('Game create failed because the maximum number of wins was invalid.',
                            $this->object->message);

        // attempt to create a game with a large number of max wins
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Bauer', 'Stark'),
            6
        );
        $this->assertNull($retval);
        $this->assertEquals('Game create failed because the maximum number of wins was invalid.',
                            $this->object->message);

        // attempt to create a game with an invalid button name
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('KJQOERUCHC', 'Stark'),
            3
        );
        $this->assertNull($retval);
        $this->assertEquals('Game create failed because a button name was not valid.',
                            $this->object->message);
    }


    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGame::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_ornery_mood_swing() {
        $retval = $this->object->game()->create_game(
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
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);

        // check swing
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

        $this->assertEquals(array(
                                array('V' => NULL, 'W' => NULL, 'X' => NULL, 'Y' => NULL, 'Z' => NULL),
                                array('V' => NULL, 'W' => NULL, 'X' => NULL, 'Y' => NULL, 'Z' => NULL)
                            ),
                            $game->swingValueArrayArray);

        // check that swing values are set correctly
        $this->object->game()->submit_die_values(
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
     * @covers BMInterfaceGame::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_one_random_button() {
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Coil', '__random'),
            4
        );
        $gameId = $retval['gameId'];

        $game = self::load_game($gameId);

        $this->assertFalse(empty($game->playerArray[0]->button));
        $this->assertEquals('Coil', $game->playerArray[0]->button->name);
        $this->assertFalse(empty($game->playerArray[1]->button));
        $this->assertNotEquals('__random', $game->playerArray[1]->button->name);
        $this->assertGreaterThan(BMGameState::START_GAME, $game->gameState);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGame::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_one_random_button_and_one_unspecified() {
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('__random', NULL),
            4
        );
        $gameId = $retval['gameId'];

        $game = self::load_game($gameId);
        $this->assertTrue(empty($game->playerArray[0]->button));
        $this->assertTrue($game->playerArray[0]->isButtonChoiceRandom);
        $this->assertTrue(empty($game->playerArray[1]->button));
        $this->assertFalse($game->playerArray[1]->isButtonChoiceRandom);

        self::save_game($game);
        $game = self::load_game($gameId);
        $this->assertTrue(empty($game->playerArray[0]->button));
        $this->assertTrue($game->playerArray[0]->isButtonChoiceRandom);
        $this->assertTrue(empty($game->playerArray[1]->button));
        $this->assertFalse($game->playerArray[1]->isButtonChoiceRandom);
        $this->assertEquals(BMGameState::START_GAME, $game->gameState);

        $retval = $this->object->game()->select_button(
            self::$userId2WithoutAutopass,
            $gameId,
            '__random'
        );
        $this->assertTrue($retval);
        $game = self::load_game($gameId);
        $this->assertFalse(empty($game->playerArray[0]->button));
        $this->assertTrue($game->playerArray[0]->isButtonChoiceRandom);
        $this->assertFalse(empty($game->playerArray[1]->button));
        $this->assertTrue($game->playerArray[1]->isButtonChoiceRandom);
        $this->assertGreaterThan(BMGameState::START_GAME, $game->gameState);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGame::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_two_random_buttons() {
        $retval = $this->object->game()->create_game(
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
     * @covers BMInterfaceGame::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_two_random_buttons_with_one_unspecified_player() {
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, NULL),
            array('__random', '__random'),
            4
        );
        $gameId = $retval['gameId'];

        $game = self::load_game($gameId);
        $this->assertTrue(empty($game->buttonArray[0]));
        $this->assertTrue($game->playerArray[0]->isButtonChoiceRandom);
        $this->assertTrue(empty($game->buttonArray[1]));
        $this->assertTrue($game->playerArray[1]->isButtonChoiceRandom);
        $this->assertEquals(BMGameState::START_GAME, $game->gameState);
    }

    /**
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGame::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_and_load_new_game_with_empty_opponent() {
        $retval = $this->object->game()->create_game(
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
     * @covers BMInterfaceGame::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_and_load_new_game_with_empty_button() {
        $retval = $this->object->game()->create_game(
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
     * @covers BMInterfaceGame::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_and_load_new_game_with_empty_opponent_and_opponent_button() {
        $retval = $this->object->create_game(
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
     * @covers BMInterfaceGame::save_join_game_decision
     */
    public function test_save_join_game_decision() {
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Bauer', 'Stark'),
            4,
            '',
            NULL,
            NULL,
            FALSE
        );

        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);
        $this->assertCount(2, $game->hasPlayerAcceptedGameArray);
        $this->assertTrue($game->hasPlayerAcceptedGameArray[0]);
        $this->assertTrue($game->hasPlayerAcceptedGameArray[1]);

        $retval = $this->object->game()->create_game(
            array(self::$userId5WithoutAutoaccept, self::$userId2WithoutAutopass),
            array('Bauer', 'Stark'),
            4,
            '',
            NULL,
            NULL,
            FALSE
        );

        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);
        $this->assertCount(2, $game->hasPlayerAcceptedGameArray);
        $this->assertTrue($game->hasPlayerAcceptedGameArray[0]);
        $this->assertTrue($game->hasPlayerAcceptedGameArray[1]);

        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId5WithoutAutoaccept),
            array('Bauer', 'Stark'),
            4,
            '',
            NULL,
            NULL,
            FALSE
        );

        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);
        $this->assertCount(2, $game->hasPlayerAcceptedGameArray);
        $this->assertTrue($game->hasPlayerAcceptedGameArray[0]);
        $this->assertFalse($game->hasPlayerAcceptedGameArray[1]);

        $this->object->game()->save_join_game_decision(
            self::$userId5WithoutAutoaccept,
            $gameId,
            'accept'
        );
        $game = self::load_game($gameId);
        $this->assertTrue($game->hasPlayerAcceptedGameArray[0]);
        $this->assertTrue($game->hasPlayerAcceptedGameArray[1]);
    }


    /**
     * @covers BMInterfaceGame::join_open_game
     */
    public function test_join_open_game() {
        // create an open game with an unspecified opponent
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, NULL),
            array('Bauer', 'Stark'),
            4
        );
        $this->assertNotNull($retval);
        $this->object->game()->join_open_game(
            self::$userId2WithoutAutopass,
            $retval['gameId']
        );

        $game = self::load_game($retval['gameId']);
        $this->assertEquals(array(self::$userId1WithoutAutopass,
                                  self::$userId2WithoutAutopass),
                            $game->playerIdArray);
    }

    /**
     * @covers BMInterfaceGame::select_button
     */
    public function test_select_button() {
        // create an open game with an unspecified button
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Bauer', NULL),
            4
        );
        $this->assertNotNull($retval);
        $this->object->game()->select_button(
            self::$userId2WithoutAutopass,
            $retval['gameId'],
            'Iago'
        );

        $game = self::load_game($retval['gameId']);
        $this->assertEquals('Bauer', $game->buttonArray[0]->name);
        $this->assertEquals('(8) (10) (12) (20) (X)', $game->buttonArray[0]->recipe);
        $this->assertEquals('Iago', $game->buttonArray[1]->name);
        $this->assertEquals('(20) (20) (20) (X)', $game->buttonArray[1]->recipe);
    }

    /**
     * Check that a decline of an auxiliary die works correctly.
     *
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGame::react_to_auxiliary
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_react_to_auxiliary_both_aux_decline() {
        // Lancelot : (10) (12) (20) (20) (X) +(X)
        // Gawaine  :  (4)  (4) (12) (20) (X) +(6)
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Lancelot', 'Gawaine'),
            4
        );
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::CHOOSE_AUXILIARY_DICE, $game->gameState);
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertCount(6, $game->activeDieArrayArray[0]);
        $this->assertCount(6, $game->activeDieArrayArray[1]);

        // a non-player attempts an action
        $this->assertFalse(
            $this->object->game()->react_to_auxiliary(
                0,
                $gameId,
                'decline')
        );

        // player 1 attempts an invalid action
        $this->assertFalse(
            $this->object->game()->react_to_auxiliary(
                self::$userId1WithoutAutopass,
                $gameId,
                'rubbish')
        );

        // player 1 declines
        $this->assertTrue(
            $this->object->game()->react_to_auxiliary(
                self::$userId1WithoutAutopass,
                $gameId,
                'decline')
        );
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertCount(5, $game->activeDieArrayArray[0]);
        $this->assertCount(5, $game->activeDieArrayArray[1]);
        foreach ($game->activeDieArrayArray as $activeDieArray) {
            foreach ($activeDieArray as $die) {
                $this->assertFalse($die->has_skill('Auxiliary'));
            }
        }
        $this->assertEquals('(10) (12) (20) (20) (X)',
                            $game->buttonArray[0]->recipe);
        $this->assertEquals('(4) (4) (12) (20) (X)',
                            $game->buttonArray[1]->recipe);
    }

    /**
     * Check that courtesy auxiliary dice are given correctly.
     *
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGame::react_to_auxiliary
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     * @covers BMGame::add_selected_auxiliary_dice
     */
    public function test_react_to_auxiliary_one_aux_decline() {
        // Kublai   :  (4) (8) (12) (20) (X)
        // Gawaine  :  (4) (4) (12) (20) (X) +(6)
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Kublai', 'Gawaine'),
            4
        );
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::CHOOSE_AUXILIARY_DICE, $game->gameState);
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertCount(6, $game->activeDieArrayArray[0]);
        $this->assertCount(6, $game->activeDieArrayArray[1]);
        $this->assertTrue($game->activeDieArrayArray[0][5]->has_skill('Auxiliary'));

        // player 1 chooses to add auxiliary die
        $this->assertTrue(
            $this->object->game()->react_to_auxiliary(
                self::$userId1WithoutAutopass,
                $gameId,
                'add',
                5
            )
        );

        $game = self::load_game($gameId);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertCount(6, $game->activeDieArrayArray[0]);
        $this->assertCount(6, $game->activeDieArrayArray[1]);
        $this->assertTrue($game->activeDieArrayArray[0][5]->has_skill('Auxiliary'));
        $this->assertTrue($game->activeDieArrayArray[0][5]->has_flag('AddAuxiliary'));

        // player 1 tries incorrectly to act again
        $this->assertFalse(
            $this->object->game()->react_to_auxiliary(
                self::$userId1WithoutAutopass,
                $gameId,
                'add',
                5)
        );

        // player 2 declines
        $this->assertTrue(
            $this->object->game()->react_to_auxiliary(
                self::$userId2WithoutAutopass,
                $gameId,
                'decline')
            );
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertCount(5, $game->activeDieArrayArray[0]);
        $this->assertCount(5, $game->activeDieArrayArray[1]);
        foreach ($game->activeDieArrayArray as $activeDieArray) {
            foreach ($activeDieArray as $die) {
                $this->assertFalse($die->has_skill('Auxiliary'));
            }
        }
        $this->assertEquals('(4) (8) (12) (20) (X)',
                            $game->buttonArray[0]->recipe);
        $this->assertEquals('(4) (4) (12) (20) (X)',
                            $game->buttonArray[1]->recipe);
    }

    /**
     * Check that courtesy auxiliary dice are given correctly.
     *
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGame::react_to_auxiliary
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     * @covers BMGame::add_selected_auxiliary_dice
     */
    public function test_react_to_auxiliary_one_aux_accept() {
        // Kublai   :  (4) (8) (12) (20) (X)
        // Gawaine  :  (4) (4) (12) (20) (X) +(6)
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Kublai', 'Gawaine'),
            4
        );
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::CHOOSE_AUXILIARY_DICE, $game->gameState);
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertCount(6, $game->activeDieArrayArray[0]);
        $this->assertCount(6, $game->activeDieArrayArray[1]);
        $this->assertTrue($game->activeDieArrayArray[0][5]->has_skill('Auxiliary'));

        // player 1 tries incorrectly adding a non-auxiliary die
        $this->assertFalse(
            $this->object->game()->react_to_auxiliary(
                self::$userId1WithoutAutopass,
                $gameId,
                'add',
                0
            )
        );

        // player 1 chooses to add an auxiliary die
        $this->assertTrue(
            $this->object->game()->react_to_auxiliary(
                self::$userId1WithoutAutopass,
                $gameId,
                'add',
                5
            )
        );

        $game = self::load_game($gameId);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertCount(6, $game->activeDieArrayArray[0]);
        $this->assertCount(6, $game->activeDieArrayArray[1]);
        $this->assertTrue($game->activeDieArrayArray[0][5]->has_skill('Auxiliary'));
        $this->assertTrue($game->activeDieArrayArray[0][5]->has_flag('AddAuxiliary'));

        $this->assertTrue(
            $this->object->game()->react_to_auxiliary(
                self::$userId2WithoutAutopass,
                $gameId,
                'add',
                5)
            );
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertCount(6, $game->activeDieArrayArray[0]);
        $this->assertCount(6, $game->activeDieArrayArray[1]);
        foreach ($game->activeDieArrayArray as $activeDieArray) {
            foreach ($activeDieArray as $die) {
                $this->assertFalse($die->has_skill('Auxiliary'));
            }
        }
        $this->assertEquals('(4) (8) (12) (20) (X) (6)',
                            $game->buttonArray[0]->recipe);
        $this->assertEquals('(4) (4) (12) (20) (X) (6)',
                            $game->buttonArray[1]->recipe);
    }

    /**
     * Check that a bad action is handled gracefully.
     *
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGame::react_to_auxiliary
     */
    public function test_react_to_auxiliary_invalid() {
        $this->assertFalse($this->object->game()->react_to_auxiliary(1.5, 2.5, 'ha!'));
    }

    /**
     * Check that a decline of a reserve die works correctly.
     *
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGame::react_to_reserve
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_react_to_reserve_decline() {
        // Sailor Moon : (8) (8) (10) (20) r(6) r(10) r(20) r(20)
        // Queen Beryl : (4) (8) (12) (20) r(4) r(12) r(20) r(20)
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Sailor Moon', 'Queen Beryl'),
            4
        );
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $game->gameScoreArrayArray = array(array('W' => 0, 'L' => 1, 'D' => 0),
                                           array('W' => 1, 'L' => 0, 'D' => 0));
        $game->isPrevRoundWinnerArray = array(FALSE, TRUE);
        $game->waitingOnActionArray = array(FALSE, FALSE);
        $game->gameState = BMGameState::LOAD_DICE_INTO_BUTTONS;
        $playerArray = $game->playerArray;
        $playerArray[0]->activeDieArray = array();
        $playerArray[1]->activeDieArray = array();
        $game->playerArray = $playerArray;

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(BMGameState::CHOOSE_RESERVE_DICE, $game->gameState);
        $this->assertEquals(array(TRUE, FALSE), $game->waitingOnActionArray);
        $this->assertCount(8, $game->activeDieArrayArray[0]);
        $this->assertCount(8, $game->activeDieArrayArray[1]);

        // a non-player attempts an action
        $this->assertFalse(
            $this->object->game()->react_to_reserve(
                0,
                $gameId,
                'decline')
        );

        // player 1 attempts an invalid action
        $this->assertFalse(
            $this->object->game()->react_to_reserve(
                self::$userId1WithoutAutopass,
                $gameId,
                'rubbish')
        );

        // player 2 attempts a reserve action
        $this->assertFalse(
            $this->object->game()->react_to_reserve(
                self::$userId2WithoutAutopass,
                $gameId,
                'add',
                6)
        );

        // player 1 declines
        $this->assertTrue(
            $this->object->game()->react_to_reserve(
                self::$userId1WithoutAutopass,
                $gameId,
                'decline')
        );
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals(1, array_sum($game->waitingOnActionArray));
        $this->assertCount(4, $game->activeDieArrayArray[0]);
        $this->assertCount(4, $game->activeDieArrayArray[1]);
        foreach ($game->activeDieArrayArray as $activeDieArray) {
            foreach ($activeDieArray as $die) {
                $this->assertFalse($die->has_skill('Reserve'));
            }
        }
        $this->assertEquals('(8) (8) (10) (20) r(6) r(10) r(20) r(20)',
                            $game->buttonArray[0]->recipe);
        $this->assertEquals('(4) (8) (12) (20) r(4) r(12) r(20) r(20)',
                            $game->buttonArray[1]->recipe);
    }

    /**
     * Check that a decline of a reserve die works correctly.
     *
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterfaceGame::react_to_reserve
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_react_to_reserve_add() {
        // Sailor Moon : (8) (8) (10) (20) r(6) r(10) r(20) r(20)
        // Queen Beryl : (4) (8) (12) (20) r(4) r(12) r(20) r(20)
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Sailor Moon', 'Queen Beryl'),
            4
        );
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $game->gameScoreArrayArray = array(array('W' => 0, 'L' => 1, 'D' => 0),
                                           array('W' => 1, 'L' => 0, 'D' => 0));
        $game->isPrevRoundWinnerArray = array(FALSE, TRUE);
        $game->waitingOnActionArray = array(FALSE, FALSE);
        $game->gameState = BMGameState::LOAD_DICE_INTO_BUTTONS;
        $playerArray = $game->playerArray;
        $playerArray[0]->activeDieArray = array();
        $playerArray[1]->activeDieArray = array();
        $game->playerArray = $playerArray;

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(BMGameState::CHOOSE_RESERVE_DICE, $game->gameState);
        $this->assertEquals(array(TRUE, FALSE), $game->waitingOnActionArray);
        $this->assertCount(8, $game->activeDieArrayArray[0]);
        $this->assertCount(8, $game->activeDieArrayArray[1]);

        // a non-player attempts an action
        $this->assertFalse(
            $this->object->game()->react_to_reserve(
                0,
                $gameId,
                'add')
        );

        // player 1 adds reserve die
        $this->assertTrue(
            $this->object->game()->react_to_reserve(
                self::$userId1WithoutAutopass,
                $gameId,
                'add',
                5)
        );
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals(1, array_sum($game->waitingOnActionArray));
        $this->assertCount(5, $game->activeDieArrayArray[0]);
        $this->assertCount(4, $game->activeDieArrayArray[1]);
        foreach ($game->activeDieArrayArray as $activeDieArray) {
            foreach ($activeDieArray as $die) {
                $this->assertFalse($die->has_skill('Reserve'));
            }
        }
        $this->assertEquals(10, $game->activeDieArrayArray[0][4]->max);
        $this->assertEquals('(8) (8) (10) (20) r(6) (10) r(20) r(20)',
                            $game->buttonArray[0]->recipe);
        $this->assertEquals('(4) (8) (12) (20) r(4) r(12) r(20) r(20)',
                            $game->buttonArray[1]->recipe);
    }

    /**
     * The following unit tests ensure that fire works correctly.
     *
     * @depends BMInterface000Test::test_create_user
     *
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     * @covers BMInterfaceGame::adjust_fire
     */
    function test_fire() {
        // (4) (6) F(8) (20) (X) vs F(4) F(6) (6) (12) (X)
        $retval = $this->object->game()->create_game(
            array(self::$userId1WithoutAutopass, self::$userId2WithoutAutopass),
            array('Poly', 'Adam Spam'),
            4
        );
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        // load game
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $this->assertEquals( 4, $game->activeDieArrayArray[0][0]->max);
        $this->assertEquals( 6, $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals( 8, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals(20, $game->activeDieArrayArray[0][3]->max);
        $this->assertFalse(isset($game->activeDieArrayArray[0][4]->max));
        $this->assertEquals( 4, $game->activeDieArrayArray[1][0]->max);
        $this->assertEquals( 6, $game->activeDieArrayArray[1][1]->max);
        $this->assertEquals( 6, $game->activeDieArrayArray[1][2]->max);
        $this->assertEquals(12, $game->activeDieArrayArray[1][3]->max);
        $this->assertFalse(isset($game->activeDieArrayArray[1][4]->max));
        $this->assertTrue($game->activeDieArrayArray[0][2]->has_skill('Fire'));
        $this->assertTrue($game->activeDieArrayArray[1][0]->has_skill('Fire'));
        $this->assertTrue($game->activeDieArrayArray[1][1]->has_skill('Fire'));

        $game->swingValueArrayArray = array(array('X' => 17), array('X' => 5));

        self::save_game($game);
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals(17, $game->activeDieArrayArray[0][4]->max);
        $this->assertEquals( 5, $game->activeDieArrayArray[1][4]->max);

        $this->assertNotNull($game->activeDieArrayArray[0][0]->value);
        $this->assertNotNull($game->activeDieArrayArray[0][1]->value);
        $this->assertNotNull($game->activeDieArrayArray[0][2]->value);
        $this->assertNotNull($game->activeDieArrayArray[0][3]->value);
        $this->assertNotNull($game->activeDieArrayArray[0][4]->value);
        $this->assertNotNull($game->activeDieArrayArray[1][0]->value);
        $this->assertNotNull($game->activeDieArrayArray[1][1]->value);
        $this->assertNotNull($game->activeDieArrayArray[1][2]->value);
        $this->assertNotNull($game->activeDieArrayArray[1][3]->value);
        $this->assertNotNull($game->activeDieArrayArray[1][4]->value);

        // artificially set player 1 as winning initiative
        $game->playerWithInitiativeIdx = 0;
        $game->activePlayerIdx = 0;
        $game->waitingOnActionArray = array(TRUE, FALSE);
        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 1;
        $dieArrayArray[0][1]->value = 1;
        $dieArrayArray[0][2]->value = 6;
        $dieArrayArray[0][3]->value = 1;
        $dieArrayArray[0][4]->value = 1;
        $dieArrayArray[1][0]->value = 4;
        $dieArrayArray[1][1]->value = 6;
        $dieArrayArray[1][2]->value = 6;
        $dieArrayArray[1][3]->value = 12;
        $dieArrayArray[1][4]->value = 5;

        // perform valid attack
        $game->attack = array(0,        // attackerPlayerIdx
                              1,        // defenderPlayerIdx
                              array(0), // attackerAttackDieIdxArray
                              array(0), // defenderAttackDieIdxArray
                              'Power'); // attackType

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(array(TRUE, FALSE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::ADJUST_FIRE_DICE, $game->gameState);
        $this->assertCount(5, $game->activeDieArrayArray[0]);
        $this->assertCount(5, $game->activeDieArrayArray[1]);
        $this->assertCount(0, $game->capturedDieArrayArray[0]);
        $this->assertCount(0, $game->capturedDieArrayArray[1]);

        $retval = $this->object->game()->adjust_fire(
            self::$userId1WithoutAutopass,
            $game->gameId,
            1,
            'ignore',
            'turndown',
            array(2),
            array(3)
        );

        $this->assertTrue($retval);

        $game = self::load_game($game->gameId);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertCount(5, $game->activeDieArrayArray[0]);
        $this->assertCount(4, $game->activeDieArrayArray[1]);
        $this->assertCount(1, $game->capturedDieArrayArray[0]);
        $this->assertCount(0, $game->capturedDieArrayArray[1]);
    }
}
