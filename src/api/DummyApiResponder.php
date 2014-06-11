<?php

/** Alternative responder which doesn't use real databases or
 *  sessions, but rather exists only to send dummy data used for
 *  automated testing of API compliance
 */

class DummyApiResponder {
    // properties

    // N.B. this class is always used for some type of testing,
    // but, the usage here matches the way responder uses this flag:
    // * FALSE: this instance is being accessed remotely via POST
    // * TRUE:  this instance is being accessed locally by unit tests
    private $isTest;               // whether this invocation is for testing

    // constructor
    // * For live invocation:
    //   * start a session (don't use api_core because dummy_responder has no backend)
    // * For test invocation:
    //   * don't start a session
    public function __construct(ApiSpec $spec, $isTest = FALSE) {
        $this->spec = $spec;
        $this->isTest = $isTest;

        if (!($this->isTest)) {
            session_start();
        }
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

    protected function get_interface_response_createUser($args) {
        $dummy_users = array(
            'tester1' => 1,
            'tester2' => 2,
            'tester3' => 3);
        $username = $args['username'];
        if (array_key_exists($username, $dummy_users)) {
            $userid = $dummy_users[$username];
            return array(NULL, "$username already exists (id=$userid)");
        }
        return array(array('userName' => $username),
                     'User ' . $username . ' created successfully.  ' .
                     'A verification code has been e-mailed to ' . $username . '@example.com.  ' .
                     'Follow the link in that message to start beating people up!');
    }

    protected function get_interface_response_verifyUser() {
        return array(TRUE, "New user tester1 has been verified.");
    }

    protected function get_interface_response_createGame() {
        // for verisimilitude, choose a game ID of one greater than
        // the number of "existing" games represented in loadGameData
        // and loadActiveGames

        $gameId = 20;
        return array(array('gameId' => $gameId), "Game $gameId created successfully.");
    }

    protected function get_interface_response_joinOpenGame() {
        // join_open_game() does not need to return much data
        return array(TRUE, "");
    }

    protected function get_interface_response_selectButton() {
        // select_button() does not need to return much data
        return array(TRUE, "");
    }

    protected function get_interface_response_loadOpenGames() {
        // Use games that didn't appear in loadGameData
        $games = array();

        // game 20
        $games[] = array(
            'gameId' => 20,
            'challengerId' => 1,
            'challengerName' => 'tester',
            'challengerButton' => 'Avis',
            'challengerColor' => '#cccccc',
            'victimButton' => NULL,
            'targetWins' => 3,
        );

        // game 21
        $games[] = array(
            'gameId' => 21,
            'challengerId' => 2,
            'challengerName' => 'tester2',
            'challengerButton' => 'Agatha',
            'challengerColor' => '#cccccc',
            'victimButton' => 'Krosp',
            'targetWins' => 3,
        );

        return array(array('games' => $games), "Open games retrieved successfully.");
    }

    protected function get_interface_response_searchGameHistory($args) {
        $games = array();

        if ((!isset($args['status']) || $args['status'] == 'COMPLETE')) {
            $games[] = $this->mock_completed_game($args);
        }

        if ((!isset($args['status']) || $args['status'] == 'ACTIVE')) {
            $games[] = $this->mock_active_game($args);
        }

        $summary = array();
        $summary['matchesFound'] = count($games);
        $summary['earliestStart'] = 1399605464;
        $summary['latestMove'] = 1399691809;
        $summary['gamesWinningA'] = count($games);
        $summary['gamesWinningB'] = 0;
        $summary['gamesDrawn'] = 0;
        $summary['gamesCompleted'] = 1;

        $data = array(
            'games' => $games,
            'summary' => $summary
        );

        return array($data, "Search results retrieved successfully.");
    }

    protected function mock_completed_game($args) {
        if (!isset($args['playerNameA']) || $args['playerNameA'] == 'tester') {
            // game 5
            $game = array(
                'gameId' => 5,
                'playerIdA' => 1,
                'playerNameA' => 'tester',
                'buttonNameA' => 'Avis',
                'colorA' => '#cccccc',
                'playerIdB' => 2,
                'playerNameB' => 'tester2',
                'buttonNameB' => 'Avis',
                'colorB' => '#dddddd',
                'gameStart' => 1399605464,
                'lastMove' => 1399691804,
                'roundsWonA' => 3,
                'roundsWonB' => 2,
                'roundsDrawn' => 0,
                'targetWins' => 3,
                'status' => 'COMPLETE',
            );
        } elseif (!isset($args['playerNameA']) || $args['playerNameA'] == 'tester2') {
            // game 5
            $game = array(
                'gameId' => 5,
                'playerIdA' => 2,
                'playerNameA' => 'tester2',
                'buttonNameA' => 'Avis',
                'colorA' => '#cccccc',
                'playerIdB' => 1,
                'playerNameB' => 'tester',
                'buttonNameB' => 'Avis',
                'colorB' => '#dddddd',
                'gameStart' => 1399605464,
                'lastMove' => 1399691804,
                'roundsWonA' => 3,
                'roundsWonB' => 2,
                'roundsDrawn' => 0,
                'targetWins' => 3,
                'status' => 'COMPLETE',
            );
        }

        return $game;
    }

    protected function mock_active_game($args) {
        if (!isset($args['playerNameA']) || $args['playerNameA'] == 'tester') {
            // game 6
            $game = array(
                'gameId' => 6,
                'playerIdA' => 1,
                'playerNameA' => 'tester',
                'buttonNameA' => 'Buck Godot',
                'colorA' => '#cccccc',
                'playerIdB' => 2,
                'playerNameB' => 'tester2',
                'buttonNameB' => 'Von Pinn',
                'colorB' => '#dddddd',
                'gameStart' => 1399605469,
                'lastMove' => 1399691809,
                'roundsWonA' => 0,
                'roundsWonB' => 0,
                'roundsDrawn' => 0,
                'targetWins' => 3,
                'status' => 'ACTIVE',
            );
        } elseif (!isset($args['playerNameA']) || $args['playerNameA'] == 'tester2') {
            // game 5
            $game = array(
                'gameId' => 6,
                'playerIdA' => 2,
                'playerNameA' => 'tester2',
                'buttonNameA' => 'Buck Godot',
                'colorA' => '#cccccc',
                'playerIdB' => 1,
                'playerNameB' => 'tester',
                'buttonNameB' => 'Von Pinn',
                'colorB' => '#dddddd',
                'gameStart' => 1399605469,
                'lastMove' => 1399691809,
                'roundsWonA' => 0,
                'roundsWonB' => 0,
                'roundsDrawn' => 0,
                'targetWins' => 3,
                'status' => 'ACTIVE',
            );
        }

        return $game;
    }

    protected function get_interface_response_loadActiveGames() {
        // Use the same fake games here which were described in loadGameData
        $data = array(
            'gameIdArray' => array(),
            'opponentIdArray' => array(),
            'opponentNameArray' => array(),
            'myButtonNameArray' => array(),
            'opponentButtonNameArray' => array(),
            'nWinsArray' => array(),
            'nLossesArray' => array(),
            'nDrawsArray' => array(),
            'nTargetWinsArray' => array(),
            'isAwaitingActionArray' => array(),
            'gameStateArray' => array(),
            'statusArray' => array(),
            'inactivityArray' => array(),
        );

        for ($gameIdx = 1; $gameIdx <= 19; $gameIdx++) {
            $funcname = 'add_active_game_data_'.$gameIdx;
            $this->$funcname($data);
        }

        return array($data, "All game details retrieved successfully.");
    }

    protected function add_active_game_data_1(&$data) {
        $data['gameIdArray'][] = 1;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Avis";
        $data['opponentButtonNameArray'][] = "Avis";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "SPECIFY_DICE";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "17 minutes";
    }

    protected function add_active_game_data_2(&$data) {
        $data['gameIdArray'][] = 2;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Avis";
        $data['opponentButtonNameArray'][] = "Avis";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 0;
        $data['gameStateArray'][] = "SPECIFY_DICE";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "2 hours";
    }

    protected function add_active_game_data_3(&$data) {
        $data['gameIdArray'][] = 3;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Avis";
        $data['opponentButtonNameArray'][] = "Avis";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "START_TURN";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "5 minutes";
    }

    protected function add_active_game_data_4(&$data) {
        $data['gameIdArray'][] = 4;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Avis";
        $data['opponentButtonNameArray'][] = "Avis";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 0;
        $data['gameStateArray'][] = "START_TURN";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "6 days";
    }

    protected function add_active_game_data_5() {
        // fake game 5 is completed
    }

    protected function add_active_game_data_6(&$data) {
        $data['gameIdArray'][] = 6;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Buck Godot";
        $data['opponentButtonNameArray'][] = "Von Pinn";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "SPECIFY_DICE";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "44 seconds";
    }

    protected function add_active_game_data_7(&$data) {
        $data['gameIdArray'][] = 7;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Crab";
        $data['opponentButtonNameArray'][] = "Crab";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "REACT_TO_INITIATIVE";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "22 minutes";
    }

    protected function add_active_game_data_8(&$data) {
        $data['gameIdArray'][] = 8;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "John Kovalic";
        $data['opponentButtonNameArray'][] = "John Kovalic";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "REACT_TO_INITIATIVE";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "19 hours";
    }

    protected function add_active_game_data_9(&$data) {
        $data['gameIdArray'][] = 9;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "John Kovalic";
        $data['opponentButtonNameArray'][] = "John Kovalic";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 0;
        $data['gameStateArray'][] = "REACT_TO_INITIATIVE";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "1 day";
    }

    protected function add_active_game_data_10() {
        // tester1 is not a participant in fake game 10
    }

    protected function add_active_game_data_11() {
        // tester1 is not a participant in fake game 11
    }

    protected function add_active_game_data_12() {
        // tester1 is not a participant in fake game 12
    }

    protected function add_active_game_data_13(&$data) {
        $data['gameIdArray'][] = 13;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "King Arthur";
        $data['opponentButtonNameArray'][] = "King Arthur";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "CHOOSE_AUXILIARY_DICE";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "16 days";
    }

    protected function add_active_game_data_14(&$data) {
        $data['gameIdArray'][] = 14;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "King Arthur";
        $data['opponentButtonNameArray'][] = "King Arthur";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 0;
        $data['gameStateArray'][] = "CHOOSE_AUXILIARY_DICE";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "38 minutes";
    }

    protected function add_active_game_data_15() {
        // tester1 is not a participant in fake game 15
    }

    protected function add_active_game_data_16(&$data) {
        $data['gameIdArray'][] = 16;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Cammy Neko";
        $data['opponentButtonNameArray'][] = "Cammy Neko";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 1;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "CHOOSE_RESERVE_DICE";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "1 minute";
    }

    protected function add_active_game_data_17(&$data) {
        $data['gameIdArray'][] = 17;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Cammy Neko";
        $data['opponentButtonNameArray'][] = "Cammy Neko";
        $data['nWinsArray'][] = 1;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 0;
        $data['gameStateArray'][] = "CHOOSE_RESERVE_DICE";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "21 hours";
    }

    protected function add_active_game_data_18() {
        // tester1 is not a participant in fake game 18
    }

    protected function add_active_game_data_19(&$data) {
        $data['gameIdArray'][] = 19;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Apples";
        $data['opponentButtonNameArray'][] = "Apples";
        $data['nWinsArray'][] = 0;
        $data['nLossesArray'][] = 0;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 1;
        $data['gameStateArray'][] = "SPECIFY_DICE";
        $data['statusArray'][] = "ACTIVE";
        $data['inactivityArray'][] = "10 minutes";
    }

    protected function get_interface_response_loadCompletedGames() {
        $data = array(
            'gameIdArray' => array(),
            'opponentIdArray' => array(),
            'opponentNameArray' => array(),
            'myButtonNameArray' => array(),
            'opponentButtonNameArray' => array(),
            'nWinsArray' => array(),
            'nLossesArray' => array(),
            'nDrawsArray' => array(),
            'nTargetWinsArray' => array(),
            'isAwaitingActionArray' => array(),
            'gameStateArray' => array(),
            'statusArray' => array(),
            'inactivityArray' => array(),
        );

        // game 5
        $data['gameIdArray'][] = 5;
        $data['opponentIdArray'][] = 2;
        $data['opponentNameArray'][] = "tester2";
        $data['myButtonNameArray'][] = "Avis";
        $data['opponentButtonNameArray'][] = "Avis";
        $data['nWinsArray'][] = 3;
        $data['nLossesArray'][] = 2;
        $data['nDrawsArray'][] = 0;
        $data['nTargetWinsArray'][] = 3;
        $data['isAwaitingActionArray'][] = 0;
        $data['gameStateArray'][] = "END_GAME";
        $data['statusArray'][] = "COMPLETE";
        $data['inactivityArray'][] = "8 days";

        return array($data, "All game details retrieved successfully.");
    }

    protected function get_interface_response_loadNextPendingGame($args) {
        // Assume that game IDs 7 is the next one waiting for tester1,
        // and that 4 is next after that if 7 is being skipped
        if (isset($args['currentGameId']) && $args['currentGameId'] == 7) {
            $data = array('gameId' => 4);
        } else {
            $data = array('gameId' => 7);
        }
        return array($data, 'Next game ID retrieved successfully.');
    }

    protected function get_interface_response_loadActivePlayers() {
        $players = array(
            array(
                'playerName' => 'responder003',
                'idleness' => '0 seconds',
            ),
            array(
                'playerName' => 'responder004',
                'idleness' => '12 minutes',
            ),
        );

        return array(array('players' => $players),
            'Active players retrieved successfully.');
    }

    protected function get_interface_response_loadButtonNames() {
        $data = array(
          'buttonNameArray' => array(),
          'recipeArray' => array(),
          'hasUnimplementedSkillArray' => array(),
        );

        // a button with no special skills
        $data['buttonNameArray'][] = "Avis";
        $data['recipeArray'][] = "(4) (4) (10) (12) (X)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // a button with an unimplemented skill
        $data['buttonNameArray'][] = "Adam Spam";
        $data['recipeArray'][] = "F(4) F(6) (6) (12) (X)";
        $data['hasUnimplementedSkillArray'][] = TRUE;

        // a button with four dice and some implemented skills
        $data['buttonNameArray'][] = "Jellybean";
        $data['recipeArray'][] = "p(20) s(20) (V) (X)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // Buck Godot
        $data['buttonNameArray'][] = "Buck Godot";
        $data['recipeArray'][] = "(6,6) (10) (12) (20) (W,W)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // Von Pinn
        $data['buttonNameArray'][] = "Von Pinn";
        $data['recipeArray'][] = "(4) p(6,6) (10) (20) (W)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // Crab: a button with focus dice
        $data['buttonNameArray'][] = "Crab";
        $data['recipeArray'][] = "(8) (10) (12) f(20) f(20)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // John Kovalic: a button with chance dice
        $data['buttonNameArray'][] = "John Kovalic";
        $data['recipeArray'][] = "(6) c(6) (10) (12) c(20)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // King Arthur: a button with an auxiliary die
        $data['buttonNameArray'][] = "King Arthur";
        $data['recipeArray'][] = "(8) (8) (10) (20) (X) +(20)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // Cammy Neko: a button with reserve dice
        $data['buttonNameArray'][] = "Cammy Neko";
        $data['recipeArray'][] = "(4) (6) (12) (10,10) r(12) r(20) r(20) r(8,8)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // Apples: a button with option dice
        $data['buttonNameArray'][] = "Apples";
        $data['recipeArray'][] = "(8) (8) (2/12) (8/16) (20/24)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        // CactusJack: a button with swing and option dice (and shadow and speed skills)
        $data['buttonNameArray'][] = "CactusJack";
        $data['recipeArray'][] = "z(8/12) (4/16) s(6/10) z(X) s(U)";
        $data['hasUnimplementedSkillArray'][] = FALSE;

        return array($data, "All button names retrieved successfully.");
    }

    protected function get_interface_response_loadGameData($args) {
        // The dummy loadGameData returns one of a number of
        // sets of dummy game data, for general test use.
        // Specify which one you want using the game number:
        //   1: a newly-created game, waiting for both players to set swing dice
        //   2: new game in which the active player has set swing dice
        //   3: game in which it is the current player's turn to attack
        //   4: game in which it is the opponent's turn to attack
        //   5: game which has been completed
        //   7: game in which focus dice can be used to respond to initiative
        //   8: game in which chance dice can be used to respond to initiative
        //   9: game in which opponent can use chance dice to respond to initiative
        //  10: game in "specify dice" state in which active player is not a participant
        //  11: game in "start turn" state in which active player is not a participant
        //  12: game in "react to initiative" state in which active player is not a participant
        //  13: game in which active player can decide whether to choose auxiliary die
        //  14: game in which it is the opponents turn to choose auxiliary die
        //  15: game in "choose auxiliary" state in which active player is not a participant
        //  16: game in which active player can decide whether to add reserve die
        //  17: game in which opponent can decide whether to add reserve die
        //  18: game in "choose reserve" state in which active player is not a participant
        //  19: game in which active player can choose option die values

        $data = NULL;

        $funcname = 'load_game_data_'.$args['game'];
        if (method_exists($this, $funcname)) {
            $data = $this->$funcname();
        }

        if ($data) {
            if (isset($args['logEntryLimit']) && $args['logEntryLimit'] > 0) {
                $data['gameActionLog'] =
                    array_slice($data['gameActionLog'], 0, $args['logEntryLimit']);
                $data['gameChatLog'] =
                    array_slice($data['gameChatLog'], 0, $args['logEntryLimit']);
            }

            if (!(array_key_exists('playerNameArray', $data))) {
                $data['playerNameArray'] = array('tester1', 'tester2');
            }
            if (!(array_key_exists('gameChatEditable', $data))) {
                $data['gameChatEditable'] = FALSE;
            }
            $timestamp = strtotime('now');
            $data['timestamp'] = $timestamp;
            return array($data, "Loaded data for game " . $args['game']);
        }
        return array(NULL, "Game does not exist.");
    }

    protected function mock_base_game_data() {
        // base params for an unstarted Avis vs. Avis game - override these as needed
        // Regardless, you *must* set gameId and gameState
        $gameData = array(
            "roundNumber" => 1,
            "maxWins" => 3,
            "activePlayerIdx" => NULL,
            "playerWithInitiativeIdx" => NULL,
            "playerIdArray" => array(1, 2),
            "buttonNameArray" => array("Avis", "Avis"),
            "buttonRecipeArray" => array("(4) (4) (10) (12) (X)", "(4) (4) (10) (12) (X)"),
            "waitingOnActionArray" => array(TRUE, TRUE),
            "nDieArray" => array(5, 5),
            "valueArrayArray" => array(array(NULL,NULL,NULL,NULL,NULL),
                                       array(NULL,NULL,NULL,NULL,NULL)),
            "sidesArrayArray" => array(array(4,4,10,12,NULL),
                                       array(4,4,10,12,NULL)),
            "dieSkillsArrayArray" => array(array(array(), array(), array(), array(), array()),
                                           array(array(), array(), array(), array(), array())),
            "diePropertiesArrayArray" => array(array(array(), array(), array(), array(), array()),
                                               array(array(), array(), array(), array(), array())),
            "dieRecipeArrayArray" => array(array("(4)","(4)","(10)","(12)","(X)"),
                                           array("(4)","(4)","(10)","(12)","(X)")),
            "dieDescriptionArrayArray" =>
                array(
                    array(
                        '4-sided die',
                        '4-sided die',
                        '10-sided die',
                        '12-sided die',
                        'X Swing Die'
                    ),
                    array(
                        '4-sided die',
                        '4-sided die',
                        '10-sided die',
                        '12-sided die',
                        'X Swing Die'
                    )
                ),
            "nCapturedDieArray" => array(0, 0),
            "capturedValueArrayArray" => array(array(), array()),
            "capturedSidesArrayArray" => array(array(), array()),
            "capturedRecipeArrayArray" => array(array(), array()),
            "capturedDiePropsArrayArray" => array(array(), array()),
            "swingRequestArrayArray" => array(array("X" => array(4, 20)), array("X" => array(4, 20))),
            "optRequestArrayArray" => array(array(), array()),
            "prevSwingValueArrayArray" => array(array(), array()),
            "prevOptValueArrayArray" => array(array(), array()),
            "validAttackTypeArray" => array(),
            "roundScoreArray" => array(NULL, NULL),
            "sideScoreArray" => array(NULL, NULL),
            "gameScoreArrayArray" => array(array("W" => 0, "L" => 0, "D" => 0),
                                           array("W" => 0, "L" => 0, "D" => 0)),
            "lastActionTimeArray" => array(0, 0),
        );

        return $gameData;
    }

    protected function mock_JohnKovalic_game_data() {
        // base params for a John Kovalic vs John Kovalic game, here to
        // avoid the duplicated code warning
        $gameData = $this->mock_base_game_data();
        $gameData['gameState'] = "REACT_TO_INITIATIVE";
        $gameData['playerWithInitiativeIdx'] = 1;
        $gameData['buttonNameArray'] = array("John Kovalic", "John Kovalic");
        $gameData['buttonRecipeArray'] = array("(6) c(6) (10) (12) c(20)", "(6) c(6) (10) (12) c(20)");
        $gameData['waitingOnActionArray'] = array(TRUE, FALSE);
        $gameData['valueArrayArray'] = array(array(4, 3, 6, 5, 4), array(2, 4, 2, 3, 18));
        $gameData['sidesArrayArray'] = array(array(6, 6, 10, 12, 20), array(6, 6, 10, 12, 20));
        $gameData['dieRecipeArrayArray'] =
            array(
                array("(6)","c(6)","(10)","(12)","c(20)"),
                array("(6)","c(6)","(10)","(12)","c(20)")
            );
        $gameData['dieDescriptionArrayArray'] =
            array(
                array(
                    '6-sided die',
                    'Chance 6-sided die',
                    '10-sided die',
                    '12-sided die',
                    'Chance 20-sided die'
                ),
                array(
                    '6-sided die',
                    'Chance 6-sided die',
                    '10-sided die',
                    '12-sided die',
                    'Chance 20-sided die'
                )
            );
        $gameData['swingRequestArrayArray'] = array(array(), array());
        $gameData['roundScoreArray'] = array(NULL, NULL);

        return $gameData;
    }

    protected function mock_KingArthur_game_data() {
        // base params for a King Arthur vs King Arthur game
        $gameData = $this->mock_base_game_data();
        $gameData['gameState'] = "CHOOSE_AUXILIARY_DICE";
        $gameData['buttonNameArray'] = array("King Arthur", "King Arthur");
        $gameData['nDieArray'] = array(6, 6);
        $gameData['buttonRecipeArray'] = array("(8) (8) (10) (20) (X) +(20)", "(8) (8) (10) (20) (X) +(20)");
        $gameData['waitingOnActionArray'] = array(TRUE, TRUE);
        $gameData['valueArrayArray'] =
            array(
                array(NULL, NULL, NULL, NULL, NULL, NULL),
                array(NULL, NULL, NULL, NULL, NULL, NULL)
            );
        $gameData['sidesArrayArray'] =
            array(
                array(8, 8, 10, 20, NULL, 20),
                array(NULL, NULL, NULL, NULL, NULL, NULL)
            );
        $gameData['dieRecipeArrayArray'] =
            array(
                array("(8)","(8)","(10)","(20)","(X)","+(20)"),
                array("(8)","(8)","(10)","(20)","(X)","+(20)")
            );
        $gameData['dieSkillsArrayArray'] =
            array(
                array(array(), array(), array(), array(), array(), array('Auxiliary' => TRUE)),
                array(array(), array(), array(), array(), array(), array('Auxiliary' => TRUE))
            );
        $gameData['diePropertiesArrayArray'] =
            array(
                array(array(), array(), array(), array(), array(), array()),
                array(array(), array(), array(), array(), array(), array())
            );
        $gameData['dieDescriptionArrayArray'] =
            array(
                array(
                    '8-sided die',
                    '8-sided die',
                    '10-sided die',
                    '20-sided die',
                    'X Swing Die',
                    'Auxiliary 20-sided die'
                ),
                array(
                    '8-sided die',
                    '8-sided die',
                    '10-sided die',
                    '20-sided die',
                    'X Swing Die',
                    'Auxiliary 20-sided die'
                )
            );
        $gameData['roundScoreArray'] = array(NULL, NULL);

        return $gameData;
    }

    protected function mock_CammyNeko_game_data() {
        // base params for a Cammy Neko vs Cammy Neko game
        $gameData = $this->mock_base_game_data();
        $gameData['gameState'] = "CHOOSE_RESERVE_DICE";
        $gameData['roundNumber'] = 2;
        $gameData['gameScoreArrayArray'] =
            array(
                array("W" => 0, "L" => 1, "D" => 0),
                array("W" => 1, "L" => 0, "D" => 0)
            );
        $gameData['buttonNameArray'] = array("Cammy Neko", "Cammy Neko");
        $gameData['nDieArray'] = array(8, 8);
        $gameData['buttonRecipeArray'] =
            array(
                "(4) (6) (12) (10,10) r(12) r(20) r(20) r(8,8)",
                "(4) (6) (12) (10,10) r(12) r(20) r(20) r(8,8)"
            );
        $gameData['waitingOnActionArray'] = array(TRUE, FALSE);
        $gameData['valueArrayArray'] =
            array(
                array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
                array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)
            );
        $gameData['sidesArrayArray'] =
            array(
                array(4, 6, 12, 20, 12, 20, 20, 16),
                array(4, 6, 12, 20, 12, 20, 20, 16)
            );
        $gameData['dieRecipeArrayArray'] =
            array(
                array("(4)","(6)","(12)","(10,10)","r(12)","r(20)","r(20)","r(8,8)"),
                array("(4)","(6)","(12)","(10,10)","r(12)","r(20)","r(20)","r(8,8)")
            );
        $gameData['dieSkillsArrayArray'] =
            array(
                array(array(), array(), array(), array(),
                      array('Reserve' => TRUE), array('Reserve' => TRUE),
                      array('Reserve' => TRUE), array('Reserve' => TRUE)),
                array(array(), array(), array(), array(),
                      array('Reserve' => TRUE), array('Reserve' => TRUE),
                      array('Reserve' => TRUE), array('Reserve' => TRUE))
            );
        $gameData['diePropertiesArrayArray'] =
            array(
                array(array(), array(), array(), array(), array(), array(), array(), array()),
                array(array(), array(), array(), array(), array(), array(), array(), array())
            );
        $gameData['dieDescriptionArrayArray'] =
            array(
                array(
                    '4-sided die',
                    '6-sided die',
                    '12-sided die',
                    'Twin Die (both with 10 sides)',
                    'Reserve 12-sided die',
                    'Reserve 20-sided die',
                    'Reserve 20-sided die',
                    'Reserve Twin Die (both with 8 sides)'
                ),
                array(
                    '4-sided die',
                    '6-sided die',
                    '12-sided die',
                    'Twin Die (both with 10 sides)',
                    'Reserve 12-sided die',
                    'Reserve 20-sided die',
                    'Reserve 20-sided die',
                    'Reserve Twin Die (both with 8 sides)'
                )
            );
        $gameData['roundScoreArray'] = array(NULL, NULL);

        return $gameData;
    }

