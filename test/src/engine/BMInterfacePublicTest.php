<?php

/**
 * BMInterfacePublicTest: external-only tests of the public BM interface
 *
 * The purpose of the tests in this class is to look for regressions
 * in the public API interface of buttonmen, by e.g. playing games
 * using only public methods.  Guidelines for tests in this class:
 * * Don't access any protected methods or internal state of
 *   BMInterface or BMGame --- if you want to test that, the main
 *   BMInterfaceTest class is a better place to do so.
 * * Don't allow randomization --- set $BM_RAND_VALS to contain the
 *   set of "random" values which should be consumed in the process
 *   of each API call, so you know exactly what behavior to expect
 * * Use a new BMInterface object for every distinct call, even
 *   within a test --- this mirrors the behavior a player will see
 *   over the course of e.g. playing a game
 * * Test the API response thoroughly, both the object which is
 *   returned and the value of $this->object->message which is set.
 *   The goal is to catch subtle (and not-subtle) regressions, so
 *   don't be shy.
 */

class BMInterfacePublicTest extends PHPUnit_Framework_TestCase {

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
        $this->object = NULL;

	// Tests in this file should override randomization, so
	// force overrides and reset the queue at the beginning of each test
        global $BM_RAND_VALS, $BM_RAND_REQUIRE_OVERRIDE;
        $BM_RAND_VALS = array();
        $BM_RAND_REQUIRE_OVERRIDE = TRUE;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

