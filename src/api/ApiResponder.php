<?php
/**
 * ApiResponder: defines how UI arguments are passed to BMInterface
 *
 * @author chaos
 */

/**
 * This class specifies the link between the public API functions and
 * BMInterface
 *
 * @SuppressWarnings(PMD.BooleanGetMethodName)
 */
class ApiResponder {

    // properties
    /**
     * whether this invocation is for testing
     *
     * @var bool
     */
    protected $isTest;

    /**
     * functions which allow access by unauthenticated users
     * For now, all game functionality should require login: only
     * add things to this list if they are necessary for user
     * creation and/or login.
     *
     * @var array
     */
    protected $unauthFunctions = array(
        'createUser',
        'verifyUser',
        'loadPlayerName',
        'login',
    );

    /**
     * Constructor
     * For live invocation:
     *   start a session (and require api_core to get session functions)
     * For test invocation:
     *   don't start a session
     *
     * @param ApiSpec $spec
     * @param bool $isTest
     */
    public function __construct(ApiSpec $spec, $isTest = FALSE) {
        $this->spec = $spec;
        $this->isTest = $isTest;

        if (!($this->isTest)) {
            session_start();
            require_once 'api_core.php';
            require_once('../lib/bootstrap.php');
        }
    }

    /**
     * Interface redirect for createUser
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_createUser($interface, $args) {
        return $interface->create_user($args['username'], $args['password'], $args['email']);
    }

    /**
     * Interface redirect for verifyUser
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|TRUE
     */
    protected function get_interface_response_verifyUser($interface, $args) {
        return $interface->verify_user($args['playerId'], $args['playerKey']);
    }

    /**
     * Interface redirect for createGame
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_createGame($interface, $args) {
        // $args['playerInfoArray'] contains an array of arrays, with one
        // subarray for each player/button combination,
        //   e.g., [0 => ['playerName1', 'buttonName1'],
        //          1 => ['playerName2', NULL]]
        $playerIdArray = array();
        $buttonNameArray = array();
        foreach ($args['playerInfoArray'] as $playerIdx => $playerInfo) {
            $playerId = '';
            if (isset($playerInfo[0])) {
                $playerId = $interface->get_player_id_from_name($playerInfo[0]);
            }
            if (is_int($playerId)) {
                $playerIdArray[$playerIdx] = $playerId;
            } else {
                $playerIdArray[$playerIdx] = NULL;
            }

            if (isset($playerInfo[1])) {
                $buttonNameArray[$playerIdx] = $playerInfo[1];
            } else {
                $buttonNameArray[$playerIdx] = NULL;
            }
        }

        $maxWins = $args['maxWins'];

        if (isset($args['description'])) {
            $description = $args['description'];
        } else {
            $description = '';
        }
        if (isset($args['previousGameId'])) {
            $previousGameId = $args['previousGameId'];
        } else {
            $previousGameId = NULL;
        }

        $retval = $interface->game()->create_game(
            $playerIdArray,
            $buttonNameArray,
            $maxWins,
            $description,
            $previousGameId,
            (int)$_SESSION['user_id'],
            FALSE
        );

        if (isset($retval)) {
            foreach ($playerIdArray as $playerId) {
                if (isset($playerId)) {
                    $interface->player()->update_last_action_time($playerId, $retval['gameId']);
                }
            }
        }

        return $retval;
    }

    /**
     * Interface redirect for searchGameHistory
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_searchGameHistory($interface, $args) {
        return $interface->history()->search_game_history($_SESSION['user_id'], $args);
    }

    /**
     * Interface redirect for joinOpenGame
     *
     * @param BMInterface $interface
     * @param array $args
     * @return bool
     */
    protected function get_interface_response_joinOpenGame($interface, $args) {
        $success = $interface->game()->join_open_game(
            $_SESSION['user_id'],
            $args['gameId']
        );
        if ($success && isset($args['buttonName'])) {
            $success = $interface->game()->select_button(
                $_SESSION['user_id'],
                (int)$args['gameId'],
                $args['buttonName']
            );
        }
        return $success;
    }

