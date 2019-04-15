<?php

/**
 * responder00Test: API tests of the buttonmen responder, file 00
 *
 * This file contains all prerequisite tests for other responder
 * tests (e.g. tests that create players), and all other "miscellaneous"
 * tests, that is, all API tests except numbered game playback tests.
 */

require_once 'responderTestFramework.php';

class responder00Test extends responderTestFramework {

    public function test_request_invalid() {
        $args = array('type' => 'foobar');
        $retval = $this->verify_api_failure($args, 'Specified API function does not exist');
    }

    public function test_request_createUser() {
        global $BM_RAND_VALS;
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
        // create it in the test DB.
        while (!($created_real)) {
            $this->assertTrue($trynum < $maxtries,
                "Internal test error: too many responderNNN users in the test database. " .
                "Clean these out by hand.");
            $username = 'responder' . sprintf('%03d', $trynum);
            $responder = new ApiResponder($this->spec, TRUE);
            $BM_RAND_VALS = array($this->get_fake_verification_randval($username));
            $real_new = $responder->process_request(
                            array('type' => 'createUser',
                                  'username' => $username,
                                  'password' => 't',
                                  'email' => $username . '@example.com'));
            if ($real_new['status'] == 'ok') {
                $created_real = True;

                $this->assertEquals(
                    $real_new['message'],
                    "User " . $username . " created successfully.  A verification code has been e-mailed to " . $username . "@example.com.  Follow the link in that message to start beating people up! (Note: If you don't see the email shortly, be sure to check your spam folder.)");
                $this->assertTrue(is_numeric($real_new['data']['playerId']));
                $this->assertEquals($real_new['data']['userName'], $username);

                // Use tester5 for the fake username, to agree with the frontend
                $real_new['message'] = str_replace($username, 'tester5', $real_new['message']);
                $real_new['data']['userName'] = 'tester5';
                $this->cache_json_api_output('createUser', 'tester5', $real_new);

                // create the same user again and make sure it fails this time
                $this->verify_api_failure(
                    array('type' => 'createUser',
                          'username' => $username,
                          'password' => 't',
                          'email' => $username . '@example.com'),
                    $username . ' already exists (id=' . $real_new['data']['playerId'] . ')'
                );

                // FIXME: also cache the failure

                // Now run a verifyUser test on the newly-created user
                $verify_retval = $responder->process_request(
                    array('type' => 'verifyUser',
                          'playerId' => $real_new['data']['playerId'],
                          'playerKey' => md5($this->get_fake_verification_randval($username)),
                    ));
                $this->assertEquals($verify_retval['status'], 'ok');
                $this->assertEquals($verify_retval['message'], 'Account activated for player ' . $username . '!');
                $this->assertEquals($verify_retval['data'], TRUE);

                // Use a fake playerId as the key for verification
                $fakePlayerId = 1;
                $this->cache_json_api_output('verifyUser', $fakePlayerId, $verify_retval);
            }
            $trynum += 1;
        }

        // Since user IDs are sequential, this is a good time to test the behavior of
        // to verify the behavior of loadProfileInfo() on an invalid player name.
        // However, mock_test_user_login() ensures that users 1-5 are created, so skip those
        // (If you create real user responder007, increment badUserNum's minimum)
        $_SESSION = $this->mock_test_user_login();
        $badUserNum = max(7, $trynum);
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

    /**
     * @group fulltest_deps
     *
     * As a side effect, this test actually enables preferences which some other tests need:
     * * turn on autopass for responder003-006
     * * turn on fire_overshooting for responder005
     * * turn off autoaccept for responder006 (and turn it on for all other players)
     */
    public function test_request_savePlayerInfo() {
        $this->verify_login_required('savePlayerInfo');

        $args = array(
            'type' => 'savePlayerInfo',
            'name_irl' => 'Test User',
            'is_email_public' => 'False',
            'dob_month' => '2',
            'dob_day' => '29',
            'gender' => '',
            'comment' => '',
            'vacation_message' => '',
            'homepage' => '',
            'autoaccept' => 'true',
            'autopass' => 'false',
            'fire_overshooting' => 'false',
            'uses_gravatar' => 'false',
            'die_background' => 'symmetric',
            'player_color' => '#dd99dd',
            'opponent_color' => '#ddffdd',
            'neutral_color_a' => '#cccccc',
            'neutral_color_b' => '#dddddd',
            'monitor_redirects_to_game' => 'false',
            'monitor_redirects_to_forum' => 'false',
            'automatically_monitor' => 'false',
        );
        $_SESSION = $this->mock_test_user_login('responder001');
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Player info updated successfully.');
        $this->assertEquals(array_keys($retval['data']), array('playerId'));

        $_SESSION = $this->mock_test_user_login('responder002');
        $args['vacation_message'] = 'Player 2 is on vacation';
        $retval = $this->verify_api_success($args);
        $args['vacation_message'] = '';
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Player info updated successfully.');
        $this->assertEquals(array_keys($retval['data']), array('playerId'));

        $_SESSION = $this->mock_test_user_login('responder003');
        $this->verify_invalid_arg_rejected('savePlayerInfo');

        // If current_password == new_password, the test executes the password change code,
        // database state from a previous test run can't cause the test to fail
        $args = array(
            'type' => 'savePlayerInfo',
            'name_irl' => 'Test User',
            'is_email_public' => 'False',
            'dob_month' => '2',
            'dob_day' => '29',
            'gender' => '',
            'comment' => '',
            'vacation_message' => '',
            'homepage' => '',
            'autoaccept' => 'true',
            'autopass' => 'true',
            'fire_overshooting' => 'false',
            'uses_gravatar' => 'false',
            'die_background' => 'realistic',
            'player_color' => '#dd99dd',
            'opponent_color' => '#ddffdd',
            'neutral_color_a' => '#cccccc',
            'neutral_color_b' => '#dddddd',
            'monitor_redirects_to_game' => 'false',
            'monitor_redirects_to_forum' => 'false',
            'automatically_monitor' => 'false',
            'current_password' => 't',
            'new_password' => 't',
        );
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Player info updated successfully.');
        $this->assertEquals(array_keys($retval['data']), array('playerId'));
        $this->cache_json_api_output('savePlayerInfo', 'Test_User', $retval);

        $_SESSION = $this->mock_test_user_login('responder004');
        $retval = $this->verify_api_success($args);

        $args['fire_overshooting'] = 'true';
        $_SESSION = $this->mock_test_user_login('responder005');
        $retval = $this->verify_api_success($args);

        $args['fire_overshooting'] = 'false';
        $args['autoaccept'] = 'false';
        $_SESSION = $this->mock_test_user_login('responder006');
        $retval = $this->verify_api_success($args);
    }

    /**
     * @depends test_request_savePlayerInfo
     */
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

        // Successfully create a game with all players and buttons specified
        $retval = $this->verify_api_createGame(
            array(1, 1, 1, 1, 2, 2, 2, 2),
            'responder003', 'responder004', 'Avis', 'Avis', 3, '', NULL, 'data'
        );

        $this->assertEquals('ok', $retval['status'], 'Game creation should succeed');
        $this->assertEquals(array('gameId'), array_keys($retval['data']));
        $this->assertTrue(is_numeric($retval['data']['gameId']));
        $this->assertEquals("Game " . $retval['data']['gameId'] . " created successfully.", $retval['message']);

        $this->cache_json_api_output('createGame', 'Avis_Avis', $retval);


        // Successfully create an open game
        $retval = $this->verify_api_createGame(
            array(),
            'responder003', '', 'Avis', '', 3, '', NULL, 'data'
        );

        $this->assertEquals('ok', $retval['status'], 'Game creation should succeed');
        $this->assertEquals(array('gameId'), array_keys($retval['data']));
        $this->assertTrue(is_numeric($retval['data']['gameId']));
        $this->assertEquals("Game " . $retval['data']['gameId'] . " created successfully.", $retval['message']);

        $this->cache_json_api_output('createGame', 'Avis_', $retval);


        // Check that the first player in a game can be different from the current logged in player
        $retval = $this->verify_api_createGame(
            array(1, 1, 1, 1, 2, 2, 2, 2),
            'responder001', 'responder002', 'Avis', 'Avis', 3, '', NULL, 'data'
        );

        $this->assertEquals('ok', $retval['status'], 'Game creation should succeed');
        $this->assertEquals(array('gameId'), array_keys($retval['data']));
        $this->assertTrue(is_numeric($retval['data']['gameId']));
        $this->assertEquals("Game " . $retval['data']['gameId'] . " created successfully.", $retval['message']);
    }

