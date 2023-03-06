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

        // attempt an invalid tournament dismiss
        $this->verify_api_dismissTournament_failure(
            array(), "Tournament $tournamentId isn't complete",
            $tournamentId
        );
    }
}