	// By default, tests use normal randomization, so always
	// reset overrides and empty the queue between tests
        global $BM_RAND_VALS, $BM_RAND_REQUIRE_OVERRIDE;
        $BM_RAND_VALS = array();
        $BM_RAND_REQUIRE_OVERRIDE = FALSE;
    }

    // Utility function to suppress non-zero timestamps in a game data option.
    // This function shouldn't do any assertions itself; that's the caller's job.
    protected function squash_game_data_timestamps($gameData) {
        $modData = $gameData;
        if (is_array($modData)) {
            if (array_key_exists('timestamp', $modData) && is_int($modData['timestamp']) && $modData['timestamp'] > 0) {
                $modData['timestamp'] = 'TIMESTAMP';
            }
            if (count($modData['gameActionLog']) > 0) {
                foreach ($modData['gameActionLog'] as $idx => $value) {
                    if (array_key_exists('timestamp', $value) && is_int($value['timestamp']) && $value['timestamp'] > 0) {
                        $modData['gameActionLog'][$idx]['timestamp'] = 'TIMESTAMP';
                    }
                }
            }
            if (count($modData['gameChatLog']) > 0) {
                foreach ($modData['gameChatLog'] as $idx => $value) {
                    if (array_key_exists('timestamp', $value) && is_int($value['timestamp']) && $value['timestamp'] > 0) {
                        $modData['gameChatLog'][$idx]['timestamp'] = 'TIMESTAMP';
                    }
                }
            }
        }
        return $modData;
    }

    // Utility function to construct a valid array of participating
    // dice, given load_api_game_data() output and the set of dice
    // which should be selected for the attack.  Each attacker
    // should be specified as array(playerIdx, dieIdx)
    protected function generate_valid_attack_array($gameData, $participatingDice) {
        $attack = array();
        foreach ($gameData['playerDataArray'] as $playerIdx => $playerData) {
            if (count($playerData['activeDieArray']) > 0) {
                foreach ($playerData['activeDieArray'] as $dieIdx => $dieInfo) {
                    $attack['playerIdx_' . $playerIdx . '_dieIdx_' . $dieIdx] = FALSE;
                }
            }
        }
        if (count($participatingDice) > 0) {
            foreach ($participatingDice as $participatingDie) {
                $playerIdx = $participatingDie[0];
                $dieIdx = $participatingDie[1];
                $attack['playerIdx_' . $playerIdx . '_dieIdx_' . $dieIdx] = TRUE;
            }
        }
        return $attack;
    }

    /**
     * @covers BMInterfaceNewuser::create_user
     */
    public function test_create_user() {
        $created_real = False;
        $maxtries = 999;
        $trynum = 1;

        // Tests may be run multiple times.  Find a user of the
        // form ifacepubNNN which hasn't been created yet and
        // create it in the test DB.  The dummy interface will claim
        // success for any username of this form.
        while (!($created_real)) {
            $this->assertTrue($trynum < $maxtries,
                "Internal test error: too many ifacepubNNN users in the test database. " .
                "Clean these out by hand.");
            $username = 'ifacepub' . sprintf('%03d', $trynum);
            $email = $username . '@example.com';
            $this->object = new BMInterfaceNewuser(TRUE);
            $createResult = $this->object->create_user($username, 't', $email);
            if (isset($createResult)) {
                $created_real = True;
            }
            $trynum++;
        }

        $this->assertTrue($created_real,
            "Creation of $username user should be reported as success");
        self::$userId1WithoutAutopass = (int)$createResult['playerId'];
        // FIXME: test return value more

        $username = 'ifacepub' . sprintf('%03d', $trynum);
        $email = $username . '@example.com';
        $this->object = new BMInterfaceNewuser(TRUE);
        $createResult = $this->object->create_user($username, 't', $email);
        self::$userId2WithoutAutopass = (int)$createResult['playerId'];

        $trynum++;
        $username = 'ifacepub' . sprintf('%03d', $trynum);
        $email = $username . '@example.com';
        $this->object = new BMInterfaceNewuser(TRUE);
        $createResult = $this->object->create_user($username, 't', $email);

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

        $this->object = new BMInterface(TRUE);
        $this->object->set_player_info($createResult['playerId'],
                                       $infoArray,
                                       $addlInfo);
        self::$userId3WithAutopass = (int)$createResult['playerId'];
        // FIXME: test result

        $trynum++;
        $username = 'ifacepub' . sprintf('%03d', $trynum);
        $email = $username . '@example.com';
        $this->object = new BMInterfaceNewuser(TRUE);
        $createResult = $this->object->create_user($username, 't', $email);
        $this->object = new BMInterface(TRUE);
        $this->object->set_player_info($createResult['playerId'],
                                       $infoArray,
                                       $addlInfo);
        self::$userId4WithAutopass = (int)$createResult['playerId'];
    }

    /**
     * @depends test_create_user
     *
     * @coversNothing
     *
     * This is the same game setup as in BMInterfaceTest::test_option_reset_bug()
     */
    public function test_interface_game_001() {
        global $BM_RAND_VALS, $BM_RAND_REQUIRE_OVERRIDE;
        $BM_RAND_REQUIRE_OVERRIDE = TRUE;

        // arguments that won't change over the course of the test
        $playerId1 = self::$userId1WithoutAutopass;
        $playerId2 = self::$userId2WithoutAutopass;
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->get_player_info($playerId1);
        $username1 = $retval['user_prefs']['name_ingame'];
        $retval = $this->object->get_player_info($playerId2);
        $username2 = $retval['user_prefs']['name_ingame'];
        $logEntryLimit = 10;

        // Non-option dice are initially rolled, namely:
        // (4) (6) (8) (12)   (20) (20) (20) (20)
        $this->object = new BMInterface(TRUE);
        $BM_RAND_VALS = array(4, 6, 8, 12, 1, 1, 1, 1);
        $retval = $this->object->create_game(array($playerId1, $playerId2),
                                             array('Frasquito', 'Wiseman'), 4);
        $this->assertEquals(0, count($BM_RAND_VALS));
        $gameId = $retval['gameId'];
        $this->assertEquals(array('gameId' => $gameId), $retval);
        $this->assertEquals('Game ' . $gameId . ' created successfully.', $this->object->message);

        // Initial expected game data object
        $expData = array(
            'gameId' => $gameId,
            'gameState' => 'SPECIFY_DICE',
            'activePlayerIdx' => NULL,
            'playerWithInitiativeIdx' => NULL,
            'roundNumber' => 1,
            'maxWins' => 4,
            'description' => '',
            'previousGameId' => NULL,
            'currentPlayerIdx' => 0,
            'timestamp' => 'TIMESTAMP',
            'validAttackTypeArray' => array(),
            'gameSkillsInfo' => array(),
            'playerDataArray' => array(
                array(
                    'playerId' => $playerId1,
                    'button' => array('name' => 'Frasquito', 'recipe' => '(4) (6) (8) (12) (2/20)', 'artFilename' => 'BMdefaultRound.png'),
                    'activeDieArray' => array(
                        array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
                        array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
                        array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
                        array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
                        array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(2/20)', 'description' => 'Option Die (with 2 or 20 sides)'),
                    ),
                    'capturedDieArray' => array(),
                    'swingRequestArray' => array(),
                    'optRequestArray' => array('4' => array(2, 20)),
                    'prevSwingValueArray' => array(),
                    'prevOptValueArray' => array(),
                    'waitingOnAction' => TRUE,
                    'roundScore' => NULL,
                    'sideScore' => NULL,
                    'gameScoreArray' => array('W' => 0, 'L' => 0, 'D' => 0),
                    'lastActionTime' => 0,
                    'hasDismissedGame' => FALSE,
                    'canStillWin' => NULL,
                    'playerName' => $username1,
                    'playerColor' => '#dd99dd',
                ),
                array(
                    'playerId' => $playerId2,
                    'button' => array('name' => 'Wiseman', 'recipe' => '(20) (20) (20) (20)', 'artFilename' => 'wiseman.png'),
                    'activeDieArray' => array(
                        array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
                        array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
                        array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
                        array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
                    ),
                    'capturedDieArray' => array(),
                    'swingRequestArray' => array(),
                    'optRequestArray' => array(),
                    'prevSwingValueArray' => array(),
                    'prevOptValueArray' => array(),
                    'waitingOnAction' => FALSE,
                    'roundScore' => NULL,
                    'sideScore' => NULL,
                    'gameScoreArray' => array('W' => 0, 'L' => 0, 'D' => 0),
                    'lastActionTime' => 0,
                    'hasDismissedGame' => FALSE,
                    'canStillWin' => NULL,
                    'playerName' => $username2,
                    'playerColor' => '#ddffdd',
                ),
            ),
            'gameActionLog' => array(),
            'gameChatLog' => array(),
            'gameChatEditable' => FALSE,
        );

        // now load the game and check its state
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->load_api_game_data($playerId1, $gameId, $logEntryLimit);
// FIXME: uncomment this when #1225 is fixed
//        $this->assertEquals('Loaded data for game ' . $gameId . '.', $this->object->message);
        $cleanedRetval = $this->squash_game_data_timestamps($retval);
        $this->assertEquals($expData, $cleanedRetval);


        //////////////////// 
        // Move 01 - specify option dice

        // this should cause all 9 dice to be rerolled
        $this->object = new BMInterface(TRUE);
        $BM_RAND_VALS = array(4, 6, 8, 12, 2, 1, 1, 1, 1);
        $retval = $this->object->submit_die_values($playerId1, $gameId, 1, array(), array(4 => 2));
        $this->assertEquals(0, count($BM_RAND_VALS));
        $this->assertEquals('Successfully set die sizes', $this->object->message);
        $this->assertEquals(TRUE, $retval);

        // expected changes to game state
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 1;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Skill');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 16;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][0]['sideScore'] = -16.0;
        $expData['playerDataArray'][1]['sideScore'] = 16.0;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'Option Die (with 2 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 1;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $username1 . ' set option dice: (2/20=2)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => $username2 . ' won initiative for round 1. Initial die values: ' . $username1 . ' rolled [(4):4, (6):6, (8):8, (12):12, (2/20=2):2], ' . $username2 . ' rolled [(20):1, (20):1, (20):1, (20):1].'));

        // load the game and check its state
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->load_api_game_data($playerId1, $gameId, $logEntryLimit);
        $cleanedRetval = $this->squash_game_data_timestamps($retval);
        $this->assertEquals($expData, $cleanedRetval);
