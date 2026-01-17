<?php
/**
 * DummyApiResponder: Contains the mock data used for unit testing the UI
 *
 * @author chaos
 */

/**
 * This class generates the mock data necessary for unit testing the UI
 */
class DummyApiResponder {
    // properties

    /**
     * Constructor
     *   start a session (don't use api_core because dummy_responder has no backend)
     *
     * @param ApiSpec $spec
     */
    public function __construct(ApiSpec $spec) {
        $this->spec = $spec;

        session_start();
        $this->jsonFileRoot = "./dummy_data/";
    }

    // This function looks at the provided arguments, fakes appropriate
    // data to match the public API, and returns either some game
    // data on success, or NULL on failure.  (Failure will happen if
    // the requested arguments are invalid.)
    protected function get_interface_response($args) {
        $funcName = 'get_interface_response_' . $args['type'];
        if (method_exists($this, $funcName)) {
            $result = $this->$funcName($args);
        } else {
            $result = array(NULL, 'Specified API function does not exist');
        }

        return $result;
    }

    /** Utility function to load canned JSON data from a file
     *
     * @param string $apiFunction
     * @param string $fileName
     *
     * @return array tuple containing data on success or NULL on failure
     */
    protected function load_json_data_from_file($apiFunction, $fileName) {
        $filePath = $this->jsonFileRoot . $apiFunction . '/' . $fileName;
        if (file_exists($filePath)) {
            try {
                $file_data = file_get_contents($filePath);
                return json_decode($file_data, TRUE);
            } catch (Exception $e) {
                error_log(
                    "Received exception in DummyApiResponder while trying to read " . $fileName .
                    "in response to an API query for " . $apiFunction . ": " . $e
                );
                return NULL;
            }
        } else {
            error_log(
                "DummyApiResponder tried to read nonexistent file " . $fileName .
                " in response to an API query for " . $apiFunction
            );
            return NULL;
        }
    }

    protected function get_interface_response_createUser($args) {
        return $this->load_json_data_from_file(
            'createUser',
            $args['username'] . '.json'
        );
    }

    protected function get_interface_response_forgotPassword($args) {
        return $this->load_json_data_from_file(
            'forgotPassword',
            $args['username'] . '.json'
        );
    }

    protected function get_interface_response_verifyUser($args) {
        return $this->load_json_data_from_file(
            'verifyUser',
            $args['playerId'] . '.json'
        );
    }

    protected function get_interface_response_resetPassword($args) {
        return $this->load_json_data_from_file(
            'resetPassword',
            $args['playerId'] . '.json'
        );
    }

    protected function get_interface_response_createGame($args) {
        return $this->load_json_data_from_file(
            'createGame',
            $args['playerInfoArray'][0][1] . '_' . $args['playerInfoArray'][1][1] . '.json'
        );
    }

    protected function get_interface_response_joinOpenGame($args) {
        return $this->load_json_data_from_file(
            'joinOpenGame',
            $args['gameId'] . '.json'
        );
    }

    protected function get_interface_response_cancelOpenGame($args) {
        return $this->load_json_data_from_file(
            'cancelOpenGame',
            $args['gameId'] . '.json'
        );
    }

    protected function get_interface_response_selectButton($args) {
        return $this->load_json_data_from_file(
            'selectButton',
            $args['gameId'] . '.json'
        );
    }

    protected function get_interface_response_loadOpenGames() {
        return $this->load_json_data_from_file(
            'loadOpenGames',
            'noargs.json'
        );
    }

    protected function get_interface_response_searchGameHistory($args) {
        if (isset($args['status'])) {
            $argval = $args['status'];
        } elseif (isset($args['buttonNameA'])) {
            $argval = $args['buttonNameA'];
        } else {
            $argval = 'noargs';
        }
        return $this->load_json_data_from_file(
            'searchGameHistory',
            $argval . '.json'
        );
    }

    protected function get_interface_response_loadNewGames() {
        return $this->load_json_data_from_file(
            'loadNewGames',
            'noargs.json'
        );
    }

    protected function get_interface_response_loadActiveGames() {
        return $this->load_json_data_from_file(
            'loadActiveGames',
            'noargs.json'
        );
    }

    protected function get_interface_response_loadCompletedGames() {
        return $this->load_json_data_from_file(
            'loadCompletedGames',
            'noargs.json'
        );
    }

    protected function get_interface_response_loadCancelledGames() {
        return $this->load_json_data_from_file(
            'loadCancelledGames',
            'noargs.json'
        );
    }

    protected function get_interface_response_loadNextPendingGame($args) {
        if (isset($args['currentGameId'])) {
            $argval = $args['currentGameId'];
        } else {
            $argval = 'noargs';
        }
        return $this->load_json_data_from_file(
            'loadNextPendingGame',
            $argval . '.json'
        );
    }

    protected function get_interface_response_loadActivePlayers($args) {
        return $this->load_json_data_from_file(
            'loadActivePlayers',
            $args['numberOfPlayers'] . '.json'
        );
    }

    protected function get_interface_response_loadButtonData($args) {
        if (isset($args['buttonName'])) {
            $argval = str_replace(' ', '_', $args['buttonName']);
        } else {
            $argval = 'noargs';
        }
        return $this->load_json_data_from_file(
            'loadButtonData',
            $argval . '.json'
        );
    }


    protected function get_interface_response_loadButtonSetData($args) {
        if (isset($args['buttonSet'])) {
            $argval = str_replace(' ', '_', $args['buttonSet']);
        } else {
            $argval = 'noargs';
        }
        return $this->load_json_data_from_file(
            'loadButtonSetData',
            $argval . '.json'
        );
    }

