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
    protected function mock_test_user_login($username = 'responder003') {

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
        return array('user_name' => $username, 'user_id' => $this->user_ids[$username]);
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

        // Make sure that the first player in a game is the current logged in player
        $args = array(
            'type' => 'createGame',
            'playerInfoArray' => array(array('responder001', 'Avis'),
                                       array('responder004', 'Avis')),
            'maxWins' => '3',
        );
        $retval = $this->object->process_request($args);
        $this->assertEquals(
            array(
                'data' => NULL,
                'message' => 'Game create failed because you must be the first player.',
                'status' => 'failed',
            ),
            $retval,
            "You cannot create games between other players."
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

    public function test_request_searchGameHistory() {
        $this->verify_login_required('searchGameHistory');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('searchGameHistory');

        // make sure there's at least one game
        $args = array(
            'type' => 'createGame',
            'playerNameArray' => array('responder003', 'responder004'),
            'buttonNameArray' => array('Hammer', 'Stark'),
            'maxWins' => '3',
        );
        $this->object->process_request($args);

        $args = array(
            'type' => 'searchGameHistory',
            'sortColumn' => 'lastMove',
            'sortDirection' => 'DESC',
            'numberOfResults' => '20',
            'page' => '1',
            'buttonNameA' => 'Avis');
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals('ok', $retval['status'], 'Loading games should succeed');

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
        $retval = $this->object->process_request($args);
        $this->assertEquals(
            array(
                'data' => NULL,
                'message' => 'Argument (buttonName) to function joinOpenGame is invalid',
                'status' => 'failed',
            ),
            $retval,
            "Button name containing a backtick should be rejected"
        );

        $_SESSION = $this->mock_test_user_login('responder004');
        $createGameArgs = array(
            'type' => 'createGame',
            'playerInfoArray' => array(
                array('responder004', 'Avis'),
                array('', 'Avis')
            ),
            'maxWins' => '3',
        );
        $createGameResult = $this->object->process_request($createGameArgs);
        $gameId = $createGameResult['data']['gameId'];

        $_SESSION = $this->mock_test_user_login('responder003');
        $args = array(
            'type' => 'joinOpenGame',
            'gameId' => $gameId,
        );
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], $retval['message']);

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];

        $this->assertEquals($retdata, $dummydata,
            "Real and dummy game joining return values should both be true");
    }

    public function test_request_loadOpenGames() {
        $this->verify_login_required('loadOpenGames');

        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_invalid_arg_rejected('loadOpenGames');

        $createGameArgs = array(
            'type' => 'createGame',
            'playerInfoArray' => array(
                array('responder004', 'Avis'),
                array('', 'Avis')
            ),
            'maxWins' => '3',
        );
        $createGameResult = $this->object->process_request($createGameArgs);
        $gameId = $createGameResult['data']['gameId'];

        $args = array(
            'type' => 'loadOpenGames',
        );
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], $retval['message']);

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

    public function test_request_loadButtonData() {
        $this->verify_login_required('loadButtonData');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadButtonData');

        $args = array('type' => 'loadButtonData');
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], "responder should succeed");
        $this->assertEquals('ok', $dummyval['status'], "dummy responder should succeed");

        $retdata = $retval['data'];
        $dummydata = $dummyval['data'];
        $this->assertTrue(
            $this->object_structures_match($retdata[0], $dummydata[0], True),
            "Real and dummy button lists should have matching structures");

        // Each button in the dummy data should exactly match a
        // button in the live data
        foreach ($dummydata as $dButton) {
            $foundButton = False;
            foreach ($retdata as $rButton) {
                if ($dButton['buttonName'] === $rButton['buttonName']) {
                    $foundButton = True;
                    $this->assertEquals(
                        $dButton,
                        $rButton,
                        'Dummy and live information about button ' . $dButton['buttonName'] . ' should match exactly'
                    );
                }
            }
            $this->assertTrue($foundButton, 'Dummy button ' . $dButton['buttonName'] . ' was found in live data');
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
        $retval = $this->object->process_request(
            array('type' => 'loadGameData', 'game' => $nonexistent_game_id, 'logEntryLimit' => 10));
        $this->assertEquals(
            array(
                'data' => NULL,
                'message' => 'Game ' . $nonexistent_game_id . ' does not exist.',
                'status' => 'failed',
            ),
            $retval,
            'loadGameData should reject a nonexistent game ID with a friendly message'
        );

        // create an open game so we have the ID to load
        $args = array(
            'type' => 'createGame',
            'playerInfoArray' => array(array('responder003', 'Avis'),
                                       array('', '')),
            'maxWins' => '3',
        );
        $retval = $this->object->process_request($args);
        $open_game_id = $retval['data']['gameId'];
        $this->assertTrue(is_int($open_game_id), "open game creation was successful");

        $retval = $this->object->process_request(
            array('type' => 'loadGameData', 'game' => $open_game_id, 'logEntryLimit' => 10));
        $this->assertEquals('ok', $retval['status'], "loadGameData on an open game should succeed");
    }

    public function test_request_countPendingGames() {
        $this->verify_login_required('countPendingGames');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('countPendingGames');

        $args = array('type' => 'countPendingGames');
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
            'is_email_public' => 'False',
            'dob_month' => '2',
            'dob_day' => '29',
            'gender' => '',
            'comment' => '',
            'homepage' => '',
            'autopass' => 'True',
            'uses_gravatar' => 'False',
            'player_color' => '#dd99dd',
            'opponent_color' => '#ddffdd',
            'neutral_color_a' => '#cccccc',
            'neutral_color_b' => '#dddddd',
            'monitor_redirects_to_game' => 'False',
            'monitor_redirects_to_forum' => 'False',
            'automatically_monitor' => 'False',
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

            if (($retval['data']['gameState'] != "REACT_TO_INITIATIVE") ||
                ($retval['data']['playerWithInitiativeIdx'] != 1)) {
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

    public function test_request_dismissGame() {
        $this->verify_login_required('dismissGame');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('dismissGame');

        $dummy_game_id = '5';

        // create and complete a game so we have the ID to dismiss
        $args = array(
            'type' => 'createGame',
            'playerInfoArray' => array(array('responder003', 'haruspex'),
                                       array('responder004', 'haruspex')),
            'maxWins' => '1',
        );
        $loggedInPlayerIdx = 0;

        $retval = $this->object->process_request($args);
        $real_game_id = $retval['data']['gameId'];

        $loadGameArgs = array(
            'type' => 'loadGameData',
            'game' => "$real_game_id",
            'logEntryLimit' => '10');

        $submitTurnArgs = array(
            'type' => 'submitTurn',
            'game' => $real_game_id,
            'roundNumber' => 1);

        // Load the game and see if we can interact with it
        $gameData = $this->object->process_request($loadGameArgs);

        // It should not take 50 turns to finish a one-round haruspex^2 match
        $maxTries = 50;
        $thisTry = 0;
        while ($gameData['data']['gameState'] != "END_GAME") {
            $thisTry++;
            if ($thisTry > $maxTries) {
                $this->fail("Failed to complete a haruspex^2 match");
            }
            if (!$gameData['data']['playerDataArray'][$loggedInPlayerIdx]['waitingOnAction']) {
                if ($loggedInPlayerIdx == 0) {
                    $_SESSION = $this->mock_test_user_login('responder004');
                    $loggedInPlayerIdx = 1;
                } else {
                    $_SESSION = $this->mock_test_user_login('responder003');
                    $loggedInPlayerIdx = 0;
                }
            } else {
                $submitTurnArgs['timestamp'] = $gameData['data']['timestamp'];
                $submitTurnArgs['attackerIdx'] = $loggedInPlayerIdx;
                $submitTurnArgs['defenderIdx'] = ($loggedInPlayerIdx + 1) % 2;
                if (in_array('Power', $gameData['data']['validAttackTypeArray'])) {
                    $submitTurnArgs['attackType'] = 'Power';
                    $submitTurnArgs['dieSelectStatus'] = array(
                        'playerIdx_0_dieIdx_0' => 'true',
                        'playerIdx_1_dieIdx_0' => 'true'
                    );
                } else {
                    $submitTurnArgs['attackType'] = 'Pass';
                    $submitTurnArgs['dieSelectStatus'] = array(
                        'playerIdx_0_dieIdx_0' => 'false',
                        'playerIdx_1_dieIdx_0' => 'false'
                    );
                }
                $turnResults = $this->object->process_request($submitTurnArgs);
            }
            $gameData = $this->object->process_request($loadGameArgs);
        }

        // now try to dismiss the game
        $args = array(
            'type' => 'dismissGame',
            'gameId' => $real_game_id,
        );
        $retval = $this->object->process_request($args);
        $args = array(
            'type' => 'dismissGame',
            'gameId' => $dummy_game_id,
        );
        $dummyval = $this->dummy->process_request($args);

        $this->assertEquals($dummyval, $retval, "game dismissal responses should be identical");
        $this->assertEquals('ok', $retval['status'], "responder should succeed");
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
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], 'Forum thread creation should succeed');

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
        $thread = $this->object->process_request($args);

        $args = array(
            'type' => 'createForumPost',
            'threadId' => $thread['data']['threadId'],
            'body' => 'Hey, wow, I do too!',
        );
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], 'Forum post creation should succeed');

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
        $thread = $this->object->process_request($args);

        $args = array(
            'type' => 'editForumPost',
            'postId' => (int)$thread['data']['posts'][0]['postId'],
            'body' => 'Cat!',
        );
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], 'Forum post editing should succeed');

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
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], 'Forum overview loading should succeed');

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
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], 'Forum board loading should succeed');

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
        $thread = $this->object->process_request($args);

        $args = array(
            'type' => 'loadForumThread',
            'threadId' => $thread['data']['threadId'],
        );
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], 'Forum thread loading should succeed');

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
        $this->object->process_request($args);

        $_SESSION = $this->mock_test_user_login('responder004');
        $args = array('type' => 'loadNextNewPost');
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], 'New forum post check should succeed');

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
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], 'Forum board marking as read should succeed');

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
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'], 'Entire forum marking as read should succeed');

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
        $thread = $this->object->process_request($args);

        $args = array(
            'type' => 'markForumThreadRead',
            'threadId' => $thread['data']['threadId'],
            'boardId' => 1,
            'timestamp' => strtotime('now'),
        );
        $retval = $this->object->process_request($args);
        $dummyval = $this->dummy->process_request($args);
        $this->assertEquals('ok', $retval['status'],
            'Forum thread marking as read should succeed');

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
}
