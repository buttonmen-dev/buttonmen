<?php

/**
 * responderTournamentTest: API tests of the buttonmen responder, focused on tournaments
 */

require_once __DIR__.'/responderTestFramework.php';

class responderTournamentTest extends responderTestFramework {

    /**
     * @depends responder00Test::test_request_savePlayerInfo
     * @group fulltest_deps
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
            'gameIdArrayArray' => array(),
            'remainCountArray' => array(),
            'timestamp' => NULL,
            'isCreator' => TRUE,
            'isWatched' => TRUE,
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
            array(), "Tournament $tournamentId isn't complete",
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
        );
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
        );
        $expData['currentPlayerIdx'] = 2;
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        // last player joins the tournament
        $_SESSION = $this->mock_test_user_login('responder006');
        
        // by chance, players end up in the same order after shuffling
        $playerShuffleRandVals = array(0, 0, 0, 0);
        $gameOneRandVals = array(1, 1, 1, 1);
        $gameTwoRandVals = array(1, 1, 1, 1, 1);
        $allRandVals = array_merge($playerShuffleRandVals, $gameOneRandVals, $gameTwoRandVals);
        $this->verify_api_updateTournament(
            $allRandVals, $tournamentId, 'join', array('Haruspex')
        );
        $expData['remainCountArray'][3] = 0;
        $expData['playerDataArray'][3] = array(
            'playerId' => $_SESSION['user_id'],
            'playerName' => $_SESSION['user_name'],
        );
        $expData['tournamentState'] = 'PLAY_GAMES';
        $expData['remainCountArray'][0] = 1;
        $expData['remainCountArray'][1] = 1;
        $expData['remainCountArray'][2] = 1;
        $expData['remainCountArray'][3] = 1;
        $_SESSION = $this->mock_test_user_login('responder003');

        // grab the response without checking it, so we can pull the gameIds
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId, $check=FALSE);
        $this->assertEquals(count($retval['gameIdArrayArray'][0]), 2);
        $expData['gameIdArrayArray'][0] = $retval['gameIdArrayArray'][0];
        $retval = $this->verify_api_loadTournamentData($expData, $tournamentId);

        $_SESSION = $this->mock_test_user_login('responder002');
        $this->verify_api_updateTournament_failure(
            array(), "The tournament has already started.", $tournamentId, 'join', array('Haruspex')
        );
    }
}
