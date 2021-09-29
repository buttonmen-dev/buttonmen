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

    // properties -- all accessible, but written as protected to enable the use of
    //               getters and setters

    /**
     * Message intended for GUI
     *
     * @var string
     */
    protected $message;

    /**
     * Timestamp of last game action
     *
     * @var DateTime
     */
    protected $timestamp;

    /**
     * Connection to database
     *
     * @var PDO
     */
    protected static $conn = NULL;

    /**
     * Modelled connection to database
     *
     * @var BMDB
     */
    protected static $db = NULL;

    /**
     * Owning BMInterface, allows back navigation
     *
     * @var BMInterface
     */
    protected $parent = NULL;

    /**
     * Is the interface for testing?
     *
     * @var bool
     */
    public $isTest;

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
            require_once __DIR__.'/../../test/src/database/mysql.test.inc.php';
        } else {
            require_once __DIR__.'/../database/mysql.inc.php';
        }

        if (!isset(self::$conn)) {
            self::$conn = conn();
        }

        self::$db = new BMDB(self::$conn);
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

    /**
     * Cast BMInterface to BMInterfaceForum
     *
     * @return BMInterfaceForum
     */
    public function forum() {
        $interface = $this->cast('BMInterfaceForum');
        $interface->parent = $this;
        return $interface;
    }

    /**
     * Cast BMInterface to BMInterfaceGame
     *
     * @return BMInterfaceGame
     */
    public function game() {
        $interface = $this->cast('BMInterfaceGame');
        $interface->parent = $this;
        return $interface;
    }

    /**
     * Cast BMInterface to BMInterfaceGameAction
     *
     * @return BMInterfaceGameAction
     */
    public function game_action() {
        $interface = $this->cast('BMInterfaceGameAction');
        $interface->parent = $this;
        return $interface;
    }

    /**
     * Cast BMInterface to BMInterfaceGameChat
     *
     * @return BMInterfaceGameChat
     */
    public function game_chat() {
        $interface = $this->cast('BMInterfaceGameChat');
        $interface->parent = $this;
        return $interface;
    }

    /**
     * Cast BMInterface to BMInterfaceHelp
     *
     * @return BMInterfaceHelp
     */
    public function help() {
        $interface = $this->cast('BMInterfaceHelp');
        $interface->parent = $this;
        return $interface;
    }

    /**
     * Cast BMInterface to BMInterfaceHistory
     *
     * @return BMInterfaceHistory
     */
    public function history() {
        $interface = $this->cast('BMInterfaceHistory');
        $interface->parent = $this;
        return $interface;
    }

    /**
     * Cast BMInterface to BMInterfacePlayer
     *
     * @return BMInterfacePlayer
     */
    public function player() {
        $interface = $this->cast('BMInterfacePlayer');
        $interface->parent = $this;
        return $interface;
    }

    // methods

    /**
     * Validate and set homepage URL
     *
     * @param string|NULL $homepage
     * @param array $infoArray
     * @return bool
     */
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

    /**
     * Load API game data for a certain game from the perspective of a certain player
     *
     * @param int $playerId
     * @param int $gameId
     * @param int $logEntryLimit
     * @return array
     */
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
            $data['dieBackgroundType'] = $this->load_die_background_type($playerId);
            $data['currentPlayerIdx'] = $currentPlayerIdx;
            foreach ($game->playerArray as $gamePlayerIdx => $gamePlayer) {
                $playerName = $this->get_player_name_from_id($gamePlayer->playerId);
                $playerNameArray[] = $playerName;
                $data['playerDataArray'][$gamePlayerIdx]['playerName'] = $playerName;
                $isOnVacation = (bool) $game->playerArray[$gamePlayerIdx]->isOnVacation;
                $data['playerDataArray'][$gamePlayerIdx]['isOnVacation'] = $isOnVacation;

                $isChatPrivate = (bool) $game->playerArray[$gamePlayerIdx]->isChatPrivate;
                $data['playerDataArray'][$gamePlayerIdx]['isChatPrivate'] = $isChatPrivate;
            }

            $actionLogArray = $this->game_action()->load_game_action_log($game, $logEntryLimit);
            if (empty($actionLogArray)) {
                $data['gameActionLog'] = NULL;
                $data['gameActionLogCount'] = 0;
            } else {
                $data['gameActionLog'] = $actionLogArray['logEntries'];
                $data['gameActionLogCount'] = $actionLogArray['nEntries'];
            }

            $chatLogArray = $this->game_chat()->load_game_chat_log($playerId, $game, $logEntryLimit);
            if (empty($chatLogArray)) {
                $data['gameChatLog'] = NULL;
                $data['gameChatLogCount'] = 0;
            } else {
                $data['gameChatLog'] = $chatLogArray['chatEntries'];
                $data['gameChatLogCount'] = $chatLogArray['nEntries'];
            }

            $data['timestamp'] = $this->timestamp;

            $data['gameChatEditable'] = $this->game_chat()->find_editable_chat_timestamp(
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

            $data['creatorDataArray']['creatorName'] =
                $this->get_player_name_from_id($data['creatorDataArray']['creatorId']);

            return $data;
        }
        return NULL;
    }

    /**
     * Count the number of pending games for a certain player
     *
     * @param int $playerId
     * @return array
     */
    public function count_pending_games($playerId) {
        try {
            $query =
                'SELECT COUNT(*) '.
                'FROM game_player_map AS gpm '.
                   'LEFT JOIN game AS g ON g.id = gpm.game_id '.
                'WHERE gpm.player_id = :player_id '.
                   'AND gpm.is_awaiting_action = 1 '.
                   'AND g.status_id IN '.
                       '(SELECT id FROM game_status WHERE name IN (\'NEW\', \'ACTIVE\'))';
            $parameters = array(':player_id' => $playerId);
            $data = array();
            $data['count'] = self::$db->select_single_value($query, $parameters, 'int');
            $this->set_message('Pending game count succeeded.');
            return $data;
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Pending game count failed.');
            error_log('Pending game count failed for player ' . $playerId);
            return NULL;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::count_pending_games: ' .
                $e->getMessage()
            );
            $this->set_message('Pending game count failed.');
            return NULL;
        }
    }

    /**
     * Load game from database
     *
     * @param int $gameId
     * @param int $logEntryLimit
     * @param bool $forceValidateRecipes
     * @return bool
     */
    protected function load_game($gameId, $logEntryLimit = NULL, $forceValidateRecipes = FALSE) {
        try {
            $game = $this->load_game_parameters($gameId, $forceValidateRecipes);

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
            $this->load_turbo_cache($game);

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

    /**
     * Load all game parameters from database
     *
     * @param int $gameId
     * @param bool $forceValidateRecipes
     * @return BMGame
     */
    protected function load_game_parameters($gameId, $forceValidateRecipes) {
        // check that the gameId exists
        $query = 'SELECT g.*,'.
                 'UNIX_TIMESTAMP(g.last_action_time) AS last_action_timestamp, '.
                 's.name AS status_name,'.
                 'v.player_id, v.position, v.autopass, v.fire_overshooting,'.
                 'v.button_name, v.original_recipe, v.alt_recipe,'.
                 'v.n_rounds_won, v.n_rounds_lost, v.n_rounds_drawn,'.
                 'v.did_win_initiative,'.
                 'v.is_awaiting_action, '.
                 'v.is_button_random, '.
                 'UNIX_TIMESTAMP(v.last_action_time) AS player_last_action_timestamp, '.
                 'v.was_game_dismissed, '.
                 'v.has_player_accepted, '.
                 'v.is_on_vacation, '.
                 'v.is_chat_private '.
                 'FROM game AS g '.
                 'LEFT JOIN game_status AS s '.
                 'ON s.id = g.status_id '.
                 'LEFT JOIN game_player_view AS v '.
                 'ON g.id = v.game_id '.
                 'WHERE g.id = :game_id '.
                 'ORDER BY g.id;';
        $parameters = array(':game_id' => $gameId);
        $columnReturnTypes = array(
            'alt_recipe' => 'str_or_null',
            'autopass' => 'bool',
            'button_name' => 'str_or_null',
            'creator_id' => 'int',
            'current_player_id' => 'int_or_null',
            'description' => 'str',
            'did_win_initiative' => 'bool',
            'fire_overshooting' => 'bool',
            'game_state' => 'int',
            'has_player_accepted' => 'bool',
            'id' => 'int',
            'is_awaiting_action' => 'bool',
            'is_button_random' => 'bool',
            'is_chat_private' => 'bool',
            'is_on_vacation' => 'bool',
            'last_action_timestamp' => 'int_or_null',
            'n_recent_passes' => 'int',
            'n_rounds_won' => 'int',
            'n_rounds_lost' => 'int',
            'n_rounds_drawn' => 'int',
            'n_target_wins' => 'int',
            'original_recipe' => 'str_or_null',
            'player_id' => 'int_or_null',
            'player_last_action_timestamp' => 'int_or_null',
            'position' => 'int_or_null',
            'previous_game_id' => 'int_or_null',
            'turn_number_in_round' => 'int',
            'was_game_dismissed' => 'bool',
        );
        $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);

        foreach ($rows as $row) {
            // load game attributes
            if (!isset($game)) {
                $game = new BMGame;
                $game->gameId = $gameId;
                $this->load_game_attributes($game, $row);
            }

            $pos = $row['position'];
            if (isset($pos)) {
                $player = $game->playerArray[$pos];
                $player->playerId = $row['player_id'];
                $player->autopass = $row['autopass'];
                $player->fireOvershooting = $row['fire_overshooting'];
                $player->hasPlayerAcceptedGame = $row['has_player_accepted'];
            }

            if ($row['did_win_initiative']) {
                $game->playerWithInitiativeIdx = $pos;
            }

            $game->playerArray[$pos]->set_gameScoreArray(
                array(
                    'W' => $row['n_rounds_won'],
                    'L' => $row['n_rounds_lost'],
                    'D' => $row['n_rounds_drawn']
                )
            );

            $this->load_button($game, $pos, $row, $forceValidateRecipes);
            $this->load_player_attributes($game, $pos, $row);
            $this->load_lastActionTime($game, $pos, $row);
            $this->load_hasPlayerDismissedGame($game, $pos, $row);
        }

        if (!isset($game)) {
            return NULL;
        }

        return $game;
    }

    /**
     * Load game attributes
     *
     * @param BMGame $game
     * @param array $row
     */
    protected function load_game_attributes($game, $row) {
        $game->creatorId = $row['creator_id'];
        $game->gameState = $row['game_state'];
        $game->maxWins   = $row['n_target_wins'];
        $game->turnNumberInRound = $row['turn_number_in_round'];
        $game->nRecentPasses = $row['n_recent_passes'];
        $game->description = $row['description'];
        $game->previousGameId = $row['previous_game_id'];
        $this->timestamp = $row['last_action_timestamp'];
    }

    /**
     * Load button
     *
     * @param BMGame $game
     * @param int $pos
     * @param array $row
     * @param bool $forceValidateRecipes
     */
    protected function load_button($game, $pos, $row, $forceValidateRecipes) {
        if (isset($row['button_name'])) {
            if (isset($row['alt_recipe'])) {
                $recipe = $row['alt_recipe'];
            } else {
                $recipe = $this->get_button_recipe_from_name($row['button_name']);
            }
            if (isset($recipe)) {
                $button = new BMButton;
                $button->load($recipe, $row['button_name'], FALSE, FALSE, $forceValidateRecipes);
                if (isset($row['alt_recipe'])) {
                    $button->hasAlteredRecipe = TRUE;
                }
                $player = $game->playerArray[$pos];
                $player->button = $button;
            } else {
                throw new InvalidArgumentException('Invalid button name.');
            }
            if (isset($row['original_recipe'])) {
                $player->button->originalRecipe = $row['original_recipe'];
            }
        }

        if ($row['is_button_random']) {
            $player = $game->playerArray[$pos];
            $player->isButtonChoiceRandom = TRUE;
        }
    }

    /**
     * Load player attributes
     *
     * @param BMGame $game
     * @param int $pos
     * @param array $row
     */
    protected function load_player_attributes($game, $pos, $row) {
        $player = $game->playerArray[$pos];
        $player->waitingOnAction = $row['is_awaiting_action'];
        $player->isOnVacation = $row['is_on_vacation'];
        $player->isChatPrivate = $row['is_chat_private'];

        if (isset($row['current_player_id']) &&
            isset($row['player_id']) &&
            ($row['current_player_id'] === $row['player_id'])) {
            $game->activePlayerIdx = $pos;
        }

        if ($row['did_win_initiative']) {
            $game->playerWithInitiativeIdx = $pos;
        }
    }

    /**
     * Load last action time
     *
     * @param BMGame $game
     * @param int $pos
     * @param array $row
     */
    protected function load_lastActionTime($game, $pos, $row) {
        if (isset($row['player_last_action_timestamp'])) {
            $player = $game->playerArray[$pos];
            $player->lastActionTime = $row['player_last_action_timestamp'];
        }
    }

    /**
     * Load whether a player has dismissed a certain game
     *
     * @param BMGame $game
     * @param int $pos
     * @param array $row
     */
    protected function load_hasPlayerDismissedGame($game, $pos, $row) {
        if (isset($row['was_game_dismissed'])) {
            $player = $game->playerArray[$pos];
            $player->hasPlayerDismissedGame = $row['was_game_dismissed'];
        }
    }

    /**
     * Set log entry limit
     *
     * @param BMGame $game
     * @param int $logEntryLimit
     */
    protected function set_logEntryLimit($game, $logEntryLimit) {
        if ($game->gameState == BMGameState::END_GAME) {
            $game->logEntryLimit = NULL;
        } else {
            $game->logEntryLimit = $logEntryLimit;
        }
    }

    /**
     * Load swing values from last round
     *
     * @param BMGame $game
     */
    protected function load_swing_values_from_last_round($game) {
        $query = 'SELECT * '.
                 'FROM game_swing_map '.
                 'WHERE game_id = :game_id '.
                 'AND is_expired = :is_expired';
        $parameters = array(':game_id' => $game->gameId,
                            ':is_expired' => 1);
        $columnReturnTypes = array(
            'player_id' => 'int',
            'swing_type' => 'str',
            'swing_value' => 'int',
        );
        $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
        foreach ($rows as $row) {
            $playerIdx = array_search($row['player_id'], $game->playerIdArray);
            $player = $game->playerArray[$playerIdx];
            $player->prevSwingValueArray[$row['swing_type']] = $row['swing_value'];
        }
    }

    /**
     * Load swing values from this round
     *
     * @param BMGame $game
     */
    protected function load_swing_values_from_this_round($game) {
        $query = 'SELECT * '.
                 'FROM game_swing_map '.
                 'WHERE game_id = :game_id '.
                 'AND is_expired = :is_expired';
        $parameters = array(':game_id' => $game->gameId,
                            ':is_expired' => 0);
        $columnReturnTypes = array(
            'player_id' => 'int',
            'swing_type' => 'str',
            'swing_value' => 'int_or_null',
        );
        $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
        foreach ($rows as $row) {
            $playerIdx = array_search($row['player_id'], $game->playerIdArray);
            $player = $game->playerArray[$playerIdx];
            $player->swingValueArray[$row['swing_type']] = $row['swing_value'];
        }
    }

    /**
     * Load option values from last round
     *
     * @param BMGame $game
     */
    protected function load_option_values_from_last_round($game) {
        $query = 'SELECT * '.
                 'FROM game_option_map '.
                 'WHERE game_id = :game_id '.
                 'AND is_expired = :is_expired';
        $parameters = array(':game_id' => $game->gameId,
                            ':is_expired' => 1);
        $columnReturnTypes = array(
            'player_id' => 'int',
            'die_idx' => 'int',
            'option_value' => 'int',
        );
        $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
        foreach ($rows as $row) {
            $playerIdx = array_search($row['player_id'], $game->playerIdArray);
            $player = $game->playerArray[$playerIdx];
            $player->prevOptValueArray[$row['die_idx']] = $row['option_value'];
        }
    }

    /**
     * Load option values from this round
     *
     * @param BMGame $game
     */
    protected function load_option_values_from_this_round($game) {
        $query = 'SELECT * '.
                 'FROM game_option_map '.
                 'WHERE game_id = :game_id '.
                 'AND is_expired = :is_expired';
        $parameters = array(':game_id' => $game->gameId,
                            ':is_expired' => 0);
        $columnReturnTypes = array(
            'player_id' => 'int',
            'die_idx' => 'int',
            'option_value' => 'int_or_null',
        );
        $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
        foreach ($rows as $row) {
            $playerIdx = array_search($row['player_id'], $game->playerIdArray);
            $player = $game->playerArray[$playerIdx];
            $player->optValueArray[$row['die_idx']] = $row['option_value'];
        }
    }

    /**
     * Load all die attributes
     *
     * @param BMGame $game
     */
    protected function load_die_attributes($game) {
        // add die attributes
        $query = 'SELECT d.*,'.
                 '       s.name AS status '.
                 'FROM die AS d '.
                 'LEFT JOIN die_status AS s '.
                 'ON d.status_id = s.id '.
                 'WHERE game_id = :game_id '.
                 'ORDER BY id;';
        $parameters = array(':game_id' => $game->gameId);
        $columnReturnTypes = array(
            'actual_max' => 'int_or_null',
            'flags' => 'str_or_null',
            'original_owner_id' => 'int',
            'owner_id' => 'int',
            'position' => 'int',
            'recipe' => 'str',
            'status' => 'str',
            'value' => 'int_or_null',
        );
        $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);

        $activeDieArrayArray = array_fill(0, $game->nPlayers, array());
        $captDieArrayArray = array_fill(0, $game->nPlayers, array());
        $outOfPlayDieArrayArray = array_fill(0, $game->nPlayers, array());

        foreach ($rows as $row) {
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

            $die->value = $row['value'];

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

    /**
     * Load cached turbo values
     *
     * @param BMGame $game
     */
    protected function load_turbo_cache($game) {
        $turboCache = array();

        $query = 'SELECT * '.
                 'FROM game_turbo_cache '.
                 'WHERE game_id = :game_id';
        $parameters = array(':game_id' => $game->gameId);
        $columnReturnTypes = array(
            'die_idx' => 'int',
            'turbo_size' => 'int',
        );
        $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);
        foreach ($rows as $row) {
            $turboCache[$row['die_idx']] = $row['turbo_size'];
        }

        $game->turboCache = $turboCache;
    }

    /**
     * Set swing max value
     *
     * @param BMDieTwin $die
     * @param int $originalPlayerIdx
     * @param BMGame $game
     * @param array $row
     */
    protected function set_swing_max($die, $originalPlayerIdx, $game, $row) {
        if (isset($die->swingType) && !$die instanceof BMDieTwin) {
            $game->request_swing_values($die, $die->swingType, $originalPlayerIdx);
            $die->set_swingValue($game->playerArray[$originalPlayerIdx]->swingValueArray);
            if (isset($row['actual_max'])) {
                $die->max = $row['actual_max'];
            }
        }
    }

    /**
     * Set twin die max
     *
     * @param BMDieTwin $die
     * @param int $originalPlayerIdx
     * @param BMGame $game
     * @param array $row
     */
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
                    $subdie->max = $row['actual_max'] / 2;
                }
            }
        }

        $die->recalc_max_min();
    }

    /**
     * Set option die max
     *
     * @param BMDieOption $die
     * @param array $row
     */
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

    /**
     * Recreate option dice request arrays
     *
     * @param BMGame $game
     */
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

    /**
     * Save game to database
     *
     * @param BMGame $game
     */
    protected function save_game(BMGame $game) {
        // force game to proceed to the latest possible before saving
        $game->proceed_to_next_user_action();

        // start transaction
        self::$conn->beginTransaction();

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
            $this->save_turbo_cache($game);
            $this->save_out_of_play_dice($game);
            $this->delete_dice_marked_as_deleted($game);
            $this->game_action()->save_action_log($game);
            $this->game_chat()->save_chat_log($game);

            self::$conn->commit();
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::save_game: ' .
                $e->getMessage()
            );
            $this->set_message("Game save failed: $e");
            self::$conn->rollBack();
            throw new BMExceptionDatabase("Game save failed");
        }
    }

    /**
     * Resolve random button selection
     *
     * @param BMGame $game
     */
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
                $allButtonData = $this->get_button_data(
                    NULL,
                    NULL,
                    TRUE,
                    array('exclude_from_random' => 'false')
                );
                $nButtons = count($allButtonData);
            }

            $randIdx = bm_rand(0, $nButtons - 1);
            $buttonId = $allButtonData[$randIdx]['buttonId'];

            $this->choose_button($game, $buttonId, $player);
        }

        // ensure that the chat and game acceptance have also been cached
        $this->game_action()->save_action_log($game);
        $this->game_chat()->save_chat_log($game);
        $this->save_player_game_decisions($game);

        $game = $this->load_game($game->gameId);
        $game->proceed_to_next_user_action();
    }

    /**
     * Should random buttons be resolved now?
     *
     * @param BMGame $game
     * @return bool
     */
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

    /**
     * Choose button
     *
     * @param BMGame $game
     * @param int $buttonId
     * @param BMPlayer $player
     */
    protected function choose_button(BMGame $game, $buttonId, $player) {
        // add info to game_player_map
        $query = 'UPDATE game_player_map '.
                 'SET button_id = :button_id, '.
                 '    is_awaiting_action = 0 '.
                 'WHERE '.
                 'game_id = :game_id AND '.
                 'position = :position';
        $parameters = array(':game_id'   => $game->gameId,
                            ':button_id' => $buttonId,
                            ':position'  => $player->position);
        self::$db->update($query, $parameters);
    }

    /**
     * Save basic game parameters
     *
     * @param BMGame $game
     */
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
        $parameters = array(':status' => $this->get_game_status($game),
                            ':game_state' => $game->gameState,
                            ':round_number' => $game->roundNumber,
                            ':turn_number_in_round' => $game->turnNumberInRound,
                            ':n_recent_passes' => $game->nRecentPasses,
                            ':current_player_id' => $this->get_currentPlayerId($game),
                            ':game_id' => $game->gameId);
        self::$db->update($query, $parameters);
    }

    /**
     * Get current player ID
     *
     * @param BMGame $game
     * @return int
     */
    protected function get_currentPlayerId($game) {
        if (is_null($game->activePlayerIdx)) {
            $currentPlayerId = NULL;
        } else {
            $currentPlayerId = $game->playerArray[$game->activePlayerIdx]->playerId;
        }

        return $currentPlayerId;
    }

    /**
     * Get game status
     *
     * @param BMGame $game
     * @return string
     */
    protected function get_game_status($game) {
        if (BMGameState::END_GAME == $game->gameState) {
            $status = 'COMPLETE';
        } elseif (BMGameState::CANCELLED == $game->gameState) {
            $status = 'CANCELLED';
        } elseif (in_array(NULL, $game->playerIdArray) ||
                  in_array(NULL, $game->buttonArray)) {
            $status = 'OPEN';
        } elseif (BMGameState::CHOOSE_JOIN_GAME == $game->gameState) {
            $status = 'NEW';
        } else {
            $status = 'ACTIVE';
        }

        return $status;
    }

    /**
     * Save button recipes
     *
     * @param BMGame $game
     */
    protected function save_button_recipes($game) {
        foreach ($game->playerArray as $player) {
            if ($player->button instanceof BMButton) {
                $boundParameters = array(':original_recipe' => $player->button->originalRecipe,
                                         ':game_id' => $game->gameId,
                                         ':player_id' => $player->playerId);

                $query = 'UPDATE game_player_map '.
                         'SET original_recipe = :original_recipe ';

                if ($player->button->hasAlteredRecipe) {
                    $query .= ', alt_recipe = :alt_recipe ';
                    $boundParameters[':alt_recipe'] = $player->button->recipe;
                }

                $query .= 'WHERE game_id = :game_id '.
                          'AND player_id = :player_id;';

                self::$db->update($query, $boundParameters);
            }
        }
    }

    /**
     * Save random button choice
     *
     * @param BMGame $game
     */
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
                $parameters = array(':game_id' => $game->gameId,
                                    ':position' => $position);
                self::$db->update($query, $parameters);
            }
        }
    }

    /**
     * Save round scores
     *
     * @param BMGame $game
     */
    protected function save_round_scores($game) {
        foreach ($game->playerArray as $player) {
            $query = 'UPDATE game_player_map '.
                     'SET n_rounds_won = :n_rounds_won,'.
                     '    n_rounds_lost = :n_rounds_lost,'.
                     '    n_rounds_drawn = :n_rounds_drawn '.
                     'WHERE game_id = :game_id '.
                     'AND player_id = :player_id;';
            $parameters = array(':n_rounds_won' => $player->gameScoreArray['W'],
                                ':n_rounds_lost' => $player->gameScoreArray['L'],
                                ':n_rounds_drawn' => $player->gameScoreArray['D'],
                                ':game_id' => $game->gameId,
                                ':player_id' => $player->playerId);
            self::$db->update($query, $parameters);
        }
    }

    /**
     * Clear swing values from database
     *
     * @param BMGame $game
     */
    protected function clear_swing_values_from_database($game) {
        $query = 'DELETE FROM game_swing_map '.
                 'WHERE game_id = :game_id;';
        $parameters = array(':game_id' => $game->gameId);
        self::$db->update($query, $parameters);
    }

    /**
     * Clear option values from database
     *
     * @param BMGame $game
     */
    protected function clear_option_values_from_database($game) {
        $query = 'DELETE FROM game_option_map '.
                 'WHERE game_id = :game_id;';
        $parameters = array(':game_id' => $game->gameId);
        self::$db->update($query, $parameters);
    }

    /**
     * Save swing values from last round
     *
     * @param BMGame $game
     */
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
                $parameters = array(':game_id'     => $game->gameId,
                                    ':player_id'   => $player->playerId,
                                    ':swing_type'  => $swingType,
                                    ':swing_value' => $swingValue,
                                    ':is_expired'  => TRUE);
                self::$db->update($query, $parameters);
            }
        }
    }

    /**
     * Save swing values from this round
     *
     * @param BMGame $game
     */
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
                $parameters = array(':game_id'     => $game->gameId,
                                    ':player_id'   => $player->playerId,
                                    ':swing_type'  => $swingType,
                                    ':swing_value' => $swingValue,
                                    ':is_expired'  => FALSE);
                self::$db->update($query, $parameters);
            }
        }
    }

    /**
     * Save option values from last round
     *
     * @param BMGame $game
     */
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
                $parameters = array(':game_id'   => $game->gameId,
                                    ':player_id' => $player->playerId,
                                    ':die_idx'   => $dieIdx,
                                    ':option_value' => $optionValue,
                                    ':is_expired' => TRUE);
                self::$db->update($query, $parameters);
            }
        }
    }

    /**
     * Save option values from this round
     *
     * @param BMGame $game
     */
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
                $parameters = array(':game_id'   => $game->gameId,
                                    ':player_id' => $player->playerId,
                                    ':die_idx'   => $dieIdx,
                                    ':option_value' => $optionValue,
                                    ':is_expired' => FALSE);
                self::$db->update($query, $parameters);
            }
        }
    }

    /**
     * Save player decisions about whether to join a proposed game
     *
     * @param BMGame $game
     */
    protected function save_player_game_decisions($game) {
        foreach ($game->playerArray as $player) {
            $query = 'UPDATE game_player_map '.
                     'SET has_player_accepted = :has_player_accepted '.
                     'WHERE game_id = :game_id '.
                     'AND position = :position;';
            $parameters = array(':has_player_accepted' => $player->hasPlayerAcceptedGame,
                                ':game_id' => $game->gameId,
                                ':position' => $player->position);
            self::$db->update($query, $parameters);
        }
    }

    /**
     * Save whether player won initiative
     *
     * @param BMGame $game
     */
    protected function save_player_with_initiative($game) {
        if (!is_null($game->playerWithInitiativeIdx)) {
            // set all players to not having initiative
            $query = 'UPDATE game_player_map '.
                     'SET did_win_initiative = 0 '.
                     'WHERE game_id = :game_id;';
            $parameters = array(':game_id' => $game->gameId);
            self::$db->update($query, $parameters);

            // set player that won initiative
            $query = 'UPDATE game_player_map '.
                     'SET did_win_initiative = 1 '.
                     'WHERE game_id = :game_id '.
                     'AND player_id = :player_id;';
            $parameters = array(':game_id' => $game->gameId,
                                ':player_id' => $game->playerArray[$game->playerWithInitiativeIdx]->playerId);
            self::$db->update($query, $parameters);
        }
    }

    /**
     * Save players awaiting action
     *
     * @param BMGame $game
     */
    protected function save_players_awaiting_action($game) {
        foreach ($game->playerArray as $player) {
            $query = 'UPDATE game_player_map '.
                     'SET is_awaiting_action = :is_awaiting_action '.
                     'WHERE game_id = :game_id '.
                     'AND position = :position;';
            $parameters = array(':is_awaiting_action' => $player->waitingOnAction,
                                ':game_id' => $game->gameId,
                                ':position' => $player->position);
            self::$db->update($query, $parameters);
        }
    }

    /**
     * Regenerate essential die flags
     *
     * @param BMGame $game
     */
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

    /**
     * Mark existing dice as deleted
     *
     * @param BMGame $game
     */
    protected function mark_existing_dice_as_deleted($game) {
        // set existing dice to have a status of DELETED and get die ids
        //
        // note that the logic is written this way to make debugging easier
        // in case something fails during the addition of dice
        $query = 'UPDATE die '.
                 'SET status_id = '.
                 '    (SELECT id FROM die_status WHERE name = "DELETED") '.
                 'WHERE game_id = :game_id;';
        $parameters = array(':game_id' => $game->gameId);
        self::$db->update($query, $parameters);
    }

    /**
     * Save dice
     *
     * @param BMGame $game
     * @param array $dieArrayArray
     * @param string $status
     */
    protected function save_dice($game, $dieArrayArray, $status) {
        if (isset($dieArrayArray)) {
            foreach ($dieArrayArray as $playerIdx => $dieArray) {
                foreach ($dieArray as $dieIdx => $die) {
                    $this->db_insert_die($game, $playerIdx, $die, $status, $dieIdx);
                }
            }
        }
    }

    /**
     * Save active dice
     *
     * @param BMGame $game
     */
    protected function save_active_dice($game) {
        $this->save_dice($game, $game->activeDieArrayArray, 'NORMAL');
    }

    /**
     * Save captured dice
     *
     * @param BMGame $game
     */
    protected function save_captured_dice($game) {
        $this->save_dice($game, $game->capturedDieArrayArray, 'CAPTURED');
    }

    /**
     * Save cached turbo values
     *
     * @param BMGame $game
     */
    protected function save_turbo_cache(BMGame $game) {
        // delete previously saved turbo cache
        $query = 'DELETE FROM game_turbo_cache '.
                 'WHERE game_id = :game_id;';
        $parameters = array(':game_id' => $game->gameId);
        self::$db->update($query, $parameters);

        if (empty($game->turboCache)) {
            return;
        }

        // now add the new turbo cache
        foreach ($game->turboCache as $dieIdx => $turboSize) {
            $query = 'INSERT INTO game_turbo_cache '.
                     '    (game_id, die_idx, turbo_size) '.
                     'VALUES '.
                     '    (:game_id, :die_idx, :turbo_size);';
            $parameters = array(':game_id' => $game->gameId,
                                ':die_idx' => $dieIdx,
                                ':turbo_size' => $turboSize);
            self::$db->update($query, $parameters);
        }
    }

    /**
     * Save out-of-play dice
     *
     * @param BMGame $game
     */
    protected function save_out_of_play_dice($game) {
        $this->save_dice($game, $game->outOfPlayDieArrayArray, 'OUT_OF_PLAY');
    }

    /**
     * Delete dice marked as deleted
     *
     * @param BMGame $game
     */
    protected function delete_dice_marked_as_deleted($game) {
        // delete dice with a status of "DELETED" for this game
        $query = 'DELETE FROM die '.
                 'WHERE status_id = '.
                 '    (SELECT id FROM die_status WHERE name = "DELETED") '.
                 'AND game_id = :game_id;';
        $parameters = array(':game_id' => $game->gameId);
        self::$db->update($query, $parameters);
    }

    /**
     * Actually insert a die into the database - all error checking to be done by caller
     *
     * @param BMGame $game
     * @param int $playerIdx
     * @param BMDie $activeDie
     * @param string $status
     * @param int $dieIdx
     */
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

        $parameters = array(':owner_id' => $game->playerArray[$playerIdx]->playerId,
                            ':original_owner_id' => $game->playerArray[$activeDie->originalPlayerIdx]->playerId,
                            ':game_id' => $game->gameId,
                            ':status' => $status,
                            ':recipe' => $activeDie->recipe,
                            ':actual_max' => $actualMax,
                            ':position' => $dieIdx,
                            ':value' => $activeDie->value,
                            ':flags' => $flags);
        self::$db->update($query, $parameters);
    }

    /**
     * Get all player games of a certain type (new, active, or inactive) from the database
     *
     * @param int $playerId
     * @param string $type
     * @return array
     */
    protected function get_all_games($playerId, $type) {
        try {
            $this->set_message('All game details retrieved successfully.');

            // the following SQL logic assumes that there are only two players per game
            $query = 'SELECT v1.game_id,'.
                     'v1.player_id AS opponent_id,'.
                     'v1.player_name AS opponent_name,'.
                     'v1.is_on_vacation AS opponent_on_vacation,'.
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
                $query .= 'AND s.name = "ACTIVE"';
            } elseif ('NEW' == $type) {
                $query .= 'AND s.name = "NEW"';
            } elseif ('COMPLETE' == $type) {
                $query .= 'AND s.name = "COMPLETE" AND v2.was_game_dismissed = 0 ';
            } elseif ('CANCELLED' == $type) {
                $query .= 'AND s.name = "CANCELLED" AND v2.was_game_dismissed = 0 ';
            }
            $query .= 'ORDER BY g.last_action_time ASC;';
            $parameters = array(':player_id' => $playerId);
            $columnReturnTypes = array(
                'description' => 'str',
                'game_id' => 'int',
                'game_state' => 'int',
                'is_awaiting_action' => 'int',
                'last_action_timestamp' => 'int',
                'my_button_name' => 'str',
                'n_draws' => 'int',
                'n_losses' => 'int',
                'n_target_wins' => 'int',
                'n_wins' => 'int',
                'opponent_button_name' => 'str',
                'opponent_id' => 'int',
                'opponent_name' => 'str',
                'opponent_on_vacation' => 'bool',
                'status' => 'str',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);

            return self::read_game_list_from_db_results($playerId, $rows);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_all_games: ' .
                $e->getMessage()
            );
            $this->set_message('Game detail get failed.');
            return NULL;
        }
    }

    /**
     * Read game list from DB results
     *
     * @param int $playerId
     * @param array $rows
     * @return array
     */
    protected function read_game_list_from_db_results($playerId, $rows) {
        // Initialize the arrays
        $gameIdArray = array();
        $gameDescriptionArray = array();
        $opponentIdArray = array();
        $opponentNameArray = array();
        $isOpponentOnVacationArray = array();
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

        foreach ($rows as $row) {
            $gameColors = $this->determine_game_colors(
                $playerId,
                $playerColors,
                $playerId,
                $row['opponent_id']
            );

            $gameIdArray[]        = $row['game_id'];
            $gameDescriptionArray[] = $row['description'];
            $opponentIdArray[]    = $row['opponent_id'];
            $opponentNameArray[]  = $row['opponent_name'];
            $isOpponentOnVacationArray[]  = $row['opponent_on_vacation'];
            $myButtonNameArray[]  = $row['my_button_name'];
            $oppButtonNameArray[] = $row['opponent_button_name'];
            $nWinsArray[]         = $row['n_wins'];
            $nDrawsArray[]        = $row['n_draws'];
            $nLossesArray[]       = $row['n_losses'];
            $nTargetWinsArray[]   = $row['n_target_wins'];
            $isToActArray[]       = $row['is_awaiting_action'];
            $gameStateArray[]     = BMGameState::as_string($row['game_state']);
            $statusArray[]        = $row['status'];
            $inactivityArray[]    =
                $this->get_friendly_time_span($row['last_action_timestamp'], $now);
            $inactivityRawArray[] = $now - $row['last_action_timestamp'];
            $playerColorArray[]   = $gameColors['playerA'];
            $opponentColorArray[] = $gameColors['playerB'];
        }

        return array('gameIdArray'             => $gameIdArray,
                     'gameDescriptionArray'    => $gameDescriptionArray,
                     'opponentIdArray'         => $opponentIdArray,
                     'opponentNameArray'       => $opponentNameArray,
                     'isOpponentOnVacationArray' => $isOpponentOnVacationArray,
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

    /**
     * Get all new games
     *
     * @param int $playerId
     * @return array
     */
    public function get_all_new_games($playerId) {
        return $this->get_all_games($playerId, 'NEW');
    }

    /**
     * Get all active games
     *
     * @param int $playerId
     * @return array
     */
    public function get_all_active_games($playerId) {
        return $this->get_all_games($playerId, 'ACTIVE');
    }

    /**
     * Get all completed games
     *
     * @param int $playerId
     * @return array
     */
    public function get_all_completed_games($playerId) {
        return $this->get_all_games($playerId, 'COMPLETE');
    }

    /**
     * Get all cancelled games
     *
     * @param int $playerId
     * @return array
     */
    public function get_all_cancelled_games($playerId) {
        return $this->get_all_games($playerId, 'CANCELLED');
    }

    /**
     * Get all open games
     *
     * @param int $currentPlayerId
     * @return array
     */
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
                    'v_challenger.is_on_vacation AS is_challenger_on_vacation, ' .
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
            $parameters = array();
            $columnReturnTypes = array(
                'challenger_button' => 'str_or_null',
                'challenger_id' => 'int',
                'challenger_random' => 'bool',
                'challenger_name' => 'str',
                'description' => 'str',
                'game_id' => 'int',
                'is_challenger_on_vacation' => 'bool',
                'target_wins' => 'int',
                'victim_button' => 'str_or_null',
                'victim_random' => 'bool',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);

            $games = array();

            foreach ($rows as $row) {
                $gameColors = $this->determine_game_colors(
                    $currentPlayerId,
                    $playerColors,
                    -1, // There is no other player yet
                    $row['challenger_id']
                );

                if ($row['challenger_random'] == 1) {
                    $challengerButton = '__random';
                } else {
                    $challengerButton = $row['challenger_button'];
                }
                if ($row['victim_random'] == 1) {
                    $victimButton = '__random';
                } else {
                    $victimButton = $row['victim_button'];
                }

                $games[] = array(
                    'gameId' => $row['game_id'],
                    'challengerId' => $row['challenger_id'],
                    'challengerName' => $row['challenger_name'],
                    'isChallengerOnVacation' => $row['is_challenger_on_vacation'],
                    'challengerButton' => $challengerButton,
                    'challengerColor' => $gameColors['playerB'],
                    'victimButton' => $victimButton,
                    'targetWins' => $row['target_wins'],
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

    /**
     * Get next pending game
     *
     * @param int $playerId
     * @param array $skippedGames
     * @return array
     */
    public function get_next_pending_game($playerId, $skippedGames) {
        try {
            $parameters = array(':player_id' => $playerId);

            $query = 'SELECT gpm.game_id '.
                     'FROM game_player_map AS gpm '.
                        'LEFT JOIN game AS g ON g.id = gpm.game_id '.
                     'WHERE gpm.player_id = :player_id '.
                        'AND gpm.is_awaiting_action = 1 '.
                        'AND g.status_id IN '.
                           '(SELECT id FROM game_status WHERE name IN (\'NEW\', \'ACTIVE\'))';
            foreach ($skippedGames as $index => $skippedGameId) {
                $parameterName = ':skipped_game_id_' . $index;
                $query = $query . 'AND gpm.game_id <> ' . $parameterName . ' ';
                $parameters[$parameterName] = $skippedGameId;
            }
            $query = $query .
                     'ORDER BY g.last_action_time ASC '.
                     'LIMIT 1';

            try {
                $gameId = self::$db->select_single_value($query, $parameters, 'int');
                $this->set_message('Next game ID retrieved successfully.');
                return array('gameId' => $gameId);
            } catch (BMExceptionDatabase $e) {
                $this->set_message('Player has no pending games.');
                return array('gameId' => NULL);
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

    /**
     * Get active players
     *
     * @param int $numberOfPlayers
     * @return array
     */
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
            $parameters = array(':number_of_players' => $numberOfPlayers);
            $columnReturnTypes = array(
                'name_ingame' => 'str',
                'last_access_timestamp' => 'int',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);

            $now = strtotime('now');

            $players = array();
            foreach ($rows as $row) {
                $players[] = array(
                    'playerName' => $row['name_ingame'],
                    'idleness' =>
                        $this->get_friendly_time_span($row['last_access_timestamp'], $now),
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

    /**
     * Retrieves a list of buttons along with associated information, including
     * their names, recipes, special abilities, sets and TL status.
     *
     * If $buttonName is specified, it only returns that one button. It also
     * includes extra textual data (flavor text, special ability and skill
     * descriptions) that is otherwise omitted for efficiency.
     *
     * If $setName is specified, it only returns buttons in that set.
     *
     * If neither is specified, it returns all buttons.
     *
     * If $tagArray is specified, filter the buttons by tag. Each element in $tagArray
     * has the tag name as a key, and a boolean (indicating positive or negative filtering)
     * as the value.
     *
     * @param string $buttonName         Return a particular button
     * @param string $setName            Return buttons in a particular set
     * @param bool $forceImplemented     Do we only want buttons with fully implemented skills?
     * @param array $tagArray            Filter by tag (either positively or negatively)
     * @return NULL|array
     */
    public function get_button_data(
        $buttonName = NULL,
        $setName = NULL,
        $forceImplemented = FALSE,
        $tagArray = NULL
    ) {
        try {
            // if the site is production, don't report unimplemented buttons at all
            $site_type = $this->get_config('site_type');
            $single_button = ($buttonName !== NULL);
            $rows = $this->execute_button_data_query($buttonName, $setName, $tagArray);

            $buttons = array();
            foreach ($rows as $row) {
                $currentButton = $this->assemble_button_data(
                    $row,
                    $site_type,
                    $single_button,
                    $forceImplemented
                );
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

    /**
     * Execute button data query
     *
     * @param string $buttonName
     * @param string $setName
     * @param array $tagArray
     * @return array
     */
    protected function execute_button_data_query($buttonName, $setName, $tagArray) {
        $parameters = array();
        $query =
            'SELECT b.id, b.name, b.recipe, b.btn_special, b.tourn_legal, b.flavor_text, ' .
            '       s.name AS set_name ' .
            'FROM button AS b ' .
            'LEFT JOIN buttonset AS s ON b.set_id = s.id ';

        if (($buttonName !== NULL) || ($setName !== NULL) || ($tagArray !== NULL)) {
            $query .= 'WHERE TRUE ';
        }
        if ($buttonName !== NULL) {
            $query .= 'AND b.name = :button_name ';
            $parameters[':button_name'] = $buttonName;
        } elseif ($setName !== NULL) {
            $query .= 'AND s.name = :set_name ';
            $parameters[':set_name'] = $setName;
        }

        if ($tagArray !== NULL) {
            $tagIdx = 0;
            foreach ($tagArray as $tag => $isTagTrue) {
                $tagQuery = 'b.id ';
                if ('false' === $isTagTrue) {
                    $tagQuery .= 'NOT ';
                }
                $tagQuery .= 'IN (SELECT m.button_id FROM button_tag_map as m '.
                                 'WHERE m.tag_id = '.
                                   '(SELECT t.id FROM tag as t '.
                                    'WHERE t.name = :tag_name' . $tagIdx . ')) ';
                $tagQueryArray[] = $tagQuery;
                $parameters[':tag_name' . $tagIdx] = $tag;
                $tagIdx++;
            }
            $query .= 'AND ' . implode('AND ', $tagQueryArray);
        }

        $query .=
            'ORDER BY s.sort_order ASC, b.sort_order ASC, b.name ASC;';

        $columnReturnTypes = array(
            'btn_special' => 'bool',
            'flavor_text' => 'str_or_null',
            'id' => 'int',
            'name' => 'str',
            'recipe' => 'str',
            'set_name' => 'str',
            'tourn_legal' => 'bool',
        );
        return self::$db->select_rows($query, $parameters, $columnReturnTypes);
    }

    /**
     * Assemble button data
     *
     * @param array $row
     * @param string $site_type
     * @param bool $single_button
     * @param bool $forceImplemented
     * @return array
     */
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
        if ($row['btn_special'] &&
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
            'buttonId' => $row['id'],
            'buttonName' => $row['name'],
            'recipe' => $row['recipe'],
            'hasUnimplementedSkill' => $hasUnimplementedSkill,
            'buttonSet' => $row['set_name'],
            'dieTypes' => $dieTypes,
            'dieSkills' => $dieSkills,
            'isTournamentLegal' => $row['tourn_legal'],
            'artFilename' => $button->artFilename,
            'tags' => $this->get_button_tags($row['name']),
        );

        // For efficiency's sake, there exist some pieces of information
        // which we include only in the case where only one button was
        // requested.
        if ($single_button) {
            $currentButton['flavorText'] = $row['flavor_text'];
            $buttonSkillClass = 'BMBtnSkill' . $standardName;
            if ($row['btn_special'] && class_exists($buttonSkillClass)) {
                $currentButton['specialText'] = $buttonSkillClass::get_description();
            } else {
                $currentButton['specialText'] = NULL;
            }
        }
        return $currentButton;
    }

    /**
     * Get button tags
     *
     * @param string $buttonName
     * @return array
     */
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
            $parameters = array(':button_name' => $buttonName);
            $columnReturnTypes = array(
                'name' => 'str',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);

            foreach ($rows as $row) {
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

    /**
     * Retrieves a list of button sets along with associated information,
     * including their name.
     *
     * If $setName is specified, it only returns that one set. It also
     * includes the buttons in that set, which are otherwise omitted for
     * efficiency.
     *
     * If $setName is not specified, it returns all sets.
     *
     * @param string $setName
     * @return array
     */
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
            $columnReturnTypes = array(
                'name' => 'str',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);

            $sets = array();
            foreach ($rows as $row) {
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

    /**
     * Get button recipe corresponding to a button name
     *
     * @param string $name
     * @return string
     */
    protected function get_button_recipe_from_name($name) {
        try {
            $query = 'SELECT recipe FROM button_view '.
                     'WHERE name = :name';
            $parameters = array(':name' => $name);
            $recipe = self::$db->select_single_value($query, $parameters, 'str');
            return($recipe);
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_button_recipe_from_name: '
                . $e->getMessage()
            );
            $this->set_message('Button recipe get failed.');
        }
    }

    /**
     * Get player names like a certain string
     *
     * @param string $input
     * @return array
     */
    public function get_player_names_like($input = '') {
        try {
            $query = 'SELECT name_ingame, status FROM player_view '.
                     'WHERE name_ingame LIKE :input '.
                     'ORDER BY name_ingame';
            $parameters = array(':input' => $input.'%');
            $columnReturnTypes = array(
                'name_ingame' => 'str',
                'status' => 'str',
            );
            $rows = self::$db->select_rows($query, $parameters, $columnReturnTypes);

            $nameArray = array();
            $statusArray = array();
            foreach ($rows as $row) {
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

    /**
     * Get player ID corresponding to a player name
     *
     * @param string $name
     * @return int
     */
    public function get_player_id_from_name($name) {
        try {
            $query = 'SELECT id FROM player '.
                     'WHERE name_ingame = :input';
            $parameters = array(':input' => $name);
            $playerId = self::$db->select_single_value($query, $parameters, 'int');
            $this->set_message('Player ID retrieved successfully.');
            return($playerId);
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Player name does not exist.');
            return('');
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_player_id_from_name: ' .
                $e->getMessage()
            );
            $this->set_message('Player ID get failed.');
        }
    }

    /**
     * Get player name corresponding to a player ID
     *
     * @param int $playerId
     * @return string
     */
    protected function get_player_name_from_id($playerId) {
        try {
            if (empty($playerId)) {
                return('');
            }

            $query = 'SELECT name_ingame FROM player '.
                     'WHERE id = :id';
            $parameters = array(':id' => $playerId);
            return self::$db->select_single_value($query, $parameters, 'str');
        } catch (BMExceptionDatabase $e) {
            return('');
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_player_name_from_id: ' .
                $e->getMessage()
            );
            $this->set_message('Player name get failed.');
        }
    }

    /**
     * Get mapping between player ID and player name
     *
     * @param BMGame $game
     * @return array
     */
    protected function get_player_name_mapping($game) {
        $idNameMapping = array();
        foreach ($game->playerArray as $player) {
            $idNameMapping[$player->playerId] = $this->get_player_name_from_id($player->playerId);
        }

        // If the game creator is not one of the players, add their name to the mapping as well
        if (!array_key_exists($game->creatorId, $idNameMapping)) {
            $idNameMapping[$game->creatorId] = $this->get_player_name_from_id($game->creatorId);
        }
        return $idNameMapping;
    }

    /**
     * Get button ID corresponding to a button ID
     *
     * @param string $name
     * @return int
     */
    protected function get_button_id_from_name($name) {
        try {
            $query = 'SELECT id FROM button '.
                     'WHERE name = :input';
            $parameters = array(':input' => $name);
            $buttonId = self::$db->select_single_value($query, $parameters, 'int');
            $this->set_message('Button ID retrieved successfully.');
            return $buttonId;
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Button name does not exist.');
            return('');
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_button_id_from_name: " .
                $e->getMessage()
            );
            $this->set_message('Button ID get failed.');
        }
    }

    /**
     * Get button set ID corresponding to a button set name
     *
     * @param string $name
     * @return int
     */
    protected function get_buttonset_id_from_name($name) {
        try {
            $query = 'SELECT id FROM buttonset '.
                     'WHERE name = :input';
            $parameters = array(':input' => $name);
            return self::$db->select_single_value($query, $parameters, 'int');
        } catch (BMExceptionDatabase $e) {
            $this->set_message('Buttonset name does not exist.');
            return('');
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_buttonset_id_from_name: " .
                $e->getMessage()
            );
            $this->set_message('Buttonset ID get failed.');
        }
    }

    /**
     * Build the various different WHERE, ORDER BY and LIMIT clauses for the
     * different action and chat log SELECT queries
     *
     * @param BMGame $game
     * @param bool $doQueryPreviousGame
     * @param bool $isCount
     * @param array $sqlParameters
     * @return string
     */
    protected function build_game_log_query_restrictions(
        BMGame $game,
        $doQueryPreviousGame,
        $isCount,
        array &$sqlParameters
    ) {
        $restrictions = 'WHERE game_id = :game_id ';
        if ($doQueryPreviousGame) {
            $restrictions .= 'OR game_id = :previous_game_id ';
            $sqlParameters[':previous_game_id'] = $game->previousGameId;
        }
        if (!$isCount) {
            $restrictions .= 'ORDER BY id DESC ' ;
        }

        return $restrictions;
    }

    /**
     * Get value from config table for a certain key
     *
     * @param string $conf_key
     * @return string
     */
    protected function get_config($conf_key) {
        try {
            $query = 'SELECT conf_value FROM config WHERE conf_key = :conf_key';
            $parameters = array(':conf_key' => $conf_key);
            return self::$db->select_single_value($query, $parameters, 'str');
        } catch (BMExceptionDatabase $e) {
            error_log('Wrong number of config values with key ' . $conf_key);
            return NULL;
        } catch (Exception $e) {
            error_log(
                'Caught exception in BMInterface::get_config: ' .
                $e->getMessage()
            );
            return NULL;
        }
    }

    /**
     * Calculates the difference between two (unix-style) timespans and formats
     * the result as a friendly approximation like '7 days' or '12 minutes'.
     *
     * @param int $firstTime
     * @param int $secondTime
     * @return string
     */
    protected function get_friendly_time_span($firstTime, $secondTime) {
        $seconds = $secondTime - $firstTime;
        if ($seconds < 0) {
            $seconds *= -1;
        }

        if ($seconds < 60) {
            return $this->count_noun($seconds, 'second');
        }

        $minutes = intdiv($seconds, 60);
        if ($minutes < 60) {
            return $this->count_noun($minutes, 'minute');
        }

        $hours = intdiv($minutes, 60);
        if ($hours < 24) {
            return $this->count_noun($hours, 'hour');
        }

        $days = intdiv($hours, 24);
        return $this->count_noun($days, 'day');
    }

    /**
     * Turns a number (like 5) and a noun (like 'golden ring') into a phrase
     * like '5 golden rings', pluralizing if needed.
     * Note: does not handle funky plurals.
     *
     * @param int $count
     * @param string $noun
     * @return string
     */
    protected function count_noun($count, $noun) {
        if ($count == 1) {
            return $count . ' ' . $noun;
        }
        if (substr($noun, -1) == 's') {
            return $count . ' ' . $noun . 'es';
        }
        return $count . ' ' . $noun . 's';
    }

    /**
     * Retrieves the die background type chosen by the current player
     *
     * @param int $playerId
     * @return string
     */
    protected function load_die_background_type($playerId) {
        $playerInfoArray = $this->player()->get_player_info($playerId);

        return $playerInfoArray['user_prefs']['die_background'];
    }

    /**
     * Retrieves the colors that the user has saved in their preferences
     *
     * @param int $currentPlayerId
     * @return array
     */
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

    /**
     * Determines which colors to use for the two players in a game.
     * $currentPlayerId is the player this is being displayed to.
     * $playerColors are the colors they've chosen as their preferences
     * (as returned by load_player_colors())
     * $gamePlayerIdA and $gamePlayerIdB are the two players in the game
     *
     * @param int $currentPlayerId
     * @param array $playerColors
     * @param int $gamePlayerIdA
     * @param int $gamePlayerIdB
     * @return array
     */
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

    /**
     * Takes a URL that was entered by a user and returns a version of it that's
     * safe to insert into an anchor tag (or returns NULL if we can't sensibly do
     * that).
     * Based in part on advice from http://stackoverflow.com/questions/205923
     *
     * @param string $url
     * @return string
     */
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

    /**
     * Set message
     *
     * @param string $message
     */
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

    /**
     * Define behaviour of isset()
     *
     * @param string $property
     * @return bool
     */
    public function __isset($property) {
        return isset($this->$property);
    }
}
