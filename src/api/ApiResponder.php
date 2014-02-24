<?php

class ApiResponder {

    // properties
    private $isTest;               // whether this invocation is for testing

    // constructor
    // * For live invocation:
    //   * start a session (and require api_core to get session functions)
    // * For test invocation:
    //   * don't start a session
    public function __construct($isTest = FALSE) {
        $this->isTest = $isTest;

        if (!($this->isTest)) {
            session_start();
            require_once 'api_core.php';
            require_once('../lib/bootstrap.php');
        }
    }

    // This function looks at the provided arguments, calls an
    // appropriate interface routine, and returns either some game
    // data on success, or NULL on failure
    protected function get_interface_response($interface, $args) {
        $funcName = 'get_interface_response_'.$args['type'];
        if (method_exists($this, $funcName)) {
            $result = $this->$funcName($interface, $args);
        } else {
            $result = NULL;
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
        $playerNameArray = $args['playerNameArray'];
        $playerIdArray = array();
        foreach ($playerNameArray as $playerName) {
            $playerId = $interface->get_player_id_from_name($playerName);
            if (is_int($playerId)) {
                $playerIdArray[] = $playerId;
            } else {
                $playerIdArray[] = NULL;
            }
        }

        $buttonNameArray = $args['buttonNameArray'];
        $maxWins = $args['maxWins'];

        return $interface->create_game($playerIdArray, $buttonNameArray, $maxWins);
    }

    protected function get_interface_response_loadActiveGames($interface) {
        return $interface->get_all_active_games($_SESSION['user_id']);
    }

    protected function get_interface_response_loadCompletedGames($interface) {
        return $interface->get_all_completed_games($_SESSION['user_id']);
    }

    protected function get_interface_response_loadButtonNames($interface) {
        return $interface->get_all_button_names();
    }

    protected function get_interface_response_loadGameData($interface, $args) {
        $data = NULL;
        $game = $interface->load_game($args['game']);
        if ($game) {
            $currentPlayerId = $_SESSION['user_id'];
            $currentPlayerIdx = array_search($currentPlayerId, $game->playerIdArray);

            foreach ($game->playerIdArray as $playerId) {
                $playerNameArray[] = $interface->get_player_name_from_id($playerId);
            }

            $data = array(
                'currentPlayerIdx' => $currentPlayerIdx,
                'gameData' => $game->getJsonData($currentPlayerId),
                'playerNameArray' => $playerNameArray,
                'timestamp' => $interface->timestamp->format(DATE_RSS),
                'gameActionLog' => $interface->load_game_action_log($game),
                'gameChatLog' => $interface->load_game_chat_log($game),
            );
        }
        return $data;
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
        $autopass = 'true' == $args['autopass'];
        return $interface->set_player_info(
            $_SESSION['user_id'],
            array('autopass' => $autopass)
        );
    }

    protected function get_interface_response_loadPlayerNames($interface) {
        return $interface->get_player_names_like('');
    }

    protected function get_interface_response_submitSwingValues($interface, $args) {
        return $interface->submit_swing_values(
            $_SESSION['user_id'],
            $args['game'],
            $args['roundNumber'],
            $args['swingValueArray']
        );
    }

    protected function get_interface_response_reactToAuxiliary($interface, $args) {
        if (!(array_key_exists('dieIdx', $args))) {
            $args['dieIdx'] = NULL;
        }

        return $interface->react_to_auxiliary(
            $_SESSION['user_id'],
            $args['game'],
            $args['action'],
            $args['dieIdx']
        );
    }

    protected function get_interface_response_reactToReserve($interface, $args) {
        if (!(array_key_exists('dieIdx', $args))) {
            $args['dieIdx'] = NULL;
        }

        return $interface->react_to_reserve(
            $_SESSION['user_id'],
            $args['game'],
            $args['action'],
            $args['dieIdx']
        );
    }

    protected function get_interface_response_reactToInitiative($interface, $args) {
        if (!(array_key_exists('dieIdxArray', $args))) {
            $args['dieIdxArray'] = NULL;
        }
        if (!(array_key_exists('dieValueArray', $args))) {
            $args['dieValueArray'] = NULL;
        }
        return $interface->react_to_initiative(
            $_SESSION['user_id'],
            $args['game'],
            $args['roundNumber'],
            $args['timestamp'],
            $args['action'],
            $args['dieIdxArray'],
            $args['dieValueArray']
        );
    }

    protected function get_interface_response_submitTurn($interface, $args) {
        if (!(array_key_exists('chat', $args))) {
            $args['chat'] = '';
        }
        return $interface->submit_turn(
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
    }

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
        $interface = new BMInterface($this->isTest);
        $data = $this->get_interface_response($interface, $args);

        $output = array(
            'data' => $data,
            'message' => $interface->message,
        );
        if ($data) {
            $output['status'] = 'ok';
        } else {
            $output['status'] = 'failed';
        }

        if ($this->isTest) {
            return $output;
        } else {
            header('Content-Type: application/json');
            echo json_encode($output);
        }
    }
}
