<?php

// Mock auth_session_exists() for unit test use
$dummyUserLoggedIn = FALSE;
function auth_session_exists() {
    global $dummyUserLoggedIn;
    return $dummyUserLoggedIn;
}

class responderTest extends PHPUnit_Framework_TestCase {

    /**
     * @var object  responder object which will be tested
     * @var dummy   dummy_responder object used to check the live responder
     */
    protected $object;
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
        $spec = new ApiSpec();
        $this->object = new ApiResponder($spec, True);

        if (file_exists('../src/api/DummyApiResponder.php')) {
            require_once '../src/api/DummyApiResponder.php';
        } else {
            require_once 'src/api/DummyApiResponder.php';
        }
        $this->dummy = new DummyApiResponder($spec, True);

        // Cache user IDs parsed from the DB for use within a test
        $this->user_ids = array();
    }

    /**
     * Check two PHP arrays to see if their structures match to a depth of one level:
     * * Do the arrays have the same sets of keys?
     * * Does each key have the same type of value for each array?
     */
    protected function object_structures_match($obja, $objb, $inspect_child_arrays=False) {
        foreach ($obja as $akey => $avalue) {
            if (!(array_key_exists($akey, $objb))) {
                return False;
            }
            if (gettype($obja[$akey]) != gettype($objb[$akey])) {
                return False;
            }
            if (($inspect_child_arrays) and (gettype($obja[$akey]) == 'array')) {
                if ((array_key_exists(0, $obja[$akey])) || (array_key_exists(0, $objb[$akey]))) {
                    if (gettype($obja[$akey][0]) != gettype($objb[$akey][0])) {
                        return False;
                    }
                }
            }
        }
        foreach ($objb as $bkey => $bvalue) {
            if (!(array_key_exists($bkey, $obja))) {
                return False;
            }
        }
        return True;
    }

    /**
     * Make sure users responder001-004 exist, and get
     * fake session data for responder003.
     */
    protected function mock_test_user_login() {

        // make sure responder001 and responder002 exist
        $args = array('type' => 'createUser',
                      'username' => 'responder001',
                      'password' => 't',
                      'email' => 'responder001@example.com');
        $this->object->process_request($args);
        $args['username'] = 'responder002';
        $args['email'] = 'responder002@example.com';
        $this->object->process_request($args);

        // now make sure responder003 exists and get the ID
        if (!(array_key_exists('responder003', $this->user_ids))) {
            $args['username'] = 'responder003';
            $args['email'] = 'responder003@example.com';
            $ret1 = $this->object->process_request($args);
            if ($ret1['data']) {
                $ret1 = $this->object->process_request($args);
            }
            $matches = array();
            preg_match('/id=(\d+)/', $ret1['message'], $matches);
            $this->user_ids['responder003'] = (int)$matches[1];
        }

        // now make sure responder004 exists and get the ID
        if (!(array_key_exists('responder004', $this->user_ids))) {
            $args['username'] = 'responder004';
            $args['email'] = 'responder004@example.com';
            $ret1 = $this->object->process_request($args);
            if ($ret1['data']) {
                $ret1 = $this->object->process_request($args);
            }
            $matches = array();
            preg_match('/id=(\d+)/', $ret1['message'], $matches);
            $this->user_ids['responder004'] = (int)$matches[1];
        }

        // now set dummy "logged in" variable and return $_SESSION variable style data for responder003
        global $dummyUserLoggedIn;
        $dummyUserLoggedIn = TRUE;
        return array('user_name' => 'responder003', 'user_id' => $this->user_ids['responder003']);
    }

    protected function verify_login_required($type) {
        $args = array('type' => $type);
        $retval = $this->object->process_request($args);
        $expected = array(
            'data' => NULL,
            'message' => "You need to login before calling API function $type",
            'status' => 'failed',
        );
        $this->assertEquals($expected, $retval,
                            "failed when invoking $type while not logged in");
    }

    protected function verify_invalid_arg_rejected($type) {
        $args = array('type' => $type, 'foobar' => 'foobar');
        $retval = $this->object->process_request($args);
        $expected = array(
            'data' => NULL,
            'message' => "Unexpected argument provided to function $type",
            'status' => 'failed',
        );
        $this->assertEquals($expected, $retval,
                            "failed when invoking $type with an unexpected argument");
    }

    protected function verify_mandatory_args_required($type, $required_args) {
        foreach (array_keys($required_args) as $missing) {
            $args = array('type' => $type);
            foreach ($required_args as $notmissing => $value) {
                if ($missing != $notmissing) {
                    $args[$notmissing] = $value;
                }
            }
            $retval = $this->object->process_request($args);
            $expected = array(
                'data' => NULL,
                'message' => "Missing mandatory argument $missing for function $type",
                'status' => 'failed',
            );
            $this->assertEquals($expected, $retval,
                                "failed when invoking $type without argument $missing");
        }
    }

    public function test_request_invalid() {
        $args = array('type' => 'foobar');
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $expected = array(
          'data' => NULL,
          'message' => 'Specified API function does not exist',
          'status' => 'failed',
        );
        $this->assertEquals($expected, $retval,
                            "return failure when invoked with a nonexistent request");

	// This test result should hold for all functions, since
	// the structure of the top-level response doesn't depend
	// on the function
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
            $real_new = $this->object->process_request(
                            array('type' => 'createUser',
                                  'username' => $username,
                                  'password' => 't',
                                  'email' => $username . '@example.com'));
            if ($real_new['status'] == 'ok') {
                $created_real = True;
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
        $retval = $this->object->process_request($args);
        $this->assertEquals(
            array(
                'data' => NULL,
                'message' => 'Game create failed because a button name was not valid.',
                'status' => 'failed',
            ),
            $retval,
            "Button name containing a backtick should be invalid"
        );

        $args = array(
            'type' => 'createGame',
            'playerInfoArray' => array(array('responder003', 'Avis'),
                                       array('responder004', 'Avis')),
            'maxWins' => '3',
        );
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], 'Game creation should succeed');

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata),
            "Real and dummy game creation return values should have matching structures");
    }

    public function test_request_loadActiveGames() {
        $this->verify_login_required('loadActiveGames');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadActiveGames');

        // make sure there's at least one game
        $args = array(
            'type' => 'createGame',
            'playerNameArray' => array('responder003', 'responder004'),
            'buttonNameArray' => array('Hammer', 'Stark'),
            'maxWins' => '3',
        );
        $this->object->process_request($args);

        $args = array('type' => 'loadActiveGames');
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $retval['status'], 'Loading games should succeed');

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
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $retval['status'], 'Loading completed games should succeed');
        $this->assertEquals('ok', $dummyval['status'], 'Dummy load of completed games should succeed');
    }

    public function test_request_loadNextPendingGame() {
        $this->verify_login_required('loadNextPendingGame');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadNextPendingGame');

        // loadGameData should fail if currentGameId is non-numeric
        $retval = $this->object->process_request(
            array('type' => 'loadNextPendingGame', 'currentGameId' => 'foobar'));
        $this->assertEquals(
            array(
                'data' => NULL,
                'message' => 'Argument (currentGameId) to function loadNextPendingGame is invalid',
                'status' => 'failed',
            ),
            $retval,
            "loadNextPendingGame should reject a non-numeric current game ID"
        );

        $args = array('type' => 'loadNextPendingGame');
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $retval['status'],
            'Loading next pending game ID should succeed');
        $this->assertEquals('ok', $dummyval['status'],
            'Dummy load of next pending game ID should succeed');

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($retdata, $dummydata, TRUE),
            "Real and dummy pending game data should have matching structures");

        $args['currentGameId'] = 7;
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $retval['status'],
            'Loading next pending game ID while skipping current game should succeed');
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
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $retval['status'], "responder should succeed");
        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata, True),
            "Real and dummy player names should have matching structures");
    }

    public function test_request_loadButtonNames() {
        $this->verify_login_required('loadButtonNames');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadButtonNames');

        $args = array('type' => 'loadButtonNames');
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], "responder should succeed");
        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($retdata, $dummydata, True),
            "Real and dummy button lists should have matching structures");

	// Each button in the dummy data should exactly match a
	// button in the live data
        foreach ($dummydata['buttonNameArray'] as $dummyidx => $dbuttonname) {
            $foundButton = False;
            foreach ($retdata['buttonNameArray'] as $retidx => $rbuttonname) {
                if ("$dbuttonname" === "$rbuttonname") {
                    $foundButton = True;
                    $this->assertEquals(
                        array("buttonName"            => $dummydata['buttonNameArray'][$dummyidx],
                              "recipe"                => $dummydata['recipeArray'][$dummyidx],
                              "hasUnimplementedSkill" => $dummydata['hasUnimplementedSkillArray'][$dummyidx]),
                        array("buttonName"            => $retdata['buttonNameArray'][$retidx],
                              "recipe"                => $retdata['recipeArray'][$retidx],
                              "hasUnimplementedSkill" => $retdata['hasUnimplementedSkillArray'][$retidx]),
                        "Dummy and live information about button $dbuttonname match exactly");
                }
            }
            $this->assertTrue($foundButton, "Dummy button $dbuttonname was found in live data");
        }
    }

    public function test_request_loadGameData() {
        $this->verify_login_required('loadGameData');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadGameData');

        // loadGameData should fail if game or logEntryLimit is non-numeric
        $retval = $this->object->process_request(
            array('type' => 'loadGameData', 'game' => 'foobar'));
        $this->assertEquals(
            array(
                'data' => NULL,
                'message' => 'Argument (game) to function loadGameData is invalid',
                'status' => 'failed',
            ),
            $retval,
            "loadGameData should reject a non-numeric game ID"
        );
        $retval = $this->object->process_request(
            array('type' => 'loadGameData', 'game' => '3', 'logEntryLimit' => 'foobar'));
        $this->assertEquals(
            array(
                'data' => NULL,
                'message' => 'Argument (logEntryLimit) to function loadGameData is invalid',
                'status' => 'failed',
            ),
            $retval,
            "loadGameData should reject a non-numeric log entry limit"
        );

        // create a game so we have the ID to load
        $args = array(
            'type' => 'createGame',
            'playerInfoArray' => array(array('responder003', 'Avis'),
                                       array('responder004', 'Avis')),
            'maxWins' => '3',
        );
        $retval = $this->object->process_request($args);
        $real_game_id = $retval['data']['gameId'];
        $dummy_game_id = '1';

        // now load real and dummy games
        $retval = $this->object->process_request(
            array('type' => 'loadGameData', 'game' => $real_game_id, 'logEntryLimit' => 10));
        $dummyval = $this->dummy->process_request(
            array('type' => 'loadGameData', 'game' => $dummy_game_id, 'logEntryLimit' => 10));
        $this->assertEquals('ok', $retval['status'], "responder should succeed");
        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata, True),
            "Real and dummy button lists should have matching structures");

	// Now hand-modify a few things we know will be different
	// and check that the data structures are entirely identical otherwise
        $dummydata['gameData']['data']['gameId'] = $retdata['gameData']['data']['gameId'];
        $dummydata['gameData']['data']['playerIdArray'] = $retdata['gameData']['data']['playerIdArray'];
        $dummydata['playerNameArray'] = $retdata['playerNameArray'];
        $dummydata['timestamp'] = $retdata['timestamp'];
        $dummydata['gameData']['data']['lastActionTimeArray'] =
            $retdata['gameData']['data']['lastActionTimeArray'];

        $this->assertEquals($dummydata, $retdata);
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
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $retval['status'], "responder should succeed");
        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata, True),
            "Real and dummy player data should have matching structures");
    }

    public function test_request_savePlayerInfo() {
        $this->verify_login_required('savePlayerInfo');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('savePlayerInfo');

        $args = array(
            'type' => 'savePlayerInfo',
            'name_irl' => 'Test User',
            'dob_month' => '2',
            'dob_day' => '29',
            'comment' => '',
            'autopass' => 'True',
        );
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $retval['status'], "responder should succeed");
        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($dummydata, $retdata),
            "Real and dummy player data update return values should have matching structures");
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
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $retval['status'], "responder should succeed");
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
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $retval['status'], "responder should succeed");
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
        $args = array(
            'type' => 'createGame',
            'playerInfoArray' => array(array('responder003', 'Avis'),
                                       array('responder004', 'Avis')),
            'maxWins' => '3',
        );
        $retval = $this->object->process_request($args);
        $real_game_id = $retval['data']['gameId'];
        $dummy_game_id = '1';

        // now ask for the game data so we have the timestamp to return
        $args = array(
            'type' => 'loadGameData',
            'game' => "$real_game_id",
            'logEntryLimit' => '10');
        $retval = $this->object->process_request($args);
        $timestamp = $retval['data']['timestamp'];

        // now submit the swing values
        $args = array(
            'type' => 'submitDieValues',
            'roundNumber' => '1',
            'timestamp' => $timestamp,
            'swingValueArray' => array('X' => '7'));
        $args['game'] = $real_game_id;
        $retval = $this->object->process_request($args);
        $args['game'] = $dummy_game_id;
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], "responder should succeed");
        $this->assertEquals($dummyval, $retval, "swing value submission responses should be identical");

        ///// Now test setting option values
        // create a game so we have the ID to load
        $args = array(
            'type' => 'createGame',
            'playerInfoArray' => array(array('responder003', 'Apples'),
                                       array('responder004', 'Apples')),
            'maxWins' => '3',
        );
        $retval = $this->object->process_request($args);
        $real_game_id = $retval['data']['gameId'];
        $dummy_game_id = '19';

        // now ask for the game data so we have the timestamp to return
        $args = array(
            'type' => 'loadGameData',
            'game' => "$real_game_id");
        $retval = $this->object->process_request($args);
        $timestamp = $retval['data']['timestamp'];

        // now submit the option values
        $args = array(
            'type' => 'submitDieValues',
            'roundNumber' => '1',
            'timestamp' => $timestamp,
            'optionValueArray' => array(2 => 12, 3 => 8, 4 => 20));
        $args['game'] = $real_game_id;

        $retval = $this->object->process_request($args);

        $args['game'] = $dummy_game_id;

        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $retval['status'], "responder should succeed");
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
        $args = array(
            'type' => 'createGame',
            'playerInfoArray' => array(array('responder003', 'Crab'),
                                       array('responder004', 'Crab')),
            'maxWins' => '3',
        );

        $maxtries = 50;
        $thistry = 0;
        $real_game_id = NULL;
        while (!($real_game_id) && ($thistry < $maxtries)) {
            $retval = $this->object->process_request($args);
            $real_game_id = $retval['data']['gameId'];

            // now ask for the game data so we have the timestamp to return
            $dataargs = array(
                'type' => 'loadGameData',
                'game' => "$real_game_id",
                'logEntryLimit' => '10');
            $retval = $this->object->process_request($dataargs);
            $timestamp = $retval['data']['timestamp'];

            if (($retval['data']['gameData']['data']['gameState'] != "REACT_TO_INITIATIVE") ||
                ($retval['data']['gameData']['data']['playerWithInitiativeIdx'] != 1)) {
                $real_game_id = NULL;
                $thistry++;
            }
        }
        $this->assertTrue($thistry < $maxtries,
            "Tried $maxtries times to create a game where the active player could use focus dice, and failed.  Unlucky, or is something wrong?");

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
        $retval = $this->object->process_request($args);
        $args['game'] = $dummy_game_id;
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals($dummyval, $retval, "swing value submission responses should be identical");
        $this->assertEquals('ok', $retval['status'], "responder should succeed");
    }

    public function test_request_submitTurn() {
        $this->verify_login_required('submitTurn');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('submitTurn');

        $this->markTestIncomplete("No test for submitTurn responder yet");
    }

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
}