    public function test_request_joinOpenGame() {
        $this->verify_login_required('joinOpenGame');

        $this->game_number = 44;

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
        $this->verify_api_joinOpenGame(array(1, 1, 1, 1, 2, 2, 2, 2), $gameId);
    }

    public function test_request_cancelOpenGame() {
        $this->verify_login_required('cancelOpenGame');

        $this->game_number = 49;

        $_SESSION = $this->mock_test_user_login('responder003');
        $this->verify_invalid_arg_rejected('cancelOpenGame');
        $this->verify_mandatory_args_required(
            'cancelOpenGame',
            array('gameId' => 49)
        );

        $_SESSION = $this->mock_test_user_login('responder004');
        $gameId = $this->verify_api_createGame(
            array(),
            'responder004', '', 'Avis', 'Avis', '3'
        );

        $this->verify_api_cancelOpenGame(array(), $gameId);
    }

    public function test_request_reactToNewGameAccept() {
        $this->verify_login_required('reactToNewGame');

        $_SESSION = $this->mock_test_user_login('responder003');
        $this->verify_invalid_arg_rejected('reactToNewGame');
        $this->verify_mandatory_args_required(
            'reactToNewGame',
            array('gameId' => 21, 'action' => 'accept')
        );

        $_SESSION = $this->mock_test_user_login('responder004');
        $gameId = $this->verify_api_createGame(
            array(),
            'responder004', 'responder006', 'Avis', 'Avis', '3'
        );

        $_SESSION = $this->mock_test_user_login('responder006');
        $this->verify_api_reactToNewGame(
            array(1, 1, 1, 1, 2, 2, 2, 2),
            $gameId,
            'accept'
        );

       // If the creating player tries to reject the game after the target player
        // has already accepted it, the API behavior should be reasonable
        $_SESSION = $this->mock_test_user_login('responder004');
        $args = array(
            'type' => 'reactToNewGame',
            'gameId' => $gameId,
            'action' => 'reject',
        );
        $retval = $this->verify_api_failure($args, 'Your decision to withdraw the game failed because the game has been updated since you loaded the page');
    }