    /**
     * Interface redirect for selectButton
     *
     * @param BMInterface $interface
     * @param array $args
     * @return bool
     */
    protected function get_interface_response_selectButton($interface, $args) {
        return $interface->game()->select_button(
            $_SESSION['user_id'],
            (int)$args['gameId'],
            $args['buttonName']
        );
    }

    /**
     * Interface redirect for loadOpenGames
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadOpenGames($interface) {
        return $interface->get_all_open_games($_SESSION['user_id']);
    }

    /**
     * Interface redirect for loadNewGames
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadNewGames($interface) {
        return $interface->get_all_new_games($_SESSION['user_id']);
    }

    /**
     * Interface redirect for loadActiveGames
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadActiveGames($interface) {
        // Once we return to the list of active games, we no longer need to remember
        // which ones we were skipping.
        unset($_SESSION['skipped_games']);

        return $interface->get_all_active_games($_SESSION['user_id']);
    }

    /**
     * Interface redirect for loadCompletedGames
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadCompletedGames($interface) {
        return $interface->get_all_completed_games($_SESSION['user_id']);
    }

    /**
     * Interface redirect for loadCancelledGames
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadCancelledGames($interface) {
        return $interface->get_all_cancelled_games($_SESSION['user_id']);
    }

    /**
     * Interface redirect for loadNextPendingGame
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadNextPendingGame($interface, $args) {
        if (isset($args['currentGameId'])) {
            if (isset($_SESSION['skipped_games'])) {
                $_SESSION['skipped_games'] =
                    $_SESSION['skipped_games'] . ',' . $args['currentGameId'];
            } else {
                $_SESSION['skipped_games'] = $args['currentGameId'];
            }
        }

        $skippedGames = array();
        if (isset($_SESSION['skipped_games'])) {
            foreach (explode(',', $_SESSION['skipped_games']) as $gameId) {
                $skippedGames[] = (int)$gameId;
            }
        }

        return $interface->get_next_pending_game($_SESSION['user_id'], $skippedGames);
    }

    /**
     * Interface redirect for loadActivePlayers
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadActivePlayers($interface, $args) {
        return $interface->get_active_players((int)$args['numberOfPlayers']);
    }

    /**
     * Interface redirect for loadButtonData
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadButtonData($interface, $args) {
        $buttonName = NULL;
        $buttonSet = NULL;
        $forceImplemented = FALSE;
        $tagArray = NULL;

        if (isset($args['buttonName'])) {
            $buttonName = $args['buttonName'];
        }
        if (isset($args['buttonSet'])) {
            $buttonSet = $args['buttonSet'];
        }
        if (isset($args['forceImplemented'])) {
            $forceImplemented = ($args['forceImplemented'] === 'true');
        }
        if (isset($args['tagArray'])) {
            $tagArray = $args['tagArray'];
        }
        return $interface->get_button_data($buttonName, $buttonSet, $forceImplemented, $tagArray);
    }

    /**
     * Interface redirect for loadButtonSetData
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadButtonSetData($interface, $args) {
        if (isset($args['buttonSet'])) {
            $buttonSet = $args['buttonSet'];
        } else {
            $buttonSet = NULL;
        }
        return $interface->get_button_set_data($buttonSet);
    }

    /**
     * Interface redirect for loadGameData
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadGameData($interface, $args) {
        if (isset($args['logEntryLimit'])) {
            $logEntryLimit = $args['logEntryLimit'];
        } else {
            $logEntryLimit = NULL;
        }
        return $interface->load_api_game_data($_SESSION['user_id'], $args['game'], $logEntryLimit);
    }

    /**
     * Interface redirect for countPendingGames
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_countPendingGames($interface) {
        return $interface->count_pending_games($_SESSION['user_id']);
    }

    /**
     * Interface redirect for loadPlayerName
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadPlayerName() {
        if (auth_session_exists()) {
            return array('userName' => $_SESSION['user_name']);
        } else {
            return NULL;
        }
    }

    /**
     * Interface redirect for loadPlayerInfo
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadPlayerInfo($interface) {
        $result = $interface->player()->get_player_info($_SESSION['user_id']);
        return $result;
    }

    /**
     * Interface redirect for savePlayerInfo
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|FALSE|array
     */
    protected function get_interface_response_savePlayerInfo($interface, $args) {
        $infoArray = array();
        $infoArray['name_irl'] = $args['name_irl'];
        $infoArray['is_email_public'] = ('true' == $args['is_email_public']);
        $infoArray['dob_month'] = (int)$args['dob_month'];
        $infoArray['dob_day'] = (int)$args['dob_day'];
        $infoArray['comment'] = $args['comment'];
        $infoArray['vacation_message'] = $args['vacation_message'];
        $infoArray['gender'] = $args['gender'];
        $infoArray['autoaccept'] = ('true' == $args['autoaccept']);
        $infoArray['autopass'] = ('true' == $args['autopass']);
        $infoArray['fire_overshooting'] = ('true' == $args['fire_overshooting']);
        $infoArray['monitor_redirects_to_game'] = ('true' == $args['monitor_redirects_to_game']);
        $infoArray['monitor_redirects_to_forum'] = ('true' == $args['monitor_redirects_to_forum']);
        $infoArray['automatically_monitor'] = ('true' == $args['automatically_monitor']);
        $infoArray['die_background'] = $args['die_background'];
        $infoArray['player_color'] = $args['player_color'];
        $infoArray['opponent_color'] = $args['opponent_color'];
        $infoArray['neutral_color_a'] = $args['neutral_color_a'];
        $infoArray['neutral_color_b'] = $args['neutral_color_b'];
        if (isset($args['image_size'])) {
            $infoArray['image_size'] = $args['image_size'];
        } else {
            $infoArray['image_size'] = NULL;
        }
        $infoArray['uses_gravatar'] = ('true' == $args['uses_gravatar']);

        $addlInfo = array();
        $addlInfo['dob_month'] = (int)$args['dob_month'];
        $addlInfo['dob_day'] = (int)$args['dob_day'];
        $addlInfo['homepage'] = $args['homepage'];

        if (isset($args['favorite_button'])) {
            $addlInfo['favorite_button'] = $args['favorite_button'];
        }
        if (isset($args['favorite_buttonset'])) {
            $addlInfo['favorite_buttonset'] = $args['favorite_buttonset'];
        }
        if (isset($args['current_password'])) {
            $addlInfo['current_password'] = $args['current_password'];
        }
        if (isset($args['new_password'])) {
            $addlInfo['new_password'] = $args['new_password'];
        }
        if (isset($args['new_email'])) {
            $addlInfo['new_email'] = $args['new_email'];
        }

        $retval = $interface->player()->set_player_info(
            $_SESSION['user_id'],
            $infoArray,
            $addlInfo
        );

        if (isset($retval)) {
            $interface->player()->update_last_action_time($_SESSION['user_id']);
        }

        return $retval;
    }

