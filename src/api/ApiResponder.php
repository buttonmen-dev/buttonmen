<?php

class ApiResponder {

    // properties
    private $isTest;               // whether this invocation is for testing

    // functions which allow access by unauthenticated users
    // For now, all game functionality should require login: only
    // add things to this list if they are necessary for user
    // creation and/or login.
    private $unauthFunctions = array(
        'createUser',
        'verifyUser',
        'loadPlayerName',
        'login',
    );

    // constructor
    // * For live invocation:
    //   * start a session (and require api_core to get session functions)
    // * For test invocation:
    //   * don't start a session
    public function __construct(ApiSpec $spec, $isTest = FALSE) {
        $this->spec = $spec;
        $this->isTest = $isTest;

        if (!($this->isTest)) {
            session_start();
            require_once 'api_core.php';
            require_once('../lib/bootstrap.php');
        }
    }

    // This function looks at the provided arguments and verifies
    // both that an appropriate interface routine exists and that
    // the requester has sufficient credentials to access it
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

    protected function get_interface_response_createUser($interface, $args) {
        return $interface->create_user($args['username'], $args['password'], $args['email']);
    }

    protected function get_interface_response_verifyUser($interface, $args) {
        return $interface->verify_user($args['playerId'], $args['playerKey']);
    }

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

        $retval = $interface->create_game(
            $playerIdArray,
            $buttonNameArray,
            $maxWins,
            (int)$_SESSION['user_id']
        );

        if (isset($retval)) {
            foreach ($playerIdArray as $playerId) {
                if (isset($playerId)) {
                    $interface->update_last_action_time($playerId, $retval['gameId']);
                }
            }
        }