    public function test_request_reactToNewGameReject() {
        $_SESSION = $this->mock_test_user_login('responder004');
        $gameId = $this->verify_api_createGame(
            array(),
            'responder004', 'responder006', 'Avis', 'Avis', '3'
        );

        $_SESSION = $this->mock_test_user_login('responder006');
        $this->verify_api_reactToNewGame(
            array(),
            $gameId,
            'reject'
        );
    }

    /**
     * @depends test_request_savePlayerInfo
     *
     * This reproduces a bug in which cancelling a game causes the
     * target player to gain an additional pending game.
     */
    public function test_request_reactToNewGameCancel() {

        // count each player's pending games before doing anything, so the test doesn't rely on DB state
        $_SESSION = $this->mock_test_user_login('responder004');
        $creatorPendingCountPrecreate = $this->verify_api_countPendingGames();
        $_SESSION = $this->mock_test_user_login('responder006');
        $targetPendingCountPrecreate = $this->verify_api_countPendingGames();

        // after the game is created, the creator should have the same number of
        // pending games as before, and the target should now have one more
        $_SESSION = $this->mock_test_user_login('responder004');
        $gameId = $this->verify_api_createGame(
            array(),
            'responder004', 'responder006', 'Avis', 'Avis', '3'
        );
        $creatorPendingCountPostcreate = $this->verify_api_countPendingGames();
        $this->assertEquals($creatorPendingCountPrecreate, $creatorPendingCountPostcreate);
        $_SESSION = $this->mock_test_user_login('responder006');
        $targetPendingCountPostcreate = $this->verify_api_countPendingGames();
        $this->assertEquals($targetPendingCountPrecreate + 1, $targetPendingCountPostcreate);

        // after the game is cancelled (rejected by the player who created it),
        // both creator and target should have the same number of pending games as before this started
        $_SESSION = $this->mock_test_user_login('responder004');
        $retdata = $this->verify_api_reactToNewGame(
            array(), $gameId, 'reject'
        );
        $creatorPendingCountPostcancel = $this->verify_api_countPendingGames();
        $this->assertEquals($creatorPendingCountPrecreate, $creatorPendingCountPostcancel);
        $_SESSION = $this->mock_test_user_login('responder006');
        $targetPendingCountPostcancel = $this->verify_api_countPendingGames();
        $this->assertEquals($targetPendingCountPrecreate, $targetPendingCountPostcancel);
    }

