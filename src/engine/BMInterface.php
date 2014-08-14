<?php

/**
 * BMInterface: interface between GUI and BMGame
 *
 * @author james
 */

/**
 * This class deals with communication between the UI, the game code, and the database
 *
 * @property-read string $message                Message intended for GUI
 * @property-read DateTime $timestamp            Timestamp of last game action
 *
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class BMInterface {
    // constants
    const DEFAULT_PLAYER_COLOR = '#dd99dd';
    const DEFAULT_OPPONENT_COLOR = '#ddffdd';
    const DEFAULT_NEUTRAL_COLOR_A = '#cccccc';
    const DEFAULT_NEUTRAL_COLOR_B = '#dddddd';

    // properties
    private $message;               // message intended for GUI
    private $timestamp;             // timestamp of last game action
    private static $conn = NULL;    // connection to database

    private $isTest;         // indicates if the interface is for testing


    // constructor
    public function __construct($isTest = FALSE) {
        if (!is_bool($isTest)) {
            throw new InvalidArgumentException('isTest must be boolean.');
        }

        $this->isTest = $isTest;

        if ($isTest) {
            if (file_exists('../test/src/database/mysql.test.inc.php')) {
                require_once '../test/src/database/mysql.test.inc.php';
            } else {
                require_once 'test/src/database/mysql.test.inc.php';
            }
        } else {
            require_once '../database/mysql.inc.php';
        }
        self::$conn = conn();
    }

    // methods

    public function get_player_info($playerId) {
        try {
            $query =
                'SELECT p.*, b.name AS favorite_button, bs.name AS favorite_buttonset, ' .
                    'UNIX_TIMESTAMP(p.last_access_time) AS last_access_timestamp, ' .
                    'UNIX_TIMESTAMP(p.last_action_time) AS last_action_timestamp, ' .
                    'UNIX_TIMESTAMP(p.creation_time) AS creation_timestamp ' .
                'FROM player p ' .
                    'LEFT JOIN button b ON b.id = p.favorite_button_id ' .
                    'LEFT JOIN buttonset bs ON bs.id = p.favorite_buttonset_id ' .
                'WHERE p.id = :id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':id' => $playerId));
            $result = $statement->fetchAll();

            if (0 == count($result)) {
                return NULL;
            }
        } catch (Exception $e) {
            if (isset($statement)) {
                $errorData = $statement->errorInfo();
                $this->message = 'Player info get failed: ' . $errorData[2];
            } else {
                $this->message = 'Player info get failed: ' . $e->getMessage();
            }
            error_log($this->message);
            return NULL;
        }

        $infoArray = $result[0];

        $last_action_time = (int)$infoArray['last_action_timestamp'];
        if ($last_action_time == 0) {
            $last_action_time = NULL;
        }

        $last_access_time = (int)$infoArray['last_access_timestamp'];
        if ($last_access_time == 0) {
            $last_access_time = NULL;
        }

        $image_size = NULL;
        if ($infoArray['image_size'] != NULL) {
            $image_size = (int)$infoArray['image_size'];
        }

        // set the values we want to actually return
        $playerInfoArray = array(
            'id' => (int)$infoArray['id'],
            'name_ingame' => $infoArray['name_ingame'],
            'name_irl' => $infoArray['name_irl'] ?: $infoArray['name_ingame'],
            'email' => $infoArray['email'],
            'is_email_public' => (bool)$infoArray['is_email_public'],
            'status' => $infoArray['status'],
            'dob_month' => (int)$infoArray['dob_month'],
            'dob_day' => (int)$infoArray['dob_day'],
            'gender' => $infoArray['gender'],
            'image_size' => $image_size,
            'autopass' => (bool)$infoArray['autopass'],
            'uses_gravatar' => (bool)$infoArray['uses_gravatar'],
            'monitor_redirects_to_game' => (bool)$infoArray['monitor_redirects_to_game'],
            'monitor_redirects_to_forum' => (bool)$infoArray['monitor_redirects_to_forum'],
            'automatically_monitor' => (bool)$infoArray['automatically_monitor'],
            'comment' => $infoArray['comment'],
            'player_color' => $infoArray['player_color'] ?: self::DEFAULT_PLAYER_COLOR,
            'opponent_color' => $infoArray['opponent_color'] ?: self::DEFAULT_OPPONENT_COLOR,
            'neutral_color_a' => $infoArray['neutral_color_a'] ?: self::DEFAULT_NEUTRAL_COLOR_A,
            'neutral_color_b' => $infoArray['neutral_color_b'] ?: self::DEFAULT_NEUTRAL_COLOR_B,
            'homepage' => $infoArray['homepage'],
            'favorite_button' => $infoArray['favorite_button'],
            'favorite_buttonset' => $infoArray['favorite_buttonset'],
            'last_action_time' => $last_action_time,
            'last_access_time' => $last_access_time,
            'creation_time' => (int)$infoArray['creation_timestamp'],
            'fanatic_button_id' => (int)$infoArray['fanatic_button_id'],
            'n_games_won' => (int)$infoArray['n_games_won'],
            'n_games_lost' => (int)$infoArray['n_games_lost'],
        );

        return array('user_prefs' => $playerInfoArray);
    }

    public function set_player_info($playerId, array $infoArray, array $addlInfo) {
        // mysql treats bools as one-bit integers
        $infoArray['autopass'] = (int)($infoArray['autopass']);
        $infoArray['monitor_redirects_to_game'] = (int)($infoArray['monitor_redirects_to_game']);
        $infoArray['monitor_redirects_to_forum'] = (int)($infoArray['monitor_redirects_to_forum']);
        $infoArray['automatically_monitor'] = (int)($infoArray['automatically_monitor']);

        $isValidData =
            ($this->validate_player_dob($infoArray) &&
            $this->validate_player_password_and_email($addlInfo, $playerId) &&
            $this->validate_and_set_homepage($addlInfo['homepage'], $infoArray));
        if (!$isValidData) {
            return NULL;
        }

        if (isset($addlInfo['favorite_button'])) {
            $infoArray['favorite_button_id'] =
                $this->get_button_id_from_name($addlInfo['favorite_button']);
            if (!is_int($infoArray['favorite_button_id'])) {
                return FALSE;
            }
        } else {
            $infoArray['favorite_button_id'] = NULL;
        }
        if (isset($addlInfo['favorite_buttonset'])) {
            $infoArray['favorite_buttonset_id'] =
                $this->get_buttonset_id_from_name($addlInfo['favorite_buttonset']);
            if (!is_int($infoArray['favorite_buttonset_id'])) {
                return FALSE;
            }
        } else {
            $infoArray['favorite_buttonset_id'] = NULL;
        }

        if (isset($addlInfo['new_password'])) {
            $infoArray['password_hashed'] = crypt($addlInfo['new_password']);
        }

        if (isset($addlInfo['new_email'])) {
            $infoArray['email'] = $addlInfo['new_email'];
        }

        foreach ($infoArray as $infoType => $info) {
            try {
                $query = 'UPDATE player '.
                         "SET $infoType = :info ".
                         'WHERE id = :player_id;';

                $statement = self::$conn->prepare($query);
                $statement->execute(array(':info' => $info,
                                          ':player_id' => $playerId));
            } catch (Exception $e) {
                $this->message = 'Player info update failed: '.$e->getMessage();
            }
        }
        $this->message = "Player info updated successfully.";
        return array('playerId' => $playerId);
    }

    protected function validate_player_dob(array $infoArray) {
        if (($infoArray['dob_month'] != 0 && $infoArray['dob_day'] == 0) ||
            ($infoArray['dob_month'] == 0 && $infoArray['dob_day'] != 0)) {
            $this->message = 'DOB is incomplete.';
            return FALSE;
        }

        if ($infoArray['dob_month'] != 0 && $infoArray['dob_day'] != 0 &&
            !checkdate($infoArray['dob_month'], $infoArray['dob_day'], 4)) {
            $this->message = 'DOB is not a valid date.';
            return FALSE;
        }

        return TRUE;
    }

    protected function validate_player_password_and_email(array $addlInfo, $playerId) {
        if ((isset($addlInfo['new_password']) || isset($addlInfo['new_email'])) &&
            !isset($addlInfo['current_password'])) {
            $this->message = 'Current password is required to change password or email.';
            return FALSE;
        }

        if (isset($addlInfo['current_password'])) {
            $passwordQuery = 'SELECT password_hashed FROM player WHERE id = :playerId';
            $passwordQuery = self::$conn->prepare($passwordQuery);
            $passwordQuery->execute(array(':playerId' => $playerId));

            $passwordResults = $passwordQuery->fetchAll();
            if (count($passwordResults) != 1) {
                $this->message = 'An error occurred in BMInterface::set_player_info().';
                return FALSE;
            }
            $password_hashed = $passwordResults[0]['password_hashed'];
            if ($password_hashed != crypt($addlInfo['current_password'], $password_hashed)) {
                $this->message = 'Current password is incorrect.';
                return FALSE;
            }
        }

        return TRUE;
    }

    private function validate_and_set_homepage($homepage, array &$infoArray) {
        if ($homepage == NULL || $homepage == "") {
            $infoArray['homepage'] = NULL;
            return TRUE;
        }

        $homepage = $this->validate_url($homepage);
        if ($homepage == NULL) {
            $this->message = 'Homepage is invalid. It may contain some characters that need to be escaped.';
            return FALSE;
        }

        $infoArray['homepage'] = $homepage;
        return TRUE;
    }

    public function get_profile_info($profilePlayerName) {
        $profilePlayerId = $this->get_player_id_from_name($profilePlayerName);
        if (!is_int($profilePlayerId)) {
            return NULL;
        }
        $playerInfoResults = $this->get_player_info($profilePlayerId);
        $playerInfo = $playerInfoResults['user_prefs'];

        $query =
            'SELECT ' .
                'COUNT(*) AS number_of_games, ' .
                'v.n_rounds_won >= g.n_target_wins AS win_or_loss ' .
            'FROM game AS g ' .
                'INNER JOIN game_status AS s ON s.id = g.status_id ' .
                'INNER JOIN game_player_view AS v ' .
                    'ON v.game_id = g.id AND v.player_id = :player_id ' .
            'WHERE s.name = "COMPLETE" ' .
            'GROUP BY v.n_rounds_won >= g.n_target_wins;';

        $statement = self::$conn->prepare($query);
        $statement->execute(array(':player_id' => $profilePlayerId));

        $nWins = 0;
        $nLosses = 0;

        while ($row = $statement->fetch()) {
            if ((int)$row['win_or_loss'] == 1) {
                $nWins = (int)$row['number_of_games'];
            }
            if ((int)$row['win_or_loss'] == 0) {
                $nLosses = (int)$row['number_of_games'];
            }
        }

        // Just select the fields we want to expose publically
        $profileInfoArray = array(
            'id' => $playerInfo['id'],
            'name_ingame' => $playerInfo['name_ingame'],
            'name_irl' => $playerInfo['name_irl'],
            'email' => ($playerInfo['is_email_public'] == 1 ? $playerInfo['email'] : NULL),
            'email_hash' => md5(strtolower(trim($playerInfo['email']))),
            'dob_month' => (int)$playerInfo['dob_month'],
            'dob_day' => (int)$playerInfo['dob_day'],
            'gender' => $playerInfo['gender'],
            'image_size' => $playerInfo['image_size'],
            'uses_gravatar' => $playerInfo['uses_gravatar'],
            'comment' => $playerInfo['comment'],
            'homepage' => $playerInfo['homepage'],
            'favorite_button' => $playerInfo['favorite_button'],
            'favorite_buttonset' => $playerInfo['favorite_buttonset'],
            'last_access_time' => $playerInfo['last_access_time'],
            'creation_time' => $playerInfo['creation_time'],
            'fanatic_button_id' => $playerInfo['fanatic_button_id'],
            'n_games_won' => $nWins,
            'n_games_lost' => $nLosses,
        );

        return array('profile_info' => $profileInfoArray);
    }

    public function create_game(
        array $playerIdArray,
        array $buttonNameArray,
        $maxWins = 3,
        $description = '',
        $previousGameId = NULL,
        $currentPlayerId = NULL
    ) {
        $isValidInfo =
            $this->validate_game_info(
                $playerIdArray,
                $maxWins,
                $currentPlayerId,
                $previousGameId
            );
        if (!$isValidInfo) {
            return NULL;
        }

        $this->resolve_random_button_selection($buttonNameArray);

        $buttonIdArray = $this->retrieve_button_ids($playerIdArray, $buttonNameArray);
        if (is_null($buttonIdArray)) {
            return NULL;
        }

        try {
            $gameId = $this->insert_new_game($playerIdArray, $maxWins, $description, $previousGameId);

            foreach ($playerIdArray as $position => $playerId) {
                $this->add_player_to_new_game($gameId, $playerId, $buttonIdArray[$position], $position);
            }

            // update game state to latest possible
            $game = $this->load_game($gameId);
            if (!($game instanceof BMGame)) {
                throw new Exception(
                    "Could not load newly-created game $gameId"
                );
            }
            if ($previousGameId) {
                $chatNotice = '[i]Continued from [game=' . $previousGameId . '][i]';
                $game->add_chat(-1, $chatNotice);
            }
            $this->save_game($game);

            $this->message = "Game $gameId created successfully.";
            return array('gameId' => $gameId);
        } catch (Exception $e) {
            $this->message = 'Game create failed: ' . $e->getMessage();
            error_log(
                'Caught exception in BMInterface::create_game: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    private function insert_new_game(
        array $playerIdArray,
        $maxWins = 3,
        $description = '',
        $previousGameId = NULL
    ) {
        try {
            // create basic game details
            $query = 'INSERT INTO game '.
                     '    (status_id, '.
                     '     n_players, '.
                     '     n_target_wins, '.
                     '     n_recent_passes, '.
                     '     creator_id, '.
                     '     start_time, '.
                     '     description, '.
                     '     previous_game_id) '.
                     'VALUES '.
                     '    ((SELECT id FROM game_status WHERE name = :status), '.
                     '     :n_players, '.
                     '     :n_target_wins, '.
                     '     :n_recent_passes, '.
                     '     :creator_id, '.
                     '     FROM_UNIXTIME(:start_time), '.
                     '     :description, '.
                     '     :previous_game_id)';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':status'        => 'OPEN',
                                      ':n_players'     => count($playerIdArray),
                                      ':n_target_wins' => $maxWins,
                                      ':n_recent_passes' => 0,
                                      ':creator_id'    => $playerIdArray[0],
                                      ':start_time' => time(),
                                      ':description' => $description,
                                      ':previous_game_id' => $previousGameId));

            $statement = self::$conn->prepare('SELECT LAST_INSERT_ID()');
            $statement->execute();
            $fetchData = $statement->fetch();
            $gameId = (int)$fetchData[0];
            return $gameId;
        } catch (Exception $e) {
            // Failure might occur on DB insert or afterward
            $errorData = $statement->errorInfo();
            if ($errorData[2]) {
                $this->message = 'Game create failed: ' . $errorData[2];
            } else {
                $this->message = 'Game create failed: ' . $e->getMessage();
            }
            error_log(
                'Caught exception in BMInterface::insert_new_game: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    private function add_player_to_new_game($gameId, $playerId, $buttonId, $position) {
        // add info to game_player_map
        $query = 'INSERT INTO game_player_map '.
                 '(game_id, player_id, button_id, position) '.
                 'VALUES '.
                 '(:game_id, :player_id, :button_id, :position)';
        $statement = self::$conn->prepare($query);

        $statement->execute(array(':game_id'   => $gameId,
                                  ':player_id' => $playerId,
                                  ':button_id' => $buttonId,
                                  ':position'  => $position));
    }

    protected function validate_game_info(
        array $playerIdArray,
        $maxWins,
        $currentPlayerId,
        $previousGameId
    ) {
        $areAllPlayersPresent = TRUE;
        // check for the possibility of unspecified players
        foreach ($playerIdArray as $playerId) {
            if (is_null($playerId)) {
                $areAllPlayersPresent = FALSE;
            }
        }

        // check for nonunique player ids
        if ($areAllPlayersPresent &&
            count(array_flip($playerIdArray)) < count($playerIdArray)) {
            $this->message = 'Game create failed because a player has been selected more than once.';
            return FALSE;
        }

        // validate all inputs
        foreach ($playerIdArray as $playerId) {
            if (!(is_null($playerId) || is_int($playerId))) {
                $this->message = 'Game create failed because player ID is not valid.';
                return FALSE;
            }
        }

        // force first player ID to be the current player ID, if specified
        if (!is_null($currentPlayerId)) {
            if ($currentPlayerId !== $playerIdArray[0]) {
                $this->message = 'Game create failed because you must be the first player.';
                error_log(
                    'validate_game_info() failed because currentPlayerId (' . $currentPlayerId .
                    ') does not match playerIdArray[0] (' . $playerIdArray[0] . ')'
                );
                return FALSE;
            }
        }

        if (FALSE ===
            filter_var(
                $maxWins,
                FILTER_VALIDATE_INT,
                array('options'=>
                      array('min_range' => 1,
                            'max_range' => 5))
            )) {
            $this->message = 'Game create failed because the maximum number of wins was invalid.';
            return FALSE;
        }

        // Check that players match those from previous game, if specified
        $arePreviousPlayersValid =
            $this->validate_previous_game_players($previousGameId, $playerIdArray);
        if (!$arePreviousPlayersValid) {
            return NULL;
        }

        return TRUE;
    }

    private function validate_previous_game_players($previousGameId, array $playerIdArray) {
        // If there was no previous game, then there's nothing to worry about
        if ($previousGameId == NULL) {
            return TRUE;
        }

        try {
            $query =
                'SELECT pm.player_id, s.name AS status ' .
                'FROM game g ' .
                    'INNER JOIN game_player_map pm ON pm.game_id = g.id ' .
                    'INNER JOIN game_status s ON s.id = g.status_id ' .
                'WHERE g.id = :previous_game_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':previous_game_id' => $previousGameId));

            $previousPlayerIds = array();
            while ($row = $statement->fetch()) {
                if ($row['status'] != 'COMPLETE') {
                    $this->message =
                        'Game create failed because the previous game has not been completed yet.';
                    return FALSE;
                }
                $previousPlayerIds[] = (int)$row['player_id'];
            }

            if (count($previousPlayerIds) == 0) {
                $this->message =
                    'Game create failed because the previous game was not found.';
                return FALSE;
            }

            foreach ($playerIdArray as $newPlayerId) {
                if (!in_array($newPlayerId, $previousPlayerIds)) {
                    $this->message =
                        'Game create failed because the previous game does not contain the same players.';
                    return FALSE;
                }
            }
            foreach ($previousPlayerIds as $oldPlayerId) {
                if (!in_array($oldPlayerId, $playerIdArray)) {
                    $this->message =
                        'Game create failed because the previous game does not contain the same players.';
                    return FALSE;
                }
            }

            return TRUE;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::validate_previous_game_players: ' .
                $e->getMessage()
            );
            $this->message = 'Game create failed because of an error.';
            return FALSE;
        }
    }

    protected function resolve_random_button_selection(&$buttonNameArray) {
        $allButtonData = array();
        $allButtonNames = array();
        $nButtons = 0;

        foreach ($buttonNameArray as &$buttonName) {
            if ('__random' != $buttonName) {
                continue;
            }

            if (empty($allButtonNames)) {
                $allButtonData = $this->get_button_data(NULL, NULL);
                $nButtons = count($allButtonData);
            }

            $buttonIdx = rand(0, $nButtons - 1);
            $buttonName = $allButtonData[$buttonIdx]['buttonName'];
        }
    }

    protected function retrieve_button_ids($playerIdArray, $buttonNameArray) {
        $buttonIdArray = array();
        foreach (array_keys($playerIdArray) as $position) {
            // get button ID
            $buttonName = $buttonNameArray[$position];
            if (!empty($buttonName)) {
                $query = 'SELECT id FROM button '.
                         'WHERE name = :button_name';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':button_name' => $buttonName));
                $fetchData = $statement->fetch();
                if (FALSE === $fetchData) {
                    $this->message = 'Game create failed because a button name was not valid.';
                    return NULL;
                }
                $buttonIdArray[] = $fetchData[0];
            } else {
                $buttonIdArray[] = NULL;
            }
        }

        return $buttonIdArray;
    }

    public function load_api_game_data($playerId, $gameId, $logEntryLimit) {
        $game = $this->load_game($gameId, $logEntryLimit);
        if ($game) {
            $currentPlayerIdx = array_search($playerId, $game->playerIdArray);

            // load_game will decide if the logEntryLimit should be overridden
            // (e.g. if chat is private or for completed games)
            $logEntryLimit = $game->logEntryLimit;

            // this is not part of the data we return directly, but
            // is currently needed for find_editable_chat_timestamp(),
            // which would need to duplicate a database query otherwise
            $playerNameArray = array();

            $data = $game->getJsonData($playerId);
            $data['currentPlayerIdx'] = $currentPlayerIdx;
            foreach ($game->playerIdArray as $gamePlayerIdx => $gamePlayerId) {
                $playerName = $this->get_player_name_from_id($gamePlayerId);
                $playerNameArray[] = $playerName;
                $data['playerDataArray'][$gamePlayerIdx]['playerName'] = $playerName;
            }

            $data['gameActionLog'] = $this->load_game_action_log($game, $logEntryLimit);
            $data['gameChatLog'] = $this->load_game_chat_log($game, $logEntryLimit);
            $data['timestamp'] = $this->timestamp;

            $data['gameChatEditable'] = $this->find_editable_chat_timestamp(
                $game,
                $currentPlayerIdx,
                $playerNameArray,
                $data['gameChatLog'],
                $data['gameActionLog']
            );

            // Get all the colors the current player has set in his or her
            // preferences, then figure out which ones to apply to this game
            $playerColors = $this->load_player_colors($playerId);
            $gameColors = $this->determine_game_colors(
                $playerId,
                $playerColors,
                $data['playerDataArray'][0]['playerId'],
                $data['playerDataArray'][1]['playerId']
            );
            $data['playerDataArray'][0]['playerColor'] = $gameColors['playerA'];
            $data['playerDataArray'][1]['playerColor'] = $gameColors['playerB'];

            return $data;
        }
        return NULL;
    }

    public function count_pending_games($playerId) {
        try {
            $parameters = array(':player_id' => $playerId);

            $query =
                'SELECT COUNT(*) '.
                'FROM game_player_map AS gpm '.
                   'LEFT JOIN game AS g ON g.id = gpm.game_id '.
                'WHERE gpm.player_id = :player_id '.
                   'AND gpm.is_awaiting_action = 1 ';

            $statement = self::$conn->prepare($query);
            $statement->execute($parameters);
            $result = $statement->fetch();
            if (!$result) {
                $this->message = 'Pending game count failed.';
                error_log('Pending game count failed for player ' . $playerId);
                return NULL;
            } else {
                $data = array();
                $data['count'] = (int)$result[0];
                $this->message = 'Pending game count succeeded.';
                return $data;
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::count_pending_games: ' .
                $e->getMessage()
            );
            $this->message = 'Pending game count failed.';
            return NULL;
        }
    }

    protected function load_game($gameId, $logEntryLimit = NULL) {
        try {
            $game = $this->load_game_parameters($gameId);

            // check whether the game exists
            if (!isset($game)) {
                $this->message = "Game $gameId does not exist.";
                return FALSE;
            }

            $this->set_logEntryLimit($game, $logEntryLimit);

            $this->load_swing_values_from_last_round($game);
            $this->load_swing_values_from_this_round($game);
            $this->load_option_values_from_last_round($game);
            $this->load_option_values_from_this_round($game);
            $this->load_die_attributes($game);

            $this->recreate_optRequestArrayArray($game);

            if (!isset($game->swingRequestArrayArray)) {
                $game->swingValueArrayArray = NULL;
            }

            $this->message = $this->message."Loaded data for game $gameId.";

            return $game;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_game: ' .
                $e->getMessage()
            );
            $this->message = "Game load failed: $e";
            return NULL;
        }
    }

    protected function load_game_parameters($gameId) {
        // check that the gameId exists
        $query = 'SELECT g.*,'.
                 'UNIX_TIMESTAMP(g.last_action_time) AS last_action_timestamp, '.
                 's.name AS status_name,'.
                 'v.player_id, v.position, v.autopass,'.
                 'v.button_name, v.alt_recipe,'.
                 'v.n_rounds_won, v.n_rounds_lost, v.n_rounds_drawn,'.
                 'v.did_win_initiative,'.
                 'v.is_awaiting_action, '.
                 'UNIX_TIMESTAMP(v.last_action_time) AS player_last_action_timestamp, '.
                 'v.was_game_dismissed '.
                 'FROM game AS g '.
                 'LEFT JOIN game_status AS s '.
                 'ON s.id = g.status_id '.
                 'LEFT JOIN game_player_view AS v '.
                 'ON g.id = v.game_id '.
                 'WHERE g.id = :game_id '.
                 'ORDER BY g.id;';
        $statement1 = self::$conn->prepare($query);
        $statement1->execute(array(':game_id' => $gameId));

        while ($row = $statement1->fetch()) {
            // load game attributes
            if (!isset($game)) {
                $game = new BMGame;
                $game->gameId = $gameId;
                $this->load_game_attributes($game, $row);
            }

            $pos = $row['position'];
            if (isset($pos)) {
                $game->setArrayPropEntry('playerIdArray', $pos, $row['player_id']);
                $game->setArrayPropEntry('autopassArray', $pos, (bool)$row['autopass']);
            }

            if (1 == $row['did_win_initiative']) {
                $game->playerWithInitiativeIdx = $pos;
            }

            $game->setArrayPropEntry(
                'gameScoreArrayArray',
                $pos,
                array(
                    'W' => $row['n_rounds_won'],
                    'L' => $row['n_rounds_lost'],
                    'D' => $row['n_rounds_drawn']
                )
            );

            $this->load_button($game, $pos, $row);
            $this->load_player_attributes($game, $pos, $row);
            $this->load_lastActionTime($game, $pos, $row);
            $this->load_hasPlayerDismissedGame($game, $pos, $row);
        }

        if (!isset($game)) {
            return NULL;
        }

        return $game;
    }

    private function load_game_attributes($game, $row) {
        $game->gameState = $row['game_state'];
        $game->maxWins   = $row['n_target_wins'];
        $game->turnNumberInRound = $row['turn_number_in_round'];
        $game->nRecentPasses = $row['n_recent_passes'];
        $game->description = $row['description'];
        if ($row['previous_game_id'] == NULL) {
            $game->previousGameId = NULL;
        } else {
            $game->previousGameId = (int)$row['previous_game_id'];
        }
        $this->timestamp = (int)$row['last_action_timestamp'];


        // initialise game arrays
        $nPlayers = $row['n_players'];
        $game->playerIdArray = array_fill(0, $nPlayers, NULL);
        $game->gameScoreArrayArray =
            array_fill(0, $nPlayers, array('W' => 0, 'L' => 0, 'D' => 0));
        $game->buttonArray = array_fill(0, $nPlayers, NULL);
        $game->waitingOnActionArray = array_fill(0, $nPlayers, FALSE);
        $game->autopassArray = array_fill(0, $nPlayers, FALSE);
        $game->lastActionTimeArray = array_fill(0, $nPlayers, NULL);
        $game->hasPlayerDismissedGameArray = array_fill(0, $nPlayers, FALSE);
    }

    protected function load_button($game, $pos, $row) {
        if (isset($row['button_name'])) {
            if (isset($row['alt_recipe'])) {
                $recipe = $row['alt_recipe'];
            } else {
                $recipe = $this->get_button_recipe_from_name($row['button_name']);
            }
            if (isset($recipe)) {
                $button = new BMButton;
                $button->load($recipe, $row['button_name']);
                if (isset($row['alt_recipe'])) {
                    $button->hasAlteredRecipe = TRUE;
                }
                $game->setArrayPropEntry('buttonArray', $pos, $button);
            } else {
                throw new InvalidArgumentException('Invalid button name.');
            }
        }
    }

    private function load_player_attributes($game, $pos, $row) {
        switch ($row['is_awaiting_action']) {
            case 1:
                $game->setArrayPropEntry('waitingOnActionArray', $pos, TRUE);
                break;
            case 0:
                $game->setArrayPropEntry('waitingOnActionArray', $pos, FALSE);
                break;
        }

        if (isset($row['current_player_id']) &&
            isset($row['player_id']) &&
            ($row['current_player_id'] === $row['player_id'])) {
            $game->activePlayerIdx = $pos;
        }

        if ($row['did_win_initiative']) {
            $game->playerWithInitiativeIdx = $pos;
        }
    }

    protected function load_lastActionTime($game, $pos, $row) {
        if (isset($row['player_last_action_timestamp'])) {
            $game->setArrayPropEntry(
                'lastActionTimeArray',
                $pos,
                (int)$row['player_last_action_timestamp']
            );
        } else {
            $game->setArrayPropEntry('lastActionTimeArray', $pos, 0);
        }
    }

    protected function load_hasPlayerDismissedGame($game, $pos, $row) {
        if (isset($row['was_game_dismissed'])) {
            $game->setArrayPropEntry(
                'hasPlayerDismissedGameArray',
                $pos,
                ((int)$row['was_game_dismissed'] == 1)
            );
        } else {
            $game->setArrayPropEntry('hasPlayerDismissedGameArray', $pos, 0);
        }
    }

    protected function set_logEntryLimit($game, $logEntryLimit) {
        if ($game->gameState == BMGameState::END_GAME) {
            $game->logEntryLimit = NULL;
        } else {
            $game->logEntryLimit = $logEntryLimit;
        }
    }

    protected function load_swing_values_from_last_round($game) {
        $game->prevSwingValueArrayArray = array_fill(0, $game->nPlayers, array());
        $query = 'SELECT * '.
                 'FROM game_swing_map '.
                 'WHERE game_id = :game_id '.
                 'AND is_expired = :is_expired';
        $statement2 = self::$conn->prepare($query);
        $statement2->execute(array(':game_id' => $game->gameId,
                                   ':is_expired' => 1));
        while ($row = $statement2->fetch()) {
            $playerIdx = array_search($row['player_id'], $game->playerIdArray);
            $game->prevSwingValueArrayArray[$playerIdx][$row['swing_type']] = $row['swing_value'];
        }
    }

    protected function load_swing_values_from_this_round($game) {
        $game->swingValueArrayArray = array_fill(0, $game->nPlayers, array());
        $query = 'SELECT * '.
                 'FROM game_swing_map '.
                 'WHERE game_id = :game_id '.
                 'AND is_expired = :is_expired';
        $statement2 = self::$conn->prepare($query);
        $statement2->execute(array(':game_id' => $game->gameId,
                                   ':is_expired' => 0));
        while ($row = $statement2->fetch()) {
            $playerIdx = array_search($row['player_id'], $game->playerIdArray);
            $game->swingValueArrayArray[$playerIdx][$row['swing_type']] = $row['swing_value'];
        }
    }

    protected function load_option_values_from_last_round($game) {
        $game->prevOptValueArrayArray = array_fill(0, $game->nPlayers, array());
        $query = 'SELECT * '.
                 'FROM game_option_map '.
                 'WHERE game_id = :game_id '.
                 'AND is_expired = :is_expired';
        $statement2 = self::$conn->prepare($query);
        $statement2->execute(array(':game_id' => $game->gameId,
                                   ':is_expired' => 1));
        while ($row = $statement2->fetch()) {
            $playerIdx = array_search($row['player_id'], $game->playerIdArray);
            $game->prevOptValueArrayArray[$playerIdx][$row['die_idx']] = $row['option_value'];
        }
    }

    protected function load_option_values_from_this_round($game) {
        $game->optValueArrayArray = array_fill(0, $game->nPlayers, array());
        $query = 'SELECT * '.
                 'FROM game_option_map '.
                 'WHERE game_id = :game_id '.
                 'AND is_expired = :is_expired';
        $statement2 = self::$conn->prepare($query);
        $statement2->execute(array(':game_id' => $game->gameId,
                                   ':is_expired' => 0));
        while ($row = $statement2->fetch()) {
            $playerIdx = array_search($row['player_id'], $game->playerIdArray);
            $game->optValueArrayArray[$playerIdx][$row['die_idx']] = $row['option_value'];
        }
    }

    protected function load_die_attributes($game) {
        // add die attributes
        $query = 'SELECT d.*,'.
                 '       s.name AS status '.
                 'FROM die AS d '.
                 'LEFT JOIN die_status AS s '.
                 'ON d.status_id = s.id '.
                 'WHERE game_id = :game_id '.
                 'ORDER BY id;';

        $statement3 = self::$conn->prepare($query);
        $statement3->execute(array(':game_id' => $game->gameId));

        $activeDieArrayArray = array_fill(0, count($game->playerIdArray), array());
        $captDieArrayArray = array_fill(0, count($game->playerIdArray), array());

        while ($row = $statement3->fetch()) {
            $playerIdx = array_search($row['owner_id'], $game->playerIdArray);

            $die = BMDie::create_from_recipe($row['recipe']);
            $die->playerIdx = $playerIdx;

            $originalPlayerIdx = array_search(
                $row['original_owner_id'],
                $game->playerIdArray
            );
            $die->originalPlayerIdx = $originalPlayerIdx;
            $die->ownerObject = $game;

            $this->set_swing_max($die, $originalPlayerIdx, $game, $row);
            $this->set_twin_swing_max($die, $originalPlayerIdx, $game, $row);
            $this->set_option_max($die, $row);

            if (isset($row['value'])) {
                $die->value = (int)$row['value'];
            }

            if (!is_null($row['flags'])) {
                $die->load_flags_from_string($row['flags']);
            }

            switch ($row['status']) {
                case 'NORMAL':
                    $activeDieArrayArray[$playerIdx][$row['position']] = $die;
                    break;
                case 'SELECTED':
                    $die->selected = TRUE;
                    $activeDieArrayArray[$playerIdx][$row['position']] = $die;
                    break;
                case 'DISABLED':
                    $die->disabled = TRUE;
                    $activeDieArrayArray[$playerIdx][$row['position']] = $die;
                    break;
                case 'DIZZY':
                    $die->dizzy = TRUE;
                    $activeDieArrayArray[$playerIdx][$row['position']] = $die;
                    break;
                case 'CAPTURED':
                    $die->captured = TRUE;
                    $captDieArrayArray[$playerIdx][$row['position']] = $die;
                    break;
            }
        }

        $game->activeDieArrayArray = $activeDieArrayArray;
        $game->capturedDieArrayArray = $captDieArrayArray;
    }

    protected function set_swing_max($die, $originalPlayerIdx, $game, $row) {
        if (isset($die->swingType)) {
            $game->request_swing_values($die, $die->swingType, $originalPlayerIdx);
            $die->set_swingValue($game->swingValueArrayArray[$originalPlayerIdx]);

            if (isset($row['actual_max'])) {
                $die->max = $row['actual_max'];
            }
        }
    }

    protected function set_twin_swing_max($die, $originalPlayerIdx, $game, $row) {
        if ($die instanceof BMDieTwin &&
            (($die->dice[0] instanceof BMDieSwing) ||
             ($die->dice[1] instanceof BMDieSwing))) {

            foreach ($die->dice as $subdie) {
                if ($subdie instanceof BMDieSwing) {
                    $swingType = $subdie->swingType;
                    $subdie->set_swingValue($game->swingValueArrayArray[$originalPlayerIdx]);

                    if (isset($row['actual_max'])) {
                        $subdie->max = (int)($row['actual_max']/2);
                    }
                }
            }

            $game->request_swing_values($die, $swingType, $originalPlayerIdx);
        }
    }

    protected function set_option_max($die, $row) {
        if ($die instanceof BMDieOption) {
            if (isset($row['actual_max'])) {
                $die->max = $row['actual_max'];
                $die->needsOptionValue = FALSE;
            } else {
                $die->needsOptionValue = TRUE;
            }
        }
    }

    protected function recreate_optRequestArrayArray($game) {
        foreach ($game->activeDieArrayArray as $activeDieArray) {
            foreach ($activeDieArray as $activeDie) {
                if ($activeDie instanceof BMDieOption) {
                    $game->request_option_values(
                        $activeDie,
                        $activeDie->optionValueArray,
                        $activeDie->playerIdx
                    );
                }
            }
        }
    }

    protected function save_game(BMGame $game) {
        // force game to proceed to the latest possible before saving
        $game->proceed_to_next_user_action();

        try {
            $this->save_basic_game_parameters($game);
            $this->save_button_recipes($game);
            $this->save_round_scores($game);
            $this->clear_swing_values_from_database($game);
            $this->clear_option_values_from_database($game);
            $this->save_swing_values_from_last_round($game);
            $this->save_swing_values_from_this_round($game);
            $this->save_option_values_from_last_round($game);
            $this->save_option_values_from_this_round($game);
            $this->save_player_with_initiative($game);
            $this->save_players_awaiting_action($game);
            $this->mark_existing_dice_as_deleted($game);
            $this->save_captured_dice($game);
            $this->delete_dice_marked_as_deleted($game);
            $this->save_action_log($game);
            $this->save_chat_log($game);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_game: ' .
                $e->getMessage()
            );
            $this->message = "Game save failed: $e";
        }
    }

    protected function save_basic_game_parameters($game) {
        $query = 'UPDATE game '.
                 'SET last_action_time = NOW(),'.
                 '    status_id = '.
                 '        (SELECT id FROM game_status WHERE name = :status),'.
                 '    game_state = :game_state,'.
                 '    round_number = :round_number,'.
                 '    turn_number_in_round = :turn_number_in_round,'.
        //:n_recent_draws
                 '    n_recent_passes = :n_recent_passes,'.
                 '    current_player_id = :current_player_id '.
        //:last_winner_id
        //:tournament_id
        //:description
        //:chat
                 'WHERE id = :game_id;';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':status' => $this->get_game_status($game),
                                  ':game_state' => $game->gameState,
                                  ':round_number' => $game->roundNumber,
                                  ':turn_number_in_round' => $game->turnNumberInRound,
                                  ':n_recent_passes' => $game->nRecentPasses,
                                  ':current_player_id' => $this->get_currentPlayerId($game),
                                  ':game_id' => $game->gameId));
    }

    protected function get_currentPlayerId($game) {
        if (is_null($game->activePlayerIdx)) {
            $currentPlayerId = NULL;
        } else {
            $currentPlayerId = $game->playerIdArray[$game->activePlayerIdx];
        }

        return $currentPlayerId;
    }

    protected function get_game_status($game) {
        if (BMGameState::END_GAME == $game->gameState) {
            $status = 'COMPLETE';
        } elseif (in_array(NULL, $game->playerIdArray) ||
                  in_array(NULL, $game->buttonArray)) {
            $status = 'OPEN';
        } else {
            $status = 'ACTIVE';
        }

        return $status;
    }

    protected function save_button_recipes($game) {
        if (isset($game->buttonArray)) {
            foreach ($game->buttonArray as $playerIdx => $button) {
                if (($button instanceof BMButton) &&
                    ($button->hasAlteredRecipe)) {
                    $query = 'UPDATE game_player_map '.
                             'SET alt_recipe = :alt_recipe '.
                             'WHERE game_id = :game_id '.
                             'AND player_id = :player_id;';
                    $statement = self::$conn->prepare($query);
                    $statement->execute(array(':alt_recipe' => $button->recipe,
                                              ':game_id' => $game->gameId,
                                              ':player_id' => $game->playerIdArray[$playerIdx]));
                }
            }
        }
    }

    protected function save_round_scores($game) {
        if (isset($game->gameScoreArrayArray)) {
            foreach ($game->playerIdArray as $playerIdx => $playerId) {
                $query = 'UPDATE game_player_map '.
                         'SET n_rounds_won = :n_rounds_won,'.
                         '    n_rounds_lost = :n_rounds_lost,'.
                         '    n_rounds_drawn = :n_rounds_drawn '.
                         'WHERE game_id = :game_id '.
                         'AND player_id = :player_id;';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':n_rounds_won' => $game->gameScoreArrayArray[$playerIdx]['W'],
                                          ':n_rounds_lost' => $game->gameScoreArrayArray[$playerIdx]['L'],
                                          ':n_rounds_drawn' => $game->gameScoreArrayArray[$playerIdx]['D'],
                                          ':game_id' => $game->gameId,
                                          ':player_id' => $playerId));
            }
        }
    }

    protected function clear_swing_values_from_database($game) {
        $query = 'DELETE FROM game_swing_map '.
                 'WHERE game_id = :game_id;';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':game_id' => $game->gameId));
    }

    protected function clear_option_values_from_database($game) {
        $query = 'DELETE FROM game_option_map '.
                 'WHERE game_id = :game_id;';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':game_id' => $game->gameId));
    }

    protected function save_swing_values_from_last_round($game) {
        if (isset($game->prevSwingValueArrayArray)) {
            foreach ($game->playerIdArray as $playerIdx => $playerId) {
                if (!array_key_exists($playerIdx, $game->prevSwingValueArrayArray)) {
                    continue;
                }
                $swingValueArray = $game->prevSwingValueArrayArray[$playerIdx];
                if (!empty($swingValueArray)) {
                    foreach ($swingValueArray as $swingType => $swingValue) {
                        $query = 'INSERT INTO game_swing_map '.
                                 '(game_id, player_id, swing_type, swing_value, is_expired) '.
                                 'VALUES '.
                                 '(:game_id, :player_id, :swing_type, :swing_value, :is_expired)';
                        $statement = self::$conn->prepare($query);
                        $statement->execute(array(':game_id'     => $game->gameId,
                                                  ':player_id'   => $playerId,
                                                  ':swing_type'  => $swingType,
                                                  ':swing_value' => $swingValue,
                                                  ':is_expired'  => TRUE));
                    }
                }
            }
        }
    }

    protected function save_swing_values_from_this_round($game) {
        if (isset($game->swingValueArrayArray)) {
            foreach ($game->playerIdArray as $playerIdx => $playerId) {
                if (!array_key_exists($playerIdx, $game->swingValueArrayArray)) {
                    continue;
                }
                $swingValueArray = $game->swingValueArrayArray[$playerIdx];
                if (!empty($swingValueArray)) {
                    foreach ($swingValueArray as $swingType => $swingValue) {
                        $query = 'INSERT INTO game_swing_map '.
                                 '(game_id, player_id, swing_type, swing_value, is_expired) '.
                                 'VALUES '.
                                 '(:game_id, :player_id, :swing_type, :swing_value, :is_expired)';
                        $statement = self::$conn->prepare($query);
                        $statement->execute(array(':game_id'     => $game->gameId,
                                                  ':player_id'   => $playerId,
                                                  ':swing_type'  => $swingType,
                                                  ':swing_value' => $swingValue,
                                                  ':is_expired'  => FALSE));
                    }
                }
            }
        }
    }

    protected function save_option_values_from_last_round($game) {
        if (isset($game->prevOptValueArrayArray)) {
            foreach ($game->playerIdArray as $playerIdx => $playerId) {
                if (!array_key_exists($playerIdx, $game->prevOptValueArrayArray)) {
                    continue;
                }
                $optValueArray = $game->prevOptValueArrayArray[$playerIdx];
                if (isset($optValueArray)) {
                    foreach ($optValueArray as $dieIdx => $optionValue) {
                        $query = 'INSERT INTO game_option_map '.
                                 '(game_id, player_id, die_idx, option_value, is_expired) '.
                                 'VALUES '.
                                 '(:game_id, :player_id, :die_idx, :option_value, :is_expired)';
                        $statement = self::$conn->prepare($query);
                        $statement->execute(array(':game_id'   => $game->gameId,
                                                  ':player_id' => $playerId,
                                                  ':die_idx'   => $dieIdx,
                                                  ':option_value' => $optionValue,
                                                  ':is_expired' => TRUE));
                    }
                }
            }
        }
    }

    protected function save_option_values_from_this_round($game) {
        if (isset($game->optValueArrayArray)) {
            foreach ($game->playerIdArray as $playerIdx => $playerId) {
                if (!array_key_exists($playerIdx, $game->optValueArrayArray)) {
                    continue;
                }
                $optValueArray = $game->optValueArrayArray[$playerIdx];
                if (isset($optValueArray)) {
                    foreach ($optValueArray as $dieIdx => $optionValue) {
                        $query = 'INSERT INTO game_option_map '.
                                 '(game_id, player_id, die_idx, option_value, is_expired) '.
                                 'VALUES '.
                                 '(:game_id, :player_id, :die_idx, :option_value, :is_expired)';
                        $statement = self::$conn->prepare($query);
                        $statement->execute(array(':game_id'   => $game->gameId,
                                                  ':player_id' => $playerId,
                                                  ':die_idx'   => $dieIdx,
                                                  ':option_value' => $optionValue,
                                                  ':is_expired' => FALSE));
                    }
                }
            }
        }
    }

    protected function save_player_with_initiative($game) {
        if (isset($game->playerWithInitiativeIdx)) {
            // set all players to not having initiative
            $query = 'UPDATE game_player_map '.
                     'SET did_win_initiative = 0 '.
                     'WHERE game_id = :game_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':game_id' => $game->gameId));

            // set player that won initiative
            $query = 'UPDATE game_player_map '.
                     'SET did_win_initiative = 1 '.
                     'WHERE game_id = :game_id '.
                     'AND player_id = :player_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':game_id' => $game->gameId,
                                      ':player_id' => $game->playerIdArray[$game->playerWithInitiativeIdx]));
        }
    }

    protected function save_players_awaiting_action($game) {
        foreach ($game->waitingOnActionArray as $playerIdx => $waitingOnAction) {
            $query = 'UPDATE game_player_map '.
                     'SET is_awaiting_action = :is_awaiting_action '.
                     'WHERE game_id = :game_id '.
                     'AND position = :position;';
            $statement = self::$conn->prepare($query);
            if ($waitingOnAction) {
                $is_awaiting_action = 1;
            } else {
                $is_awaiting_action = 0;
            }
            $statement->execute(array(':is_awaiting_action' => $is_awaiting_action,
                                      ':game_id' => $game->gameId,
                                      ':position' => $playerIdx));
        }
    }

    protected function mark_existing_dice_as_deleted($game) {
        // set existing dice to have a status of DELETED and get die ids
        //
        // note that the logic is written this way to make debugging easier
        // in case something fails during the addition of dice
        $query = 'UPDATE die '.
                 'SET status_id = '.
                 '    (SELECT id FROM die_status WHERE name = "DELETED") '.
                 'WHERE game_id = :game_id;';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':game_id' => $game->gameId));

        // add active dice to table 'die'
        if (isset($game->activeDieArrayArray)) {
            foreach ($game->activeDieArrayArray as $playerIdx => $activeDieArray) {
                foreach ($activeDieArray as $dieIdx => $activeDie) {
                    // james: set status, this is currently INCOMPLETE
                    $status = 'NORMAL';
                    if ($activeDie->selected) {
                        $status = 'SELECTED';
                    } elseif ($activeDie->disabled) {
                        $status = 'DISABLED';
                    } elseif ($activeDie->dizzy) {
                        $status = 'DIZZY';
                    }

                    $this->db_insert_die($game, $playerIdx, $activeDie, $status, $dieIdx);
                }
            }
        }
    }

    protected function save_captured_dice($game) {
        // add captured dice to table 'die'
        if (isset($game->capturedDieArrayArray)) {
            foreach ($game->capturedDieArrayArray as $playerIdx => $activeDieArray) {
                foreach ($activeDieArray as $dieIdx => $activeDie) {
                    // james: set status, this is currently INCOMPLETE
                    $status = 'CAPTURED';

                    $this->db_insert_die($game, $playerIdx, $activeDie, $status, $dieIdx);
                }
            }
        }
    }

    protected function delete_dice_marked_as_deleted($game) {
        // delete dice with a status of "DELETED" for this game
        $query = 'DELETE FROM die '.
                 'WHERE status_id = '.
                 '    (SELECT id FROM die_status WHERE name = "DELETED") '.
                 'AND game_id = :game_id;';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':game_id' => $game->gameId));
    }

    protected function save_action_log($game) {
        // If any game action entries were generated, load them
        // into the message so the calling player can see them,
        // then save them to the historical log
        if (count($game->actionLog) > 0) {
            $this->load_message_from_game_actions($game);
            $this->log_game_actions($game);
        }
    }

    protected function save_chat_log($game) {
        // If the player sent a chat message, insert it now
        // then save them to the historical log
        if ($game->chat['chat']) {
            $this->log_game_chat($game);
        }
    }

    // Actually insert a die into the database - all error checking to be done by caller
    protected function db_insert_die($game, $playerIdx, $activeDie, $status, $dieIdx) {
        $gameId = $game->gameId;
        $playerId = $game->playerIdArray[$playerIdx];

        $query = 'INSERT INTO die '.
                 '    (owner_id, '.
                 '     original_owner_id, '.
                 '     game_id, '.
                 '     status_id, '.
                 '     recipe, '.
                 '     actual_max, '.
                 '     position, '.
                 '     value, '.
                 '     flags)'.
                 'VALUES '.
                 '    (:owner_id, '.
                 '     :original_owner_id, '.
                 '     :game_id, '.
                 '     (SELECT id FROM die_status WHERE name = :status), '.
                 '     :recipe, '.
                 '     :actual_max, '.
                 '     :position, '.
                 '     :value, '.
                 '     :flags);';
        $statement = self::$conn->prepare($query);

        $flags = $activeDie->flags_as_string();
        if (empty($flags)) {
            $flags = NULL;
        }

        $actualMax = NULL;

        if ($activeDie->forceReportDieSize() ||
            ($activeDie instanceof BMDieOption)) {
            $actualMax = $activeDie->max;
        }

        $statement->execute(array(':owner_id' => $playerId,
                                  ':original_owner_id' => $game->playerIdArray[$activeDie->originalPlayerIdx],
                                  ':game_id' => $gameId,
                                  ':status' => $status,
                                  ':recipe' => $activeDie->recipe,
                                  ':actual_max' => $actualMax,
                                  ':position' => $dieIdx,
                                  ':value' => $activeDie->value,
                                  ':flags' => $flags));
    }

    // Parse the search filters, converting them to standardized forms (such
    // as converting names to ID's), and validating them against the database
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
            $this->message = 'Game search failed.';
            return NULL;
        }
    }

    protected function set_playerNames(&$searchFilters, $searchParameters) {
        if (isset($searchParameters['playerNameA'])) {
            $playerIdA = $this->get_player_id_from_name($searchParameters['playerNameA']);
            if (is_int($playerIdA)) {
                $searchFilters['playerIdA'] = $playerIdA;
            } else {
                $this->message = 'Player A: ' . $this->message;
                return FALSE;
            }
        }

        if (isset($searchParameters['playerNameB'])) {
            $playerIdB = $this->get_player_id_from_name($searchParameters['playerNameB']);
            if (is_int($playerIdB)) {
                $searchFilters['playerIdB'] = $playerIdB;
            } else {
                $this->message = 'Player B: ' . $this->message;
                return FALSE;
            }
        }

        return TRUE;
    }

    protected function set_buttonNames(&$searchFilters, $searchParameters) {
        if (isset($searchParameters['buttonNameA'])) {
            $buttonIdA = $this->get_button_id_from_name($searchParameters['buttonNameA']);
            if (is_int($buttonIdA)) {
                $searchFilters['buttonIdA'] = $buttonIdA;
            } else {
                $this->message = 'Button A: ' . $this->message;
                return FALSE;
            }
        }

        if (isset($searchParameters['buttonNameB'])) {
            $buttonIdB = $this->get_button_id_from_name($searchParameters['buttonNameB']);
            if (is_int($buttonIdB)) {
                $searchFilters['buttonIdB'] = $buttonIdB;
            } else {
                $this->message = 'Button B: ' . $this->message;
                return FALSE;
            }
        }

        return TRUE;
    }

    protected function set_gameStart_limits(&$searchFilters, $searchParameters) {
        if (isset($searchParameters['gameStartMin'])) {
            $searchFilters['gameStartMin'] = (int)$searchParameters['gameStartMin'];
        }
        if (isset($searchParameters['gameStartMax'])) {
            $searchFilters['gameStartMax'] = (int)$searchParameters['gameStartMax'];
        }
    }

    protected function set_lastMove_limits(&$searchFilters, $searchParameters) {
        if (isset($searchParameters['lastMoveMin'])) {
            $searchFilters['lastMoveMin'] = (int)$searchParameters['lastMoveMin'];
        }
        if (isset($searchParameters['lastMoveMax'])) {
            $searchFilters['lastMoveMax'] = (int)$searchParameters['lastMoveMax'];
        }
    }

    // Parse out the additional options that affect how search results
    // are to be presented
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
                    $this->message = 'numberOfResults may not exceed 1000';
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
            $this->message = 'Game search failed.';
            return NULL;
        }
    }

    // Get all games matching the specified search parameters.
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

            $this->message = 'Sought games retrieved successfully.';
            return array('games' => $games, 'summary' => $summary);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::search_game_history: ' .
                $e->getMessage() .
                ' -- Full SQL query: ' . $combinedQuery
            );
            $this->message = 'Game search failed.';
            return NULL;
        }
    }

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

    protected function player_join_0() {
        return  'INNER JOIN game_player_view AS vA ' .
                    'ON vA.game_id = g.id AND vA.position = 0 ' .
                'INNER JOIN game_player_view AS vB ' .
                    'ON vB.game_id = g.id AND vB.position = 1 ';
    }

    protected function player_join_1() {
        return  'INNER JOIN game_player_view AS vA ' .
                    'ON vA.game_id = g.id AND vA.position = 1 ' .
                'INNER JOIN game_player_view AS vB ' .
                    'ON vB.game_id = g.id AND vB.position = 0 ';
    }

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
            $where .= 'AND (s.name = "COMPLETE" OR s.name = "ACTIVE") ';
        }
    }

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

    protected function apply_limit($searchOptions, &$limitParameters) {
        $limitParameters[':offset'] =
            ($searchOptions['page'] - 1) * $searchOptions['numberOfResults'];
        $limitParameters[':page_size'] = $searchOptions['numberOfResults'];
    }

    protected function game_query($where_0, $where_1, $sort, $limit) {
        return  'SELECT * FROM (( ' .
                    $this->base_query() . $this->player_join_0() . $where_0 .
                ') UNION (' .
                    $this->base_query() . $this->player_join_1() . $where_1 .
                ')) AS games ' .
                'GROUP BY game_id ' . $sort . $limit . ';';
    }

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
            $this->message = 'Retrieving summary data for history search failed';
            error_log(
                $this->message .
                ' in BMInterface::search_game_history' .
                ' -- Full SQL query: ' . $combinedQuery
            );
        }
    }

    // Get all player games (either active or inactive) from the database
    // No error checking - caller must do it
    protected function get_all_games($playerId, $getActiveGames) {

        // the following SQL logic assumes that there are only two players per game
        $query = 'SELECT v1.game_id,'.
                 'v1.player_id AS opponent_id,'.
                 'v1.player_name AS opponent_name,'.
                 'v2.button_name AS my_button_name,'.
                 'v1.button_name AS opponent_button_name,'.
                 'v2.n_rounds_won AS n_wins,'.
                 'v2.n_rounds_drawn AS n_draws,'.
                 'v1.n_rounds_won AS n_losses,'.
                 'v1.n_target_wins,'.
                 'v2.is_awaiting_action,'.
                 'g.game_state,'.
                 's.name AS status, '.
                 'UNIX_TIMESTAMP(g.last_action_time) AS last_action_timestamp '.
                 'FROM game_player_view AS v1 '.
                 'LEFT JOIN game_player_view AS v2 '.
                 'ON v1.game_id = v2.game_id '.
                 'LEFT JOIN game AS g '.
                 'ON g.id = v1.game_id '.
                 'LEFT JOIN game_status AS s '.
                 'ON g.status_id = s.id '.
                 'WHERE v2.player_id = :player_id '.
                 'AND v1.player_id != v2.player_id ';
        if ($getActiveGames) {
            $query .= 'AND s.name = "ACTIVE" ';
        } else {
            $query .= 'AND s.name = "COMPLETE" AND v2.was_game_dismissed = 0 ';
        }
        $query .= 'ORDER BY g.last_action_time ASC;';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':player_id' => $playerId));

        return self::read_game_list_from_db_results($playerId, $statement);
    }

    protected function read_game_list_from_db_results($playerId, $results) {
        // Initialize the arrays
        $gameIdArray = array();
        $opponentIdArray = array();
        $opponentNameArray = array();
        $myButtonNameArray = array();
        $oppButtonNameArray = array();
        $nWinsArray = array();
        $nDrawsArray = array();
        $nLossesArray = array();
        $nTargetWinsArray = array();
        $isToActArray = array();
        $gameStateArray = array();
        $statusArray = array();
        $inactivityArray = array();
        $playerColorArray = array();
        $opponentColorArray = array();

        // Ensure that the inactivity time for all games is relative to the
        // same moment
        $now = strtotime('now');

        // Get all the colors the current player has set in his or her
        // preferences
        $playerColors = $this->load_player_colors($playerId);

        while ($row = $results->fetch()) {
            $gameColors = $this->determine_game_colors(
                $playerId,
                $playerColors,
                $playerId,
                (int)$row['opponent_id']
            );

            $gameIdArray[]        = (int)$row['game_id'];
            $opponentIdArray[]    = (int)$row['opponent_id'];
            $opponentNameArray[]  = $row['opponent_name'];
            $myButtonNameArray[]  = $row['my_button_name'];
            $oppButtonNameArray[] = $row['opponent_button_name'];
            $nWinsArray[]         = (int)$row['n_wins'];
            $nDrawsArray[]        = (int)$row['n_draws'];
            $nLossesArray[]       = (int)$row['n_losses'];
            $nTargetWinsArray[]   = (int)$row['n_target_wins'];
            $isToActArray[]       = (int)$row['is_awaiting_action'];
            $gameStateArray[]     = BMGameState::as_string($row['game_state']);
            $statusArray[]        = $row['status'];
            $inactivityArray[]    =
                $this->get_friendly_time_span((int)$row['last_action_timestamp'], $now);
            $playerColorArray[]   = $gameColors['playerA'];
            $opponentColorArray[] = $gameColors['playerB'];
        }

        return array('gameIdArray'             => $gameIdArray,
                     'opponentIdArray'         => $opponentIdArray,
                     'opponentNameArray'       => $opponentNameArray,
                     'myButtonNameArray'       => $myButtonNameArray,
                     'opponentButtonNameArray' => $oppButtonNameArray,
                     'nWinsArray'              => $nWinsArray,
                     'nDrawsArray'             => $nDrawsArray,
                     'nLossesArray'            => $nLossesArray,
                     'nTargetWinsArray'        => $nTargetWinsArray,
                     'isAwaitingActionArray'   => $isToActArray,
                     'gameStateArray'          => $gameStateArray,
                     'statusArray'             => $statusArray,
                     'inactivityArray'         => $inactivityArray,
                     'playerColorArray'        => $playerColorArray,
                     'opponentColorArray'      => $opponentColorArray);
    }

    public function get_all_active_games($playerId) {
        try {
            $this->message = 'All game details retrieved successfully.';
            return $this->get_all_games($playerId, TRUE);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_all_active_games: ' .
                $e->getMessage()
            );
            $this->message = 'Game detail get failed.';
            return NULL;
        }
    }

    public function get_all_completed_games($playerId) {
        try {
            $this->message = 'All game details retrieved successfully.';
            return $this->get_all_games($playerId, FALSE);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_all_active_games: ' .
                $e->getMessage()
            );
            $this->message = 'Game detail get failed.';
            return NULL;
        }
    }

    public function get_all_open_games($currentPlayerId) {
        try {
            // Get all the colors the current player has set in his or her
            // preferences
            $playerColors = $this->load_player_colors($currentPlayerId);

            $query =
                'SELECT ' .
                    'g.id AS game_id, ' .
                    'v_challenger.player_id AS challenger_id, ' .
                    'v_challenger.player_name AS challenger_name, ' .
                    'v_challenger.button_name AS challenger_button, ' .
                    'v_victim.button_name AS victim_button, ' .
                    'g.n_target_wins AS target_wins, ' .
                    'g.description AS description ' .
                'FROM game AS g ' .
                    'INNER JOIN game_status AS s ON s.id = g.status_id ' .
                    // For the time being, I'm assuming there are only two
                    // players. If we later implement 3+ player games, this
                    // will need to be updated.
                    'INNER JOIN game_player_view AS v_challenger ' .
                        'ON v_challenger.game_id = g.id AND v_challenger.player_id IS NOT NULL ' .
                    'INNER JOIN game_player_view AS v_victim ' .
                        'ON v_victim.game_id = g.id AND v_victim.player_id IS NULL ' .
                'WHERE s.name = "OPEN"' .
                'ORDER BY g.id ASC;';

            $statement = self::$conn->prepare($query);
            $statement->execute();

            $games = array();

            while ($row = $statement->fetch()) {
                $gameColors = $this->determine_game_colors(
                    $currentPlayerId,
                    $playerColors,
                    -1, // There is no other player yet
                    (int)$row['challenger_id']
                );

                $games[] = array(
                    'gameId' => (int)$row['game_id'],
                    'challengerId' => (int)$row['challenger_id'],
                    'challengerName' => $row['challenger_name'],
                    'challengerButton' => $row['challenger_button'],
                    'challengerColor' => $gameColors['playerB'],
                    'victimButton' => $row['victim_button'],
                    'targetWins' => (int)$row['target_wins'],
                    'description' => $row['description'],
                );
            }

            $this->message = 'Open games retrieved successfully.';
            return array('games' => $games);
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_all_open_games: " .
                $e->getMessage()
            );
            $this->message = 'Game detail get failed.';
            return NULL;
        }
    }

    public function get_next_pending_game($playerId, $skippedGames) {
        try {
            $parameters = array(':player_id' => $playerId);

            $query = 'SELECT gpm.game_id '.
                     'FROM game_player_map AS gpm '.
                        'LEFT JOIN game AS g ON g.id = gpm.game_id '.
                     'WHERE gpm.player_id = :player_id '.
                        'AND gpm.is_awaiting_action = 1 ';
            foreach ($skippedGames as $index => $skippedGameId) {
                $parameterName = ':skipped_game_id_' . $index;
                $query = $query . 'AND gpm.game_id <> ' . $parameterName . ' ';
                $parameters[$parameterName] = $skippedGameId;
            };
            $query = $query .
                     'ORDER BY g.last_action_time ASC '.
                     'LIMIT 1';

            $statement = self::$conn->prepare($query);
            $statement->execute($parameters);
            $result = $statement->fetch();
            if (!$result) {
                $this->message = 'Player has no pending games.';
                return array('gameId' => NULL);
            } else {
                $gameId = ((int)$result[0]);
                $this->message = 'Next game ID retrieved successfully.';
                return array('gameId' => $gameId);
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_next_pending_game: ' .
                $e->getMessage()
            );
            $this->message = 'Game ID get failed.';
            return NULL;
        }
    }

    public function get_active_players($numberOfPlayers) {
        try {
            $query =
                'SELECT ' .
                    'name_ingame, ' .
                    'UNIX_TIMESTAMP(last_access_time) AS last_access_timestamp ' .
                'FROM player ' .
                'WHERE UNIX_TIMESTAMP(last_access_time) > 0 ' .
                'ORDER BY last_access_time DESC ' .
                'LIMIT :number_of_players;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':number_of_players' => $numberOfPlayers));

            $now = strtotime('now');

            $players = array();
            while ($row = $statement->fetch()) {
                $players[] = array(
                    'playerName' => $row['name_ingame'],
                    'idleness' =>
                        $this->get_friendly_time_span((int)$row['last_access_timestamp'], $now),
                );
            }
            $this->message = 'Active players retrieved successfully.';
            return array('players' => $players);
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_active_players: " .
                $e->getMessage()
            );
            $this->message = 'Getting active players failed.';
            return NULL;
        }
    }

    // Retrieves a list of buttons along with associated information, including
    // their names, recipes, special abilities, sets and TL status.
    // If $buttonName is specified, it only returns that one button. It also
    // includes extra textual data (flavor text, special ability and skill
    // descriptions) that is otherwise omitted for efficiency.
    // If $setName is specified, it only returns buttons in that set.
    // If neither is specified, it returns all buttons.
    public function get_button_data($buttonName = NULL, $setName = NULL) {
        try {
            // if the site is production, don't report unimplemented buttons at all
            $site_type = $this->get_config('site_type');
            $single_button = ($buttonName !== NULL);
            $statement = $this->execute_button_data_query($buttonName, $setName);

            $buttons = array();
            while ($row = $statement->fetch()) {
                $currentButton = $this->assemble_button_data($row, $site_type, $single_button);
                if ($currentButton) {
                    $buttons[] = $currentButton;
                }
            }

            if (count($buttons) == 0) {
                $this->message = 'Button not found.';
                return NULL;
            }

            $this->message = 'Button data retrieved successfully.';
            return $buttons;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_button_data: ' .
                $e->getMessage()
            );
            $this->message = 'Button info get failed.';
            return NULL;
        }
    }

    private function execute_button_data_query($buttonName, $setName) {
        $parameters = array();
        $query =
            'SELECT name, recipe, btn_special, set_name, tourn_legal, flavor_text ' .
            'FROM button_view v ';
        if ($buttonName !== NULL) {
            $query .= 'WHERE v.name = :button_name ';
            $parameters[':button_name'] = $buttonName;
        } elseif ($setName !== NULL) {
            $query .= 'WHERE v.set_name = :set_name ';
            $parameters[':set_name'] = $setName;
        }
        $query .=
            'ORDER BY v.set_id ASC, v.name ASC;';

        $statement = self::$conn->prepare($query);
        $statement->execute($parameters);
        return $statement;
    }

    private function assemble_button_data($row, $site_type, $single_button) {
        // Look for unimplemented skills in each button definition.
        $button = new BMButton();
        $button->load($row['recipe'], $row['name']);
        $dieSkills = array_keys($button->dieSkills);
        sort($dieSkills);
        // For efficiency's sake, there exist some pieces of information
        // which we include only in the case where only one button was
        // requested.
        if (!$single_button) {
            $dieTypes = array_keys($button->dieTypes);
        } else {
            $dieTypes = $button->dieTypes;
            $dieSkillNames = $dieSkills;
            $dieSkills = array();
            foreach ($dieSkillNames as $skillType) {
                $dieSkills[$skillType] = BMSkill::describe($skillType, $dieSkillNames);
            }
        }

        $standardName = preg_replace('/[^a-zA-Z0-9]/', '', $button->name);
        if (((int)$row['btn_special'] == 1) &&
            !class_exists('BMBtnSkill' . $standardName)) {
            $button->hasUnimplementedSkill = TRUE;
        }

        $hasUnimplementedSkill = $button->hasUnimplementedSkill;

        if ($site_type != 'production' || !$hasUnimplementedSkill) {
            $currentButton = array(
                'buttonName' => $row['name'],
                'recipe' => $row['recipe'],
                'hasUnimplementedSkill' => $hasUnimplementedSkill,
                'buttonSet' => $row['set_name'],
                'dieTypes' => $dieTypes,
                'dieSkills' => $dieSkills,
                'isTournamentLegal' => ((int)$row['tourn_legal'] == 1),
                'artFilename' => $button->artFilename,
            );
            // For efficiency's sake, there exist some pieces of information
            // which we include only in the case where only one button was
            // requested.
            if ($single_button) {
                $currentButton['flavorText'] = $row['flavor_text'];
                $buttonSkillClass = 'BMBtnSkill' . $standardName;
                if ((int)$row['btn_special'] == 1 && class_exists($buttonSkillClass)) {
                    $currentButton['specialText'] = $buttonSkillClass::get_description();
                } else {
                    $currentButton['specialText'] = NULL;
                }
            }
            return $currentButton;
        } else {
            return NULL;
        }
    }

    // Retrieves a list of button sets along with associated information,
    // including their name.
    // If $setName is specified, it only returns that one set. It also
    // includes the buttons in that set, which are otherwise omitted for
    // efficiency.
    // If $setName is not specified, it returns all sets.
    public function get_button_set_data($setName = NULL) {
        try {
            $parameters = array();
            $query =
                'SELECT bs.name FROM buttonset bs ';
            if ($setName !== NULL) {
                $query .= 'WHERE bs.name = :set_name ';
                $parameters[':set_name'] = $setName;
            }
            $query .=
                'ORDER BY bs.name ASC;';
            $statement = self::$conn->prepare($query);
            $statement->execute($parameters);

            $sets = array();
            while ($row = $statement->fetch()) {
                $buttons = $this->get_button_data(NULL, $row['name']);
                if (count($buttons) == 0) {
                    continue;
                }

                $currentSet = array('setName' => $row['name']);

                // For efficiency's sake, there exist some pieces of information
                // which we include only in the case that not more than a single
                // button was requested.
                if ($setName !== NULL) {
                    $currentSet['buttons'] = $buttons;
                }

                $currentSet['numberOfButtons'] = count($buttons);

                $dieSkills = array();
                $dieTypes = array();
                $onlyHasUnimplementedButtons = TRUE;
                foreach ($buttons as $button) {
                    $dieSkills = array_unique(array_merge($dieSkills, $button['dieSkills']));
                    $dieTypes = array_unique(array_merge($dieTypes, $button['dieTypes']));
                    if (!$button['hasUnimplementedSkill']) {
                        $onlyHasUnimplementedButtons = FALSE;
                    }
                }
                sort($dieSkills);
                sort($dieTypes);

                $currentSet['dieSkills'] = $dieSkills;
                $currentSet['dieTypes'] = $dieTypes;
                $currentSet['onlyHasUnimplementedButtons'] = $onlyHasUnimplementedButtons;

                $sets[] = $currentSet;
            }

            if (count($sets) == 0) {
                $this->message = 'Button set not found.';
                return NULL;
            }

            $this->message = 'Button set data retrieved successfully.';
            return $sets;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_button_set_data: ' .
                $e->getMessage()
            );
            $this->message = 'Button set info get failed.';
            return NULL;
        }
    }

    protected function get_button_recipe_from_name($name) {
        try {
            $query = 'SELECT recipe FROM button_view '.
                     'WHERE name = :name';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':name' => $name));

            $row = $statement->fetch();
            return($row['recipe']);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_button_recipe_from_name: '
                . $e->getMessage()
            );
            $this->message = 'Button recipe get failed.';
        }
    }

    public function get_player_names_like($input = '') {
        try {
            $query = 'SELECT name_ingame,status FROM player '.
                     'WHERE name_ingame LIKE :input '.
                     'ORDER BY name_ingame';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':input' => $input.'%'));

            $nameArray = array();
            $statusArray = array();
            while ($row = $statement->fetch()) {
                $nameArray[] = $row['name_ingame'];
                $statusArray[] = $row['status'];
            }
            $this->message = 'Names retrieved successfully.';
            return array('nameArray' => $nameArray, 'statusArray' => $statusArray);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_player_names_like: ' .
                $e->getMessage()
            );
            $this->message = 'Player name get failed.';
            return NULL;
        }
    }

    public function get_player_id_from_name($name) {
        try {
            $query = 'SELECT id FROM player '.
                     'WHERE name_ingame = :input';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':input' => $name));
            $result = $statement->fetch();
            if (!$result) {
                $this->message = 'Player name does not exist.';
                return('');
            } else {
                $this->message = 'Player ID retrieved successfully.';
                return((int)$result[0]);
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_player_id_from_name: ' .
                $e->getMessage()
            );
            $this->message = 'Player ID get failed.';
        }
    }

    protected function get_player_name_from_id($playerId) {
        try {
            if (is_null($playerId)) {
                return('');
            }

            $query = 'SELECT name_ingame FROM player '.
                     'WHERE id = :id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':id' => $playerId));
            $result = $statement->fetch();
            if (!$result) {
                $this->message = 'Player ID does not exist.';
                return('');
            } else {
                return($result[0]);
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_player_name_from_id: ' .
                $e->getMessage()
            );
            $this->message = 'Player name get failed.';
        }
    }

    protected function get_player_name_mapping($game) {
        $idNameMapping = array();
        foreach ($game->playerIdArray as $playerId) {
            $idNameMapping[$playerId] = $this->get_player_name_from_id($playerId);
        }
        return $idNameMapping;
    }

    protected function get_button_id_from_name($name) {
        try {
            $query = 'SELECT id FROM button '.
                     'WHERE name = :input';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':input' => $name));
            $result = $statement->fetch();
            if (!$result) {
                $this->message = 'Button name does not exist.';
                return('');
            } else {
                $this->message = 'Button ID retrieved successfully.';
                return((int)$result[0]);
            }
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_button_id_from_name: " .
                $e->getMessage()
            );
            $this->message = 'Button ID get failed.';
        }
    }

    protected function get_buttonset_id_from_name($name) {
        try {
            $query = 'SELECT id FROM buttonset '.
                     'WHERE name = :input';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':input' => $name));
            $result = $statement->fetch();
            if (!$result) {
                $this->message = 'Buttonset name does not exist.';
                return('');
            } else {
                return((int)$result[0]);
            }
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_buttonset_id_from_name: " .
                $e->getMessage()
            );
            $this->message = 'Buttonset ID get failed.';
        }
    }

    // Check whether a requested action still needs to be taken.
    // If the time stamp is not important, use the string 'ignore'
    // for $postedTimestamp.
    protected function is_action_current(
        BMGame $game,
        $expectedGameState,
        $postedTimestamp,
        $roundNumber,
        $currentPlayerId
    ) {
        $currentPlayerIdx = array_search($currentPlayerId, $game->playerIdArray);

        if (FALSE === $currentPlayerIdx) {
            $this->message = 'You are not a participant in this game';
            return FALSE;
        }

        if (FALSE === $game->waitingOnActionArray[$currentPlayerIdx]) {
            $this->message = 'You are not the active player';
            return FALSE;
        };

        $doesTimeStampAgree =
            ('ignore' === $postedTimestamp) ||
            ($postedTimestamp == $this->timestamp);
        $doesRoundNumberAgree =
            ('ignore' === $roundNumber) ||
            ($roundNumber == $game->roundNumber);
        $doesGameStateAgree = $expectedGameState == $game->gameState;

        $this->message = 'Game state is not current';
        return ($doesTimeStampAgree &&
                $doesRoundNumberAgree &&
                $doesGameStateAgree);
    }

    // Enter recent game actions into the action log
    // Note: it might be possible for this to be a protected function
    protected function log_game_actions(BMGame $game) {
        $query = 'INSERT INTO game_action_log ' .
                 '(game_id, game_state, action_type, acting_player, message) ' .
                 'VALUES ' .
                 '(:game_id, :game_state, :action_type, :acting_player, :message)';
        foreach ($game->actionLog as $gameAction) {
            $statement = self::$conn->prepare($query);
            $statement->execute(
                array(':game_id'     => $game->gameId,
                      ':game_state' => $gameAction->gameState,
                      ':action_type' => $gameAction->actionType,
                      ':acting_player' => $gameAction->actingPlayerId,
                      ':message'    => json_encode($gameAction->params))
            );
        }
        $game->empty_action_log();
    }

    protected function load_game_action_log(BMGame $game, $logEntryLimit) {
        try {
            $query = 'SELECT UNIX_TIMESTAMP(action_time) AS action_timestamp, ' .
                     'game_state,action_type,acting_player,message ' .
                     'FROM game_action_log ' .
                     'WHERE game_id = :game_id ORDER BY id DESC';
            if (!is_null($logEntryLimit)) {
                $query = $query . ' LIMIT ' . $logEntryLimit;
            }

            $statement = self::$conn->prepare($query);
            $statement->execute(array(':game_id' => $game->gameId));
            $logEntries = array();
            $playerIdNames = $this->get_player_name_mapping($game);
            while ($row = $statement->fetch()) {
                $params = json_decode($row['message'], TRUE);
                if (!($params)) {
                    $params = $row['message'];
                }
                $gameAction = new BMGameAction(
                    $row['game_state'],
                    $row['action_type'],
                    $row['acting_player'],
                    $params
                );

                // Only add the message to the log if one is returned: friendly_message() may
                // intentionally return no message if providing one would leak information
                $message = $gameAction->friendly_message($playerIdNames, $game->roundNumber, $game->gameState);
                if ($message) {
                    $logEntries[] = array(
                        'timestamp' => (int)$row['action_timestamp'],
                        'message' => $message,
                    );
                }
            }

            return $logEntries;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_game_action_log: ' .
                $e->getMessage()
            );
            $this->message = 'Internal error while reading log entries';
            return NULL;
        }
    }

    // Create a status message based on recent game actions
    protected function load_message_from_game_actions(BMGame $game) {
        $this->message = '';
        $playerIdNames = $this->get_player_name_mapping($game);
        foreach ($game->actionLog as $gameAction) {
            $this->message .= $gameAction->friendly_message(
                $playerIdNames,
                $game->roundNumber,
                $game->gameState
            ) . '. ';
        }
    }

    protected function log_game_chat(BMGame $game) {
        $this->db_insert_chat(
            $game->chat['playerIdx'],
            $game->gameId,
            $game->chat['chat']
        );
    }

    // Insert a new chat message into the database
    protected function db_insert_chat($playerId, $gameId, $chat) {

        $query = 'INSERT INTO game_chat_log ' .
                 '(game_id, chatting_player, message) ' .
                 'VALUES ' .
                 '(:game_id, :chatting_player, :message)';
        $statement = self::$conn->prepare($query);
        $statement->execute(
            array(':game_id'         => $gameId,
                  ':chatting_player' => $playerId,
                  ':message'         => $chat)
        );
    }

    // Modify an existing chat message in the database
    protected function db_update_chat($playerId, $gameId, $editTimestamp, $chat) {
        $query = 'UPDATE game_chat_log ' .
                 'SET message = :message, chat_time = now() ' .
                 'WHERE game_id = :game_id ' .
                 'AND chatting_player = :player_id ' .
                 'AND UNIX_TIMESTAMP(chat_time) = :timestamp ' .
                 'ORDER BY id DESC ' .
                 'LIMIT 1';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':message' => $chat,
                                  ':game_id' => $gameId,
                                  ':player_id' => $playerId,
                                  ':timestamp' => $editTimestamp));
    }

    // Delete an existing chat message in the database
    protected function db_delete_chat($playerId, $gameId, $editTimestamp) {
        $query = 'DELETE FROM game_chat_log ' .
                 'WHERE game_id = :game_id ' .
                 'AND chatting_player = :player_id ' .
                 'AND UNIX_TIMESTAMP(chat_time) = :timestamp ' .
                 'ORDER BY id DESC ' .
                 'LIMIT 1';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':game_id' => $gameId,
                                  ':player_id' => $playerId,
                                  ':timestamp' => $editTimestamp));
    }

    protected function load_game_chat_log(BMGame $game, $logEntryLimit) {
        try {
            $sqlParameters = array(':game_id' => $game->gameId);
            $query =
                'SELECT ' .
                    'UNIX_TIMESTAMP(chat_time) AS chat_timestamp, ' .
                    'chatting_player, ' .
                    'message ' .
                'FROM game_chat_log ' .
                'WHERE game_id = :game_id ';
            if ($game->gameState != BMGameState::END_GAME && !is_null($game->previousGameId)) {
                $query .= 'OR game_id = :previous_game_id ';
                $sqlParameters[':previous_game_id'] = $game->previousGameId;
            }
            $query .= 'ORDER BY id DESC ' ;
            if (!is_null($logEntryLimit)) {
                $query .= 'LIMIT :log_entry_limit';
                $sqlParameters[':log_entry_limit'] = $logEntryLimit;
            }

            $statement = self::$conn->prepare($query);
            $statement->execute($sqlParameters);
            $chatEntries = array();
            while ($row = $statement->fetch()) {
                $chatEntries[] = array(
                    'timestamp' => (int)$row['chat_timestamp'],
                    'player' => $this->get_player_name_from_id($row['chatting_player']),
                    'message' => $row['message'],
                );
            }
            return $chatEntries;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_game_chat_log: ' .
                $e->getMessage()
            );
            $this->message = 'Internal error while reading chat entries';
            return NULL;
        }
    }

   // Can the active player edit the most recent chat entry in this game?
    protected function find_editable_chat_timestamp(
        $game,
        $currentPlayerIdx,
        $playerNameArray,
        $chatLogEntries,
        $actionLogEntries
    ) {

        // Completed games can't be modified
        if ($game->gameState >= BMGameState::END_GAME) {
            return FALSE;
        }

        // If there are no chat entries, none can be modified
        if (count($chatLogEntries) == 0) {
            return FALSE;
        }

        // Only the most recent chat entry can be modified --- was
        // it made by the active player?
        if ((FALSE === $currentPlayerIdx) ||
            ($playerNameArray[$currentPlayerIdx] != $chatLogEntries[0]['player'])) {
            return FALSE;
        }

        // save_game() saves action log entries before chat log
        // entries.  So, if there are action log entries, and the
        // chat log entry predates the most recent action log entry,
        // it is not current
        if ((count($actionLogEntries) > 0) &&
            ($chatLogEntries[0]['timestamp'] < $actionLogEntries[0]['timestamp'])) {
            return FALSE;
        }

        // The active player can edit the most recent chat entry:
        // return its timestamp so it can be identified later
        return $chatLogEntries[0]['timestamp'];
    }

    // Can the active player insert a new chat entry (without an attack) right now?
    protected function chat_is_insertable(
        $game,
        $currentPlayerIdx,
        $playerNameArray,
        $chatLogEntries,
        $actionLogEntries
    ) {

        // Completed games can't be modified
        if ($game->gameState >= BMGameState::END_GAME) {
            return FALSE;
        }

        // If the player is not in the game, they can't insert chat
        if (FALSE === $currentPlayerIdx) {
            return FALSE;
        }

        // If the game is awaiting action from a player, that player
        // can't chat without taking an action
        if (TRUE === $game->waitingOnActionArray[$currentPlayerIdx]) {
            return FALSE;
        }

        // If the most recent chat entry was made by the active
        // player, and is current, that player can't insert a new one
        if ((count($chatLogEntries) > 0) &&
            ($playerNameArray[$currentPlayerIdx] == $chatLogEntries[0]['player']) &&
            (count($actionLogEntries) > 0) &&
            ($chatLogEntries[0]['timestamp'] >= $actionLogEntries[0]['timestamp'])) {
            return FALSE;
        }

        // The active player can insert a new chat entry
        return TRUE;
    }

    public function submit_chat(
        $playerId,
        $gameId,
        $editTimestamp,
        $chat
    ) {
        try {
            $game = $this->load_game($gameId);
            $currentPlayerIdx = array_search($playerId, $game->playerIdArray);

            foreach ($game->playerIdArray as $gamePlayerId) {
                $playerNameArray[] = $this->get_player_name_from_id($gamePlayerId);
            }
            $lastChatEntryList = $this->load_game_chat_log($game, 1);
            $lastActionEntryList = $this->load_game_action_log($game, 1);

            if ($editTimestamp) {
                // player is trying to edit a given chat entry -
                // do this if it's valid
                $gameChatEditable = $this->find_editable_chat_timestamp(
                    $game,
                    $currentPlayerIdx,
                    $playerNameArray,
                    $lastChatEntryList,
                    $lastActionEntryList
                );
                if ($editTimestamp == $gameChatEditable) {
                    if (strlen($chat) > 0) {
                        $this->db_update_chat($playerId, $gameId, $editTimestamp, $chat);
                        $this->message = 'Updated previous game message';
                        return TRUE;
                    } else {
                        $this->db_delete_chat($playerId, $gameId, $editTimestamp);
                        $this->message = 'Deleted previous game message';
                        return TRUE;
                    }
                } else {
                    $this->message = 'You can\'t edit the requested chat message now';
                    return FALSE;
                }
            } else {
                // player is trying to insert a new chat entry -
                // do this if it's valid
                $gameChatInsertable = $this->chat_is_insertable(
                    $game,
                    $currentPlayerIdx,
                    $playerNameArray,
                    $lastChatEntryList,
                    $lastActionEntryList
                );
                if ($gameChatInsertable) {
                    if (strlen($chat) > 0) {
                        $this->db_insert_chat($playerId, $gameId, $chat);
                        $this->message = 'Added game message';
                        return TRUE;
                    } else {
                        $this->message = 'No game message specified';
                        return FALSE;
                    }
                } else {
                    $this->message = 'You can\'t add a new chat message now';
                    return FALSE;
                }
            }

        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::submit_chat: ' .
                $e->getMessage()
            );
            $this->message = 'Internal error while updating game chat';
        }
    }

    public function join_open_game($currentPlayerId, $gameId) {
        try {
            $game = $this->load_game($gameId);

            // check that there are still unspecified players and
            // that the player is not already part of the game
            $emptyPlayerIdx = NULL;
            $isPlayerPartOfGame = FALSE;

            foreach ($game->playerIdArray as $playerIdx => $playerId) {
                if (is_null($playerId) && is_null($emptyPlayerIdx)) {
                    $emptyPlayerIdx = $playerIdx;
                } elseif ($currentPlayerId == $playerId) {
                    $isPlayerPartOfGame = TRUE;
                    break;
                }
            }

            if ($isPlayerPartOfGame) {
                $this->message = 'You are already playing in this game.';
                return FALSE;
            }

            if (is_null($emptyPlayerIdx)) {
                $this->message = 'No empty player slots in game '.$gameId.'.';
                return FALSE;
            }

            $query = 'UPDATE game_player_map SET player_id = :player_id '.
                     'WHERE game_id = :game_id '.
                     'AND position = :position';
            $statement = self::$conn->prepare($query);

            $statement->execute(array(':game_id'   => $gameId,
                                      ':player_id' => $currentPlayerId,
                                      ':position'  => $emptyPlayerIdx));

            $query = 'UPDATE game SET start_time = FROM_UNIXTIME(:start_time) '.
                     'WHERE id = :id';
            $statement = self::$conn->prepare($query);

            $statement->execute(array(':start_time' => time(),
                                      ':id'         => $gameId));

            $game = $this->load_game($gameId);
            $this->save_game($game);

            return TRUE;
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::join_open_game: ".
                $e->getMessage()
            );
            $this->message = 'Internal error while joining open game';
        }
    }

    public function select_button(
        $playerId,
        $gameId,
        $buttonName
    ) {
        try {
            if (empty($buttonName)) {
                return FALSE;
            }

            $game = $this->load_game($gameId);

            $playerIdx = array_search($playerId, $game->playerIdArray);

            if (FALSE === $playerIdx) {
                $this->message = 'Player is not a participant in game.';
                return FALSE;
            }

            if (!is_null($game->buttonArray[$playerIdx])) {
                $this->message = 'Button has already been selected.';
                return FALSE;
            }

            $query = 'SELECT id FROM button '.
                     'WHERE name = :button_name';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':button_name' => $buttonName));
            $fetchData = $statement->fetch();
            if (FALSE === $fetchData) {
                $this->message = 'Button select failed because button name was not valid.';
                return FALSE;
            }
            $buttonId = $fetchData[0];

            $query = 'UPDATE game_player_map SET button_id = :button_id '.
                     'WHERE game_id = :game_id '.
                     'AND player_id = :player_id';

            $statement = self::$conn->prepare($query);

            $statement->execute(array(':game_id'   => $gameId,
                                      ':player_id' => $playerId,
                                      ':button_id' => $buttonId));

            $query = 'UPDATE game SET start_time = FROM_UNIXTIME(:start_time) '.
                     'WHERE id = :id';
            $statement = self::$conn->prepare($query);

            $statement->execute(array(':start_time' => time(),
                                      ':id'         => $gameId));

            $game = $this->load_game($gameId);
            $this->save_game($game);

            return TRUE;

        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::select_button: ".
                $e->getMessage()
            );
            $this->message = 'Internal error while selecting button';
        }
    }

    public function submit_die_values(
        $playerId,
        $gameId,
        $roundNumber,
        $swingValueArray,
        $optionValueArray
    ) {
        try {
            $game = $this->load_game($gameId);
            $currentPlayerIdx = array_search($playerId, $game->playerIdArray);

            // check that the timestamp and the game state are correct, and that
            // the die values still need to be set
            if (!$this->is_action_current(
                $game,
                BMGameState::SPECIFY_DICE,
                'ignore',
                $roundNumber,
                $playerId
            )) {
                $this->message = 'Dice sizes no longer need to be set';
                return NULL;
            }

            $isSwingSetSuccessful = $this->set_swing_values($swingValueArray, $currentPlayerIdx, $game);
            if (!$isSwingSetSuccessful) {
                return NULL;
            }

            $this->set_option_values($optionValueArray, $currentPlayerIdx, $game);

            // Create the action log entry for choosing die values
            // now, so it will happen before any initiative actions.
            // If the swing/option selection is unsuccessful,
            // save_game() won't be called, so this action log entry
            // will simply be dropped.
            $optionLogArray = array();
            foreach ($optionValueArray as $dieIdx => $optionValue) {
                $dieRecipe = $game->activeDieArrayArray[$currentPlayerIdx][$dieIdx]->recipe;
                $optionLogArray[$dieRecipe] = $optionValue;
            }
            $game->log_action(
                'choose_die_values',
                $game->playerIdArray[$currentPlayerIdx],
                array(
                    'roundNumber' => $game->roundNumber,
                    'swingValues' => $swingValueArray,
                    'optionValues' => $optionLogArray,
                )
            );

            $game->proceed_to_next_user_action();

            // check for successful swing value set
            if ((FALSE == $game->waitingOnActionArray[$currentPlayerIdx]) ||
                ($game->gameState > BMGameState::SPECIFY_DICE) ||
                ($game->roundNumber > $roundNumber)) {

                $this->save_game($game);
                $this->message = 'Successfully set die sizes';
                return TRUE;
            } else {
                if ($game->message) {
                    $this->message = $game->message;
                } else {
                    $this->message = 'Failed to set die sizes';
                }
                return NULL;
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::submit_die_values: ' .
                $e->getMessage()
            );
            $this->message = 'Internal error while setting die sizes';
        }
    }

    protected function set_swing_values($swingValueArray, $currentPlayerIdx, $game) {
        $game->swingValueArrayArray[$currentPlayerIdx] = $swingValueArray;
        $swingRequestArray = $game->swingRequestArrayArray[$currentPlayerIdx];
        if (is_array($swingRequestArray)) {
            $swingRequested = array_keys($game->swingRequestArrayArray[$currentPlayerIdx]);
            sort($swingRequested);
        } else {
            $swingRequested = array();
        }

        if (is_array($swingValueArray)) {
            $swingSubmitted = array_keys($swingValueArray);
            sort($swingSubmitted);
        } else {
            $swingSubmitted = array();
        }

        $isSwingSetSuccessful = ($swingRequested == $swingSubmitted);

        if (!$isSwingSetSuccessful) {
            $this->message = 'Wrong swing values submitted: expected ' . implode(',', $swingRequested);
        }

        return $isSwingSetSuccessful;
    }

    protected function set_option_values($optionValueArray, $currentPlayerIdx, $game) {
        if (is_array($optionValueArray)) {
            foreach ($optionValueArray as $dieIdx => $optionValue) {
                $game->optValueArrayArray[$currentPlayerIdx][$dieIdx] = $optionValue;
            }
        }
    }

    protected function submit_swing_values(
        $playerId,
        $gameId,
        $roundNumber,
        $swingValueArray
    ) {
        try {
            $game = $this->load_game($gameId);
            $currentPlayerIdx = array_search($playerId, $game->playerIdArray);

            // check that the timestamp and the game state are correct, and that
            // the swing values still need to be set
            if (!$this->is_action_current(
                $game,
                BMGameState::SPECIFY_DICE,
                'ignore',
                $roundNumber,
                $playerId
            )) {
                $this->message = 'Swing dice no longer need to be set';
                return NULL;
            }

            // try to set swing values
            $swingRequestArray = $game->swingRequestArrayArray[$currentPlayerIdx];
            if (is_array($swingRequestArray)) {
                $swingRequested = array_keys($game->swingRequestArrayArray[$currentPlayerIdx]);
                sort($swingRequested);
            } else {
                $swingRequested = array();
            }

            if (is_array($swingValueArray)) {
                $swingSubmitted = array_keys($swingValueArray);
                sort($swingSubmitted);
            } else {
                $swingSubmitted = array();
            }

            if ($swingRequested != $swingSubmitted) {
                $this->message = 'Wrong swing values submitted: expected ' . implode(',', $swingRequested);
                return NULL;
            }

            $game->swingValueArrayArray[$currentPlayerIdx] = $swingValueArray;

            $game->proceed_to_next_user_action();

            // check for successful swing value set
            if ((FALSE == $game->waitingOnActionArray[$currentPlayerIdx]) ||
                ($game->gameState > BMGameState::SPECIFY_DICE) ||
                ($game->roundNumber > $roundNumber)) {
                $game->log_action(
                    'choose_swing',
                    $game->playerIdArray[$currentPlayerIdx],
                    array(
                        'roundNumber' => $game->roundNumber,
                        'swingValues' => $swingValueArray,
                    )
                );
                $this->save_game($game);
                $this->message = 'Successfully set swing values';
                return TRUE;
            } else {
                if ($game->message) {
                    $this->message = $game->message;
                } else {
                    $this->message = 'Failed to set swing values';
                }
                return NULL;
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::submit_swing_values: ' .
                $e->getMessage()
            );
            $this->message = 'Internal error while setting swing values';
        }
    }

    protected function submit_option_values(
        $playerId,
        $gameId,
        $roundNumber,
        $optionValueArray
    ) {
        try {
            $game = $this->load_game($gameId);
            $currentPlayerIdx = array_search($playerId, $game->playerIdArray);

            // check that the timestamp and the game state are correct, and that
            // the option values still need to be set
            if (!$this->is_action_current(
                $game,
                BMGameState::SPECIFY_DICE,
                'ignore',
                $roundNumber,
                $playerId
            )) {
                $this->message = 'Option dice no longer need to be set';
                return NULL;
            }

            // try to set option values
            foreach ($optionValueArray as $dieIdx => $optionValue) {
                $game->optValueArrayArray[$currentPlayerIdx][$dieIdx] = $optionValue;
            }
            $game->proceed_to_next_user_action();

            // check for successful option value set
            if ((FALSE == $game->waitingOnActionArray[$currentPlayerIdx]) ||
                ($game->gameState > BMGameState::SPECIFY_DICE) ||
                ($game->roundNumber > $roundNumber)) {
                $game->log_action(
                    'choose_option',
                    $game->playerIdArray[$currentPlayerIdx],
                    array(
                        'roundNumber' => $game->roundNumber,
                        'optionValues' => $optionValueArray,
                    )
                );
                $this->save_game($game);
                $this->message = 'Successfully set option values';
                return TRUE;
            } else {
                if ($game->message) {
                    $this->message = $game->message;
                } else {
                    $this->message = 'Failed to set option values';
                }
                return NULL;
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::submit_option_values: ' .
                $e->getMessage()
            );
            $this->message = 'Internal error while setting option values';
        }
    }

    public function submit_turn(
        $playerId,
        $gameId,
        $roundNumber,
        $submitTimestamp,
        $dieSelectStatus,
        $attackType,
        $attackerIdx,
        $defenderIdx,
        $chat
    ) {
        try {
            $game = $this->load_game($gameId);
            if (!$this->is_action_current(
                $game,
                BMGameState::START_TURN,
                $submitTimestamp,
                $roundNumber,
                $playerId
            )) {
                $this->message = 'It is not your turn to attack right now';
                return NULL;
            }

            // N.B. dieSelectStatus should contain boolean values of whether each
            // die is selected, starting with attacker dice and concluding with
            // defender dice

            // attacker and defender indices are provided in POST
            $attackers = array();
            $defenders = array();
            $attackerDieIdx = array();
            $defenderDieIdx = array();

            // divide selected dice up into attackers and defenders
            $nAttackerDice = count($game->activeDieArrayArray[$attackerIdx]);
            $nDefenderDice = count($game->activeDieArrayArray[$defenderIdx]);

            for ($dieIdx = 0; $dieIdx < $nAttackerDice; $dieIdx++) {
                if (filter_var(
                    $dieSelectStatus["playerIdx_{$attackerIdx}_dieIdx_{$dieIdx}"],
                    FILTER_VALIDATE_BOOLEAN
                )) {
                    $attackers[] = $game->activeDieArrayArray[$attackerIdx][$dieIdx];
                    $attackerDieIdx[] = $dieIdx;
                }
            }

            for ($dieIdx = 0; $dieIdx < $nDefenderDice; $dieIdx++) {
                if (filter_var(
                    $dieSelectStatus["playerIdx_{$defenderIdx}_dieIdx_{$dieIdx}"],
                    FILTER_VALIDATE_BOOLEAN
                )) {
                    $defenders[] = $game->activeDieArrayArray[$defenderIdx][$dieIdx];
                    $defenderDieIdx[] = $dieIdx;
                }
            }

            // populate BMAttack object for the specified attack
            $game->attack = array($attackerIdx, $defenderIdx,
                                  $attackerDieIdx, $defenderDieIdx,
                                  $attackType);
            $attack = BMAttack::get_instance($attackType);

            foreach ($attackers as $attackDie) {
                $attack->add_die($attackDie);
            }

            $game->add_chat($playerId, $chat);

            // validate the attack and output the result
            if ($attack->validate_attack($game, $attackers, $defenders)) {
                $this->save_game($game);

                // On success, don't set a message, because one will be set from the action log
                return TRUE;
            } else {
                if (empty($attack->validationMessage)) {
                    $this->message = 'Requested attack is not valid';
                } else {
                    $this->message = $attack->validationMessage;
                }
                return NULL;
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::submit_turn: ' .
                $e->getMessage()
            );
            $this->message = 'Internal error while submitting turn';
        }
    }

    // react_to_auxiliary expects the following inputs:
    //
    //   $action:
    //       One of {'add', 'decline'}.
    //
    //   $dieIdx:
    //       (i)  If this is an 'add' action, then this is the die index of the
    //            die to be added.
    //       (ii) If this is a 'decline' action, then this will be ignored.
    //
    // The function returns a boolean telling whether the reaction has been
    // successful.
    // If it fails, $this->message will say why it has failed.

    public function react_to_auxiliary(
        $playerId,
        $gameId,
        $action,
        $dieIdx = NULL
    ) {
        try {
            $game = $this->load_game($gameId);
            if (!$this->is_action_current(
                $game,
                BMGameState::CHOOSE_AUXILIARY_DICE,
                'ignore',
                'ignore',
                $playerId
            )) {
                return FALSE;
            }

            $playerIdx = array_search($playerId, $game->playerIdArray);

            switch ($action) {
                case 'add':
                    if (!array_key_exists($dieIdx, $game->activeDieArrayArray[$playerIdx]) ||
                        !$game->activeDieArrayArray[$playerIdx][$dieIdx]->has_skill('Auxiliary')) {
                        $this->message = 'Invalid auxiliary choice';
                        return FALSE;
                    }
                    $die = $game->activeDieArrayArray[$playerIdx][$dieIdx];
                    $die->selected = TRUE;
                    $waitingOnActionArray = $game->waitingOnActionArray;
                    $waitingOnActionArray[$playerIdx] = FALSE;
                    $game->waitingOnActionArray = $waitingOnActionArray;
                    $game->log_action(
                        'add_auxiliary',
                        $game->playerIdArray[$playerIdx],
                        array(
                            'roundNumber' => $game->roundNumber,
                            'die' => $die->get_action_log_data(),
                        )
                    );
                    $this->message = 'Auxiliary die chosen successfully';
                    break;
                case 'decline':
                    $game->waitingOnActionArray = array_fill(0, $game->nPlayers, FALSE);
                    $game->log_action(
                        'decline_auxiliary',
                        $game->playerIdArray[$playerIdx],
                        array('declineAuxiliary' => TRUE)
                    );
                    $this->message = 'Declined auxiliary dice';
                    break;
                default:
                    $this->message = 'Invalid response to auxiliary choice.';
                    return FALSE;
            }
            $this->save_game($game);

            return TRUE;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::react_to_auxiliary: ' .
                $e->getMessage()
            );
            $this->message = 'Internal error while making auxiliary decision';
            return FALSE;
        }
    }

    // react_to_reserve expects the following inputs:
    //
    //   $action:
    //       One of {'add', 'decline'}.
    //
    //   $dieIdx:
    //       (i)  If this is an 'add' action, then this is the die index of the
    //            die to be added.
    //       (ii) If this is a 'decline' action, then this will be ignored.
    //
    // The function returns a boolean telling whether the reaction has been
    // successful.
    // If it fails, $this->message will say why it has failed.

    public function react_to_reserve(
        $playerId,
        $gameId,
        $action,
        $dieIdx = NULL
    ) {
        try {
            $game = $this->load_game($gameId);
            if (!$this->is_action_current(
                $game,
                BMGameState::CHOOSE_RESERVE_DICE,
                'ignore',
                'ignore',
                $playerId
            )) {
                return FALSE;
            }

            $playerIdx = array_search($playerId, $game->playerIdArray);

            switch ($action) {
                case 'add':
                    if (!array_key_exists($dieIdx, $game->activeDieArrayArray[$playerIdx]) ||
                        !$game->activeDieArrayArray[$playerIdx][$dieIdx]->has_skill('Reserve')) {
                        $this->message = 'Invalid reserve choice';
                        return FALSE;
                    }
                    $die = $game->activeDieArrayArray[$playerIdx][$dieIdx];
                    $die->selected = TRUE;
                    $waitingOnActionArray = $game->waitingOnActionArray;
                    $waitingOnActionArray[$playerIdx] = FALSE;
                    $game->waitingOnActionArray = $waitingOnActionArray;
                    $game->log_action(
                        'add_reserve',
                        $game->playerIdArray[$playerIdx],
                        array( 'die' => $die->get_action_log_data(), )
                    );
                    $this->message = 'Reserve die chosen successfully';
                    break;
                case 'decline':
                    $waitingOnActionArray = $game->waitingOnActionArray;
                    $waitingOnActionArray[$playerIdx] = FALSE;
                    $game->waitingOnActionArray = $waitingOnActionArray;
                    $game->log_action(
                        'decline_reserve',
                        $game->playerIdArray[$playerIdx],
                        array('declineReserve' => TRUE)
                    );
                    $this->message = 'Declined reserve dice';
                    break;
                default:
                    $this->message = 'Invalid response to reserve choice.';
                    return FALSE;
            }

            $this->save_game($game);


            return TRUE;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::react_to_reserve: ' .
                $e->getMessage()
            );
            $this->message = 'Internal error while making reserve decision';
            return FALSE;
        }
    }

    // react_to_initiative expects the following inputs:
    //
    //   $action:
    //       One of {'chance', 'focus', 'decline'}.
    //
    //   $dieIdxArray:
    //       (i)   If this is a 'chance' action, then an array containing the
    //             index of the chance die that is being rerolled.
    //       (ii)  If this is a 'focus' action, then this is the nonempty array
    //             of die indices corresponding to the die values in
    //             dieValueArray. This can be either the indices of ALL focus
    //             dice OR just a subset.
    //       (iii) If this is a 'decline' action, then this will be ignored.
    //
    //   $dieValueArray:
    //       This is only used for the 'focus' action. It is a nonempty array
    //       containing the values of the focus dice that have been chosen by
    //       the user. The die indices of the dice being specified are given in
    //       $dieIdxArray.
    //
    // The function returns a boolean telling whether the reaction has been
    // successful.
    // If it fails, $this->message will say why it has failed.

    public function react_to_initiative(
        $playerId,
        $gameId,
        $roundNumber,
        $submitTimestamp,
        $action,
        $dieIdxArray = NULL,
        $dieValueArray = NULL
    ) {
        try {
            $game = $this->load_game($gameId);
            if (!$this->is_action_current(
                $game,
                BMGameState::REACT_TO_INITIATIVE,
                $submitTimestamp,
                $roundNumber,
                $playerId
            )) {
                return FALSE;
            }

            $playerIdx = array_search($playerId, $game->playerIdArray);

            $argArray = array('action' => $action,
                              'playerIdx' => $playerIdx);

            switch ($action) {
                case 'chance':
                    if (1 != count($dieIdxArray)) {
                        $this->message = 'Only one chance die can be rerolled';
                        return FALSE;
                    }
                    $argArray['rerolledDieIdx'] = (int)$dieIdxArray[0];
                    break;
                case 'focus':
                    if (count($dieIdxArray) != count($dieValueArray)) {
                        $this->message = 'Mismatch in number of indices and values';
                        return FALSE;
                    }
                    $argArray['focusValueArray'] = array();
                    foreach ($dieIdxArray as $tempIdx => $dieIdx) {
                        $argArray['focusValueArray'][$dieIdx] = $dieValueArray[$tempIdx];
                    }
                    break;
                case 'decline':
                    $argArray['dieIdxArray'] = $dieIdxArray;
                    $argArray['dieValueArray'] = $dieValueArray;
                    break;
                default:
                    $this->message = 'Invalid action to respond to initiative.';
                    return FALSE;
            }

            $isSuccessful = $game->react_to_initiative($argArray);
            if ($isSuccessful) {
                $this->save_game($game);
                $this->message = 'Successfully gained initiative';
            } else {
                $this->message = $game->message;
            }

            return $isSuccessful;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::react_to_initiative: ' .
                $e->getMessage()
            );
            $this->message = 'Internal error while reacting to initiative';
            return FALSE;
        }
    }

    // adjust_fire expects the following inputs:
    //
    //   $action:
    //       One of {'turndown', 'cancel'}.
    //
    //   $dieIdxArray:
    //       (i)  If this is a 'turndown' action, then this is the nonempty array
    //             of die indices corresponding to the die values in
    //             dieValueArray. This can be either the indices of ALL fire
    //             dice OR just a subset.
    //       (ii) If this is a 'cancel' action, then this will be ignored.
    //
    //   $dieValueArray:
    //       This is only used for the 'turndown' action. It is a nonempty array
    //       containing the values of the fire dice that have been chosen by
    //       the user. The die indices of the dice being specified are given in
    //       $dieIdxArray.
    //
    // The function returns a boolean telling whether the reaction has been
    // successful.
    // If it fails, $this->message will say why it has failed.

    public function adjust_fire(
        $playerId,
        $gameId,
        $roundNumber,
        $submitTimestamp,
        $action,
        $dieIdxArray = NULL,
        $dieValueArray = NULL
    ) {
        try {
            $game = $this->load_game($gameId);
            if (!$this->is_action_current(
                $game,
                BMGameState::ADJUST_FIRE_DICE,
                $submitTimestamp,
                $roundNumber,
                $playerId
            )) {
                return FALSE;
            }

            $playerIdx = array_search($playerId, $game->playerIdArray);

            $argArray = array('action' => $action,
                              'playerIdx' => $playerIdx);

            switch ($action) {
                case 'turndown':
                    if (count($dieIdxArray) != count($dieValueArray)) {
                        $this->message = 'Mismatch in number of indices and values';
                        return FALSE;
                    }

                    $argArray['fireValueArray'] = array();
                    foreach ($dieIdxArray as $tempIdx => $dieIdx) {
                        $argArray['fireValueArray'][$dieIdx] = $dieValueArray[$tempIdx];
                    }
                    break;
                case 'cancel':
                    $argArray['dieIdxArray'] = $dieIdxArray;
                    $argArray['dieValueArray'] = $dieValueArray;
                    break;
                default:
                    $this->message = 'Invalid action to adjust fire dice.';
                    return FALSE;
            }

            $isSuccessful = $game->react_to_firing($argArray);
            if ($isSuccessful) {
                $this->save_game($game);
            }
            $this->message = $game->message;

            return $isSuccessful;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::adjust_fire: ' .
                $e->getMessage()
            );
            $this->message = 'Internal error while adjusting fire dice';
            return FALSE;
        }
    }

    public function dismiss_game($playerId, $gameId) {
        try {
            $query =
                'SELECT s.name AS "status", m.was_game_dismissed ' .
                'FROM game AS g ' .
                'INNER JOIN game_status AS s ON s.id = g.status_id ' .
                    'LEFT JOIN game_player_map AS m ' .
                    'ON m.game_id = g.id AND m.player_id = :player_id ' .
                'WHERE g.id = :game_id';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':player_id' => $playerId,
                ':game_id' => $gameId,
            ));
            $fetchResult = $statement->fetchAll();

            if (count($fetchResult) == 0) {
                $this->message = "Game $gameId does not exist";
                return NULL;
            }
            if ($fetchResult[0]['status'] != 'COMPLETE') {
                $this->message = "Game $gameId isn't complete";
                return NULL;
            }
            if ($fetchResult[0]['was_game_dismissed'] === NULL) {
                $this->message = "You aren't a player of game $gameId";
                return NULL;
            }
            if ((int)$fetchResult[0]['was_game_dismissed'] == 1) {
                $this->message = "You have already dismissed game $gameId";
                return NULL;
            }

            $query =
                'UPDATE game_player_map ' .
                'SET was_game_dismissed = 1 ' .
                'WHERE player_id = :player_id AND game_id = :game_id';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':player_id' => $playerId,
                ':game_id' => $gameId,
            ));

            $this->message = 'Dismissing game succeeded';
            return TRUE;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::dismiss_game: ' .
                $e->getMessage()
            );
            $this->message = 'Internal error while dismissing a game';
            return FALSE;
        }
    }

    ////////////////////////////////////////////////////////////
    // Forum-related methods

    // Retrieves an overview of all of the boards available on the forum
    public function load_forum_overview($currentPlayerId) {
        try {
            $results = array();

            // Get the list of all boards, identifying the first new post on each
            $query =
                'SELECT ' .
                    'b_plus.*, ' .
                    'COUNT(t.id) AS number_of_threads, ' .
                    'first_new_post.thread_id AS first_new_post_thread_id ' .
                'FROM ' .
                    '(SELECT ' .
                        'b.*, ' .
                        '(SELECT v.id FROM forum_player_post_view AS v ' .
                        'WHERE v.board_id = b.id AND v.reader_player_id = :current_player_id AND v.is_new = 1 ' .
                        'ORDER BY v.creation_time ASC LIMIT 1) AS first_new_post_id ' .
                    'FROM forum_board AS b) AS b_plus ' .
                    'LEFT JOIN forum_thread AS t ' .
                        'ON t.board_id = b_plus.id AND t.deleted = 0 ' .
                    'LEFT JOIN forum_post AS first_new_post ' .
                        'ON first_new_post.id = b_plus.first_new_post_id ' .
                'GROUP BY b_plus.id ' .
                'ORDER BY b_plus.sort_order ASC;';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(':current_player_id' => $currentPlayerId));

            $boards = array();
            while ($row = $statement->fetch()) {
                $boards[] = array(
                    'boardId' => (int)$row['id'],
                    'boardName' => $row['name'],
                    'boardColor' => $row['board_color'],
                    'threadColor' => $row['thread_color'],
                    'description' => $row['description'],
                    'numberOfThreads' => (int)$row['number_of_threads'],
                    'firstNewPostId' => (int)$row['first_new_post_id'],
                    'firstNewPostThreadId' => (int)$row['first_new_post_thread_id'],
                );
            }

            $results['boards'] = $boards;
            $results['timestamp'] = strtotime('now');

            if ($results) {
                $this->message = 'Forum overview loading succeeded';
            }
            return $results;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_forum_overview: ' .
                $e->getMessage()
            );
            $this->message = 'Forum overview loading failed';
            return NULL;
        }
    }

    // Retrieves an overview of a specific forum board, plus information on all
    // the threads on that board
    public function load_forum_board($currentPlayerId, $boardId) {
        try {
            $results = array();

            // Get the details about the board itself
            $query =
                'SELECT b.* ' .
                'FROM forum_board AS b ' .
                'WHERE b.id = :board_id';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(':board_id' => $boardId));

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) != 1) {
                $this->message = 'Forum board loading failed';
                error_log('Wrong number of records returned for forum_board.id = ' . $boardId);
                return NULL;
            }
            $results['boardId'] = (int)$fetchResult[0]['id'];
            $results['boardName'] = $fetchResult[0]['name'];
            $results['boardColor'] = $fetchResult[0]['board_color'];
            $results['threadColor'] = $fetchResult[0]['thread_color'];
            $results['description'] = $fetchResult[0]['description'];

            // Get a list of threads on this board, with info on their old and new posts
            $query =
                'SELECT ' .
                    't_plus.*, ' .
                    'COUNT(all_posts.id) AS number_of_posts, ' .
                    'first_post_poster.name_ingame AS original_poster_name, ' .
                    'UNIX_TIMESTAMP(first_post.creation_time) AS original_creation_timestamp, ' .
                    'lastest_post_poster.name_ingame AS latest_poster_name, ' .
                    'UNIX_TIMESTAMP(lastest_post.last_update_time) AS latest_update_timestamp, ' .
                    't_plus.first_new_post_id ' .
                'FROM ' .
                    '(SELECT ' .
                        't.*, ' .
                        '(SELECT post.id FROM forum_post AS post ' .
                        'WHERE post.thread_id = t.id ' .
                        'ORDER BY post.creation_time ASC LIMIT 1) AS first_post_id, ' .
                        '(SELECT post.id FROM forum_post AS post ' .
                        'WHERE post.thread_id = t.id ' .
                        'ORDER BY post.last_update_time DESC LIMIT 1) AS lastest_post_id, ' .
                        '(SELECT v.id FROM forum_player_post_view AS v ' .
                        'WHERE v.thread_id = t.id AND v.reader_player_id = :current_player_id AND v.is_new = 1 ' .
                        'ORDER BY v.creation_time ASC LIMIT 1) AS first_new_post_id ' .
                    'FROM forum_thread AS t ' .
                    'WHERE t.board_id = :board_id AND t.deleted = 0) AS t_plus ' .
                    'LEFT JOIN forum_post AS all_posts ' .
                        'ON all_posts.thread_id = t_plus.id ' .
                    'LEFT JOIN forum_post AS first_post ' .
                        'ON first_post.id = t_plus.first_post_id ' .
                    'LEFT JOIN player AS first_post_poster ' .
                        'ON first_post_poster.id = first_post.poster_player_id ' .
                    'LEFT JOIN forum_post AS lastest_post ' .
                        'ON lastest_post.id = t_plus.lastest_post_id ' .
                    'LEFT JOIN player AS lastest_post_poster ' .
                        'ON lastest_post_poster.id = lastest_post.poster_player_id ' .
                'GROUP BY t_plus.id ' .
                'ORDER BY lastest_post.last_update_time DESC';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':current_player_id' => $currentPlayerId,
                ':board_id' => $boardId,
            ));

            $threads = array();
            while ($row = $statement->fetch()) {
                $threads[] = array(
                    'threadId' => $row['id'],
                    'threadTitle' => $row['title'],
                    'numberOfPosts' => (int)$row['number_of_posts'],
                    'originalPosterName' => $row['original_poster_name'],
                    'originalCreationTime' => (int)$row['original_creation_timestamp'],
                    'latestPosterName' => $row['latest_poster_name'],
                    'latestLastUpdateTime' => (int)$row['latest_update_timestamp'],
                    'firstNewPostId' => (int)$row['first_new_post_id'],
                );
            }

            $results['threads'] = $threads;
            $results['timestamp'] = strtotime('now');

            if ($results) {
                $this->message = 'Forum board loading succeeded';
            }
            return $results;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_forum_board: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    // Retrieves an overview of a specific forum thread, plus information on
    // the posts in that thread
    public function load_forum_thread($currentPlayerId, $threadId, $currentPostId) {
        try {
            $results = array();

            $playerColors = $this->load_player_colors($currentPlayerId);

            // Get the details about the thread itself
            $query =
                'SELECT t.*, b.name AS board_name, b.board_color, b.thread_color AS board_thread_color ' .
                'FROM forum_thread AS t ' .
                    'INNER JOIN forum_board AS b ON b.id = t.board_id ' .
                'WHERE t.id = :thread_id AND t.deleted = 0;';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(':thread_id' => $threadId));

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) != 1) {
                $this->message = 'Forum thread loading failed';
                error_log('Wrong number of records returned for forum_thread.id = ' . $threadId);
                return NULL;
            }
            $results['threadId'] = (int)$fetchResult[0]['id'];
            $results['threadTitle'] = $fetchResult[0]['title'];
            $results['boardId'] = (int)$fetchResult[0]['board_id'];
            $results['boardName'] = $fetchResult[0]['board_name'];
            $results['boardColor'] = $fetchResult[0]['board_color'];
            $results['boardThreadColor'] = $fetchResult[0]['board_thread_color'];
            $results['currentPostId'] = $currentPostId;

            // Get a list of posts in this thread
            $query =
                'SELECT ' .
                    'v.*, ' .
                    'UNIX_TIMESTAMP(v.creation_time) AS creation_timestamp, ' .
                    'UNIX_TIMESTAMP(v.last_update_time) AS last_update_timestamp ' .
                'FROM forum_player_post_view v ' .
                'WHERE v.thread_id = :thread_id AND v.reader_player_id = :current_player_id ' .
                'ORDER BY v.creation_time ASC;';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':current_player_id' => $currentPlayerId,
                ':thread_id' => $threadId,
            ));

            $posts = array();
            while ($row = $statement->fetch()) {
                $posterColor =
                    $this->determine_game_colors(
                        $currentPlayerId,
                        $playerColors,
                        (int)$row['poster_player_id'],
                        NULL
                    );
                $posts[] = array(
                    'postId' => (int)$row['id'],
                    'posterName' => $row['poster_name'],
                    'posterColor' => $posterColor['playerA'],
                    'creationTime' => (int)$row['creation_timestamp'],
                    'lastUpdateTime' => (int)$row['last_update_timestamp'],
                    'isNew' => ($row['is_new'] == 1),
                    'body' => (($row['deleted'] == 1) ? '[DELETED POST]' : $row['body']),
                    'deleted' => ($row['deleted'] == 1),
                );
            }

            $results['posts'] = $posts;
            $results['timestamp'] = strtotime('now');

            if ($results) {
                $this->message = 'Forum thread loading succeeded';
            }
            return $results;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_forum_thread: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    // Load the ID's of the next new post and its thread
    public function get_next_new_post($currentPlayerId) {
        try {
            $results = array();

            // Get the list of all boards, identifying the first new post on each
            $query =
                'SELECT v.id, v.thread_id ' .
                'FROM forum_player_post_view AS v ' .
                'WHERE v.reader_player_id = :current_player_id AND v.is_new = 1 ' .
                'ORDER BY v.creation_time ASC ' .
                'LIMIT 1;';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(':current_player_id' => $currentPlayerId));

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) != 1) {
                $results['nextNewPostId'] = NULL;
                $results['nextNewPostThreadId'] = NULL;
                $this->message = 'No new forum posts';
                return $results;
            }

            $results['nextNewPostId'] = (int)$fetchResult[0]['id'];
            $results['nextNewPostThreadId'] = (int)$fetchResult[0]['thread_id'];

            if ($results) {
                $this->message = 'Checked new forum posts successfully';
            }
            return $results;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_next_new_post: ' .
                $e->getMessage()
            );
            $this->message = 'New forum post check failed';
            return NULL;
        }
    }

    // Indicates that the reader has finished reading all of the posts on every
    // board which they care to read
    public function mark_forum_read($currentPlayerId, $timestamp) {
        try {
            $query = 'SELECT b.id FROM forum_board AS b;';

            $statement = self::$conn->prepare($query);
            $statement->execute();

            while ($row = $statement->fetch()) {
                $boardId = (int)$row['id'];
                $results = $this->mark_forum_board_read($currentPlayerId, $boardId, $timestamp, TRUE);
                if (!$results || !$results['success']) {
                    $this->message = 'Marking board ' . $boardId . ' read failed: ' . $this->message;
                    return NULL;
                }
            }

            $this->message = 'Entire forum marked read successfully';
            return $this->load_forum_overview($currentPlayerId);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::mark_forum_read: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }


    // Indicates that the reader has finished reading all of the posts on this
    // board which they care to read
    public function mark_forum_board_read($currentPlayerId, $boardId, $timestamp, $suppressResults = FALSE) {
        try {
            $query =
                'INSERT INTO forum_board_player_map ' .
                    '(board_id, player_id, read_time) ' .
                'VALUES ' .
                    '(:board_id, :current_player_id, FROM_UNIXTIME(:timestamp_insert)) ' .
                'ON DUPLICATE KEY UPDATE read_time = FROM_UNIXTIME(:timestamp_update);';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':board_id' => $boardId,
                ':current_player_id' => $currentPlayerId,
                ':timestamp_insert' => $timestamp,
                ':timestamp_update' => $timestamp,
            ));

            $this->message = 'Forum board marked read successfully';
            if ($suppressResults) {
                return array('success' => TRUE);
            } else {
                return $this->load_forum_overview($currentPlayerId);
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::mark_forum_board_read: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    // Indicates that the reader has finished reading all of the posts in this
    // thread which they care to read
    public function mark_forum_thread_read($currentPlayerId, $threadId, $boardId, $timestamp) {
        try {
            $query =
                'INSERT INTO forum_thread_player_map ' .
                    '(thread_id, player_id, read_time) ' .
                'VALUES (:thread_id, :current_player_id, FROM_UNIXTIME(:timestamp_insert)) ' .
                'ON DUPLICATE KEY UPDATE read_time = FROM_UNIXTIME(:timestamp_update);';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':thread_id' => $threadId,
                ':current_player_id' => $currentPlayerId,
                ':timestamp_insert' => $timestamp,
                ':timestamp_update' => $timestamp,
            ));

            $this->message = 'Forum thread marked read successfully';
            return $this->load_forum_board($currentPlayerId, $boardId);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::mark_forum_thread_read: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    // Adds a new thread to the specified board
    public function create_forum_thread($currentPlayerId, $boardId, $title, $body) {
        try {
            $query =
                'INSERT INTO forum_thread (board_id, title, deleted) ' .
                'VALUES (:board_id, :title, 0);';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':board_id' => $boardId,
                ':title' => $title,
            ));

            $statement = self::$conn->prepare('SELECT LAST_INSERT_ID()');
            $statement->execute();
            $fetchData = $statement->fetch();
            $threadId = (int)$fetchData[0];

            $query =
                'INSERT INTO forum_post ' .
                    '(thread_id, poster_player_id, creation_time, last_update_time, body, deleted) ' .
                'VALUES ' .
                    '(:thread_id, :current_player_id, NOW(), NOW(), :body, 0);';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':thread_id' => $threadId,
                ':current_player_id' => $currentPlayerId,
                ':body' => $body,
            ));

            $this->message = 'Forum thread created successfully';
            return $this->load_forum_thread($currentPlayerId, $threadId, NULL);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::create_forum_thread: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    // Adds a new post to the specified thread
    public function create_forum_post($currentPlayerId, $threadId, $body) {
        try {
            $query =
                'INSERT INTO forum_post ' .
                    '(thread_id, poster_player_id, creation_time, last_update_time, body, deleted) ' .
                'VALUES ' .
                    '(:thread_id, :current_player_id, NOW(), NOW(), :body, 0);';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':thread_id' => $threadId,
                ':current_player_id' => $currentPlayerId,
                ':body' => $body,
            ));

            $statement = self::$conn->prepare('SELECT LAST_INSERT_ID()');
            $statement->execute();
            $fetchData = $statement->fetch();
            $postId = (int)$fetchData[0];

            $results = $this->load_forum_thread($currentPlayerId, $threadId, $postId);

            if ($results) {
                $this->message = 'Forum post created successfully';
            }
            return $results;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::create_forum_post: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    // Changes the body of the specified post
    public function edit_forum_post($currentPlayerId, $postId, $body) {
        try {
            $query =
                'SELECT p.poster_player_id, p.deleted, p.thread_id ' .
                'FROM forum_post p ' .
                'WHERE p.id = :post_id;';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(':post_id' => $postId));

            $fetchResult = $statement->fetchAll();
            if (count($fetchResult) != 1) {
                $this->message = 'Post not found';
                return NULL;
            }
            if ((int)$fetchResult[0]['poster_player_id'] != $currentPlayerId) {
                $this->message = 'Post does not belong to you';
                return NULL;
            }
            if ((int)$fetchResult[0]['deleted'] == 1) {
                $this->message = 'Post was already deleted';
                return NULL;
            }
            $threadId = (int)$fetchResult[0]['thread_id'];

            $query =
                'UPDATE forum_post ' .
                'SET body = :body, last_update_time = NOW() ' .
                'WHERE id = :post_id;';

            $statement = self::$conn->prepare($query);
            $statement->execute(array(
                ':post_id' => $postId,
                ':body' => $body,
            ));

            $results = $this->load_forum_thread($currentPlayerId, $threadId, $postId);

            if ($results) {
                $this->message = 'Forum post edited successfully';
            }
            return $results;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::edit_forum_post: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    // End of Forum-related methods
    ////////////////////////////////////////////////////////////

    public function update_last_action_time($playerId, $gameId = NULL) {
        try {
            $query = 'UPDATE player SET last_action_time = now() WHERE id = :id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':id' => $playerId));

            if (is_null($gameId)) {
                return;
            }

            $query = 'UPDATE game_player_map SET last_action_time = now() '.
                     'WHERE player_id = :player_id '.
                     'AND game_id = :game_id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':player_id' => $playerId,
                                      ':game_id' => $gameId));

        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::update_last_action_time: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    public function update_last_access_time($playerId) {
        try {
            $query = 'UPDATE player SET last_access_time = now() WHERE id = :id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':id' => $playerId));
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::update_last_access_time: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    protected function get_config($conf_key) {
        try {
            $query = 'SELECT conf_value FROM config WHERE conf_key = :conf_key';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':conf_key' => $conf_key));
            $fetchResult = $statement->fetchAll();

            if (count($fetchResult) != 1) {
                error_log('Wrong number of config values with key ' . $conf_key);
                return NULL;
            }
            return $fetchResult[0]['conf_value'];
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_config: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    // Calculates the difference between two (unix-style) timespans and formats
    // the result as a friendly approximation like '7 days' or '12 minutes'.
    protected function get_friendly_time_span($firstTime, $secondTime) {
        $seconds = (int)($secondTime - $firstTime);
        if ($seconds < 0) {
            $seconds *= -1;
        }

        if ($seconds < 60) {
            return $this->count_noun($seconds, 'second');
        }

        $minutes = (int)($seconds / 60);
        if ($minutes < 60) {
            return $this->count_noun($minutes, 'minute');
        }

        $hours = (int)($minutes / 60);
        if ($hours < 24) {
            return $this->count_noun($hours, 'hour');
        }

        $days = (int)($hours / 24);
        return $this->count_noun($days, 'day');
    }

    // Turns a number (like 5) and a noun (like 'golden ring') into a phrase
    // like '5 golden rings', pluralizing if needed.
    // Note: does not handle funky plurals.
    protected function count_noun($count, $noun) {
        if ($count == 1) {
            return $count . ' ' . $noun;
        }
        if (substr($noun, -1) == 's') {
            return $count . ' ' . $noun . 'es';
        }
        return $count . ' ' . $noun . 's';
    }

    // Retrieves the colors that the user has saved in their preferences
    protected function load_player_colors($currentPlayerId) {
        $playerInfoArray = $this->get_player_info($currentPlayerId);

        $colors = array(
            'player' => $playerInfoArray['user_prefs']['player_color'],
            'opponent' => $playerInfoArray['user_prefs']['opponent_color'],
            'neutralA' => $playerInfoArray['user_prefs']['neutral_color_a'],
            'neutralB' => $playerInfoArray['user_prefs']['neutral_color_b'],
            // Itself an associative array of player ID's => color strings
            'battleBuddies' => array(),
        );
        return $colors;
    }

    // Determines which colors to use for the two players in a game.
    // $currentPlayerId is the player this is being displayed to.
    // $playerColors are the colors they've chosen as their preferences
    // (as returned by load_player_colors())
    // $gamePlayerIdA and $gamePlayerIdB are the two players in the game
    protected function determine_game_colors($currentPlayerId, $playerColors, $gamePlayerIdA, $gamePlayerIdB) {
        $gameColors = array();

        if ($gamePlayerIdA == $currentPlayerId) {
            $gameColors['playerA'] = $playerColors['player'];
            if (isset($playerColors['battleBuddies'][$gamePlayerIdB])) {
                $gameColors['playerB'] = $playerColors['battleBuddies'][$gamePlayerIdB];
            } else {
                $gameColors['playerB'] = $playerColors['opponent'];
            }
            return $gameColors;
        }

        if ($gamePlayerIdB == $currentPlayerId) {
            $gameColors['playerB'] = $playerColors['player'];
            if (isset($playerColors['battleBuddies'][$gamePlayerIdA])) {
                $gameColors['playerA'] = $playerColors['battleBuddies'][$gamePlayerIdA];
            } else {
                $gameColors['playerA'] = $playerColors['opponent'];
            }
            return $gameColors;
        }

        if (isset($playerColors['battleBuddies'][$gamePlayerIdA])) {
            $gameColors['playerA'] = $playerColors['battleBuddies'][$gamePlayerIdA];
        } else {
            $gameColors['playerA'] = $playerColors['neutralA'];
        }

        if (isset($playerColors['battleBuddies'][$gamePlayerIdB])) {
            $gameColors['playerB'] = $playerColors['battleBuddies'][$gamePlayerIdB];
        } else {
            $gameColors['playerB'] = $playerColors['neutralB'];
        }

        return $gameColors;
    }

    // Takes a URL that was entered by a user and returns a version of it that's
    // safe to insert into an anchor tag (or returns NULL if we can't sensibly do
    // that).
    // Based in part on advice from http://stackoverflow.com/questions/205923
    private function validate_url($url) {
        // First, check for and reject anything with inappropriate characters
        // (We can expand this list later if it becomes necessary)
        if (!preg_match('/^[-A-Za-z0-9+&@#\\/%?=~_!:,.\\(\\)]+$/', $url)) {
            return NULL;
        }

        // Then ensure that it begins with http:// or https://
        if (strpos(strtolower($url), 'http://') !== 0 &&
            strpos(strtolower($url), 'https://') !== 0) {
            $url = 'http://' . $url;
        }

        // This should create a relatively safe URL. It does not verify that it's a
        // *valid* URL, but if it is invalid, this should at least render it impotent.
        // This also doesn't verify that the URL points to a safe page, but that is
        // outside of the scope of this function.
        return $url;
    }

    public function __get($property) {
        if (property_exists($this, $property)) {
            switch ($property) {
                default:
                    return $this->$property;
            }
        }
    }

    public function __set($property, $value) {
        switch ($property) {
            case 'message':
                throw new LogicException(
                    'message can only be read, not written.'
                );
            default:
                $this->$property = $value;
        }
    }
}
