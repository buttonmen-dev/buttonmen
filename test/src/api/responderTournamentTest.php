<?php

/**
 * responderTournamentTest: API tests of the buttonmen responder, focused on tournaments
 */

require_once __DIR__.'/responderTestFramework.php';

class responderTournamentTest extends responderTestFramework {

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     *
     * This test invokes several tournament API methods
     */
    public function test_interface_tournament() {

        // responder003 is the POV player, so if you need to fake
        // login as a different player e.g. to submit an attack, always
        // return to responder003 as soon as you've done so
        $_SESSION = $this->mock_test_user_login('responder003');

        // A tournament with an invalid number of wins should fail
        $this->verify_api_createTournament_failure(
            array(), 'Tournament create failed because the maximum number of wins was invalid',
            'Single Elimination', 4, 0
        );

        // A tournament with an invalid tournament type should fail
        $this->verify_api_createTournament_failure(
            array(), 'Invalid tournament type',
            'foobar', 4, 3
        );

        // A tournament with an invalid number of players should fail
        $this->verify_api_createTournament_failure(
            array(), 'Invalid number of players for this tournament type',
            'Single Elimination', 5, 3
        );

        // Create a tournament to use for successful tournament tests
        $tournamentId = $this->verify_api_createTournament(
            array(),
            'Single Elimination', 4, 1
        );

        $expData = array(
            'tournamentId' => $tournamentId,
            'type' => 'SingleElimination',
            'nPlayers' => 4,
            'maxWins' => 1,
            'description' => '',
            'creatorDataArray' => array(
                'creatorId' => $_SESSION['user_id'],
                'creatorName' => $_SESSION['user_name'],
            ),

            'tournamentRoundNumber' => 1,
            'tournamentState' => 'JOIN_TOURNAMENT',
            'currentPlayerIdx' => FALSE,
            'gameDataArrayArray' => array(),
            'remainCountArray' => array(),
            'timestamp' => NULL,
            'isCreator' => TRUE,
            'isWatched' => TRUE,
            'maxRound' => 2,
        );
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // data from the perspective of a non-participant
        $_SESSION = $this->mock_test_user_login('responder004');
        $expData['isCreator'] = FALSE;
        $expData['isWatched'] = FALSE;
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);
        $_SESSION = $this->mock_test_user_login('responder003');
        $expData['isCreator'] = TRUE;
        $expData['isWatched'] = TRUE;

        // attempt an invalid tournament dismiss
        $this->verify_api_dismissTournament_failure(
            array(), "Only participants can dismiss tournaments",
            $tournamentId
        );
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // join the tournament
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_updateTournament(
            array(), $tournamentId, 'join', array('Avis')
        );
        $expData['remainCountArray'] = array(0 => 0);
        $expData['playerDataArray'] = array(
            0 => array(
                'playerId' => $_SESSION['user_id'],
                'playerName' => $_SESSION['user_name'],
                'buttonName' => 'Avis',
            ),
        );
        $_SESSION = $this->mock_test_user_login('responder003');
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // try to join the tournament with an invalid button choice
        $_SESSION = $this->mock_test_user_login('responder005');
        $this->verify_api_updateTournament_failure(
            array(), 'Invalid button choice.', $tournamentId, 'join', array('Bauer', 'Hammer')
        );
        $_SESSION = $this->mock_test_user_login('responder003');
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // try again, validly this time
        $_SESSION = $this->mock_test_user_login('responder005');
        $this->verify_api_updateTournament(
            array(), $tournamentId, 'join', array('Haruspex')
        );
        $expData['remainCountArray'][1] = 0;
        $expData['playerDataArray'][1] = array(
            'playerId' => $_SESSION['user_id'],
            'playerName' => $_SESSION['user_name'],
            'buttonName' => 'haruspex',
        );
        $_SESSION = $this->mock_test_user_login('responder003');
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // nonparticipant unfollows the tournament, but they weren't previously following it
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_unfollowTournament_failure(
            array(), "Tournament was not being followed", $tournamentId
        );
        $_SESSION = $this->mock_test_user_login('responder003');
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // participant follows the tournament, which is allowed because they're already in the tournament
        $_SESSION = $this->mock_test_user_login('responder005');
        $retval = $this->verify_api_followTournament(
            array(), $tournamentId
        );
        $this->assertEquals('Tournament was already being followed', $retval['message']);
        $_SESSION = $this->mock_test_user_login('responder003');
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // nonparticipant follows the tournament
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_followTournament(
            array(), $tournamentId
        );
        $_SESSION = $this->mock_test_user_login('responder003');
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // participant follows the tournament, which is allowed because they're already following it
        $_SESSION = $this->mock_test_user_login('responder002');
        $retval = $this->verify_api_followTournament(
            array(), $tournamentId
        );
        $this->assertEquals('Tournament was already being followed', $retval['message']);
        $_SESSION = $this->mock_test_user_login('responder003');
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // tournament creator joins the tournament
        $this->verify_api_updateTournament(
            array(), $tournamentId, 'join', array('ConMan')
        );
        $expData['remainCountArray'][2] = 0;
        $expData['playerDataArray'][2] = array(
            'playerId' => $_SESSION['user_id'],
            'playerName' => $_SESSION['user_name'],
            'buttonName' => 'ConMan',
        );
        $expData['currentPlayerIdx'] = 2;
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // last player joins the tournament
        $_SESSION = $this->mock_test_user_login('responder006');

