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
 * * Every time you receive an API result, check three things:
 *   1. $this->object->message contains the string you expect
 *   2. $this->assertEquals(0, count($BM_RAND_VALS));
 *      N.B. you only have to test this if you actually set any
 *      random values for this call; if you started the call with
 *      no random values and turn out to need some, an exception
 *      will be thrown without you having to do anything
 *   3. the return value of the API call contains what you expect
 *   Check them in that order for ease of test debugging --- that
 *   way, if you mess up, the first thing you see is the hopefully-useful
 *   API message telling you so.
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
    private static $username1;
    private static $username2;
    private static $username3;
    private static $username4;

    // duplicate skill info in one place so we don't have to retype it
    private static $skillInfo = array(
        'Poison' => array(
            'code' => 'p',
            'description' => 'These dice are worth negative points. If you keep a Poison Die of your own at the end of a round, subtract its full value from your score. If you capture a Poison Die from someone else, subtract half its value from your score.',
            'interacts' => array(),
        ),
        'Shadow' => array(
            'code' => 's',
            'description' => 'These dice are normal in all respects, except that they cannot make Power Attacks. Instead, they make inverted Power Attacks, called "Shadow Attacks." To make a Shadow Attack, Use one of your Shadow Dice to capture one of your opponent\'s dice. The number showing on the die you capture must be greater than or equal to the number showing on your die, but within its range. For example, a shadow 10-sided die showing a 2 can capture a die showing any number from 2 to 10.',
            'interacts' => array(),
        ),
    );

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
            if (array_key_exists('gameChatEditable', $modData) && is_int($modData['gameChatEditable']) && $modData['gameChatEditable'] > 0) {
                $modData['gameChatEditable'] = 'TIMESTAMP';
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

    // Utility function to initialize a data array, just because
    // there's a lot of stuff in this, and a lot of it is always
    // the same at the beginning of a game, so save some typing.
    // This does *not* initialize buttons or active dice --- you
    // need to do that
    protected function generate_init_expected_data_array(
        $gameId, $playerId1, $playerId2, $username1, $username2, $maxWins, $gameState
    ) {
        $expData = array(
            'gameId' => $gameId,
            'gameState' => $gameState,
            'activePlayerIdx' => NULL,
            'playerWithInitiativeIdx' => NULL,
            'roundNumber' => 1,
            'maxWins' => $maxWins,
            'description' => '',
            'previousGameId' => NULL,
            'currentPlayerIdx' => 0,
            'timestamp' => 'TIMESTAMP',
            'validAttackTypeArray' => array(),
            'gameSkillsInfo' => array(),
            'playerDataArray' => array(
                array(
                    'playerId' => $playerId1,
                    'capturedDieArray' => array(),
                    'swingRequestArray' => array(),
                    'optRequestArray' => array(),
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
                    'capturedDieArray' => array(),
                    'swingRequestArray' => array(),
                    'optRequestArray' => array(),
                    'prevSwingValueArray' => array(),
                    'prevOptValueArray' => array(),
                    'waitingOnAction' => TRUE,
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
        return $expData;
    }

    /**
     * verify_create_game() - helper routine which calls
     * BMInterface::create_game() using provided fake die
     * rolls, and makes standard assertions about its return value
     */
    protected function verify_create_game($postCreateDieRolls, $playerArray, $buttonArray, $maxWins, $description='', $prevGame=NULL) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postCreateDieRolls;
        $this->object = new BMInterface(TRUE);
        if (is_null($prevGame)) {
            $retval = $this->object->create_game($playerArray, $buttonArray, $maxWins, $description);
        } else {
            $retval = $this->object->create_game($playerArray, $buttonArray, $maxWins, $description, $prevGame);
        }
        $gameId = $retval['gameId'];
        $this->assertEquals('Game ' . $gameId . ' created successfully.', $this->object->message);
        $this->assertEquals(0, count($BM_RAND_VALS));
        $this->assertEquals(array('gameId' => $gameId), $retval);
        return $gameId;
    }

    /**
     * verify_submit_die_values() - helper routine which calls
     * BMInterface::submit_die_values() using provided fake die
     * rolls, and makes standard assertions about its return value
     */
    protected function verify_submit_die_values($postSubmitDieRolls, $playerId, $gameId, $roundNum, $swingArray, $optionArray) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postSubmitDieRolls;
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->submit_die_values($playerId, $gameId, $roundNum, $swingArray, $optionArray);
        $this->assertEquals('Successfully set die sizes', $this->object->message);
        $this->assertEquals(0, count($BM_RAND_VALS));
        $this->assertEquals(TRUE, $retval);
    }

    /**
     * verify_submit_turn() - helper routine which calls
     * BMInterface::submit_turn() using provided fake die
     * rolls, and makes standard assertions about its return value
     */
    protected function verify_submit_turn(
	$postSubmitDieRolls, $expMessage, $prevRetval, $participatingDice,
	$playerId, $gameId, $roundNum, $attackType, $attackerIdx, $defenderIdx, $chat
    ) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postSubmitDieRolls;
        $this->object = new BMInterface(TRUE);
        $dieSelects = $this->generate_valid_attack_array($prevRetval, $participatingDice);
        $retval = $this->object->submit_turn(
            $playerId, $gameId, $roundNum, $prevRetval['timestamp'], $dieSelects, $attackType, $attackerIdx, $defenderIdx, $chat);
        $this->assertEquals($expMessage, $this->object->message);
        $this->assertEquals(0, count($BM_RAND_VALS));
        $this->assertEquals(TRUE, $retval);
    }

    /*
     * verify_load_api_game_data() - helper routine which calls
     * BMInterface::load_api_game_data(), makes standard assertions
     * about its return value which shouldn't change, and compares
     * its return value to an expected game state object compiled
     * by the caller.
     */
    protected function verify_load_api_game_data($expData, $playerId, $gameId, $logEntryLimit) {
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->load_api_game_data($playerId, $gameId, $logEntryLimit);
// FIXME: uncomment this when #1225 is fixed
//        $this->assertEquals('Loaded data for game ' . $gameId . '.', $this->object->message);
        $cleanedRetval = $this->squash_game_data_timestamps($retval);
        $this->assertEquals($expData, $cleanedRetval);
        return $retval;
    }


    /**
     * @covers BMInterfaceNewuser
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
        self::$username1 = $username;
        $this->assertEquals(
            'User ' . $username . ' created successfully.  A verification code has been e-mailed to ' . $username . '@example.com.  Follow the link in that message to start beating people up! (Note: If you don\'t see the email shortly, be sure to check your spam folder.)',
            $this->object->message);
        $this->assertEquals(array('userName' => $username, 'playerId' => self::$userId1WithoutAutopass), $createResult);

        $username = 'ifacepub' . sprintf('%03d', $trynum);
        $email = $username . '@example.com';
        $this->object = new BMInterfaceNewuser(TRUE);
        $createResult = $this->object->create_user($username, 't', $email);
        self::$userId2WithoutAutopass = (int)$createResult['playerId'];
        self::$username2 = $username;

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
        $retval = $this->object->set_player_info(
            $createResult['playerId'], $infoArray, $addlInfo);
        self::$userId3WithAutopass = (int)$createResult['playerId'];
        self::$username3 = $username;
        $this->assertEquals('Player info updated successfully.', $this->object->message);
        $this->assertEquals(array('playerId' => self::$userId3WithAutopass), $retval);

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
        self::$username4 = $username;
    }

    /**
     * @depends test_create_user
     * @covers BMInterface
     *
     */
    public function test_get_profile_info() {

        // successful retrieval of profile info (maybe later actually test the profile info itself)
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->get_profile_info(self::$username3);
        $this->assertEquals('Player ID retrieved successfully.', $this->object->message);

        // unsuccessful retrieval of profile info
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->get_profile_info(self::$username3 + 'foobar');
        $this->assertEquals('Player name does not exist.', $this->object->message);
        $this->assertEquals(NULL, $retval);
    }

    /**
     * @depends test_create_user
     * @covers BMInterface
     *
     * This is the same game setup as in BMInterfaceTest::test_option_reset_bug()
     */
    public function test_interface_game_001() {

        // arguments that won't change over the course of the test
        $playerId1 = self::$userId1WithoutAutopass;
        $playerId2 = self::$userId2WithoutAutopass;
        $username1 = self::$username1;
        $username2 = self::$username2;
        $logEntryLimit = 10;

        // Non-option dice are initially rolled, namely:
        // (4) (6) (8) (12)   (20) (20) (20) (20)
        $gameId = $this->verify_create_game(
            array(4, 6, 8, 12, 1, 1, 1, 1),
            array($playerId1, $playerId2), array('Frasquito', 'Wiseman'), 4);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, $playerId1, $playerId2, $username1, $username2, 4, 'SPECIFY_DICE');
        $expData['playerDataArray'][0]['button'] = array('name' => 'Frasquito', 'recipe' => '(4) (6) (8) (12) (2/20)', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Wiseman', 'recipe' => '(20) (20) (20) (20)', 'artFilename' => 'wiseman.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(2/20)', 'description' => 'Option Die (with 2 or 20 sides)'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['optRequestArray'] = array('4' => array(2, 20));

        // now load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 01 - specify option dice

        // this should cause all 9 dice to be rerolled
        $this->verify_submit_die_values(
            array(4, 6, 8, 12, 2, 1, 1, 1, 1),
            $playerId1, $gameId, 1, array(), array(4 => 2));

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
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 02 - player 2 captures player 1's option die

        // capture the option die - two attacking dice need to reroll
        $this->verify_submit_turn(
            array(1, 1),
            $username2 . ' performed Skill attack using [(20):1,(20):1] against [(2/20=2):2]; Defender (2/20=2) was captured; Attacker (20) rerolled 1 => 1; Attacker (20) rerolled 1 => 1. ',
            $retval, array(array(1, 0), array(1, 1), array(0, 4)),
            $playerId2, $gameId, 1, 'Skill', 1, 0, '');

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
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 03 - player 1 captures player 2's first 20-sider

        // 4 6 8 12 vs 1 1 1 1
        $this->verify_submit_turn(
            array(4),
            $username1 . ' performed Power attack using [(4):4] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 4 => 4. ',
            $retval, array(array(0, 0), array(1, 0)),
            $playerId1, $gameId, 1, 'Power', 0, 1, '');

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
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 04 - player 2 passes

        // 4 6 8 12 vs 1 1 1
        $this->verify_submit_turn(
            array(),
            $username2 . ' passed. ',
            $retval, array(),
            $playerId2, $gameId, 1, 'Pass', 1, 0, '');

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
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 05 - player 1 captures player 2's first remaining (20)

        // 4 6 8 12 vs 1 1 1
        $this->verify_submit_turn(
            array(3),
            $username1 . ' performed Power attack using [(4):4] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 4 => 3. ',
            $retval, array(array(0, 0), array(1, 0)),
            $playerId1, $gameId, 1, 'Power', 0, 1, '');

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $username1 . ' performed Power attack using [(4):4] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 4 => 3'));

        // now load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 06 - player 2 passes

        // 4 6 8 12 vs 1 1
        $this->verify_submit_turn(
            array(),
            $username2 . ' passed. ',
            $retval, array(),
            $playerId2, $gameId, 1, 'Pass', 1, 0, '');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => $username2 . ' passed'));

        // now load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 07 - player 1 captures player 2's first remaining (20)

        // 4 6 8 12 vs 1 1
        $this->verify_submit_turn(
            array(2),
            $username1 . ' performed Power attack using [(6):6] against [(20):1]; Defender (20) was captured; Attacker (6) rerolled 6 => 2. ',
            $retval, array(array(0, 1), array(1, 0)),
            $playerId1, $gameId, 1, 'Power', 0, 1, '');

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $username1 . ' performed Power attack using [(6):6] against [(20):1]; Defender (20) was captured; Attacker (6) rerolled 6 => 2'));

        // now load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 08 - player 2 passes

        // 4 6 8 12 vs 1
        $this->verify_submit_turn(
            array(),
            $username2 . ' passed. ',
            $retval, array(),
            $playerId2, $gameId, 1, 'Pass', 1, 0, '');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => $username2 . ' passed'));

        // now load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 09 - player 1 captures player 2's last remaining (20)

        // 4 6 8 12 vs 1
        $this->verify_submit_turn(
            array(4, 1, 1, 1, 1, 2, 15, 16, 17, 18),
            $username1 . ' performed Power attack using [(4):3] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 3 => 4. End of round: ' . $username1 . ' won round 1 (95 vs. 2). ' . $username1 . ' won initiative for round 2. Initial die values: ' . $username1 . ' rolled [(4):1, (6):1, (8):1, (12):1, (2/20=2):2], ' . $username2 . ' rolled [(20):15, (20):16, (20):17, (20):18].. ',
            $retval, array(array(0, 0), array(1, 0)),
            $playerId1, $gameId, 1, 'Power', 0, 1, '');

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
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);
    }

    /**
     * @depends test_create_user
     * @covers BMInterface
     *
     * Game scenario (both players have autopass):
     * p1 (Jellybean) vs. p2 (Dirgo):
     *  1. p1 set swing values: V=6, X=10
     *  2. p2 set swing values: X=4
     *     p1 won initiative for round 1. Initial die values: p1 rolled [p(20):2, s(20):11, (V=6):3, (X=10):1], p2 rolled [(20):5, (20):8, (20):12, (X=4):4].
     *  3. p1 performed Shadow attack using [s(20):11] against [(20):12]; Defender (20) was captured; Attacker s(20) rerolled 11 => 15
     *  4. p2 performed Power attack using [(20):5] against [(V=6):3]; Defender (V=6) was captured; Attacker (20) rerolled 5 => 12
     *     p1 passed
     *  5. p2 performed Power attack using [(20):8] against [(X=10):1]; Defender (X=10) was captured; Attacker (20) rerolled 8 => 13
     *     p1 passed
     *  6. p2 performed Power attack using [(20):12] against [p(20):2]; Defender p(20) was captured; Attacker (20) rerolled 12 => 1
     *     p1 passed
     *     p2 passed
     *     End of round: p1 won round 1 (30 vs. 28)
     *  7. p2 set swing values: X=7
     *     p1 won initiative for round 2. Initial die values: p1 rolled [p(20):8, s(20):6, (V=6):1, (X=10):1], p2 rolled [(20):7, (20):2, (20):17, (X=7):2].
     */
    public function test_interface_game_002() {

        // arguments that won't change over the course of the test
        $playerId1 = self::$userId3WithAutopass;
        $playerId2 = self::$userId4WithAutopass;
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->get_player_info($playerId1);
        $username1 = $retval['user_prefs']['name_ingame'];
        $retval = $this->object->get_player_info($playerId2);
        $username2 = $retval['user_prefs']['name_ingame'];
        $logEntryLimit = 10;

        //////////////////// 
        // initial game setup

        // Non-swing dice are initially rolled, namely:
        // p(20) s(20)  (20) (20) (20)
        $gameId = $this->verify_create_game(
            array(1, 1, 1, 1, 1),
            array($playerId1, $playerId2), array('Jellybean', 'Dirgo'), 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, $playerId1, $playerId2, $username1, $username2, 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = array('Poison' => self::$skillInfo['Poison'], 'Shadow' => self::$skillInfo['Shadow']);
        $expData['playerDataArray'][0]['button'] = array('name' => 'Jellybean', 'recipe' => 'p(20) s(20) (V) (X)', 'artFilename' => 'jellybean.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Dirgo', 'recipe' => '(20) (20) (20) (X)', 'artFilename' => 'dirgo.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array('Poison'), 'properties' => array(), 'recipe' => 'p(20)', 'description' => 'Poison 20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(20)', 'description' => 'Shadow 20-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(V)', 'description' => 'V Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20), 'V' => array(6, 12));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('X' => array(4, 20));

        // now load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 01 - player 1 specifies swing dice

        // this causes all specified dice to be rerolled twice with values that are never used:
        // p(20) s(20) (V) (X)   (20) (20) (20)
        $this->verify_submit_die_values(
            array(2, 2, 2, 2, 2, 2, 2, 3, 3, 3, 3, 3, 3, 3),
            $playerId1, $gameId, 1, array('V' => 6, 'X' => 10), array());

        // expected changes to game state
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = 'V Swing Die (with 6 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = 'X Swing Die (with 10 sides)';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $username1 . ' set die sizes'));

        // load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 02 - player 2 specifies swing dice

        // this causes all specified dice to be rerolled with their real values:
        // p(20) s(20) (V) (X)   (20) (20) (20) (X)
        $this->verify_submit_die_values(
            array(2, 11, 3, 1, 5, 8, 12, 4),
            $playerId2, $gameId, 1, array('X' => 4), array());

        // expected changes to game state
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Skill', 'Shadow');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = -2;
        $expData['playerDataArray'][1]['roundScore'] = 32;
        $expData['playerDataArray'][0]['sideScore'] = -22.7;
        $expData['playerDataArray'][1]['sideScore'] = 22.7;
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = 'X Swing Die (with 4 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 4;
        $expData['gameActionLog'][0]['message'] = $username1 . ' set swing values: V=6, X=10';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => $username2 . ' set swing values: X=4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => $username1 . ' won initiative for round 1. Initial die values: ' . $username1 . ' rolled [p(20):2, s(20):11, (V=6):3, (X=10):1], ' . $username2 . ' rolled [(20):5, (20):8, (20):12, (X=4):4].'));

        // load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 03 - player 1 performs shadow attack

        // p(20) s(20) (V) (X)  vs.  (20) (20) (20) (X)
        $this->verify_submit_turn(
            array(15),
            $username1 . ' performed Shadow attack using [s(20):11] against [(20):12]; Defender (20) was captured; Attacker s(20) rerolled 11 => 15. ',
            $retval, array(array(0, 1), array(1, 2)),
            $playerId1, $gameId, 1, 'Shadow', 0, 1, '');

        // expected changes to game state
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 18;
        $expData['playerDataArray'][1]['roundScore'] = 22;
        $expData['playerDataArray'][0]['sideScore'] = -2.7;
        $expData['playerDataArray'][1]['sideScore'] = 2.7;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 15;
        $expData['playerDataArray'][0]['capturedDieArray'][]= 
            array('value' => 12, 'sides' => 20, 'properties' => array('WasJustCaptured'), 'recipe' => '(20)');
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 2, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $username1 . ' performed Shadow attack using [s(20):11] against [(20):12]; Defender (20) was captured; Attacker s(20) rerolled 11 => 15'));

        // load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 04 - player 2 performs power attack; player 1 passes

        // p(20) s(20) (V) (X)  vs.  (20) (20) (X)
        $this->verify_submit_turn(
            array(12),
            $username2 . ' performed Power attack using [(20):5] against [(V=6):3]; Defender (V=6) was captured; Attacker (20) rerolled 5 => 12. ' . $username1 . ' passed. ',
            $retval, array(array(1, 0), array(0, 2)),
            $playerId2, $gameId, 1, 'Power', 1, 0, '');

        // expected changes to game state
        $expData['playerDataArray'][0]['roundScore'] = 15;
        $expData['playerDataArray'][1]['roundScore'] = 28;
        $expData['playerDataArray'][0]['sideScore'] = -8.7;
        $expData['playerDataArray'][1]['sideScore'] = 8.7;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 12;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][]= 
            array('value' => 3, 'sides' => 6, 'properties' => array(), 'recipe' => '(V)');
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 2, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => $username2 . ' performed Power attack using [(20):5] against [(V=6):3]; Defender (V=6) was captured; Attacker (20) rerolled 5 => 12'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $username1 . ' passed'));

        // load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 05 - player 2 performs power attack; player 1 passes

        // p(20) s(20) (X)  vs.  (20) (20) (X)
        $this->verify_submit_turn(
            array(13),
            $username2 . ' performed Power attack using [(20):8] against [(X=10):1]; Defender (X=10) was captured; Attacker (20) rerolled 8 => 13. ' . $username1 . ' passed. ',
            $retval, array(array(1, 1), array(0, 2)),
            $playerId2, $gameId, 1, 'Power', 1, 0, '');

        // no new code coverage; load the data, but don't bother to test it
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->load_api_game_data($playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 06 - player 2 performs power attack; player 1 passes; player 2 passes; round ends

        // p(20) s(20)  vs.  (20) (20) (X)
        // random values needed: 1 for reroll, 7 for end of turn reroll
        $this->verify_submit_turn(
            array(1, 2, 2, 2, 2, 2, 2, 2),
            $username2 . ' performed Power attack using [(20):12] against [p(20):2]; Defender p(20) was captured; Attacker (20) rerolled 12 => 1. ' . $username1 . ' passed. ' . $username2 . ' passed. End of round: ' . $username1 . ' won round 1 (30 vs. 28). ',
            $retval, array(array(1, 0), array(0, 0)),
            $playerId2, $gameId, 1, 'Power', 1, 0, '');

        // expected changes to game state
        $expData['roundNumber'] = 2;
        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = NULL;
        $expData['playerDataArray'][1]['roundScore'] = NULL;
        $expData['playerDataArray'][0]['sideScore'] = NULL;
        $expData['playerDataArray'][1]['sideScore'] = NULL;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array('V' => 6, 'X' => 10);
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array('X' => 4);
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array('Poison'), 'properties' => array(), 'recipe' => 'p(20)', 'description' => 'Poison 20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array('Shadow'), 'properties' => array(), 'recipe' => 's(20)', 'description' => 'Shadow 20-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(V)', 'description' => 'V Swing Die (with 6 sides)'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die (with 10 sides)'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => $username2 . ' performed Power attack using [(20):8] against [(X=10):1]; Defender (X=10) was captured; Attacker (20) rerolled 8 => 13'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $username1 . ' passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => $username2 . ' performed Power attack using [(20):12] against [p(20):2]; Defender p(20) was captured; Attacker (20) rerolled 12 => 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $username1 . ' passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => $username2 . ' passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => 'End of round: ' . $username1 . ' won round 1 (30 vs. 28)'));
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);

        // load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 07 - player 2 specifies swing dice

        // this causes all specified dice to be rerolled with their real values:
        // p(20) s(20) (V) (X)   (20) (20) (20) (X)
        $this->verify_submit_die_values(
            array(8, 6, 1, 1, 7, 2, 17, 2),
            $playerId2, $gameId, 2, array('X' => 7), array());

        // expected changes to game state
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill', 'Shadow');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = -2;
        $expData['playerDataArray'][1]['roundScore'] = 33.5;
        $expData['playerDataArray'][0]['sideScore'] = -23.7;
        $expData['playerDataArray'][1]['sideScore'] = 23.7;
        $expData['playerDataArray'][0]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][1]['prevSwingValueArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = 'X Swing Die (with 7 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 7;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 17;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 2;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => $username2 . ' set swing values: X=7'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => $username1 . ' won initiative for round 2. Initial die values: ' . $username1 . ' rolled [p(20):8, s(20):6, (V=6):1, (X=10):1], ' . $username2 . ' rolled [(20):7, (20):2, (20):17, (X=7):2].'));
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);

        // load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);
    }

    /**
     * @depends test_create_user
     * @covers BMInterface
     *
     * In this scenario, a 1-round Haruspex mirror battle is played,
     * letting us test a completed game, and a number of "continue
     * previous game" and game chat scenarios.  Steps:
     * * Create haruspex mirror battle game 1.
     * *   test invalid game continuation of a game in progress
     * *   game 1: p2 passes while chatting
     * *   game 1: p1 power attacks while chatting and wins
     * *   test various invalid game continuations
     * * Create haruspex mirror battle game 2, continuing game 1.
     * *   game 2: p1 passes
     * *   game 2: p1 submits chat 
     * *   game 2: p1 updates chat 
     * *   game 2: p1 deletes chat 
     * *   game 2: p2 power attacks and wins
     * * Create haruspex mirror battle game 3, continuing game 2 (double-continuation).
     * *   game 3: p1 passes while chatting (verify chat is editable)
     */
    public function test_interface_game_003() {

        // arguments that won't change over the course of the test
        $playerId1 = self::$userId3WithAutopass;
        $playerId2 = self::$userId4WithAutopass;
        $playerId3 = self::$userId1WithoutAutopass;
        $username1 = self::$username3;
        $username2 = self::$username4;
        $logEntryLimit = 10;

        //////////////////// 
        // initial game setup

        // Both dice are initially rolled:
        // (99)  (99)
        $gameId = $this->verify_create_game(
            array(54, 42),
            array($playerId1, $playerId2), array('haruspex', 'haruspex'), 1, 'a competitive and interesting game');

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, $playerId1, $playerId2, $username1, $username2, 1, 'START_TURN');
        $expData['description'] = 'a competitive and interesting game';
        $expData['activePlayerIdx'] = 1;
        $expData['playerWithInitiativeIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 49.5;
        $expData['playerDataArray'][1]['roundScore'] = 49.5;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'artFilename' => 'haruspex.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'artFilename' => 'haruspex.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 54, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 42, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => $username2 . ' won initiative for round 1. Initial die values: ' . $username1 . ' rolled [(99):54], ' . $username2 . ' rolled [(99):42].'));

        // load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
	// Verify that a continuation of this game while it is still in progress fails
        $this->object = new BMInterface(TRUE);
        $tmpRetval = $this->object->create_game(array($playerId1, $playerId2), array('haruspex', 'haruspex'), 1, '', $gameId);
        $this->assertEquals('Game create failed because the previous game has not been completed yet.', $this->object->message);
        $this->assertEquals(NULL, $tmpRetval);


        //////////////////// 
        // Move 01 - player 2 passes

        // (99)  vs  (99)
        $this->verify_submit_turn(
            array(),
            $username2 . ' passed. ',
            $retval, array(),
            $playerId2, $gameId, 1, 'Pass', 1, 0, 'I think you\'ve got this one');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => $username2 . ' passed'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => 'I think you\'ve got this one'));

        // now load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 02 - player 1 performs power attack; game ends

        // (99)  vs  (99)
        $this->verify_submit_turn(
            array(10),
            $username1 . ' performed Power attack using [(99):54] against [(99):42]; Defender (99) was captured; Attacker (99) rerolled 54 => 10. End of round: ' . $username1 . ' won round 1 (148.5 vs. 0). ',
            $retval, array(array(0, 0), array(1, 0)),
            $playerId1, $gameId, 1, 'Power', 0, 1, 'Good game!');

        // expected changes as a result of the attack
        $expData['gameState'] = 'END_GAME';
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 0;
        $expData['playerDataArray'][1]['roundScore'] = 0;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][0]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $username1 . ' performed Power attack using [(99):54] against [(99):42]; Defender (99) was captured; Attacker (99) rerolled 54 => 10'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => 'End of round: ' . $username1 . ' won round 1 (148.5 vs. 0)'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => 'Good game!'));

        // now load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
	// Game creation failures - make sure various invalid argumentsj
	// that the public API will allow, are rejected with friendly messages

        // same player appears in the game twice
        $this->object = new BMInterface(TRUE);
        $retval = $this->object->create_game(array($playerId1, $playerId1), array('haruspex', 'haruspex'), 3);
        $this->assertEquals('Game create failed because a player has been selected more than once.', $this->object->message);
        $this->assertEquals(NULL, $retval);


        //////////////////// 
	// Verify that a continuation of this game with an invalid previous game fails
        $this->object = new BMInterface(TRUE);
        $tmpRetval = $this->object->create_game(array($playerId1, $playerId2), array('haruspex', 'haruspex'), 1, '', -3);


        //////////////////// 
	// Verify that a continuation of this game with different players fails
        $this->object = new BMInterface(TRUE);
        $tmpRetval = $this->object->create_game(array($playerId3, $playerId2), array('haruspex', 'haruspex'), 1, '', $gameId);
        $this->assertEquals('Game create failed because the previous game does not contain the same players.', $this->object->message);


        //////////////////// 
        // Creation of continuation game

        // Both dice are initially rolled:
        // (99)  (99)
        $oldGameId = $gameId;
        $gameId = $this->verify_create_game(
            array(29, 50),
            array($playerId1, $playerId2), array('haruspex', 'haruspex'), 1, 'another competitive and interesting game', $oldGameId);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, $playerId1, $playerId2, $username1, $username2, 1, 'START_TURN');
        $expData['description'] = 'another competitive and interesting game';
        $expData['previousGameId'] = $oldGameId;
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 49.5;
        $expData['playerDataArray'][1]['roundScore'] = 49.5;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'artFilename' => 'haruspex.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'artFilename' => 'haruspex.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 29, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 50, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => $username1 . ' won initiative for round 1. Initial die values: ' . $username1 . ' rolled [(99):29], ' . $username2 . ' rolled [(99):50].'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => 'I think you\'ve got this one'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => 'Good game!'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => '[i]Continued from [game=' . $oldGameId . '][i]'));

        // load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 01 (game 2) - player 1 passes

        // (99)  vs  (99)
        $this->verify_submit_turn(
            array(),
            $username1 . ' passed. ',
            $retval, array(),
            $playerId1, $gameId, 1, 'Pass', 0, 1, '');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $username1 . ' passed'));

        // now load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 02 (game 2) - player 1 submits chat

        $this->object = new BMInterface(TRUE);
        $retval = $this->object->submit_chat($playerId1, $gameId, NULL, 'There was something i meant to say');
        $this->assertEquals('Added game message', $this->object->message);
        $this->assertEquals(TRUE, $retval);

        // expected changes as a result
        $expData['gameChatEditable'] = 'TIMESTAMP';
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => 'There was something i meant to say'));

        // now load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 03 (game 2) - player 1 updates chat

        $this->object = new BMInterface(TRUE);
        $retval = $this->object->submit_chat($playerId1, $gameId, $retval['gameChatEditable'], '...but i forgot what it was');
        $this->assertEquals('Updated previous game message', $this->object->message);
        $this->assertEquals(TRUE, $retval);

        // expected changes as a result
        $expData['gameChatLog'][0]['message'] = '...but i forgot what it was';

        // now load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 04 (game 2) - player 1 deletes chat

        $this->object = new BMInterface(TRUE);
        $retval = $this->object->submit_chat($playerId1, $gameId, $retval['gameChatEditable'], '');
        $this->assertEquals('Deleted previous game message', $this->object->message);
        $this->assertEquals(TRUE, $retval);

        // expected changes as a result
        $expData['gameChatEditable'] = FALSE;
        array_shift($expData['gameChatLog']);

        // now load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 05 (game 2) - player 2 wins game without chatting

        // (99)  vs  (99)
        $this->verify_submit_turn(
            array(11),
            $username2 . ' performed Power attack using [(99):50] against [(99):29]; Defender (99) was captured; Attacker (99) rerolled 50 => 11. End of round: ' . $username2 . ' won round 1 (148.5 vs. 0). ',
            $retval, array(array(1, 0), array(0, 0)),
            $playerId2, $gameId, 1, 'Power', 1, 0, '');

        // expected changes as a result of the attack
        $expData['gameState'] = 'END_GAME';
        $expData['activePlayerIdx'] = NULL;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 0;
        $expData['playerDataArray'][1]['roundScore'] = 0;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][0]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'] = array();
        $expData['playerDataArray'][1]['activeDieArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => $username2 . ' performed Power attack using [(99):50] against [(99):29]; Defender (99) was captured; Attacker (99) rerolled 50 => 11'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username2, 'message' => 'End of round: ' . $username2 . ' won round 1 (148.5 vs. 0)'));
        // chat from previous game is no longer included in a closed continuation game
        array_pop($expData['gameChatLog']);
        array_pop($expData['gameChatLog']);

        // now load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Creation of another continuation game

        // Both dice are initially rolled:
        // (99)  (99)
        $secondGameId = $gameId;
        $gameId = $this->verify_create_game(
            array(13, 64),
            array($playerId1, $playerId2), array('haruspex', 'haruspex'), 1, 'this series is a nailbiter', $secondGameId);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, $playerId1, $playerId2, $username1, $username2, 1, 'START_TURN');
        $expData['description'] = 'this series is a nailbiter';
        $expData['previousGameId'] = $secondGameId;
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Pass');
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 49.5;
        $expData['playerDataArray'][1]['roundScore'] = 49.5;
        $expData['playerDataArray'][0]['sideScore'] = 0;
        $expData['playerDataArray'][1]['sideScore'] = 0;
        $expData['playerDataArray'][0]['canStillWin'] = TRUE;
        $expData['playerDataArray'][1]['canStillWin'] = TRUE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'artFilename' => 'haruspex.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'artFilename' => 'haruspex.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 13, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 64, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => $username1 . ' won initiative for round 1. Initial die values: ' . $username1 . ' rolled [(99):13], ' . $username2 . ' rolled [(99):64].'));
        // This behavior may change depending on the resolution of #1170
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => '[i]Continued from [game=' . $oldGameId . '][i]'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => '[i]Continued from [game=' . $secondGameId . '][i]'));

        // load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);


        //////////////////// 
        // Move 01 (game 3) - player 1 passes

        // (99)  vs  (99)
        $this->verify_submit_turn(
            array(),
            $username1 . ' passed. ',
            $retval, array(),
            $playerId1, $gameId, 1, 'Pass', 0, 1, 'Who will win?  The suspense is killing me!');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['gameChatEditable'] = 'TIMESTAMP';
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => $username1 . ' passed'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => $username1, 'message' => 'Who will win?  The suspense is killing me!'));

        // now load the game and check its state
        $retval = $this->verify_load_api_game_data($expData, $playerId1, $gameId, $logEntryLimit);
    }
}