    /**
     * Interface redirect for loadProfileInfo
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadProfileInfo(&$interface, $args) {
        $result = $interface->player()->get_profile_info($args['playerName']);
        return $result;
    }

    /**
     * Interface redirect for loadPlayerNames
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadPlayerNames($interface) {
        return $interface->get_player_names_like('');
    }

    /**
     * Interface redirect for submitDieValues
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_submitDieValues($interface, $args) {
        if (array_key_exists('swingValueArray', $args)) {
            $swingValueArray = $args['swingValueArray'];
        } else {
            $swingValueArray = array();
        }
        if (array_key_exists('optionValueArray', $args)) {
            $optionValueArray = $args['optionValueArray'];
        } else {
            $optionValueArray = array();
        }
        $retval = $interface->game()->submit_die_values(
            $_SESSION['user_id'],
            $args['game'],
            $args['roundNumber'],
            $swingValueArray,
            $optionValueArray
        );

        if (isset($retval)) {
            $interface->player()->update_last_action_time($_SESSION['user_id'], $args['game']);
        }

        return $retval;
    }

    /**
     * Interface redirect for reactToAuxiliary
     *
     * @param BMInterface $interface
     * @param array $args
     * @return bool
     */
    protected function get_interface_response_reactToAuxiliary($interface, $args) {
        if (!(array_key_exists('dieIdx', $args))) {
            $args['dieIdx'] = NULL;
        }

        $retval = $interface->game()->react_to_auxiliary(
            $_SESSION['user_id'],
            $args['game'],
            $args['action'],
            $args['dieIdx']
        );

        if ($retval) {
            $interface->player()->update_last_action_time($_SESSION['user_id'], $args['game']);
        }

        return $retval;
    }

