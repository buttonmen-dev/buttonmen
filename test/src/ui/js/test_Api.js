module("Api", {
  'setup': function() {
    BMTestUtils.ApiPre = BMTestUtils.getAllElements();
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create
    delete Api.test_data;
    delete Api.button;
    delete Api.buttonSet;
    delete Api.player;
    delete Api.new_games;
    delete Api.active_games;
    delete Api.completed_games;
    delete Api.rejected_games;
    delete Api.user_prefs;
    delete Api.game;
    delete Api.gameNavigation;
    delete Api.forumNavigation;
    delete Api.game_history;
    delete Api.siteConfig;
    delete Api.forum_overview;
    delete Api.forum_board;
    delete Api.forum_thread;
    delete Api.open_games;
    delete Api.join_game_result;
    delete Api.active_players;
    delete Api.profile_info;
    delete Api.pending_games;
    delete Env.message;
    BMTestUtils.deleteEnvMessage();

    Api.automatedApiCall = false;

    // Page elements (for test use only)
    $('#api_page').remove();

    // Fail if any other elements were added or removed
    BMTestUtils.ApiPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.ApiPost, BMTestUtils.ApiPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Api module has been loaded
test("test_Api_is_loaded", function(assert) {
  expect(3); // number of tests plus 2 for the teardown test
  assert.ok(Api, "The Api namespace exists");
});

test("test_Api.parseApiPost_automatedApiCall", function(assert) {
  stop(2); // this test has two async calls that need to resolve separately
  expect(4); // tests + 2 teardown

  Api.automatedApiCall = true;

  // Api.getGameData calls Api.parseApiPost
  var gameId = BMTestUtils.testGameId('frasquito_wiseman_specifydice');
  Api.getGameData(gameId, 10, function() {
    assert.equal(Api.game.load_status, 'ok',
      'getGameData should be a valid automated API call');
    start();
  });

  // Api.getUserPrefsData calls Api.parseApiPost
  Api.getUserPrefsData(function() {
    assert.notEqual(Api.game.user_prefs, 'ok',
      'getUserPrefsData should not be a valid automated API call');
    start();
  });

});

test("test_Api.parseGenericData", function(assert) {
  expect(3); // number of tests plus 2 for the teardown test

  var apiKey = 'test_data';
  Api[apiKey] = { };
  var data = { 'value': 37 };
  Api.parseGenericData(data, apiKey);
  assert.equal(Api.test_data.value, 37, 'Data value should be set on the Api object');
});

test("test_Api.verifyApiData", function(assert) {
  expect(4); // number of tests plus 2 for the teardown test

  var apiKey = 'test_data';

  Env.message = undefined;
  var message = undefined;
  Api.verifyApiData(apiKey, function(assert) {
    message = Env.message;
  });
  assert.equal(message.type, 'error', 'Should error if data is missing');

  Env.message = undefined;
  message = undefined;
  Api[apiKey] = { 'load_status': 'ok' };
  Api.verifyApiData(apiKey, function() {
    message = Env.message;
  });
  assert.equal(message, undefined, 'Should not error if data is present');
});

test("test_Api.getButtonData", function(assert) {
  stop();
  expect(6); // number of tests plus 2 for the teardown test
  Api.getButtonData(null, function() {
    assert.equal(Api.button.load_status, "ok", "Api.button.load_status should be ok");
    assert.equal(typeof Api.button.list, "object",
          "Api.button.list should be an object");
    assert.deepEqual(
      Api.button.list["Avis"],
      {
        'buttonId': 256,
        'buttonName': 'Avis',
        'hasUnimplementedSkill': false,
        'recipe': '(4) (4) (10) (12) (X)',
        'buttonSet': 'Soldiers',
        'dieTypes': [ 'X Swing', ],
        'dieSkills': [],
        'isTournamentLegal': true,
        'artFilename': 'avis.png',
        'tags': [ ],
      },
      "Button Avis should have correct contents");
    assert.deepEqual(Env.message, undefined,
              "Api.getButtonData should not set Env.message");
    start();
  });
});

test("test_Api.getPlayerData", function(assert) {
  stop();
  expect(6); // number of tests plus 2 for the teardown test
  Api.getPlayerData(function() {
    assert.equal(Api.player.load_status, "ok", "Api.player.load_status should be ok");
    assert.equal(typeof Api.player.list, "object",
          "Api.player.list should be an object");
    assert.deepEqual(
      Api.player.list["tester2"],
      {'status': 'ACTIVE', },
      "Player tester2 should have correct contents");
    assert.deepEqual(Env.message, undefined,
              "Api.getPlayerData should not set Env.message");
    start();
  });
});

test("test_Api.parseButtonData", function(assert) {
  expect(5); // number of tests plus 2 for the teardown test

  Api.button = {};
  var retval = Api.parseButtonData([
    {
      'buttonName': 'Avis',
      'recipe': '(4) (4) (10) (12) (X)',
      'hasUnimplementedSkill': false,
      'buttonSet': 'Soldiers',
      'dieTypes': [ 'X Swing', ],
      'dieSkills': [],
      'isTournamentLegal': true,
      'artFilename': 'avis.png',
    },
    {
      'buttonName': 'Adam Spam',
      'recipe': 'F(4) F(6) (6) (12) (X)',
      'hasUnimplementedSkill': true,
      'buttonSet': 'Polycon',
      'dieTypes': [ 'X Swing', ],
      'dieSkills': [ 'Fire', ],
      'isTournamentLegal': true,
      'artFilename': 'adamspam.png',
    },
    {
      'buttonName': 'Jellybean',
      'recipe': 'p(20) s(20) (V) (X)',
      'hasUnimplementedSkill': false,
      'buttonSet': 'BROM',
      'dieTypes': [ 'V Swing', 'X Swing', ],
      'dieSkills': [ 'Poison', 'Shadow', ],
      'isTournamentLegal': true,
      'artFilename': 'jellybean.png',
    },
  ]);
  assert.equal(retval, true, "Api.parseButtonData() returns true");
  assert.deepEqual(
    Api.button.list,
    { 'Adam Spam': {
        'buttonName': 'Adam Spam',
        'hasUnimplementedSkill': true,
        'recipe': 'F(4) F(6) (6) (12) (X)',
        'buttonSet': 'Polycon',
        'dieTypes': [ 'X Swing', ],
        'dieSkills': [ 'Fire', ],
        'isTournamentLegal': true,
        'artFilename': 'adamspam.png',
      },
      'Avis': {
        'buttonName': 'Avis',
        'hasUnimplementedSkill': false,
        'recipe': '(4) (4) (10) (12) (X)',
        'buttonSet': 'Soldiers',
        'dieTypes': [ 'X Swing', ],
        'dieSkills': [ ],
        'isTournamentLegal': true,
        'artFilename': 'avis.png',
      },
      'Jellybean': {
        'buttonName': 'Jellybean',
        'hasUnimplementedSkill': false,
        'recipe': 'p(20) s(20) (V) (X)',
        'buttonSet': 'BROM',
        'dieTypes': [ 'V Swing', 'X Swing', ],
        'dieSkills': [ 'Poison', 'Shadow', ],
        'isTournamentLegal': true,
        'artFilename': 'jellybean.png',
      }
  });
  assert.deepEqual(Env.message, undefined,
            "Api.parseButtonData should not set Env.message");
});

test("test_Api.parseButtonData_failure", function(assert) {
  Api.button = {};
  var retval = Api.parseButtonData({});
  assert.equal(retval, false, "Api.parseButtonData({}) returns false");
});

test("test_Api.getButtonSetData", function(assert) {
  stop();
  expect(5); // number of tests plus 2 for the teardown tests

  Api.getButtonSetData(null, function() {
    assert.equal(Api.buttonSet.load_status, "ok",
      "Api.buttonSet.load_status should be ok");
    assert.equal(typeof Api.buttonSet.list, "object",
      "Api.buttonSet.list should be an object");
    assert.deepEqual(Env.message, undefined,
      "Api.getButtonSetData should not set Env.message");
    start();
  });
});

test("test_Api.parseButtonSetData", function(assert) {
  Api.buttonSet = {};
  Api.parseButtonSetData([
    { 'setName': 'Lunch Money' },
    { 'setName': 'Soldiers' },
    { 'setName': 'The Big Cheese' },
    { 'setName': 'Vampyres' },
  ]);
  assert.deepEqual(
    Api.buttonSet.list,
    {
      'Lunch Money': { 'setName': 'Lunch Money' },
      'Soldiers': { 'setName': 'Soldiers' },
      'The Big Cheese': { 'setName': 'The Big Cheese' },
      'Vampyres': { 'setName': 'Vampyres' },
    },
    "Set list should have correct contents"
  );
});

test("test_Api.parsePlayerData", function(assert) {
  expect(5); // number of tests plus 2 for the teardown tests

  Api.player = {};
  var retval = Api.parsePlayerData({
    'nameArray': ['tester1', 'tester2', 'tester3' ],
    'statusArray': ['ACTIVE', 'ACTIVE', 'ACTIVE' ],
  });
  assert.equal(retval, true, "Api.parsePlayerData() returns true");
  assert.deepEqual(
    Api.player.list,
    { 'tester1': {'status': 'ACTIVE', },
      'tester2': {'status': 'ACTIVE', },
      'tester3': {'status': 'ACTIVE', }
    }
  );
  assert.deepEqual(Env.message, undefined,
            "Api.parseButtonData should not set Env.message");
});

test("test_Api.parsePlayerData_failure", function(assert) {
  Api.player = {};
  var retval = Api.parsePlayerData({});
  assert.equal(retval, false, "Api.parsePlayerData({}) returns false");
});

test("test_Api.getNewGamesData", function(assert) {
  stop();
  Api.getNewGamesData(function() {
    assert.equal(Api.new_games.load_status, 'ok',
         'Successfully loaded new games data');
    assert.equal(Api.new_games.nGames, 0, 'Got expected number of new games');
    start();
  });
});

test("test_Api.getActiveGamesData", function(assert) {
  stop();
  Api.getActiveGamesData(function() {
    assert.equal(Api.active_games.load_status, 'ok',
         'Successfully loaded active games data');
    assert.ok(Api.active_games.nGames > 0, 'Parsed some active games');
    assert.ok(Api.active_games.games.awaitingPlayer.length > 0,
          "Parsed some games as waiting for the active player");
    assert.ok(Api.active_games.nGames > Api.active_games.games.awaitingPlayer.length,
          "Parsed more active games than games waiting for the active player");
    start();
  });
});

test("test_Api.getCompletedGamesData", function(assert) {
  stop();
  Api.getCompletedGamesData(function() {
    assert.equal(Api.completed_games.load_status, 'ok',
         'Successfully loaded completed games data');
    assert.equal(Api.completed_games.nGames, 1, 'Got expected number of completed games');
    assert.equal(Api.completed_games.games[0].gameId, 5,
          "expected completed game ID exists");
    start();
  });
});

test("test_Api.getRejectedGamesData", function(assert) {
  stop();
  Api.getRejectedGamesData(function() {
    assert.equal(Api.rejected_games.load_status, 'ok',
         'Successfully loaded rejected games data');
    assert.equal(Api.rejected_games.nGames, 1, 'Got expected number of rejected games');
    assert.equal(Api.rejected_games.games[0].gameId, 505,
          "expected rejected game ID exists");
    start();
  });
});

test("test_Api.getUserPrefsData", function(assert) {
  stop();
  Api.getUserPrefsData(function() {
    assert.equal(Api.user_prefs.load_status, 'ok', "Successfully loaded user data");
    start();
  });
});

test("test_Api.parseUserPrefsData", function(assert) {
  stop();
  Api.getUserPrefsData(function() {
    assert.equal(Api.user_prefs.autopass, true, "Successfully parsed autopass value");
    start();
  });
});

test("test_Api.parseUserPrefsDataFireOvershooting", function(assert) {
  stop();
  Api.getUserPrefsData(function() {
    assert.equal(Api.user_prefs.fire_overshooting, false, "Successfully parsed fire overshooting value");
    start();
  });
});

test("test_Api.getGameData", function(assert) {
  stop();
  var gameId = BMTestUtils.testGameId('frasquito_wiseman_specifydice');
  Game.game = gameId;
  Api.getGameData(Game.game, 10, function() {
    assert.equal(Api.game.gameId, gameId, "parseGameData() parsed gameId from API data");
    assert.equal(Api.game.isParticipant, true, "parseGameData() set isParticipant based on API data");
    assert.equal(Api.game.playerIdx, 0, "parseGameData() set playerIdx based on API data");
    assert.equal(Api.game.opponentIdx, 1, "parseGameData() set opponentIdx based on API data");
    assert.equal(Api.game.activePlayerIdx, null, "parseGameData() parsed activePlayerIdx from API data");
    assert.equal(Api.game.playerWithInitiativeIdx, null, "parseGameData() parsed playerWithInitiativeIdx from API data");
    delete Game.game;
    start();
  });
});

test("test_Api.getGameData_nonplayer", function(assert) {
  stop();
  var gameId = BMTestUtils.testGameId('frasquito_wiseman_specifydice_nonplayer');
  Game.game = gameId;
  Api.getGameData(Game.game, 10, function() {
    assert.equal(Api.game.gameId, gameId,
          "parseGameData() set gameId for nonparticipant");
    delete Game.game;
    start();
  });
});

test("test_Api.getGameData_somelogs", function(assert) {
  stop();
  var gameId = BMTestUtils.testGameId('washu_hooloovoo_cant_win');
  Game.game = gameId;
  Api.getGameData(Game.game, 10, function() {
    assert.equal(Api.game.actionLog.length, 10, "getGameData() passed limited action log length");
    assert.equal(Api.game.chatLog.length, 10, "getGameData() passed limited chat log length");
    delete Game.game;
    start();
  });
});

// Technically, this is a cheat.  It's showing that when the backend
// sends the full logs, the Api object receives them, but it is not
// testing whether Api.getGameData() successfully sends the request
// for full logs, because the API response is canned, so it can't test that.
test("test_Api.getGameData_alllogs", function(assert) {
  stop();
  Game.game = BMTestUtils.testGameId('washu_hooloovoo_cant_win_fulllogs');
  Api.getGameData(Game.game, undefined, function() {
    assert.ok(Api.game.actionLog.length > 10, "getGameData() passed unlimited action log length");
    assert.ok(Api.game.chatLog.length > 10, "getGameData() passed unlimited chat log length");
    delete Game.game;
    start();
  });
});

// N.B. use Api.getGameData() to query dummy_responder, but
// test any details of parsePlayerData()'s processing here
test("test_Api.parseGamePlayerData", function(assert) {
  stop();
  var gameId = BMTestUtils.testGameId('frasquito_wiseman_specifydice');
  Game.game = gameId;
  Api.getGameData(Game.game, 10, function() {
    assert.ok(Api.game.player.playerId,
              "player ID should be set in API response");
    assert.deepEqual(Api.game.player.playerName, 'responder001',
              "player name should be parsed from API response");
    assert.deepEqual(Api.game.player.waitingOnAction, true,
              "'waiting on action' status should be parsed from API response");
    assert.deepEqual(Api.game.player.roundScore, null,
              "round score should be parsed from API response");
    assert.deepEqual(Api.game.player.sideScore, null,
              "side score should be parsed from API response");
    assert.deepEqual(Api.game.player.gameScoreArray, {'W': 0, 'L': 0, 'D': 0, },
              "game score array should be parsed from API response");
    assert.ok(Api.game.player.lastActionTime,
              "last action time is set in API response");
    assert.deepEqual(Api.game.player.hasDismissedGame, false,
              "'has dismissed game' should be parsed from API response");
    assert.deepEqual(Api.game.player.canStillWin, null,
              "'can still win' should be parsed from API response");
    assert.deepEqual(Api.game.player.button, {
                'name': 'Frasquito',
                'recipe': '(4) (6) (8) (12) (2/20)',
                'artFilename': 'BMdefaultRound.png',
              }, "recipe data should be parsed from API response");
    assert.deepEqual(Api.game.player.activeDieArray[0].description, '4-sided die',
              "die descriptions should be parsed");
    assert.deepEqual(Api.game.player.activeDieArray[4].recipe, '(2/20)',
              "player die recipe should be parsed correctly");
    assert.deepEqual(Api.game.player.capturedDieArray, [],
              "array of captured dice should be parsed");
    assert.deepEqual(
      Api.game.player.optRequestArray[4],
      ['2', '20'],
      "option request array should contain entry for (2/20)");
    delete Game.game;
    start();
  });
});

test("test_Api.parseGamePlayerData_option", function(assert) {
  stop();
  var gameId = BMTestUtils.testGameId('frasquito_wiseman_specifydice');
  Api.getGameData(gameId, 10, function() {
    assert.deepEqual(Api.game.player.swingRequestArray, {});
    assert.deepEqual(Api.game.player.optRequestArray, {
      4: ["2", "20"],
    });
    start();
  });
});

test("test_Api.disableSubmitButton", function(assert) {
  $('body').append($('<div>', {'id': 'api_page', }));
  $('#api_page').append($('<button>', {
    'id': 'api_action_button',
    'text': 'Submit',
  }));
  Api.disableSubmitButton('#api_action_button');
  var item = document.getElementById('api_action_button');
  assert.equal(item.getAttribute('disabled'), 'disabled',
        "After a submit button has been clicked, it should be disabled");
});

test("test_Api.getNextGameId", function(assert) {
  stop();
  Api.getNextGameId(
    function() {
      assert.equal(Api.gameNavigation.load_status, 'ok',
        'Successfully retrieved next game ID');
      start();
    });
});

test("test_Api.parseNextGameId", function(assert) {
  stop();
  Api.getNextGameId(function() {
    assert.equal(Api.gameNavigation.nextGameId, 7, "Successfully parsed next game ID");
    start();
  });
});

test("test_Api.parseNextGameId_skipping", function(assert) {
  stop();
  Api.game =
    {
      'gameId': 7,
      'isParticipant': true,
      'player': { 'waitingOnAction': true },
    };
  Api.getNextGameId(function() {
    assert.equal(Api.gameNavigation.nextGameId, 4, "Successfully parsed next game ID");
    start();
  });
});

test("test_Api.getNextNewPostId", function(assert) {
  stop();
  Api.getNextNewPostId(
    function() {
      assert.equal(Api.forumNavigation.load_status, 'ok',
        'Successfully retrieved next game ID');
      assert.equal(Api.forumNavigation.nextNewPostId, 3,
        'Retrieved correct next game ID');
      start();
    });
});

test("test_Api.getOpenGamesData", function(assert) {
  stop();
  Api.getOpenGamesData(
    function() {
      assert.equal(Api.open_games.load_status, 'ok',
        'Successfully retrieved open games');
      start();
    });
});

test("test_Api.loadForumOverview", function(assert) {
  stop();
  Api.loadForumOverview(
    function() {
      assert.equal(Api.forum_overview.load_status, 'ok',
        'Successfully loaded forum overview');
      start();
    });
});

test("test_Api.loadForumBoard", function(assert) {
  stop();
  Api.loadForumBoard(1,
    function() {
      assert.equal(Api.forum_board.load_status, 'ok',
        'Successfully loaded forum board');
      start();
    });
});

test("test_Api.loadForumThread", function(assert) {
  stop();
  Api.loadForumThread(1, 2,
    function() {
      assert.equal(Api.forum_thread.load_status, 'ok',
        'Successfully loaded forum overview');
      start();
    });
});

test("test_Api.getActivePlayers", function(assert) {
  stop();
  Api.getActivePlayers(50,
    function() {
      assert.equal(Api.active_players.load_status, 'ok',
        'Successfully retrieved active players');
      start();
    });
});

test("test_Api.parseActivePlayers", function(assert) {
  stop();
  Api.getActivePlayers(50,
    function() {
      assert.ok(Api.active_players.players.length,
        "Successfully parsed active players info");
      start();
    });
});

test("test_Api.loadProfileInfo", function(assert) {
  stop();
  Api.loadProfileInfo('tester',
    function() {
      assert.equal(Api.profile_info.load_status, 'ok',
        'Successfully retrieved profile info');
      start();
    });
});

test("test_Api.searchGameHistory", function(assert) {
  stop();
  var searchParameters = {
            'sortColumn': 'lastMove',
            'sortDirection': 'DESC',
            'numberOfResults': '20',
            'page': '1',
            'playerNameA': 'tester',
            'status': 'COMPLETE',
  };

  Api.searchGameHistory(searchParameters,
    function() {
      assert.equal(Api.game_history.load_status, 'ok',
        'Successfully performed search');
    start();
  });
});

test("test_Api.parseOpenGames", function(assert) {
  stop();
  Api.getOpenGamesData(function() {
    assert.ok(Api.open_games.games.length > 0, "Successfully parsed open games");
    start();
  });
});

test("test_Api.joinOpenGame", function(assert) {
  stop();
  Api.joinOpenGame(4400, 'Avis',
    function() {
      assert.equal(Api.join_game_result.load_status, 'ok',
        'Successfully retrieved open games');
      start();
    },
    function() {
      assert.ok(false, 'Retrieving game data should succeed');
      start();
    });
});

test("test_Api.parseJoinGameResult", function(assert) {
  stop();
  Api.joinOpenGame(4400, 'Avis',
    function() {
      assert.equal(Api.join_game_result.success, true,
        "Successfully parsed join game result");
      start();
    },
    function() {
      assert.ok(false, 'Retrieving game data should succeed');
      start();
    });
});

test("test_Api.parseNextGameId", function(assert) {
  stop();
  Api.loadProfileInfo('tester',
    function() {
      assert.equal(Api.profile_info.name_ingame, 'tester',
        "Successfully parsed profile info");
      start();
    });
});


test("test_Api.parseSearchResults_games", function(assert) {
  stop();
  var searchParameters = {
            'sortColumn': 'lastMove',
            'sortDirection': 'DESC',
            'numberOfResults': '20',
            'page': '1',
            'playerNameA': 'tester',
            'status': 'COMPLETE',
  };

  Api.searchGameHistory(searchParameters, function() {
    assert.ok(Api.game_history.games.length > 0,
      "Successfully parsed search results games list");
    start();
  });
});

test("test_Api.parseSearchResults_summary", function(assert) {
  stop();
  var searchParameters = {
            'sortColumn': 'lastMove',
            'sortDirection': 'DESC',
            'numberOfResults': '20',
            'page': '1',
            'playerNameA': 'tester2',
            'buttonNameA': 'Avis',
  };

  Api.searchGameHistory(searchParameters, function() {
    assert.ok(Api.game_history.summary.matchesFound > 0,
      "Successfully parsed search results summary data");
    start();
  });
});

test("test_Api.getPendingGameCount", function(assert) {
  stop();
  Api.getPendingGameCount(function() {
    assert.equal(Api.pending_games.load_status, 'ok',
      "Successfully retrieved count of pending games");
    assert.ok(typeof Api.pending_games.count === 'number',
      "Successfully retrieved valid count of pending games");
    start();
  });
});