// FIXME: uncomment once #1225 is fixed
//        $this->assertEquals('Loaded data for game ' . $gameId . '.', $this->object->message);


        //////////////////// 
        // Move 02 - player 2 captures player 1's option die

        // capture the option die - two attacking dice need to reroll
        $this->object = new BMInterface(TRUE);
        $attack = $this->generate_valid_attack_array($retval, array(array(1, 0), array(1, 1), array(0, 4)));
        $BM_RAND_VALS = array(1, 1);
        $retval = $this->object->submit_turn(
            $playerId2, $gameId, 1, $retval['timestamp'], $attack, 'Skill', 1, 0, '');
        $this->assertEquals(0, count($BM_RAND_VALS));
        $this->assertEquals(
            $username2 . ' performed Skill attack using [(20):1,(20):1] against [(2/20=2):2]; Defender (2/20=2) was captured; Attacker (20) rerolled 1 => 1; Attacker (20) rerolled 1 => 1. ',
            $this->object->message);
        $this->assertEquals(TRUE, $retval);

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 15;
        $expData['playerDataArray'][1]['roundScore'] = 42;
        $expData['playerDataArray'][0]['sideScore'] = -18.0;
        $expData['playerDataArray'][1]['sideScore'] = 18.0;
        $expData['playerDataArray'][0]['optRequestArray'] = array();
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 4, 1);
        $expData['playerDataArray'][1]['capturedDieArray'] = array(
            array('value' => 2, 'sides' => 2, 'properties' => array('WasJustCaptured'), 'recipe' => '(2/20)'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => $username2 . ' performed Skill attack using [(20):1,(20):1] against [(2/20=2):2]; Defender (2/20=2) was captured; Attacker (20) rerolled 1 => 1; Attacker (20) rerolled 1 => 1'));
        

        // now load the game and check its state
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->load_api_game_data($playerId1, $gameId, $logEntryLimit);
        $cleanedRetval = $this->squash_game_data_timestamps($retval);
        $this->assertEquals($expData, $cleanedRetval);