    /**
     * Interface redirect for reactToReserve
     *
     * @param BMInterface $interface
     * @param array $args
     * @return bool
     */
    protected function get_interface_response_reactToReserve($interface, $args) {
        if (!(array_key_exists('dieIdx', $args))) {
            $args['dieIdx'] = NULL;
        }

        $retval = $interface->game()->react_to_reserve(
            $_SESSION['user_id'],
            $args['game'],
            $args['action'],
            $args['dieIdx']
        );

        if ($retval) {
            $interface->player()->update_last_action_time($_SESSION['user_id'], $args['game']);
        }

        return $retval;
    }

    /**
     * Interface redirect for reactToInitiative
     *
     * @param BMInterface $interface
     * @param array $args
     * @return bool
     */
    protected function get_interface_response_reactToInitiative($interface, $args) {
        if (!(array_key_exists('dieIdxArray', $args))) {
            $args['dieIdxArray'] = NULL;
        }
        if (!(array_key_exists('dieValueArray', $args))) {
            $args['dieValueArray'] = NULL;
        }
        $retval = $interface->game()->react_to_initiative(
            $_SESSION['user_id'],
            $args['game'],
            $args['roundNumber'],
            $args['timestamp'],
            $args['action'],
            $args['dieIdxArray'],
            $args['dieValueArray']
        );

        if ($retval) {
            $interface->player()->update_last_action_time($_SESSION['user_id'], $args['game']);
        }

        return $retval;
    }

    /**
     * Interface redirect for adjustFire
     *
     * @param BMInterface $interface
     * @param array $args
     * @return bool
     */
    protected function get_interface_response_adjustFire($interface, $args) {
        if (!(array_key_exists('dieIdxArray', $args))) {
            $args['dieIdxArray'] = NULL;
        }
        if (!(array_key_exists('dieValueArray', $args))) {
            $args['dieValueArray'] = NULL;
        }
        $retval = $interface->game()->adjust_fire(
            $_SESSION['user_id'],
            $args['game'],
            $args['roundNumber'],
            $args['timestamp'],
            $args['action'],
            $args['dieIdxArray'],
            $args['dieValueArray']
        );

        if ($retval) {
            $interface->player()->update_last_action_time($_SESSION['user_id'], $args['game']);
        }

        return $retval;
    }

    /**
     * Interface redirect for submitChat
     *
     * @param BMInterface $interface
     * @param array $args
     * @return bool
     */
    protected function get_interface_response_submitChat($interface, $args) {
        if (!(array_key_exists('edit', $args))) {
            $args['edit'] = FALSE;
        }
        $retval = $interface->game_chat()->submit_chat(
            $_SESSION['user_id'],
            $args['game'],
            $args['edit'],
            $args['chat']
        );

        if ($retval) {
            $interface->player()->update_last_action_time($_SESSION['user_id'], $args['game']);
        }

        return $retval;
    }

    /**
     * Interface redirect for submitTurn
     *
     * @param BMInterface $interface
     * @param array $args
     * @return bool
     */
    protected function get_interface_response_submitTurn($interface, $args) {
        if (!(array_key_exists('chat', $args))) {
            $args['chat'] = '';
        }
        $retval = $interface->game()->submit_turn(
            $_SESSION['user_id'],
            $args['game'],
            $args['roundNumber'],
            $args['timestamp'],
            $args['dieSelectStatus'],
            $args['attackType'],
            (int)$args['attackerIdx'],
            (int)$args['defenderIdx'],
            $args['chat']
        );

        if (isset($retval)) {
            $interface->player()->update_last_action_time($_SESSION['user_id'], $args['game']);
        }

        return $retval;
    }