    protected function get_interface_response_loadGameData($args) {
        return $this->load_json_data_from_file(
            'loadGameData',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_loadTournamentData($args) {
        return $this->load_json_data_from_file(
            'loadTournamentData',
            $args['tournament'] . '.json'
        );
    }

    protected function get_interface_response_countPendingGames() {
        return $this->load_json_data_from_file(
            'countPendingGames',
            'noargs.json'
        );
    }

    protected function get_interface_response_loadPlayerName() {
        return $this->load_json_data_from_file(
            'loadPlayerName',
            'noargs.json'
        );
    }

    protected function get_interface_response_loadPlayerInfo() {
        return $this->load_json_data_from_file(
            'loadPlayerInfo',
            'noargs.json'
        );
    }

    protected function get_interface_response_savePlayerInfo($args) {
        $argval = str_replace(' ', '_', $args['name_irl']);
        return $this->load_json_data_from_file(
            'savePlayerInfo',
            $argval . '.json'
        );
    }

    protected function get_interface_response_loadProfileInfo($args) {
        return $this->load_json_data_from_file(
            'loadProfileInfo',
            $args['playerName'] . '.json'
        );
    }

    protected function get_interface_response_loadPlayerNames() {
        return $this->load_json_data_from_file(
            'loadPlayerNames',
            'noargs.json'
        );
    }

    protected function get_interface_response_setChatVisibility($args) {
        return $this->load_json_data_from_file(
            'setChatVisibility',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_submitDieValues($args) {
        return $this->load_json_data_from_file(
            'submitDieValues',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_reactToInitiative($args) {
        return $this->load_json_data_from_file(
            'reactToInitiative',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_reactToAuxiliary($args) {
        return $this->load_json_data_from_file(
            'reactToAuxiliary',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_reactToReserve($args) {
        return $this->load_json_data_from_file(
            'reactToReserve',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_adjustFire($args) {
        return $this->load_json_data_from_file(
            'adjustFire',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_submitChat($args) {
        return $this->load_json_data_from_file(
            'submitChat',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_submitTurn($args) {
        return $this->load_json_data_from_file(
            'submitTurn',
            $args['game'] . '.json'
        );
    }

    protected function get_interface_response_reactToNewGame($args) {
        return $this->load_json_data_from_file(
            'reactToNewGame',
            $args['action'] . '.json'
        );
    }

    protected function get_interface_response_dismissGame($args) {
        return $this->load_json_data_from_file(
            'dismissGame',
            $args['gameId'] . '.json'
        );
    }

    ////////////////////////////////////////////////////////////
    // Forum-related methods

    protected function get_interface_response_loadForumOverview() {
        return $this->load_json_data_from_file(
            'loadForumOverview',
            'noargs.json'
        );
    }

    protected function get_interface_response_loadForumBoard($args) {
        return $this->load_json_data_from_file(
            'loadForumBoard',
            $args['boardId'] . '.json'
        );
    }

    protected function get_interface_response_loadForumThread($args) {
        return $this->load_json_data_from_file(
            'loadForumThread',
            $args['threadId'] . '.json'
        );
    }

    protected function get_interface_response_loadNextNewPost() {
        return $this->load_json_data_from_file(
            'loadNextNewPost',
            'noargs.json'
        );
    }

    protected function get_interface_response_markForumRead() {
        return $this->load_json_data_from_file(
            'markForumRead',
            'noargs.json'
        );
    }

    protected function get_interface_response_markForumBoardRead($args) {
        return $this->load_json_data_from_file(
            'markForumBoardRead',
            $args['boardId'] . '.json'
        );
    }

    protected function get_interface_response_markForumThreadRead($args) {
        return $this->load_json_data_from_file(
            'markForumThreadRead',
            $args['threadId'] . '.json'
        );
    }

    protected function get_interface_response_createForumThread($args) {
        return $this->load_json_data_from_file(
            'createForumThread',
            $args['boardId'] . '.json'
        );
    }

    protected function get_interface_response_createForumPost($args) {
        return $this->load_json_data_from_file(
            'createForumPost',
            $args['threadId'] . '.json'
        );
    }

    protected function get_interface_response_editForumPost($args) {
        return $this->load_json_data_from_file(
            'editForumPost',
            $args['postId'] . '.json'
        );
    }

    // End of Forum-related methods
    ////////////////////////////////////////////////////////////

    protected function get_interface_response_loadDieSkillsData() {
        return $this->load_json_data_from_file(
            'loadDieSkillsData',
            'noargs.json'
        );
    }

    protected function get_interface_response_loadDieTypesData() {
        return $this->load_json_data_from_file(
            'loadDieTypesData',
            'noargs.json'
        );
    }

    // Ask get_interface_response() for the dummy response to the
    // request, then construct a response.  Match the logic in
    // responder as closely as possible for convenience.
    public function process_request($args) {

        // make sure all arguments passed to the function are
        // syntactically reasonable, using the same ApiSpec used
        // by the real responder
        $argcheck = $this->spec->verify_function_args($args);
        if ($argcheck['ok']) {
            // As far as we can easily tell, arguments are okay.
            // Pass them along to the dummy responder functions.
            $retval = $this->get_interface_response($args);
            if ($retval) {
                $output = $retval;
            } else {
                $output = array(
                    'data' => NULL,
                    'status' => 'failed',
                    'message' => 'The arguments provided to dummy_responder were not recognized fake inputs',
                );
            }
        } else {
            $output = array(
                'data' => NULL,
                'status' => 'failed',
                'message' => $argcheck['message'],
            );
        }

        header('Content-Type: application/json');
        echo json_encode($output);
    }
}