// FIXME: uncomment once #1225 is fixed
//        $this->assertEquals('Loaded data for game ' . $gameId . '.', $this->object->message);


        //////////////////// 
        // Move 03 - player 1 captures player 2's first 20-sider

        // 4 6 8 12 vs 1 1 1 1
        $this->object = new BMInterface(TRUE);
        $attack = $this->generate_valid_attack_array($retval, array(array(0, 0), array(1, 0)));
        $BM_RAND_VALS = array(4);
        $retval = $this->object->submit_turn(
            $playerId1, $gameId, 1, $retval['timestamp'], $attack, 'Power', 0, 1, '');
        $this->assertEquals(0, count($BM_RAND_VALS));
        $this->assertEquals(
            $username1 . ' performed Power attack using [(4):4] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 4 => 4. ',
            $this->object->message);
        $this->assertEquals(TRUE, $retval);

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 35;
        $expData['playerDataArray'][1]['roundScore'] = 32;
        $expData['playerDataArray'][0]['sideScore'] = 2.0;
        $expData['playerDataArray'][1]['sideScore'] = -2.0;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][]= 
            array('value' => 1, 'sides' => 20, 'properties' => array('WasJustCaptured'), 'recipe' => '(20)');
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $username1 . ' performed Power attack using [(4):4] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 4 => 4'));

        // now load the game and check its state
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->load_api_game_data($playerId1, $gameId, $logEntryLimit);
        $cleanedRetval = $this->squash_game_data_timestamps($retval);
        $this->assertEquals($expData, $cleanedRetval);
// FIXME: uncomment once #1225 is fixed
//        $this->assertEquals('Loaded data for game ' . $gameId . '.', $this->object->message);


        //////////////////// 
        // Move 04 - player 2 passes

        // 4 6 8 12 vs 1 1 1
        $this->object = new BMInterface(TRUE);
        $attack = $this->generate_valid_attack_array($retval, array());
        $retval = $this->object->submit_turn(
            $playerId2, $gameId, 1, $retval['timestamp'], $attack, 'Pass', 1, 0, '');
        $this->assertEquals(0, count($BM_RAND_VALS));
        $this->assertEquals(
            $username2 . ' passed. ',
            $this->object->message);
        $this->assertEquals(TRUE, $retval);

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 35;
        $expData['playerDataArray'][1]['roundScore'] = 32;
        $expData['playerDataArray'][0]['sideScore'] = 2.0;
        $expData['playerDataArray'][1]['sideScore'] = -2.0;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => $username2 . ' passed'));

        // now load the game and check its state
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->load_api_game_data($playerId1, $gameId, $logEntryLimit);
        $cleanedRetval = $this->squash_game_data_timestamps($retval);
        $this->assertEquals($expData, $cleanedRetval);