    /**
     * Interface redirect for reactToNewGame
     *
     * @param BMInterface $interface
     * @param array $args
     * @return bool
     */
    protected function get_interface_response_reactToNewGame($interface, $args) {
        $retval = $interface->game()->save_join_game_decision(
            $_SESSION['user_id'],
            $args['gameId'],
            $args['action']
        );

        if (isset($retval)) {
            $interface->player()->update_last_action_time($_SESSION['user_id'], $args['gameId']);
        }

        return $retval;
    }

    /**
     * Interface redirect for dismissGame
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|bool
     */
    protected function get_interface_response_dismissGame($interface, $args) {
        $retval = $interface->game()->dismiss_game($_SESSION['user_id'], $args['gameId']);
        if (isset($retval)) {
            // Just update the player's last action time. Don't update the
            // game's, since the game is already over.
            $interface->player()->update_last_action_time($_SESSION['user_id']);
        }
        return $retval;
    }

    ////////////////////////////////////////////////////////////
    // Forum-related methods

    /**
     * Interface redirect for loadForumOverview
     *
     * @param BMInterface $interface
     * @return NULL|array
     */
    protected function get_interface_response_loadForumOverview($interface) {
        return $interface->forum()->load_forum_overview($_SESSION['user_id']);
    }

    /**
     * Interface redirect for loadForumBoard
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadForumBoard($interface, $args) {
        return $interface->forum()->load_forum_board($_SESSION['user_id'], (int)$args['boardId']);
    }

    /**
     * Interface redirect for loadForumThread
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadForumThread($interface, $args) {
        if (isset($args['currentPostId'])) {
            $currentPostId = (int)$args['currentPostId'];
        } else {
            $currentPostId = NULL;
        }
        return $interface->forum()->load_forum_thread(
            $_SESSION['user_id'],
            (int)$args['threadId'],
            $currentPostId
        );
    }

    /**
     * Interface redirect for loadNextNewPost
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_loadNextNewPost($interface) {
        return $interface->forum()->get_next_new_post($_SESSION['user_id']);
    }

    /**
     * Interface redirect for markForumRead
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_markForumRead($interface, $args) {
        return $interface->forum()->mark_forum_read(
            $_SESSION['user_id'],
            (int)$args['timestamp']
        );
    }

    /**
     * Interface redirect for markForumBoardRead
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_markForumBoardRead($interface, $args) {
        return $interface->forum()->mark_forum_board_read(
            $_SESSION['user_id'],
            (int)$args['boardId'],
            (int)$args['timestamp']
        );
    }

    /**
     * Interface redirect for markForumTthreadRead
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_markForumThreadRead($interface, $args) {
        return $interface->forum()->mark_forum_thread_read(
            $_SESSION['user_id'],
            (int)$args['threadId'],
            (int)$args['boardId'],
            (int)$args['timestamp']
        );
    }

    /**
     * Interface redirect for createForumThread
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_createForumThread($interface, $args) {
        return $interface->forum()->create_forum_thread(
            $_SESSION['user_id'],
            (int)$args['boardId'],
            $args['title'],
            $args['body']
        );
    }

    /**
     * Interface redirect for create_forum_post
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_createForumPost($interface, $args) {
        return $interface->forum()->create_forum_post(
            $_SESSION['user_id'],
            (int)$args['threadId'],
            $args['body']
        );
    }

    /**
     * Interface redirect for editForumPost
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_editForumPost($interface, $args) {
        return $interface->forum()->edit_forum_post(
            $_SESSION['user_id'],
            (int)$args['postId'],
            $args['body']
        );
    }

    // End of Forum-related methods
    ////////////////////////////////////////////////////////////

    /**
     * Interface redirect for response_logic
     *
     * @param BMInterface $interface
     * @param array $args
     * @return NULL|array
     */
    protected function get_interface_response_login($interface, $args) {
        assert(!is_array($interface));

        $doStayLoggedIn = isset($args['doStayLoggedIn']) &&
                          ('true' == $args['doStayLoggedIn']);
        $login_success = login($args['username'], $args['password'], $doStayLoggedIn);

        if ($login_success) {
            return array('userName' => $args['username']);
        } else {
            return NULL;
        }
    }