        return $retval;
    }

    protected function get_interface_response_searchGameHistory($interface, $args) {
        return $interface->search_game_history($_SESSION['user_id'], $args);
    }

    protected function get_interface_response_joinOpenGame($interface, $args) {
        $success = $interface->join_open_game($_SESSION['user_id'], $args['gameId']);
        if ($success && isset($args['buttonName'])) {
            $success = $interface->select_button(
                $_SESSION['user_id'],
                (int)$args['gameId'],
                $args['buttonName']
            );
        }
        return $success;
    }

    protected function get_interface_response_selectButton($interface, $args) {
        return $interface->select_button(
            $_SESSION['user_id'],
            (int)$args['gameId'],
            $args['buttonName']
        );
    }

    protected function get_interface_response_loadOpenGames($interface) {
        return $interface->get_all_open_games($_SESSION['user_id']);
    }

    protected function get_interface_response_loadActiveGames($interface) {
        // Once we return to the list of active games, we no longer need to remember
        // which ones we were skipping.
        unset($_SESSION['skipped_games']);

        return $interface->get_all_active_games($_SESSION['user_id']);
    }

    protected function get_interface_response_loadCompletedGames($interface) {
        return $interface->get_all_completed_games($_SESSION['user_id']);
    }

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

    protected function get_interface_response_loadActivePlayers($interface, $args) {
        return $interface->get_active_players((int)$args['numberOfPlayers']);
    }

    protected function get_interface_response_loadButtonNames($interface) {
        return $interface->get_all_button_names();
    }

    protected function get_interface_response_loadGameData($interface, $args) {
        if (isset($args['logEntryLimit'])) {
            $logEntryLimit = $args['logEntryLimit'];
        } else {
            $logEntryLimit = NULL;
        }
        return $interface->load_api_game_data($_SESSION['user_id'], $args['game'], $logEntryLimit);
    }

    protected function get_interface_response_loadPlayerName() {
        if (auth_session_exists()) {
            return array('userName' => $_SESSION['user_name']);
        } else {
            return NULL;
        }
    }

    protected function get_interface_response_loadPlayerInfo($interface) {
        return $interface->get_player_info($_SESSION['user_id']);
    }

    protected function get_interface_response_savePlayerInfo($interface, $args) {
        $infoArray = array();
        $infoArray['name_irl'] = $args['name_irl'];
        $infoArray['comment'] = $args['comment'];
        $infoArray['autopass'] = ('true' == $args['autopass']);
        $infoArray['monitorRedirectsToGame'] = ('true' == $args['monitorRedirectsToGame']);
        $infoArray['monitorRedirectsToForum'] = ('true' == $args['monitorRedirectsToForum']);

        $addlInfo = array();
        $addlInfo['dob_month'] = (int)$args['dob_month'];
        $addlInfo['dob_day'] = (int)$args['dob_day'];
        if (isset($args['current_password'])) {
            $addlInfo['current_password'] = $args['current_password'];
        }
        if (isset($args['new_password'])) {
            $addlInfo['new_password'] = $args['new_password'];
        }
        if (isset($args['new_email'])) {
            $addlInfo['new_email'] = $args['new_email'];
        }

        $retval = $interface->set_player_info(
            $_SESSION['user_id'],
            $infoArray,
            $addlInfo
        );

        if (isset($retval)) {
            $interface->update_last_action_time($_SESSION['user_id']);
        }

        return $retval;
    }

    protected function get_interface_response_loadProfileInfo($interface, $args) {
        return $interface->get_profile_info($args['playerName']);
    }

    protected function get_interface_response_loadPlayerNames($interface) {
        return $interface->get_player_names_like('');
    }

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
        $retval = $interface->submit_die_values(
            $_SESSION['user_id'],
            $args['game'],
            $args['roundNumber'],
            $swingValueArray,
            $optionValueArray
        );

        if (isset($retval)) {
            $interface->update_last_action_time($_SESSION['user_id'], $args['game']);
        }

        return $retval;
    }

    protected function get_interface_response_reactToAuxiliary($interface, $args) {
        if (!(array_key_exists('dieIdx', $args))) {
            $args['dieIdx'] = NULL;
        }

        $retval = $interface->react_to_auxiliary(
            $_SESSION['user_id'],
            $args['game'],
            $args['action'],
            $args['dieIdx']
        );

        if ($retval) {
            $interface->update_last_action_time($_SESSION['user_id'], $args['game']);
        }

        return $retval;
    }

    protected function get_interface_response_reactToReserve($interface, $args) {
        if (!(array_key_exists('dieIdx', $args))) {
            $args['dieIdx'] = NULL;
        }

        $retval = $interface->react_to_reserve(
            $_SESSION['user_id'],
            $args['game'],
            $args['action'],
            $args['dieIdx']
        );

        if ($retval) {
            $interface->update_last_action_time($_SESSION['user_id'], $args['game']);
        }

        return $retval;
    }

    protected function get_interface_response_reactToInitiative($interface, $args) {
        if (!(array_key_exists('dieIdxArray', $args))) {
            $args['dieIdxArray'] = NULL;
        }
        if (!(array_key_exists('dieValueArray', $args))) {
            $args['dieValueArray'] = NULL;
        }
        $retval = $interface->react_to_initiative(
            $_SESSION['user_id'],
            $args['game'],
            $args['roundNumber'],
            $args['timestamp'],
            $args['action'],
            $args['dieIdxArray'],
            $args['dieValueArray']
        );

        if ($retval) {
            $interface->update_last_action_time($_SESSION['user_id'], $args['game']);
        }

        return $retval;
    }

    protected function get_interface_response_submitChat($interface, $args) {
        if (!(array_key_exists('edit', $args))) {
            $args['edit'] = FALSE;
        }
        $retval = $interface->submit_chat(
            $_SESSION['user_id'],
            $args['game'],
            $args['edit'],
            $args['chat']
        );

        if ($retval) {
            $interface->update_last_action_time($_SESSION['user_id'], $args['game']);
        }

        return $retval;
    }

    protected function get_interface_response_submitTurn($interface, $args) {
        if (!(array_key_exists('chat', $args))) {
            $args['chat'] = '';
        }
        $retval = $interface->submit_turn(
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
            $interface->update_last_action_time($_SESSION['user_id'], $args['game']);
        }

        return $retval;
    }

    protected function get_interface_response_dismissGame($interface, $args) {
        $retval = $interface->dismiss_game($_SESSION['user_id'], $args['gameId']);
        if (isset($retval)) {
            // Just update the player's last action time. Don't update the
            // game's, since the game is already over.
            $interface->update_last_action_time($_SESSION['user_id']);
        }
        return $retval;
    }

    ////////////////////////////////////////////////////////////
    // Forum-related methods

    protected function get_interface_response_loadForumOverview($interface) {
        return $interface->load_forum_overview($_SESSION['user_id']);
    }

    protected function get_interface_response_loadForumBoard($interface, $args) {
        return $interface->load_forum_board($_SESSION['user_id'], (int)$args['boardId']);
    }

    protected function get_interface_response_loadForumThread($interface, $args) {
        if (isset($args['currentPostId'])) {
            $currentPostId = (int)$args['currentPostId'];
        } else {
            $currentPostId = NULL;
        }
        return $interface->load_forum_thread(
            $_SESSION['user_id'],
            (int)$args['threadId'],
            $currentPostId
        );
    }

    protected function get_interface_response_markForumRead($interface, $args) {
        return $interface->mark_forum_read(
            $_SESSION['user_id'],
            (int)$args['timestamp']
        );
    }

    protected function get_interface_response_markForumBoardRead($interface, $args) {
        return $interface->mark_forum_board_read(
            $_SESSION['user_id'],
            (int)$args['boardId'],
            (int)$args['timestamp']
        );
    }

    protected function get_interface_response_markForumThreadRead($interface, $args) {
        return $interface->mark_forum_thread_read(
            $_SESSION['user_id'],
            (int)$args['threadId'],
            (int)$args['boardId'],
            (int)$args['timestamp']
        );
    }

    protected function get_interface_response_createForumThread($interface, $args) {
        return $interface->create_forum_thread(
            $_SESSION['user_id'],
            (int)$args['boardId'],
            $args['title'],
            $args['body']
        );
    }

    protected function get_interface_response_createForumPost($interface, $args) {
        return $interface->create_forum_post(
            $_SESSION['user_id'],
            (int)$args['threadId'],
            $args['body']
        );
    }

    protected function get_interface_response_loadNextNewPost($interface, $args) {
        return $interface->get_next_new_post($_SESSION['user_id']);
    }

    // End of Forum-related methods
    ////////////////////////////////////////////////////////////

    protected function get_interface_response_login($interface, $args) {
        assert(!is_array($interface));
        $login_success = login($args['username'], $args['password']);
        if ($login_success) {
            return array('userName' => $args['username']);
        } else {
            return NULL;
        }
    }

    protected function get_interface_response_logout() {
        logout();
        return array('userName' => FALSE);
    }

    // Construct an interface, ask it for the response to the
    // request, then construct a response
    // * For live invocation:
    //   * display the output to the user
    // * For test invocation:
    //   * return the output as a PHP variable
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
                if ($check['functype'] == 'auth') {
                    $interface = new BMInterface($this->isTest);
                    $interface->update_last_access_time($_SESSION['user_id']);
                } else {
                    $interface = new BMInterfaceNewuser($this->isTest);
                }
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

        if ($this->isTest) {
            return $output;
        } else {
            header('Content-Type: application/json');
            echo json_encode($output);
        }
    }
}