// FIXME: uncomment once #1225 is fixed
//        $this->assertEquals('Loaded data for game ' . $gameId . '.', $this->object->message);


        //////////////////// 
        // Move 05 - player 1 captures player 2's first remaining (20)

        // 4 6 8 12 vs 1 1 1
        $this->object = new BMInterface(TRUE);
        $attack = $this->generate_valid_attack_array($retval, array(array(0, 0), array(1, 0)));
        $BM_RAND_VALS = array(3);
        $retval = $this->object->submit_turn(
            $playerId1, $gameId, 1, $retval['timestamp'], $attack, 'Power', 0, 1, '');
        $this->assertEquals(0, count($BM_RAND_VALS));
        $actiondesc = $username1 . ' performed Power attack using [(4):4] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 4 => 3';
        $this->assertEquals($actiondesc . '. ', $this->object->message);
        $this->assertEquals(TRUE, $retval);

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 55;
        $expData['playerDataArray'][1]['roundScore'] = 22;
        $expData['playerDataArray'][0]['sideScore'] = 22.0;
        $expData['playerDataArray'][1]['sideScore'] = -22.0;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][]= 
            array('value' => 1, 'sides' => 20, 'properties' => array('WasJustCaptured'), 'recipe' => '(20)');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $actiondesc));

        // now load the game and check its state
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->load_api_game_data($playerId1, $gameId, $logEntryLimit);
        $cleanedRetval = $this->squash_game_data_timestamps($retval);
        $this->assertEquals($expData, $cleanedRetval);
// FIXME: uncomment once #1225 is fixed
//        $this->assertEquals('Loaded data for game ' . $gameId . '.', $this->object->message);


        //////////////////// 
        // Move 06 - player 2 passes

        // 4 6 8 12 vs 1 1
        $this->object = new BMInterface(TRUE);
        $attack = $this->generate_valid_attack_array($retval, array());
        $retval = $this->object->submit_turn(
            $playerId2, $gameId, 1, $retval['timestamp'], $attack, 'Pass', 1, 0, '');
        $this->assertEquals(0, count($BM_RAND_VALS));
        $this->assertEquals($username2 . ' passed. ', $this->object->message);
        $this->assertEquals(TRUE, $retval);

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => $username2 . ' passed'));

        // now load the game and check its state
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->load_api_game_data($playerId1, $gameId, $logEntryLimit);
        $cleanedRetval = $this->squash_game_data_timestamps($retval);
        $this->assertEquals($expData, $cleanedRetval);
// FIXME: uncomment once #1225 is fixed
//        $this->assertEquals('Loaded data for game ' . $gameId . '.', $this->object->message);


        //////////////////// 
        // Move 07 - player 1 captures player 2's first remaining (20)

        // 4 6 8 12 vs 1 1
        $this->object = new BMInterface(TRUE);
        $attack = $this->generate_valid_attack_array($retval, array(array(0, 1), array(1, 0)));
        $BM_RAND_VALS = array(2);
        $retval = $this->object->submit_turn(
            $playerId1, $gameId, 1, $retval['timestamp'], $attack, 'Power', 0, 1, '');
        $this->assertEquals(0, count($BM_RAND_VALS));
        $actiondesc = $username1 . ' performed Power attack using [(6):6] against [(20):1]; Defender (20) was captured; Attacker (6) rerolled 6 => 2';
        $this->assertEquals($actiondesc . '. ', $this->object->message);
        $this->assertEquals(TRUE, $retval);

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 75;
        $expData['playerDataArray'][1]['roundScore'] = 12;
        $expData['playerDataArray'][0]['sideScore'] = 42.0;
        $expData['playerDataArray'][1]['sideScore'] = -42.0;
        $expData['playerDataArray'][1]['canStillWin'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][]= 
            array('value' => 1, 'sides' => 20, 'properties' => array('WasJustCaptured'), 'recipe' => '(20)');
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $actiondesc));

        // now load the game and check its state
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->load_api_game_data($playerId1, $gameId, $logEntryLimit);
        $cleanedRetval = $this->squash_game_data_timestamps($retval);
        $this->assertEquals($expData, $cleanedRetval);
