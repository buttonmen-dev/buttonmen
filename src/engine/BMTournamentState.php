<?php
/**
 * BMTournamentState: The various tournament states possible in a tournament
 *
 * @author james
 */

/**
 * This class defines the tournament states possible in a tournament
 */
class BMTournamentState {
    // pre-tournament
    const START_TOURNAMENT = 10;

    // join tournament
    const JOIN_TOURNAMENT = 20;

    // shuffle players
    const SHUFFLE_PLAYERS = 25;

    // start round
    const START_ROUND = 30;

    // play games
    const PLAY_GAMES = 40;

    // end round
    const END_ROUND = 50;

    // end tournament
    const END_TOURNAMENT = 60;

    // special states
    const CANCELLED = 251;


    /**
     * All possible tournament state strings
     *
     * @return array
     */
    public static function all_tournament_state_strings() {
        return array('START_TOURNAMENT',
                     'JOIN_TOURNAMENT',
                     'SHUFFLE_PLAYERS',
                     'START_ROUND',
                     'PLAY_GAMES',
                     'END_ROUND',
                     'END_TOURNAMENT',
                     'CANCELLED');
    }

    /**
     * All possible tournament state values
     *
     * @return array
     */
    public static function all_tournament_state_values() {
        $tournamentStateValueArray = array();

        foreach (BMTournamentState::all_tournament_state_strings() as $tournamentStateStr) {
            $tournamentStateValueArray[] = constant('BMTournamentState::'.$tournamentStateStr);
        }
        return $tournamentStateValueArray;
    }

    /**
     * Convert numerical tournament state into a string
     *
     * @param int $tournamentState
     * @return string
     */
    public static function as_string($tournamentState) {
        $tournamentStateStrings = BMTournamentState::all_tournament_state_strings();
        $tournamentStateValues = BMTournamentState::all_tournament_state_values();

        $tournamentStateIdx = array_search($tournamentState, $tournamentStateValues);

        $tournamentStateString = '';
        if (FALSE !== $tournamentStateIdx) {
            $tournamentStateString = $tournamentStateStrings[$tournamentStateIdx];
        }

        return $tournamentStateString;
    }

    /**
     * Check that a provided tournament state is valid
     *
     * @param mixed $value
     */
    public static function validate_tournament_state($value) {
        if (FALSE === filter_var($value, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException(
                'Tournament state must be an integer.'
            );
        }

        if (!in_array($value, BMTournamentState::all_tournament_state_values())) {
            throw new InvalidArgumentException(
                'Invalid tournament state.'
            );
        }
    }
}
