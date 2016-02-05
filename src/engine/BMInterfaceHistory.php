<?php

/**
 * BMInterfaceHistory: interface between GUI and BMGame for history-related requests
 *
 * @author james
 */

/**
 * This class deals with communication between the UI, the game code, and the database
 * pertaining to history-related information
 */
class BMInterfaceHistory extends BMInterface {
    /**
     * Parse the search filters, converting them to standardized forms (such
     * as converting names to ID's), and validating them against the database
     *
     * @param array $searchParameters
     * @return array|NULL
     */
    protected function assemble_search_filters($searchParameters) {
        try {
            $searchFilters = array();

            if (isset($searchParameters['gameId'])) {
                $searchFilters['gameId'] = (int)$searchParameters['gameId'];
            }

            $arePlayerNamesValid = $this->set_playerNames($searchFilters, $searchParameters);
            if (!$arePlayerNamesValid) {
                return NULL;
            }

            $areButtonNamesValid = $this->set_buttonNames($searchFilters, $searchParameters);
            if (!$areButtonNamesValid) {
                return NULL;
            }

            $this->set_gameStart_limits($searchFilters, $searchParameters);
            $this->set_lastMove_limits($searchFilters, $searchParameters);

            if (isset($searchParameters['winningPlayer'])) {
                $searchFilters['winningPlayer'] = $searchParameters['winningPlayer'];
            }

            if (isset($searchParameters['status'])) {
                $searchFilters['status'] = $searchParameters['status'];
            }

            return $searchFilters;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::assemble_search_filters: ' .
                $e->getMessage()
            );
            $this->set_message('Game search failed.');
            return NULL;
        }
    }

    /**
     * Set player name search filters
     *
     * @param array $searchFilters
     * @param array $searchParameters
     * @return bool
     */
    protected function set_playerNames(&$searchFilters, $searchParameters) {
        if (isset($searchParameters['playerNameA'])) {
            $playerIdA = $this->get_player_id_from_name($searchParameters['playerNameA']);
            if (is_int($playerIdA)) {
                $searchFilters['playerIdA'] = $playerIdA;
            } else {
                $this->set_message('Player A: ' . $this->message);
                return FALSE;
            }
        }

        if (isset($searchParameters['playerNameB'])) {
            $playerIdB = $this->get_player_id_from_name($searchParameters['playerNameB']);
            if (is_int($playerIdB)) {
                $searchFilters['playerIdB'] = $playerIdB;
            } else {
                $this->set_message('Player B: ' . $this->message);
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Set button name search filters
     *
     * @param array $searchFilters
     * @param array $searchParameters
     * @return bool
     */
    protected function set_buttonNames(&$searchFilters, $searchParameters) {
        if (isset($searchParameters['buttonNameA'])) {
            $buttonIdA = $this->get_button_id_from_name($searchParameters['buttonNameA']);
            if (is_int($buttonIdA)) {
                $searchFilters['buttonIdA'] = $buttonIdA;
            } else {
                $this->set_message('Button A: ' . $this->message);
                return FALSE;
            }
        }

        if (isset($searchParameters['buttonNameB'])) {
            $buttonIdB = $this->get_button_id_from_name($searchParameters['buttonNameB']);
            if (is_int($buttonIdB)) {
                $searchFilters['buttonIdB'] = $buttonIdB;
            } else {
                $this->set_message('Button B: ' . $this->message);
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * Set game start search filters
     *
     * @param array $searchFilters
     * @param array $searchParameters
     */
    protected function set_gameStart_limits(&$searchFilters, $searchParameters) {
        if (isset($searchParameters['gameStartMin'])) {
            $searchFilters['gameStartMin'] = (int)$searchParameters['gameStartMin'];
        }
        if (isset($searchParameters['gameStartMax'])) {
            $searchFilters['gameStartMax'] = (int)$searchParameters['gameStartMax'];
        }
    }

    /**
     * Set last move search filters
     *
     * @param array $searchFilters
     * @param array $searchParameters
     */
    protected function set_lastMove_limits(&$searchFilters, $searchParameters) {
        if (isset($searchParameters['lastMoveMin'])) {
            $searchFilters['lastMoveMin'] = (int)$searchParameters['lastMoveMin'];
        }
        if (isset($searchParameters['lastMoveMax'])) {
            $searchFilters['lastMoveMax'] = (int)$searchParameters['lastMoveMax'];
        }
    }

    /**
     * Parse out the additional options that affect how search results
     * are to be presented
     *
     * @param array $searchParameters
     * @return array|NULL
     */
    protected function assemble_search_options($searchParameters) {
        try {
            $searchOptions = array();

            if (isset($searchParameters['sortColumn'])) {
                $searchOptions['sortColumn'] = $searchParameters['sortColumn'];
            }
            if (isset($searchParameters['sortDirection'])) {
                $searchOptions['sortDirection'] = $searchParameters['sortDirection'];
            }
            if (isset($searchParameters['numberOfResults'])) {
                $numberOfResults = (int)$searchParameters['numberOfResults'];
                if ($numberOfResults <= 1000) {
                    $searchOptions['numberOfResults'] = $numberOfResults;
                } else {
                    $this->set_message('numberOfResults may not exceed 1000');
                    return NULL;
                }
            }
            if (isset($searchParameters['page'])) {
                $searchOptions['page'] = (int)$searchParameters['page'];
            }

            return $searchOptions;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::assemble_search_options: ' .
                $e->getMessage()
            );
            $this->set_message('Game search failed.');
            return NULL;
        }
    }

    /**
     * Get all games matching the specified search parameters
     *
     * @param int $currentPlayerId
     * @param array $args
     * @return array|NULL
     */
    public function search_game_history($currentPlayerId, $args) {
        $combinedQuery = '';

        try {
            $searchFilters = $this->assemble_search_filters($args);
            $searchOptions = $this->assemble_search_options($args);

            if ($searchFilters === NULL || $searchOptions === NULL) {
                return NULL;
            }

            // We're going to build (and then UNION) two queries: one where we
            // check all the Player A filters against the player in position 0
            // and all the Player B filters against the player and position 1,
            // and one where we do the opposite.
            // (If we just did it with OR clauses, then we wouldn't know at the
            // end which player matched which.)

            $where = 'WHERE 1=1 ';
            $whereParameters = array();
            $this->apply_all_filters($searchFilters, $where, $whereParameters);

            // I want to use the same WHERE clause for both sides of the
            // UNION, but PHP won't let us use the same parameter twice in
            // a query (without PDO::ATTR_EMULATE_PREPARES).
            // So I've used _%%% as a placeholder which I'm now replacing
            // with _0 and _1, to produce the two otherwise identical
            // versions.
            $where_0 = str_replace('_%%%', '_0', $where);
            $where_1 = str_replace('_%%%', '_1', $where);
            $whereParameters_0 = array();
            $whereParameters_1 = array();
            foreach ($whereParameters as $parameterName => $parameterValue) {
                $whereParameters_0[str_replace('_%%%', '_0', $parameterName)] =
                    $parameterValue;
                $whereParameters_1[str_replace('_%%%', '_1', $parameterName)] =
                    $parameterValue;
            }

            $sort = 'ORDER BY ';
            $this->apply_order_by($searchOptions, $sort);

            $limit = 'LIMIT :offset, :page_size ';
            $limitParameters = array();
            $this->apply_limit($searchOptions, $limitParameters);

            $combinedQuery = $this->game_query($where_0, $where_1, $sort, $limit);
            $games = array();
            $this->execute_game_query(
                $combinedQuery,
                $currentPlayerId,
                $whereParameters_0,
                $whereParameters_1,
                $limitParameters,
                $games
            );

            $combinedQuery = $this->summary_query($where_0, $where_1);
            $summary = array();
            $this->execute_summary_query(
                $combinedQuery,
                $whereParameters_0,
                $whereParameters_1,
                $summary
            );

            $this->set_message('Sought games retrieved successfully.');
            return array('games' => $games, 'summary' => $summary);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::search_game_history: ' .
                $e->getMessage() .
                ' -- Full SQL query: ' . $combinedQuery
            );
            $this->set_message('Game search failed.');
            return NULL;
        }
    }

    /**
     * Create base SQL query for history search
     *
     * @return string
     */
    protected function base_query() {
        return  'SELECT ' .
                    'g.id AS game_id, ' .
                    'vA.player_id AS player_id_A, ' .
                    'vA.player_name AS player_name_A, ' .
                    'vA.button_name AS button_name_A, ' .
                    'vA.is_awaiting_action AS waiting_on_A, '.
                    'vB.player_id AS player_id_B, ' .
                    'vB.player_name AS player_name_B, ' .
                    'vB.button_name AS button_name_B, ' .
                    'vB.is_awaiting_action AS waiting_on_B, '.
                    'UNIX_TIMESTAMP(g.start_time) AS game_start, ' .
                    'UNIX_TIMESTAMP(g.last_action_time) AS last_move, ' .
                    'vA.n_rounds_won AS rounds_won_A, ' .
                    'vB.n_rounds_won AS rounds_won_B, ' .
                    'vA.n_rounds_drawn AS rounds_drawn, ' .
                    'g.n_target_wins AS target_wins, ' .
                    's.name AS status ' .
                'FROM game AS g ' .
                    'INNER JOIN game_status AS s ON s.id = g.status_id ';
    }

    /**
     * Create inner join for the first player
     *
     * @return string
     */
    protected function player_join_0() {
        return  'INNER JOIN game_player_view AS vA ' .
                    'ON vA.game_id = g.id AND vA.position = 0 ' .
                'INNER JOIN game_player_view AS vB ' .
                    'ON vB.game_id = g.id AND vB.position = 1 ';
    }

    /**
     * Create inner join for the second player
     *
     * @return string
     */
    protected function player_join_1() {
        return  'INNER JOIN game_player_view AS vA ' .
                    'ON vA.game_id = g.id AND vA.position = 1 ' .
                'INNER JOIN game_player_view AS vB ' .
                    'ON vB.game_id = g.id AND vB.position = 0 ';
    }

    /**
     * Add all filters to WHERE conditions
     *
     * @param array $searchFilters
     * @param string $where
     * @param array $whereParameters
     */
    protected function apply_all_filters($searchFilters, &$where, &$whereParameters) {
        $this->apply_filter($searchFilters, 'gameId', 'g.id', 'game_id_%%%', $where, $whereParameters);
        $this->apply_filter($searchFilters, 'playerIdA', 'vA.player_id', 'player_id_A_%%%', $where, $whereParameters);
        $this->apply_filter($searchFilters, 'buttonIdA', 'vA.button_id', 'button_id_A_%%%', $where, $whereParameters);
        $this->apply_filter($searchFilters, 'playerIdB', 'vB.player_id', 'player_id_B_%%%', $where, $whereParameters);
        $this->apply_filter($searchFilters, 'buttonIdB', 'vB.button_id', 'button_id_B_%%%', $where, $whereParameters);

        if (isset($searchFilters['gameStartMin'])) {
            $where .= 'AND UNIX_TIMESTAMP(g.start_time) >= :game_start_min_%%% ';
            $whereParameters[':game_start_min_%%%'] = $searchFilters['gameStartMin'];
        }
        if (isset($searchFilters['gameStartMax'])) {
            $where .= 'AND UNIX_TIMESTAMP(g.start_time) < :game_start_max_%%% ';
            // We want the range to end at the *end* of the day (i.e.,
            // the start of the next one).
            $whereParameters[':game_start_max_%%%'] =
                $searchFilters['gameStartMax'] + 24 * 60 * 60;
        }

        if (isset($searchFilters['lastMoveMin'])) {
            $where .= 'AND UNIX_TIMESTAMP(g.last_action_time) >= :last_move_min_%%% ';
            $whereParameters[':last_move_min_%%%'] = $searchFilters['lastMoveMin'];
        }
        if (isset($searchFilters['lastMoveMax'])) {
            $where .= 'AND UNIX_TIMESTAMP(g.last_action_time) < :last_move_max_%%% ';
            // We want the range to end at the *end* of the day (i.e.,
            // the start of the next one).
            $whereParameters[':last_move_max_%%%'] =
                $searchFilters['lastMoveMax'] + 24 * 60 * 60;
        }

        if (isset($searchFilters['winningPlayer'])) {
            if ($searchFilters['winningPlayer'] == 'A') {
                $where .= 'AND vA.n_rounds_won > vB.n_rounds_won ';
            } elseif ($searchFilters['winningPlayer'] == 'B') {
                $where .= 'AND vA.n_rounds_won < vB.n_rounds_won ';
            } elseif ($searchFilters['winningPlayer'] == 'Tie') {
                $where .= 'AND vA.n_rounds_won = vB.n_rounds_won ';
            }
        }

        if (isset($searchFilters['status'])) {
            $where .= 'AND s.name = :status_%%% ';
            $whereParameters[':status_%%%'] = $searchFilters['status'];
        } else {
            // We'll only display games that have actually started
            $where .= 'AND (s.name = "COMPLETE" ' .
                      'OR s.name = "ACTIVE" ' .
                      'OR s.name = "CANCELLED") ';
        }
    }

    /**
     * Add a specific filter to WHERE conditions
     *
     * @param array $searchFilters
     * @param string $searchFilterType
     * @param string $whereKeyStr
     * @param string $whereParameterStr
     * @param string $where
     * @param array $whereParameters
     */
    protected function apply_filter(
        $searchFilters,
        $searchFilterType,
        $whereKeyStr,
        $whereParameterStr,
        &$where,
        &$whereParameters
    ) {
        if (isset($searchFilters[$searchFilterType])) {
            $where .= 'AND ' . $whereKeyStr . ' = :' . $whereParameterStr . ' ';
            $whereParameters[':' . $whereParameterStr] = $searchFilters[$searchFilterType];
        }
    }

    /**
     * Customise sort order
     *
     * @param array $searchOptions
     * @param string $sort
     */
    protected function apply_order_by($searchOptions, &$sort) {
        switch($searchOptions['sortColumn']) {
            case 'gameId':
                $sort .= 'game_id ';
                break;
            case 'playerNameA':
                $sort .= 'player_name_A ';
                break;
            case 'buttonNameA':
                $sort .= 'button_name_A ';
                break;
            case 'playerNameB':
                $sort .= 'player_name_B ';
                break;
            case 'buttonNameB':
                $sort .= 'button_name_B ';
                break;
            case 'gameStart':
                $sort .= 'game_start ';
                break;
            case 'lastMove':
                $sort .= 'last_move ';
                break;
            case 'winningPlayer':
                // We want to rank games where A has already won the
                // highest, followed by games in progress, followed by
                // games where B has already won. And within those, we
                // should rank by how many rounds A is ahead or behind by.
                $sort .=
                    '1000 * (rounds_won_A >= target_wins) + ' .
                    'CAST(rounds_won_A AS SIGNED INTEGER) - ' .
                        'CAST(rounds_won_B AS SIGNED INTEGER) + ' .
                    '-1000 * (rounds_won_B >= target_wins) ';
                break;
            case 'status':
                $sort .= 'status ';
                break;
        }
        switch($searchOptions['sortDirection']) {
            case 'ASC':
                $sort .= 'ASC ';
                break;
            case 'DESC':
                $sort .= 'DESC ';
                break;
        }
    }

    /**
     * Determine limit parameters
     *
     * @param array $searchOptions
     * @param array $limitParameters
     */
    protected function apply_limit($searchOptions, &$limitParameters) {
        $limitParameters[':offset'] =
            ($searchOptions['page'] - 1) * $searchOptions['numberOfResults'];
        $limitParameters[':page_size'] = $searchOptions['numberOfResults'];
    }

    /**
     * Create full game query
     *
     * @param string $where_0
     * @param string $where_1
     * @param string $sort
     * @param string $limit
     * @return string
     */
    protected function game_query($where_0, $where_1, $sort, $limit) {
        return  'SELECT * FROM (( ' .
                    $this->base_query() . $this->player_join_0() . $where_0 .
                ') UNION (' .
                    $this->base_query() . $this->player_join_1() . $where_1 .
                ')) AS games ' .
                'GROUP BY game_id ' . $sort . $limit . ';';
    }

    /**
     * Execute the game query
     *
     * @param string $combinedGameQuery
     * @param int $currentPlayerId
     * @param array $whereParameters_0
     * @param array $whereParameters_1
     * @param array $limitParameters
     * @param array $games
     */
    protected function execute_game_query(
        $combinedGameQuery,
        $currentPlayerId,
        $whereParameters_0,
        $whereParameters_1,
        $limitParameters,
        &$games
    ) {
        $statement = self::$conn->prepare($combinedGameQuery);
        $statement->execute(array_merge($whereParameters_0, $whereParameters_1, $limitParameters));

        $playerColors = $this->load_player_colors($currentPlayerId);

        while ($row = $statement->fetch()) {
            $gameColors = $this->determine_game_colors(
                $currentPlayerId,
                $playerColors,
                (int)$row['player_id_A'],
                (int)$row['player_id_B']
            );

            $games[] = array(
                'gameId' => (int)$row['game_id'],
                'playerIdA' => (int)$row['player_id_A'],
                'playerNameA' => $row['player_name_A'],
                'buttonNameA' => $row['button_name_A'],
                'waitingOnA' => ($row['waiting_on_A'] == 1),
                'colorA' => $gameColors['playerA'],
                'playerIdB' => (int)$row['player_id_B'],
                'playerNameB' => $row['player_name_B'],
                'buttonNameB' => $row['button_name_B'],
                'waitingOnB' => ($row['waiting_on_B'] == 1),
                'colorB' => $gameColors['playerB'],
                'gameStart' => (int)$row['game_start'],
                'lastMove' => (int)$row['last_move'],
                'roundsWonA' => (int)$row['rounds_won_A'],
                'roundsWonB' => (int)$row['rounds_won_B'],
                'roundsDrawn' => (int)$row['rounds_drawn'],
                'targetWins' => (int)$row['target_wins'],
                'status' => $row['status'],
            );
        }
    }

    /**
     * Create summary query
     *
     * @param array $where_0
     * @param array $where_1
     * @return array
     */
    protected function summary_query($where_0, $where_1) {
        return  'SELECT ' .
                    'COUNT(*) AS matches_found, ' .
                    'MIN(game_start) AS earliest_start, ' .
                    'MAX(last_move) AS latest_move, ' .
                    'SUM(rounds_won_A >= target_wins) AS games_won_A, ' .
                    'SUM(rounds_won_B >= target_wins) AS games_won_B, ' .
                    'SUM(status = "COMPLETE") AS games_completed ' .
                'FROM (' .
                    'SELECT * FROM (( ' .
                        $this->base_query() . $this->player_join_0() . $where_0 .
                    ') UNION (' .
                        $this->base_query() . $this->player_join_1() . $where_1 .
                    ')) AS games ' .
                    'GROUP BY game_id ' .
                ') AS summary;';
    }

    /**
     * Execute summary query
     *
     * @param string $combinedQuery
     * @param array $whereParameters_0
     * @param array $whereParameters_1
     * @param array $summary
     */
    protected function execute_summary_query(
        $combinedQuery,
        $whereParameters_0,
        $whereParameters_1,
        &$summary
    ) {
        $statement = self::$conn->prepare($combinedQuery);
        $statement->execute(array_merge($whereParameters_0, $whereParameters_1));

        $summaryRows = $statement->fetchAll();
        // If it fails mysteriously, it's probably better to ignore that
        // and still return the games list than to error out and return
        // nothing
        if (count($summaryRows) == 1) {
            $summary['matchesFound'] = (int)$summaryRows[0]['matches_found'];
            if ($summaryRows[0]['earliest_start'] == NULL) {
                $summary['earliestStart'] = NULL;
            } else {
                $summary['earliestStart'] = (int)$summaryRows[0]['earliest_start'];
            }
            if ($summaryRows[0]['latest_move'] == NULL) {
                $summary['latestMove'] = NULL;
            } else {
                $summary['latestMove'] = (int)$summaryRows[0]['latest_move'];
            }
            $summary['gamesWonA'] = (int)$summaryRows[0]['games_won_A'];
            $summary['gamesWonB'] = (int)$summaryRows[0]['games_won_B'];
            $summary['gamesCompleted'] = (int)$summaryRows[0]['games_completed'];
        } else {
            $this->set_message('Retrieving summary data for history search failed');
            error_log(
                $this->message .
                ' in BMInterface::search_game_history' .
                ' -- Full SQL query: ' . $combinedQuery
            );
        }
    }
}