    protected function mock_Apples_game_data() {
        // base params for an Apples vs Apples game
        $gameData = $this->mock_base_game_data();
        $gameData['gameState'] = "SPECIFY_DICE";
        $gameData['playerWithInitiativeIdx'] = NULL;
        $gameData['buttonNameArray'] = array("Apples", "Apples");
        $gameData['buttonRecipeArray'] = array("(8) (8) (2/12) (8/16) (20/24)", "(8) (8) (2/12) (8/16) (20/24)");
        $gameData['waitingOnActionArray'] = array(TRUE, TRUE);
        $gameData['valueArrayArray'] = array(array(4, 3, NULL, NULL, NULL), array(2, 4, NULL, NULL, NULL));
        $gameData['sidesArrayArray'] = array(array(6, 6, NULL, NULL, NULL), array(6, 6, NULL, NULL, NULL));
        $gameData['dieRecipeArrayArray'] =
            array(
                array("(8)","(8)","(2/12)","(8/16)","(20/24)"),
                array("(8)","(8)","(2/12)","(8/16)","(20/24)")
            );
        $gameData['dieDescriptionArrayArray'] =
            array(
                array(
                    '8-sided die',
                    '8-sided die',
                    'Option die (with 2 or 12 sides)',
                    'Option die (with 8 or 16 sides)',
                    'Option die (with 20 or 24 sides)'
                ),
                array(
                    '8-sided die',
                    '8-sided die',
                    'Option die (with 2 or 12 sides)',
                    'Option die (with 8 or 16 sides)',
                    'Option die (with 20 or 24 sides)'
                )
            );
        $gameData['swingRequestArrayArray'] = array(array(), array());
        $gameData['optRequestArrayArray'] =
            array(
                array(
                    2 => array(2, 12),
                    3 => array(8, 16),
                    4 => array(20, 24),
                ),
                array(
                    2 => array(2, 12),
                    3 => array(8, 16),
                    4 => array(20, 24),
                ),
            );
        $gameData['roundScoreArray'] = array(NULL, NULL);

        return $gameData;
    }

