<?php
/**
 * BMTournamentSingleElimination: Contains the logic for single elimination tournaments
 *
 * @author: james
 */

/**
 * This class contains the logic for single elimination tournaments
 */
class BMTournamentSingleElimination extends BMTournament {
    // methods

    /**
     *  Initialise remainCountArray
     */
    protected function initialiseRemainCountArray() {
        if ($this->nPlayers <= 0) {
            return;
        }

        if (array_sum($this->remainCountArray) <= 0) {
            $this->remainCountArray = array_fill(0, $this->nPlayers, 1);
        }
    }

    /**
     * Create all games for a specific round of the tournament
     *
     * @param int $roundNumber
     */
    protected function create_games_for_round($roundNumber) {
        $remainingPlayerIdArray = $this->remainingPlayerIdArray();

        if (count($remainingPlayerIdArray) <= 1) {
            return;
        }

        if (array_key_exists($roundNumber - 1, $this->gameIdArrayArray) &&
            (count($this->gameIdArrayArray[$roundNumber - 1]) > 0)) {
            throw new LogicException('Games already exist for round ' . $roundNumber);
        }

        $this->gameIdArrayArray[$roundNumber - 1] = array();
        $interface = new BMInterface($this->isTest);

        for ($gameIdx = 0; $gameIdx < count($remainingPlayerIdArray) / 2; $gameIdx++) {
            // create game between players 2*$gameIdx and 2*$gameIdx + 1
            $playerId1 = $remainingPlayerIdArray[2*$gameIdx];
            $playerId2 = $remainingPlayerIdArray[2*$gameIdx + 1];
            $buttonId1 = $this->buttonIdArrayArray[$playerId1][0];
            $buttonId2 = $this->buttonIdArrayArray[$playerId2][0];
            $buttonNames = $interface->game()->retrieve_button_names(array($buttonId1, $buttonId2));

            $interfaceResponse = $interface->game()->create_game_from_button_ids(
                array($playerId1, $playerId2),
                array($buttonId1, $buttonId2),
                $buttonNames,
                $this->gameMaxWins,
                $this->description . ' Round ' . $roundNumber,
                NULL,
                0,                   // needs to be non-null, but also a non-player ID
                TRUE,
                array(),
                $this->tournamentId,
                $roundNumber
            );

            // add game number to $this->gameIdArrayArray
            if (is_array($interfaceResponse) && array_key_exists('gameId', $interfaceResponse)) {
                array_push($this->gameIdArrayArray[$roundNumber - 1], $interfaceResponse['gameId']);
            }
        }
    }

    /**
     * Update array of player remain chances based on last round
     */
    protected function update_remainCountArray() {
        $thisRoundGameIdArray = $this->gameIdArrayArray[$this->roundNumber - 1];
        $remainCountArray = $this->remainCountArray;

        $interface = new BMInterface($this->isTest);

        // reduce remain count for losers of the previous round
        foreach ($thisRoundGameIdArray as $gameId) {
            $gameData = $interface->load_api_game_data(0, $gameId, 0);

            if ($gameData['maxWins'] == $gameData['playerDataArray'][0]['gameScoreArray']['L']) {
                $loserId = $gameData['playerDataArray'][0]['playerId'];
            } elseif ($gameData['maxWins'] == $gameData['playerDataArray'][1]['gameScoreArray']['L']) {
                $loserId = $gameData['playerDataArray'][1]['playerId'];
            } else {
                // loser is the one who is currently active, since this is the person who is holding up play
                $loserId = $gameData['playerDataArray'][$gameData['activePlayerIdx']]['playerId'];
            }

            $loserIdx = array_search($loserId, $this->playerIdArray);
            if (FALSE === $loserIdx) {
                throw new LogicException("Loser ID $loserId not found");
            }
            if ($remainCountArray[$loserIdx] <= 0) {
                throw new LogicException("Remain count for player $loserId is already nonpositive");
            }

            $remainCountArray[$loserIdx]--;
        }

        $this->remainCountArray = $remainCountArray;
    }

    /**
     * Determine whether the tournament has completed
     *
     * @return bool
     */
    protected function has_tournament_completed() {
        // check for one player remaining
        return (1 == count($this->remainingPlayerIdArray()));
    }

    /**
     * Validate buttons chosen by a player
     *
     * @param array $buttonIdArray
     * @return bool
     */
    public function validate_button_choice($buttonIdArray) {
        if (1 != count($buttonIdArray)) {
            return FALSE;
        }

        if (empty($buttonIdArray[0])) {
            return FALSE;
        }

        return TRUE;
    }

    // utility methods

    public function __construct(
        $tournamentId = 0
    ) {
        parent::__construct($tournamentId);
        $this->type = 'SingleElimination';
    }
}