    public function test_request_loadActivePlayers() {
        $this->verify_login_required('loadActivePlayers');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadActivePlayers');

        $this->verify_mandatory_args_required(
            'loadActivePlayers',
            array('numberOfPlayers' => 50)
        );

        // Invoke an API call to make sure some player has recently been active.
        $args = array('type' => 'loadButtonData', 'buttonName' => 'Avis');
        $this->verify_api_success($args);

        // Now invoke loadActivePlayers
        $args = array('type' => 'loadActivePlayers', 'numberOfPlayers' => 50);
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Active players retrieved successfully.');
        $this->assertEquals(array_keys($retval['data']), array('players'));

        // loadActivePlayers does not guarantee an ordering if multiple players have been active
        // within the same second
        $this->assertEquals(substr($retval['data']['players'][0]['playerName'], 0, 9), 'responder');

        $this->cache_json_api_output('loadActivePlayers', '50', $retval);
    }

    public function test_request_loadButtonData() {
        $this->verify_login_required('loadButtonData');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadButtonData');

        // First, examine one button in detail
        $args = array('type' => 'loadButtonData', 'buttonName' => 'Avis');
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Button data retrieved successfully.');
        $this->assertEquals($retval['data'], array(array(
           'buttonId' => 256,
           'buttonName' => 'Avis',
           'recipe' => '(4) (4) (10) (12) (X)',
           'hasUnimplementedSkill' => FALSE,
           'buttonSet' => 'Soldiers',
           'dieTypes' => array('X Swing' => array('code' => 'X', 'swingMin' => 4, 'swingMax' => 20, 'description' => 'X Swing Dice can be any die between 4 and 20. Swing Dice are allowed to be any integral size between their upper and lower limit, including both ends, and including nonstandard die sizes like 17 or 9. Each player chooses his or her Swing Die in secret at the beginning of the match, and thereafter the loser of each round may change their Swing Die between rounds. If a character has any two Swing Dice of the same letter, they must always be the same size.')),
           'dieSkills' => array(),
           'isTournamentLegal' => true,
           'artFilename' => 'avis.png',
           'tags' => array(),
           'flavorText' => 'Avis is an expert chainsaw dueler and ice sculptor, and she likes to beat people up.',
           'specialText' => NULL,
        )));

        $this->cache_json_api_output('loadButtonData', 'Avis', $retval);

        // Then examine the rest
        $args = array('type' => 'loadButtonData');
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Button data retrieved successfully.');
        $this->assertEquals(count($retval['data']), 784);

        $this->cache_json_api_output('loadButtonData', 'noargs', $retval);
    }