    /**
     * Interface redirect for response_logout()
     *
     * @return NULL|array
     */
    protected function get_interface_response_logout() {
        logout();
        return array('userName' => FALSE);
    }

    /**
     * Construct an interface, ask it for the response to the
     * request, then construct a response
     * - For live invocation:
     *   - display the output to the user
     * - For test invocation:
     *   - return the output as a PHP variable
     *
     * @param array $args
     * @return string
     */
    public function process_request($args) {
        $check = $this->verify_function_access($args);
        if ($check['ok']) {

            // now make sure all arguments passed to the function
            // are syntactically reasonable
            $argcheck = $this->spec->verify_function_args($args);
            if ($argcheck['ok']) {
                // As far as we can easily tell, it's safe to call
                // the function.  Go ahead and create an interface
                // object, invoke the function, and return the result
                $interface = $this->create_interface($args, $check);
                apache_note('BMAPIMethod', $args['type']);
                $data = $this->$check['funcname']($interface, $args);

                $output = array(
                    'data' => $data,
                    'message' => $interface->message,
                );
                if ($data) {
                    $output['status'] = 'ok';
                } else {
                    $output['status'] = 'failed';
                }
            } else {

                // found a problem with the args, report that
                $output = array(
                    'data' => NULL,
                    'status' => 'failed',
                    'message' => $argcheck['message'],
                );
            }
        } else {

            // found a problem with access to the function, report that
            $output = array(
                'data' => NULL,
                'status' => 'failed',
                'message' => $check['message'],
            );
        }
        apache_note('BMAPIStatus', $output['status']);

        if ($this->isTest) {
            return $output;
        } else {
            header('Content-Type: application/json');
            echo json_encode($output);
        }
    }

    /**
     * This function looks at the provided arguments and verifies
     * both that an appropriate interface routine exists and that
     * the requester has sufficient credentials to access it
     *
     * @param array $args
     * @return string
     */
    protected function verify_function_access($args) {
        if (array_key_exists('type', $args)) {
            $funcname = 'get_interface_response_' . $args['type'];
            if (method_exists($this, $funcname)) {
                if (in_array($args['type'], $this->unauthFunctions)) {
                    $result = array(
                        'ok' => TRUE,
                        'functype' => 'newuser',
                        'funcname' => $funcname,
                    );
                } elseif (auth_session_exists()) {
                    $result = array(
                        'ok' => TRUE,
                        'functype' => 'auth',
                        'funcname' => $funcname,
                    );
                } else {
                    $result = array(
                        'ok' => FALSE,
                        'message' => "You need to login before calling API function " . $args['type'],
                    );
                }
            } else {
                $result = array(
                    'ok' => FALSE,
                    'message' => 'Specified API function does not exist',
                );
            }
        } else {
            $result = array(
                'ok' => FALSE,
                'message' => 'No "type" argument specified',
            );
        }
        return $result;
    }

    /**
     * Create an interface object
     *
     * @param array $args
     * @param array $check
     * @return \BMInterfaceNewuser|\BMInterface
     */
    protected function create_interface($args, $check) {
        if ($check['functype'] != 'auth') {
            return new BMInterfaceNewuser($this->isTest);
        }

        apache_note('BMUserID', $_SESSION['user_id']);

        $interface = new BMInterface($this->isTest);

        if (!isset($args['automatedApiCall']) || $args['automatedApiCall'] != 'true') {
            $interface->player()->update_last_access_time($_SESSION['user_id']);
        }

        return $interface;
    }
}

// This function exists when we're running under apache, but not when we're
// running PHP unit tests, so we need to fake so things don't fail miserably.
if (!function_exists('apache_note')) {
    /**
     * Mock the PHP function apache_note when running PHP unit tests
     *
     * @param string $note_name
     * @param mixed $note_value
     * @return mixed
     */
    function apache_note($note_name, $note_value) {
        if (strpos($note_name, 'BM') !== 0) {
            throw new Exception('Note name should be prefixed with "BM"');
        }
        return $note_value;
    }
}
