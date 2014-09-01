<?php

class BMInterfaceTest extends PHPUnit_Framework_TestCase {

    /**
     * @var BMInterface
     */
    protected $object;
    private static $userId1WithoutAutopass;
    private static $userId2WithoutAutopass;
    private static $userId3WithAutopass;
    private static $userId4WithAutopass;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        if (file_exists('../test/src/database/mysql.test.inc.php')) {
            require_once '../test/src/database/mysql.test.inc.php';
        } else {
            require_once 'test/src/database/mysql.test.inc.php';
        }
        $this->object = new BMInterface(TRUE);
        $this->newuserObject = new BMInterfaceNewuser(TRUE);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    protected static function getMethod($name) {
        $class = new ReflectionClass('BMInterface');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    protected function load_game($gameId) {
        $load_game = self::getMethod('load_game');
        return $load_game->invokeArgs($this->object, array($gameId));
    }

    protected function save_game($game) {
        $save_game = self::getMethod('save_game');
        return $save_game->invokeArgs($this->object, array($game));
    }

    /**
     * @covers BMInterfaceNewuser::create_user
     */
    public function test_create_user() {
        $created_real = False;
        $maxtries = 999;
        $trynum = 1;

        // Tests may be run multiple times.  Find a user of the
        // form interfaceNNN which hasn't been created yet and
        // create it in the test DB.  The dummy interface will claim
        // success for any username of this form.
        while (!($created_real)) {
            $this->assertTrue($trynum < $maxtries,
                "Internal test error: too many interfaceNNN users in the test database. " .
                "Clean these out by hand.");
            $username = 'interface' . sprintf('%03d', $trynum);
            $email = $username . '@example.com';
            $createResult = $this->newuserObject->create_user($username, 't', $email);
            if (isset($createResult)) {
                $created_real = True;
            }
            $trynum++;
        }

        $this->assertTrue($created_real,
            "Creation of $username user should be reported as success");
        self::$userId1WithoutAutopass = (int)$createResult['playerId'];

        $username = 'interface' . sprintf('%03d', $trynum);
        $email = $username . '@example.com';
        $createResult = $this->newuserObject->create_user($username, 't', $email);
        self::$userId2WithoutAutopass = (int)$createResult['playerId'];

        $trynum++;
        $username = 'interface' . sprintf('%03d', $trynum);
        $email = $username . '@example.com';
        $createResult = $this->newuserObject->create_user($username, 't', $email);

        $infoArray = array(
            'name_irl' => '',
            'is_email_public' => FALSE,
            'dob_month' => 0,
            'dob_day' => 0,
            'gender' => '',
            'comment' => '',
            'monitor_redirects_to_game' => 0,
            'monitor_redirects_to_forum' => 0,
            'automatically_monitor' => 0,
            'autopass' => 1
        );
        $addlInfo = array('dob_month' => 0, 'dob_day' => 0, 'homepage' => '');

        $this->object->set_player_info($createResult['playerId'],
                                       $infoArray,
                                       $addlInfo);
        self::$userId3WithAutopass = (int)$createResult['playerId'];

        $trynum++;
        $username = 'interface' . sprintf('%03d', $trynum);
        $email = $username . '@example.com';
        $createResult = $this->newuserObject->create_user($username, 't', $email);
        $this->object->set_player_info($createResult['playerId'],
                                       $infoArray,
                                       $addlInfo);
        self::$userId4WithAutopass = (int)$createResult['playerId'];
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::get_player_info
     */
    public function test_get_player_info() {
        $data = $this->object->get_player_info(self::$userId3WithAutopass);
        $resultArray = $data['user_prefs'];
        $this->assertTrue(is_array($resultArray));

        $this->assertArrayHasKey('id', $resultArray);
        $this->assertArrayHasKey('name_ingame', $resultArray);
        $this->assertArrayNotHasKey('password_hashed', $resultArray);
        $this->assertArrayHasKey('name_irl', $resultArray);
        $this->assertArrayHasKey('email', $resultArray);
        $this->assertArrayHasKey('is_email_public', $resultArray);
        $this->assertArrayHasKey('dob_month', $resultArray);
        $this->assertArrayHasKey('dob_day', $resultArray);
        $this->assertArrayHasKey('gender', $resultArray);
        $this->assertArrayHasKey('image_size', $resultArray);
        $this->assertArrayHasKey('image_size', $resultArray);
        $this->assertArrayHasKey('uses_gravatar', $resultArray);
        $this->assertArrayHasKey('monitor_redirects_to_game', $resultArray);
        $this->assertArrayHasKey('monitor_redirects_to_forum', $resultArray);
        $this->assertArrayHasKey('automatically_monitor', $resultArray);
        $this->assertArrayHasKey('comment', $resultArray);
        $this->assertArrayHasKey('player_color', $resultArray);
        $this->assertArrayHasKey('opponent_color', $resultArray);
        $this->assertArrayHasKey('neutral_color_a', $resultArray);
        $this->assertArrayHasKey('neutral_color_b', $resultArray);
        $this->assertArrayHasKey('homepage', $resultArray);
        $this->assertArrayHasKey('favorite_button', $resultArray);
        $this->assertArrayHasKey('favorite_buttonset', $resultArray);
        $this->assertArrayHasKey('last_action_time', $resultArray);
        $this->assertArrayHasKey('creation_time', $resultArray);
        $this->assertArrayHasKey('fanatic_button_id', $resultArray);
        $this->assertArrayHasKey('n_games_won', $resultArray);
        $this->assertArrayHasKey('n_games_lost', $resultArray);

        $this->assertTrue(is_int($resultArray['id']));
        $this->assertEquals(self::$userId3WithAutopass, $resultArray['id']);

        $this->assertTrue(is_bool($resultArray['autopass']));
        $this->assertTrue(is_bool($resultArray['monitor_redirects_to_game']));
        $this->assertTrue(is_bool($resultArray['monitor_redirects_to_forum']));
        $this->assertTrue(is_bool($resultArray['automatically_monitor']));

        $this->assertTrue(is_int($resultArray['fanatic_button_id']));
        $this->assertEquals(0, $resultArray['fanatic_button_id']);
        $this->assertTrue(is_int($resultArray['n_games_won']));
        $this->assertTrue(is_int($resultArray['n_games_lost']));
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::get_player_info
     * @covers BMInterface::set_player_info
     */
    public function test_set_player_info() {
        $infoArray = array(
            'name_irl' => '',
            'is_email_public' => FALSE,
            'dob_month' => 0,
            'dob_day' => 0,
            'gender' => '',
            'comment' => '',
            'autopass' => 1,
            'player_color' => '#dd99dd',
            'opponent_color' => '#ddffdd',
            'neutral_color_a' => '#cccccc',
            'neutral_color_b' => '#dddddd',
            'monitor_redirects_to_game' => 1,
            'monitor_redirects_to_forum' => 1,
            'automatically_monitor' => 1,
        );
        $addlInfo = array('dob_month' => 0, 'dob_day' => 0, 'homepage' => 'google.com');

        $this->object->set_player_info(self::$userId1WithoutAutopass,
                                       $infoArray,
                                       $addlInfo);
        $data = $this->object->get_player_info(self::$userId1WithoutAutopass);
        $playerInfoArray = $data['user_prefs'];
        $this->assertEquals(TRUE, $playerInfoArray['autopass']);
        $this->assertEquals(TRUE, $playerInfoArray['monitor_redirects_to_game']);
        $this->assertEquals(TRUE, $playerInfoArray['monitor_redirects_to_forum']);
        $this->assertEquals(TRUE, $playerInfoArray['automatically_monitor']);
        $this->assertEquals('http://google.com', $playerInfoArray['homepage']);

        $infoArray['autopass'] = 0;
        $infoArray['monitor_redirects_to_game'] = 0;
        $infoArray['monitor_redirects_to_forum'] = 0;
        $infoArray['automatically_monitor'] = 0;
        $this->object->set_player_info(self::$userId1WithoutAutopass,
                                       $infoArray,
                                       $addlInfo);
        $data = $this->object->get_player_info(self::$userId1WithoutAutopass);
        $playerInfoArray = $data['user_prefs'];
        $this->assertEquals(FALSE, $playerInfoArray['autopass']);
        $this->assertEquals(FALSE, $playerInfoArray['monitor_redirects_to_game']);
        $this->assertEquals(FALSE, $playerInfoArray['monitor_redirects_to_forum']);
        $this->assertEquals(FALSE, $playerInfoArray['automatically_monitor']);

        $addlInfo['homepage'] = 'javascript:alert(\"Evil\");';
        $response =
            $this->object->set_player_info(
                self::$userId1WithoutAutopass,
                $infoArray,
                $addlInfo
            );
        $this->assertEquals(NULL, $response);
        $data = $this->object->get_player_info(self::$userId1WithoutAutopass);
        $this->assertEquals('http://google.com', $playerInfoArray['homepage']);
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_and_load_new_game() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                             array('Bauer', 'Stark'), 4);
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
        $this->assertTrue(isset($game->activeDieArrayArray));
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
     * @depends test_create_user
     *
     * @covers BMInterface::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_ornery_mood_swing() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                             array('Skeeve', 'Skeeve'), 4);
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
        $this->assertTrue(isset($game->activeDieArrayArray));
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
     * @depends test_create_user
     *
     * @covers BMInterface::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_one_random_button() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                             array('Coil', '__random'), 4);
        $gameId = $retval['gameId'];

        $game = self::load_game($gameId);
        $this->assertFalse(empty($game->buttonArray[0]));
        $this->assertEquals('Coil', $game->buttonArray[0]->name);
        $this->assertFalse(empty($game->buttonArray[1]));
        $this->assertNotEquals('__random', $game->buttonArray[1]->name);
        $this->assertGreaterThan(BMGameState::START_GAME, $game->gameState);
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_one_random_button_and_one_unspecified() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                             array('__random', NULL), 4);
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
     * @depends test_create_user
     *
     * @covers BMInterface::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_two_random_buttons() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                             array('__random', '__random'), 4);
        $gameId = $retval['gameId'];

        $game = self::load_game($gameId);
        $this->assertFalse(empty($game->buttonArray[0]->name));
        $this->assertNotEquals('__random', $game->buttonArray[0]->name);
        $this->assertFalse(empty($game->buttonArray[1]->name));
        $this->assertNotEquals('__random', $game->buttonArray[1]->name);
        $this->assertGreaterThan(BMGameState::START_GAME, $game->gameState);
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_game_with_two_random_buttons_with_one_unspecified_player() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   NULL),
                                             array('__random', '__random'), 4);
        $gameId = $retval['gameId'];

        $game = self::load_game($gameId);
        $this->assertTrue(empty($game->buttonArray[0]));
        $this->assertTrue($game->isButtonChoiceRandom[0]);
        $this->assertTrue(empty($game->buttonArray[1]));
        $this->assertTrue($game->isButtonChoiceRandom[1]);
        $this->assertEquals(BMGameState::START_GAME, $game->gameState);
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_and_load_new_game_with_empty_opponent() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   NULL),
                                             array('Bauer', 'Stark'), 4);
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
        $this->assertTrue(isset($game->activeDieArrayArray));
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
        $this->assertFalse(isset($game->swingValueArrayArray));

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
     * @depends test_create_user
     *
     * @covers BMInterface::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_and_load_new_game_with_empty_button() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                             array('Bauer', NULL), 4);
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
        $this->assertTrue(isset($game->activeDieArrayArray));
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
        $this->assertFalse(isset($game->swingValueArrayArray));

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
     * @depends test_create_user
     *
     * @covers BMInterface::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_and_load_new_game_with_empty_opponent_and_opponent_button() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   NULL),
                                             array('Bauer', NULL), 4);
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
        $this->assertTrue(isset($game->activeDieArrayArray));
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
        $this->assertFalse(isset($game->swingValueArrayArray));

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
     * @depends test_create_and_load_new_game_with_empty_opponent
     *
     * @covers BMInterface::join_open_game
     */
    public function test_join_open_game() {
        // create an open game with an unspecified opponent
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   NULL),
                                             array('Bauer', 'Stark'), 4);
        $this->assertNotNull($retval);
        $this->object->join_open_game(self::$userId2WithoutAutopass, $retval['gameId']);

        $game = self::load_game($retval['gameId']);
        $this->assertEquals(array(self::$userId1WithoutAutopass,
                                  self::$userId2WithoutAutopass),
                            $game->playerIdArray);
    }

    /**
     * @depends test_create_and_load_new_game_with_empty_button
     *
     * @covers BMInterface::select_button
     */
    public function test_select_button() {
        // create an open game with an unspecified button
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                             array('Bauer', NULL), 4);
        $this->assertNotNull($retval);
        $this->object->select_button(self::$userId2WithoutAutopass, $retval['gameId'], 'Iago');

        $game = self::load_game($retval['gameId']);
        $this->assertEquals('Bauer', $game->buttonArray[0]->name);
        $this->assertEquals('(8) (10) (12) (20) (X)', $game->buttonArray[0]->recipe);
        $this->assertEquals('Iago', $game->buttonArray[1]->name);
        $this->assertEquals('(20) (20) (20) (X)', $game->buttonArray[1]->recipe);
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::create_game
     */
    public function test_create_self_game() {
        // attempt to create a game with the same player on both sides
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId1WithoutAutopass),
                                             array('Bauer', 'Stark'), 4);
        $this->assertNull($retval);
        $this->assertEquals('Game create failed because a player has been selected more than once.',
                            $this->object->message);
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::create_game
     */
    public function test_create_game_with_invalid_parameters() {
        // attempt to create a game with a non-integer number of max wins
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Bauer', 'Stark'), 4.5);
        $this->assertNull($retval);
        $this->assertEquals('Game create failed because the maximum number of wins was invalid.',
                            $this->object->message);

        // attempt to create a game with a zero number of max wins
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Bauer', 'Stark'), 0);
        $this->assertNull($retval);
        $this->assertEquals('Game create failed because the maximum number of wins was invalid.',
                            $this->object->message);

        // attempt to create a game with a large number of max wins
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Bauer', 'Stark'), 6);
        $this->assertNull($retval);
        $this->assertEquals('Game create failed because the maximum number of wins was invalid.',
                            $this->object->message);

        // attempt to create a game with an invalid button name
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('KJQOERUCHC', 'Stark'), 3);
        $this->assertNull($retval);
        $this->assertEquals('Game create failed because a button name was not valid.',
                            $this->object->message);
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_load_game_after_setting_swing_values() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Bauer', 'Stark'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);
        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        // specify swing dice correctly
        $game->swingValueArrayArray = array(array('X'=>19), array('X'=>5));
        self::save_game($game);
        $game = self::load_game($gameId);

        // check player info
        $this->assertCount(2, $game->playerIdArray);
        $this->assertEquals(2, $game->nPlayers);
        $this->assertEquals(self::$userId1WithoutAutopass, $game->playerIdArray[0]);
        $this->assertEquals(self::$userId2WithoutAutopass, $game->playerIdArray[1]);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertTrue(isset($game->activePlayerIdx));
        $this->assertTrue(isset($game->playerWithInitiativeIdx));
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
        $this->assertTrue(isset($game->activeDieArrayArray));
        $this->assertCount(2, $game->activeDieArrayArray);

        $expectedRecipes = array(array('(8)', '(10)', '(12)', '(20)', '(X)'),
                                 array('(4)', '(6)', '(8)', '(X)', '(X)'));
        $expectedSizes = array(array(8, 10, 12, 20, 19),
                               array(4, 6, 8, 5, 5));

        foreach ($game->activeDieArrayArray as $playerIdx => $activeDieArray) {
            $this->assertEquals(count($expectedRecipes[$playerIdx]),
                                count($activeDieArray));
            for ($dieIdx = 0; $dieIdx <= 4; $dieIdx++) {
                $this->assertEquals($expectedRecipes[$playerIdx][$dieIdx],
                                    $activeDieArray[$dieIdx]->recipe);
                $this->assertEquals($game, $activeDieArray[$dieIdx]->ownerObject);
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
        $this->assertTrue(isset($game->capturedDieArrayArray));
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
        $this->assertEquals(array(array('X' => 19), array('X' => 5)),
                            $game->swingValueArrayArray);

        // check round info
        $this->assertEquals(1, $game->roundNumber);
        $this->assertEquals(4, $game->maxWins);

        // check action info
        $this->assertFalse(isset($game->attack));
        $this->assertEquals(0, $game->nRecentPasses);
        $this->assertEquals(TRUE, $game->waitingOnActionArray[$game->activePlayerIdx]);


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
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_play_turn() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Bauer', 'Stark'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);
        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);

        // specify swing dice correctly
        $game->swingValueArrayArray = array(array('X'=>17), array('X'=>8));
        self::save_game($game);
        $game = self::load_game($gameId);

        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 8;
        $dieArrayArray[0][1]->value = 1;
        $dieArrayArray[0][2]->value = 10;
        $dieArrayArray[0][3]->value = 15;
        $dieArrayArray[0][4]->value = 7;
        $dieArrayArray[1][0]->value = 2;
        $dieArrayArray[1][1]->value = 3;
        $dieArrayArray[1][2]->value = 8;
        $dieArrayArray[1][3]->value = 4;
        $dieArrayArray[1][4]->value = 1;

        $game->activePlayerIdx = 1;

        self::save_game($game);
        $game = self::load_game($gameId);

        $game->attack = array(1,        // attackerPlayerIdx
                              0,        // defenderPlayerIdx
                              array(2), // attackerAttackDieIdxArray
                              array(1), // defenderAttackDieIdxArray
                              'Power'); // attackType

        self::save_game($game);
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertCount(4, $game->activeDieArrayArray[0]);
        $this->assertCount(5, $game->activeDieArrayArray[1]);
        $this->assertCount(0, $game->capturedDieArrayArray[0]);
        $this->assertCount(1, $game->capturedDieArrayArray[1]);
        $this->assertEquals(10, $game->capturedDieArrayArray[1][0]->max);
        $this->assertEquals(1, $game->capturedDieArrayArray[1][0]->value);
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::load_game
     */
    public function test_load_poison() {
        // Coil: p4 12 p20 20 V
        // Bane: p2 p4 12 12 V
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Coil', 'Bane'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);
        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);

        // specify swing dice correctly
        $game->swingValueArrayArray = array(array('V'=>11), array('V'=>7));
        self::save_game($game);
        $game = self::load_game($gameId);

        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 4;
        $dieArrayArray[0][1]->value = 3;
        $dieArrayArray[0][2]->value = 2;
        $dieArrayArray[0][3]->value = 1;
        $dieArrayArray[0][4]->value = 7;
        $dieArrayArray[1][0]->value = 2;
        $dieArrayArray[1][1]->value = 2;
        $dieArrayArray[1][2]->value = 3;
        $dieArrayArray[1][3]->value = 4;
        $dieArrayArray[1][4]->value = 5;

        $game->activePlayerIdx = 0;

        self::save_game($game);
        $game = self::load_game($gameId);

        $this->assertEquals(array(-2.5, 9.5), $game->roundScoreArray);

        $game->attack = array(0,        // attackerPlayerIdx
                              1,        // defenderPlayerIdx
                              array(0), // attackerAttackDieIdxArray
                              array(0), // defenderAttackDieIdxArray
                              'Power'); // attackType

        self::save_game($game);
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertCount(5, $game->activeDieArrayArray[0]);
        $this->assertCount(4, $game->activeDieArrayArray[1]);
        $this->assertCount(1, $game->capturedDieArrayArray[0]);
        $this->assertCount(0, $game->capturedDieArrayArray[1]);
        $this->assertEquals(2, $game->capturedDieArrayArray[0][0]->max);
        $this->assertEquals(2, $game->capturedDieArrayArray[0][0]->value);
        $this->assertEquals(array(-3.5, 11.5), $game->roundScoreArray);
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     */
    public function test_swing_value_reset_at_end_of_round() {
        // create a dummy game that will be overwritten
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Tess', 'Coil'), 4);
        $gameId = $retval['gameId'];

        // start as if we were close to the end of Round 1

        // load buttons
        $button1 = new BMButton;
        $button1->load('(1) (X)', 'Tess');
        $this->assertEquals('(1) (X)', $button1->recipe);
        // check dice in $button1->dieArray are correct
        $this->assertCount(2, $button1->dieArray);
        $this->assertEquals(1, $button1->dieArray[0]->max);
        $this->assertFalse(isset($button1->dieArray[1]->max));
        $this->assertTrue($button1->dieArray[1] instanceof BMDieSwing);
        $this->assertTrue($button1->dieArray[1]->needsSwingValue);

        $button2 = new BMButton;
        $button2->load('(2) p(V)', 'Coil');
        $this->assertEquals('(2) p(V)', $button2->recipe);
        // check dice in $button2->dieArray are correct
        $this->assertCount(2, $button2->dieArray);
        $this->assertEquals(2, $button2->dieArray[0]->max);
        $this->assertFalse(isset($button2->dieArray[1]->max));
        $this->assertTrue($button2->dieArray[1] instanceof BMDieSwing);
        $this->assertTrue($button2->dieArray[1]->needsSwingValue);
        $this->assertEquals(array('score_value'),
                            array_keys($button2->dieArray[1]->hookList));
        $this->assertEquals(array('BMSkillPoison'),
                            $button2->dieArray[1]->hookList['score_value']);

        // load game
        $game = new BMGame($gameId, array(self::$userId1WithoutAutopass,
                                          self::$userId2WithoutAutopass),
                           array('', ''), 2);
        $this->assertEquals(BMGameState::START_GAME, $game->gameState);
        $this->assertEquals(2, $game->maxWins);
        $game->buttonArray = array($button1, $button2);
        $this->assertEquals($game, $game->buttonArray[0]->ownerObject);
        $this->assertEquals($game, $game->buttonArray[1]->ownerObject);
        $this->assertEquals($game, $game->buttonArray[0]->dieArray[0]->ownerObject);
        $this->assertEquals($game, $game->buttonArray[0]->dieArray[1]->ownerObject);
        $this->assertEquals($game, $game->buttonArray[1]->dieArray[0]->ownerObject);
        $this->assertEquals($game, $game->buttonArray[1]->dieArray[1]->ownerObject);

        $game->waitingOnActionArray = array(FALSE, FALSE);
        $game->proceed_to_next_user_action();
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $this->assertEquals(array(array('X' => NULL), array('V' => NULL)),
                            $game->swingValueArrayArray);

        // specify swing dice correctly
        $game->swingValueArrayArray = array(array('X' => 7), array('V' => 11));
        $game->proceed_to_next_user_action();
        $this->assertTrue($game->activeDieArrayArray[0][1] instanceof BMDieSwing);
        $this->assertTrue($game->activeDieArrayArray[1][1] instanceof BMDieSwing);
        $this->assertFalse($game->activeDieArrayArray[0][1]->needsSwingValue);
        $this->assertFalse($game->activeDieArrayArray[1][1]->needsSwingValue);

        $this->assertEquals(1, array_sum($game->waitingOnActionArray));
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals(array(array('X' => 7), array('V' => 11)),
                            $game->swingValueArrayArray);
        $this->assertEquals(7,  $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals(11, $game->activeDieArrayArray[1][1]->max);

        $this->assertNotNull($game->activeDieArrayArray[0][1]->value);
        $this->assertNotNull($game->activeDieArrayArray[1][1]->value);

        $this->assertEquals(array('score_value'),
                            array_keys($game->activeDieArrayArray[1][1]->hookList));
        $this->assertEquals(array('BMSkillPoison'),
                            $game->activeDieArrayArray[1][1]->hookList['score_value']);

        $this->assertEquals(array(4, -10), $game->roundScoreArray);

        // artificially set player 1 as winning initiative
        $game->playerWithInitiativeIdx = 0;
        $game->activePlayerIdx = 0;
        $game->waitingOnActionArray = array(TRUE, FALSE);
        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 1;
        $dieArrayArray[0][1]->value = 1;
        $dieArrayArray[1][0]->value = 1;
        $dieArrayArray[1][1]->value = 1;

        // perform attack
        $game->attack = array(0,        // attackerPlayerIdx
                              1,        // defenderPlayerIdx
                              array(0), // attackerAttackDieIdxArray
                              array(1), // defenderAttackDieIdxArray
                              'Power'); // attackType

        $this->assertEquals(array('X'), array_keys($game->swingValueArrayArray[0]));
        $this->assertEquals(7, $game->swingValueArrayArray[0]['X']);
        $this->assertEquals(array('V'), array_keys($game->swingValueArrayArray[1]));
        $this->assertEquals(11, $game->swingValueArrayArray[1]['V']);

        self::save_game($game);
        $game = self::load_game($gameId);

        $this->assertEquals(array('X'), array_keys($game->swingValueArrayArray[0]));
        $this->assertEquals(7, $game->swingValueArrayArray[0]['X']);
        $this->assertEquals(array('V'), array_keys($game->swingValueArrayArray[1]));
        $this->assertEquals(11, $game->swingValueArrayArray[1]['V']);

        $this->assertEquals(1, count($game->activeDieArrayArray[1]));

        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 1;

        // perform attack
        $game->attack = array(1,        // attackerPlayerIdx
                              0,        // defenderPlayerIdx
                              array(0), // attackerAttackDieIdxArray
                              array(1), // defenderAttackDieIdxArray
                              'Power'); // attackType

        self::save_game($game);
        $game = self::load_game($gameId);

        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[1][0]->value = 1;

        // perform attack
        $game->attack = array(0,        // attackerPlayerIdx
                              1,        // defenderPlayerIdx
                              array(0), // attackerAttackDieIdxArray
                              array(0), // defenderAttackDieIdxArray
                              'Power'); // attackType

        $this->assertEquals(array('X'), array_keys($game->swingValueArrayArray[0]));
        $this->assertEquals(7, $game->swingValueArrayArray[0]['X']);
        $this->assertEquals(array('V'), array_keys($game->swingValueArrayArray[1]));
        $this->assertEquals(11, $game->swingValueArrayArray[1]['V']);

        self::save_game($game);
        $game = self::load_game($gameId);

        $this->assertEquals(array(array('W' => 0, 'L' => 1, 'D' => 0),
                                  array('W' => 1, 'L' => 0, 'D' => 0)),
                            $game->gameScoreArrayArray);

        $this->assertEquals(array('X'), array_keys($game->swingValueArrayArray[0]));
        $this->assertFalse(isset($game->swingValueArrayArray[0]['X']));
        $this->assertEquals(array('V'), array_keys($game->swingValueArrayArray[1]));
        $this->assertTrue(isset($game->swingValueArrayArray[1]['V']));
        $this->assertTrue(isset($game->activeDieArrayArray[1][4]->swingValue));
        $this->assertEquals(array(TRUE, FALSE), $game->waitingOnActionArray);

        $this->assertEquals(array(array('X' => NULL), array('V' => 11)),
                            $game->swingValueArrayArray);
        $this->assertEquals(array(array('X' => 7), array('V' => 11)),
                            $game->prevSwingValueArrayArray);
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     */
    public function test_swing_value_reset_at_end_of_game() {
        // create a dummy game that will be overwritten
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Tess', 'Coil'), 1);
        $gameId = $retval['gameId'];

        // start as if we were close to the end of the game
        // load buttons
        $button1 = new BMButton;
        $button1->load('(X)', 'Test1');

        $button2 = new BMButton;
        $button2->load('(V)', 'Test2');

        // load game
        $game = new BMGame($gameId, array(self::$userId1WithoutAutopass,
                                          self::$userId2WithoutAutopass),
                           array('', ''), 1);
        $game->buttonArray = array($button1, $button2);

        $game->waitingOnActionArray = array(FALSE, FALSE);

        self::save_game($game);
        $game = self::load_game($gameId);

        // specify swing dice correctly
        $game->swingValueArrayArray = array(array('X' => 7), array('V' => 11));
        self::save_game($game);
        $game = self::load_game($gameId);

        // artificially set player 1 as winning initiative
        $game->playerWithInitiativeIdx = 0;

        // artificially set player 2 as being active
        $game->activePlayerIdx = 1;
        $game->waitingOnActionArray = array(FALSE, TRUE);
        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;

        $dieArrayArray[0][0]->value = 1;
        $dieArrayArray[1][0]->value = 2;

        // perform attack
        $game->attack = array(1,        // attackerPlayerIdx
                              0,        // defenderPlayerIdx
                              array(0), // attackerAttackDieIdxArray
                              array(0), // defenderAttackDieIdxArray
                              'Power'); // attackType

        self::save_game($game);
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::END_GAME, $game->gameState);
        $this->assertNull($game->swingValueArrayArray);
        $this->assertEquals(array(array('W' => 0, 'L' => 1, 'D' => 0),
                                  array('W' => 1, 'L' => 0, 'D' => 0)),
                            $game->gameScoreArrayArray);
        $this->assertEquals(1, $game->roundNumber);
    }


    /**
     * The following unit tests ensure that the swing values are persistent,
     * even when the swing dice have been changed to normal dice,
     *   e.g., by a berserk attack.
     *
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_swing_value_persistence() {
        // create a dummy game that will be overwritten
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Tess', 'Coil'), 4);
        $gameId = $retval['gameId'];

        // start as if we were close to the end of Round 1

        // load buttons
        $button1 = new BMButton;
        $button1->load('(1) (X)', 'Test1');
        $this->assertFalse(isset($button1->dieArray[1]->max));
        $this->assertTrue($button1->dieArray[1] instanceof BMDieSwing);
        $this->assertTrue($button1->dieArray[1]->needsSwingValue);

        $button2 = new BMButton;
        $button2->load('(2) p(V)', 'Test2');
        $this->assertEquals('(2) p(V)', $button2->recipe);
        $this->assertFalse(isset($button2->dieArray[1]->max));
        $this->assertTrue($button2->dieArray[1] instanceof BMDieSwing);
        $this->assertTrue($button2->dieArray[1]->needsSwingValue);

        // load game
        $game = new BMGame($gameId, array(self::$userId1WithoutAutopass,
                                          self::$userId2WithoutAutopass),
                           array('', ''), 2);
        $this->assertEquals(BMGameState::START_GAME, $game->gameState);
        $this->assertEquals(2, $game->maxWins);
        $game->buttonArray = array($button1, $button2);
        $game->waitingOnActionArray = array(FALSE, FALSE);
        $game->proceed_to_next_user_action();

        // specify swing dice correctly
        $game->swingValueArrayArray = array(array('X' => 7), array('V' => 11));
        $game->proceed_to_next_user_action();
        $this->assertTrue($game->activeDieArrayArray[0][1] instanceof BMDieSwing);
        $this->assertTrue($game->activeDieArrayArray[1][1] instanceof BMDieSwing);
        $this->assertFalse($game->activeDieArrayArray[0][1]->needsSwingValue);
        $this->assertFalse($game->activeDieArrayArray[1][1]->needsSwingValue);

        $this->assertEquals(1, array_sum($game->waitingOnActionArray));
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals(array(array('X' => 7), array('V' => 11)),
                            $game->swingValueArrayArray);
        $this->assertEquals(7,  $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals(11, $game->activeDieArrayArray[1][1]->max);
        $this->assertNotNull($game->activeDieArrayArray[0][1]->value);
        $this->assertNotNull($game->activeDieArrayArray[1][1]->value);

        $newDie = new BMDie;
        $newDie->init(4);
        $newDie->ownerObject = $game->activeDieArrayArray[0][1]->ownerObject;
        $newDie->playerIdx = $game->activeDieArrayArray[0][1]->playerIdx;
        $newDie->originalPlayerIdx = $game->activeDieArrayArray[0][1]->originalPlayerIdx;

        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][1] = $newDie;
        $game->activeDieArrayArray = $dieArrayArray;

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(array(array('X' => 7), array('V' => 11)),
                            $game->swingValueArrayArray);
    }

    /**
     * The following unit tests ensure that the number of passes is updated
     * correctly.
     *
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_all_pass() {
        // create a dummy game that will be overwritten
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Wiseman', 'Wiseman'), 4);
        $gameId = $retval['gameId'];

        // load buttons
        $button1 = new BMButton;
        $button1->load('(1) (1)', 'Test1');

        $button2 = new BMButton;
        $button2->load('s(20) s(20)', 'Test2');

        // load game
        $game = new BMGame($gameId, array(self::$userId1WithoutAutopass,
                                          self::$userId2WithoutAutopass),
                           array('', ''), 2);
        $game->buttonArray = array($button1, $button2);

        $game->waitingOnActionArray = array(FALSE, FALSE);
        $game->proceed_to_next_user_action();

        $this->assertCount(2, $game->activeDieArrayArray[0]);
        $this->assertCount(2, $game->activeDieArrayArray[1]);

        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 1;
        $dieArrayArray[0][1]->value = 1;
        $dieArrayArray[1][0]->value = 20;
        $dieArrayArray[1][1]->value = 20;

        // artificially guarantee that the active player is player 1
        $game->activePlayerIdx = 0;
        $game->waitingOnActionArray = array(TRUE, FALSE);

        // player 1 passes
        $game->attack = array(0, 1, array(), array(), 'Pass');
        $game->proceed_to_next_user_action();

        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals(1, $game->activePlayerIdx);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(array(array('W' => 0, 'L' => 0, 'D' => 0),
                                  array('W' => 0, 'L' => 0, 'D' => 0)),
                            $game->gameScoreArrayArray);
        $this->assertEquals(1, $game->nRecentPasses);

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals(1, $game->activePlayerIdx);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(array(array('W' => 0, 'L' => 0, 'D' => 0),
                                  array('W' => 0, 'L' => 0, 'D' => 0)),
                            $game->gameScoreArrayArray);
        $this->assertEquals(1, $game->nRecentPasses);
        $this->assertCount(2, $game->activeDieArrayArray[0]);
        $this->assertCount(2, $game->activeDieArrayArray[1]);

        // player 2 passes
        $game->attack = array(1, 0, array(), array(), 'Pass');
        $game->proceed_to_next_user_action();

        // beginning of round 2, active dice reloaded from Wiseman
        $this->assertEquals(array(array('W' => 0, 'L' => 1, 'D' => 0),
                                  array('W' => 1, 'L' => 0, 'D' => 0)),
                            $game->gameScoreArrayArray);
        $this->assertEquals(0, $game->nRecentPasses);
        $this->assertCount(4, $game->activeDieArrayArray[0]);
        $this->assertCount(4, $game->activeDieArrayArray[1]);
    }

    /**
     * The following unit tests ensure that autopass works correctly.
     *
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_autopass() {
        // create a dummy game that will be overwritten
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId3WithAutopass),
                                                   array('Bunnies', 'Peace'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $this->assertEquals('(1) (1) (1) (1) (X)', $game->buttonArray[0]->recipe);
        $this->assertEquals('s(10) s(12) s(20) s(X) s(X)', $game->buttonArray[1]->recipe);

        $this->assertCount(5, $game->activeDieArrayArray[0]);
        $this->assertCount(5, $game->activeDieArrayArray[1]);

        $game->swingValueArrayArray = array(array('X' => 4), array('X' => 20));
        $game->proceed_to_next_user_action();

        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 1;
        $dieArrayArray[0][1]->value = 1;
        $dieArrayArray[0][2]->value = 1;
        $dieArrayArray[0][3]->value = 1;
        $dieArrayArray[0][4]->value = 4;
        $dieArrayArray[1][0]->value = 5;
        $dieArrayArray[1][1]->value = 12;
        $dieArrayArray[1][2]->value = 20;
        $dieArrayArray[1][3]->value = 20;
        $dieArrayArray[1][4]->value = 20;

        // artificially guarantee that the active player is player 1
        $game->activePlayerIdx = 0;
        $game->waitingOnActionArray = array(TRUE, FALSE);

        self::save_game($game);
        $game = self::load_game($gameId);

        // player 1 performs skill attack, player 2 autopasses
        $game->attack = array(0, 1, array(0, 4), array(0), 'Skill');
        $game->proceed_to_next_user_action();
        $this->assertCount(4, $game->activeDieArrayArray[1]);
        self::save_game($game);
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals(0, $game->activePlayerIdx);
        $this->assertEquals(array(TRUE, FALSE), $game->waitingOnActionArray);
        $this->assertCount(4, $game->activeDieArrayArray[1]);
        $this->assertCount(1, $game->capturedDieArrayArray[0]);

        // player 1 passes
        $game->attack = array(0, 1, array(), array(), 'Pass');
        $game->proceed_to_next_user_action();
        self::save_game($game);
        $game = self::load_game($gameId);
        $game->swingValueArrayArray = array(array('X' => 4), array('X' => 20));
        self::save_game($game);
        $game = self::load_game($gameId);

        // should now be at the beginning of round 2
        $this->assertEquals(array(array('W' => 0, 'L' => 1, 'D' => 0),
                                  array('W' => 1, 'L' => 0, 'D' => 0)),
                            $game->gameScoreArrayArray);
        $this->assertCount(5, $game->activeDieArrayArray[0]);
        $this->assertCount(5, $game->activeDieArrayArray[1]);
    }

    /**
     * The following unit tests ensure that twin dice work correctly.
     *
     * @depends test_create_user
     *
     * @covers BMInterface::create_game
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    function test_twin_die() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Cthulhu', 'Bill'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        // load game
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $this->assertEquals(array(array(), array('V' => NULL)),
                            $game->swingValueArrayArray);

        // specify swing dice correctly
        $game->swingValueArrayArray = array(array(), array('V' => 11));
        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertTrue($game->activeDieArrayArray[1][3]->dice[0] instanceof BMDieSwing);
        $this->assertFalse($game->activeDieArrayArray[1][3]->dice[0]->needsSwingValue);
        $this->assertTrue($game->activeDieArrayArray[1][3]->dice[1] instanceof BMDieSwing);
        $this->assertFalse($game->activeDieArrayArray[1][3]->dice[1]->needsSwingValue);

        $this->assertEquals(1, array_sum($game->waitingOnActionArray));
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals(array(array(), array('V' => 11)),
                            $game->swingValueArrayArray);
        $this->assertEquals( 1, $game->activeDieArrayArray[0][0]->min);
        $this->assertEquals( 1, $game->activeDieArrayArray[0][1]->min);
        $this->assertEquals( 2, $game->activeDieArrayArray[0][2]->min);
        $this->assertEquals( 2, $game->activeDieArrayArray[0][3]->min);
        $this->assertEquals( 2, $game->activeDieArrayArray[0][4]->min);
        $this->assertEquals( 1, $game->activeDieArrayArray[1][0]->min);
        $this->assertEquals( 1, $game->activeDieArrayArray[1][1]->min);
        $this->assertEquals( 1, $game->activeDieArrayArray[1][2]->min);
        $this->assertEquals( 2, $game->activeDieArrayArray[1][3]->min);
        $this->assertEquals( 4, $game->activeDieArrayArray[0][0]->max);
        $this->assertEquals(20, $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals(12, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals(18, $game->activeDieArrayArray[0][3]->max);
        $this->assertEquals(26, $game->activeDieArrayArray[0][4]->max);
        $this->assertEquals(20, $game->activeDieArrayArray[1][0]->max);
        $this->assertEquals(20, $game->activeDieArrayArray[1][1]->max);
        $this->assertEquals(20, $game->activeDieArrayArray[1][2]->max);
        $this->assertEquals(22, $game->activeDieArrayArray[1][3]->max);

        $this->assertNotNull($game->activeDieArrayArray[0][0]->value);
        $this->assertNotNull($game->activeDieArrayArray[0][1]->value);
        $this->assertNotNull($game->activeDieArrayArray[0][2]->value);
        $this->assertNotNull($game->activeDieArrayArray[0][3]->value);
        $this->assertNotNull($game->activeDieArrayArray[0][4]->value);
        $this->assertNotNull($game->activeDieArrayArray[1][0]->value);
        $this->assertNotNull($game->activeDieArrayArray[1][1]->value);
        $this->assertNotNull($game->activeDieArrayArray[1][2]->value);
        $this->assertNotNull($game->activeDieArrayArray[1][3]->value);

        // artificially set player 1 as winning initiative
        $game->playerWithInitiativeIdx = 0;
        $game->activePlayerIdx = 0;
        $game->waitingOnActionArray = array(TRUE, FALSE);
        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 1;
        $dieArrayArray[0][1]->value = 2;
        $dieArrayArray[0][2]->value = 3;
        $dieArrayArray[0][3]->value = 13;
        $dieArrayArray[0][4]->value = 13;
        $dieArrayArray[1][0]->value = 4;
        $dieArrayArray[1][1]->value = 12;
        $dieArrayArray[1][2]->value = 5;
        $dieArrayArray[1][3]->value = 6;

        // perform valid attack
        $game->attack = array(0,        // attackerPlayerIdx
                              1,        // defenderPlayerIdx
                              array(2), // attackerAttackDieIdxArray
                              array(3), // defenderAttackDieIdxArray
                              'Shadow'); // attackType

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertCount(5, $game->activeDieArrayArray[0]);
        $this->assertCount(3, $game->activeDieArrayArray[1]);
        $this->assertCount(1, $game->capturedDieArrayArray[0]);
        $this->assertCount(0, $game->capturedDieArrayArray[1]);
        $this->assertEquals(22, $game->capturedDieArrayArray[0][0]->max);
        $this->assertEquals(6, $game->capturedDieArrayArray[0][0]->value);
    }

    /**
     * The following unit tests ensure that konstant works correctly.
     *
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    function test_konstant() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Al-Khwarizmi',
                                                         'Carl Friedrich Gauss'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        // load game
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);
        $this->assertEquals(1, array_sum($game->waitingOnActionArray));
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals( 4, $game->activeDieArrayArray[0][0]->max);
        $this->assertEquals( 6, $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals( 8, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals(12, $game->activeDieArrayArray[0][3]->max);
        $this->assertEquals(20, $game->activeDieArrayArray[0][4]->max);
        $this->assertEquals( 6, $game->activeDieArrayArray[1][0]->max);
        $this->assertEquals( 8, $game->activeDieArrayArray[1][1]->max);
        $this->assertEquals( 8, $game->activeDieArrayArray[1][2]->max);
        $this->assertEquals(24, $game->activeDieArrayArray[1][3]->max);
        $this->assertEquals(20, $game->activeDieArrayArray[1][4]->max);
        $this->assertTrue($game->activeDieArrayArray[0][1]->has_skill('Konstant'));
        $this->assertFalse($game->activeDieArrayArray[0][1]->doesReroll);
        $this->assertTrue($game->activeDieArrayArray[1][0]->has_skill('Konstant'));
        $this->assertFalse($game->activeDieArrayArray[1][0]->doesReroll);

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
        $dieArrayArray[0][2]->value = 1;
        $dieArrayArray[0][3]->value = 1;
        $dieArrayArray[0][4]->value = 1;
        $dieArrayArray[1][0]->value = 2;
        $dieArrayArray[1][1]->value = 2;
        $dieArrayArray[1][2]->value = 2;
        $dieArrayArray[1][3]->value = 2;
        $dieArrayArray[1][4]->value = 2;

        // perform valid attack
        $game->attack = array(0,        // attackerPlayerIdx
                              1,        // defenderPlayerIdx
                              array(0, 1), // attackerAttackDieIdxArray
                              array(0), // defenderAttackDieIdxArray
                              'Skill'); // attackType

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertCount(5, $game->activeDieArrayArray[0]);
        $this->assertCount(4, $game->activeDieArrayArray[1]);
        $this->assertCount(1, $game->capturedDieArrayArray[0]);
        $this->assertCount(0, $game->capturedDieArrayArray[1]);
        $this->assertEquals(6, $game->capturedDieArrayArray[0][0]->max);
        $this->assertEquals(2, $game->capturedDieArrayArray[0][0]->value);

        // check explicitly that the konstant die does not reroll
        $this->assertEquals(1, $game->activeDieArrayArray[0][1]->value);
        $this->assertFalse($game->activeDieArrayArray[0][1]->doesReroll);
    }

    /**
     * The following unit tests ensure that konstant works correctly.
     *
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     * @covers BMInterface::adjust_fire
     */
    function test_fire() {
        // (4) (6) F(8) (20) (X) vs F(4) F(6) (6) (12) (X)
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Poly',
                                                         'Adam Spam'), 4);
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

        $retval = $this->object->adjust_fire(
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

    /**
     * The following unit tests ensure that surrender attacks work correctly.
     *
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_surrender() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Sonia', 'Tamiya'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $game->playerWithInitiativeIdx = 1;
        $game->activePlayerIdx = 1;
        $game->waitingOnActionArray = array(FALSE, TRUE);
        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 4;
        $dieArrayArray[0][1]->value = 2;
        $dieArrayArray[0][2]->value = 8;
        $dieArrayArray[0][3]->value = 12;
        $dieArrayArray[0][4]->value = 7;
        $dieArrayArray[1][0]->value = 4;
        $dieArrayArray[1][1]->value = 1;
        $dieArrayArray[1][2]->value = 8;
        $dieArrayArray[1][3]->value = 12;
        $dieArrayArray[1][4]->value = 17;

        // perform invalid surrender attack with dice selected
        $game->attack = array(1,        // attackerPlayerIdx
                              0,        // defenderPlayerIdx
                              array(2), // attackerAttackDieIdxArray
                              array(1), // defenderAttackDieIdxArray
                              'Surrender'); // attackType

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(1, $game->activePlayerIdx);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);

        $this->assertEquals(array(array('W' => 0, 'L' => 0, 'D' => 0),
                                  array('W' => 0, 'L' => 0, 'D' => 0)),
                            $game->gameScoreArrayArray);
        $this->assertCount(5, $game->activeDieArrayArray[0]);
        $this->assertCount(5, $game->activeDieArrayArray[1]);
        $this->assertCount(0, $game->capturedDieArrayArray[0]);
        $this->assertCount(0, $game->capturedDieArrayArray[1]);

        // perform invalid surrender attack with non-active player
        $game->attack = array(0,        // attackerPlayerIdx
                              1,        // defenderPlayerIdx
                              array(),  // attackerAttackDieIdxArray
                              array(),  // defenderAttackDieIdxArray
                              'Surrender'); // attackType

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(1, $game->activePlayerIdx);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);

        $this->assertEquals(array(array('W' => 0, 'L' => 0, 'D' => 0),
                                  array('W' => 0, 'L' => 0, 'D' => 0)),
                            $game->gameScoreArrayArray);
        $this->assertCount(5, $game->activeDieArrayArray[0]);
        $this->assertCount(5, $game->activeDieArrayArray[1]);
        $this->assertCount(0, $game->capturedDieArrayArray[0]);
        $this->assertCount(0, $game->capturedDieArrayArray[1]);

        // perform valid surrender attack
        $game->attack = array(1,        // attackerPlayerIdx
                              0,        // defenderPlayerIdx
                              array(),  // attackerAttackDieIdxArray
                              array(),  // defenderAttackDieIdxArray
                              'Surrender'); // attackType

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(BMGameState::START_TURN, $game->gameState);

        $this->assertEquals(array(array('W' => 1, 'L' => 0, 'D' => 0),
                                  array('W' => 0, 'L' => 1, 'D' => 0)),
                            $game->gameScoreArrayArray);
        $this->assertCount(5, $game->activeDieArrayArray[0]);
        $this->assertCount(5, $game->activeDieArrayArray[1]);
        $this->assertCount(0, $game->capturedDieArrayArray[0]);
        $this->assertCount(0, $game->capturedDieArrayArray[1]);
    }

    /**
     * The following unit tests ensure that the autoplay bug doesn't occur.
     *
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_autoplay_bug() {
        // autoplay bug requires autopass turned on
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId4WithAutopass),
                                                   array('Scorpion', 'Kakita'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        // artificially change button recipes
        $button1 = $game->buttonArray[0];
        $button1->recipe = '(1) (1) (1)';
        $button1->hasAlteredRecipe = TRUE;
        $this->assertEquals('(1) (1) (1)', $game->buttonArray[0]->recipe);

        $button2 = $game->buttonArray[1];
        $button2->recipe = '(1) (1) (1,1) (1,1)';
        $button2->hasAlteredRecipe = TRUE;
        $this->assertEquals('(1) (1) (1,1) (1,1)', $game->buttonArray[1]->recipe);

        $game->activeDieArrayArray = array(array(), array());
        $game->gameState = BMGameState::START_GAME;

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(array(TRUE, FALSE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals( 1, $game->activeDieArrayArray[0][0]->max);
        $this->assertEquals( 1, $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals( 1, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals( 1, $game->activeDieArrayArray[1][0]->max);
        $this->assertEquals( 1, $game->activeDieArrayArray[1][1]->max);
        $this->assertEquals( 2, $game->activeDieArrayArray[1][2]->max);
        $this->assertEquals( 2, $game->activeDieArrayArray[1][3]->max);

        // round 1, turn 1, player 1 to attack
        // [1 1 1] vs [1 1 2 2]
        $this->assertNULL($game->attack);
        $game->attack = array(0,           // attackerPlayerIdx
                              1,           // defenderPlayerIdx
                              array(0,1),  // attackerAttackDieIdxArray
                              array(2),    // defenderAttackDieIdxArray
                              'Skill');    // attackType

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertCount(3, $game->activeDieArrayArray[0]);
        $this->assertCount(3, $game->activeDieArrayArray[1]);
        $this->assertCount(1, $game->capturedDieArrayArray[0]);
        $this->assertCount(0, $game->capturedDieArrayArray[1]);
        $this->assertEquals(1, $game->activeDieArrayArray[0][0]->value);
        $this->assertEquals(1, $game->activeDieArrayArray[0][1]->value);
        $this->assertEquals(2, $game->capturedDieArrayArray[0][0]->value);
        $this->assertTrue($game->capturedDieArrayArray[0][0]->has_flag('WasJustCaptured'));

        // round 1, turn 2, player 2 to attack
        // [1 1 1] vs [1 1 2]
        $this->assertNULL($game->attack);
        $game->attack = array(1,           // attackerPlayerIdx
                              0,           // defenderPlayerIdx
                              array(0),    // attackerAttackDieIdxArray
                              array(0),    // defenderAttackDieIdxArray
                              'Power');    // attackType

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(array(TRUE, FALSE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertCount(2, $game->activeDieArrayArray[0]);
        $this->assertCount(3, $game->activeDieArrayArray[1]);
        $this->assertCount(1, $game->capturedDieArrayArray[0]);
        $this->assertCount(1, $game->capturedDieArrayArray[1]);
        $this->assertEquals(1, $game->activeDieArrayArray[1][0]->value);
        $this->assertEquals(1, $game->capturedDieArrayArray[1][0]->value);
        $this->assertFalse($game->capturedDieArrayArray[0][0]->has_flag('WasJustCaptured'));
        $this->assertTrue($game->capturedDieArrayArray[1][0]->has_flag('WasJustCaptured'));

        // round 1, turn 3, player 1 to attack
        // [1 1] vs [1 1 2]
        $this->assertNULL($game->attack);
        $game->attack = array(0,           // attackerPlayerIdx
                              1,           // defenderPlayerIdx
                              array(0,1),  // attackerAttackDieIdxArray
                              array(2),    // defenderAttackDieIdxArray
                              'Skill');    // attackType

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertCount(2, $game->activeDieArrayArray[0]);
        $this->assertCount(2, $game->activeDieArrayArray[1]);
        $this->assertCount(2, $game->capturedDieArrayArray[0]);
        $this->assertCount(1, $game->capturedDieArrayArray[1]);
        $this->assertEquals(1, $game->activeDieArrayArray[0][0]->value);
        $this->assertEquals(1, $game->activeDieArrayArray[0][1]->value);
        $this->assertEquals(2, $game->capturedDieArrayArray[0][1]->value);

        // round 1, turn 4, player 2 to attack
        // [1 1] vs [1 1]
        $this->assertNULL($game->attack);
        $game->attack = array(1,           // attackerPlayerIdx
                              0,           // defenderPlayerIdx
                              array(0),    // attackerAttackDieIdxArray
                              array(0),    // defenderAttackDieIdxArray
                              'Power');    // attackType

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(array(TRUE, FALSE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertCount(1, $game->activeDieArrayArray[0]);
        $this->assertCount(2, $game->activeDieArrayArray[1]);
        $this->assertCount(2, $game->capturedDieArrayArray[0]);
        $this->assertCount(2, $game->capturedDieArrayArray[1]);
        $this->assertEquals(1, $game->activeDieArrayArray[1][0]->value);
        $this->assertEquals(1, $game->capturedDieArrayArray[1][1]->value);

        // round 1, turn 5, player 1 to attack
        // [1] vs [1 1]
        $this->assertNULL($game->attack);
        $game->attack = array(0,           // attackerPlayerIdx
                              1,           // defenderPlayerIdx
                              array(0),    // attackerAttackDieIdxArray
                              array(0),    // defenderAttackDieIdxArray
                              'Skill');    // attackType

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertCount(1, $game->activeDieArrayArray[0]);
        $this->assertCount(1, $game->activeDieArrayArray[1]);
        $this->assertCount(3, $game->capturedDieArrayArray[0]);
        $this->assertCount(2, $game->capturedDieArrayArray[1]);
        $this->assertEquals(1, $game->activeDieArrayArray[0][0]->value);
        $this->assertEquals(1, $game->capturedDieArrayArray[0][2]->value);
    }

    /**
     * Check that a decline of an auxiliary die works correctly.
     *
     * @depends test_create_user
     *
     * @covers BMInterface::react_to_auxiliary
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_react_to_auxiliary_both_aux_decline() {
        // Lancelot : (10) (12) (20) (20) (X) +(X)
        // Gawaine  :  (4)  (4) (12) (20) (X) +(6)
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Lancelot', 'Gawaine'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::CHOOSE_AUXILIARY_DICE, $game->gameState);
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertCount(6, $game->activeDieArrayArray[0]);
        $this->assertCount(6, $game->activeDieArrayArray[1]);

        // a non-player attempts an action
        $this->assertFalse(
            $this->object->react_to_auxiliary(
                0,
                $gameId,
                'decline')
        );

        // player 1 attempts an invalid action
        $this->assertFalse(
            $this->object->react_to_auxiliary(
                self::$userId1WithoutAutopass,
                $gameId,
                'rubbish')
        );

        // player 1 declines
        $this->assertTrue(
            $this->object->react_to_auxiliary(
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
     * @depends test_create_user
     *
     * @covers BMInterface::react_to_auxiliary
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     * @covers BMGame::add_selected_auxiliary_dice
     */
    public function test_react_to_auxiliary_one_aux_decline() {
        // Kublai   :  (4) (8) (12) (20) (X)
        // Gawaine  :  (4) (4) (12) (20) (X) +(6)
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Kublai', 'Gawaine'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::CHOOSE_AUXILIARY_DICE, $game->gameState);
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertCount(6, $game->activeDieArrayArray[0]);
        $this->assertCount(6, $game->activeDieArrayArray[1]);
        $this->assertTrue($game->activeDieArrayArray[0][5]->has_skill('Auxiliary'));

        // player 1 chooses to add auxiliary die
        $this->assertTrue(
            $this->object->react_to_auxiliary(
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
        $this->assertTrue($game->activeDieArrayArray[0][5]->selected);

        // player 1 tries incorrectly to act again
        $this->assertFalse(
            $this->object->react_to_auxiliary(
                self::$userId1WithoutAutopass,
                $gameId,
                'add',
                5)
        );

        // player 2 declines
        $this->assertTrue(
            $this->object->react_to_auxiliary(
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
     * @depends test_create_user
     *
     * @covers BMInterface::react_to_auxiliary
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     * @covers BMGame::add_selected_auxiliary_dice
     */
    public function test_react_to_auxiliary_one_aux_accept() {
        // Kublai   :  (4) (8) (12) (20) (X)
        // Gawaine  :  (4) (4) (12) (20) (X) +(6)
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Kublai', 'Gawaine'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::CHOOSE_AUXILIARY_DICE, $game->gameState);
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertCount(6, $game->activeDieArrayArray[0]);
        $this->assertCount(6, $game->activeDieArrayArray[1]);
        $this->assertTrue($game->activeDieArrayArray[0][5]->has_skill('Auxiliary'));

        // player 1 tries incorrectly adding a non-auxiliary die
        $this->assertFalse(
            $this->object->react_to_auxiliary(
                self::$userId1WithoutAutopass,
                $gameId,
                'add',
                0
            )
        );

        // player 1 chooses to add an auxiliary die
        $this->assertTrue(
            $this->object->react_to_auxiliary(
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
        $this->assertTrue($game->activeDieArrayArray[0][5]->selected);

        $this->assertTrue(
            $this->object->react_to_auxiliary(
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
     * @depends test_create_user
     *
     * @covers BMInterface::react_to_auxiliary
     */
    public function test_react_to_auxiliary_invalid() {
        $this->assertFalse($this->object->react_to_auxiliary(1.5, 2.5, 'ha!'));
    }

    /**
     * Check that a decline of a reserve die works correctly.
     *
     * @depends test_create_user
     *
     * @covers BMInterface::react_to_reserve
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_react_to_reserve_decline() {
        // Sailor Moon : (8) (8) (10) (20) r(6) r(10) r(20) r(20)
        // Queen Beryl : (4) (8) (12) (20) r(4) r(12) r(20) r(20)
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Sailor Moon', 'Queen Beryl'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $game->gameScoreArrayArray = array(array('W' => 0, 'L' => 1, 'D' => 0),
                                           array('W' => 1, 'L' => 0, 'D' => 0));
        $game->isPrevRoundWinnerArray = array(FALSE, TRUE);
        $game->waitingOnActionArray = array(FALSE, FALSE);
        $game->gameState = BMGameState::LOAD_DICE_INTO_BUTTONS;

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(BMGameState::CHOOSE_RESERVE_DICE, $game->gameState);
        $this->assertEquals(array(TRUE, FALSE), $game->waitingOnActionArray);
        $this->assertCount(8, $game->activeDieArrayArray[0]);
        $this->assertCount(8, $game->activeDieArrayArray[1]);

        // a non-player attempts an action
        $this->assertFalse(
            $this->object->react_to_reserve(
                0,
                $gameId,
                'decline')
        );

        // player 1 attempts an invalid action
        $this->assertFalse(
            $this->object->react_to_reserve(
                self::$userId1WithoutAutopass,
                $gameId,
                'rubbish')
        );

        // player 2 attempts a reserve action
        $this->assertFalse(
            $this->object->react_to_reserve(
                self::$userId2WithoutAutopass,
                $gameId,
                'add',
                6)
        );

        // player 1 declines
        $this->assertTrue(
            $this->object->react_to_reserve(
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
     * @depends test_create_user
     *
     * @covers BMInterface::react_to_reserve
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_react_to_reserve_add() {
        // Sailor Moon : (8) (8) (10) (20) r(6) r(10) r(20) r(20)
        // Queen Beryl : (4) (8) (12) (20) r(4) r(12) r(20) r(20)
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Sailor Moon', 'Queen Beryl'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $game->gameScoreArrayArray = array(array('W' => 0, 'L' => 1, 'D' => 0),
                                           array('W' => 1, 'L' => 0, 'D' => 0));
        $game->isPrevRoundWinnerArray = array(FALSE, TRUE);
        $game->waitingOnActionArray = array(FALSE, FALSE);
        $game->gameState = BMGameState::LOAD_DICE_INTO_BUTTONS;

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(BMGameState::CHOOSE_RESERVE_DICE, $game->gameState);
        $this->assertEquals(array(TRUE, FALSE), $game->waitingOnActionArray);
        $this->assertCount(8, $game->activeDieArrayArray[0]);
        $this->assertCount(8, $game->activeDieArrayArray[1]);

        // a non-player attempts an action
        $this->assertFalse(
            $this->object->react_to_reserve(
                0,
                $gameId,
                'add')
        );

        // player 1 adds reserve die
        $this->assertTrue(
            $this->object->react_to_reserve(
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
     * Check that the reserve swing setting bug is fixed.
     *
     * @depends test_create_user
     *
     */
    public function test_reserve_swing_setting() {
        // Zomulgustar : t(4) p(5/23)! t(9) t(13) rdD(1) rsz(1) r^(1,1) rBqn(Z)?
        // Cammy Neko  : (4) (6) (12) (10,10) r(12) r(20) r(20) r(8,8)
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Zomulgustar', 'Cammy Neko'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        // specify option die
        $this->assertEquals(array(TRUE, FALSE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $game->optValueArrayArray = array(array(1 => 5), array());

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(BMGameState::START_TURN, $game->gameState);

        // artificially set player 2 as winning initiative
        $game->playerWithInitiativeIdx = 1;
        $game->activePlayerIdx = 1;
        $game->waitingOnActionArray = array(FALSE, TRUE);
        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 4;
        $dieArrayArray[0][1]->value = 5;
        $dieArrayArray[0][2]->value = 9;
        $dieArrayArray[0][3]->value = 13;
        $dieArrayArray[1][0]->value = 1;
        $dieArrayArray[1][1]->value = 1;
        $dieArrayArray[1][2]->value = 1;
        $dieArrayArray[1][3]->value = 2;

        $game->attack = array(1, 0, array(), array(), 'Surrender');

        // we should now be at the point where the bug triggers, at the stage of
        // loading the previous round's swing values
        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(2, $game->roundNumber);
        $this->assertEquals(array(array('W' => 1, 'L' => 0, 'D' => 0),
                                  array('W' => 0, 'L' => 1, 'D' => 0)),
                            $game->gameScoreArrayArray);
        $this->assertEquals(BMGameState::CHOOSE_RESERVE_DICE, $game->gameState);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
    }

    /**
     * Check that Echo games can be created and played correctly.
     *
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_echo_vs_other() {
        // Echo : none
        // Avis : (4) (4) (10) (12) (X)
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Echo', 'Avis'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $this->assertInstanceOf('BMGame', $game);
        $this->assertEquals(24, $game->gameState);
        $this->assertEquals('Echo', $game->buttonArray[0]->name);
        $this->assertEquals($game, $game->buttonArray[0]->ownerObject);
        $this->assertEquals($game, $game->buttonArray[1]->ownerObject);
        $this->assertCount(5, $game->activeDieArrayArray[0]);
        $this->assertEquals(4,  $game->activeDieArrayArray[0][0]->max);
        $this->assertEquals(4,  $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals(10, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals(12, $game->activeDieArrayArray[0][3]->max);
        $this->assertFalse(isset($game->activeDieArrayArray[0][4]->max));
        $this->assertEquals(0, $game->activeDieArrayArray[0][0]->playerIdx);
        $this->assertEquals(0, $game->activeDieArrayArray[0][1]->playerIdx);
        $this->assertEquals(0, $game->activeDieArrayArray[0][2]->playerIdx);
        $this->assertEquals(0, $game->activeDieArrayArray[0][3]->playerIdx);
        $this->assertEquals(0, $game->activeDieArrayArray[0][4]->playerIdx);
        $this->assertEquals(0, $game->activeDieArrayArray[0][0]->originalPlayerIdx);
        $this->assertEquals(0, $game->activeDieArrayArray[0][1]->originalPlayerIdx);
        $this->assertEquals(0, $game->activeDieArrayArray[0][2]->originalPlayerIdx);
        $this->assertEquals(0, $game->activeDieArrayArray[0][3]->originalPlayerIdx);
        $this->assertEquals(0, $game->activeDieArrayArray[0][4]->originalPlayerIdx);

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals('(4) (4) (10) (12) (X)', $game->buttonArray[0]->recipe);
    }

    /**
     * @depends test_create_user
     *
     *
     * @coversNothing
     */
    public function test_echo_recipe_save() {
        // Echo : none
        // Avis : (4) (4) (10) (12) (X)
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Echo', 'Avis'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        // artificially change Echo's recipe
        $button = $game->buttonArray[0];
        $button->recipe = '(V)';
        $button->hasAlteredRecipe = TRUE;
        $this->assertEquals('(V)', $game->buttonArray[0]->recipe);

        $game->activeDieArrayArray = array(array(), array());
        $game->gameState = BMGameState::START_GAME;

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals('(V)', $game->buttonArray[0]->recipe);
    }

    /**
     * The following unit tests ensure that declined courtesy auxiliary swing dice work correctly.
     *
     * @depends test_create_user
     *
     * @coversNothing
     */
    public function test_declined_courtesy_auxiliary_swing_dice() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Ayeka', 'Merlin'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $this->assertEquals(BMGameState::CHOOSE_AUXILIARY_DICE, $game->gameState);
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertCount(0, $game->swingRequestArrayArray[0]);
        $this->assertCount(1, $game->swingRequestArrayArray[1]);

        // decline auxiliary dice
        $game->waitingOnActionArray = array(FALSE, FALSE);
        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);

        $this->assertEquals(array(), $game->swingRequestArrayArray[0]);
        $this->assertTrue(array_key_exists('X', $game->swingRequestArrayArray[1]));
        $this->assertEquals(array(array(), array('X' => NULL)), $game->swingValueArrayArray);
        $this->assertCount(4, $game->activeDieArrayArray[0]);
        $this->assertCount(5, $game->activeDieArrayArray[1]);

        $game->swingValueArrayArray = array(array(), array('X' => 5));
        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
    }

    /**
     * Check that option dice are loaded and saved correctly.
     *
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_option_game() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Apples', 'Green Apple'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        // check buttons
        $this->assertEquals('Apples', $game->buttonArray[0]->name);
        $this->assertEquals('(8) (8) (2/12) (8/16) (20/24)', $game->buttonArray[0]->recipe);
        $this->assertEquals('Green Apple', $game->buttonArray[1]->name);
        $this->assertEquals('(8) (10) (1/8) (6/12) (12/20)', $game->buttonArray[1]->recipe);

        // load game
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);
        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $this->assertEquals(array(array(2 => array(2, 12), 3 => array(8, 16), 4 => array(20, 24)),
                                  array(2 => array(1, 8), 3 => array(6, 12), 4 => array(12, 20))),
                            $game->optRequestArrayArray);

        // specify option dice incorrectly
        $game->optValueArrayArray[0][2] = 6;

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertFalse(isset($game->activeDieArrayArray[0][2]->max));

        // specify option dice partially
        $game->optValueArrayArray[0][2] = 12;
        $game->optValueArrayArray[0][3] = 16;
        $game->optValueArrayArray[0][4] = 20;

        $this->assertEquals(array(array(2 => 12, 3 => 16, 4 => 20), array()),
                            $game->optValueArrayArray);

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(array(array(2 => 12, 3 => 16, 4 => 20), array()),
                            $game->optValueArrayArray);

        $this->assertTrue(isset($game->activeDieArrayArray[0][2]->max));
        $this->assertTrue(isset($game->activeDieArrayArray[0][3]->max));
        $this->assertTrue(isset($game->activeDieArrayArray[0][4]->max));
        $this->assertEquals(12, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals(16, $game->activeDieArrayArray[0][3]->max);
        $this->assertEquals(20, $game->activeDieArrayArray[0][4]->max);

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $this->assertEquals(array(FALSE, TRUE), $game->waitingOnActionArray);
        $out1 = $game->getJsonData(self::$userId1WithoutAutopass);
        $this->assertEquals(12, $out1['playerDataArray'][0]['activeDieArray'][2]['sides']);
        $this->assertEquals(16, $out1['playerDataArray'][0]['activeDieArray'][3]['sides']);
        $this->assertEquals(20, $out1['playerDataArray'][0]['activeDieArray'][4]['sides']);
        $this->assertNull($out1['playerDataArray'][1]['activeDieArray'][2]['sides']);
        $this->assertNull($out1['playerDataArray'][1]['activeDieArray'][3]['sides']);
        $this->assertNull($out1['playerDataArray'][1]['activeDieArray'][4]['sides']);
        $this->assertNull($out1['playerDataArray'][0]['activeDieArray'][0]['value']);
        $this->assertNull($out1['playerDataArray'][0]['activeDieArray'][1]['value']);
        $this->assertNull($out1['playerDataArray'][0]['activeDieArray'][2]['value']);
        $this->assertNull($out1['playerDataArray'][0]['activeDieArray'][3]['value']);
        $this->assertNull($out1['playerDataArray'][0]['activeDieArray'][4]['value']);
        $this->assertNull($out1['playerDataArray'][1]['activeDieArray'][0]['value']);
        $this->assertNull($out1['playerDataArray'][1]['activeDieArray'][1]['value']);
        $this->assertNull($out1['playerDataArray'][1]['activeDieArray'][2]['value']);
        $this->assertNull($out1['playerDataArray'][1]['activeDieArray'][3]['value']);
        $this->assertNull($out1['playerDataArray'][1]['activeDieArray'][4]['value']);
        $this->assertEquals('Option Die (with 20 sides)',
                            $out1['playerDataArray'][0]['activeDieArray'][4]['description']);

        $out2 = $game->getJsonData(self::$userId2WithoutAutopass);
        $this->assertNull($out2['playerDataArray'][0]['activeDieArray'][2]['sides']);
        $this->assertNull($out2['playerDataArray'][0]['activeDieArray'][3]['sides']);
        $this->assertNull($out2['playerDataArray'][0]['activeDieArray'][4]['sides']);
        $this->assertNull($out2['playerDataArray'][1]['activeDieArray'][2]['sides']);
        $this->assertNull($out2['playerDataArray'][1]['activeDieArray'][3]['sides']);
        $this->assertNull($out2['playerDataArray'][1]['activeDieArray'][4]['sides']);
        $this->assertNull($out2['playerDataArray'][0]['activeDieArray'][0]['value']);
        $this->assertNull($out2['playerDataArray'][0]['activeDieArray'][1]['value']);
        $this->assertNull($out2['playerDataArray'][0]['activeDieArray'][2]['value']);
        $this->assertNull($out2['playerDataArray'][0]['activeDieArray'][3]['value']);
        $this->assertNull($out2['playerDataArray'][0]['activeDieArray'][4]['value']);
        $this->assertNull($out2['playerDataArray'][1]['activeDieArray'][0]['value']);
        $this->assertNull($out2['playerDataArray'][1]['activeDieArray'][1]['value']);
        $this->assertNull($out2['playerDataArray'][1]['activeDieArray'][2]['value']);
        $this->assertNull($out2['playerDataArray'][1]['activeDieArray'][3]['value']);
        $this->assertNull($out2['playerDataArray'][1]['activeDieArray'][4]['value']);
        $this->assertEquals('Option Die (with 20 or 24 sides)',
                            $out2['playerDataArray'][0]['activeDieArray'][4]['description']);

        // specify option dice fully
        $this->assertEquals(12, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals(16, $game->activeDieArrayArray[0][3]->max);
        $this->assertEquals(20, $game->activeDieArrayArray[0][4]->max);

        $game->optValueArrayArray[1][2] = 8;
        $game->optValueArrayArray[1][3] = 6;
        $game->optValueArrayArray[1][4] = 12;

        $this->assertEquals(array(array(2 => 12, 3 => 16, 4 => 20),
                                  array(2 =>  8, 3 =>  6, 4 => 12)),
                            $game->optValueArrayArray);

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(array(array(2 => 12, 3 => 16, 4 => 20),
                                  array(2 =>  8, 3 =>  6, 4 => 12)),
                            $game->optValueArrayArray);

        $this->assertTrue(isset($game->activeDieArrayArray[1][2]->max));
        $this->assertTrue(isset($game->activeDieArrayArray[1][3]->max));
        $this->assertTrue(isset($game->activeDieArrayArray[1][4]->max));
        $this->assertEquals(8, $game->activeDieArrayArray[1][2]->max);
        $this->assertEquals(6, $game->activeDieArrayArray[1][3]->max);
        $this->assertEquals(12, $game->activeDieArrayArray[1][4]->max);

        $this->assertInstanceOf('BMDieOption', $game->activeDieArrayArray[0][2]);
        $this->assertInstanceOf('BMDieOption', $game->activeDieArrayArray[0][3]);
        $this->assertInstanceOf('BMDieOption', $game->activeDieArrayArray[0][4]);
        $this->assertInstanceOf('BMDieOption', $game->activeDieArrayArray[1][2]);
        $this->assertInstanceOf('BMDieOption', $game->activeDieArrayArray[1][3]);
        $this->assertInstanceOf('BMDieOption', $game->activeDieArrayArray[1][4]);
        $this->assertFalse($game->activeDieArrayArray[0][2]->needsOptionValue);
        $this->assertFalse($game->activeDieArrayArray[0][3]->needsOptionValue);
        $this->assertFalse($game->activeDieArrayArray[0][4]->needsOptionValue);
        $this->assertFalse($game->activeDieArrayArray[1][2]->needsOptionValue);
        $this->assertFalse($game->activeDieArrayArray[1][3]->needsOptionValue);
        $this->assertFalse($game->activeDieArrayArray[1][4]->needsOptionValue);

        $this->assertEquals(1, array_sum($game->waitingOnActionArray));
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals(8,  $game->activeDieArrayArray[0][0]->max);
        $this->assertEquals(8,  $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals(12, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals(16, $game->activeDieArrayArray[0][3]->max);
        $this->assertEquals(20, $game->activeDieArrayArray[0][4]->max);
        $this->assertEquals(8,  $game->activeDieArrayArray[1][0]->max);
        $this->assertEquals(10, $game->activeDieArrayArray[1][1]->max);
        $this->assertEquals(8,  $game->activeDieArrayArray[1][2]->max);
        $this->assertEquals(6,  $game->activeDieArrayArray[1][3]->max);
        $this->assertEquals(12, $game->activeDieArrayArray[1][4]->max);

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

        // now set the game as if it were almost at the end of the first round
        $activeDieArrayArray = array(array($game->activeDieArrayArray[0][0]),
                                     array($game->activeDieArrayArray[1][0]));
        $activeDieArrayArray[0][0]->value = 1;
        $activeDieArrayArray[1][0]->value = 1;
        $game->activeDieArrayArray = $activeDieArrayArray;
        $game->waitingOnActionArray = array(TRUE, FALSE);
        $game->activePlayerIdx = 0;
        $this->assertCount(1, $game->activeDieArrayArray[0]);
        $this->assertCount(1, $game->activeDieArrayArray[1]);
        $this->assertEquals(1, $game->activeDieArrayArray[0][0]->value);
        $this->assertEquals(1, $game->activeDieArrayArray[1][0]->value);

        // perform attack
        $this->assertNULL($game->attack);
        $game->attack = array(0,        // attackerPlayerIdx
                              1,        // defenderPlayerIdx
                              array(0), // attackerAttackDieIdxArray
                              array(0), // defenderAttackDieIdxArray
                              'Power'); // attackType

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(array(array('W' => 1, 'L' => 0, 'D' => 0),
                                  array('W' => 0, 'L' => 1, 'D' => 0)),
                            $game->gameScoreArrayArray);
        $this->assertEquals(array(array(2 => 12, 3 => 16, 4 => 20),
                                  array()),
                            $game->optValueArrayArray);
        $this->assertEquals(array(array(2 => 12, 3 => 16, 4 => 20),
                                  array(2 =>  8, 3 =>  6, 4 => 12)),
                            $game->prevOptValueArrayArray);
    }

    /**
     * @depends test_create_user
     *
     * @coversNothing
     */
    public function test_option_game_multiple_identical_option_bug() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Farrell', 'Farrell'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $this->assertEquals(array(array(2 => array(6, 20), 3 => array(6, 20), 4 => array(8, 12)),
                                  array(2 => array(6, 20), 3 => array(6, 20), 4 => array(8, 12))),
                            $game->optRequestArrayArray);
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_mood_swing_round() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Gilly', 'Igor'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        // check buttons
        $this->assertEquals('Gilly', $game->buttonArray[0]->name);
        $this->assertEquals('(6) (8) z(8) (20) (X)?', $game->buttonArray[0]->recipe);
        $this->assertEquals('Igor', $game->buttonArray[1]->name);
        $this->assertEquals('(3) (12) (20) (20) (X)?', $game->buttonArray[1]->recipe);

        // load game
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);
        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $this->assertEquals(array(array('X' => NULL), array('X' => NULL)),
                            $game->swingValueArrayArray);

        // specify swing dice correctly
        $game->swingValueArrayArray = array(array('X' => 19), array('X' => 4));

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertInstanceOf('BMDieSwing', $game->activeDieArrayArray[0][4]);
        $this->assertInstanceOf('BMDieSwing', $game->activeDieArrayArray[1][4]);
        $this->assertFalse($game->activeDieArrayArray[0][4]->needsSwingValue);
        $this->assertFalse($game->activeDieArrayArray[1][4]->needsSwingValue);

        $this->assertEquals(1, array_sum($game->waitingOnActionArray));
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals(array(array('X' => 19), array('X' => 4)),
                            $game->swingValueArrayArray);
        $this->assertEquals(6,  $game->activeDieArrayArray[0][0]->max);
        $this->assertEquals(8, $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals(8, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals(20, $game->activeDieArrayArray[0][3]->max);
        $this->assertEquals(19, $game->activeDieArrayArray[0][4]->max);
        $this->assertEquals(3,  $game->activeDieArrayArray[1][0]->max);
        $this->assertEquals(12,  $game->activeDieArrayArray[1][1]->max);
        $this->assertEquals(20,  $game->activeDieArrayArray[1][2]->max);
        $this->assertEquals(20,  $game->activeDieArrayArray[1][3]->max);
        $this->assertEquals(4,  $game->activeDieArrayArray[1][4]->max);
        $this->assertEquals(19, $game->activeDieArrayArray[0][4]->swingValue);
        $this->assertEquals(4,  $game->activeDieArrayArray[1][4]->swingValue);

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


        // round 1, turn 1
        // player 1: [6 8 8 20 19] showing [3 1 8 15 7], captured []
        // player 2: [3 12 20 20 4] showing [2 3 8 4 1], captured []
        // player 2 takes player 1's d8 showing 1 with his/her d4 mood swing showing 1
        // check that the player with initiative is set as the attacking player
        $this->assertEquals($game->activePlayerIdx, $game->playerWithInitiativeIdx);

        // artificially set player 2 as winning initiative
        $game->playerWithInitiativeIdx = 1;
        $game->activePlayerIdx = 1;
        $game->waitingOnActionArray = array(FALSE, TRUE);
        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 3;
        $dieArrayArray[0][1]->value = 1;
        $dieArrayArray[0][2]->value = 8;
        $dieArrayArray[0][3]->value = 15;
        $dieArrayArray[0][4]->value = 7;
        $dieArrayArray[1][0]->value = 2;
        $dieArrayArray[1][1]->value = 3;
        $dieArrayArray[1][2]->value = 8;
        $dieArrayArray[1][3]->value = 4;
        $dieArrayArray[1][4]->value = 1;

        $this->assertEquals(3, $game->activeDieArrayArray[0][0]->value);

        // perform attack
        $this->assertNULL($game->attack);
        $game->attack = array(1,        // attackerPlayerIdx
                              0,        // defenderPlayerIdx
                              array(4), // attackerAttackDieIdxArray
                              array(1), // defenderAttackDieIdxArray
                              'Power'); // attackType

        $preProceedSwingSize = $game->activeDieArrayArray[1][4]->swingValue;
        $this->assertEquals(4, $preProceedSwingSize);
        $game->proceed_to_next_user_action();

        $preSaveMoodMax = $game->activeDieArrayArray[1][4]->max;
        self::save_game($game);
        $game = self::load_game($game->gameId);
        $postSaveMoodMax = $game->activeDieArrayArray[1][4]->max;
        $postSaveSwingSize = $game->activeDieArrayArray[1][4]->swingValue;
        $this->assertEquals($preSaveMoodMax, $postSaveMoodMax);
        $this->assertEquals($preProceedSwingSize, $postSaveSwingSize);
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::save_game
     * @covers BMInterface::load_game
     */
    public function test_twin_mood_swing_round() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('TheFool', 'TheFool'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        // check buttons
        $this->assertEquals('TheFool', $game->buttonArray[0]->name);
        $this->assertEquals('v(5) v(10) vq(10) vs(15) s(R,R)?', $game->buttonArray[0]->recipe);
        $this->assertEquals('TheFool', $game->buttonArray[1]->name);
        $this->assertEquals('v(5) v(10) vq(10) vs(15) s(R,R)?', $game->buttonArray[1]->recipe);

        // load game
        $this->assertEquals(array(TRUE, TRUE), $game->waitingOnActionArray);
        $this->assertEquals(array(array(), array()), $game->capturedDieArrayArray);
        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $this->assertEquals(array(array('R' => NULL), array('R' => NULL)),
                            $game->swingValueArrayArray);
        $this->assertTrue($game->activeDieArrayArray[0][4]->has_skill('Mood'));
        $this->assertTrue($game->activeDieArrayArray[1][4]->has_skill('Mood'));

        // specify swing dice correctly
        $game->swingValueArrayArray = array(array('R' => 16), array('R' => 2));

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertTrue($game->activeDieArrayArray[0][4]->has_skill('Mood'));
        $this->assertTrue($game->activeDieArrayArray[1][4]->has_skill('Mood'));
        $this->assertInstanceOf('BMDieTwin', $game->activeDieArrayArray[0][4]);
        $this->assertInstanceOf('BMDieTwin', $game->activeDieArrayArray[1][4]);
        $this->assertInstanceOf('BMDieSwing', $game->activeDieArrayArray[0][4]->dice[0]);
        $this->assertInstanceOf('BMDieSwing', $game->activeDieArrayArray[0][4]->dice[1]);
        $this->assertInstanceOf('BMDieSwing', $game->activeDieArrayArray[1][4]->dice[0]);
        $this->assertInstanceOf('BMDieSwing', $game->activeDieArrayArray[1][4]->dice[1]);
        $this->assertFalse($game->activeDieArrayArray[0][4]->dice[0]->needsSwingValue);
        $this->assertFalse($game->activeDieArrayArray[0][4]->dice[1]->needsSwingValue);
        $this->assertFalse($game->activeDieArrayArray[1][4]->dice[0]->needsSwingValue);
        $this->assertFalse($game->activeDieArrayArray[1][4]->dice[1]->needsSwingValue);


        $this->assertEquals(1, array_sum($game->waitingOnActionArray));
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
        $this->assertEquals(array(array('R' => 16), array('R' => 2)),
                            $game->swingValueArrayArray);
        $this->assertEquals(5,  $game->activeDieArrayArray[0][0]->max);
        $this->assertEquals(10, $game->activeDieArrayArray[0][1]->max);
        $this->assertEquals(10, $game->activeDieArrayArray[0][2]->max);
        $this->assertEquals(15, $game->activeDieArrayArray[0][3]->max);
        $this->assertEquals(32, $game->activeDieArrayArray[0][4]->max);
        $this->assertEquals(5,  $game->activeDieArrayArray[1][0]->max);
        $this->assertEquals(10, $game->activeDieArrayArray[1][1]->max);
        $this->assertEquals(10, $game->activeDieArrayArray[1][2]->max);
        $this->assertEquals(15, $game->activeDieArrayArray[1][3]->max);
        $this->assertEquals(4,  $game->activeDieArrayArray[1][4]->max);
        $this->assertEquals(16, $game->activeDieArrayArray[0][4]->dice[0]->swingValue);
        $this->assertEquals(16, $game->activeDieArrayArray[0][4]->dice[1]->swingValue);
        $this->assertEquals(16, $game->activeDieArrayArray[0][4]->dice[0]->max);
        $this->assertEquals(16, $game->activeDieArrayArray[0][4]->dice[1]->max);
        $this->assertEquals(2,  $game->activeDieArrayArray[1][4]->dice[0]->swingValue);
        $this->assertEquals(2,  $game->activeDieArrayArray[1][4]->dice[1]->swingValue);
        $this->assertEquals(2,  $game->activeDieArrayArray[1][4]->dice[0]->max);
        $this->assertEquals(2,  $game->activeDieArrayArray[1][4]->dice[1]->max);

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


        // round 1, turn 1
        // player 1: [5 10 10 15 32] showing [3 1 8 15 7], captured []
        // player 2: [5 10 10 15 4] showing [2 3 8 4 2], captured []
        // player 2 takes player 1's d10 showing 1 with his/her d(2,2) twin mood swing showing 2
        // check that the player with initiative is set as the attacking player
        $this->assertEquals($game->activePlayerIdx, $game->playerWithInitiativeIdx);

        // artificially set player 2 as winning initiative
        $game->playerWithInitiativeIdx = 1;
        $game->activePlayerIdx = 1;
        $game->waitingOnActionArray = array(FALSE, TRUE);
        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 3;
        $dieArrayArray[0][1]->value = 1;
        $dieArrayArray[0][2]->value = 8;
        $dieArrayArray[0][3]->value = 15;
        $dieArrayArray[0][4]->value = 7;
        $dieArrayArray[1][0]->value = 2;
        $dieArrayArray[1][1]->value = 3;
        $dieArrayArray[1][2]->value = 8;
        $dieArrayArray[1][3]->value = 4;
        $dieArrayArray[1][4]->value = 2;

        $this->assertEquals(3, $game->activeDieArrayArray[0][0]->value);

        // perform attack
        $this->assertNULL($game->attack);
        $game->attack = array(1,        // attackerPlayerIdx
                              0,        // defenderPlayerIdx
                              array(4), // attackerAttackDieIdxArray
                              array(1), // defenderAttackDieIdxArray
                              'Shadow'); // attackType

        $preProceedSwingSize1 = $game->activeDieArrayArray[1][4]->dice[0]->swingValue;
        $preProceedSwingSize2 = $game->activeDieArrayArray[1][4]->dice[1]->swingValue;
        $this->assertEquals(2, $preProceedSwingSize1);
        $this->assertEquals(2, $preProceedSwingSize2);
        $this->assertTrue($game->activeDieArrayArray[1][4]->has_skill('Mood'));
        $game->proceed_to_next_user_action();

        $preSaveMoodMax = $game->activeDieArrayArray[1][4]->max;
        self::save_game($game);
        $game = self::load_game($game->gameId);
        $postSaveMoodMax = $game->activeDieArrayArray[1][4]->max;
        $postSaveSwingSize1 = $game->activeDieArrayArray[1][4]->dice[0]->swingValue;
        $postSaveSwingSize2 = $game->activeDieArrayArray[1][4]->dice[1]->swingValue;
        $this->assertEquals($preSaveMoodMax, $postSaveMoodMax);
        $this->assertEquals($preProceedSwingSize1, $postSaveSwingSize1);
        $this->assertEquals($postSaveSwingSize1, $postSaveSwingSize2);
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::update_last_action_time
     */
    public function test_update_last_action_time() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Avis', 'Hammer'), 4);
        $gameId = $retval['gameId'];

        $game = self::load_game($gameId);
        $this->assertEquals(array(0, 0), $game->lastActionTimeArray);

        $this->object->update_last_action_time(self::$userId1WithoutAutopass);
        $game = self::load_game($gameId);
        $this->assertEquals(array(0, 0), $game->lastActionTimeArray);

        $this->object->update_last_action_time(self::$userId1WithoutAutopass, $gameId);
        $game = self::load_game($gameId);
        $this->assertNotEquals(array(0, 0), $game->lastActionTimeArray);
        $this->assertGreaterThan(0, $game->lastActionTimeArray[0]);
        $this->assertEquals(0, $game->lastActionTimeArray[1]);
    }

    /**
     * @depends test_create_user
     *
     * @covers BMInterface::update_last_access_time
     */
    public function test_update_last_access_time() {
        $retval =  $this->object->get_player_info(self::$userId1WithoutAutopass);
        $playerInfoArray = $retval['user_prefs'];
        $preTime = $playerInfoArray['last_access_time'];

        $this->object->update_last_access_time(self::$userId1WithoutAutopass);

        $retval =  $this->object->get_player_info(self::$userId1WithoutAutopass);
        $playerInfoArray = $retval['user_prefs'];
        $postTime = $playerInfoArray['last_access_time'];

        $this->assertGreaterThan($preTime, $postTime);
    }

    /**
     * @depends test_create_user
     *
     * @coversNothing
     */
    public function test_option_reset_bug() {
        $retval = $this->object->create_game(array(self::$userId1WithoutAutopass,
                                                   self::$userId2WithoutAutopass),
                                                   array('Frasquito', 'Wiseman'), 4);
        $gameId = $retval['gameId'];
        $game = self::load_game($gameId);

        // check buttons
        $this->assertEquals('Frasquito', $game->buttonArray[0]->name);
        $this->assertEquals('(4) (6) (8) (12) (2/20)', $game->buttonArray[0]->recipe);
        $this->assertEquals('Wiseman', $game->buttonArray[1]->name);
        $this->assertEquals('(20) (20) (20) (20)', $game->buttonArray[1]->recipe);

        // specify option die
        $this->assertEquals(array(TRUE, FALSE), $game->waitingOnActionArray);
        $this->assertEquals(BMGameState::SPECIFY_DICE, $game->gameState);
        $game->optValueArrayArray = array(array(4 => 2), array());

        self::save_game($game);
        $game = self::load_game($game->gameId);

        $this->assertEquals(BMGameState::START_TURN, $game->gameState);

        // artificially set player 2 as winning initiative
        $game->playerWithInitiativeIdx = 1;
        $game->activePlayerIdx = 1;
        $game->waitingOnActionArray = array(FALSE, TRUE);
        // artificially set die values
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 4;
        $dieArrayArray[0][1]->value = 6;
        $dieArrayArray[0][2]->value = 8;
        $dieArrayArray[0][3]->value = 12;
        $dieArrayArray[0][4]->value = 2;
        $dieArrayArray[1][0]->value = 1;
        $dieArrayArray[1][1]->value = 1;
        $dieArrayArray[1][2]->value = 1;
        $dieArrayArray[1][3]->value = 1;

        // capture the option die
        $game->attack = array(1, 0, array(0, 1), array(4), 'Skill');
        self::save_game($game);
        $game = self::load_game($game->gameId);

        // artificially set die value of rolled die
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[1][0]->value = 1;
        $dieArrayArray[1][1]->value = 1;

        // now have player 1 win, having lost all its option dice
        // 4 6 8 12 vs 1 1 1 1
        $this->assertCount(4, $game->activeDieArrayArray[0]);
        $this->assertCount(4, $game->activeDieArrayArray[1]);
        $game->attack = array(0, 1, array(0), array(0), 'Power');
        self::save_game($game);
        $game = self::load_game($game->gameId);

        // artificially set die value of rolled die
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 4;

        // 4 6 8 12 vs 1 1 1
        $this->assertCount(4, $game->activeDieArrayArray[0]);
        $this->assertCount(3, $game->activeDieArrayArray[1]);
        $game->attack = array(1, 0, array(), array(), 'Pass');
        self::save_game($game);
        $game = self::load_game($game->gameId);

        $game->attack = array(0, 1, array(0), array(0), 'Power');
        self::save_game($game);
        $game = self::load_game($game->gameId);

        // artificially set die value of rolled die
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 4;

        // 4 6 8 12 vs 1 1
        $this->assertCount(4, $game->activeDieArrayArray[0]);
        $this->assertCount(2, $game->activeDieArrayArray[1]);
        $game->attack = array(1, 0, array(), array(), 'Pass');
        self::save_game($game);
        $game = self::load_game($game->gameId);

        $game->attack = array(0, 1, array(0), array(0), 'Power');
        self::save_game($game);
        $game = self::load_game($game->gameId);

        // artificially set die value of rolled die
        $dieArrayArray = $game->activeDieArrayArray;
        $dieArrayArray[0][0]->value = 4;

        // 4 6 8 12 vs 1
        $this->assertCount(4, $game->activeDieArrayArray[0]);
        $this->assertCount(1, $game->activeDieArrayArray[1]);
        $game->attack = array(1, 0, array(), array(), 'Pass');
        self::save_game($game);
        $game = self::load_game($game->gameId);

        $game->attack = array(0, 1, array(0), array(0), 'Power');
        self::save_game($game);
        $game = self::load_game($game->gameId);

        // we should now be at the point where the bug triggers, at end of round
        $this->assertEquals(2, $game->roundNumber);
        $this->assertEquals(array(array('W' => 1, 'L' => 0, 'D' => 0),
                                  array('W' => 0, 'L' => 1, 'D' => 0)),
                            $game->gameScoreArrayArray);
        $this->assertEquals(BMGameState::START_TURN, $game->gameState);
    }


    ////////////////////////////////////////////////////////////
    // Forum-related methods

    /**
     * @covers BMInterface::load_forum_overview
     * @covers BMInterface::load_forum_board
     * @covers BMInterface::load_forum_thread
     * @covers BMInterface::create_forum_thread
     * @covers BMInterface::create_forum_post
     * @covers BMInterface::edit_forum_post
     */
    public function test_create_load_forum_posts() {
        $overview = $this->object->load_forum_overview(self::$userId1WithoutAutopass);
        $boardId = $overview['boards'][0]['boardId'];
        $originalThreadsOnBoard = $overview['boards'][0]['numberOfThreads'];

        $title = uniqid();
        $body1 = uniqid();
        $this->object->create_forum_thread(self::$userId1WithoutAutopass,
            $boardId, $title, $body1);

        $overview = $this->object->load_forum_overview(self::$userId1WithoutAutopass);
        $this->assertEquals(
            $originalThreadsOnBoard + 1,
            $overview['boards'][0]['numberOfThreads'],
            'Adding a new thread should increase the number of threads on the board by one.'
        );

        $board = $this->object->load_forum_board(self::$userId1WithoutAutopass,
            $boardId);
        $this->assertEquals($title, $board['threads'][0]['threadTitle'],
            'Newly-added thread should appear first on the board.');
        $threadId = $board['threads'][0]['threadId'];
        $originalPostsInThread = $board['threads'][0]['numberOfPosts'];

        $body2 = uniqid();
        $this->object->create_forum_post(self::$userId1WithoutAutopass,
            $threadId, $body2);

        $board = $this->object->load_forum_board(self::$userId1WithoutAutopass,
            $boardId);
        $this->assertEquals($title, $board['threads'][0]['threadTitle'],
            'Newly-updated thread should appear first on the board.');
        $this->assertEquals(
            $originalPostsInThread + 1,
            $board['threads'][0]['numberOfPosts'],
            'Adding a new post should increase the number of posts in the thread by one.'
        );

        $thread = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $threadId, 2);
        $this->assertEquals(2, $thread['currentPostId'],
            'Requested post ID should be returned.');
        $this->assertEquals($title, $thread['threadTitle'],
            'Thread should have the correct title.');
        $this->assertEquals($body1, $thread['posts'][0]['body'],
            'First post should have the correct body.');
        $this->assertEquals($body2, $thread['posts'][1]['body'],
            'Followup post should have the correct body.');

        $firstPostId = $thread['posts'][0]['postId'];
        $body3 = uniqid();
        $this->object->edit_forum_post(self::$userId1WithoutAutopass,
            $firstPostId, $body3);

        $thread = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $threadId, 2);
        $this->assertNotEquals($body1, $thread['posts'][0]['body'],
            'First post should not have the old body.');
        $this->assertEquals($body3, $thread['posts'][0]['body'],
            'First post should have the new body.');
    }

    /**
     * @covers BMInterface::mark_forum_read
     * @covers BMInterface::mark_forum_board_read
     * @covers BMInterface::mark_forum_thread_read
     * @covers BMInterface::get_next_new_post
     */
    public function test_mark_forum_posts_read() {
        // First, the first player views the forum. Then the second player makes
        // several new threads: two on one board and one on another.
        $overview = $this->object->load_forum_overview(self::$userId1WithoutAutopass);
        $boardId1 = $overview['boards'][0]['boardId'];
        $boardId2 = $overview['boards'][1]['boardId'];

        $thread1 = $this->object->create_forum_thread(self::$userId2WithoutAutopass,
            $boardId1, 'Test Title 1', 'Test Body 1');
        // Separate the posts slightly, so that the first one is measurably first
        // (This is just because the DB timestamp granularity is only one second.)
        sleep(1);
        $thread2 = $this->object->create_forum_thread(self::$userId2WithoutAutopass,
            $boardId1, 'Test Title 2', 'Test Body 2');
        $thread3 = $this->object->create_forum_thread(self::$userId2WithoutAutopass,
            $boardId2, 'Test Title 3', 'Test Body 3');

        // Wait a moment, to ensure that the server's timestamp has a chance to
        // tick over.
        sleep(1);

        // Then the first player marks all boards as read, using the timestamp
        // from before the posts were made. Verify that, for the first player,
        // all three threads still start with a new post.
        $this->object->mark_forum_read(self::$userId1WithoutAutopass,
            $overview['timestamp']);

        $thread1 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread1['threadId'], NULL);
        $this->assertTrue($thread1['posts'][0]['isNew']);
        $thread2 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread2['threadId'], NULL);
        $this->assertTrue($thread2['posts'][0]['isNew']);
        $thread3 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread3['threadId'], NULL);
        $this->assertTrue($thread3['posts'][0]['isNew']);

        // Also, verify that none of these posts are new to the second player,
        // as they're the one who created them.
        $thread1 = $this->object->load_forum_thread(self::$userId2WithoutAutopass,
            $thread1['threadId'], NULL);
        $this->assertFalse($thread1['posts'][0]['isNew']);
        $thread2 = $this->object->load_forum_thread(self::$userId2WithoutAutopass,
            $thread2['threadId'], NULL);
        $this->assertFalse($thread2['posts'][0]['isNew']);
        $thread3 = $this->object->load_forum_thread(self::$userId2WithoutAutopass,
            $thread3['threadId'], NULL);
        $this->assertFalse($thread3['posts'][0]['isNew']);

        // And verify that the first new post is indeed recognized as the
        // first new post
        $nextNewPost = $this->object->get_next_new_post(self::$userId1WithoutAutopass);
        $this->assertEquals($thread1['threadId'], $nextNewPost['nextNewPostThreadId']);
        $this->assertEquals($thread1['posts'][0]['postId'], $nextNewPost['nextNewPostId']);

        // The first player marks the first thread read. Verify that its first
        // post is no longer new, but that the first posts of the other two
        // threads are unaffected.
        $this->object->mark_forum_thread_read(self::$userId1WithoutAutopass,
            $thread1['threadId'], $boardId1, $thread1['timestamp']);
        $thread1 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread1['threadId'], NULL);
        $this->assertFalse($thread1['posts'][0]['isNew']);
        $thread2 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread2['threadId'], NULL);
        $this->assertTrue($thread2['posts'][0]['isNew']);
        $thread3 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread3['threadId'], NULL);
        $this->assertTrue($thread3['posts'][0]['isNew']);

        // The first player marks the first board read. Verify that the first
        // posts on both threads on that board are no longer new, but that
        // the first post of the thread on the other board is unaffected.
        $this->object->mark_forum_board_read(self::$userId1WithoutAutopass,
            $boardId1, $thread1['timestamp']);
        $thread1 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread1['threadId'], NULL);
        $this->assertFalse($thread1['posts'][0]['isNew']);
        $thread2 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread2['threadId'], NULL);
        $this->assertFalse($thread2['posts'][0]['isNew']);
        $thread3 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread3['threadId'], NULL);
        $this->assertTrue($thread3['posts'][0]['isNew']);

        // The first player marks all boards read. Verify that the first posts
        // of all three threads are no longer new.
        $this->object->mark_forum_read(self::$userId1WithoutAutopass,
            $thread1['timestamp']);
        $thread1 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread1['threadId'], NULL);
        $this->assertFalse($thread1['posts'][0]['isNew']);
        $thread2 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread2['threadId'], NULL);
        $this->assertFalse($thread2['posts'][0]['isNew']);
        $thread3 = $this->object->load_forum_thread(self::$userId1WithoutAutopass,
            $thread3['threadId'], NULL);
        $this->assertFalse($thread3['posts'][0]['isNew']);
    }

    // End of Forum-related methods
    ////////////////////////////////////////////////////////////

}
