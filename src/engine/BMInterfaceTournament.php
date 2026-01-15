<?php

/**
 * BMInterfaceTournament: interface between GUI and BMTournament for all tournament-related requests
 *
 * @author james
 */

/**
 * This class deals with communication between the UI, the tournament code, and the database
 */
class BMInterfaceTournament extends BMInterface {

    /**
     * Check whether a tournament is being watched
     *
     * @param int $playerId
     * @param int $tournamentId
     * @return bool|null
     */
    public function is_tournament_watched($playerId, $tournamentId) {
        try {
            $query = 'SELECT COUNT(*) FROM tournament_player_watch_map ' .
                    'WHERE tournament_id = :tournament_id AND player_id = :player_id';
            $parameters = array(':tournament_id' => $tournamentId,
                ':player_id' => $playerId);
            $count = self::$db->select_single_value($query, $parameters, 'int');
            return $count > 0;
        } catch (BMExceptionDatabase $e) {
            $this->set_message(
                'Cannot determine if tournament is watched because a player or tournament ID was not valid'
            );
            return NULL;
        } catch (Exception $e) {
            $this->set_message('Attempt to determine if tournament is watched failed: ' . $e->getMessage());
            error_log(
                'Caught exception in BMInterface::is_tournament_watched: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Make a tournament visible on the overview page
     *
     * @param int $playerId
     * @param int $tournamentId
     * @return null|bool
     */
    protected function watch_tournament($playerId, $tournamentId) {
        try {
            if ($this->is_tournament_watched($playerId, $tournamentId)) {
                $this->set_message('Tournament was already being watched');
                return NULL;
            }

            $query = 'INSERT INTO tournament_player_watch_map (tournament_id, player_id) ' .
                    'SELECT :tournament_id1, :player_id1 ' .
                    'WHERE NOT EXISTS ' .
                    '(SELECT * FROM tournament_player_watch_map ' .
                    ' WHERE tournament_id = :tournament_id2 AND player_id = :player_id2)';
            $parameters = array(':tournament_id1' => $tournamentId,
                ':player_id1' => $playerId,
                ':tournament_id2' => $tournamentId,
                ':player_id2' => $playerId);
            self::$db->update($query, $parameters);

            return TRUE;
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Tournament watch set failed because a player or tournament ID was not valid');
            return NULL;
        } catch (Exception $e) {
            $this->set_message('Watch failed: ' . $e->getMessage());
            error_log(
                'Caught exception in BMInterface::watch_tournament: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Make a tournament not visible on the overview page
     *
     * @param int $playerId
     * @param int $tournamentId
     * @return null|bool
     */
    protected function unwatch_tournament($playerId, $tournamentId) {
        try {
            if (!($this->is_tournament_watched($playerId, $tournamentId))) {
                $this->set_message('Tournament was not being followed');
                return NULL;
            }

            $query = 'DELETE FROM tournament_player_watch_map ' .
                     'WHERE player_id = :player_id AND tournament_id = :tournament_id';

            $statement = self::$conn->prepare($query);
            $parameters = array(
                ':player_id' => $playerId,
                ':tournament_id' => $tournamentId,
            );
            self::$db->update($query, $parameters);

            return TRUE;
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Tournament watch unset failed because a player or tournament ID was not valid');
            return NULL;
        } catch (Exception $e) {
            $this->set_message('Unwatch failed: ' . $e->getMessage());
            error_log(
                'Caught exception in BMInterface::unwatch_tournament: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Create a tournament
     *
     * @param int $creatorId
     * @param string $type
     * @param int $nPlayers
     * @param int $maxWins
     * @param string $description
     * @return array|FALSE
     */
    public function create_tournament(
        $creatorId,
        $type,
        $nPlayers,
        $maxWins = 3,
        $description = ''
    ) {
        if (!$this->cast('BMInterfaceGame')->validate_max_wins($maxWins)) {
            $this->set_message('Tournament create failed because the maximum number of wins was invalid');
            return NULL;
        }

        if (!$this->validate_n_players($type, $nPlayers)) {
            return NULL;
        }

        try {
            $tournamentId = $this->insert_new_tournament(
                $creatorId,
                $type,
                $nPlayers,
                $maxWins,
                $description
            );

            if (!$tournamentId) {
                return NULL;
            }

            $this->watch_tournament($creatorId, $tournamentId);

            // update tournament state to latest possible
            $tournament = $this->load_tournament($tournamentId);
            if (!($tournament instanceof BMTournament)) {
                throw new Exception(
                    "Could not load newly-created tournament $tournamentId"
                );
            }

            if (!$this->save_tournament($tournament)) {
                return NULL;
            }

            $this->set_message("Tournament $tournamentId created successfully");

            return array('tournamentId' => $tournamentId);
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Tournament create failed because the creator ID was not valid');
            return NULL;
        } catch (Exception $e) {
            $this->set_message('Tournament create failed: ' . $e->getMessage());
            error_log(
                'Caught exception in BMInterface::create_tournament: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Check that the number of players is valid for the type of tournament specified.
     *
     * @param string $type
     * @param int $nPlayers
     * @return bool
     */
    protected function validate_n_players($type, $nPlayers) {
        $tournament = BMTournament::create($type);

        if (is_null($tournament)) {
            $this->set_message('Invalid tournament type');
            return FALSE;
        }

        $isValid = $tournament->validate_n_players($nPlayers);

        if (!$isValid) {
            $this->set_message('Invalid number of players for this tournament type');
        }

        return $isValid;
    }

    /**
     * Insert a new tournament into the database
     *
     * @param int $creatorId
     * @param string $type
     * @param int $nPlayers
     * @param int $maxWins
     * @param string $description
     * @return int|FALSE
     */
    protected function insert_new_tournament(
        $creatorId,
        $type,
        $nPlayers,
        $maxWins,
        $description
    ) {
        try {
            // create basic game details
            $query = 'INSERT INTO tournament ' .
                    '    (status_id, ' .
                    '     tournament_state, ' .
                    '     round_number, ' .
                    '     n_players, ' .
                    '     n_target_wins, ' .
                    '     tournament_type, ' .
                    '     creator_id, ' .
                    '     start_time, ' .
                    '     description) ' .
                    'VALUES ' .
                    '    ((SELECT id FROM tournament_status WHERE name = :status), ' .
                    '     :tournament_state, ' .
                    '     :round_number, ' .
                    '     :n_players, ' .
                    '     :n_target_wins, ' .
                    '     :tournament_type, ' .
                    '     :creator_id, ' .
                    '     FROM_UNIXTIME(:start_time), ' .
                    '     :description)';
            $parameters = array(':status' => 'OPEN',
                ':tournament_state' => BMTournamentState::START_TOURNAMENT,
                ':round_number' => 1,
                ':n_players' => $nPlayers,
                ':n_target_wins' => $maxWins,
                ':tournament_type' => $type,
                ':creator_id' => $creatorId,
                ':start_time' => time(),
                ':description' => $description);
            self::$db->update($query, $parameters);

            $tournamentId = self::$db->select_single_value('SELECT LAST_INSERT_ID()', array(), 'int');

            return $tournamentId;
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Cannot insert tournament because the creator ID was not valid');
            return NULL;
        } catch (Exception $e) {
            $this->set_message('Tournament create failed: ' . $e->getMessage());
            error_log(
                'Caught exception in BMInterface::insert_new_tournament: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Load a tournament from the database
     *
     * @param int $tournamentId
     * @return NULL|BMTournament
     */
    protected function load_tournament($tournamentId) {
        try {
            $tournament = $this->load_tournament_parameters($tournamentId);

            // check whether the game exists
            if (!isset($tournament)) {
                $this->set_message("Tournament $tournamentId does not exist.");
                return NULL;
            }

            if (('' === $this->message) ||
                    ('Loaded data for game' === substr($this->message, 0, 20))) {
                $this->set_message("Loaded data for tournament $tournamentId.");
            }

            return $tournament;
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Cannot load tournament the tournament ID was not valid');
            return NULL;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_tournament: ' .
                $e->getMessage()
            );
            $this->set_message("Internal error while loading tournament.");
            return NULL;
        }
    }

    /**
     * Most of the tournament loading logic
     *
     * @param int $tournamentId
     * @return NULL|BMTournament
     */
    protected function load_tournament_parameters($tournamentId) {
        $query = 'SELECT t.tournament_state,' .
                't.round_number,' .
                't.n_players,' .
                't.n_target_wins,' .
                't.tournament_type,' .
                't.creator_id,' .
                't.description,' .
                'g.id AS game_id,' .
                'g.tournament_round_number AS tournament_round_number ' .
                'FROM tournament AS t ' .
                'LEFT JOIN game AS g ' .
                'ON t.id = g.tournament_id ' .
                'WHERE t.id = :tournament_id ' .
                'ORDER BY g.id;';
        $parameters = array(':tournament_id' => $tournamentId);
        $columnReturnTypes = array(
            'tournament_state' => 'int',
            'round_number' => 'int',
            'n_players' => 'int',
            'n_target_wins' => 'int',
            'tournament_type' => 'str',
            'creator_id' => 'int',
            'description' => 'str',
            'game_id' => 'int_or_null',
            'tournament_round_number' => 'int_or_null',
        );

        $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);

        foreach ($rows as $row) {
            // load tournament attributes
            if (!isset($tournament)) {
                $tournament = BMTournament::create($row['tournament_type']);
                $tournament->tournamentId = $tournamentId;
                $this->load_tournament_attributes($tournament, $row);
            }

            if (!is_null($row['game_id'])) {
                $gameIdArrayArray = $tournament->gameIdArrayArray;
                $gameIdArrayArray[$row['tournament_round_number'] - 1][] = $row['game_id'];
                $tournament->gameIdArrayArray = $gameIdArrayArray;

                $gameArrayArray = $tournament->gameArrayArray;
                $gameArrayArray[$row['tournament_round_number'] - 1][] = $this->load_game($row['game_id']);
                $tournament->gameArrayArray = $gameArrayArray;
            }
        }

        if (!isset($tournament)) {
            return NULL;
        }

        $this->load_tournament_participants($tournament);

        return $tournament;
    }

    /**
     * Logic to assign tournament parameters
     *
     * @param BMTournament $tournament
     * @param array $row
     */
    protected function load_tournament_attributes(BMTournament $tournament, array $row) {
        $tournament->tournamentState = $row['tournament_state'];
        $tournament->roundNumber = $row['round_number'];
        $tournament->nPlayers = $row['n_players'];
        $tournament->gameMaxWins = $row['n_target_wins'];
        $tournament->creatorId = $row['creator_id'];
        $tournament->description = $row['description'];
    }

    /**
     * Load tournament participants into a BMTournament
     *
     * @param BMTournament $tournament
     */
    protected function load_tournament_participants(BMTournament $tournament) {
        if (is_null($tournament)) {
            return;
        }

        $query = 'SELECT t.player_id,' .
                't.button_id,' .
                't.remain_count ' .
                'FROM tournament_player_map AS t ' .
                'WHERE t.tournament_id = :tournament_id ' .
                'ORDER BY t.position;';
        $parameters = array(':tournament_id' => $tournament->tournamentId);
        $columnReturnTypes = array(
            'player_id' => 'int',
            'button_id' => 'int',
            'remain_count' => 'int',
        );
        $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);

        $remainCountArray = array();

        foreach ($rows as $row) {
            // load tournament participants
            $tournament->add_player(
                $row['player_id'],
                array($row['button_id'])
            );
            $remainCountArray[] = $row['remain_count'];
        }

        $tournament->remainCountArray = $remainCountArray;
    }

    /**
     * Check whether a player is in a tournament
     *
     * @param int $playerId
     * @param int $tournamentId
     * @return bool
     */
    protected function is_player_in_tournament($playerId, $tournamentId) {
        if (($playerId <= 0) || ($tournamentId <= 0)) {
            return FALSE;
        }

        $query = 'SELECT t.player_id ' .
                'FROM tournament_player_map AS t ' .
                'WHERE t.player_id = :player_id ' .
                'AND t.tournament_id = :tournament_id;';
        $parameters = array(
            ':player_id' => $playerId,
            ':tournament_id' => $tournamentId
        );
        $columnReturnTypes = array(
            'player_id' => 'int',
        );
        $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
        $is_player_in_tournament = count($rows) > 0;

        return $is_player_in_tournament;
    }

    /**
     * Save a tournament to the database
     *
     * @param BMTournament $tournament
     * @return bool
     */
    protected function save_tournament(BMTournament $tournament) {
        // force tournament to proceed to the latest possible before saving
        $tournament->proceed_to_next_user_action();

        try {
            $this->generate_new_games($tournament);
            $this->save_basic_tournament_parameters($tournament);
            $this->save_player_parameters($tournament);
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Cannot save tournament because the tournament ID was not valid');
            return NULL;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_tournament: ' .
                $e->getMessage()
            );
            $this->set_message("Tournament save failed: $e");
            return NULL;
        }

        return TRUE;
    }

    /**
     * Generate new games required by a tournament
     *
     * @param BMTournament $tournament
     */
    protected function generate_new_games(BMTournament $tournament) {
        if (!isset($tournament->gameDataToBeCreatedArray) ||
                (0 == count($tournament->gameDataToBeCreatedArray))) {
            return;
        }

        foreach ($tournament->gameDataToBeCreatedArray as $gameData) {
            $buttonNames = $this->game()->retrieve_button_names(
                array($gameData['buttonId1'], $gameData['buttonId2'])
            );

            $roundDescription = 'Tournament Round ' . $gameData['roundNumber'];
            $tournDescription = $tournament->description;

            if ('' == trim($tournDescription)) {
                $tournDescription = $roundDescription;
            } else {
                $tournDescription = $tournDescription . ' • ' . $roundDescription;
            }

            $interfaceResponse = $this->game()->create_game_from_button_ids(
                array($gameData['playerId1'], $gameData['playerId2']),
                array($gameData['buttonId1'], $gameData['buttonId2']),
                $buttonNames,
                $tournament->gameMaxWins,
                $tournDescription,
                NULL,
                0, // needs to be non-null, but also a non-player ID
                TRUE,
                array(),
                $tournament->tournamentId,
                $gameData['roundNumber']
            );

            // add game number to $this->gameIdArrayArray
            if (is_array($interfaceResponse) && array_key_exists('gameId', $interfaceResponse)) {
                $gameIdArrayArray = $tournament->gameIdArrayArray;
                $gameIdArrayArray[$gameData['roundNumber'] - 1][] = $interfaceResponse['gameId'];
                $tournament->gameIdArrayArray = $gameIdArrayArray;
            }
        }
    }

    /**
     * Most of the tournament saving logic
     *
     * @param BMTournament $tournament
     */
    protected function save_basic_tournament_parameters(BMTournament $tournament) {
        $query = 'UPDATE tournament ' .
                'SET status_id = ' .
                '        (SELECT id FROM tournament_status WHERE name = :status),' .
                '    tournament_state = :tournament_state,' .
                '    round_number = :round_number ' .
                'WHERE id = :tournament_id;';
        $parameters = array(':status' => $this->get_tournament_status($tournament),
            ':tournament_state' => $tournament->tournamentState,
            ':round_number' => $tournament->roundNumber,
            ':tournament_id' => $tournament->tournamentId);
        self::$db->update($query, $parameters);
    }

    /**
     * Logic to save player parameters
     *
     * @param BMTournament $tournament
     */
    protected function save_player_parameters(BMTournament $tournament) {
        // james: In adding the ability to save button selection, I've taken the shortcut
        //        of assuming that only one button is being selected at the moment

        foreach ($tournament->playerIdArray as $playerIdx => $playerId) {
            $query = 'UPDATE tournament_player_map ' .
                    'SET position = :position,' .
                    '    remain_count = :remain_count,' .
                    '    button_id = :button_id ' .
                    'WHERE tournament_id = :tournament_id ' .
                    'AND player_id = :player_id;';
            $parameters = array(':position' => $playerIdx + 1,
                ':remain_count' => $tournament->remainCountArray[$playerIdx],
                ':button_id' => $tournament->buttonIdArrayArray[$playerId][0],
                ':tournament_id' => $tournament->tournamentId,
                ':player_id' => $playerId);
            self::$db->update($query, $parameters);
        }
    }

    /**
     * Force a tournament to advance if possible
     *
     * @param int $tournamentId
     */
    protected function advance_tournament($tournamentId) {
        // james: we really don't want to show feedback on what's going on
        //        behind the scenes when a tournament is advancing
        $message = $this->message;
        $tournament = $this->load_tournament($tournamentId);
        $this->save_tournament($tournament);
        $this->set_message($message);
    }

    /**
     * Retrieve the status of a tournament
     *
     * @param BMTournament $tournament
     * @return string
     */
    protected function get_tournament_status(BMTournament $tournament) {
        if (BMTournamentState::END_TOURNAMENT == $tournament->tournamentState) {
            $status = 'COMPLETE';
        } elseif (BMTournamentState::CANCELLED == $tournament->tournamentState) {
            $status = 'CANCELLED';
        } elseif (BMTournamentState::JOIN_TOURNAMENT == $tournament->tournamentState) {
            $status = 'OPEN';
        } else {
            $status = 'ACTIVE';
        }

        return $status;
    }

    /**
     * Check whether a tournament has ended, either through completion or cancellation
     *
     * @param int $tournamentId
     * @return bool
     */
    protected function is_tournament_finished($tournamentId) {
        $tournament = $this->load_tournament($tournamentId);
        $tournamentState = $tournament->tournamentState;

        return (BMTournamentState::END_TOURNAMENT == $tournamentState) ||
               (BMTournamentState::CANCELLED == $tournamentState);
    }

    /**
     * Perform a user action on a tournament
     *
     * @param int $userId
     * @param int $tournamentId
     * @param string $action
     * @param array $buttonNameArray
     * @return bool|null
     */
    public function act_on_tournament($userId, $tournamentId, $action, $buttonNameArray = NULL) {
        switch ($action) {
            case 'join':
                return $this->join_tournament($userId, $tournamentId, $buttonNameArray);
            case 'leave':
                return $this->leave_tournament($userId, $tournamentId);
            case 'cancel':
                return $this->cancel_tournament($userId, $tournamentId);
            case 'changeButton':
                return $this->change_button_in_tournament($userId, $tournamentId, $buttonNameArray);
            default:
                $this->set_message('Invalid action on tournament');
                return NULL;
        }
    }

    /**
     * Attempt to add a player to a tournament
     *
     * @param int $userId
     * @param int $tournamentId
     * @param array $buttonNameArray
     * @return bool|null
     */
    protected function join_tournament($userId, $tournamentId, $buttonNameArray) {
        try {
            $tournament = $this->load_tournament($tournamentId);

            // convert button names to button IDs
            $buttonIdArray = $this->game()->retrieve_button_ids(
                array_fill(0, count($buttonNameArray), $userId),
                $buttonNameArray
            );

            if (!$this->validate_join_tournament($userId, $tournamentId, $tournament, $buttonIdArray)) {
                return NULL;
            }

            $this->resolve_random_button_selection_tournament($buttonIdArray);

            $success = $this->add_user_to_tournament($userId, $tournamentId, $tournament->nPlayers, $buttonIdArray);

            if (!$success) {
                // something's gone wrong between validation and attempting to add the user
                $tournament = $this->load_tournament($tournamentId);
                $validation = $this->validate_join_tournament($userId, $tournamentId, $tournament, $buttonIdArray);

                if ($validation) {
                    error_log('Paradoxical validation success in BMInterface::join_tournament');
                    $this->set_message('Tournament join failed even though it should have succeeded');
                }
                return NULL;
            }

            $this->watch_tournament($userId, $tournamentId);
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Cannot join tournament because a player or tournament ID was not valid');
            return NULL;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::join_tournament: ' .
                $e->getMessage()
            );
            $this->set_message("Tournament join failed: $e");
            return NULL;
        }

        $this->advance_tournament($tournamentId);
        $this->set_message('You have successfully joined this tournament');
        return TRUE;
    }

    /**
     * Validate whether a player can join a tournament
     *
     * @param int $userId
     * @param int $tournamentId
     * @param BMTournament $tournament
     * @param array $buttonIdArray
     * @return bool|null
     */
    protected function validate_join_tournament($userId, $tournamentId, $tournament, $buttonIdArray) {
        if (is_null($tournament)) {
            $this->set_message("Tournament $tournamentId does not exist");
            return NULL;
        }

        if (BMTournamentState::CANCELLED == $tournament->tournamentState) {
            $this->set_message('The tournament was cancelled.');
            return NULL;
        }

        if ($tournament->tournamentState > BMTournamentState::JOIN_TOURNAMENT) {
            $this->set_message('The tournament has already started.');
            return NULL;
        }

        if (in_array($userId, $tournament->playerIdArray)) {
            $this->set_message('You are already part of this tournament.');
            return NULL;
        }

        if (!$tournament->validate_button_choice($buttonIdArray)) {
            $this->set_message('Invalid button choice.');
            return NULL;
        }

        return TRUE;
    }

    /**
     * Resolve random buttons for someone joining a tournament
     *
     * @param array $buttonIdArray
     */
    protected function resolve_random_button_selection_tournament(&$buttonIdArray) {
        $allButtonData = array();
        $nButtons = 0;

        foreach ($buttonIdArray as $buttonIdx => $buttonId) {
            if (BMInterfaceGame::RANDOM_BUTTON_ID == $buttonId) {
                if (empty($allButtonData)) {
                    $allButtonData = $this->get_button_data(
                        NULL,
                        NULL,
                        TRUE,
                        array('exclude_from_random' => 'false')
                    );
                    $nButtons = count($allButtonData);
                }

                $randIdx = bm_rand(0, $nButtons - 1);
                $buttonIdArray[$buttonIdx] = $allButtonData[$randIdx]['buttonId'];
            }
        }
    }

    /**
     * Attempt to add a user to a tournament in the database
     *
     * @param int $userId
     * @param int $tournamentId
     * @param int $nPlayers
     * @param array $buttonIdArray
     * @return bool
     */
    protected function add_user_to_tournament($userId, $tournamentId, $nPlayers, $buttonIdArray) {
        // query is written in this way to avoid possible race conflicts between
        // multiple people trying to join a tournament at the same time
        //
        // it only inserts a row into tournament_player_map if
        // - there are fewer than n_players already in the tournament
        // - the player is not already part of the tournament
        $query = 'INSERT INTO tournament_player_map ' .
                '    (tournament_id,' .
                '     player_id,' .
                '     button_id,' .
                '     position) ' .
                'SELECT ' .
                '    :tournament_id_to_be_joined,' .
                '    :player_id_wants_to_join,' .
                '    :button_id,' .
                '    (SELECT MAX(m.position) FROM tournament_player_map AS m ' .
                '     WHERE m.tournament_id = :tournament_id_check_position_to_join) + 1 ' .
                'FROM DUAL ' .
                'WHERE (' .
                '    SELECT COUNT(*) ' .
                '    FROM tournament_player_map ' .
                '    WHERE tournament_id = :tournament_id_check_current_number_of_players' .
                ') < :n_players ' .
                'AND (' .
                '    SELECT COUNT(*) ' .
                '    FROM tournament_player_map ' .
                '    WHERE tournament_id = :tournament_id_check_if_has_already_joined ' .
                '    AND player_id = :player_id_check_if_has_already_joined' .
                ') < 1';
        // james: need to add button details here too, instead of ignoring them
        $parameters = array(':tournament_id_to_be_joined' => $tournamentId,
            ':tournament_id_check_position_to_join' => $tournamentId,
            ':tournament_id_check_current_number_of_players' => $tournamentId,
            ':tournament_id_check_if_has_already_joined' => $tournamentId,
            ':player_id_wants_to_join' => $userId,
            ':player_id_check_if_has_already_joined' => $userId,
            ':button_id' => $buttonIdArray[0],
            ':n_players' => $nPlayers);
        return self::$db->update_and_report_if_changed($query, $parameters);
    }

    /**
     * Attempt to change a player's selected button in a tournament
     *
     * @param int $userId
     * @param int $tournamentId
     * @param array $buttonNameArray
     * @return bool|null
     */
    protected function change_button_in_tournament($userId, $tournamentId, $buttonNameArray) {
        try {
            $tournament = $this->load_tournament($tournamentId);

            if (!$this->validate_change_button_in_tournament($userId, $tournamentId, $tournament, $buttonNameArray)) {
                return NULL;
            }

            // convert button names to button IDs
            $buttonIdArray = $this->game()->retrieve_button_ids(
                array_fill(0, count($buttonNameArray), $userId),
                $buttonNameArray
            );

            $this->resolve_random_button_selection_tournament($buttonIdArray);

            $buttonIdArrayArray = $tournament->buttonIdArrayArray;
            $buttonIdArrayArray[$userId] = $buttonIdArray;
            $tournament->buttonIdArrayArray = $buttonIdArrayArray;

            $success = $this->save_tournament($tournament);

            if (!$success) {
                // something's gone wrong between validation and attempting to add the user
                $tournament = $this->load_tournament($tournamentId);
                $validation = $this->validate_change_button_in_tournament($userId, $tournamentId, $tournament);

                if ($validation) {
                    error_log('Paradoxical validation success in BMInterface::change_button_in_tournament');
                    $this->set_message('Change of button failed even though it should have succeeded');
                }
                return NULL;
            }
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Cannot update button because a player or tournament ID was not valid');
            return NULL;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::change_button_in_tournament: ' .
                $e->getMessage()
            );
            $this->set_message("Button change failed: $e");
            return NULL;
        }

        $this->advance_tournament($tournamentId);
        $this->set_message('You have successfully updated your button selection');
        return TRUE;
    }

    /**
     * Validate whether a player can change their button choice in a tournament
     *
     * @param int $userId
     * @param int $tournamentId
     * @param BMTournament $tournament
     * @param array $buttonIdArray
     * @return bool|null
     */
    protected function validate_change_button_in_tournament($userId, $tournamentId, $tournament, $buttonIdArray) {
        if (is_null($tournament)) {
            $this->set_message("Tournament $tournamentId does not exist");
            return NULL;
        }

        if (BMTournamentState::CANCELLED == $tournament->tournamentState) {
            $this->set_message('The tournament was cancelled.');
            return NULL;
        }

        if ($tournament->tournamentState > BMTournamentState::JOIN_TOURNAMENT) {
            $this->set_message('The tournament has already started.');
            return NULL;
        }

        if (!in_array($userId, $tournament->playerIdArray)) {
            $this->set_message('You are not part of this tournament.');
            return NULL;
        }

        if (!$tournament->validate_button_choice($buttonIdArray)) {
            $this->set_message('Invalid button choice.');
            return NULL;
        }

        return TRUE;
    }

    /**
     * Attempt to remove a player from a tournament
     *
     * @param int $userId
     * @param int $tournamentId
     * @return bool|null
     */
    protected function leave_tournament($userId, $tournamentId) {
        try {
            $tournament = $this->load_tournament($tournamentId);

            if (!$this->validate_leave_tournament($userId, $tournamentId, $tournament)) {
                return NULL;
            }

            $success = $this->remove_user_from_tournament($userId, $tournamentId, $tournament->nPlayers);

            if (!$success) {
                // something's gone wrong between validation and attempting to remove the user
                $tournament = $this->load_tournament($tournamentId);
                $validation = $this->validate_leave_tournament($userId, $tournamentId, $tournament);

                if ($validation) {
                    error_log('Paradoxical validation success in BMInterface::leave_tournament');
                    $this->set_message('Tournament leave failed even though it should have succeeded');
                }
                return NULL;
            }

            $this->unwatch_tournament($userId, $tournamentId);
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Cannot leave tournament because a player or tournament ID was not valid');
            return NULL;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::leave_tournament: ' .
                $e->getMessage()
            );
            $this->set_message("Tournament leave failed: $e");
            return NULL;
        }

        $this->advance_tournament($tournamentId);
        $this->set_message('You have successfully left this tournament');
        return TRUE;
    }

    /**
     * Validate whether a player can leave a tournament
     *
     * @param int $userId
     * @param int $tournamentId
     * @param BMTournament $tournament
     * @return bool|null
     */
    protected function validate_leave_tournament($userId, $tournamentId, $tournament) {
        if (is_null($tournament)) {
            $this->set_message("Tournament $tournamentId does not exist");
            return NULL;
        }

        if (BMTournamentState::CANCELLED == $tournament->tournamentState) {
            $this->set_message('The tournament was cancelled.');
            return NULL;
        }

        if ($tournament->tournamentState > BMTournamentState::JOIN_TOURNAMENT) {
            $this->set_message('The tournament has already started.');
            return NULL;
        }

        if (!in_array($userId, $tournament->playerIdArray)) {
            $this->set_message('You are not part of this tournament.');
            return NULL;
        }

        if (count($tournament->playerIdArray) == $tournament->nPlayers) {
            $this->error_log('A tournament should not still be in JOIN_TOURNAMENT when it is full.');
            throw new LogicException('Tournament is in JOIN_TOURNAMENT and has a full complement of players.');
        }

        return TRUE;
    }

    /**
     * Attempt to remove a user from a tournament in the database
     *
     * @param int $userId
     * @param int $tournamentId
     * @param int $nPlayers
     * @return bool
     */
    protected function remove_user_from_tournament($userId, $tournamentId, $nPlayers) {
        // query is written in this way to avoid possible race conflicts between
        // multiple people trying to act on a tournament at the same time
        //
        // it only deletes a row from tournament_player_map if
        // - there are fewer than n_players already in the tournament
        $query = 'DELETE FROM tournament_player_map ' .
                'WHERE (' .
                '    SELECT COUNT(*) ' .
                '    FROM (SELECT * FROM tournament_player_map) AS tournament_player_map_temp ' .
                '    WHERE tournament_id = :tournament_id_check_has_started' .
                ') < :n_players ' .
                'AND tournament_id = :tournament_id_current ' .
                'AND player_id = :player_id';

        $parameters = array(':tournament_id_check_has_started' => $tournamentId,
            ':tournament_id_current' => $tournamentId,
            ':player_id' => $userId,
            ':n_players' => $nPlayers);
        return self::$db->update_and_report_if_changed($query, $parameters);
    }

    /**
     * Attempt to cancel a tournament
     *
     * @param int $userId
     * @param int $tournamentId
     * @return bool|null
     */
    protected function cancel_tournament($userId, $tournamentId) {
        try {
            $tournament = $this->load_tournament($tournamentId);

            if (!$this->validate_cancel_tournament($userId, $tournamentId, $tournament)) {
                return NULL;
            }

            $success = $this->cancel_tournament_in_database($userId, $tournamentId);

            if (!$success) {
                // something's gone wrong between validation and attempting to remove the user
                $tournament = $this->load_tournament($tournamentId);
                $validation = $this->validate_cancel_tournament($userId, $tournamentId, $tournament);

                if ($validation) {
                    error_log('Paradoxical validation success in BMInterface::cancel_tournament');
                    $this->set_message('Tournament cancel failed even though it should have succeeded');
                }
                return NULL;
            }
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Cannot cancel tournament because a player or tournament ID was not valid');
            return NULL;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::cancel_tournament: ' .
                $e->getMessage()
            );
            $this->set_message("Tournament cancel failed: $e");
            return NULL;
        }

        $this->advance_tournament($tournamentId);
        $this->set_message('You have successfully cancelled this tournament');
        return TRUE;
    }

    /**
     * Validate whether a player can cancel a tournament
     *
     * @param int $userId
     * @param int $tournamentId
     * @param BMTournament $tournament
     * @return bool|null
     */
    protected function validate_cancel_tournament($userId, $tournamentId, $tournament) {
        if (is_null($tournament)) {
            $this->set_message("Tournament $tournamentId does not exist");
            return NULL;
        }

        if (BMTournamentState::CANCELLED == $tournament->tournamentState) {
            $this->set_message('The tournament has already been cancelled.');
            return NULL;
        }

        if ($tournament->tournamentState > BMTournamentState::JOIN_TOURNAMENT) {
            $this->set_message('The tournament has already started.');
            return NULL;
        }

        if ($tournament->creatorId !== $userId) {
            $this->set_message('You did not create this tournament.');
            return NULL;
        }

        return TRUE;
    }

    /**
     * Attempt to cancel a tournament in the database
     *
     * @param int $userId
     * @param int $tournamentId
     * @return bool
     */
    protected function cancel_tournament_in_database($userId, $tournamentId) {
        // query is written in this way to avoid possible race conflicts between
        // multiple people trying to act on a tournament at the same time
        //
        // a tournament can only be deleted by the creator if it has not started yet
        $query = 'UPDATE tournament ' .
                'SET status_id = (SELECT id FROM tournament_status WHERE name = "CANCELLED"), ' .
                'tournament_state = :tournament_state ' .
                'WHERE id = :tournament_id ' .
                'AND tournament_state <= 20 ' .
                'AND creator_id = :creator_id';
        $parameters = array(':tournament_id' => $tournamentId,
            ':tournament_state' => BMTournamentState::CANCELLED,
            ':creator_id' => $userId);

        return self::$db->update_and_report_if_changed($query, $parameters);
    }

    /**
     * Follow tournament
     *
     * This adds a link to the tournament on the overview page
     *
     * @param int $playerId
     * @param int $tournamentId
     * @return bool|null
     */
    public function follow_tournament($playerId, $tournamentId) {
        try {
            if ($this->is_tournament_finished($tournamentId)) {
                $this->set_message('Cannot follow a tournament that has ended');
                return NULL;
            }

            if ($this->is_tournament_watched($playerId, $tournamentId)) {
                $this->set_message('Tournament was already being followed');
                return TRUE;
            }

            $this->watch_tournament($playerId, $tournamentId);

            $this->set_message('Tournament followed');
            return TRUE;
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Cannot follow tournament because tournament ID was not valid');
            return NULL;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::follow_tournament: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while following a tournament');
            return NULL;
        }
    }

    /**
     * Unfollow tournament
     *
     * This removes a link to the tournament from the overview page
     *
     * @param int $playerId
     * @param int $tournamentId
     * @return bool|null
     */
    public function unfollow_tournament($playerId, $tournamentId) {
        try {
            if (!$this->is_tournament_watched($playerId, $tournamentId)) {
                $this->set_message('Tournament was not being followed');
                return NULL;
            }

            if ($this->is_player_in_tournament($playerId, $tournamentId)) {
                if ($this->is_tournament_finished($tournamentId)) {
                    $this->set_message('This tournament should be dismissed, not unfollowed');
                    return NULL;
                } else {
                    $this->set_message('Participants cannot unfollow an ongoing tournament');
                    return NULL;
                }
            }

            $this->unwatch_tournament($playerId, $tournamentId);

            $this->set_message('Tournament unfollowed');
            return TRUE;
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Cannot unfollow tournament because tournament ID was not valid');
            return NULL;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::unfollow_tournament: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while unfollowing a tournament');
            return NULL;
        }
    }

    /**
     * Dismiss tournament
     *
     * This removes a link to a completed tournament from the overview page
     *
     * @param int $playerId
     * @param int $tournamentId
     * @return bool|null
     */
    public function dismiss_tournament($playerId, $tournamentId) {
        try {
            if (!$this->is_player_in_tournament($playerId, $tournamentId)) {
                $this->set_message('Only participants can dismiss tournaments');
                return NULL;
            }

            if (!$this->is_tournament_finished($tournamentId)) {
                $this->set_message("Tournament $tournamentId isn't complete");
                return NULL;
            }

            $this->unwatch_tournament($playerId, $tournamentId);

            $this->set_message('Dismissing tournament succeeded');
            return TRUE;
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Cannot dismiss tournament because tournament ID was not valid');
            return NULL;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::dismiss_tournament: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while dismissing a tournament');
            return NULL;
        }
    }
}