    public function test_request_loadButtonSetData() {
        $this->verify_login_required('loadButtonSetData');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadButtonSetData');

        // First, examine one set in detail
        $args = array('type' => 'loadButtonSetData', 'buttonSet' => 'The Big Cheese');
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Button set data retrieved successfully.');
        $this->assertEquals($retval['data'], array(array(
            'setName' => 'The Big Cheese',
            'buttons' => array(
                array(
                    'buttonId' => 35,
                    'buttonName' => 'Bunnies',
                    'recipe' => '(1) (1) (1) (1) (X)',
                    'hasUnimplementedSkill' => false,
                    'buttonSet' => 'The Big Cheese',
                    'dieTypes' => array('X Swing'),
                    'dieSkills' => array(),
                    'isTournamentLegal' => false,
                    'artFilename' => 'bunnies.png',
                    'tags' => array(),
                ),
                array(
                    'buttonId' => 36,
                    'buttonName' => 'Lab Rat',
                    'recipe' => '(2) (2) (2) (2) (X)',
                    'hasUnimplementedSkill' => false,
                    'buttonSet' => 'The Big Cheese',
                    'dieTypes' => array('X Swing'),
                    'dieSkills' => array(),
                    'isTournamentLegal' => false,
                    'artFilename' => 'labrat.png',
                    'tags' => array(),
                )),
            'numberOfButtons' => 2,
            'dieSkills' => array(),
            'dieTypes' => array('X Swing'),
            'onlyHasUnimplementedButtons' => FALSE,
        )));

        $this->cache_json_api_output('loadButtonSetData', 'The_Big_Cheese', $retval);

        // Then examine the rest
        $args = array('type' => 'loadButtonSetData');
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Button set data retrieved successfully.');
        $this->assertEquals(count($retval['data']), 83);

        $this->cache_json_api_output('loadButtonSetData', 'noargs', $retval);
    }

    /**
     * @depends test_request_savePlayerInfo
     */
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

        // now load the game data
        $retval = $this->verify_api_success(
            array('type' => 'loadGameData', 'game' => $real_game_id, 'logEntryLimit' => 10));

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

        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Pending game count succeeded.');
        $this->assertTrue(array_key_exists('count', $retval['data']));
        $this->assertTrue(is_numeric($retval['data']['count']));

