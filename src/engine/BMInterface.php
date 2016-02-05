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
    protected $message;            // message intended for GUI
    protected $timestamp;          // timestamp of last game action
    protected static $conn = NULL; // connection to database
    protected $parent = NULL;      // allows navigation back to owning BMInterface

    public $isTest;         // indicates if the interface is for testing

    /**
     * Constructor
     *
     * @param bool $isTest
     */
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

    /**
     * Casts a BMInterface* object to another BMInterface* object
     *
     * @param string $className
     */
    public function cast($className) {
        // only allow cast to another BMInterface class
        if ('BMInterface' != substr($className, 0, 11)) {
            throw new InvalidArgumentException('BMInterface classes can only be cast to another BMInterface class');
        }

        if (!class_exists($className)) {
            throw new InvalidArgumentException('Non-existent class');
        }

        $result = new $className($this->isTest);
        $result->message = $this->message;
        $result->timestamp = $this->timestamp;
        $result::$conn = self::$conn;
        return $result;
    }

    // pseudo-properties, allowing the BMInterface to access methods from
    // daughter classes

    public function player() {
        $interface = $this->cast('BMInterfacePlayer');
        $interface->parent = $this;
        return $interface;
    }

    public function forum() {
        $interface = $this->cast('BMInterfaceForum');
        $interface->parent = $this;
        return $interface;
    }

    public function history() {
        $interface = $this->cast('BMInterfaceHistory');
        $interface->parent = $this;
        return $interface;
    }

    // methods

    protected function validate_and_set_homepage($homepage, array &$infoArray) {
        if ($homepage == NULL || $homepage == "") {
            $infoArray['homepage'] = NULL;
            return TRUE;
        }

        $homepage = $this->validate_url($homepage);
        if ($homepage == NULL) {
            $this->set_message('Homepage is invalid. It may contain some characters that need to be escaped.');
            return FALSE;
        }

        $infoArray['homepage'] = $homepage;
        return TRUE;
    }

    public function create_game(
        array $playerIdArray,
        array $buttonNameArray,
        $maxWins = 3,
        $description = '',
        $previousGameId = NULL,
        $currentPlayerId = NULL,
        $autoAccept = TRUE
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

        $buttonIdArray = $this->retrieve_button_ids($playerIdArray, $buttonNameArray);
        if (is_null($buttonIdArray)) {
            return NULL;
        }

        try {
            $gameId = $this->insert_new_game($playerIdArray, $maxWins, $description, $previousGameId);

            foreach ($playerIdArray as $position => $playerId) {
                $this->add_player_to_new_game(
                    $gameId,
                    $playerId,
                    $buttonIdArray[$position],
                    $position,
                    (0 == $position) || $autoAccept || $this->retrieve_player_autoaccept($playerId)
                );
            }
            $this->set_random_button_flags($gameId, $buttonNameArray);

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

            $this->set_message("Game $gameId created successfully.");
            return array('gameId' => $gameId);
        } catch (Exception $e) {
            $this->set_message('Game create failed: ' . $e->getMessage());
            error_log(
                'Caught exception in BMInterface::create_game: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    protected function insert_new_game(
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
                $this->set_message('Game create failed: ' . $errorData[2]);
            } else {
                $this->set_message('Game create failed: ' . $e->getMessage());
            }
            error_log(
                'Caught exception in BMInterface::insert_new_game: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    protected function add_player_to_new_game($gameId, $playerId, $buttonId, $position, $hasAccepted) {
        // add info to game_player_map
        $query = 'INSERT INTO game_player_map '.
                 '(game_id, player_id, button_id, position, has_player_accepted) '.
                 'VALUES '.
                 '(:game_id, :player_id, :button_id, :position, :has_player_accepted)';
        $statement = self::$conn->prepare($query);

        $statement->execute(array(':game_id'   => $gameId,
                                  ':player_id' => $playerId,
                                  ':button_id' => $buttonId,
                                  ':position'  => $position,
                                  ':has_player_accepted' => $hasAccepted));
    }

    protected function set_random_button_flags($gameId, array $buttonNameArray) {
        foreach ($buttonNameArray as $position => $buttonName) {
            if ('__random' == $buttonName) {
                $query = 'UPDATE game_player_map '.
                         'SET is_button_random = 1 '.
                         'WHERE game_id = :game_id '.
                         'AND position = :position;';
                $statement = self::$conn->prepare($query);

                $statement->execute(array(':game_id'   => $gameId,
                                          ':position'  => $position));
            }
        }
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
            $this->set_message('Game create failed because a player has been selected more than once.');
            return FALSE;
        }

        // validate all inputs
        foreach ($playerIdArray as $playerId) {
            if (!(is_null($playerId) || is_int($playerId))) {
                $this->set_message('Game create failed because player ID is not valid.');
                return FALSE;
            }
        }

        // force first player ID to be the current player ID, if specified
        if (!is_null($currentPlayerId)) {
            if ($currentPlayerId !== $playerIdArray[0]) {
                $this->set_message('Game create failed because you must be the first player.');
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
            $this->set_message('Game create failed because the maximum number of wins was invalid.');
            return FALSE;
        }

        // Check that players match those from previous game, if specified
        $arePreviousPlayersValid =
            $this->validate_previous_game_players($previousGameId, $playerIdArray);
        if (!$arePreviousPlayersValid) {
            return FALSE;
        }

        return TRUE;
    }

    protected function validate_previous_game_players($previousGameId, array $playerIdArray) {
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
                if (($row['status'] != 'COMPLETE') &&
                    ($row['status'] != 'CANCELLED')) {
                    $this->set_message(
                        'Game create failed because the previous game has not been completed yet.'
                    );
                    return FALSE;
                }
                $previousPlayerIds[] = (int)$row['player_id'];
            }

            if (count($previousPlayerIds) == 0) {
                $this->set_message(
                    'Game create failed because the previous game was not found.'
                );
                return FALSE;
            }

            foreach ($playerIdArray as $newPlayerId) {
                if (!in_array($newPlayerId, $previousPlayerIds)) {
                    $this->set_message(
                        'Game create failed because the previous game does not contain the same players.'
                    );
                    return FALSE;
                }
            }
            foreach ($previousPlayerIds as $oldPlayerId) {
                if (!in_array($oldPlayerId, $playerIdArray)) {
                    $this->set_message(
                        'Game create failed because the previous game does not contain the same players.'
                    );
                    return FALSE;
                }
            }

            return TRUE;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::validate_previous_game_players: ' .
                $e->getMessage()
            );
            $this->set_message('Game create failed because of an error.');
            return FALSE;
        }
    }

    protected function retrieve_button_ids($playerIdArray, $buttonNameArray) {
        $buttonIdArray = array();
        foreach (array_keys($playerIdArray) as $position) {
            // get button ID
            $buttonName = $buttonNameArray[$position];

            if ('__random' == $buttonName) {
                $buttonIdArray[] = NULL;
            } elseif (!empty($buttonName)) {
                $query = 'SELECT id FROM button '.
                         'WHERE name = :button_name';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':button_name' => $buttonName));
                $fetchData = $statement->fetch();
                if (FALSE === $fetchData) {
                    $this->set_message('Game create failed because a button name was not valid.');
                    return NULL;
                }
                $buttonIdArray[] = $fetchData[0];
            } else {
                $buttonIdArray[] = NULL;
            }
        }

        return $buttonIdArray;
    }

    protected function retrieve_player_autoaccept($playerId) {
        $query = 'SELECT autoaccept FROM player '.
                 'WHERE id = :player_id';
        $statement = self::$conn->prepare($query);
        $statement->execute(array(':player_id' => $playerId));
        $fetchData = $statement->fetch();
        if (FALSE === $fetchData) {
            $this->set_message('Game create failed because a player id was not valid.');
            return NULL;
        }
        return $fetchData[0];
    }

    public function save_join_game_decision($playerId, $gameId, $decision) {
        if (('accept' != $decision) && ('reject' != $decision)) {
            throw new InvalidArgumentException('decision must be either accept or reject');
        }

        $game = $this->load_game($gameId);

        if (BMGameState::CHOOSE_JOIN_GAME != $game->gameState) {
            if (('reject' == $decision) &&
                ($playerId == $game->playerArray[0]->playerId)) {
                $decision = 'withdraw';
            }
            $this->set_message(
                'Your decision to ' .
                $decision .
                ' the game failed because the game has been updated ' .
                'since you loaded the page'
            );
            return;
        }

        $playerIdx = array_search($playerId, $game->playerIdArray);

        if (FALSE === $playerIdx) {
            return;
        }

        $player = $game->playerArray[$playerIdx];
        $player->waitingOnAction = FALSE;
        $decisionFlag = ('accept' == $decision);
        $player->hasPlayerAcceptedGame = $decisionFlag;

        if (!$decisionFlag) {
            $game->gameState = BMGameState::CANCELLED;
        }

        $this->save_game($game);

        if ($decisionFlag) {
            $this->set_message("Joined game $gameId");
        } else {
            $this->set_message("Rejected game $gameId");
        }

        return TRUE;
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
            foreach ($game->playerArray as $gamePlayerIdx => $gamePlayer) {
                $playerName = $this->get_player_name_from_id($gamePlayer->playerId);
                $playerNameArray[] = $playerName;
                $data['playerDataArray'][$gamePlayerIdx]['playerName'] = $playerName;
            }

            $actionLogArray = $this->load_game_action_log($game, $logEntryLimit);
            if (empty($actionLogArray)) {
                $data['gameActionLog'] = NULL;
                $data['gameActionLogCount'] = 0;
            } else {
                $data['gameActionLog'] = $actionLogArray['logEntries'];
                $data['gameActionLogCount'] = $actionLogArray['nEntries'];
            }

            $chatLogArray = $this->load_game_chat_log($game, $logEntryLimit);
            if (empty($actionLogArray)) {
                $data['gameChatLog'] = NULL;
                $data['gameChatLogCount'] = 0;
            } else {
                $data['gameChatLog'] = $chatLogArray['chatEntries'];
                $data['gameChatLogCount'] = $chatLogArray['nEntries'];
            }

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
                   'AND gpm.is_awaiting_action = 1 '.
                   'AND g.status_id = '.
                       '(SELECT id FROM game_status WHERE name = \'ACTIVE\') ';

            $statement = self::$conn->prepare($query);
            $statement->execute($parameters);
            $result = $statement->fetch();
            if (!$result) {
                $this->set_message('Pending game count failed.');
                error_log('Pending game count failed for player ' . $playerId);
                return NULL;
            } else {
                $data = array();
                $data['count'] = (int)$result[0];
                $this->set_message('Pending game count succeeded.');
                return $data;
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::count_pending_games: ' .
                $e->getMessage()
            );
            $this->set_message('Pending game count failed.');
            return NULL;
        }
    }

    protected function load_game($gameId, $logEntryLimit = NULL) {
        try {
            $game = $this->load_game_parameters($gameId);

            // check whether the game exists
            if (!isset($game)) {
                $this->set_message("Game $gameId does not exist.");
                return FALSE;
            }

            $this->set_logEntryLimit($game, $logEntryLimit);

            $this->load_swing_values_from_last_round($game);
            $this->load_swing_values_from_this_round($game);
            $this->load_option_values_from_last_round($game);
            $this->load_option_values_from_this_round($game);
            $this->load_die_attributes($game);

            $this->recreate_optRequestArrayArray($game);

            $this->set_message($this->message."Loaded data for game $gameId.");

            return $game;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_game: ' .
                $e->getMessage()
            );
            $this->set_message("Internal error while loading game.");
            return NULL;
        }
    }

    protected function load_game_parameters($gameId) {
        // check that the gameId exists
        $query = 'SELECT g.*,'.
                 'UNIX_TIMESTAMP(g.last_action_time) AS last_action_timestamp, '.
                 's.name AS status_name,'.
                 'v.player_id, v.position, v.autopass, v.fire_overshooting,'.
                 'v.button_name, v.alt_recipe,'.
                 'v.n_rounds_won, v.n_rounds_lost, v.n_rounds_drawn,'.
                 'v.did_win_initiative,'.
                 'v.is_awaiting_action, '.
                 'v.is_button_random, '.
                 'UNIX_TIMESTAMP(v.last_action_time) AS player_last_action_timestamp, '.
                 'v.was_game_dismissed, '.
                 'v.has_player_accepted '.
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
                $player = $game->playerArray[$pos];
                if (isset($row['player_id'])) {
                    $player->playerId = (int)$row['player_id'];
                } else {
                    $player->playerId = NULL;
                }
                $player->autopass = (bool)$row['autopass'];
                $player->fireOvershooting = (bool)$row['fire_overshooting'];
                $player->hasPlayerAcceptedGame = (bool)$row['has_player_accepted'];
            }

            if (1 == $row['did_win_initiative']) {
                $game->playerWithInitiativeIdx = $pos;
            }

            $game->playerArray[$pos]->set_gameScoreArray(
                array(
                    'W' => (int)$row['n_rounds_won'],
                    'L' => (int)$row['n_rounds_lost'],
                    'D' => (int)$row['n_rounds_drawn']
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

    protected function load_game_attributes($game, $row) {
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
                $player = $game->playerArray[$pos];
                $player->button = $button;
            } else {
                throw new InvalidArgumentException('Invalid button name.');
            }
        }

        if ($row['is_button_random']) {
            $player = $game->playerArray[$pos];
            $player->isButtonChoiceRandom = TRUE;
        }
    }

    protected function load_player_attributes($game, $pos, $row) {
        $player = $game->playerArray[$pos];
        switch ($row['is_awaiting_action']) {
            case 1:
                $player->waitingOnAction = TRUE;
                break;
            case 0:
                $player->waitingOnAction = FALSE;
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
            $player = $game->playerArray[$pos];
            $player->lastActionTime = (int)$row['player_last_action_timestamp'];
        }
    }

    protected function load_hasPlayerDismissedGame($game, $pos, $row) {
        if (isset($row['was_game_dismissed'])) {
            $player = $game->playerArray[$pos];
            $player->hasPlayerDismissedGame = ((int)$row['was_game_dismissed'] == 1);
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
        $query = 'SELECT * '.
                 'FROM game_swing_map '.
                 'WHERE game_id = :game_id '.
                 'AND is_expired = :is_expired';
        $statement2 = self::$conn->prepare($query);
        $statement2->execute(array(':game_id' => $game->gameId,
                                   ':is_expired' => 1));
        while ($row = $statement2->fetch()) {
            $playerIdx = array_search($row['player_id'], $game->playerIdArray);
            $player = $game->playerArray[$playerIdx];
            $player->prevSwingValueArray[$row['swing_type']] = $row['swing_value'];
        }
    }

    protected function load_swing_values_from_this_round($game) {
        $query = 'SELECT * '.
                 'FROM game_swing_map '.
                 'WHERE game_id = :game_id '.
                 'AND is_expired = :is_expired';
        $statement2 = self::$conn->prepare($query);
        $statement2->execute(array(':game_id' => $game->gameId,
                                   ':is_expired' => 0));
        while ($row = $statement2->fetch()) {
            $playerIdx = array_search($row['player_id'], $game->playerIdArray);
            $player = $game->playerArray[$playerIdx];
            $player->swingValueArray[$row['swing_type']] = $row['swing_value'];
        }
    }

    protected function load_option_values_from_last_round($game) {
        $query = 'SELECT * '.
                 'FROM game_option_map '.
                 'WHERE game_id = :game_id '.
                 'AND is_expired = :is_expired';
        $statement2 = self::$conn->prepare($query);
        $statement2->execute(array(':game_id' => $game->gameId,
                                   ':is_expired' => 1));
        while ($row = $statement2->fetch()) {
            $playerIdx = array_search($row['player_id'], $game->playerIdArray);
            $player = $game->playerArray[$playerIdx];
            $player->prevOptValueArray[$row['die_idx']] = $row['option_value'];
        }
    }

    protected function load_option_values_from_this_round($game) {
        $query = 'SELECT * '.
                 'FROM game_option_map '.
                 'WHERE game_id = :game_id '.
                 'AND is_expired = :is_expired';
        $statement2 = self::$conn->prepare($query);
        $statement2->execute(array(':game_id' => $game->gameId,
                                   ':is_expired' => 0));
        while ($row = $statement2->fetch()) {
            $playerIdx = array_search($row['player_id'], $game->playerIdArray);
            $player = $game->playerArray[$playerIdx];
            $player->optValueArray[$row['die_idx']] = $row['option_value'];
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

        $activeDieArrayArray = array_fill(0, $game->nPlayers, array());
        $captDieArrayArray = array_fill(0, $game->nPlayers, array());
        $outOfPlayDieArrayArray = array_fill(0, $game->nPlayers, array());

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

            if (!is_null($row['flags'])) {
                $die->load_flags_from_string($row['flags']);
            }

            $this->set_swing_max($die, $originalPlayerIdx, $game, $row);
            $this->set_twin_max($die, $originalPlayerIdx, $game, $row);
            $this->set_option_max($die, $row);

            if (isset($row['value'])) {
                $die->value = (int)$row['value'];
            }

            switch ($row['status']) {
                case 'NORMAL':
                    $activeDieArrayArray[$playerIdx][$row['position']] = $die;
                    break;
                case 'SELECTED':
                    // james: maintain backward compatibility
                    if (BMGameState::CHOOSE_AUXILIARY_DICE == $game->gameState) {
                        $die->add_flag('AddAuxiliary');
                    } elseif (BMGameState::CHOOSE_AUXILIARY_DICE == $game->gameState) {
                        $die->add_flag('AddReserve');
                    }
                    $activeDieArrayArray[$playerIdx][$row['position']] = $die;
                    break;
                case 'DISABLED':
                    $die->add_flag('Disabled');
                    $activeDieArrayArray[$playerIdx][$row['position']] = $die;
                    break;
                case 'DIZZY':
                    // james: maintain backward compatibility
                    $die->add_flag('Dizzy');
                    $activeDieArrayArray[$playerIdx][$row['position']] = $die;
                    break;
                case 'CAPTURED':
                    $die->captured = TRUE;
                    $captDieArrayArray[$playerIdx][$row['position']] = $die;
                    break;
                case 'OUT_OF_PLAY':
                    $outOfPlayDieArrayArray[$playerIdx][$row['position']] = $die;
                    break;
            }
        }

        $game->activeDieArrayArray = $activeDieArrayArray;
        $game->capturedDieArrayArray = $captDieArrayArray;
        $game->outOfPlayDieArrayArray = $outOfPlayDieArrayArray;
    }

    protected function set_swing_max($die, $originalPlayerIdx, $game, $row) {
        if (isset($die->swingType) && !$die instanceof BMDieTwin) {
            $game->request_swing_values($die, $die->swingType, $originalPlayerIdx);
            $die->set_swingValue($game->playerArray[$originalPlayerIdx]->swingValueArray);

            if (isset($row['actual_max'])) {
                $die->max = (int)$row['actual_max'];
            }
        }
    }

    protected function set_twin_max($die, $originalPlayerIdx, $game, $row) {
        if (!($die instanceof BMDieTwin)) {
            return;
        }

        if (($die->dice[0] instanceof BMDieSwing) ||
            ($die->dice[1] instanceof BMDieSwing)) {

            foreach ($die->dice as $subdie) {
                if ($subdie instanceof BMDieSwing) {
                    $swingType = $subdie->swingType;
                    $subdie->set_swingValue($game->playerArray[$originalPlayerIdx]->swingValueArray);
                }
            }

            $game->request_swing_values($die, $swingType, $originalPlayerIdx);
        }

        foreach ($die->dice as $subdieIdx => $subdie) {
            if ($die->has_flag('Twin')) {
                $subdiePropertyArray = $die->flagList['Twin']->value();
                $max = $subdiePropertyArray['sides'][$subdieIdx];
                if (isset($max)) {
                    $subdie->max = (int)$max;
                }
                $value = $subdiePropertyArray['values'][$subdieIdx];
                if (isset($value)) {
                    $subdie->value = (int)$value;
                }
            } else {
                // continue to handle the old case where there was no BMFlagTwin information
                if (isset($row['actual_max'])) {
                    $subdie->max = (int)($row['actual_max']/2);
                }
            }
        }

        $die->recalc_max_min();
    }

    protected function set_option_max($die, $row) {
        if ($die instanceof BMDieOption) {
            if (isset($row['actual_max'])) {
                $die->max = (int)$row['actual_max'];
                $die->needsOptionValue = FALSE;
            } else {
                $die->needsOptionValue = TRUE;
            }
        }
    }

    protected function recreate_optRequestArrayArray($game) {
        foreach ($game->playerArray as $player) {
            foreach ($player->activeDieArray as $activeDie) {
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
            $this->resolve_random_button_selection($game);
            $this->save_basic_game_parameters($game);
            $this->save_button_recipes($game);
            $this->save_random_button_choice($game);
            $this->save_round_scores($game);
            $this->clear_swing_values_from_database($game);
            $this->clear_option_values_from_database($game);
            $this->save_swing_values_from_last_round($game);
            $this->save_swing_values_from_this_round($game);
            $this->save_option_values_from_last_round($game);
            $this->save_option_values_from_this_round($game);
            $this->save_player_game_decisions($game);
            $this->save_player_with_initiative($game);
            $this->save_players_awaiting_action($game);
            $this->regenerate_essential_die_flags($game);
            $this->mark_existing_dice_as_deleted($game);
            $this->save_active_dice($game);
            $this->save_captured_dice($game);
            $this->save_out_of_play_dice($game);
            $this->delete_dice_marked_as_deleted($game);
            $this->save_action_log($game);
            $this->save_chat_log($game);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_game: ' .
                $e->getMessage()
            );
            $this->set_message("Game save failed: $e");
        }
    }

    protected function resolve_random_button_selection(BMGame &$game) {
        if (!$this->does_need_random_button_selection($game)) {
            return;
        }

        $allButtonData = array();
        $nButtons = 0;

        foreach ($game->playerArray as $player) {
            if (!empty($player->button)) {
                continue;
            }

            if (!$player->isButtonChoiceRandom) {
                continue;
            }

            if (empty($allButtonData)) {
                $allButtonData = $this->get_button_data(NULL, NULL, TRUE);
                $nButtons = count($allButtonData);
            }

            $randIdx = bm_rand(0, $nButtons - 1);
            $buttonId = $allButtonData[$randIdx]['buttonId'];

            $this->choose_button($game, $buttonId, $player);
        }

        // ensure that the chat and game acceptance have also been cached
        $this->save_chat_log($game);
        $this->save_player_game_decisions($game);

        $game = $this->load_game($game->gameId);
        $game->proceed_to_next_user_action();
    }

    protected function does_need_random_button_selection(BMGame $game) {
        $hasRandomlyChosenButtons = FALSE;
        $hasUnresolvedNames = FALSE;
        foreach ($game->playerArray as $player) {
            // do not resolve random button names unless all players have joined the game
            if (empty($player->playerId)) {
                return FALSE;
            }

            $hasRandomlyChosenButtons |= $player->isButtonChoiceRandom;
            $hasUnresolvedNames |= is_null($player->button);
        }

        // only resolve random names if there are some randomly chosen buttons
        if (!$hasRandomlyChosenButtons) {
            return FALSE;
        }

        // only resolve random names if there are some left to resolve
        if (!$hasUnresolvedNames) {
            return FALSE;
        }

        // do not resolve random names unless all buttons have been chosen
        foreach ($game->playerArray as $player) {
            if (empty($player->button) && !$player->isButtonChoiceRandom) {
                return FALSE;
            }
        }

        return TRUE;
    }

    protected function choose_button(BMGame $game, $buttonId, $player) {
        // add info to game_player_map
        $query = 'UPDATE game_player_map '.
                 'SET button_id = :button_id, '.
                 '    is_awaiting_action = 0 '.
                 'WHERE '.
                 'game_id = :game_id AND '.
                 'position = :position';
        $statement = self::$conn->prepare($query);

        $statement->execute(array(':game_id'   => $game->gameId,
                                  ':button_id' => $buttonId,
                                  ':position'  => $player->position));
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
            $currentPlayerId = $game->playerArray[$game->activePlayerIdx]->playerId;
        }

        return $currentPlayerId;
    }

    protected function get_game_status($game) {
        if (BMGameState::END_GAME == $game->gameState) {
            $status = 'COMPLETE';
        } elseif (BMGameState::CANCELLED == $game->gameState) {
            $status = 'CANCELLED';
        } elseif (in_array(NULL, $game->playerIdArray) ||
                  in_array(NULL, $game->buttonArray)) {
            $status = 'OPEN';
        } else {
            $status = 'ACTIVE';
        }

        return $status;
    }

    protected function save_button_recipes($game) {
        foreach ($game->playerArray as $player) {
            if (($player->button instanceof BMButton) &&
                ($player->button->hasAlteredRecipe)) {
                $query = 'UPDATE game_player_map '.
                         'SET alt_recipe = :alt_recipe '.
                         'WHERE game_id = :game_id '.
                         'AND player_id = :player_id;';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':alt_recipe' => $player->button->recipe,
                                          ':game_id' => $game->gameId,
                                          ':player_id' => $player->playerId));
            }
        }
    }

    protected function save_random_button_choice(BMGame $game) {
        if ($game->gameState > BMGameState::START_GAME) {
            return;
        }

        foreach ($game->playerArray as $position => $player) {
            if ($player->isButtonChoiceRandom) {
                $query = 'UPDATE game_player_map '.
                         'SET is_button_random = 1 '.
                         'WHERE game_id = :game_id '.
                         'AND position = :position;';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':game_id' => $game->gameId,
                                          ':position' => $position));
            }
        }
    }

    protected function save_round_scores($game) {
        foreach ($game->playerArray as $player) {
            $query = 'UPDATE game_player_map '.
                     'SET n_rounds_won = :n_rounds_won,'.
                     '    n_rounds_lost = :n_rounds_lost,'.
                     '    n_rounds_drawn = :n_rounds_drawn '.
                     'WHERE game_id = :game_id '.
                     'AND player_id = :player_id;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':n_rounds_won' => $player->gameScoreArray['W'],
                                      ':n_rounds_lost' => $player->gameScoreArray['L'],
                                      ':n_rounds_drawn' => $player->gameScoreArray['D'],
                                      ':game_id' => $game->gameId,
                                      ':player_id' => $player->playerId));
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
        foreach ($game->playerArray as $player) {
            if (empty($player->prevSwingValueArray)) {
                continue;
            }

            foreach ($player->prevSwingValueArray as $swingType => $swingValue) {
                $query = 'INSERT INTO game_swing_map '.
                         '(game_id, player_id, swing_type, swing_value, is_expired) '.
                         'VALUES '.
                         '(:game_id, :player_id, :swing_type, :swing_value, :is_expired)';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':game_id'     => $game->gameId,
                                          ':player_id'   => $player->playerId,
                                          ':swing_type'  => $swingType,
                                          ':swing_value' => $swingValue,
                                          ':is_expired'  => TRUE));
            }
        }
    }

    protected function save_swing_values_from_this_round($game) {
        foreach ($game->playerArray as $player) {
            if (empty($player->swingValueArray)) {
                continue;
            }

            foreach ($player->swingValueArray as $swingType => $swingValue) {
                $query = 'INSERT INTO game_swing_map '.
                         '(game_id, player_id, swing_type, swing_value, is_expired) '.
                         'VALUES '.
                         '(:game_id, :player_id, :swing_type, :swing_value, :is_expired)';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':game_id'     => $game->gameId,
                                          ':player_id'   => $player->playerId,
                                          ':swing_type'  => $swingType,
                                          ':swing_value' => $swingValue,
                                          ':is_expired'  => FALSE));
            }
        }
    }

    protected function save_option_values_from_last_round($game) {
        foreach ($game->playerArray as $player) {
            if (empty($player->prevOptValueArray)) {
                continue;
            }

            foreach ($player->prevOptValueArray as $dieIdx => $optionValue) {
                $query = 'INSERT INTO game_option_map '.
                         '(game_id, player_id, die_idx, option_value, is_expired) '.
                         'VALUES '.
                         '(:game_id, :player_id, :die_idx, :option_value, :is_expired)';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':game_id'   => $game->gameId,
                                          ':player_id' => $player->playerId,
                                          ':die_idx'   => $dieIdx,
                                          ':option_value' => $optionValue,
                                          ':is_expired' => TRUE));
            }
        }
    }

    protected function save_option_values_from_this_round($game) {
        foreach ($game->playerArray as $player) {
            if (empty($player->optValueArray)) {
                continue;
            }

            foreach ($player->optValueArray as $dieIdx => $optionValue) {
                $query = 'INSERT INTO game_option_map '.
                         '(game_id, player_id, die_idx, option_value, is_expired) '.
                         'VALUES '.
                         '(:game_id, :player_id, :die_idx, :option_value, :is_expired)';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':game_id'   => $game->gameId,
                                          ':player_id' => $player->playerId,
                                          ':die_idx'   => $dieIdx,
                                          ':option_value' => $optionValue,
                                          ':is_expired' => FALSE));
            }
        }
    }

    protected function save_player_game_decisions($game) {
        foreach ($game->playerArray as $player) {
            $query = 'UPDATE game_player_map '.
                     'SET has_player_accepted = :has_player_accepted '.
                     'WHERE game_id = :game_id '.
                     'AND position = :position;';
            $statement = self::$conn->prepare($query);
            if ($player->hasPlayerAcceptedGame) {
                $hasPlayerAccepted = 1;
            } else {
                $hasPlayerAccepted = 0;
            }
            $statement->execute(array(':has_player_accepted' => $hasPlayerAccepted,
                                      ':game_id' => $game->gameId,
                                      ':position' => $player->position));
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
            $statement->execute(
                array(':game_id' => $game->gameId,
                      ':player_id' => $game->playerArray[$game->playerWithInitiativeIdx]->playerId)
            );
        }
    }

    protected function save_players_awaiting_action($game) {
        foreach ($game->playerArray as $player) {
            $query = 'UPDATE game_player_map '.
                     'SET is_awaiting_action = :is_awaiting_action '.
                     'WHERE game_id = :game_id '.
                     'AND position = :position;';
            $statement = self::$conn->prepare($query);
            if ($player->waitingOnAction) {
                $is_awaiting_action = 1;
            } else {
                $is_awaiting_action = 0;
            }
            $statement->execute(array(':is_awaiting_action' => $is_awaiting_action,
                                      ':game_id' => $game->gameId,
                                      ':position' => $player->position));
        }
    }

    protected function regenerate_essential_die_flags($game) {
        foreach ($game->playerArray as $player) {
            if (!empty($player->activeDieArray)) {
                foreach ($player->activeDieArray as $activeDie) {
                    if ($activeDie instanceof BMDieTwin) {
                        // force regeneration of max, min, and BMFlagTwin
                        $activeDie->recalc_max_min();
                    }
                }
            }

            if (!empty($player->capturedDieArray)) {
                foreach ($player->capturedDieArray as $capturedDie) {
                    if ($capturedDie instanceof BMDieTwin) {
                        // force regeneration of max, min, and BMFlagTwin
                        $capturedDie->recalc_max_min();
                    }
                }
            }
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
    }

    protected function save_dice($game, $dieArrayArray, $status) {
        if (isset($dieArrayArray)) {
            foreach ($dieArrayArray as $playerIdx => $dieArray) {
                foreach ($dieArray as $dieIdx => $die) {
                    $this->db_insert_die($game, $playerIdx, $die, $status, $dieIdx);
                }
            }
        }
    }

    protected function save_active_dice($game) {
        $this->save_dice($game, $game->activeDieArrayArray, 'NORMAL');
    }

    protected function save_captured_dice($game) {
        $this->save_dice($game, $game->capturedDieArrayArray, 'CAPTURED');
    }

    protected function save_out_of_play_dice($game) {
        $this->save_dice($game, $game->outOfPlayDieArrayArray, 'OUT_OF_PLAY');
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
            ($activeDie instanceof BMDieOption) ||
            ($activeDie instanceof BMDieSwing) ||
            ($activeDie instanceof BMDieTwin)) {
            $actualMax = $activeDie->max;
        }

        $statement->execute(array(':owner_id' => $game->playerArray[$playerIdx]->playerId,
                                  ':original_owner_id' => $game->playerArray[$activeDie->originalPlayerIdx]
                                                               ->playerId,
                                  ':game_id' => $game->gameId,
                                  ':status' => $status,
                                  ':recipe' => $activeDie->recipe,
                                  ':actual_max' => $actualMax,
                                  ':position' => $dieIdx,
                                  ':value' => $activeDie->value,
                                  ':flags' => $flags));
    }

    // Get all player games of a certain type (new, active, or inactive) from
    // the database.
    protected function get_all_games($playerId, $type) {
        try {
            $this->set_message('All game details retrieved successfully.');

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
                     'g.description,'.
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
            if ('ACTIVE' == $type) {
                $query .= 'AND s.name = "ACTIVE" AND g.game_state > 13 ';
            } elseif ('NEW' == $type) {
                $query .= 'AND s.name = "ACTIVE" AND g.game_state <= 13 ';
            } elseif ('COMPLETE' == $type) {
                $query .= 'AND s.name = "COMPLETE" AND v2.was_game_dismissed = 0 ';
            } elseif ('CANCELLED' == $type) {
                $query .= 'AND s.name = "CANCELLED" AND v2.was_game_dismissed = 0 ';
            }
            $query .= 'ORDER BY g.last_action_time ASC;';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':player_id' => $playerId));

            return self::read_game_list_from_db_results($playerId, $statement);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_all_games: ' .
                $e->getMessage()
            );
            $this->set_message('Game detail get failed.');
            return NULL;
        }
    }

    protected function read_game_list_from_db_results($playerId, $results) {
        // Initialize the arrays
        $gameIdArray = array();
        $gameDescriptionArray = array();
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
        $inactivityRawArray = array();
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
            $gameDescriptionArray[] = $row['description'];
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
            $inactivityRawArray[] = (int)$now - $row['last_action_timestamp'];
            $playerColorArray[]   = $gameColors['playerA'];
            $opponentColorArray[] = $gameColors['playerB'];
        }

        return array('gameIdArray'             => $gameIdArray,
                     'gameDescriptionArray'    => $gameDescriptionArray,
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
                     'inactivityRawArray'      => $inactivityRawArray,
                     'playerColorArray'        => $playerColorArray,
                     'opponentColorArray'      => $opponentColorArray);
    }

    public function get_all_new_games($playerId) {
        return $this->get_all_games($playerId, 'NEW');
    }

    public function get_all_active_games($playerId) {
        return $this->get_all_games($playerId, 'ACTIVE');
    }

    public function get_all_completed_games($playerId) {
        return $this->get_all_games($playerId, 'COMPLETE');
    }

    public function get_all_cancelled_games($playerId) {
        return $this->get_all_games($playerId, 'CANCELLED');
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
                    'v_challenger.is_button_random AS challenger_random, ' .
                    'v_victim.button_name AS victim_button, ' .
                    'v_victim.is_button_random AS victim_random, ' .
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

                if ((int)$row['challenger_random'] == 1) {
                    $challengerButton = '__random';
                } else {
                    $challengerButton = $row['challenger_button'];
                }
                if ((int)$row['victim_random'] == 1) {
                    $victimButton = '__random';
                } else {
                    $victimButton = $row['victim_button'];
                }

                $games[] = array(
                    'gameId' => (int)$row['game_id'],
                    'challengerId' => (int)$row['challenger_id'],
                    'challengerName' => $row['challenger_name'],
                    'challengerButton' => $challengerButton,
                    'challengerColor' => $gameColors['playerB'],
                    'victimButton' => $victimButton,
                    'targetWins' => (int)$row['target_wins'],
                    'description' => $row['description'],
                );
            }

            $this->set_message('Open games retrieved successfully.');
            return array('games' => $games);
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_all_open_games: " .
                $e->getMessage()
            );
            $this->set_message('Game detail get failed.');
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
                        'AND gpm.is_awaiting_action = 1 '.
                        'AND g.status_id = '.
                           '(SELECT id FROM game_status WHERE name = \'ACTIVE\') ';
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
                $this->set_message('Player has no pending games.');
                return array('gameId' => NULL);
            } else {
                $gameId = ((int)$result[0]);
                $this->set_message('Next game ID retrieved successfully.');
                return array('gameId' => $gameId);
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_next_pending_game: ' .
                $e->getMessage()
            );
            $this->set_message('Game ID get failed.');
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
            $this->set_message('Active players retrieved successfully.');
            return array('players' => $players);
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_active_players: " .
                $e->getMessage()
            );
            $this->set_message('Getting active players failed.');
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
    // Set $forceImplemented to TRUE to only retrieve buttons with fully implemented skills.
    public function get_button_data($buttonName = NULL, $setName = NULL, $forceImplemented = FALSE) {
        try {
            // if the site is production, don't report unimplemented buttons at all
            $site_type = $this->get_config('site_type');
            $single_button = ($buttonName !== NULL);
            $statement = $this->execute_button_data_query($buttonName, $setName);

            $buttons = array();
            while ($row = $statement->fetch()) {
                $currentButton = $this->assemble_button_data($row, $site_type, $single_button, $forceImplemented);
                if ($currentButton) {
                    $buttons[] = $currentButton;
                }
            }

            if (count($buttons) == 0) {
                $this->set_message('Button not found.');
                return NULL;
            }

            $this->set_message('Button data retrieved successfully.');
            return $buttons;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_button_data: ' .
                $e->getMessage()
            );
            $this->set_message('Button info get failed.');
            return NULL;
        }
    }

    protected function execute_button_data_query($buttonName, $setName) {
        $parameters = array();
        $query =
            'SELECT b.id, b.name, b.recipe, b.btn_special, b.tourn_legal, b.flavor_text, ' .
            '       s.name AS set_name ' .
            'FROM button AS b ' .
            'LEFT JOIN buttonset AS s ON b.set_id = s.id ';
        if ($buttonName !== NULL) {
            $query .= 'WHERE b.name = :button_name ';
            $parameters[':button_name'] = $buttonName;
        } elseif ($setName !== NULL) {
            $query .= 'WHERE s.name = :set_name ';
            $parameters[':set_name'] = $setName;
        }
        $query .=
            'ORDER BY s.sort_order ASC, b.name ASC;';

        $statement = self::$conn->prepare($query);
        $statement->execute($parameters);
        return $statement;
    }

    protected function assemble_button_data($row, $site_type, $single_button, $forceImplemented = FALSE) {
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

        if ('production' == $site_type) {
            $forceImplemented = TRUE;
        }

        if ($hasUnimplementedSkill && $forceImplemented) {
            return NULL;
        }

        $currentButton = array(
            'buttonId' => (int)$row['id'],
            'buttonName' => $row['name'],
            'recipe' => $row['recipe'],
            'hasUnimplementedSkill' => $hasUnimplementedSkill,
            'buttonSet' => $row['set_name'],
            'dieTypes' => $dieTypes,
            'dieSkills' => $dieSkills,
            'isTournamentLegal' => ((int)$row['tourn_legal'] == 1),
            'artFilename' => $button->artFilename,
            'tags' => $this->get_button_tags($row['name']),
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
    }

    protected function get_button_tags($buttonName) {
        $tags = array();

        try {
            $query =
                'SELECT t.name ' .
                'FROM button_tag_map btm ' .
                    'INNER JOIN button b ON b.id = btm.button_id ' .
                    'INNER JOIN tag t ON t.id = btm.tag_id ' .
                'WHERE b.name = :button_name ' .
                'ORDER BY t.name ASC;';
            $statement = self::$conn->prepare($query);
            $parameters = array(':button_name' => $buttonName);
            $statement->execute($parameters);

            while ($row = $statement->fetch()) {
                $tags[] = $row['name'];
            }
        } catch (Exception $e) {
            // If this fails, we should log the error, but we don't need to
            // fail the whole request just on account of tags
            error_log(
                'Caught exception in BMInterface::get_button_tags for ' .
                $buttonName . ': ' . $e->getMessage()
            );
        }

        return $tags;
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
                $this->set_message('Button set not found.');
                return NULL;
            }

            $this->set_message('Button set data retrieved successfully.');
            return $sets;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_button_set_data: ' .
                $e->getMessage()
            );
            $this->set_message('Button set info get failed.');
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
            $this->set_message('Button recipe get failed.');
        }
    }

    public function get_player_names_like($input = '') {
        try {
            $query = 'SELECT name_ingame, status FROM player_view '.
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
            $this->set_message('Names retrieved successfully.');
            return array('nameArray' => $nameArray, 'statusArray' => $statusArray);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_player_names_like: ' .
                $e->getMessage()
            );
            $this->set_message('Player name get failed.');
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
                $this->set_message('Player name does not exist.');
                return('');
            } else {
                $this->set_message('Player ID retrieved successfully.');
                return((int)$result[0]);
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_player_id_from_name: ' .
                $e->getMessage()
            );
            $this->set_message('Player ID get failed.');
        }
    }

    protected function get_player_name_from_id($playerId) {
        try {
            if (empty($playerId)) {
                return('');
            }

            $query = 'SELECT name_ingame FROM player '.
                     'WHERE id = :id';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':id' => $playerId));
            $result = $statement->fetch();
            if (!$result) {
                return('');
            } else {
                return($result[0]);
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_player_name_from_id: ' .
                $e->getMessage()
            );
            $this->set_message('Player name get failed.');
        }
    }

    protected function get_player_name_mapping($game) {
        $idNameMapping = array();
        foreach ($game->playerArray as $player) {
            $idNameMapping[$player->playerId] = $this->get_player_name_from_id($player->playerId);
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
                $this->set_message('Button name does not exist.');
                return('');
            } else {
                $this->set_message('Button ID retrieved successfully.');
                return((int)$result[0]);
            }
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_button_id_from_name: " .
                $e->getMessage()
            );
            $this->set_message('Button ID get failed.');
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
                $this->set_message('Buttonset name does not exist.');
                return('');
            } else {
                return((int)$result[0]);
            }
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_buttonset_id_from_name: " .
                $e->getMessage()
            );
            $this->set_message('Buttonset ID get failed.');
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
            $this->set_message('You are not a participant in this game');
            return FALSE;
        }

        if (FALSE === $game->playerArray[$currentPlayerIdx]->waitingOnAction) {
            $this->set_message('You are not the active player');
            return FALSE;
        };

        $doesTimeStampAgree =
            ('ignore' === $postedTimestamp) ||
            ($postedTimestamp == $this->timestamp);
        $doesRoundNumberAgree =
            ('ignore' === $roundNumber) ||
            ($roundNumber == $game->roundNumber);
        $doesGameStateAgree = $expectedGameState == $game->gameState;

        $isGameStateCurrent =
            $doesTimeStampAgree &&
            $doesRoundNumberAgree &&
            $doesGameStateAgree;

        if (!$isGameStateCurrent) {
            $this->set_message('Game state is not current');
        }

        return $isGameStateCurrent;
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
            $sqlParameters = array(':game_id' => $game->gameId);
            $query = 'SELECT UNIX_TIMESTAMP(action_time) AS action_timestamp, ' .
                     'game_state,action_type,acting_player,message ' .
                     'FROM game_action_log ';
            $query .= $this->build_game_log_query_restrictions($game, FALSE, FALSE, $sqlParameters);

            $statement = self::$conn->prepare($query);
            $statement->execute($sqlParameters);
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
                        'player' => $this->get_player_name_from_id($gameAction->actingPlayerId),
                        'message' => $message,
                    );
                }
            }

            $nEntries = count($logEntries);

            if (!is_null($logEntryLimit) &&
                ($nEntries > $logEntryLimit)) {
                $logEntries = array_slice($logEntries, 0, $logEntryLimit);
            }

            return array('logEntries' => $logEntries, 'nEntries' => $nEntries);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_game_action_log: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading log entries');
            return NULL;
        }
    }

    protected function load_game_chat_log(BMGame $game, $logEntryLimit) {
        try {
            $sqlParameters = array(':game_id' => $game->gameId);
            $query =
                'SELECT ' .
                    'UNIX_TIMESTAMP(chat_time) AS chat_timestamp, ' .
                    'chatting_player, ' .
                    'message ' .
                'FROM game_chat_log ';
            $query .= $this->build_game_log_query_restrictions($game, TRUE, FALSE, $sqlParameters);

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

            $nEntries = count($chatEntries);

            if (!is_null($logEntryLimit) &&
                ($nEntries > $logEntryLimit)) {
                $chatEntries = array_slice($chatEntries, 0, $logEntryLimit);
            }

            return array('chatEntries' => $chatEntries, 'nEntries' => $nEntries);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::load_game_chat_log: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reading chat entries');
            return NULL;
        }
    }

    // Build the various different WHERE, ORDER BY and LIMIT clauses for the
    // different action and chat log SELECT queries
    protected function build_game_log_query_restrictions(
        BMGame $game,
        $isChat,
        $isCount,
        array &$sqlParameters
    ) {
        $restrictions = 'WHERE game_id = :game_id ';
        if ($isChat && $game->gameState < BMGameState::END_GAME && !is_null($game->previousGameId)) {
            $restrictions .= 'OR game_id = :previous_game_id ';
            $sqlParameters[':previous_game_id'] = $game->previousGameId;
        }
        if (!$isCount) {
            $restrictions .= 'ORDER BY id DESC ' ;
        }

        return $restrictions;
    }

    // Create a status message based on recent game actions
    protected function load_message_from_game_actions(BMGame $game) {
        $message = '';
        $playerIdNames = $this->get_player_name_mapping($game);
        foreach ($game->actionLog as $gameAction) {
            $messagePart = $gameAction->friendly_message(
                $playerIdNames,
                $game->roundNumber,
                $game->gameState
            );

            if (!empty($messagePart)) {
                if ('.' == substr($messagePart, -1)) {
                    $message .= $messagePart . ' ';
                } else {
                    $message .= $messagePart . '. ';
                }
            }
        }

        if (!empty($message)) {
            $this->set_message($message);
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

        // only a player in this game can modify the last chat message
        if (FALSE === $currentPlayerIdx) {
            return FALSE;
        }

        // only the player who chatted last can modify the last chat message
        if ($playerNameArray[$currentPlayerIdx] != $chatLogEntries[0]['player']) {
            return FALSE;
        }

        // only the player who was last active can modify the last chat message,
        // unless the game is in a state where the last activity in the game was
        // an automatic action
        if (('' != $actionLogEntries[0]['player']) &&
            ($playerNameArray[$currentPlayerIdx] != $actionLogEntries[0]['player'])) {
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
        if (TRUE === $game->playerArray[$currentPlayerIdx]->waitingOnAction) {
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

            foreach ($game->playerArray as $gamePlayer) {
                $playerNameArray[] = $this->get_player_name_from_id($gamePlayer->playerId);
            }
            $chatArray = $this->load_game_chat_log($game, 1);
            $lastChatEntryList = $chatArray['chatEntries'];
            $logArray = $this->load_game_action_log($game, 1);
            $lastActionEntryList = $logArray['logEntries'];

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
                        $this->set_message('Updated previous game message');
                        return TRUE;
                    } else {
                        $this->db_delete_chat($playerId, $gameId, $editTimestamp);
                        $this->set_message('Deleted previous game message');
                        return TRUE;
                    }
                } else {
                    $this->set_message('You can\'t edit the requested chat message now');
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
                        $this->set_message('Added game message');
                        return TRUE;
                    } else {
                        $this->set_message('No game message specified');
                        return FALSE;
                    }
                } else {
                    $this->set_message('You can\'t add a new chat message now');
                    return FALSE;
                }
            }

        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::submit_chat: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while updating game chat');
        }
    }

    public function join_open_game($currentPlayerId, $gameId) {
        try {
            $game = $this->load_game($gameId);

            // check that there are still unspecified players and
            // that the player is not already part of the game
            $emptyPlayerIdx = NULL;
            $isPlayerPartOfGame = FALSE;

            foreach ($game->playerArray as $playerIdx => $player) {
                if (is_null($player->playerId) && is_null($emptyPlayerIdx)) {
                    $emptyPlayerIdx = $playerIdx;
                } elseif ($currentPlayerId == $player->playerId) {
                    $isPlayerPartOfGame = TRUE;
                    break;
                }
            }

            if ($isPlayerPartOfGame) {
                $this->set_message('You are already playing in this game.');
                return FALSE;
            }

            if (is_null($emptyPlayerIdx)) {
                $this->set_message('No empty player slots in game '.$gameId.'.');
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
            $player = $game->playerArray[$emptyPlayerIdx];
            $player->hasPlayerAcceptedGame = TRUE;
            $this->save_game($game);
            $this->set_message('Successfully joined game ' . $gameId);

            return TRUE;
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::join_open_game: ".
                $e->getMessage()
            );
            $this->set_message('Internal error while joining open game');
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
                $this->set_message('Player is not a participant in game.');
                return FALSE;
            }

            if (!is_null($game->playerArray[$playerIdx]->button)) {
                $this->set_message('Button has already been selected.');
                return FALSE;
            }

            if ('__random' == $buttonName) {
                $query = 'UPDATE game_player_map SET is_button_random = 1 '.
                         'WHERE game_id = :game_id '.
                         'AND player_id = :player_id';

                $statement = self::$conn->prepare($query);

                $statement->execute(array(':game_id'   => $gameId,
                                          ':player_id' => $playerId));
            } else {
                $query = 'SELECT id FROM button '.
                         'WHERE name = :button_name';
                $statement = self::$conn->prepare($query);
                $statement->execute(array(':button_name' => $buttonName));
                $fetchData = $statement->fetch();
                if (FALSE === $fetchData) {
                    $this->set_message('Button select failed because button name was not valid.');
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
            }

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
            $this->set_message('Internal error while selecting button');
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
                $this->set_message('Dice sizes no longer need to be set');
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
                $dieRecipe = $game->playerArray[$currentPlayerIdx]->activeDieArray[$dieIdx]->recipe;
                $optionLogArray[$dieRecipe] = $optionValue;
            }
            $game->log_action(
                'choose_die_values',
                $game->playerArray[$currentPlayerIdx]->playerId,
                array(
                    'roundNumber' => $game->roundNumber,
                    'swingValues' => $swingValueArray,
                    'optionValues' => $optionLogArray,
                )
            );

            $game->proceed_to_next_user_action();
            // check for successful swing value set
            if ((FALSE == $game->playerArray[$currentPlayerIdx]->waitingOnAction) ||
                ($game->gameState > BMGameState::SPECIFY_DICE) ||
                ($game->roundNumber > $roundNumber)) {
                $this->save_game($game);
                $this->set_message('Successfully set die sizes');
                return TRUE;
            } else {
                if ($game->message) {
                    $this->set_message($game->message);
                } else {
                    $this->set_message('Failed to set die sizes');
                }
                return NULL;
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::submit_die_values: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while setting die sizes');
        }
    }

    protected function set_swing_values($swingValueArray, $currentPlayerIdx, $game) {
        $player = $game->playerArray[$currentPlayerIdx];
        $player->swingValueArray = $swingValueArray;
        $swingRequestArray = $player->swingRequestArray;
        if (is_array($swingRequestArray)) {
            $swingRequested = array_keys($player->swingRequestArray);
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
            $this->set_message('Wrong swing values submitted: expected ' . implode(',', $swingRequested));
        }

        return $isSwingSetSuccessful;
    }

    protected function set_option_values($optionValueArray, $currentPlayerIdx, $game) {
        if (is_array($optionValueArray)) {
            $player = $game->playerArray[$currentPlayerIdx];
            foreach ($optionValueArray as $dieIdx => $optionValue) {
                $player->optValueArray[$dieIdx] = $optionValue;
            }
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
                $this->set_message('It is not your turn to attack right now');
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
            $nAttackerDice = count($game->playerArray[$attackerIdx]->activeDieArray);
            $nDefenderDice = count($game->playerArray[$defenderIdx]->activeDieArray);

            for ($dieIdx = 0; $dieIdx < $nAttackerDice; $dieIdx++) {
                if (filter_var(
                    $dieSelectStatus["playerIdx_{$attackerIdx}_dieIdx_{$dieIdx}"],
                    FILTER_VALIDATE_BOOLEAN
                )) {
                    $attackers[] = $game->playerArray[$attackerIdx]->activeDieArray[$dieIdx];
                    $attackerDieIdx[] = $dieIdx;
                }
            }

            for ($dieIdx = 0; $dieIdx < $nDefenderDice; $dieIdx++) {
                if (filter_var(
                    $dieSelectStatus["playerIdx_{$defenderIdx}_dieIdx_{$dieIdx}"],
                    FILTER_VALIDATE_BOOLEAN
                )) {
                    $defenders[] = $game->playerArray[$defenderIdx]->activeDieArray[$dieIdx];
                    $defenderDieIdx[] = $dieIdx;
                }
            }

            // populate BMAttack object for the specified attack
            $game->attack = array($attackerIdx, $defenderIdx,
                                  $attackerDieIdx, $defenderDieIdx,
                                  $attackType);
            $attack = BMAttack::create($attackType);

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
                    $this->set_message('Requested attack is not valid');
                } else {
                    $this->set_message($attack->validationMessage);
                }
                return NULL;
            }
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::submit_turn: ' .
                $e->getMessage()
            );
            var_dump($e->getMessage());
            $this->set_message('Internal error while submitting turn');
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
            $player = $game->playerArray[$playerIdx];

            switch ($action) {
                case 'add':
                    if (!array_key_exists($dieIdx, $player->activeDieArray) ||
                        !$player->activeDieArray[$dieIdx]->has_skill('Auxiliary')) {
                        $this->set_message('Invalid auxiliary choice');
                        return FALSE;
                    }
                    $die = $player->activeDieArray[$dieIdx];
                    $die->add_flag('AddAuxiliary');
                    $player->waitingOnAction = FALSE;
                    $game->log_action(
                        'add_auxiliary',
                        $player->playerId,
                        array(
                            'roundNumber' => $game->roundNumber,
                            'die' => $die->get_action_log_data(),
                        )
                    );
                    $this->set_message('Chose to add auxiliary die');
                    break;
                case 'decline':
                    $game->setAllToNotWaiting();
                    $game->log_action(
                        'decline_auxiliary',
                        $player->playerId,
                        array('declineAuxiliary' => TRUE)
                    );
                    $this->set_message('Declined auxiliary dice');
                    break;
                default:
                    $this->set_message('Invalid response to auxiliary choice.');
                    return FALSE;
            }
            $this->save_game($game);
            return TRUE;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::react_to_auxiliary: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while making auxiliary decision');
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
            $player = $game->playerArray[$playerIdx];

            switch ($action) {
                case 'add':
                    if (!array_key_exists($dieIdx, $player->activeDieArray) ||
                        !$player->activeDieArray[$dieIdx]->has_skill('Reserve')) {
                        $this->set_message('Invalid reserve choice');
                        return FALSE;
                    }
                    $die = $player->activeDieArray[$dieIdx];
                    $die->add_flag('AddReserve');
                    $player->waitingOnAction = FALSE;
                    $game->log_action(
                        'add_reserve',
                        $player->playerId,
                        array( 'die' => $die->get_action_log_data(), )
                    );
                    $this->set_message('Reserve die chosen successfully');
                    break;
                case 'decline':
                    $player->waitingOnAction = FALSE;
                    $game->log_action(
                        'decline_reserve',
                        $player->playerId,
                        array('declineReserve' => TRUE)
                    );
                    $this->set_message('Declined reserve dice');
                    break;
                default:
                    $this->set_message('Invalid response to reserve choice.');
                    return FALSE;
            }

            $this->save_game($game);

            return TRUE;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::react_to_reserve: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while making reserve decision');
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
                        $this->set_message('Only one chance die can be rerolled');
                        return FALSE;
                    }
                    $argArray['rerolledDieIdx'] = (int)$dieIdxArray[0];
                    break;
                case 'focus':
                    if (count($dieIdxArray) != count($dieValueArray)) {
                        $this->set_message('Mismatch in number of indices and values');
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
                    $this->set_message('Invalid action to respond to initiative.');
                    return FALSE;
            }

            $isSuccessful = $game->react_to_initiative($argArray);
            if ($isSuccessful) {
                $this->save_game($game);

                if ($isSuccessful['gainedInitiative']) {
                    $this->set_message('Successfully gained initiative');
                } else {
                    $this->set_message('Failed to gain initiative');
                }
            } else {
                $this->set_message($game->message);
            }

            return $isSuccessful;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::react_to_initiative: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while reacting to initiative');
            return FALSE;
        }
    }

    // adjust_fire expects the following inputs:
    //
    //   $action:
    //       One of {'turndown', 'no_turndown', 'cancel'}.
    //
    //   $dieIdxArray:
    //       (i)   If this is a 'turndown' action, then this is the nonempty array
    //             of die indices corresponding to the die values in
    //             dieValueArray. This can be either the indices of ALL fire
    //             dice OR just a subset.
    //       (ii)  If this is a 'no_turndown' action, then this will be ignored.
    //       (iii) If this is a 'cancel' action, then this will be ignored.
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
                    if (0 == count($dieIdxArray)) {
                        $this->set_message('At least one fire value must be turned down for a turndown action');
                        return FALSE;
                    }

                    if (count($dieIdxArray) != count($dieValueArray)) {
                        $this->set_message('Mismatch in number of indices and values');
                        return FALSE;
                    }

                    $argArray['fireValueArray'] = array();
                    foreach ($dieIdxArray as $tempIdx => $dieIdx) {
                        $argArray['fireValueArray'][$dieIdx] = $dieValueArray[$tempIdx];
                    }
                    break;
                case 'no_turndown':  // fallthrough to allow multiple cases with the same logic
                case 'cancel':
                    $argArray['dieIdxArray'] = $dieIdxArray;
                    $argArray['dieValueArray'] = $dieValueArray;
                    break;
                default:
                    $this->set_message('Invalid action to adjust fire dice.');
                    return FALSE;
            }

            $isSuccessful = $game->react_to_firing($argArray);
            if ($isSuccessful) {
                $this->save_game($game);
            } else {
                $this->set_message('Invalid fire turndown');
            }

            return $isSuccessful;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::adjust_fire: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while adjusting fire dice');
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
                $this->set_message("Game $gameId does not exist");
                return NULL;
            }
            if (($fetchResult[0]['status'] != 'COMPLETE') &&
                ($fetchResult[0]['status'] != 'CANCELLED')) {
                $this->set_message("Game $gameId isn't complete");
                return NULL;
            }
            if ($fetchResult[0]['was_game_dismissed'] === NULL) {
                $this->set_message("You aren't a player of game $gameId");
                return NULL;
            }
            if ((int)$fetchResult[0]['was_game_dismissed'] == 1) {
                $this->set_message("You have already dismissed game $gameId");
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

            $this->set_message('Dismissing game succeeded');
            return TRUE;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::dismiss_game: ' .
                $e->getMessage()
            );
            $this->set_message('Internal error while dismissing a game');
            return FALSE;
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
        $playerInfoArray = $this->player()->get_player_info($currentPlayerId);

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
    protected function validate_url($url) {
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

    protected function set_message($message) {
        $this->message = $message;
        if (!is_null($this->parent)) {
            $this->parent->set_message($message);
        }
    }

    /**
     * Getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        if (property_exists($this, $property)) {
            switch ($property) {
                default:
                    return $this->$property;
            }
        }
    }

    /**
     * Setter
     *
     * @param string $property
     * @param mixed $value
     */
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