// FIXME: uncomment once #1225 is fixed
//        $this->assertEquals('Loaded data for game ' . $gameId . '.', $this->object->message);


        //////////////////// 
        // Move 08 - player 2 passes

        // 4 6 8 12 vs 1
        $this->object = new BMInterface(TRUE);
        $attack = $this->generate_valid_attack_array($retval, array());
        $retval = $this->object->submit_turn(
            $playerId2, $gameId, 1, $retval['timestamp'], $attack, 'Pass', 1, 0, '');
        $this->assertEquals(0, count($BM_RAND_VALS));
        $this->assertEquals($username2 . ' passed. ', $this->object->message);
        $this->assertEquals(TRUE, $retval);

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => $username2 . ' passed'));

        // now load the game and check its state
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->load_api_game_data($playerId1, $gameId, $logEntryLimit);
        $cleanedRetval = $this->squash_game_data_timestamps($retval);
        $this->assertEquals($expData, $cleanedRetval);
// FIXME: uncomment once #1225 is fixed
//        $this->assertEquals('Loaded data for game ' . $gameId . '.', $this->object->message);


        //////////////////// 
        // Move 09 - player 1 captures player 2's last remaining (20)

        // 4 6 8 12 vs 1
        $this->object = new BMInterface(TRUE);
        $attack = $this->generate_valid_attack_array($retval, array(array(0, 0), array(1, 0)));
        $BM_RAND_VALS = array(4, 1, 1, 1, 1, 2, 15, 16, 17, 18);
        $retval = $this->object->submit_turn(
            $playerId1, $gameId, 1, $retval['timestamp'], $attack, 'Power', 0, 1, '');
//        $this->assertEquals(0, count($BM_RAND_VALS));
        $this->assertEquals(
            $username1 . ' performed Power attack using [(4):3] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 3 => 4. End of round: ' . $username1 . ' won round 1 (95 vs. 2). ' . $username1 . ' won initiative for round 2. Initial die values: ' . $username1 . ' rolled [(4):1, (6):1, (8):1, (12):1, (2/20=2):2], ' . $username2 . ' rolled [(20):15, (20):16, (20):17, (20):18].. ',
            $this->object->message);
        $this->assertEquals(TRUE, $retval);

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['roundNumber'] = 2;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 16;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][0]['sideScore'] = -16.0;
        $expData['playerDataArray'][1]['sideScore'] = 16.0;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][0]['optRequestArray'] = array('4' => array(2, 20));
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 1, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => 1, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => 1, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => 1, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => 2, 'sides' => 2, 'skills' => array(), 'properties' => array(), 'recipe' => '(2/20)', 'description' => 'Option Die (with 2 sides)'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
                        array('value' => 15, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
                        array('value' => 16, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
                        array('value' => 17, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
                        array('value' => 18, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $username1 . ' performed Power attack using [(4):3] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 3 => 4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => 'End of round: ' . $username1 . ' won round 1 (95 vs. 2)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => $username1 . ' won initiative for round 2. Initial die values: ' . $username1 . ' rolled [(4):1, (6):1, (8):1, (12):1, (2/20=2):2], ' . $username2 . ' rolled [(20):15, (20):16, (20):17, (20):18].'));
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);

        // now load the game and check its state
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->load_api_game_data($playerId1, $gameId, $logEntryLimit);
        $cleanedRetval = $this->squash_game_data_timestamps($retval);
        $this->assertEquals($expData, $cleanedRetval);
// FIXME: uncomment once #1225 is fixed
//        $this->assertEquals('Loaded data for game ' . $gameId . '.', $this->object->message);
    }
}