        // by chance, players end up in the same order after shuffling
        $playerShuffleRandVals = array(0, 0, 0, 0);
        $gameOneRandVals = array(1, 1, 1, 1, 54);
        $gameTwoRandVals = array(1, 1, 1, 1);
        $allRandVals = array_merge($playerShuffleRandVals, $gameOneRandVals, $gameTwoRandVals);
        $this->verify_api_updateTournament(
            $allRandVals, $tournamentId, 'join', array('Haruspex')
        );
        $expData['remainCountArray'][3] = 0;
        $expData['playerDataArray'][3] = array(
            'playerId' => $_SESSION['user_id'],
            'playerName' => $_SESSION['user_name'],
            'buttonName' => 'haruspex',
        );
        $expData['tournamentState'] = 'PLAY_GAMES';
        $expData['remainCountArray'][0] = 1;
        $expData['remainCountArray'][1] = 1;
        $expData['remainCountArray'][2] = 1;
        $expData['remainCountArray'][3] = 1;
        $_SESSION = $this->mock_test_user_login('responder003');

        // grab the response without checking it, so we can pull the gameIds
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId, $check=FALSE);
        $this->assertEquals(count($retval['gameDataArrayArray'][0]), 2);
        $expData['gameDataArrayArray'][0] = $retval['gameDataArrayArray'][0];
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        $gameOneId = $expData['gameDataArrayArray'][0][0]['gameId'];
        $gameTwoId = $expData['gameDataArrayArray'][0][1]['gameId'];

        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_updateTournament_failure(
            array(), "The tournament has already started.", $tournamentId, 'join', array('Haruspex')
        );

        // Initial game data for game 1
        $_SESSION = $this->mock_test_user_login('responder003');
        $gameOneExpData = $this->generate_init_expected_data_array($gameOneId, 'responder004', 'responder005', 1, 'SPECIFY_DICE');
        $gameOneExpData['tournamentId'] = $tournamentId;
        $gameOneExpData['tournamentRoundNumber'] = 1;
        $gameOneExpData['description'] = 'Tournament Round 1';
        $gameOneExpData['currentPlayerIdx'] = FALSE;
        $gameOneExpData['creatorDataArray'] = array('creatorId' => 0, 'creatorName' => '');
        $gameOneExpData['gameActionLog'][0]['message'] = 'Game created automatically';
        $gameOneExpData['gameChatLog'] = array(array('timestamp' => 0, 'player' => '', 'message' => 'The chat for this game is private'));
        $gameOneExpData['gameChatLogCount'] = 1;
        $gameOneExpData['playerDataArray'][0]['swingRequestArray'] = array('X' => array(4, 20));
        $gameOneExpData['playerDataArray'][0]['button'] = array('name' => 'Avis', 'recipe' => '(4) (4) (10) (12) (X)', 'originalRecipe' => '(4) (4) (10) (12) (X)', 'artFilename' => 'avis.png');
        $gameOneExpData['playerDataArray'][1]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'originalRecipe' => '(99)', 'artFilename' => 'haruspex.png');
        $gameOneExpData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => NULL, 'sides' => 10, 'skills' => array(), 'properties' => array(), 'recipe' => '(10)', 'description' => '10-sided die'),
            array('value' => NULL, 'sides' => 12, 'skills' => array(), 'properties' => array(), 'recipe' => '(12)', 'description' => '12-sided die'),
            array('value' => NULL, 'sides' => NULL, 'skills' => array(), 'properties' => array(), 'recipe' => '(X)', 'description' => 'X Swing Die'),
        );
        $gameOneExpData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => NULL, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        $gameOneExpData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $gameOneExpData['playerDataArray'][0]['lastActionTime'] = 0;
        $gameOneExpData['playerDataArray'][1]['lastActionTime'] = 0;
        $gameOneExpData['playerDataArray'][0]['playerColor'] = '#cccccc';
        $gameOneExpData['playerDataArray'][1]['playerColor'] = '#dddddd';
        $this->game_number = 100001;
        $retval = $this->verify_api_loadGameData($gameOneExpData, $gameOneId, 10);

        // Initial game data for game 2
        $gameTwoExpData = $this->generate_init_expected_data_array($gameTwoId, 'responder003', 'responder006', 1, 'START_TURN');
        $gameTwoExpData['gameSkillsInfo'] = $this->get_skill_info(array('Poison'));
        $gameTwoExpData['tournamentId'] = $tournamentId;
        $gameTwoExpData['tournamentRoundNumber'] = 1;
        $gameTwoExpData['description'] = 'Tournament Round 1';
        $gameTwoExpData['activePlayerIdx'] = 0;
        $gameTwoExpData['playerWithInitiativeIdx'] = 0;
        $gameTwoExpData['creatorDataArray'] = array('creatorId' => 0, 'creatorName' => '');
        $gameTwoExpData['validAttackTypeArray'] = array('Power', 'Skill');
        $gameTwoExpData['gameActionLog'][0]['message'] = 'Game created automatically';
        array_unshift($gameTwoExpData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder003 rolled [(4):1, (6):1, p(20):1], responder006 rolled [(99):1].'));
        $gameTwoExpData['gameActionLogCount'] = 2;
        $gameTwoExpData['playerDataArray'][0]['button'] = array('name' => 'ConMan', 'recipe' => '(4) (6) p(20)', 'originalRecipe' => '(4) (6) p(20)', 'artFilename' => 'conman.png');
        $gameTwoExpData['playerDataArray'][1]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'originalRecipe' => '(99)', 'artFilename' => 'haruspex.png');
        $gameTwoExpData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 1, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => 1, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => 1, 'sides' => 20, 'skills' => array('Poison'), 'properties' => array(), 'recipe' => 'p(20)', 'description' => 'Poison 20-sided die'),
        );
        $gameTwoExpData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 1, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        $gameTwoExpData['playerDataArray'][1]['waitingOnAction'] = FALSE;
        $gameTwoExpData['playerDataArray'][0]['roundScore'] = -15;
        $gameTwoExpData['playerDataArray'][1]['roundScore'] = 49.5;
        $gameTwoExpData['playerDataArray'][0]['sideScore'] = -43.0;
        $gameTwoExpData['playerDataArray'][1]['sideScore'] = 43.0;
        $gameTwoExpData['playerDataArray'][0]['canStillWin'] = TRUE;
        $gameTwoExpData['playerDataArray'][1]['canStillWin'] = TRUE;
        $gameTwoExpData['playerDataArray'][0]['lastActionTime'] = 0;
        $gameTwoExpData['playerDataArray'][1]['lastActionTime'] = 0;
        $this->game_number = 100002;
        $gameTwoRetval = $this->verify_api_loadGameData($gameTwoExpData, $gameTwoId, 10);

        // Take a turn in game 1
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitDieValues(
            array(1),
            $gameOneId, 1, array('X' => 4), NULL);
        $gameOneRetval = $this->verify_api_loadGameData($gameOneExpData, $gameOneId, 10, FALSE);

        // game activity does not currently change tournament metadata
        $_SESSION = $this->mock_test_user_login('responder003');
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // Take a turn in game 2, completing it
        $this->verify_api_submitTurn(
            array(4),
            'responder003 performed Skill attack using [(4):1] against [(99):1]; Defender (99) was captured; Attacker (4) rerolled 1 => 4. End of round: responder003 won round 1 (84 vs. 0). ',
            $gameTwoRetval, array(array(0, 0), array(1, 0)),
            $gameTwoId, 1, 'Skill', 0, 1, '');

        $expData['gameDataArrayArray'][0][1]['statusId'] = 3;
        $expData['gameDataArrayArray'][0][1]['status'] = 'COMPLETE';
        $expData['gameDataArrayArray'][0][1]['nWinsArray'][0] = 1;
        $expData['gameDataArrayArray'][0][1]['winner'] = 'responder003';
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // Take turns in game 1 until it is completed
        $_SESSION = $this->mock_test_user_login('responder004');
        $this->verify_api_submitTurn(
            array(),
            'responder004 passed. ',
            $gameOneRetval, array(),
            $gameOneId, 1, 'Pass', 0, 1, '');
        $gameOneRetval = $this->verify_api_loadGameData($gameOneExpData, $gameOneId, 10, FALSE);

        $_SESSION = $this->mock_test_user_login('responder005');
        $this->verify_api_submitTurn(
            array(55),
            'responder005 performed Power attack using [(99):54] against [(4):1]; Defender (4) was captured; Attacker (99) rerolled 54 => 55. responder004 passed. ',
            $gameOneRetval, array(array(0, 0), array(1, 0)),
            $gameOneId, 1, 'Power', 1, 0, '');
        $gameOneRetval = $this->verify_api_loadGameData($gameOneExpData, $gameOneId, 10, FALSE);

        $this->verify_api_submitTurn(
            array(56),
            'responder005 performed Power attack using [(99):55] against [(4):1]; Defender (4) was captured; Attacker (99) rerolled 55 => 56. responder004 passed. ',
            $gameOneRetval, array(array(0, 0), array(1, 0)),
            $gameOneId, 1, 'Power', 1, 0, '');
        $gameOneRetval = $this->verify_api_loadGameData($gameOneExpData, $gameOneId, 10, FALSE);

        $this->verify_api_submitTurn(
            array(57),
            'responder005 performed Power attack using [(99):56] against [(10):1]; Defender (10) was captured; Attacker (99) rerolled 56 => 57. responder004 passed. ',
            $gameOneRetval, array(array(0, 0), array(1, 0)),
            $gameOneId, 1, 'Power', 1, 0, '');
        $gameOneRetval = $this->verify_api_loadGameData($gameOneExpData, $gameOneId, 10, FALSE);

        $this->verify_api_submitTurn(
            array(58),
            'responder005 performed Power attack using [(99):57] against [(12):1]; Defender (12) was captured; Attacker (99) rerolled 57 => 58. responder004 passed. ',
            $gameOneRetval, array(array(0, 0), array(1, 0)),
            $gameOneId, 1, 'Power', 1, 0, '');
        $gameOneRetval = $this->verify_api_loadGameData($gameOneExpData, $gameOneId, 10, FALSE);

        $this->verify_api_submitTurn(
            array(59, 2, 2, 2, 2),
            'responder005 performed Power attack using [(99):58] against [(X=4):1]; Defender (X=4) was captured; Attacker (99) rerolled 58 => 59. End of round: responder005 won round 1 (83.5 vs. 0). ',
            $gameOneRetval, array(array(0, 0), array(1, 0)),
            $gameOneId, 1, 'Power', 1, 0, '');
        $gameOneRetval = $this->verify_api_loadGameData($gameOneExpData, $gameOneId, 10, FALSE);
        $_SESSION = $this->mock_test_user_login('responder003');

        // Second round of tournament has now started
        // Again, grab the response without checking it, so we can pull the gameId
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId, $check=FALSE);
        $this->assertEquals(count($retval['gameDataArrayArray'][1]), 1);
        $expData['gameDataArrayArray'][1] = $retval['gameDataArrayArray'][1];
        $gameThreeId = $expData['gameDataArrayArray'][1][0]['gameId'];
        $expData['tournamentRoundNumber'] = 2;
        $expData['remainCountArray'][0] = 0;
        $expData['remainCountArray'][3] = 0;
        $expData['gameDataArrayArray'][0][0]['statusId'] = 3;
        $expData['gameDataArrayArray'][0][0]['status'] = 'COMPLETE';
        $expData['gameDataArrayArray'][0][0]['nWinsArray'][1] = 1;
        $expData['gameDataArrayArray'][0][0]['winner'] = 'responder005';
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // Initial game data for game 3
        $gameThreeExpData = $this->generate_init_expected_data_array($gameThreeId, 'responder005', 'responder003', 1, 'START_TURN');
        $gameThreeExpData['gameSkillsInfo'] = $this->get_skill_info(array('Poison'));
        $gameThreeExpData['tournamentId'] = $tournamentId;
        $gameThreeExpData['tournamentRoundNumber'] = 2;
        $gameThreeExpData['description'] = 'Tournament Round 2';
        $gameThreeExpData['activePlayerIdx'] = 1;
        $gameThreeExpData['playerWithInitiativeIdx'] = 1;
        $gameThreeExpData['currentPlayerIdx'] = 1;
        $gameThreeExpData['creatorDataArray'] = array('creatorId' => 0, 'creatorName' => '');
        $gameThreeExpData['validAttackTypeArray'] = array('Power', 'Skill');
        $gameThreeExpData['gameActionLog'][0]['message'] = 'Game created automatically';
        array_unshift($gameThreeExpData['gameActionLog'], array('timestamp' => 'TIMESTAMP', 'player' => '', 'message' => 'responder003 won initiative for round 1. Initial die values: responder005 rolled [(99):2], responder003 rolled [(4):2, (6):2, p(20):2].'));
        $gameThreeExpData['gameActionLogCount'] = 2;
        $gameThreeExpData['playerDataArray'][0]['button'] = array('name' => 'haruspex', 'recipe' => '(99)', 'originalRecipe' => '(99)', 'artFilename' => 'haruspex.png');
        $gameThreeExpData['playerDataArray'][1]['button'] = array('name' => 'ConMan', 'recipe' => '(4) (6) p(20)', 'originalRecipe' => '(4) (6) p(20)', 'artFilename' => 'conman.png');
        $gameThreeExpData['playerDataArray'][0]['activeDieArray'] = array(
            array('value' => 2, 'sides' => 99, 'skills' => array(), 'properties' => array(), 'recipe' => '(99)', 'description' => '99-sided die'),
        );
        $gameThreeExpData['playerDataArray'][1]['activeDieArray'] = array(
            array('value' => 2, 'sides' => 4, 'skills' => array(), 'properties' => array(), 'recipe' => '(4)', 'description' => '4-sided die'),
            array('value' => 2, 'sides' => 6, 'skills' => array(), 'properties' => array(), 'recipe' => '(6)', 'description' => '6-sided die'),
            array('value' => 2, 'sides' => 20, 'skills' => array('Poison'), 'properties' => array(), 'recipe' => 'p(20)', 'description' => 'Poison 20-sided die'),
        );
        $gameThreeExpData['playerDataArray'][0]['waitingOnAction'] = FALSE;
        $gameThreeExpData['playerDataArray'][0]['roundScore'] = 49.5;
        $gameThreeExpData['playerDataArray'][1]['roundScore'] = -15;
        $gameThreeExpData['playerDataArray'][0]['sideScore'] = 43.0;
        $gameThreeExpData['playerDataArray'][1]['sideScore'] = -43.0;
        $gameThreeExpData['playerDataArray'][0]['canStillWin'] = TRUE;
        $gameThreeExpData['playerDataArray'][1]['canStillWin'] = TRUE;
        $gameThreeExpData['playerDataArray'][0]['lastActionTime'] = 0;
        $gameThreeExpData['playerDataArray'][1]['lastActionTime'] = 0;
        $gameThreeExpData['playerDataArray'][0]['playerColor'] = '#ddffdd';
        $gameThreeExpData['playerDataArray'][1]['playerColor'] = '#dd99dd';
        $this->game_number = 100003;
        $gameThreeRetval = $this->verify_api_loadGameData($gameThreeExpData, $gameThreeId, 10);

        // Take a turn in game 3, completing it
        $this->verify_api_submitTurn(
            array(3),
            'responder003 performed Power attack using [(4):2] against [(99):2]; Defender (99) was captured; Attacker (4) rerolled 2 => 3. End of round: responder003 won round 1 (84 vs. 0). ',
            $gameThreeRetval, array(array(0, 0), array(1, 0)),
            $gameThreeId, 1, 'Power', 1, 0, '');

        // Tournament data at the end of the tournament
        $expData['tournamentState'] = 'END_TOURNAMENT';
        $expData['remainCountArray'][1] = 0;
        $expData['gameDataArrayArray'][1][0]['statusId'] = 3;
        $expData['gameDataArrayArray'][1][0]['status'] = 'COMPLETE';
        $expData['gameDataArrayArray'][1][0]['nWinsArray'][1] = 1;
        $expData['gameDataArrayArray'][1][0]['winner'] = 'responder003';
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // bystander dismisses the tournament, which isn't allowed because only participants can dismiss a tournament
        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_dismissTournament_failure(
            array(), "Only participants can dismiss tournaments", $tournamentId
        );
        $_SESSION = $this->mock_test_user_login('responder003');
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // Participant dismisses the tournament
        $this->verify_api_dismissTournament(
            array(), $tournamentId
        );

        $expData['isWatched'] = FALSE;
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);
    }
}
