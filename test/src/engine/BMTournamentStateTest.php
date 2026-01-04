<?php

class BMTournamentStateTest extends PHPUnit\Framework\TestCase {

    /**
     * @coversNothing
     */
    public function testBMTournamentStateOrder() {
        $this->assertTrue(BMGameState::START_GAME <
                          BMGameState::APPLY_HANDICAPS);
        $this->assertTrue(BMGameState::END_ROUND <
                          BMGameState::END_GAME);
        $this->assertTrue(BMGameState::END_GAME <
                          BMGameState::CANCELLED);
    }

    /**
     * @covers BMTournamentState::validate_tournament_state
     */
    public function test_validate_tournament_state() {
        // valid set
        BMTournamentState::validate_tournament_state(BMTournamentState::START_ROUND);

        // invalid set
        try {
            BMTournamentState::validate_tournament_state('abcd');
            $this->fail('Tournament state must be an integer.');
        }
        catch (InvalidArgumentException $expected) {
        }

        try {
            BMTournamentState::validate_tournament_state(0);
            $this->fail('Invalid tournament state.');
        }
        catch (InvalidArgumentException $expected) {
        }
    }
}
