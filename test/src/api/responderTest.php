<?php

// Mock auth_session_exists() for unit test use
$dummyUserLoggedIn = FALSE;
function auth_session_exists() {
    global $dummyUserLoggedIn;
    return $dummyUserLoggedIn;
}

class responderTest extends PHPUnit_Framework_TestCase {

    /**
     * @var spec    ApiSpec object which will be used as a helper
     * @var dummy   dummy_responder object used to check the live responder
     */
    protected $spec;
    protected $dummy;
    protected $user_ids;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {

        // setup the test interface
        //
        // The multiple paths are to deal with the many diverse testing
        // environments that we have, and their different assumptions about
        // which directory is the unit test run directory.
        if (file_exists('../test/src/database/mysql.test.inc.php')) {
            require_once '../test/src/database/mysql.test.inc.php';
        } else {
            require_once 'test/src/database/mysql.test.inc.php';
        }

        if (file_exists('../src/api/ApiResponder.php')) {
            require_once '../src/api/ApiResponder.php';
            require_once '../src/api/ApiSpec.php';
        } else {
            require_once 'src/api/ApiResponder.php';
            require_once 'src/api/ApiSpec.php';
        }
        $this->spec = new ApiSpec();

        if (file_exists('../src/api/DummyApiResponder.php')) {
            require_once '../src/api/DummyApiResponder.php';
        } else {
            require_once 'src/api/DummyApiResponder.php';
        }
        $this->dummy = new DummyApiResponder($this->spec, True);

        // Cache user IDs parsed from the DB for use within a test
        $this->user_ids = array();

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

    /**
     * Utility function to get skill information for use in games
     */
    protected function get_skill_info($skillNames) {
        $skillInfo = array(
            'Auxiliary' => array(
                'code' => '+',
                'description' => 'These are optional extra dice. Before each game, both players decide whether or not to play with their Auxiliary Dice. Only if both players choose to have them will they be in play.',
                'interacts' => array(),
            ),
            'Mood' => array(
                'code' => '?',
                'description' => 'These are a subcategory of Swing dice, whose size changes randomly when rerolled. At the very start of the game (and again after any round they lose, just as with normal Swing dice) the player sets the initial size of Mood Swing dice, but from then on whenever they are rolled their size is set randomly to any legal size for that Swing type.',
                'interacts' => array(),
            ),
            'Morphing' => array(
                'code' => 'm',
                'description' => 'When a Morphing Die is used in any attack, it changes size, becoming the same size as the die that was captured. It is then re-rolled. Morphing Dice change size every time they capture another die. If a Morphing die is captured, its scoring value is based on its size at the time of capture; likewise, if it is not captured during a round, its scoring value is based on its size at the end of the round',
                'interacts' => array(),
            ),
            'Ornery' => array(
                'code' => 'o',
                'description' => 'Ornery dice reroll every time the player makes any attack - whether the Ornery dice participated in it or not. The only time they don\'t reroll is if the player passes, making no attack whatsoever.',
                'interacts' => array(
                    'Mood' => 'Dice with both Ornery and Mood Swing have their sizes randomized during ornery rerolls',
                ),
            ),
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
            'Slow' => array(
                'code' => 'w',
                'description' => 'These dice are not counted for the purposes of initiative.',
                'interacts' => array(),
            ),
        );
        $retval = array();
        foreach ($skillNames as $skillName) {
            $retval[$skillName] = $skillInfo[$skillName];
            $retval[$skillName]['interacts'] = array();
            foreach ($skillNames as $otherSkill) {
                if (array_key_exists($otherSkill, $skillInfo[$skillName]['interacts'])) {
                    $retval[$skillName]['interacts'][$otherSkill] = $skillInfo[$skillName]['interacts'][$otherSkill];
                }
            }
        }
        return $retval;
    }

    /**
     * Utility function to suppress non-zero timestamps in a game data option.
     * This function shouldn't do any assertions itself; that's the caller's job.
     */
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
            if (count($modData['playerDataArray']) > 0) {
                foreach ($modData['playerDataArray'] as $idx => $value) {
                    if (array_key_exists('lastActionTime', $value) && is_int($value['lastActionTime']) && $value['lastActionTime'] > 0) {
                        $modData['playerDataArray'][$idx]['lastActionTime'] = 'TIMESTAMP';
                    }
                }
            }
        }
        return $modData;
    }

    /**
     * Check two PHP arrays to see if their structures match to a depth of one level:
     * * Do the arrays have the same sets of keys?
     * * Does each key have the same type of value for each array?
     */
    protected function object_structures_match($obja, $objb, $inspect_child_arrays=False) {
        foreach ($obja as $akey => $avalue) {
            if (!(array_key_exists($akey, $objb))) {
                $this->output_mismatched_objects($obja, $objb);
                return False;
            }
            if (gettype($obja[$akey]) != gettype($objb[$akey])) {
                $this->output_mismatched_objects($obja, $objb);
                return False;
            }
            if (($inspect_child_arrays) and (gettype($obja[$akey]) == 'array')) {
                if ((array_key_exists(0, $obja[$akey])) || (array_key_exists(0, $objb[$akey]))) {
                    if (gettype($obja[$akey][0]) != gettype($objb[$akey][0])) {
                        $this->output_mismatched_objects($obja, $objb);
                        return False;
                    }
                }
            }
        }
        foreach ($objb as $bkey => $bvalue) {
            if (!(array_key_exists($bkey, $obja))) {
                $this->output_mismatched_objects($obja, $objb);
                return False;
            }
        }
        return True;
    }

    /**
     * Utility function to construct a valid array of participating
     * dice, given loadGameData output and the set of dice
     * which should be selected for the attack.  Each attacker
     * should be specified as array(playerIdx, dieIdx)
     */
    protected function generate_valid_attack_array($gameData, $participatingDice) {
        $attack = array();
        foreach ($gameData['playerDataArray'] as $playerIdx => $playerData) {
            if (count($playerData['activeDieArray']) > 0) {
                foreach ($playerData['activeDieArray'] as $dieIdx => $dieInfo) {
                    $attack['playerIdx_' . $playerIdx . '_dieIdx_' . $dieIdx] = 'false';
                }
            }
        }
        if (count($participatingDice) > 0) {
            foreach ($participatingDice as $participatingDie) {
                $playerIdx = $participatingDie[0];
                $dieIdx = $participatingDie[1];
                $attack['playerIdx_' . $playerIdx . '_dieIdx_' . $dieIdx] = 'true';
            }
        }
        return $attack;
    }

    /**
     * Utility function to initialize a data array, just because
     * there's a lot of stuff in this, and a lot of it is always
     * the same at the beginning of a game, so save some typing.
     * This does *not* initialize buttons or active dice --- you
     * need to do that
     */
    protected function generate_init_expected_data_array(
        $gameId, $username1, $username2, $maxWins, $gameState
    ) {
        $playerId1 = $this->user_ids[$username1];
        $playerId2 = $this->user_ids[$username2];
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
                    'lastActionTime' => 'TIMESTAMP',
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
                    'lastActionTime' => 'TIMESTAMP',
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
     * Helper method used by object_structures_match() to provide debugging
     * feedback if the check fails.
     */
    private function output_mismatched_objects($obja, $objb) {
        var_dump('First object: ');
        var_dump($obja);
        var_dump('Second object: ');
        var_dump($objb);
    }

    /**
     * Make sure four users, responder001-004, exist, and return
     * fake session data for whichever one was requested.
     */
    protected function mock_test_user_login($username = 'responder003') {

        $responder = new ApiResponder($this->spec, TRUE);

        $args = array('type' => 'createUser', 'password' => 't');

        $userarray = array('responder001', 'responder002', 'responder003', 'responder004');

	// Hack: we don't know in advance whether each user creation
	// will succeed or fail.  Therefore, repeat each one so we
	// know it must fail the second time, and parse the user
	// ID from the message that second time.
        foreach ($userarray as $newuser) {
            if (!(array_key_exists($newuser, $this->user_ids))) {
                $args['username'] = $newuser;
                $args['email'] = $newuser . '@example.com';
                $ret1 = $responder->process_request($args);
                if ($ret1['data']) {
                    $ret1 = $responder->process_request($args);
                }
                $matches = array();
                preg_match('/id=(\d+)/', $ret1['message'], $matches);
                $this->user_ids[$newuser] = (int)$matches[1];
            }
        }

        // now set dummy "logged in" variable and return $_SESSION variable style data for requested user
        global $dummyUserLoggedIn;
        $dummyUserLoggedIn = TRUE;
        return array('user_name' => $username, 'user_id' => $this->user_ids[$username]);
    }

    protected function verify_login_required($type) {
        $args = array('type' => $type);
        $this->verify_api_failure($args, "You need to login before calling API function $type");
    }

    protected function verify_invalid_arg_rejected($type) {
        $args = array('type' => $type, 'foobar' => 'foobar');
        $this->verify_api_failure($args, "Unexpected argument provided to function $type");
    }

    protected function verify_mandatory_args_required($type, $required_args) {
        foreach (array_keys($required_args) as $missing) {
            $args = array('type' => $type);
            foreach ($required_args as $notmissing => $value) {
                if ($missing != $notmissing) {
                    $args[$notmissing] = $value;
                }
            }
            $this->verify_api_failure($args, "Missing mandatory argument $missing for function $type");
        }
    }

    /**
     * verify_api_failure() - helper routine which invokes a live
     * responder to process a given set of arguments, and asserts that the
     * API returns a clean failure.
     */
    protected function verify_api_failure($args, $expMessage) {
        $responder = new ApiResponder($this->spec, True);
        $retval = $responder->process_request($args);
	// unexpected behavior may manifest as API successes which should be failures,
        // so help debugging by printing the full API args and response if it comes to that
        $this->assertEquals('failed', $retval['status'],
            "API call should fail:\nARGS: " . var_export($args, $return=TRUE) . "\nRETURN: " . var_export($retval, $return=TRUE));
        $this->assertEquals(NULL, $retval['data']);
        $this->assertEquals($expMessage, $retval['message']);
        return $retval;
    }

    /**
     * verify_api_success() - helper routine which invokes a live
     * responder to process a given set of arguments, and asserts that the
     * API returns a success, and doesn't leave any random values unused.
     *
     * Unlike in the failure case, here it's the caller's responsibility
     * to test the contents of $retval['data'] and $retval['message']
     */
    protected function verify_api_success($args) {
        global $BM_RAND_VALS;
        $responder = new ApiResponder($this->spec, True);
        $retval = $responder->process_request($args);
	// unexpected regressions may manifest as API failures, so help debugging
        // by printing the full API args and response if it comes to that
        $this->assertEquals('ok', $retval['status'],
            "API call should succeed:\nARGS: " . var_export($args, $return=TRUE) . "\nRETURN: " . var_export($retval, $return=TRUE));
        $this->assertEquals(0, count($BM_RAND_VALS));
        return $retval;
    }

    /**
     * verify_api_createGame() - helper routine which calls the API
     * createGame method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_createGame(
        $postCreateDieRolls, $player1, $player2, $button1, $button2, $maxWins, $description='', $prevGame=NULL, $returnType='gameId'
    ) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postCreateDieRolls;
        $args = array(
            'type' => 'createGame',
            'playerInfoArray' => array(array($player1, $button1), array($player2, $button2)),
            'maxWins' => $maxWins,
            'description' => $description,
        );
        if ($prevGame) {
            $args['previousGameId'] = $prevGame;
        }
        $retval = $this->verify_api_success($args);
        $gameId = $retval['data']['gameId'];
        $this->assertEquals('Game ' . $gameId . ' created successfully.', $retval['message']);
        $this->assertEquals(array('gameId' => $gameId), $retval['data']);
        if ($returnType == 'gameId') {
            return $gameId;
        } else {
            return $retval;
        }
    }

    /**
     * verify_api_joinOpenGame() - helper routine which calls the API
     * joinOpenGame method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_joinOpenGame($postJoinDieRolls, $gameId) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postJoinDieRolls;
        $args = array(
            'type' => 'joinOpenGame',
            'gameId' => $gameId,
        );
        $retval = $this->verify_api_success($args);
        // FIXME: once #1274 is resolved, actually test the message here
        // $this->assertEquals('foobar', $retval['message']);
        $this->assertEquals(TRUE, $retval['data']);
        return $retval['data'];
    }

    /*
     * verify_api_loadGameData() - helper routine which calls the API
     * loadGameData method, makes standard assertions about its
     * return value which shouldn't change, and compares its return
     * value to an expected game state object compiled by the caller.
     */
    protected function verify_api_loadGameData($expData, $gameId, $logEntryLimit, $check=TRUE) {
        $args = array(
            'type' => 'loadGameData',
            'game' => $gameId,
            'logEntryLimit' => $logEntryLimit,
        );
        $retval = $this->verify_api_success($args);
// FIXME: uncomment this when #1225 is fixed
//        $this->assertEquals('Loaded data for game ' . $gameId . '.', $retval['message']);
        if ($check) {
            $cleanedData = $this->squash_game_data_timestamps($retval['data']);
            $this->assertEquals($expData, $cleanedData);
        }
        return $retval['data'];
    }

    /**
     * verify_api_reactToAuxiliary() - helper routine which calls the API
     * reactToAuxiliary method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_reactToAuxiliary($postSubmitDieRolls, $expMessage, $gameId, $action, $dieIdx) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postSubmitDieRolls;
        $args = array(
            'type' => 'reactToAuxiliary',
            'gameId' => $gameId,
            'action' => $action,
            'dieIdx' => $dieIdx,
        );
        $retval = $this->verify_api_success($args);
        $this->assertEquals($expMessage, $this->object->message);
        $this->assertEquals(TRUE, $retval);
    }

    /**
     * verify_api_submitDieValues() - helper routine which calls the API
     * submitDieValues method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_submitDieValues($postSubmitDieRolls, $gameId, $roundNum, $swingArray=NULL, $optionArray=NULL) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postSubmitDieRolls;
        $args = array(
            'type' => 'submitDieValues',
            'game' => $gameId,
            'roundNumber' => $roundNum,
            // BUG: this argument will no longer be needed when #1275 is fixed
            'timestamp' => 1234567890,
        );
        if ($swingArray) {
            $args['swingValueArray'] = $swingArray;
        }
        if ($optionArray) {
            $args['optionValueArray'] = $optionArray;
        }
        $retval = $this->verify_api_success($args);
        $this->assertEquals('Successfully set die sizes', $retval['message']);
        $this->assertEquals(TRUE, $retval['data']);
        return $retval;
    }

    /**
     * verify_api_submitTurn() - helper routine which calls the API
     * submitTurn method using provided fake die rolls, and makes
     * standard assertions about its return value
     */
    protected function verify_api_submitTurn(
        $postSubmitDieRolls, $expMessage, $prevData, $participatingDice,
        $gameId, $roundNum, $attackType, $attackerIdx, $defenderIdx, $chat
    ) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postSubmitDieRolls;
        $dieSelects = $this->generate_valid_attack_array($prevData, $participatingDice);
        $args = array(
            'type' => 'submitTurn',
            'game' => $gameId,
            'roundNumber' => $roundNum,
            'timestamp' => $prevData['timestamp'],
            'dieSelectStatus' => $dieSelects,
            'attackType' => $attackType,
            'attackerIdx' => $attackerIdx,
            'defenderIdx' => $defenderIdx,
            'chat' => $chat,
        );
        $retval = $this->verify_api_success($args);

        $this->assertEquals($expMessage, $retval['message']);
        $this->assertEquals(TRUE, $retval['data']);
        return $retval;
    }

    /**
     * verify_api_submitTurn_failure() - helper routine which calls the API
     * submitTurn method with arguments which *should* lead to a
     * failure condition, and verifies that the call fails with the expected parameters
     */
    protected function verify_api_submitTurn_failure(
        $postSubmitDieRolls, $expMessage, $prevData, $participatingDice,
        $gameId, $roundNum, $attackType, $attackerIdx, $defenderIdx, $chat
    ) {
        global $BM_RAND_VALS;
        $BM_RAND_VALS = $postSubmitDieRolls;
        $dieSelects = $this->generate_valid_attack_array($prevData, $participatingDice);
        $args = array(
            'type' => 'submitTurn',
            'game' => $gameId,
            'roundNumber' => $roundNum,
            'timestamp' => $prevData['timestamp'],
            'dieSelectStatus' => $dieSelects,
            'attackType' => $attackType,
            'attackerIdx' => $attackerIdx,
            'defenderIdx' => $defenderIdx,
            'chat' => $chat,
        );
        $retval = $this->verify_api_failure($args, $expMessage);
        return $retval;
    }



    public function test_request_invalid() {
        $args = array('type' => 'foobar');
        $retval = $this->verify_api_failure($args, 'Specified API function does not exist');

	// This test result should hold for all functions, since
	// the structure of the top-level response doesn't depend
	// on the function
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals($retval, $dummyval);
        $this->assertTrue(
            $this->object_structures_match($retval, $dummyval),
            "Real and dummy return values should have matching structures");
    }

    public function test_request_createUser() {
        $this->verify_invalid_arg_rejected('createUser');
        $this->verify_mandatory_args_required(
            'createUser',
            array('username' => 'foobar', 'password' => 't', 'email' => 'foobar@example.com')
        );

        $created_real = False;
        $maxtries = 999;
        $trynum = 1;

	// Tests may be run multiple times.  Find a user of the
	// form responderNNN which hasn't been created yet and
	// create it in the test DB.  The dummy interface will claim
	// success for any username of this form.
        while (!($created_real)) {
            $this->assertTrue($trynum < $maxtries,
                "Internal test error: too many responderNNN users in the test database. " .
                "Clean these out by hand.");
            $username = 'responder' . sprintf('%03d', $trynum);
            $responder = new ApiResponder($this->spec, TRUE);
            $real_new = $responder->process_request(
                            array('type' => 'createUser',
                                  'username' => $username,
                                  'password' => 't',
                                  'email' => $username . '@example.com'));
            if ($real_new['status'] == 'ok') {
                $created_real = True;

                // create the same user again and make sure it fails this time
                $this->verify_api_failure(
                    array('type' => 'createUser',
                          'username' => $username,
                          'password' => 't',
                          'email' => $username . '@example.com'),
                    $username . ' already exists (id=' . $real_new['data']['playerId'] . ')'
                );
            }
            $trynum += 1;
        }
        $dummy_new = $this->dummy->process_request(
                         array('type' => 'createUser',
                               'username' => $username,
                               'password' => 't',
                               'email' => $username . '@example.com'));

        // remove debugging playerId attribute
        unset($real_new['data']['playerId']);

        $this->assertEquals($dummy_new, $real_new,
            "Creation of $username user should be reported as success");

        // Since user IDs are sequential, this is a good time to test the behavior of
        // to verify the behavior of loadProfileInfo() on an invalid player name.
        // However, mock_test_user_login() ensures that users 1-4 are created, so skip those
        $_SESSION = $this->mock_test_user_login();
        $badUserNum = max(5, $trynum);
        $args = array(
           'type' => 'loadProfileInfo',
           'playerName' =>  'responder' . sprintf('%03d', $badUserNum),
        );
        $this->verify_api_failure($args, 'Player name does not exist.');
    }

    public function test_request_verifyUser() {
        $this->verify_invalid_arg_rejected('verifyUser');
        $this->verify_mandatory_args_required(
            'verifyUser',
            array('playerId' => '4', 'playerKey' => 'beadedfacade')
        );
    }

    public function test_request_createGame() {
        $this->verify_login_required('createGame');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('createGame');
        $this->verify_mandatory_args_required(
            'createGame',
            array(
                'playerInfoArray' => array(array('responder003', 'Avis'),
                                           array('responder004', 'Avis')),
                'maxWins' => '3',
            )
        );

        // Make sure a button name with a backtick is rejected
        $args = array(
            'type' => 'createGame',
            'playerInfoArray' => array(array('responder003', 'Avis'),
                                       array('responder004', 'Av`is')),
            'maxWins' => '3',
        );
        $this->verify_api_failure($args, 'Game create failed because a button name was not valid.');

        // Make sure that the first player in a game is the current logged in player
        $args = array(
            'type' => 'createGame',
            'playerInfoArray' => array(array('responder001', 'Avis'),
                                       array('responder004', 'Avis')),
            'maxWins' => '3',
        );
        $this->verify_api_failure($args, 'Game create failed because you must be the first player.');

        $retval = $this->verify_api_createGame(
            array(1, 1, 1, 1, 2, 2, 2, 2),
            'responder003', 'responder004', 'Avis', 'Avis', 3, '', NULL, 'data'
        );
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], 'Game creation should succeed');

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata),
            "Real and dummy game creation return values should have matching structures");
    }

    public function test_request_searchGameHistory() {
        $this->verify_login_required('searchGameHistory');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('searchGameHistory');

        // make sure there's at least one game
        $this->verify_api_createGame(
            array(1, 1, 1, 1, 2, 2, 2),
            'responder003', 'responder004', 'Hammer', 'Stark', 3
        );

        $args = array(
            'type' => 'searchGameHistory',
            'sortColumn' => 'lastMove',
            'sortDirection' => 'DESC',
            'numberOfResults' => '20',
            'page' => '1',
            'buttonNameA' => 'Avis');
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('Sought games retrieved successfully.', $retval['message']);

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata, True),
            "Real and dummy game lists should have matching structures");
    }

    public function test_request_joinOpenGame() {
        $this->verify_login_required('joinOpenGame');

        $_SESSION = $this->mock_test_user_login('responder003');
        $this->verify_invalid_arg_rejected('joinOpenGame');
        $this->verify_mandatory_args_required(
            'joinOpenGame',
            array('gameId' => 21)
        );

        // Make sure a button name with a backtick is rejected
        $args = array(
            'type' => 'joinOpenGame',
            'gameId' => 21,
            'buttonName' => 'Av`is',
        );
        $this->verify_api_failure($args, 'Argument (buttonName) to function joinOpenGame is invalid');

        $_SESSION = $this->mock_test_user_login('responder004');
        $gameId = $this->verify_api_createGame(
            array(),
            'responder004', '', 'Avis', 'Avis', '3'
        );

        $_SESSION = $this->mock_test_user_login('responder003');
        $retdata = $this->verify_api_joinOpenGame(array(1, 1, 1, 1, 2, 2, 2, 2), $gameId);

        $args = array(
            'type' => 'joinOpenGame',
            'gameId' => $gameId,
        );
        $dummyval = $this->dummy->process_request($args);
        $dummydata = $dummyval['data'];

        $this->assertEquals($retdata, $dummydata,
            "Real and dummy game joining return values should both be true");
    }

    public function test_request_loadOpenGames() {
        $this->verify_login_required('loadOpenGames');

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_invalid_arg_rejected('loadOpenGames');

        $gameId = $this->verify_api_createGame(
            array(),
            'responder004', '', 'Avis', 'Avis', '3'
        );

        $args = array(
            'type' => 'loadOpenGames',
        );
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];

        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata, True),
            "Real and dummy game lists should have matching structures");
    }

    public function test_request_loadActiveGames() {
        $this->verify_login_required('loadActiveGames');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadActiveGames');

        // make sure there's at least one game
        $this->verify_api_createGame(
            array(1, 1, 1, 1, 2, 2, 2),
            'responder003', 'responder004', 'Hammer', 'Stark', '3'
        );

        $args = array('type' => 'loadActiveGames');
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata, True),
            "Real and dummy game lists should have matching structures");
    }

    public function test_request_loadCompletedGames() {
        $this->verify_login_required('loadCompletedGames');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadCompletedGames');

        $args = array('type' => 'loadCompletedGames');
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $dummyval['status'], 'Dummy load of completed games should succeed');
    }

    public function test_request_loadNextPendingGame() {
        $this->verify_login_required('loadNextPendingGame');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadNextPendingGame');

        // loadGameData should fail if currentGameId is non-numeric
        $args = array('type' => 'loadNextPendingGame', 'currentGameId' => 'foobar');
        $this->verify_api_failure($args, 'Argument (currentGameId) to function loadNextPendingGame is invalid');

        $args = array('type' => 'loadNextPendingGame');
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $dummyval['status'],
            'Dummy load of next pending game ID should succeed');

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($retdata, $dummydata, TRUE),
            "Real and dummy pending game data should have matching structures");

        // now skip a game and verify that this is a valid invocation
        $args['currentGameId'] = 7;
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $dummyval['status'],
            'Dummy load of next pending game ID while skipping current game should succeed');

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($retdata, $dummydata, TRUE),
            "Real and dummy pending game data should have matching structures");
    }

    public function test_request_loadActivePlayers() {
        $this->verify_login_required('loadActivePlayers');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadActivePlayers');

        $this->verify_mandatory_args_required(
            'loadActivePlayers',
            array('numberOfPlayers' => 20)
        );

        $args = array('type' => 'loadActivePlayers', 'numberOfPlayers' => 20);
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata, True),
            "Real and dummy player names should have matching structures");
    }

    public function test_request_loadButtonData() {
        $this->verify_login_required('loadButtonData');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadButtonData');

        // First, examine one button in detail
        $args = array('type' => 'loadButtonData', 'buttonName' => 'Avis');
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($retdata[0], $dummydata[0], True),
            "Real and dummy button lists should have matching structures");

        // Then examine the rest
        $args = array('type' => 'loadButtonData');
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($retdata[0], $dummydata[0], False),
            "Real and dummy button lists should have matching structures");

        // Each button in the dummy data should exactly match a
        // button in the live data
        foreach ($dummydata as $dummyButton) {
            $foundButton = False;
            foreach ($retdata as $realButton) {
                if ($dummyButton['buttonName'] === $realButton['buttonName']) {
                    $foundButton = True;
                    $this->assertEquals(
                        $dummyButton,
                        $realButton,
                        'Dummy and live information about button ' . $dummyButton['buttonName'] . ' should match exactly'
                    );
                }
            }
            $this->assertTrue($foundButton, 'Dummy button ' . $dummyButton['buttonName'] . ' was found in live data');
        }
    }

    public function test_request_loadButtonSetData() {
        $this->verify_login_required('loadButtonSetData');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadButtonSetData');

        // First, examine one set in detail
        $args = array('type' => 'loadButtonSetData', 'buttonSet' => 'The Big Cheese');
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($retdata[0], $dummydata[0], True),
            "Real and dummy set lists should have matching structures");

        // Then examine the rest
        $args = array('type' => 'loadButtonSetData');
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($retdata[0], $dummydata[0], False),
            "Real and dummy set lists should have matching structures");

        // Each button in the dummy data should exactly match a
        // button in the live data
        foreach ($dummydata as $dummySet) {
            $foundSet = False;
            foreach ($retdata as $realSet) {
                if ($dummySet['setName'] === $realSet['setName']) {
                    $foundSet = True;
                    $this->assertEquals(
                        $dummySet,
                        $realSet,
                        'Dummy and live information about set ' . $dummySet['setName'] . ' should match exactly'
                    );
                }
            }
            $this->assertTrue($foundSet, 'Dummy set ' . $dummySet['setName'] . ' was found in live data');
        }
    }

    public function test_request_loadGameData() {
        $this->verify_login_required('loadGameData');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadGameData');

        // loadGameData should fail if game is non-numeric
        $args = array('type' => 'loadGameData', 'game' => 'foobar');
        $this->verify_api_failure($args, 'Argument (game) to function loadGameData is invalid');

        // loadGameData should fail if logEntryLimit is non-numeric
        $args = array('type' => 'loadGameData', 'game' => '3', 'logEntryLimit' => 'foobar');
        $this->verify_api_failure($args, 'Argument (logEntryLimit) to function loadGameData is invalid');

        // create a game so we have the ID to load
        $real_game_id = $this->verify_api_createGame(
            array(1, 1, 1, 1, 2, 2, 2, 2),
            'responder003', 'responder004', 'Avis', 'Avis', '3'
        );
        $dummy_game_id = '1';

        // now load real and dummy games
        $retval = $this->verify_api_success(
            array('type' => 'loadGameData', 'game' => $real_game_id, 'logEntryLimit' => 10));
        $dummyval = $this->dummy->process_request(
            array('type' => 'loadGameData', 'game' => $dummy_game_id, 'logEntryLimit' => 10));
        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata, True),
            "Real and dummy game data should have matching structures");
        $this->assertTrue(
            $this->object_structures_match($dummydata['playerDataArray'][0], $retdata['playerDataArray'][0], True),
            "Real and dummy game playerData objects should have matching structures");

        // Now hand-modify a few things we know will be different
        // and check that the data structures are entirely identical otherwise
        $dummydata['gameId'] = $retdata['gameId'];
        foreach(array_keys($retdata['playerDataArray']) as $playerIdx) {
            foreach(array('playerName', 'playerId', 'lastActionTime') as $playerKey) {
                $dummydata['playerDataArray'][$playerIdx][$playerKey] =
                    $retdata['playerDataArray'][$playerIdx][$playerKey];
            }
        }
        $dummydata['timestamp'] = $retdata['timestamp'];

        $this->assertEquals($dummydata, $retdata);

        // Since game IDs are sequential, $real_game_id + 1 should not be an existing game
        $nonexistent_game_id = $real_game_id + 1;
        $retval = $this->verify_api_failure(
            array('type' => 'loadGameData', 'game' => $nonexistent_game_id, 'logEntryLimit' => 10),
            'Game ' . $nonexistent_game_id . ' does not exist.');

        // create an open game so we have the ID to load
        $open_game_id = $this->verify_api_createGame(
            array(),
            'responder003', '', 'Avis', '', '3'
        );

        $retval = $this->verify_api_success(
            array('type' => 'loadGameData', 'game' => $open_game_id, 'logEntryLimit' => 10));
    }

    public function test_request_countPendingGames() {
        $this->verify_login_required('countPendingGames');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('countPendingGames');

        $args = array('type' => 'countPendingGames');
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $dummyval['status'],
            'Dummy load of next pending game ID should succeed');

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($retdata, $dummydata, TRUE),
            "Real and dummy pending game data should have matching structures");
    }

    public function test_request_loadPlayerName() {
        $this->verify_invalid_arg_rejected('loadPlayerName');
        $this->markTestIncomplete("No test for loadPlayerName using session and cookies");
    }

    public function test_request_loadPlayerInfo() {
        $this->verify_login_required('loadPlayerInfo');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadPlayerInfo');

        $args = array('type' => 'loadPlayerInfo');
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata, True),
            "Real and dummy player data should have matching structures");
    }

    /**
     * As a side effect, this test actually enables autopass for
     * players responder003 and responder004, which some of the game
     * tests need
     */
    public function test_request_savePlayerInfo() {
        $this->verify_login_required('savePlayerInfo');

        $_SESSION = $this->mock_test_user_login('responder003');
        $this->verify_invalid_arg_rejected('savePlayerInfo');

        $args = array(
            'type' => 'savePlayerInfo',
            'name_irl' => 'Test User',
            'is_email_public' => 'False',
            'dob_month' => '2',
            'dob_day' => '29',
            'gender' => '',
            'comment' => '',
            'homepage' => '',
            'autopass' => 'true',
            'uses_gravatar' => 'false',
            'player_color' => '#dd99dd',
            'opponent_color' => '#ddffdd',
            'neutral_color_a' => '#cccccc',
            'neutral_color_b' => '#dddddd',
            'monitor_redirects_to_game' => 'false',
            'monitor_redirects_to_forum' => 'false',
            'automatically_monitor' => 'false',
        );
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata),
            "Real and dummy player data update return values should have matching structures");

        $_SESSION = $this->mock_test_user_login('responder004');
        $retval = $this->verify_api_success($args);
    }

    public function test_request_loadProfileInfo() {
        $this->verify_login_required('loadProfileInfo');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadProfileInfo');
        $this->verify_mandatory_args_required(
            'loadProfileInfo',
            array('playerName' => 'foobar',)
        );

        $args = array('type' => 'loadProfileInfo', 'playerName' => 'responder003');
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata, True),
            "Real and dummy player data should have matching structures");
    }

    public function test_request_loadPlayerNames() {
        $this->verify_login_required('loadPlayerNames');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadPlayerNames');

        $args = array('type' => 'loadPlayerNames');
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata, True),
            "Real and dummy player names should have matching structures");
    }

    public function test_request_submitChat() {
        $this->verify_login_required('submitChat');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('submitChat');

        $this->markTestIncomplete("No test for submitChat yet");
    }

    public function test_request_submitDieValues() {
        $this->verify_login_required('submitDieValues');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('submitDieValues');

        // create a game so we have the ID to load
        $real_game_id = $this->verify_api_createGame(
            array(1, 1, 1, 1, 2, 2, 2, 2),
            'responder003', 'responder004', 'Avis', 'Avis', '3'
        );
        $dummy_game_id = '1';

        // now ask for the game data so we have the timestamp to return
        $args = array(
            'type' => 'loadGameData',
            'game' => "$real_game_id",
            'logEntryLimit' => '10');
        $retval = $this->verify_api_success($args);
        $timestamp = $retval['data']['timestamp'];

        // after die value submission, one new random value is needed
        $retval = $this->verify_api_submitDieValues(
            array(3),
            $real_game_id, '1', array('X' => '7'));

        $args = array(
            'type' => 'submitDieValues',
            'game' => $dummy_game_id,
            'roundNumber' => '1',
            'timestamp' => $timestamp,
            'swingValueArray' => array('X' => '7')
        );
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals($dummyval, $retval, "swing value submission responses should be identical");

        ///// Now test setting option values
        // create a game so we have the ID to load
        $real_game_id = $this->verify_api_createGame(
            array(1, 1, 2, 2),
            'responder003', 'responder004', 'Apples', 'Apples', '3'
        );
        $dummy_game_id = '19';

        // now ask for the game data so we have the timestamp to return
        $args = array(
            'type' => 'loadGameData',
            'game' => "$real_game_id");
        $retval = $this->verify_api_success($args);
        $timestamp = $retval['data']['timestamp'];

        // now submit the option values
        $retval = $this->verify_api_submitDieValues(
            array(3, 3, 3),
            $real_game_id, '1', NULL, array(2 => 12, 3 => 8, 4 => 20));

        $args = array(
            'type' => 'submitDieValues',
            'game' => $dummy_game_id,
            'roundNumber' => '1',
            'timestamp' => $timestamp,
            'optionValueArray' => array(2 => 12, 3 => 8, 4 => 20));
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals($dummyval, $retval, "option value submission responses should be identical");
    }

    public function test_request_reactToAuxiliary() {
        $this->verify_login_required('reactToAuxiliary');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('reactToAuxiliary');
        $this->verify_mandatory_args_required(
            'reactToAuxiliary',
            array(
                'game' => '18',
                'action' => 'decline',
            )
        );
    }

    public function test_request_reactToReserve() {
        $this->verify_login_required('reactToReserve');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('reactToReserve');
        $this->verify_mandatory_args_required(
            'reactToReserve',
            array(
                'game' => '18',
                'action' => 'decline',
            )
        );
    }

    public function test_request_reactToInitiative() {
        $this->verify_login_required('reactToInitiative');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('reactToInitiative');

        $dummy_game_id = '7';

	// create a game so we have the ID to load, making sure we
	// get a game which is in the react to initiative game
	// state, and the other player has initiative
        $real_game_id = $this->verify_api_createGame(
            array(3, 3, 3, 3, 3, 2, 2, 2, 2, 2),
            'responder003', 'responder004', 'Crab', 'Crab', '3'
        );

        // now ask for the game data so we have the timestamp to return
        $dataargs = array(
            'type' => 'loadGameData',
            'game' => "$real_game_id",
            'logEntryLimit' => '10');
        $retval = $this->verify_api_success($dataargs);
        $timestamp = $retval['data']['timestamp'];

        $this->assertEquals("REACT_TO_INITIATIVE", $retval['data']['gameState']);
        $this->assertEquals(1, $retval['data']['playerWithInitiativeIdx']);

        // now submit the initiative response
        $args = array(
            'type' => 'reactToInitiative',
            'roundNumber' => '1',
            'timestamp' => $timestamp,
            'action' => 'focus',
            'dieIdxArray' => array('3', '4'),
            'dieValueArray' => array('1', '1'),
        );
        $args['game'] = $real_game_id;
        $retval = $this->verify_api_success($args);
        $args['game'] = $dummy_game_id;
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals($dummyval, $retval, "swing value submission responses should be identical");
        $this->assertEquals('ok', $retval['status'], "responder should succeed");
    }

    public function test_request_submitTurn() {
        $this->verify_login_required('submitTurn');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('submitTurn');

        // create a game so we can test submitTurn responses
        $real_game_id = $this->verify_api_createGame(
            array(20, 30),
            'responder003', 'responder004', 'Haruspex', 'Haruspex', '1'
        );

        $gameData = $this->verify_api_success(array(
            'type' => 'loadGameData',
            'game' => "$real_game_id",
            'logEntryLimit' => '10'));
// BUG: once #1276 is fixed, this should fail with a reasonable error
//        $this->verify_api_submitTurn_failure(
//            array(),
//            'foobar', $gameData['data'], array(),
//            $real_game_id, 1, 'Pass', 0, 0, '');

        $this->markTestIncomplete("No test for submitTurn responder yet");
    }

    public function test_request_dismissGame() {
        $this->verify_login_required('dismissGame');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('dismissGame');

        $dummy_game_id = '5';

        // create and complete a game so we have the ID to dismiss
        $real_game_id = $this->verify_api_createGame(
            array(20, 30),
            'responder003', 'responder004', 'Haruspex', 'Haruspex', '1'
        );

        $loadGameArgs = array(
            'type' => 'loadGameData',
            'game' => "$real_game_id",
            'logEntryLimit' => '10');

        // Move 1: responder003 passes
        $gameData = $this->verify_api_success($loadGameArgs);
        $this->verify_api_submitTurn(
            array(),
            'responder003 passed. ', $gameData['data'], array(),
            $real_game_id, 1, 'Pass', 0, 1, '');

        // Move 2: responder004 attacks, ending the game
        $_SESSION = $this->mock_test_user_login('responder004');
        $gameData = $this->verify_api_success($loadGameArgs);
        $this->verify_api_submitTurn(
            array(40),
            'responder004 performed Power attack using [(99):30] against [(99):20]; Defender (99) was captured; Attacker (99) rerolled 30 => 40. End of round: responder004 won round 1 (148.5 vs. 0). ',
            $gameData['data'], array(array(0, 0), array(1, 0)),
            $real_game_id, 1, 'Power', 1, 0, '');

        // now try to dismiss the game
        $args = array(
            'type' => 'dismissGame',
            'gameId' => $real_game_id,
        );
        $retval = $this->verify_api_success($args);
        $args = array(
            'type' => 'dismissGame',
            'gameId' => $dummy_game_id,
        );
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals($dummyval, $retval, "game dismissal responses should be identical");
    }

    ////////////////////////////////////////////////////////////
    // Forum-related methods

    public function test_request_createForumThread() {
        $this->verify_login_required('createForumThread');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('createForumThread');
        $this->verify_mandatory_args_required(
            'createForumThread',
            array(
                'boardId' => 1,
                'title' => 'Who likes ice cream?',
                'body' => 'I can\'t be the only one!',
            )
        );

        $args = array(
            'type' => 'createForumThread',
            'boardId' => 1,
            'title' => 'Who likes ice cream?',
            'body' => 'I can\'t be the only one!',
        );
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata),
            "Real and dummy forum thread creation return values should have matching structures");
    }

    public function test_request_createForumPost() {
        $this->verify_login_required('createForumPost');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('createForumPost');
        $this->verify_mandatory_args_required(
            'createForumPost',
            array(
                'threadId' => 1,
                'body' => 'Hey, wow, I do too!',
            )
        );

        // Create the thread first
        $args = array(
            'type' => 'createForumThread',
            'boardId' => 1,
            'title' => 'Hello Wisconsin',
            'body' => 'When are you coming home?',
        );
        $thread = $this->verify_api_success($args);

        $args = array(
            'type' => 'createForumPost',
            'threadId' => $thread['data']['threadId'],
            'body' => 'Hey, wow, I do too!',
        );
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata),
            "Real and dummy forum post creation return values should have matching structures");
    }

    public function test_request_editForumPost() {
        $this->verify_login_required('editForumPost');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('editForumPost');
        $this->verify_mandatory_args_required(
            'editForumPost',
            array(
                'postId' => 1,
                'body' => 'Hey, wow, I do too!',
            )
        );

        // Create the thread first
        $args = array(
            'type' => 'createForumThread',
            'boardId' => 1,
            'title' => 'Cat or dog?',
            'body' => 'Dog!',
        );
        $thread = $this->verify_api_success($args);

        $args = array(
            'type' => 'editForumPost',
            'postId' => (int)$thread['data']['posts'][0]['postId'],
            'body' => 'Cat!',
        );
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata),
            "Real and dummy forum post editing return values should have matching structures");
    }

    public function test_request_loadForumOverview() {
        $this->verify_login_required('loadForumOverview');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadForumOverview');

        $args = array('type' => 'loadForumOverview');
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata),
            "Real and dummy forum overview loading return values should have matching structures");
    }

    public function test_request_loadForumBoard() {
        $this->verify_login_required('loadForumBoard');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadForumBoard');
        $this->verify_mandatory_args_required(
            'loadForumBoard',
            array('boardId' => 1)
        );

        $args = array(
            'type' => 'loadForumBoard',
            'boardId' => 1,
        );
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata),
            "Real and dummy forum board loading return values should have matching structures");
    }

    public function test_request_loadForumThread() {
        $this->verify_login_required('loadForumThread');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadForumThread');
        $this->verify_mandatory_args_required(
            'loadForumThread',
            array('threadId' => 2)
        );

        // Create the thread first
        $args = array(
            'type' => 'createForumThread',
            'boardId' => 1,
            'title' => 'Hello Wisconsin',
            'body' => 'When are you coming home?',
        );
        $thread = $this->verify_api_success($args);

        $args = array(
            'type' => 'loadForumThread',
            'threadId' => $thread['data']['threadId'],
        );
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata),
            "Real and dummy forum thread loading return values should have matching structures");
    }

    public function test_request_loadNextNewPost() {
        $this->verify_login_required('loadNextNewPost');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadNextNewPost');

        // Post something new first
        $_SESSION = $this->mock_test_user_login('responder003');
        $args = array(
            'type' => 'createForumThread',
            'boardId' => 1,
            'title' => 'New Thread',
            'body' => 'New Post',
        );
        $this->verify_api_success($args);

        $_SESSION = $this->mock_test_user_login('responder004');
        $args = array('type' => 'loadNextNewPost');
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata),
            "Real and dummy new forum post check return values should have matching structures");
    }

    public function test_request_markForumBoardRead() {
        $this->verify_login_required('markForumBoardRead');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('markForumBoardRead');
        $this->verify_mandatory_args_required(
            'markForumBoardRead',
            array('boardId' => 1, 'timestamp' => strtotime('now'))
        );

        $args = array(
            'type' => 'markForumBoardRead',
            'boardId' => 1,
            'timestamp' => strtotime('now'),
        );
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata),
            "Real and dummy forum board marking as read return values should have matching structures");
    }

    public function test_request_markForumRead() {
        $this->verify_login_required('markForumRead');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('markForumRead');
        $this->verify_mandatory_args_required(
            'markForumRead',
            array('timestamp' => strtotime('now'))
        );

        $args = array(
            'type' => 'markForumRead',
            'timestamp' => strtotime('now'),
        );
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata),
            "Real and dummy entire forum marking as read return values should have matching structures");
    }

    public function test_request_markForumThreadRead() {
        $this->verify_login_required('markForumThreadRead');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('markForumThreadRead');
        $this->verify_mandatory_args_required(
            'markForumThreadRead',
            array(
                'threadId' => 1,
                'boardId' => 1,
                'timestamp' => strtotime('now'),
            )
        );

        // Create the thread first
        $args = array(
            'type' => 'createForumThread',
            'boardId' => 1,
            'title' => 'Hello Wisconsin',
            'body' => 'When are you coming home?',
        );
        $thread = $this->verify_api_success($args);

        $args = array(
            'type' => 'markForumThreadRead',
            'threadId' => $thread['data']['threadId'],
            'boardId' => 1,
            'timestamp' => strtotime('now'),
        );
        $retval = $this->verify_api_success($args);
        $dummyval = $this->dummy->process_request($args);

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata),
            "Real and dummy forum thread marking as read return values should have matching structures");
    }

    // End of Forum-related methods
    ////////////////////////////////////////////////////////////

    public function test_request_login() {
        $this->verify_invalid_arg_rejected('login');
        $this->markTestIncomplete("No test for login responder yet");
    }

    public function test_request_logout() {
        $this->verify_login_required('logout');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('logout');

        $this->markTestIncomplete("No test for logout responder yet");
    }

    /**
     * This is the same game setup as in
     * BMInterfaceTest::test_option_reset_bug(), but tested from
     * the API point of view, and we play long enough to set option dice in two consecutive rounds.
     */
    public function test_api_game_001() {

        $_SESSION = $this->mock_test_user_login('responder001');

        // Non-option dice are initially rolled, namely:
        // (4) (6) (8) (12)   (20) (20) (20) (20)
        $gameId = $this->verify_api_createGame(
            array(4, 6, 8, 12, 1, 1, 1, 1),
            'responder001', 'responder002', 'Frasquito', 'Wiseman', 4);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder001', 'responder002', 4, 'SPECIFY_DICE');
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
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - specify option dice

        // this should cause the one option die to be rerolled
        $this->verify_api_submitDieValues(
            array(2),
            $gameId, 1, NULL, array(4 => 2));

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder001', 'message' => 'responder001 set option dice: (2/20=2)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder002 won initiative for round 1. Initial die values: responder001 rolled [(4):4, (6):6, (8):8, (12):12, (2/20=2):2], responder002 rolled [(20):1, (20):1, (20):1, (20):1].'));

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - player 2 captures player 1's option die

        // capture the option die - two attacking dice need to reroll
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(1, 1),
            'responder002 performed Skill attack using [(20):1,(20):1] against [(2/20=2):2]; Defender (2/20=2) was captured; Attacker (20) rerolled 1 => 1; Attacker (20) rerolled 1 => 1. ',
            $retval, array(array(1, 0), array(1, 1), array(0, 4)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder002', 'message' => 'responder002 performed Skill attack using [(20):1,(20):1] against [(2/20=2):2]; Defender (2/20=2) was captured; Attacker (20) rerolled 1 => 1; Attacker (20) rerolled 1 => 1'));


        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - player 1 captures player 2's first 20-sider

        // 4 6 8 12 vs 1 1 1 1
        $this->verify_api_submitTurn(
            array(4),
            'responder001 performed Power attack using [(4):4] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 4 => 4. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder001', 'message' => 'responder001' . ' performed Power attack using [(4):4] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 4 => 4'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - player 2 passes

        // 4 6 8 12 vs 1 1 1
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(),
            'responder002' . ' passed. ',
            $retval, array(),
            $gameId, 1, 'Pass', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder002', 'message' => 'responder002' . ' passed'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - player 1 captures player 2's first remaining (20)

        // 4 6 8 12 vs 1 1 1
        $this->verify_api_submitTurn(
            array(3),
            'responder001' . ' performed Power attack using [(4):4] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 4 => 3. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder001', 'message' => 'responder001' . ' performed Power attack using [(4):4] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 4 => 3'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 06 - player 2 passes

        // 4 6 8 12 vs 1 1
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(),
            'responder002' . ' passed. ',
            $retval, array(),
            $gameId, 1, 'Pass', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder002', 'message' => 'responder002' . ' passed'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 07 - player 1 captures player 2's first remaining (20)

        // 4 6 8 12 vs 1 1
        $this->verify_api_submitTurn(
            array(2),
            'responder001' . ' performed Power attack using [(6):6] against [(20):1]; Defender (20) was captured; Attacker (6) rerolled 6 => 2. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder001', 'message' => 'responder001' . ' performed Power attack using [(6):6] against [(20):1]; Defender (20) was captured; Attacker (6) rerolled 6 => 2'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 08 - player 2 passes

        // 4 6 8 12 vs 1
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(),
            'responder002' . ' passed. ',
            $retval, array(),
            $gameId, 1, 'Pass', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder002', 'message' => 'responder002' . ' passed'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 09 - player 1 captures player 2's last remaining (20)

        // 4 6 8 12 vs 1
        $this->verify_api_submitTurn(
            array(4, 1, 1, 1, 1, 2, 15, 16, 17, 18),
            'responder001' . ' performed Power attack using [(4):3] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 3 => 4. End of round: ' . 'responder001' . ' won round 1 (95 vs. 2). ' . 'responder001' . ' won initiative for round 2. Initial die values: ' . 'responder001' . ' rolled [(4):1, (6):1, (8):1, (12):1, (2/20=2):2], ' . 'responder002' . ' rolled [(20):15, (20):16, (20):17, (20):18]. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder001', 'message' => 'responder001' . ' performed Power attack using [(4):3] against [(20):1]; Defender (20) was captured; Attacker (4) rerolled 3 => 4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder001', 'message' => 'End of round: ' . 'responder001' . ' won round 1 (95 vs. 2)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder001' . ' won initiative for round 2. Initial die values: ' . 'responder001' . ' rolled [(4):1, (6):1, (8):1, (12):1, (2/20=2):2], ' . 'responder002' . ' rolled [(20):15, (20):16, (20):17, (20):18].'));
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 10 - player 1 passes (round 2)

        // [(4):1, (6):1, (8):1, (12):1, (2/20=2):2] vs. [(20):15, (20):16, (20):17, (20):18]
        $this->verify_api_submitTurn(
            array(),
            'responder001' . ' passed. ',
            $retval, array(),
            $gameId, 2, 'Pass', 0, 1, '');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 11 - player 2 attacks (round 2)

        // [(4):1, (6):1, (8):1, (12):1, (2/20=2):2] vs. [(20):15, (20):16, (20):17, (20):18]
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(19),
            'responder002' . ' performed Power attack using [(20):15] against [(12):1]; Defender (12) was captured; Attacker (20) rerolled 15 => 19. ',
            $retval, array(array(0, 3), array(1, 0)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 12 - player 1 passes (round 2)

        // [(4):1, (6):1, (8):1, (2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        $this->verify_api_submitTurn(
            array(),
            'responder001' . ' passed. ',
            $retval, array(),
            $gameId, 2, 'Pass', 0, 1, '');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 13 - player 2 attacks (round 2)

        // [(4):1, (6):1, (8):1, (2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(16),
            'responder002' . ' performed Power attack using [(20):16] against [(8):1]; Defender (8) was captured; Attacker (20) rerolled 16 => 16. ',
            $retval, array(array(0, 2), array(1, 1)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 14 - player 1 passes (round 2)

        // [(4):1, (6):1, (2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        $this->verify_api_submitTurn(
            array(),
            'responder001' . ' passed. ',
            $retval, array(),
            $gameId, 2, 'Pass', 0, 1, '');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 15 - player 2 attacks (round 2)

        // [(4):1, (6):1, (2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(19),
            'responder002' . ' performed Power attack using [(20):19] against [(6):1]; Defender (6) was captured; Attacker (20) rerolled 19 => 19. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 16 - player 1 passes (round 2)

        // [(4):1, (2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        $this->verify_api_submitTurn(
            array(),
            'responder001' . ' passed. ',
            $retval, array(),
            $gameId, 2, 'Pass', 0, 1, '');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 17 - player 2 attacks (round 2)

        // [(4):1, (2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(19),
            'responder002' . ' performed Power attack using [(20):19] against [(4):1]; Defender (4) was captured; Attacker (20) rerolled 19 => 19. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 18 - player 1 passes (round 2)

        // [(2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        $this->verify_api_submitTurn(
            array(),
            'responder001' . ' passed. ',
            $retval, array(),
            $gameId, 2, 'Pass', 0, 1, '');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 19 - player 2 attacks (round 2)

        // [(2/20=2):2] vs. [(20):19, (20):16, (20):17, (20):18]
        // 1 value for attacker's reroll, then 4 + 4 for non-option dice for round 3
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_submitTurn(
            array(19, 2, 2, 2, 2, 10, 10, 10, 10),
            'responder002' . ' performed Power attack using [(20):19] against [(2/20=2):2]; Defender (2/20=2) was captured; Attacker (20) rerolled 19 => 19. End of round: ' . 'responder002' . ' won round 2 (72 vs. 0). ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 2, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder001');

        // expected changes as a result of the attack
        $expData['gameState'] = 'SPECIFY_DICE';
        $expData['activePlayerIdx'] = NULL;
        $expData['roundNumber'] = 3;
        $expData['validAttackTypeArray'] = array();
        $expData['playerDataArray'][0]['gameScoreArray']['L'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][0]['roundScore'] = NULL;
        $expData['playerDataArray'][1]['roundScore'] = NULL;
        $expData['playerDataArray'][0]['sideScore'] = NULL;
        $expData['playerDataArray'][1]['sideScore'] = NULL;
        $expData['playerDataArray'][0]['canStillWin'] = NULL;
        $expData['playerDataArray'][1]['canStillWin'] = NULL;
        $expData['playerDataArray'][0]['prevOptValueArray'] = array(4 => 2);
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'Option Die (with 2 or 20 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = NULL;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = NULL;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = NULL;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = NULL;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = NULL;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = NULL;
        $expData['gameActionLog'][0]['player'] = 'responder002';
        $expData['gameActionLog'][0]['message'] = 'End of round: ' . 'responder002' . ' won round 2 (72 vs. 0)';
        $expData['gameActionLog'][1]['player'] = 'responder002';
        $expData['gameActionLog'][1]['message'] = 'responder002' . ' performed Power attack using [(20):19] against [(2/20=2):2]; Defender (2/20=2) was captured; Attacker (20) rerolled 19 => 19';
        $expData['gameActionLog'][2]['message'] = 'responder001' . ' passed';
        $expData['gameActionLog'][3]['message'] = 'responder002' . ' performed Power attack using [(20):19] against [(4):1]; Defender (4) was captured; Attacker (20) rerolled 19 => 19';
        $expData['gameActionLog'][4]['message'] = 'responder001' . ' passed';
        $expData['gameActionLog'][5]['message'] = 'responder002' . ' performed Power attack using [(20):19] against [(6):1]; Defender (6) was captured; Attacker (20) rerolled 19 => 19';
        $expData['gameActionLog'][6]['message'] = 'responder001' . ' passed';
        $expData['gameActionLog'][7]['message'] = 'responder002' . ' performed Power attack using [(20):16] against [(8):1]; Defender (8) was captured; Attacker (20) rerolled 16 => 16';
        $expData['gameActionLog'][8]['message'] = 'responder001' . ' passed';
        $expData['gameActionLog'][9]['message'] = 'responder002' . ' performed Power attack using [(20):15] against [(12):1]; Defender (12) was captured; Attacker (20) rerolled 15 => 19';

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends test_request_savePlayerInfo
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
    public function test_api_game_002() {

	// responder003 is the POV player, so if you need to fake
	// login as a different player e.g. to submit an attack, always
	// return to responder003 as soon as you've done so
        $_SESSION = $this->mock_test_user_login('responder003');

        ////////////////////
        // initial game setup

        // Non-swing dice are initially rolled, namely:
        // p(20) s(20)  (20) (20) (20)
        $gameId = $this->verify_api_createGame(
            array(2, 11, 5, 8, 12),
            'responder003', 'responder004', 'Jellybean', 'Dirgo', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Poison', 'Shadow'));
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
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - player 1 specifies swing dice

        // this causes all newly-specified swing dice to be rolled:
        // (V) (X)
        $this->verify_api_submitDieValues(
            array(3, 1),
            $gameId, 1, array('V' => 6, 'X' => 10), NULL);

        // expected changes to game state
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = 'V Swing Die (with 6 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = 'X Swing Die (with 10 sides)';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' set die sizes'));

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - player 2 specifies swing dice

        // this causes the newly-specified swing die to be rolled:
        // (X)
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(4),
            $gameId, 1, array('X' => 4), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

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
        $expData['gameActionLog'][0]['message'] = 'responder003' . ' set swing values: V=6, X=10';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' set swing values: X=4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003' . ' won initiative for round 1. Initial die values: ' . 'responder003' . ' rolled [p(20):2, s(20):11, (V=6):3, (X=10):1], ' . 'responder004' . ' rolled [(20):5, (20):8, (20):12, (X=4):4].'));

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - player 1 performs shadow attack

        // p(20) s(20) (V) (X)  vs.  (20) (20) (20) (X)
        $this->verify_api_submitTurn(
            array(15),
            'responder003' . ' performed Shadow attack using [s(20):11] against [(20):12]; Defender (20) was captured; Attacker s(20) rerolled 11 => 15. ',
            $retval, array(array(0, 1), array(1, 2)),
            $gameId, 1, 'Shadow', 0, 1, '');

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' performed Shadow attack using [s(20):11] against [(20):12]; Defender (20) was captured; Attacker s(20) rerolled 11 => 15'));

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - player 2 performs power attack; player 1 passes

        // p(20) s(20) (V) (X)  vs.  (20) (20) (X)
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(12),
            'responder004' . ' performed Power attack using [(20):5] against [(V=6):3]; Defender (V=6) was captured; Attacker (20) rerolled 5 => 12. ' . 'responder003' . ' passed. ',
            $retval, array(array(1, 0), array(0, 2)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' performed Power attack using [(20):5] against [(V=6):3]; Defender (V=6) was captured; Attacker (20) rerolled 5 => 12'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' passed'));

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - player 2 performs power attack; player 1 passes

        // p(20) s(20) (X)  vs.  (20) (20) (X)
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(13),
            'responder004' . ' performed Power attack using [(20):8] against [(X=10):1]; Defender (X=10) was captured; Attacker (20) rerolled 8 => 13. ' . 'responder003' . ' passed. ',
            $retval, array(array(1, 1), array(0, 2)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // no new code coverage; load the data, but don't bother to test it
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 06 - player 2 performs power attack; player 1 passes; player 2 passes; round ends

        // p(20) s(20)  vs.  (20) (20) (X)
        // random values needed: 1 for reroll, 7 for end of turn reroll
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1, 8, 6, 1, 1, 7, 2, 17),
            'responder004' . ' performed Power attack using [(20):12] against [p(20):2]; Defender p(20) was captured; Attacker (20) rerolled 12 => 1. ' . 'responder003' . ' passed. ' . 'responder004' . ' passed. End of round: ' . 'responder003' . ' won round 1 (30 vs. 28). ',
            $retval, array(array(1, 0), array(0, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' performed Power attack using [(20):8] against [(X=10):1]; Defender (X=10) was captured; Attacker (20) rerolled 8 => 13'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' performed Power attack using [(20):12] against [p(20):2]; Defender p(20) was captured; Attacker (20) rerolled 12 => 1'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' passed'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'End of round: ' . 'responder003' . ' won round 1 (30 vs. 28)'));
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 07 - player 2 specifies swing dice

        // this causes the swing die to be rolled:
        // (X)
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(2),
            $gameId, 2, array('X' => 7), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' set swing values: X=7'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003' . ' won initiative for round 2. Initial die values: ' . 'responder003' . ' rolled [p(20):8, s(20):6, (V=6):1, (X=10):1], ' . 'responder004' . ' rolled [(20):7, (20):2, (20):17, (X=7):2].'));
        array_pop($expData['gameActionLog']);
        array_pop($expData['gameActionLog']);

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends test_request_savePlayerInfo
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

	// responder003 is the POV player, so if you need to fake
	// login as a different player e.g. to submit an attack, always
	// return to responder003 as soon as you've done so
        $_SESSION = $this->mock_test_user_login('responder003');

        ////////////////////
        // initial game setup

        // Both dice are initially rolled:
        // (99)  (99)
        $gameId = $this->verify_api_createGame(
            array(54, 42),
            'responder003', 'responder004', 'haruspex', 'haruspex', 1, 'a competitive and interesting game');

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 1, 'START_TURN');
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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder004' . ' won initiative for round 1. Initial die values: ' . 'responder003' . ' rolled [(99):54], ' . 'responder004' . ' rolled [(99):42].'));

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Verify that a continuation of this game while it is still in progress fails
        $this->verify_api_failure(
            array(
                'type' => 'createGame',
                'playerInfoArray' => array(array('responder003', 'Haruspex'), array('responder004', 'Haruspex')),
                'maxWins' => 3,
                'previousGameId' => $gameId,
            ),
            'Game create failed because the previous game has not been completed yet.');


        ////////////////////
        // Move 01 - player 2 passes

        // (99)  vs  (99)
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(),
            'responder004' . ' passed. ',
            $retval, array(),
            $gameId, 1, 'Pass', 1, 0, 'I think you\'ve got this one');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' passed'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'I think you\'ve got this one'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - player 1 performs power attack; game ends

        // (99)  vs  (99)
        $this->verify_api_submitTurn(
            array(10),
            'responder003' . ' performed Power attack using [(99):54] against [(99):42]; Defender (99) was captured; Attacker (99) rerolled 54 => 10. End of round: ' . 'responder003' . ' won round 1 (148.5 vs. 0). ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, 'Good game!');

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' performed Power attack using [(99):54] against [(99):42]; Defender (99) was captured; Attacker (99) rerolled 54 => 10'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'End of round: ' . 'responder003' . ' won round 1 (148.5 vs. 0)'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Good game!'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Game creation failures - make sure various invalid argumentsj
        // that the public API will allow, are rejected with friendly messages

        // same player appears in the game twice
        $this->verify_api_failure(
            array(
                'type' => 'createGame',
                'playerInfoArray' => array(array('responder003', 'Haruspex'), array('responder003', 'Haruspex')),
                'maxWins' => 1,
                'previousGameId' => $gameId,
            ),
            'Game create failed because a player has been selected more than once.');

        ////////////////////
        // Verify that a continuation of this game with an invalid previous game fails
        $this->verify_api_failure(
            array(
                'type' => 'createGame',
                'playerInfoArray' => array(array('responder003', 'Haruspex'), array('responder004', 'Haruspex')),
                'maxWins' => 1,
                'previousGameId' => -3,
            ),
            'Argument (previousGameId) to function createGame is invalid');


        ////////////////////
        // Verify that a continuation of this game with different players fails
        $this->verify_api_failure(
            array(
                'type' => 'createGame',
                'playerInfoArray' => array(array('responder003', 'Haruspex'), array('responder001', 'Haruspex')),
                'maxWins' => 1,
                'previousGameId' => $gameId,
            ),
            'Game create failed because the previous game does not contain the same players.');


        ////////////////////
        // Creation of continuation game

        // Both dice are initially rolled:
        // (99)  (99)
        $oldGameId = $gameId;
        $gameId = $this->verify_api_createGame(
            array(29, 50),
            'responder003', 'responder004', 'haruspex', 'haruspex', 1, 'another competitive and interesting game', $oldGameId);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 1, 'START_TURN');
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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003' . ' won initiative for round 1. Initial die values: ' . 'responder003' . ' rolled [(99):29], ' . 'responder004' . ' rolled [(99):50].'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'I think you\'ve got this one'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Good game!'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => '[i]Continued from [game=' . $oldGameId . '][i]'));

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 (game 2) - player 1 passes

        // (99)  vs  (99)
        $this->verify_api_submitTurn(
            array(),
            'responder003' . ' passed. ',
            $retval, array(),
            $gameId, 1, 'Pass', 0, 1, '');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' passed'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 (game 2) - player 1 submits chat

        $retval = $this->verify_api_success(array(
            'type' => 'submitChat',
            'game' => $gameId,
            'chat' => 'There was something i meant to say',
        ));
        $this->assertEquals('Added game message', $retval['message']);
        $this->assertEquals(TRUE, $retval['data']);

        // expected changes as a result
        $expData['gameChatEditable'] = 'TIMESTAMP';
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'There was something i meant to say'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 (game 2) - player 1 updates chat

        $retval = $this->verify_api_success(array(
            'type' => 'submitChat',
            'game' => $gameId,
            'edit' => $retval['gameChatEditable'],
            'chat' => '...but i forgot what it was',
        ));
        $this->assertEquals('Updated previous game message', $retval['message']);
        $this->assertEquals(TRUE, $retval['data']);

        // expected changes as a result
        $expData['gameChatLog'][0]['message'] = '...but i forgot what it was';

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 (game 2) - player 1 deletes chat

        $retval = $this->verify_api_success(array(
            'type' => 'submitChat',
            'game' => $gameId,
            'edit' => $retval['gameChatEditable'],
            'chat' => '',
        ));
        $this->assertEquals('Deleted previous game message', $retval['message']);
        $this->assertEquals(TRUE, $retval['data']);

        // expected changes as a result
        $expData['gameChatEditable'] = FALSE;
        array_shift($expData['gameChatLog']);

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 (game 2) - player 2 wins game without chatting

        // (99)  vs  (99)
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(11),
            'responder004' . ' performed Power attack using [(99):50] against [(99):29]; Defender (99) was captured; Attacker (99) rerolled 50 => 11. End of round: ' . 'responder004' . ' won round 1 (148.5 vs. 0). ',
            $retval, array(array(1, 0), array(0, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' performed Power attack using [(99):50] against [(99):29]; Defender (99) was captured; Attacker (99) rerolled 50 => 11'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'End of round: ' . 'responder004' . ' won round 1 (148.5 vs. 0)'));
        // chat from previous game is no longer included in a closed continuation game
        array_pop($expData['gameChatLog']);
        array_pop($expData['gameChatLog']);

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Creation of another continuation game

        // Both dice are initially rolled:
        // (99)  (99)
        $secondGameId = $gameId;
        $gameId = $this->verify_api_createGame(
            array(13, 64),
            'responder003', 'responder004', 'haruspex', 'haruspex', 1, 'this series is a nailbiter', $secondGameId);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 1, 'START_TURN');
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
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003' . ' won initiative for round 1. Initial die values: ' . 'responder003' . ' rolled [(99):13], ' . 'responder004' . ' rolled [(99):64].'));
        // This behavior may change depending on the resolution of #1170
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => '[i]Continued from [game=' . $oldGameId . '][i]'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => '[i]Continued from [game=' . $secondGameId . '][i]'));

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 (game 3) - player 1 passes

        // (99)  vs  (99)
        $this->verify_api_submitTurn(
            array(),
            'responder003' . ' passed. ',
            $retval, array(),
            $gameId, 1, 'Pass', 0, 1, 'Who will win?  The suspense is killing me!');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['gameChatEditable'] = 'TIMESTAMP';
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' passed'));
        array_unshift($expData['gameChatLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'Who will win?  The suspense is killing me!'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends test_request_savePlayerInfo
     *
     * This scenario tests ornery mood swing dice at the BMInterface level
     */
    public function test_interface_game_004() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $_SESSION = $this->mock_test_user_login('responder003');

        ////////////////////
        // initial game setup

        // No dice are initially rolled, since they're all swing dice
        $gameId = $this->verify_api_createGame(
            array(),
            'responder003', 'responder004', 'Skeeve', 'Skeeve', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Mood', 'Ornery'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('V' => array(6, 12), 'W' => array(4, 12), 'X' => array(4, 20), 'Y' => array(1, 20), 'Z' => array(4, 30));
        $expData['playerDataArray'][1]['swingRequestArray'] = array('V' => array(6, 12), 'W' => array(4, 12), 'X' => array(4, 20), 'Y' => array(1, 20), 'Z' => array(4, 30));
        $expData['playerDataArray'][0]['button'] = array('name' => 'Skeeve', 'recipe' => 'o(V)? o(W)? o(X)? o(Y)? o(Z)?', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Skeeve', 'recipe' => 'o(V)? o(W)? o(X)? o(Y)? o(Z)?', 'artFilename' => 'BMdefaultRound.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(V)?', 'description' => 'Ornery V Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(W)?', 'description' => 'Ornery W Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(X)?', 'description' => 'Ornery X Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(Y)?', 'description' => 'Ornery Y Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(Z)?', 'description' => 'Ornery Z Mood Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(V)?', 'description' => 'Ornery V Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(W)?', 'description' => 'Ornery W Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(X)?', 'description' => 'Ornery X Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(Y)?', 'description' => 'Ornery Y Mood Swing Die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Ornery', 'Mood'), 'properties' => array(), 'recipe' => 'o(Z)?', 'description' => 'Ornery Z Mood Swing Die'),
        );

        // load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - player 1 submits die values

        // This needs 5 random values, for player 1's swing dice
        $this->verify_api_submitDieValues(
            array(2, 2, 4, 1, 4),
            $gameId, 1, array('V' => 6, 'W' => 4, 'X' => 4, 'Y' => 1, 'Z' => 4), NULL);

        // expected changes as a result of the attack
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] .= ' (with 6 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] .= ' (with 1 side)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] .= ' (with 4 sides)';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' set die sizes'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - player 2 submits die values

        // This needs 5 random values, for each of p2's swing dice
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(9, 4, 9, 8, 1),
            $gameId, 1, array('V' => 12, 'W' => 11, 'X' => 10, 'Y' => 9, 'Z' => 8), NULL);
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes as a result of the attack
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 9.5;
        $expData['playerDataArray'][1]['roundScore'] = 25;
        $expData['playerDataArray'][0]['sideScore'] = -10.3;
        $expData['playerDataArray'][1]['sideScore'] = 10.3;
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][4]['sides'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] .= ' (with 12 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] .= ' (with 11 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] .= ' (with 10 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] .= ' (with 9 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][4]['description'] .= ' (with 8 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 9;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 1;
        $expData['gameActionLog'][0]['message'] = 'responder003' . ' set swing values: V=6, W=4, X=4, Y=1, Z=4';
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' set swing values: V=12, W=11, X=10, Y=9, Z=8'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003' . ' won initiative for round 1. Initial die values: ' . 'responder003' . ' rolled [o(V=6)?:2, o(W=4)?:2, o(X=4)?:4, o(Y=1)?:1, o(Z=4)?:4], ' . 'responder004' .' rolled [o(V=12)?:9, o(W=11)?:4, o(X=10)?:9, o(Y=9)?:8, o(Z=8)?:1].'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - player 1 performs skill attack using 3 dice
        // o(X)? attacks, goes to 16 sides (idx 5) and value 10
        // o(Y)? attacks, goes to 6 sides (idx 3) and value 3
        // o(Z)? attacks, goes to 12 sides (idx 4) and value 10
        // o(V)? idle rerolls, goes to 12 sides (idx 3) and value 3
        // o(W)? idle rerolls, goes to 4 sides (idx 0) and value 2
        $this->verify_api_submitTurn(
            array(5, 10, 3, 3, 4, 10, 3, 3, 0, 2),
            'responder003' . ' performed Skill attack using [o(X=4)?:4,o(Y=1)?:1,o(Z=4)?:4] against [o(X=10)?:9]; Defender o(X=10)? was captured; Attacker o(X=4)? changed size from 4 to 16 sides, recipe changed from o(X=4)? to o(X=16)?, rerolled 4 => 10; Attacker o(Y=1)? changed size from 1 to 6 sides, recipe changed from o(Y=1)? to o(Y=6)?, rerolled 1 => 3; Attacker o(Z=4)? changed size from 4 to 12 sides, recipe changed from o(Z=4)? to o(Z=12)?, rerolled 4 => 10. ' . 'responder003' . '\'s idle ornery dice rerolled at end of turn: o(V=6)? changed size from 6 to 12 sides, recipe changed from o(V=6)? to o(V=12)?, rerolled 2 => 3; o(W=4)? remained the same size, rerolled 2 => 2. ',
            $retval, array(array(0, 2), array(0, 3), array(0, 4), array(1, 2)),
            $gameId, 1, 'Skill', 0, 1, '');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 35;
        $expData['playerDataArray'][1]['roundScore'] = 20;
        $expData['playerDataArray'][0]['sideScore'] = 10.0;
        $expData['playerDataArray'][1]['sideScore'] = -10.0;
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 16;
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = 'Ornery V Mood Swing Die (with 12 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = 'Ornery X Mood Swing Die (with 16 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = 'Ornery Y Mood Swing Die (with 6 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'Ornery Z Mood Swing Die (with 12 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array('HasJustRerolledOrnery');
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array('HasJustRerolledOrnery');
        $expData['playerDataArray'][0]['capturedDieArray'][] =
            array('value' => 9, 'sides' => '10', 'recipe' => 'o(X)?', 'properties' => array('WasJustCaptured'));
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 2, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' performed Skill attack using [o(X=4)?:4,o(Y=1)?:1,o(Z=4)?:4] against [o(X=10)?:9]; Defender o(X=10)? was captured; Attacker o(X=4)? changed size from 4 to 16 sides, recipe changed from o(X=4)? to o(X=16)?, rerolled 4 => 10; Attacker o(Y=1)? changed size from 1 to 6 sides, recipe changed from o(Y=1)? to o(Y=6)?, rerolled 1 => 3; Attacker o(Z=4)? changed size from 4 to 12 sides, recipe changed from o(Z=4)? to o(Z=12)?, rerolled 4 => 10'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . '\'s idle ornery dice rerolled at end of turn: o(V=6)? changed size from 6 to 12 sides, recipe changed from o(V=6)? to o(V=12)?, rerolled 2 => 3; o(W=4)? remained the same size, rerolled 2 => 2'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - player 2 performs skill attack using 2 dice
        // o(V)? attacks, goes to 10 sides (idx 2) and value 1
        // o(Z)? attacks, goes to 30 sides (idx 7) and value 18
        // o(W)? idle rerolls, goes to 8 sides (idx 2) and value 5
        // o(Y)? idle rerolls, goes to 12 sides (idx 6) and value 3
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(2, 1, 7, 18, 2, 5, 6, 3),
            'responder004' . ' performed Skill attack using [o(V=12)?:9,o(Z=8)?:1] against [o(X=16)?:10]; Defender o(X=16)? was captured; Attacker o(V=12)? changed size from 12 to 10 sides, recipe changed from o(V=12)? to o(V=10)?, rerolled 9 => 1; Attacker o(Z=8)? changed size from 8 to 30 sides, recipe changed from o(Z=8)? to o(Z=30)?, rerolled 1 => 18. ' . 'responder004' . '\'s idle ornery dice rerolled at end of turn: o(W=11)? changed size from 11 to 8 sides, recipe changed from o(W=11)? to o(W=8)?, rerolled 4 => 5; o(Y=9)? changed size from 9 to 12 sides, recipe changed from o(Y=9)? to o(Y=12)?, rerolled 8 => 3. ',
            $retval, array(array(1, 0), array(1, 3), array(0, 2)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 27;
        $expData['playerDataArray'][1]['roundScore'] = 46;
        $expData['playerDataArray'][0]['sideScore'] = -12.7;
        $expData['playerDataArray'][1]['sideScore'] = 12.7;
        $expData['playerDataArray'][0]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 10;
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][3]['sides'] = 30;
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = 'Ornery V Mood Swing Die (with 10 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = 'Ornery W Mood Swing Die (with 8 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = 'Ornery Y Mood Swing Die (with 12 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][3]['description'] = 'Ornery Z Mood Swing Die (with 30 sides)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 3;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 18;
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array('HasJustRerolledOrnery');
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array('HasJustRerolledOrnery');
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] =
            array('value' => 10, 'sides' => '16', 'recipe' => 'o(X)?', 'properties' => array('WasJustCaptured'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . ' performed Skill attack using [o(V=12)?:9,o(Z=8)?:1] against [o(X=16)?:10]; Defender o(X=16)? was captured; Attacker o(V=12)? changed size from 12 to 10 sides, recipe changed from o(V=12)? to o(V=10)?, rerolled 9 => 1; Attacker o(Z=8)? changed size from 8 to 30 sides, recipe changed from o(Z=8)? to o(Z=30)?, rerolled 1 => 18'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004' . '\'s idle ornery dice rerolled at end of turn: o(W=11)? changed size from 11 to 8 sides, recipe changed from o(W=11)? to o(W=8)?, rerolled 4 => 5; o(Y=9)? changed size from 9 to 12 sides, recipe changed from o(Y=9)? to o(Y=12)?, rerolled 8 => 3'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - player 1 performs skill attack using all 4 dice
        // o(V)? attacks, goes to 10 sides (idx 2) and value 2
        // o(W)? attacks, goes to 12 sides (idx 4) and value 11
        // o(Y)? attacks, goes to 10 sides (idx 5) and value 7
        // o(Z)? attacks, goes to 8 sides (idx 2) and value 7
        $this->verify_api_submitTurn(
            array(2, 2, 4, 11, 5, 7, 2, 7),
            'responder003' . ' performed Skill attack using [o(V=12)?:3,o(W=4)?:2,o(Y=6)?:3,o(Z=12)?:10] against [o(Z=30)?:18]; Defender o(Z=30)? was captured; Attacker o(V=12)? changed size from 12 to 10 sides, recipe changed from o(V=12)? to o(V=10)?, rerolled 3 => 2; Attacker o(W=4)? changed size from 4 to 12 sides, recipe changed from o(W=4)? to o(W=12)?, rerolled 2 => 11; Attacker o(Y=6)? changed size from 6 to 10 sides, recipe changed from o(Y=6)? to o(Y=10)?, rerolled 3 => 7; Attacker o(Z=12)? changed size from 12 to 8 sides, recipe changed from o(Z=12)? to o(Z=8)?, rerolled 10 => 7. ',
            $retval, array(array(0, 0), array(0, 1), array(0, 2), array(0, 3), array(1, 3)),
            $gameId, 1, 'Skill', 0, 1, '');

        // expected changes as a result of the attack
        $expData['activePlayerIdx'] = 1;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 60;
        $expData['playerDataArray'][1]['roundScore'] = 31;
        $expData['playerDataArray'][0]['sideScore'] = 19.3;
        $expData['playerDataArray'][1]['sideScore'] = -19.3;
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][0]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][1]['sides'] = 12;
        $expData['playerDataArray'][0]['activeDieArray'][2]['sides'] = 10;
        $expData['playerDataArray'][0]['activeDieArray'][3]['sides'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][0]['description'] = 'Ornery V Mood Swing Die (with 10 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][1]['description'] = 'Ornery W Mood Swing Die (with 12 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][2]['description'] = 'Ornery Y Mood Swing Die (with 10 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][3]['description'] = 'Ornery Z Mood Swing Die (with 8 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 7;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 3, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] =
            array('value' => 18, 'sides' => '30', 'recipe' => 'o(Z)?', 'properties' => array('WasJustCaptured'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003' . ' performed Skill attack using [o(V=12)?:3,o(W=4)?:2,o(Y=6)?:3,o(Z=12)?:10] against [o(Z=30)?:18]; Defender o(Z=30)? was captured; Attacker o(V=12)? changed size from 12 to 10 sides, recipe changed from o(V=12)? to o(V=10)?, rerolled 3 => 2; Attacker o(W=4)? changed size from 4 to 12 sides, recipe changed from o(W=4)? to o(W=12)?, rerolled 2 => 11; Attacker o(Y=6)? changed size from 6 to 10 sides, recipe changed from o(Y=6)? to o(Y=10)?, rerolled 3 => 7; Attacker o(Z=12)? changed size from 12 to 8 sides, recipe changed from o(Z=12)? to o(Z=8)?, rerolled 10 => 7'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends test_request_savePlayerInfo
     *
     * This scenario reproduces the option/swing setting bug in #1224
     * 0. Start a game with responder003 playing Mau and responder004 playing Wiseman
     * 1. responder003 set swing values: X=4
     *    responder003 won initiative for round 1. Initial die values: responder003 rolled [(6):3, (6):6, (8):7, (12):2, m(X=4):4], responder004 rolled [(20):11, (20):5, (20):8, (20):6].
     * 2. responder003 performed Skill attack using [(8):7,m(X=4):4] against [(20):11]; Defender (20) was captured; Attacker (8) rerolled 7 => 4; Attacker m(X=4) changed size from 4 to 20 sides, recipe changed from m(X=4) to m(20), rerolled 4 => 8
     * 3. responder004 performed Power attack using [(20):8] against [(12):2]; Defender (12) was captured; Attacker (20) rerolled 8 => 6
     * 4. responder003 performed Power attack using [(6):6] against [(20):6]; Defender (20) was captured; Attacker (6) rerolled 6 => 6
     * 5. responder004 performed Power attack using [(20):5] against [(6):3]; Defender (6) was captured; Attacker (20) rerolled 5 => 12
     * 6. responder003 performed Skill attack using [(8):4,m(20):8] against [(20):12]; Defender (20) was captured; Attacker (8) rerolled 4 => 3; Attacker m(20) rerolled 8 => 11
     * 7. responder004 performed Power attack using [(20):6] against [(8):3]; Defender (8) was captured; Attacker (20) rerolled 6 => 5
     * 8. responder003 performed Power attack using [(6):6] against [(20):5]; Defender (20) was captured; Attacker (6) rerolled 6 => 5
     *    End of round: responder003 won round 1 (93 vs. 26)
     * At this point, responder003 is incorrectly prompted to set swing dice
     */
    public function test_interface_game_005() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup

        // 4 of Mau's dice, and 4 of Wiseman's, are initially rolled
        $gameId = $this->verify_api_createGame(
            array(3, 6, 7, 2, 11, 5, 8, 6),
            'responder003', 'responder004', 'Mau', 'Wiseman', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Morphing'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'Mau', 'recipe' => '(6) (6) (8) (12) m(X)', 'artFilename' => 'mau.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Wiseman', 'recipe' => '(20) (20) (20) (20)', 'artFilename' => 'wiseman.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(X)', 'description' => 'Morphing X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => NULL, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - p1 sets swing values
        $this->verify_api_submitDieValues(
            array(4),
            $gameId, 1, array('X' => 4), NULL);

        // expected changes
        // note, since morphing dice are in play, canStillWin should remain null all game
        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'Morphing X Swing Die (with 4 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][0]['roundScore'] = 18;
        $expData['playerDataArray'][0]['sideScore'] = -14.7;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][1]['sideScore'] = 14.7;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 11;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 6;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(6):3, (6):6, (8):7, (12):2, m(X=4):4], responder004 rolled [(20):11, (20):5, (20):8, (20):6].'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 performed Skill attack using [(8):7,m(X=4):4] against [(20):11]
        //   [(6):3, (6):6, (8):7, (12):2, m(X=4):4] => [(20):11, (20):5, (20):8, (20):6]
        // Need an extra unused roll value because morphing die rolls twice (i consider this a bug, but it's an open question)
        $this->verify_api_submitTurn(
            array(4, 15, 8),
            'responder003 performed Skill attack using [(8):7,m(X=4):4] against [(20):11]; Defender (20) was captured; Attacker (8) rerolled 7 => 4; Attacker m(X=4) changed size from 4 to 20 sides, recipe changed from m(X=4) to m(20), rerolled 4 => 8. ',
            $retval, array(array(0, 2), array(0, 4), array(1, 0)),
            $gameId, 1, 'Skill', 0, 1, '');

	// This change is not per se the bug in #1224, but it's a
	// symptom showing that this is when the problem happens -
	// p1's swing die request array goes away
        $expData['playerDataArray'][0]['swingRequestArray'] = array();

        // expected changes
        $expData['activePlayerIdx'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 46;
        $expData['playerDataArray'][0]['sideScore'] = 10.7;
        $expData['playerDataArray'][1]['roundScore'] = 30;
        $expData['playerDataArray'][1]['sideScore'] = -10.7;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array('HasJustMorphed');
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 20;
        $expData['playerDataArray'][0]['activeDieArray'][4]['recipe'] = 'm(20)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] = 'Morphing 20-sided die';
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 8;
        $expData['playerDataArray'][0]['capturedDieArray'][] = array( 'value' => 11, 'sides' => 20, 'recipe' => '(20)', 'properties' => array('WasJustCaptured'));
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(8):7,m(X=4):4] against [(20):11]; Defender (20) was captured; Attacker (8) rerolled 7 => 4; Attacker m(X=4) changed size from 4 to 20 sides, recipe changed from m(X=4) to m(20), rerolled 4 => 8'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder004 performed Power attack using [(20):8] against [(12):2]
        //   [(6):3, (6):6, (8):4, (12):2, m(20):8] <= [(20):5, (20):8, (20):6]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(6),
            'responder004 performed Power attack using [(20):8] against [(12):2]; Defender (12) was captured; Attacker (20) rerolled 8 => 6. ',
            $retval, array(array(0, 3), array(1, 1)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['activePlayerIdx'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 40;
        $expData['playerDataArray'][0]['sideScore'] = -1.3;
        $expData['playerDataArray'][1]['roundScore'] = 42;
        $expData['playerDataArray'][1]['sideScore'] = 1.3;
        $expData['playerDataArray'][0]['activeDieArray'][4]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 6;
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][] = array( 'value' => 2, 'sides' => 12, 'recipe' => '(12)', 'properties' => array('WasJustCaptured'));
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 3, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):8] against [(12):2]; Defender (12) was captured; Attacker (20) rerolled 8 => 6'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder003 performed Power attack using [(6):6] against [(20):6]
        //   [(6):3, (6):6, (8):4, m(20):8] => [(20):5, (20):6, (20):6]
        $this->verify_api_submitTurn(
            array(6),
            'responder003 performed Power attack using [(6):6] against [(20):6]; Defender (20) was captured; Attacker (6) rerolled 6 => 6. ',
            $retval, array(array(0, 1), array(1, 2)),
            $gameId, 1, 'Power', 0, 1, '');

        array_splice($expData['playerDataArray'][1]['activeDieArray'], 2, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(6):6] against [(20):6]; Defender (20) was captured; Attacker (6) rerolled 6 => 6'));

        // no changes of interest
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 05 - responder004 performed Power attack using [(20):5] against [(6):3]
        //   [(6):3, (6):6, (8):4, m(20):8] <= [(20):5, (20):6]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(12),
            'responder004 performed Power attack using [(20):5] against [(6):3]; Defender (6) was captured; Attacker (20) rerolled 5 => 12. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        array_splice($expData['playerDataArray'][0]['activeDieArray'], 0, 1);
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):5] against [(6):3]; Defender (6) was captured; Attacker (20) rerolled 5 => 12'));

        // no changes of interest
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10, FALSE);


        ////////////////////
        // Move 06 - responder003 performed Skill attack using [(8):4,m(20):8] against [(20):12]
        // again, there's an extra die roll because of the morph
        //   [(6):6, (8):4, m(20):8] => [(20):12, (20):6]
        $this->verify_api_submitTurn(
            array(3, 1, 11),
            'responder003 performed Skill attack using [(8):4,m(20):8] against [(20):12]; Defender (20) was captured; Attacker (8) rerolled 4 => 3; Attacker m(20) rerolled 8 => 11. ',
            $retval, array(array(0, 1), array(0, 2), array(1, 0)),
            $gameId, 1, 'Skill', 0, 1, '');

        // expected changes from past several rounds
        $expData['activePlayerIdx'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 77;
        $expData['playerDataArray'][0]['sideScore'] = 32.7;
        $expData['playerDataArray'][1]['roundScore'] = 28;
        $expData['playerDataArray'][1]['sideScore'] = -32.7;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 3;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 11;
        $expData['playerDataArray'][0]['activeDieArray'][2]['properties'] = array('HasJustMorphed');
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array( 'value' => 6, 'sides' => 20, 'recipe' => '(20)', 'properties' => array() );
        $expData['playerDataArray'][0]['capturedDieArray'][] = array( 'value' => 12, 'sides' => 20, 'recipe' => '(20)', 'properties' => array('WasJustCaptured') );
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'][] = array( 'value' => 3, 'sides' => 6, 'recipe' => '(6)', 'properties' => array() );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(8):4,m(20):8] against [(20):12]; Defender (20) was captured; Attacker (8) rerolled 4 => 3; Attacker m(20) rerolled 8 => 11'));

        // check here to verify that HasJustMorphed gets set on the die, even though it stayed the same size
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 07 - responder004 performed Power attack using [(20):6] against [(8):3]
        //   [(6):6, (8):3, m(20):11] <= [(20):6]
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(5),
            'responder004 performed Power attack using [(20):6] against [(8):3]; Defender (8) was captured; Attacker (20) rerolled 6 => 5. ',
            $retval, array(array(0, 1), array(1, 0)),
            $gameId, 1, 'Power', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes from past several rounds
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power');
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 73;
        $expData['playerDataArray'][0]['sideScore'] = 24.7;
        $expData['playerDataArray'][1]['roundScore'] = 36;
        $expData['playerDataArray'][1]['sideScore'] = -24.7;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 1, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][2]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 5;
        $expData['playerDataArray'][1]['capturedDieArray'][] = array( 'value' => 3, 'sides' => 8, 'recipe' => '(8)', 'properties' => array('WasJustCaptured') );
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Power attack using [(20):6] against [(8):3]; Defender (8) was captured; Attacker (20) rerolled 6 => 5'));

        // check here to verify that HasJustMorphed gets set on the die, even though it stayed the same size
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 08 - responder003 performed Power attack using [(6):6] against [(20):5]
        //   [(6):6, m(20):11] => [(20):5]
        // need 1 for attacker's reroll, and should be 9 for next round
        $this->verify_api_submitTurn(
            array(5, 2, 3, 2, 9, 2, 11, 20, 7, 3),
            'responder003 performed Power attack using [(6):6] against [(20):5]; Defender (20) was captured; Attacker (6) rerolled 6 => 5. End of round: responder003 won round 1 (93 vs. 26). responder003 won initiative for round 2. Initial die values: responder003 rolled [(6):2, (6):3, (8):2, (12):9, m(X=4):2], responder004 rolled [(20):11, (20):20, (20):7, (20):3]. ',
            $retval, array(array(0, 0), array(1, 0)),
            $gameId, 1, 'Power', 0, 1, '');

        $expData['roundNumber'] = 2;
        $expData['playerDataArray'][0]['gameScoreArray']['W'] = 1;
        $expData['playerDataArray'][1]['gameScoreArray']['L'] = 1;
        $expData['gameState'] = 'START_TURN';
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['activePlayerIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 18;
        $expData['playerDataArray'][0]['sideScore'] = -14.7;
        $expData['playerDataArray'][1]['roundScore'] = 40;
        $expData['playerDataArray'][1]['sideScore'] = 14.7;
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        // all of these dice should have values
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 2, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => 3, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => 2, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => 9, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => 2, 'sides' => 4, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(X)', 'description' => 'Morphing X Swing Die (with 4 sides)'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 11, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' => 20, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' =>  7, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
            array('value' =>  3, 'sides' => 20, 'skills' => array(), 'properties' => array(), 'recipe' => '(20)', 'description' => '20-sided die'),
        );
        $expData['playerDataArray'][0]['capturedDieArray'] = array();
        $expData['playerDataArray'][1]['capturedDieArray'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Power attack using [(6):6] against [(20):5]; Defender (20) was captured; Attacker (6) rerolled 6 => 5'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'End of round: responder003 won round 1 (93 vs. 26)'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 2. Initial die values: responder003 rolled [(6):2, (6):3, (8):2, (12):9, m(X=4):2], responder004 rolled [(20):11, (20):20, (20):7, (20):3].'));

        // truncate log to 10 entries
        $expData['gameActionLog'] = array_slice($expData['gameActionLog'], 0, 10);

        // load and verify game attributes
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }

    /**
     * @depends test_request_savePlayerInfo
     *
     * This scenario reproduces the morphing die bug in #1306
     * 0. Start a game with responder003 playing Mau and responder004 playing Skomp
     * 1. responder003 set swing values: X=4
     *    responder003 won initiative for round 1. Initial die values: responder003 rolled [(6):1, (6):2, (8):8, (12):7, m(X=4):4], responder004 rolled [wm(1):1, wm(2):2, wm(4):1, m(8):8, m(10):8]. responder004 has dice which are not counted for initiative due to die skills: [wm(1), wm(2), wm(4)].
     * 2. responder003 performed Skill attack using [(6):1,(12):7] against [m(10):8]; Defender m(10) was captured; Attacker (6) rerolled 1 => 4; Attacker (12) rerolled 7 => 6
     * 3. responder004 performed Skill attack using [wm(1):1,wm(2):2,wm(4):1] against [(6):4]; Defender (6) was captured; Attacker wm(1) changed size from 1 to 6 sides, recipe changed from wm(1) to wm(6), rerolled 1 => 5; Attacker wm(2) changed size from 2 to 6 sides, recipe changed from wm(2) to wm(6), rerolled 2 => 2; Attacker wm(4) changed size from 4 to 6 sides, recipe changed from wm(4) to wm(6), rerolled 1 => 6
     * 4. responder003 performed Skill attack using [(6):2,(12):6] against [m(8):8]; Defender m(8) was captured; Attacker (6) rerolled 2 => 5; Attacker (12) rerolled 6 => 8
     * 5. responder004 performed Skill attack using [wm(6):2,wm(6):6] against [(12):8]; Defender (12) was captured; Attacker wm(6) changed size from 6 to 12 sides, recipe changed from wm(6) to wm(12), rerolled 2 => 8; Attacker wm(6) changed size from 6 to 12 sides, recipe changed from wm(6) to wm(12), rerolled 6 => 2
     * At this point, responder004 incorrectly has dice [wm(6):5,wm(12):2:,wm(6):4], where it should be [wm(12):8,wm(12):2,wm(6):4]
     */
    public function test_interface_game_006() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder004 as soon as you've done so
        $_SESSION = $this->mock_test_user_login('responder003');


        ////////////////////
        // initial game setup

        // 4 of Mau's dice, and 5 of Skomp's dice, are initially rolled
        $gameId = $this->verify_api_createGame(
            array(1, 2, 8, 7, 1, 2, 1, 8, 8),
            'responder003', 'responder004', 'Mau', 'Skomp', 3);

        // Initial expected game data object
        $expData = $this->generate_init_expected_data_array($gameId, 'responder003', 'responder004', 3, 'SPECIFY_DICE');
        $expData['gameSkillsInfo'] = $this->get_skill_info(array('Morphing', 'Slow'));
        $expData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['button'] = array('name' => 'Mau', 'recipe' => '(6) (6) (8) (12) m(X)', 'artFilename' => 'mau.png');
        $expData['playerDataArray'][1]['button'] = array('name' => 'Skomp', 'recipe' => 'wm(1) wm(2) wm(4) m(8) m(10)', 'artFilename' => 'skomp.png');
        $expData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array(), 'properties' => array(), 'recipe' => '(8)', 'description' => '8-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(X)', 'description' => 'Morphing X Swing Die'),
        );
        $expData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 1, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(1)', 'description' => 'Slow Morphing 1-sided die'),
            array('value' => NULL, 'sides' => 2, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(2)', 'description' => 'Slow Morphing 2-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array('Slow', 'Morphing'), 'properties' => array(), 'recipe' => 'wm(4)', 'description' => 'Slow Morphing 4-sided die'),
            array('value' => NULL, 'sides' => 8, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(8)', 'description' => 'Morphing 8-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array('Morphing'), 'properties' => array(), 'recipe' => 'm(10)', 'description' => 'Morphing 10-sided die'),
        );

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 01 - p1 sets swing values
        $this->verify_api_submitDieValues(
            array(4),
            $gameId, 1, array('X' => 4), NULL);

        // expected changes
        $expData['gameState'] = 'START_TURN';
        $expData['activePlayerIdx'] = 0;
        $expData['playerWithInitiativeIdx'] = 0;
        $expData['validAttackTypeArray'] = array('Power', 'Skill');
        $expData['playerDataArray'][0]['roundScore'] = 18;
        $expData['playerDataArray'][1]['roundScore'] = 12.5;
        $expData['playerDataArray'][0]['sideScore'] = 3.7;
        $expData['playerDataArray'][1]['sideScore'] = -3.7;
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][0]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 7;
        $expData['playerDataArray'][0]['activeDieArray'][4]['sides'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][4]['description'] .= ' (with 4 sides)';
        $expData['playerDataArray'][0]['activeDieArray'][4]['value'] = 4;
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 1;
        $expData['playerDataArray'][1]['activeDieArray'][3]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][4]['value'] = 8;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 set swing values: X=4'));
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(6):1, (6):2, (8):8, (12):7, m(X=4):4], responder004 rolled [wm(1):1, wm(2):2, wm(4):1, m(8):8, m(10):8]. responder004 has dice which are not counted for initiative due to die skills: [wm(1), wm(2), wm(4)].'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 02 - responder003 performed Skill attack using [(6):1,(12):7] against [m(10):8]
        // [(6):1, (6):2, (8):8, (12):7, m(X=4):4] => [wm(1):1, wm(2):2, wm(4):1, m(8):8, m(10):8]
        $this->verify_api_submitTurn(
            array(4, 6),
            'responder003 performed Skill attack using [(6):1,(12):7] against [m(10):8]; Defender m(10) was captured; Attacker (6) rerolled 1 => 4; Attacker (12) rerolled 7 => 6. ',
            $retval, array(array(0, 0), array(0, 3), array(1, 4)),
            $gameId, 1, 'Skill', 0, 1, '');

        // expected changes
        $expData['activePlayerIdx'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 28;
        $expData['playerDataArray'][1]['roundScore'] = 7.5;
        $expData['playerDataArray'][0]['sideScore'] = 13.7;
        $expData['playerDataArray'][1]['sideScore'] = -13.7;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 4, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 8, 'sides' => 10, 'properties' => array('WasJustCaptured'), 'recipe' => 'm(10)');
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 4;
        $expData['playerDataArray'][0]['activeDieArray'][3]['value'] = 6;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(6):1,(12):7] against [m(10):8]; Defender m(10) was captured; Attacker (6) rerolled 1 => 4; Attacker (12) rerolled 7 => 6'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 03 - responder004 performed Skill attack using [wm(1):1,wm(2):2,wm(4):1] against [(6):4]
        // [(6):4, (6):2, (8):8, (12):6, m(X=4):4] <= [wm(1):1, wm(2):2, wm(4):1, m(8):8]
        // since the dice are morphing, an extra roll is needed for each
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(1, 1, 1, 5, 2, 6),
            'responder004 performed Skill attack using [wm(1):1,wm(2):2,wm(4):1] against [(6):4]; Defender (6) was captured; Attacker wm(1) changed size from 1 to 6 sides, recipe changed from wm(1) to wm(6), rerolled 1 => 5; Attacker wm(2) changed size from 2 to 6 sides, recipe changed from wm(2) to wm(6), rerolled 2 => 2; Attacker wm(4) changed size from 4 to 6 sides, recipe changed from wm(4) to wm(6), rerolled 1 => 6. ',
            $retval, array(array(0, 0), array(1, 0), array(1, 1), array(1, 2)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['activePlayerIdx'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 25;
        $expData['playerDataArray'][1]['roundScore'] = 19;
        $expData['playerDataArray'][0]['sideScore'] = 4.0;
        $expData['playerDataArray'][1]['sideScore'] = -4.0;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 0, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 4, 'sides' => 6, 'properties' => array('WasJustCaptured'), 'recipe' => '(6)');
        $expData['playerDataArray'][0]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][0]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][0]['recipe'] = 'wm(6)';
        $expData['playerDataArray'][1]['activeDieArray'][0]['description'] = 'Slow Morphing 6-sided die';
        $expData['playerDataArray'][1]['activeDieArray'][0]['properties'] = array('HasJustMorphed');
        $expData['playerDataArray'][1]['activeDieArray'][0]['value'] = 5;
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = 'wm(6)';
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = 'Slow Morphing 6-sided die';
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array('HasJustMorphed');
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 2;
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 6;
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = 'wm(6)';
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = 'Slow Morphing 6-sided die';
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array('HasJustMorphed');
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 6;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [wm(1):1,wm(2):2,wm(4):1] against [(6):4]; Defender (6) was captured; Attacker wm(1) changed size from 1 to 6 sides, recipe changed from wm(1) to wm(6), rerolled 1 => 5; Attacker wm(2) changed size from 2 to 6 sides, recipe changed from wm(2) to wm(6), rerolled 2 => 2; Attacker wm(4) changed size from 4 to 6 sides, recipe changed from wm(4) to wm(6), rerolled 1 => 6'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 04 - responder003 performed Skill attack using [(6):2,(12):6] against [m(8):8]
        // [(6):2, (8):8, (12):6, m(X=4):4] => [wm(6):5, wm(6):2, wm(6):6, m(8):8]
        $this->verify_api_submitTurn(
            array(5, 8),
            'responder003 performed Skill attack using [(6):2,(12):6] against [m(8):8]; Defender m(8) was captured; Attacker (6) rerolled 2 => 5; Attacker (12) rerolled 6 => 8. ',
            $retval, array(array(0, 0), array(0, 2), array(1, 3)),
            $gameId, 1, 'Skill', 0, 1, '');

        // expected changes
        $expData['activePlayerIdx'] = 1;
        $expData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][1]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][0]['roundScore'] = 33;
        $expData['playerDataArray'][1]['roundScore'] = 15;
        $expData['playerDataArray'][0]['sideScore'] = 12.0;
        $expData['playerDataArray'][1]['sideScore'] = -12.0;
        array_splice($expData['playerDataArray'][1]['activeDieArray'], 3, 1);
        $expData['playerDataArray'][0]['capturedDieArray'][] = array('value' => 8, 'sides' => 8, 'properties' => array('WasJustCaptured'), 'recipe' => 'm(8)');
        $expData['playerDataArray'][1]['capturedDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][0]['activeDieArray'][0]['value'] = 5;
        $expData['playerDataArray'][0]['activeDieArray'][2]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][0]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array();
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder003', 'message' => 'responder003 performed Skill attack using [(6):2,(12):6] against [m(8):8]; Defender m(8) was captured; Attacker (6) rerolled 2 => 5; Attacker (12) rerolled 6 => 8'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);


        ////////////////////
        // Move 05 - responder004 performed Skill attack using [wm(6):2,wm(6):6] against [(12):8]
        // [(6):5, (8):8, (12):8, m(X=4):4] <= [wm(6):5, wm(6):2, wm(6):6]
        // Bug #1306 is triggered when the intermediate rolls of the two dice with the same recipe are identical
        // If you change the 5, 5 in the array below to two non-identical numbers, the bug won't be triggered
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(5, 5, 8, 2),
            'responder004 performed Skill attack using [wm(6):2,wm(6):6] against [(12):8]; Defender (12) was captured; Attacker wm(6) changed size from 6 to 12 sides, recipe changed from wm(6) to wm(12), rerolled 2 => 8; Attacker wm(6) changed size from 6 to 12 sides, recipe changed from wm(6) to wm(12), rerolled 6 => 2. ',
            $retval, array(array(0, 2), array(1, 1), array(1, 2)),
            $gameId, 1, 'Skill', 1, 0, '');
        $_SESSION = $this->mock_test_user_login('responder003');

        // expected changes
        $expData['activePlayerIdx'] = 0;
        $expData['playerDataArray'][0]['waitingOnAction'] = TRUE;
        $expData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $expData['playerDataArray'][0]['roundScore'] = 27;
        $expData['playerDataArray'][1]['roundScore'] = 33;
        $expData['playerDataArray'][0]['sideScore'] = -4.0;
        $expData['playerDataArray'][1]['sideScore'] = 4.0;
        array_splice($expData['playerDataArray'][0]['activeDieArray'], 2, 1);
        $expData['playerDataArray'][1]['capturedDieArray'][] = array('value' => 8, 'sides' => 12, 'properties' => array('WasJustCaptured'), 'recipe' => '(12)');
        $expData['playerDataArray'][0]['capturedDieArray'][1]['properties'] = array();
        $expData['playerDataArray'][1]['activeDieArray'][1]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][1]['recipe'] = 'wm(12)';
        $expData['playerDataArray'][1]['activeDieArray'][1]['description'] = 'Slow Morphing 12-sided die';
        $expData['playerDataArray'][1]['activeDieArray'][1]['properties'] = array('HasJustMorphed');
        $expData['playerDataArray'][1]['activeDieArray'][1]['value'] = 8;
        $expData['playerDataArray'][1]['activeDieArray'][2]['sides'] = 12;
        $expData['playerDataArray'][1]['activeDieArray'][2]['recipe'] = 'wm(12)';
        $expData['playerDataArray'][1]['activeDieArray'][2]['description'] = 'Slow Morphing 12-sided die';
        $expData['playerDataArray'][1]['activeDieArray'][2]['properties'] = array('HasJustMorphed');
        $expData['playerDataArray'][1]['activeDieArray'][2]['value'] = 2;
        array_unshift($expData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => 'responder004', 'message' => 'responder004 performed Skill attack using [wm(6):2,wm(6):6] against [(12):8]; Defender (12) was captured; Attacker wm(6) changed size from 6 to 12 sides, recipe changed from wm(6) to wm(12), rerolled 2 => 8; Attacker wm(6) changed size from 6 to 12 sides, recipe changed from wm(6) to wm(12), rerolled 6 => 2'));

        // now load the game and check its state
        $retval = $this->verify_api_loadGameData($expData, $gameId, 10);
    }
}