    protected function load_game_data_1() {
        $gameData = $this->mock_base_game_data();
        $gameData['gameId'] = 1;
        $gameData['gameState'] = "SPECIFY_DICE";
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => 0,
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_2() {
        $gameData = $this->mock_base_game_data();
        $gameData['gameId'] = 2;
        $gameData['gameState'] = "SPECIFY_DICE";
        $gameData['waitingOnActionArray'] = array(FALSE, TRUE);
        $gameData['sidesArrayArray'] = array(array(4,4,10,12,4),
                                             array(NULL,NULL,NULL,NULL,NULL));
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => 0,
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_3() {
        $gameData = $this->mock_base_game_data();
        $gameData['gameId'] = 3;
        $gameData['gameState'] = "START_TURN";
        $gameData['activePlayerIdx'] = 0;
        $gameData['playerWithInitiativeIdx'] = 1;
        $gameData['waitingOnActionArray'] = array(TRUE, FALSE);
        $gameData['nDieArray'] = array(2, 3);
        $gameData['valueArrayArray'] = array(array(4, 2), array(4, 4, 5));
        $gameData['sidesArrayArray'] = array(array(4,4), array(4,4,12));
        $gameData['dieRecipeArrayArray'] = array(array("(4)","(X)"),
                                                 array("(4)","(4)","(12)"));
        $gameData['dieDescriptionArrayArray'] =
            array(
                array('4-sided die', 'X Swing Die (with 4 sides)'),
                array('4-sided die', '4-sided die', '12-sided die')
            );
        $gameData['nCapturedDieArray'] = array(2, 3);
        $gameData['capturedValueArrayArray'] = array(array(3, 1), array(11, 7, 1));
        $gameData['capturedSidesArrayArray'] = array(array(10, 4), array(12, 10, 4));
        $gameData['capturedRecipeArrayArray'] = array(array("(10)", "(X)"),
                                                      array("(12)", "(10)", "(4)"));
        $gameData['capturedDiePropsArrayArray'] = array(array(array(), array()),
                                                        array(array(), array(), array("WasJustCaptured" => TRUE)));
        $gameData['validAttackTypeArray'] = array("Power" => "Power", "Skill" => "Skill", );
        $gameData['roundScoreArray'] = array(18, 36);
        $gameData['sideScoreArray'] = array(-12, 12);
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'gameActionLog' => array(
                array("timestamp" => 1387746541,
                      "message" =>
                          "tester2 performed Power attack using [(12):1] against [(4):1]; " .
                          "Defender (4) was captured; Attacker (12) rerolled 1 => 5"),
                array("timestamp" => 1387746536,
                      "message" =>
                          "tester1 performed Power attack using [(4):2] against [(X):1]; " .
                          "Defender (X) was captured; Attacker (4) rerolled 2 => 1"),
                array("timestamp" => 1387746232,
                      "message" =>
                          "tester2 performed Skill attack using [(4):4,(X):3] against [(10):7]; " .
                          "Defender (10) was captured; Attacker (4) rerolled 4 => 4; " .
                          "Attacker (X) rerolled 3 => 1"),
                array("timestamp" => 1387746219,
                      "message" =>
                          "tester1 performed Power attack using [(4):3] against [(10):3]; " .
                          "Defender (10) was captured; Attacker (4) rerolled 3 => 4"),
                array("timestamp" => 1387746192,
                      "message" =>
                          "tester2 performed Skill attack using [(4):1,(10):5,(12):5] against [(12):11]; " .
                          "Defender (12) was captured; Attacker (4) rerolled 1 => 4; " .
                          "Attacker (10) rerolled 5 => 3; Attacker (12) rerolled 5 => 1")
            ),
            'gameChatLog' => array(
                array("timestamp" => 1387746541,
                      "player" => "tester2",
                      "message" => "Hello!\n    Ceci n'est pas une <script>tag</script>."),
                array("timestamp" => 1387746536,
                      "player" => "tester1",
                      "message" => "Hi."),
                array("timestamp" => 1387746232,
                      "player" => "tester2",
                      "message" => "Greetings."),
                array("timestamp" => 1387746219,
                      "player" => "tester1",
                      "message" => "Salutations."),
                array("timestamp" => 1387746192,
                      "player" => "tester2",
                      "message" => "Good morning."),
                array("timestamp" => 1387746092,
                      "player" => "tester2",
                      "message" => "Bonjour."),
                array("timestamp" => 1387745992,
                      "player" => "tester1",
                      "message" => "Yo."),
                array("timestamp" => 1387745892,
                      "player" => "tester2",
                      "message" => "How are you?"),
                array("timestamp" => 1387745792,
                      "player" => "tester1",
                      "message" => "Howdy."),
                array("timestamp" => 1387745692,
                      "player" => "tester2",
                      "message" => "Ping!"),
                array("timestamp" => 1387745592,
                      "player" => "tester2",
                      "message" => "G'day."),
            ),
        );
        $data['currentPlayerIdx'] = 0;

        return $data;
    }

    protected function load_game_data_4() {
        $gameData = $this->mock_base_game_data();
        $gameData['gameId'] = 4;
        $gameData['gameState'] = "START_TURN";
        $gameData['activePlayerIdx'] = 1;
        $gameData['playerWithInitiativeIdx'] = 1;
        $gameData['waitingOnActionArray'] = array(FALSE, TRUE);
        $gameData['valueArrayArray'] = array(array(3, 4, 7, 9, 4),
                                             array(1, 3, 4, 5, 2));
        $gameData['sidesArrayArray'] = array(array(4,4,10,12,4),
                                             array(4,4,10,12,4));
        $gameData['validAttackTypeArray'] = array("Power" => "Power", "Skill" => "Skill", );
        $gameData['roundScoreArray'] = array(17, 17);
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => 0,
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_5() {
        $gameData = $this->mock_base_game_data();
        $gameData['gameId'] = 5;
        $gameData['gameState'] = "END_GAME";
        $gameData['roundNumber'] = 6;
        $gameData['playerWithInitiativeIdx'] = 1;
        $gameData['waitingOnActionArray'] = array(FALSE, FALSE);
        $gameData['nDieArray'] = array(0, 0);
        $gameData['valueArrayArray'] = array(array(), array());
        $gameData['sidesArrayArray'] = array(array(), array());
        $gameData['dieRecipeArrayArray'] = array(array(), array());
        $gameData['dieDescriptionArrayArray'] = array(array(), array());
        $gameData['roundScoreArray'] = array(0, 0);
        $gameData['sideScoreArray'] = array(0, 0);
        $gameData['gameScoreArrayArray'] = array(array("W" => 3, "L" => 2, "D" => 0),
                                                 array("W" => 2, "L" => 3, "D" => 0));
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => 0,
            'gameActionLog' => array(
                array("timestamp" => 1387500762,
                      "message" => "End of round: tester1 won round 5 (46 vs 30)"),
                array("timestamp" => 1387500762,
                      "message" =>
                          "tester1 performed Power attack using [(X):7] against [(4):2]; " .
                          "Defender (4) was captured; Attacker (X) rerolled 7 => 4"),
                array("timestamp" => 1387500756,
                      "message" => "tester2 passed"),
                array("timestamp" => 1387500753,
                      "message" =>
                          "tester1 performed Power attack using [(X):14] against [(10):4]; " .
                          "Defender (10) was captured; Attacker (X) rerolled 14 => 7"),
                array("timestamp" => 1387500749,
                      "message" =>
                          "tester2 performed Power attack using [(10):10] against [(4):4]; " .
                          "Defender (4) was captured; Attacker (10) rerolled 10 => 4"),
            ),
            'gameChatLog' => array(
                array("timestamp" => "2013-12-20 00:52:42",
                      "player" => "tester1",
                      "message" => "Pong."),
                array("timestamp" => "2013-12-20 00:52:29",
                      "player" => "tester2",
                      "message" => "Ping!"),
            ),
        );

        return $data;
    }

    protected function load_game_data_6() {
        $gameData = $this->mock_base_game_data();
        $gameData['gameId'] = 6;
        $gameData['gameState'] = "SPECIFY_DICE";
        $gameData['buttonNameArray'] = array("Buck Godot", "Von Pinn");
        $gameData['buttonRecipeArray'] = array("(6,6) (10) (12) (20) (W,W)", "(4) p(6,6) (10) (20) (W)");
        $gameData['sidesArrayArray'] = array(array(12,10,12,20,NULL),
                                             array(NULL,NULL,NULL,NULL,NULL));
        $gameData['dieRecipeArrayArray'] = array(array("(6,6)","(10)","(12)","(20)","(W,W)"),
                                                 array("(4)","p(6,6)","(10)","(20)","(W)"));
        $gameData['dieDescriptionArrayArray'] =
            array(
                array(
                    'Twin Die (both with 6 sides)',
                    '10-sided die',
                    '12-sided die',
                    '20-sided die',
                    'Twin W Swing Die'
                ),
                array(
                    '4-sided die',
                    'Poison Twin Die (both with 6 sides)',
                    '10-sided die',
                    '20-sided die',
                    'W Swing Die'
                )
            );
        $gameData['swingRequestArrayArray'] = array(array("W" => array(4, 12)), array("W" => array(4, 12)));
        $gameData['roundScoreArray'] = array(NULL, NULL);
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => 0,
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_7() {
        $gameData = $this->mock_base_game_data();
        $gameData['gameId'] = 7;
        $gameData['gameState'] = "REACT_TO_INITIATIVE";
        $gameData['playerWithInitiativeIdx'] = 1;
        $gameData['buttonNameArray'] = array("Crab", "Crab");
        $gameData['buttonRecipeArray'] = array("(8) (10) (12) f(20) f(20)", "(8) (10) (12) f(20) f(20)");
        $gameData['waitingOnActionArray'] = array(TRUE, FALSE);
        $gameData['valueArrayArray'] = array(array(1, 8, 10, 6, 18),
                                             array(4, 7, 5, 1, 12));
        $gameData['sidesArrayArray'] = array(array(8,10,12,20,20),
                                             array(8,10,12,20,20));
        $gameData['dieRecipeArrayArray'] = array(array("(8)","(10)","(12)","f(20)","f(20)"),
                                                 array("(8)","(10)","(12)","f(20)","f(20)"));
        $gameData['dieSkillsArrayArray'] =
            array(array(array(), array(), array(), array('Focus' => TRUE), array('Focus' => TRUE)),
                  array(array(), array(), array(), array('Focus' => TRUE), array('Focus' => TRUE)));
        $gameData['dieDescriptionArrayArray'] =
            array(
                array(
                    '8-sided die',
                    '10-sided die',
                    '12-sided die',
                    'Focus 20-sided die',
                    'Focus 20-sided die'
                ),
                array(
                    '8-sided die',
                    '10-sided die',
                    '12-sided die',
                    'Focus 20-sided die',
                    'Focus 20-sided die'
                )
            );
        $gameData['swingRequestArrayArray'] = array(array(), array());
        $gameData['roundScoreArray'] = array(NULL, NULL);
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => 0,
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_8() {
        $gameData = $this->mock_JohnKovalic_game_data();
        $gameData['gameId'] = 8;
        $gameData['playerWithInitiativeIdx'] = 1;
        $gameData['waitingOnActionArray'] = array(TRUE, FALSE);
        $gameData['valueArrayArray'] = array(array(4, 3, 6, 5, 4), array(2, 4, 2, 3, 18));
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => 0,
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_9() {
        $gameData = $this->mock_JohnKovalic_game_data();
        $gameData['gameId'] = 9;
        $gameData['playerWithInitiativeIdx'] = 0;
        $gameData['waitingOnActionArray'] = array(FALSE, TRUE);
        $gameData['valueArrayArray'] = array(array(2, 4, 2, 3, 18), array(4, 3, 6, 5, 4));
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => 0,
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_10() {
        $gameData = $this->mock_base_game_data();
        $gameData['gameId'] = 10;
        $gameData['gameState'] = "SPECIFY_DICE";
        $gameData['playerIdArray'] = array(2, 3);
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => FALSE,
            'playerNameArray' => array('tester2', 'tester3'),
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_11() {
        // game 11 uses the same somewhat-involved game state as in game 3,
        // but in game 11, tester1 is not a participant

        $data = $this->load_game_data_3();
        $data['gameData']['data']['gameId'] = 11;
        $data['currentPlayerIdx'] = FALSE;
        $data['playerNameArray'] = array('tester2', 'tester3');

        return $data;
    }

    protected function load_game_data_12() {
        $gameData = $this->mock_JohnKovalic_game_data();
        $gameData['gameId'] = 12;
        $gameData['playerWithInitiativeIdx'] = 1;
        $gameData['waitingOnActionArray'] = array(TRUE, FALSE);
        $gameData['valueArrayArray'] = array(array(4, 3, 6, 5, 4), array(2, 4, 2, 3, 18));
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => FALSE,
            'playerNameArray' => array('tester2', 'tester3'),
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_13() {
        $gameData = $this->mock_KingArthur_game_data();
        $gameData['gameId'] = 13;
        $gameData['waitingOnActionArray'] = array(TRUE, TRUE);
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => 0,
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_14() {
        $gameData = $this->mock_KingArthur_game_data();
        $gameData['gameId'] = 14;
        $gameData['waitingOnActionArray'] = array(FALSE, TRUE);
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => 0,
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_15() {
        $gameData = $this->mock_KingArthur_game_data();
        $gameData['gameId'] = 15;
        $gameData['waitingOnActionArray'] = array(TRUE, TRUE);
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => FALSE,
            'playerNameArray' => array('tester2', 'tester3'),
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_16() {
        $gameData = $this->mock_CammyNeko_game_data();
        $gameData['gameId'] = 16;
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => 0,
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_17() {
        $gameData = $this->mock_CammyNeko_game_data();
        $gameData['gameId'] = 17;
        $gameData['waitingOnActionArray'] = array(FALSE, TRUE);
        $gameData['gameScoreArrayArray'] =
            array(
                array("W" => 1, "L" => 0, "D" => 0),
                array("W" => 0, "L" => 1, "D" => 0)
            );
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => 0,
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_18() {
        $gameData = $this->mock_CammyNeko_game_data();
        $gameData['gameId'] = 18;
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => FALSE,
            'playerNameArray' => array('tester2', 'tester3'),
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function load_game_data_19() {
        $gameData = $this->mock_Apples_game_data();
        $gameData['gameId'] = 19;
        $data = array(
            'gameData' => array(
                "status" => "ok",
                "data" => $gameData,
            ),
            'currentPlayerIdx' => 0,
            'gameActionLog' => array(),
            'gameChatLog' => array(),
        );

        return $data;
    }

    protected function get_interface_response_loadPlayerName() {
        return array(array('userName' => 'tester1'), NULL);
    }

    protected function get_interface_response_loadPlayerInfo() {
        $playerInfoArray = array('id' => 1,
                                'name_ingame' => 'tester1',
                                'name_irl' => '',
                                'email' => 'tester1@example.com',
                                'status' => 'active',
                                'dob' => NULL,
                                'autopass' => TRUE,
                                'comment' => NULL,
                                'last_action_time' => 0,
                                'last_access_time' => 0,
                                'creation_time' => 1388193734,
                                'fanatic_button_id' => 0,
                                'n_games_won' => 0,
                                'n_games_lost' => 0,
                               );

        return array(array('user_prefs' => $playerInfoArray), NULL);
    }

    protected function get_interface_response_savePlayerInfo() {
        return array(array('playerId' => 1), 'Player info updated successfully.');
    }

    protected function get_interface_response_loadProfileInfo($args) {
        $profileInfoArray = array(
            'id' => 3,
            'name_ingame' => $args['playerName'],
            'name_irl' => 'Test User',
            'email' => NULL,
            'dob_month' => 2,
            'dob_day' => 29,
            'comment' => '',
            'last_access_time' => 0,
            'creation_time' => 0,
            'fanatic_button_id' => 0,
            'n_games_won' => 0,
            'n_games_lost' => 0,
        );

        return array(array('profile_info' => $profileInfoArray), NULL);
    }

    protected function get_interface_response_loadPlayerNames() {
        $data = array(
            'nameArray' => array(),
            'statusArray' => array(),
        );

        // three test players exist and are all active
        $data['nameArray'][] = 'tester1';
        $data['statusArray'][] = 'active';
        $data['nameArray'][] = 'tester2';
        $data['statusArray'][] = 'active';
        $data['nameArray'][] = 'tester3';
        $data['statusArray'][] = 'active';

        return array($data, "Names retrieved successfully.");
    }

    protected function get_interface_response_submitDieValues() {
        return array(TRUE, 'Successfully set die sizes');
    }

    protected function get_interface_response_reactToInitiative() {
        return array(array('gainedInitiative' => TRUE),
                     'Successfully gained initiative');
    }

    protected function get_interface_response_reactToAuxiliary() {
        return array(TRUE, 'Auxiliary die chosen successfully');
    }

    protected function get_interface_response_reactToReserve() {
        return array(TRUE, 'Reserve die chosen successfully');
    }

    protected function get_interface_response_submitChat($args) {
        if (array_key_exists('edit', $args)) {
            if ($args['chat']) {
                return array(TRUE, 'Updated previous game message');
            } else {
                return array(TRUE, 'Deleted previous game message');
            }
        } else {
            if ($args['chat']) {
                return array(TRUE, 'Added game message');
            } else {
                return array(FALSE, 'No game message specified');
            }
        }
    }

    protected function get_interface_response_submitTurn() {
        return array(TRUE, 'Dummy turn submission accepted');
    }

    protected function get_interface_response_login() {
//            $login_success = login($_POST['username'], $_POST['password']);
//            if ($login_success) {
//                $data = array('userName' => $_POST['username']);
//            } else {
//                $data = NULL;
//            }
        return array(NULL, "function not implemented");
    }

    protected function get_interface_response_logout() {
//            logout();
//            $data = array('userName' => FALSE);
        return array(NULL, "function not implemented");
    }

    // Ask get_interface_response() for the dummy response to the
    // request, then construct a response.  Match the logic in
    // responder as closely as possible for convenience.
    // * For live (remote) invocation:
    //   * display the output to the user
    // * For test invocation:
    //   * return the output as a PHP variable
    public function process_request($args) {

        // make sure all arguments passed to the function are
        // syntactically reasonable, using the same ApiSpec used
        // by the real responder
        $argcheck = $this->spec->verify_function_args($args);
        if ($argcheck['ok']) {

            // As far as we can easily tell, arguments are okay.
            // Pass them along to the dummy responder functions.
            $retval = $this->get_interface_response($args);
            $data = $retval[0];
            $message = $retval[1];

            $output = array(
                'data' => $data,
                'message' => $message,
            );
            if ($data) {
                $output['status'] = 'ok';
            } else {
                $output['status'] = 'failed';
            }
        } else {
            $output = array(
                'data' => NULL,
                'status' => 'failed',
                'message' => $argcheck['message'],
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