        // countPendingGames takes no args, so store this as the sole reference API output
        $this->cache_json_api_output('countPendingGames', 'noargs', $retval);
    }

    public function test_request_loadPlayerName() {
        $this->verify_invalid_arg_rejected('loadPlayerName');

        $_SESSION = $this->mock_test_user_login();
        $args = array('type' => 'loadPlayerName');
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], NULL);
        $this->assertEquals($retval['data'], array('userName' => 'responder003'));

        // loadPlayerName takes no args, so store this as the sole reference API output
        // after changing the username to match the UI tests' expectations
        $retval['data']['userName'] = 'tester1';
        $this->cache_json_api_output('loadPlayerName', 'noargs', $retval);
    }

    public function test_request_loadPlayerInfo() {
        $this->verify_login_required('loadPlayerInfo');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadPlayerInfo');

        $args = array('type' => 'loadPlayerInfo');
        $retval = $this->verify_api_success($args);

        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], NULL);

        $akeys = array_keys($retval['data']['user_prefs']);
        sort($akeys);
        $this->assertEquals($akeys, array('autoaccept', 'automatically_monitor', 'autopass', 'comment', 'creation_time', 'die_background', 'dob_day', 'dob_month', 'email', 'fanatic_button_id', 'favorite_button', 'favorite_buttonset', 'fire_overshooting', 'gender', 'homepage', 'id', 'image_size', 'is_email_public', 'last_access_time', 'last_action_time', 'monitor_redirects_to_forum', 'monitor_redirects_to_game', 'n_games_lost', 'n_games_won', 'name_ingame', 'name_irl', 'neutral_color_a', 'neutral_color_b', 'opponent_color', 'player_color', 'status', 'uses_gravatar', 'vacation_message'));
        $this->assertEquals($retval['data']['user_prefs']['name_ingame'], 'responder003');
        $this->assertEquals($retval['data']['user_prefs']['autoaccept'], TRUE);
        $this->assertEquals($retval['data']['user_prefs']['neutral_color_a'], '#cccccc');
        $this->assertEquals($retval['data']['user_prefs']['die_background'], 'realistic');

        // loadPlayerName takes no args, so store this as the sole reference API output
        // after changing the username to match the UI tests' expectations
        $retval['data']['user_prefs']['name_ingame'] = 'tester1';
        $this->cache_json_api_output('loadPlayerInfo', 'noargs', $retval);
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

        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Player ID retrieved successfully.');

        $akeys = array_keys($retval['data']['profile_info']);
        sort($akeys);
        $this->assertEquals($akeys, array('comment', 'creation_time', 'dob_day', 'dob_month', 'email', 'email_hash', 'fanatic_button_id', 'favorite_button', 'favorite_buttonset', 'gender', 'homepage', 'id', 'image_size', 'last_access_time', 'n_games_lost', 'n_games_won', 'name_ingame', 'name_irl', 'uses_gravatar','vacation_message'));
        $this->assertEquals($retval['data']['profile_info']['name_ingame'], 'responder003');
        $this->assertEquals($retval['data']['profile_info']['email'], NULL);
        $this->assertEquals($retval['data']['profile_info']['dob_day'], '29');

        // Cache the data as is, and also under the 'tester' name for which the UI tests load profile data
        $this->cache_json_api_output('loadProfileInfo', 'responder003', $retval);
        $retval['data']['profile_info']['name_ingame'] = 'tester';
        $this->cache_json_api_output('loadProfileInfo', 'tester', $retval);
    }

    public function test_request_loadPlayerNames() {
        $this->verify_login_required('loadPlayerNames');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadPlayerNames');

        $args = array('type' => 'loadPlayerNames');
        $retval = $this->verify_api_success($args);

        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Names retrieved successfully.');
        $this->assertEquals(array_keys($retval['data']), array('nameArray', 'statusArray'));
        $this->assertEquals(count($retval['data']['nameArray']), count($retval['data']['statusArray']));

        // We don't know for sure which player will be active, and the UI testing expects
        // 'tester2', so find an active player and modify its name before saving
        $active_player_idx = -1;
        foreach ($retval['data']['statusArray'] as $idx => $status) {
            if ($status == 'ACTIVE') {
                $active_player_idx = $idx;
            }
        }
        $this->assertTrue($active_player_idx >= 0);
        $retval['data']['nameArray'][$active_player_idx] = 'tester2';

        // loadPlayerNames takes no args, so store this as the sole reference API output
        $this->cache_json_api_output('loadPlayerNames', 'noargs', $retval);
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
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Successfully set die sizes');
        $this->assertEquals($retval['data'], TRUE);

        ///// Now test setting option values
        // create a game so we have the ID to load
        $real_game_id = $this->verify_api_createGame(
            array(1, 1, 2, 2),
            'responder003', 'responder004', 'Apples', 'Apples', '3'
        );

        // now ask for the game data so we have the timestamp to return
        $args = array(
            'type' => 'loadGameData',
            'game' => "$real_game_id");
        $retval = $this->verify_api_success($args);
        $timestamp = $retval['data']['timestamp'];

        // test submitting invalid responses (wrong indices)
        // #1407: this should return something friendlier
        $args = array(
            'type' => 'submitDieValues',
            'game' => $real_game_id,
            'roundNumber' => '1',
            'timestamp' => $timestamp,
            'optionValueArray' => array(1 => 12, 3 => 8, 4 => 20));
        $retval = $this->verify_api_failure($args, 'Internal error while setting die sizes');

        // now submit the option values
        $retval = $this->verify_api_submitDieValues(
            array(3, 3, 3),
            $real_game_id, '1', NULL, array(2 => 12, 3 => 8, 4 => 20));
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
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Successfully gained initiative');
        $this->assertEquals($retval['data'], array('gainedInitiative' => TRUE));
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

        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Dismissing game succeeded');
        $this->assertEquals($retval['data'], TRUE);

        // Hardcode a single fake game number here until we need to test dismissing in a more complex way
        $fakeGameNumber = 5;
        $this->cache_json_api_output('dismissGame', $fakeGameNumber, $retval);
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

        $this->assertEquals($retval['status'], 'ok');
        // BUG #1877: this message should be different
        $this->assertEquals($retval['message'], 'Forum thread loading succeeded');
        $this->assertEquals($retval['data']['boardId'], 1);
        $this->assertEquals($retval['data']['threadTitle'], 'Who likes ice cream?');

        // Cache retval under board ID for dummy API retrieval
        $this->cache_json_api_output('createForumThread', '1', $retval);
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

        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Forum post created successfully');
        $this->assertEquals($retval['data']['threadTitle'], 'Hello Wisconsin');

        // Cache retval under a fake thread ID for dummy API retrieval
        $fakeThreadId = 1;
        $this->cache_json_api_output('createForumPost', $fakeThreadId, $retval);
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
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Forum post edited successfully');
        $this->assertEquals($retval['data']['currentPostId'], $args['postId']);

        // Cache retval under a fake post ID for dummy API retrieval
        $fakePostId = 2;
        $retval['data']['currentPostId'] = $fakePostId;
        $this->cache_json_api_output('editForumPost', $fakePostId, $retval);
    }

    public function test_request_loadForumOverview() {
        $this->verify_login_required('loadForumOverview');

        $_SESSION = $this->mock_test_user_login();
        $this->verify_invalid_arg_rejected('loadForumOverview');

        $args = array('type' => 'loadForumOverview');
        $retval = $this->verify_api_success($args);
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Forum overview loading succeeded');
        $this->assertTrue(is_numeric($retval['data']['timestamp']));
        $this->assertTrue(is_array($retval['data']['boards']));

        $this->cache_json_api_output('loadForumOverview', 'noargs', $retval);
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
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Forum board loading succeeded');
        $this->assertEquals($retval['data']['boardId'], 1);

        $this->cache_json_api_output('loadForumBoard', '1', $retval);
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

        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Forum thread loading succeeded');
        $this->assertEquals($retval['data']['boardId'], 1);
        $this->assertEquals($retval['data']['threadId'], $thread['data']['threadId']);

        $fakeThreadId = 1;
        $retval['data']['threadId'] = $fakeThreadId;
        $this->cache_json_api_output('loadForumThread', $fakeThreadId, $retval);
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
        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Checked new forum posts successfully');
        $this->assertEquals(array_keys($retval['data']), array('nextNewPostId', 'nextNewPostThreadId'));
        $this->assertTrue(is_numeric($retval['data']['nextNewPostId']));
        $this->assertTrue(is_numeric($retval['data']['nextNewPostThreadId']));

        // fake the nextNewPostId so the UI tests can look for a fixed value
        $retval['data']['nextNewPostId'] = 3;
        $this->cache_json_api_output('loadNextNewPost', 'noargs', $retval);
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
        $this->assertEquals($retval['status'], 'ok');
        # See #1877 for concerns about this return strategy
        $this->assertEquals($retval['message'], 'Forum overview loading succeeded');
        $this->assertTrue(is_array($retval['data']));

        $fakeBoardId = 1;
        $this->cache_json_api_output('markForumBoardRead', $fakeBoardId, $retval);
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
        $this->assertEquals($retval['status'], 'ok');
        # See #1877 for concerns about this return strategy
        $this->assertEquals($retval['message'], 'Forum overview loading succeeded');
        $this->assertTrue(is_array($retval['data']));

        $this->cache_json_api_output('markForumRead', 'noargs', $retval);
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

        $this->assertEquals($retval['status'], 'ok');
        $this->assertEquals($retval['message'], 'Forum board loading succeeded');
        $this->assertEquals($retval['data']['boardId'], 1);

        $fakeThreadId = 1;
        $this->cache_json_api_output('markForumThreadRead', $fakeThreadId, $retval);
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
     * @depends test_request_savePlayerInfo
     *
     * This tests load of a game which does not exist
     */
    public function test_interface_game_load_failure() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $this->game_number = 100000;
        $_SESSION = $this->mock_test_user_login('responder003');

        $retval = $this->verify_api_loadGameData_failure(
            $this->game_number, "Game " . $this->game_number . " does not exist.");
    }
}
