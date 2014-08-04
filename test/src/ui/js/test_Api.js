module("Api", {
  'setup': function() {
    BMTestUtils.ApiPre = BMTestUtils.getAllElements();
  },
  'teardown': function() {

    // Delete all elements we expect this module to create
    delete Api.test_data;
    delete Api.button;
    delete Api.player;
    delete Api.active_games;
    delete Api.completed_games;
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

    // Page elements (for test use only)
    $('#api_page').remove();
    $('#api_page').empty();

    // Fail if any other elements were added or removed
    BMTestUtils.ApiPost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.ApiPost, BMTestUtils.ApiPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Api module has been loaded
test("test_Api_is_loaded", function() {
  expect(2); // number of tests plus 1 for the teardown test
  ok(Api, "The Api namespace exists");
});

test("test_Api.parseGenericData", function() {
  expect(2); // number of tests plus 1 for the teardown test

  var apiKey = 'test_data';
  Api[apiKey] = { };
  var data = { 'value': 37 };
  Api.parseGenericData(data, apiKey);
  equal(Api.test_data.value, 37, 'Data value should be set on the Api object');
});

test("test_Api.verifyApiData", function() {
  expect(3); // number of tests plus 1 for the teardown test

  var apiKey = 'test_data';

  Env.message = undefined;
  var message = undefined;
  Api.verifyApiData(apiKey, function() {
    message = Env.message;
  });
  equal(message.type, 'error', 'Should error if data is missing');

  Env.message = undefined;
  message = undefined;
  Api[apiKey] = { 'load_status': 'ok' };
  Api.verifyApiData(apiKey, function() {
    message = Env.message;
  });
  equal(message, undefined, 'Should not error if data is present');
});

asyncTest("test_Api.getButtonData", function() {
  expect(5); // number of tests plus 1 for the teardown test
  Api.getButtonData(null, function() {
    equal(Api.button.load_status, "ok", "Api.button.load_status should be ok");
    equal(typeof Api.button.list, "object",
          "Api.button.list should be an object");
    deepEqual(
      Api.button.list["Avis"],
      {
        'buttonName': 'Avis',
        'hasUnimplementedSkill': false,
        'recipe': '(4) (4) (10) (12) (X)',
        'buttonSet': 'Soldiers',
        'dieSkills': [],
        'isTournamentLegal': true,
      },
      "Button Avis should have correct contents");
    deepEqual(Env.message, undefined,
              "Api.getButtonData should not set Env.message");
    start();
  });
});

asyncTest("test_Api.getPlayerData", function() {
  expect(5); // number of tests plus 1 for the teardown test
  Api.getPlayerData(function() {
    equal(Api.player.load_status, "ok", "Api.player.load_status should be ok");
    equal(typeof Api.player.list, "object",
          "Api.player.list should be an object");
    deepEqual(
      Api.player.list["tester2"],
      {'status': 'active', },
      "Player tester2 should have correct contents");
    deepEqual(Env.message, undefined,
              "Api.getPlayerData should not set Env.message");
    start();
  });
});

test("test_Api.parseButtonData", function() {
  expect(4); // number of tests plus 1 for the teardown test

  Api.button = {};
  var retval = Api.parseButtonData([
    {
      'buttonName': 'Avis',
      'recipe': '(4) (4) (10) (12) (X)',
      'hasUnimplementedSkill': false,
      'buttonSet': 'Soldiers',
      'dieSkills': [],
      'isTournamentLegal': true,
    },
    {
      'buttonName': 'Adam Spam',
      'recipe': 'F(4) F(6) (6) (12) (X)',
      'hasUnimplementedSkill': true,
      'buttonSet': 'Polycon',
      'dieSkills': [ 'Fire', ],
      'isTournamentLegal': true,
    },
    {
      'buttonName': 'Jellybean',
      'recipe': 'p(20) s(20) (V) (X)',
      'hasUnimplementedSkill': false,
      'buttonSet': 'BROM',
      'dieSkills': [ 'Poison', 'Shadow', ],
      'isTournamentLegal': true,
    },
  ]);
  equal(retval, true, "Api.parseButtonData() returns true");
  deepEqual(
    Api.button.list,
    { 'Adam Spam': {
        'buttonName': 'Adam Spam',
        'hasUnimplementedSkill': true,
        'recipe': 'F(4) F(6) (6) (12) (X)',
        'buttonSet': 'Polycon',
        'dieSkills': [ 'Fire', ],
        'isTournamentLegal': true,
      },
      'Avis': {
        'buttonName': 'Avis',
        'hasUnimplementedSkill': false,
        'recipe': '(4) (4) (10) (12) (X)',
        'buttonSet': 'Soldiers',
        'dieSkills': [ ],
        'isTournamentLegal': true,
      },
      'Jellybean': {
        'buttonName': 'Jellybean',
        'hasUnimplementedSkill': false,
        'recipe': 'p(20) s(20) (V) (X)',
        'buttonSet': 'BROM',
        'dieSkills': [ 'Poison', 'Shadow', ],
        'isTournamentLegal': true,
      }
  });
  deepEqual(Env.message, undefined,
            "Api.parseButtonData should not set Env.message");
});

test("test_Api.parseButtonData_failure", function() {
  Api.button = {};
  var retval = Api.parseButtonData({});
  equal(retval, false, "Api.parseButtonData({}) returns false");
});

test("test_Api.parsePlayerData", function() {
  expect(4); // number of tests plus 1 for the teardown test

  Api.player = {};
  var retval = Api.parsePlayerData({
    'nameArray': ['tester1', 'tester2', 'tester3' ],
    'statusArray': ['active', 'active', 'active' ],
  });
  equal(retval, true, "Api.parsePlayerData() returns true");
  deepEqual(
    Api.player.list,
    { 'tester1': {'status': 'active', },
      'tester2': {'status': 'active', },
      'tester3': {'status': 'active', }
    }
  );
  deepEqual(Env.message, undefined,
            "Api.parseButtonData should not set Env.message");
});

test("test_Api.parsePlayerData_failure", function() {
  Api.player = {};
  var retval = Api.parsePlayerData({});
  equal(retval, false, "Api.parsePlayerData({}) returns false");
});

asyncTest("test_Api.getActiveGamesData", function() {
  Api.getActiveGamesData(function() {
    equal(Api.active_games.load_status, 'ok',
         'Successfully loaded active games data');
    equal(Api.active_games.nGames, 15, 'Got expected number of active games');
    start();
  });
});

asyncTest("test_Api.parseActiveGamesData", function() {
  Api.getActiveGamesData(function() {
    equal(Api.active_games.games.awaitingPlayer.length, 9,
          "expected number of games parsed as waiting for the active player");
    start();
  });
});

asyncTest("test_Api.getCompletedGamesData", function() {
  Api.getCompletedGamesData(function() {
    equal(Api.completed_games.load_status, 'ok',
         'Successfully loaded completed games data');
    equal(Api.completed_games.nGames, 1, 'Got expected number of completed games');
    start();
  });
});

asyncTest("test_Api.parseCompletedGamesData", function() {
  Api.getCompletedGamesData(function() {
    equal(Api.completed_games.games[0].gameId, 5,
          "expected completed game ID exists");
    start();
  });
});

asyncTest("test_Api.getUserPrefsData", function() {
  Api.getUserPrefsData(function() {
    equal(Api.user_prefs.load_status, 'ok', "Successfully loaded user data");
    start();
  });
});

asyncTest("test_Api.parseUserPrefsData", function() {
  Api.getUserPrefsData(function() {
    equal(Api.user_prefs.autopass, true, "Successfully parsed autopass value");
    start();
  });
});

asyncTest("test_Api.getGameData", function() {
  Game.game = '1';
  Api.getGameData(Game.game, 10, function() {
    equal(Api.game.gameId, '1', "parseGameData() parsed gameId from API data");
    equal(Api.game.isParticipant, true, "parseGameData() set isParticipant based on API data");
    equal(Api.game.playerIdx, 0, "parseGameData() set playerIdx based on API data");
    equal(Api.game.opponentIdx, 1, "parseGameData() set opponentIdx based on API data");
    equal(Api.game.activePlayerIdx, null, "parseGameData() parsed activePlayerIdx from API data");
    equal(Api.game.playerWithInitiativeIdx, null, "parseGameData() parsed playerWithInitiativeIdx from API data");
    delete Game.game;
    start();
  });
});

asyncTest("test_Api.getGameData_nonplayer", function() {
  Game.game = '10';
  Api.getGameData(Game.game, 10, function() {
    equal(Api.game.gameId, '10',
          "parseGameData() set gameId for nonparticipant");
    delete Game.game;
    start();
  });
});

asyncTest("test_Api.getGameData_somelogs", function() {
  Game.game = '3';
  Api.getGameData(Game.game, 3, function() {
    equal(Api.game.actionLog.length, 3, "getGameData() passed limited action log length");
    equal(Api.game.chatLog.length, 3, "getGameData() passed limited chat log length");
    delete Game.game;
    start();
  });
});

asyncTest("test_Api.getGameData_alllogs", function() {
  Game.game = '3';
  Api.getGameData(Game.game, 0, function() {
    ok(Api.game.actionLog.length > 3, "getGameData() passed unlimited action log length");
    ok(Api.game.chatLog.length > 3, "getGameData() passed unlimited chat log length");
    delete Game.game;
    start();
  });
});

// N.B. use Api.getGameData() to query dummy_responder, but
// test any details of parsePlayerData()'s processing here
asyncTest("test_Api.parseGamePlayerData", function() {
  Game.game = '1';
  Api.getGameData(Game.game, 10, function() {
    deepEqual(Api.game.player.playerId, 1,
              "player ID should be parsed from API response");
    deepEqual(Api.game.player.playerName, 'tester1',
              "player name should be parsed from API response");
    deepEqual(Api.game.player.waitingOnAction, true,
              "'waiting on action' status should be parsed from API response");
    deepEqual(Api.game.player.roundScore, null,
              "round score should be parsed from API response");
    deepEqual(Api.game.player.sideScore, null,
              "side score should be parsed from API response");
    deepEqual(Api.game.player.gameScoreArray, {'W': 0, 'L': 0, 'D': 0, },
              "game score array should be parsed from API response");
    deepEqual(Api.game.player.lastActionTime, 0,
              "last action time should be parsed from API response");
    deepEqual(Api.game.player.canStillWin, null,
              "'can still win' should be parsed from API response");
    deepEqual(Api.game.player.button, {
                'name': 'Avis',
                'recipe': '(4) (4) (10) (12) (X)',
                'artFilename': 'avis.png',
              }, "recipe data should be parsed from API response");
    deepEqual(Api.game.player.activeDieArray[0].description, '4-sided die',
              "die descriptions should be parsed");
    deepEqual(Api.game.player.activeDieArray[4].recipe, '(X)',
              "player die recipe should be parsed correctly");
    deepEqual(Api.game.player.capturedDieArray, [],
              "array of captured dice should be parsed");
    deepEqual(
      Api.game.player.swingRequestArray['X'],
      {'min': 4, 'max': 20},
      "swing request array should contain X entry with correct min/max");
    delete Game.game;
    start();
  });
});

asyncTest("test_Api.parseGamePlayerData_option", function() {
  Game.game = '19';
  Api.getGameData(Game.game, 10, function() {
    deepEqual(Api.game.player.swingRequestArray, {});
    deepEqual(Api.game.player.optRequestArray, {
      2: [2, 12],
      3: [8, 16],
      4: [20, 24],
    });
    delete Game.game;
    start();
  });
});

asyncTest("test_Api.playerWLTText", function() {
  Api.getGameData('5', 10, function() {
    var text = Api.playerWLTText('opponent');
    ok(text.match('2/3/0'),
       "opponent WLT text should contain opponent's view of WLT state");
    start();
  });
});

test("test_Api.disableSubmitButton", function() {
  $('body').append($('<div>', {'id': 'api_page', }));
  $('#api_page').append($('<button>', {
    'id': 'api_action_button',
    'text': 'Submit',
  }));
  Api.disableSubmitButton('api_action_button');
  var item = document.getElementById('api_action_button');
  equal(item.getAttribute('disabled'), 'disabled',
        "After a submit button has been clicked, it should be disabled");
});

asyncTest("test_Api.getNextGameId", function() {
  Api.getNextGameId(
    function() {
      equal(Api.gameNavigation.load_status, 'ok',
        'Successfully retrieved next game ID');
      start();
    });
});

asyncTest("test_Api.parseNextGameId", function() {
  Api.getNextGameId(function() {
    equal(Api.gameNavigation.nextGameId, 7, "Successfully parsed next game ID");
    start();
  });
});

asyncTest("test_Api.parseNextGameId_skipping", function() {
  Api.game =
    {
      'gameId': 7,
      'isParticipant': true,
      'player': { 'waitingOnAction': true },
    };
  Api.getNextGameId(function() {
    equal(Api.gameNavigation.nextGameId, 4, "Successfully parsed next game ID");
    start();
  });
});

asyncTest("test_Api.getNextNewPostId", function() {
  Api.getNextNewPostId(
    function() {
      equal(Api.forumNavigation.load_status, 'ok',
        'Successfully retrieved next game ID');
      equal(Api.forumNavigation.nextNewPostId, 3,
        'Retrieved correct next game ID');
      start();
    });
});

asyncTest("test_Api.getOpenGamesData", function() {
  Api.getOpenGamesData(
    function() {
      equal(Api.open_games.load_status, 'ok',
        'Successfully retrieved open games');
      start();
    });
});

asyncTest("test_Api.loadForumOverview", function() {
  Api.loadForumOverview(
    function() {
      equal(Api.forum_overview.load_status, 'ok',
        'Successfully loaded forum overview');
      start();
    });
});

asyncTest("test_Api.loadForumBoard", function() {
  Api.loadForumBoard(1,
    function() {
      equal(Api.forum_board.load_status, 'ok',
        'Successfully loaded forum board');
      start();
    });
});

asyncTest("test_Api.loadForumThread", function() {
  Api.loadForumThread(1, 2,
    function() {
      equal(Api.forum_thread.load_status, 'ok',
        'Successfully loaded forum overview');
      start();
    });
});

asyncTest("test_Api.getActivePlayers", function() {
  Api.getActivePlayers(20,
    function() {
      equal(Api.active_players.load_status, 'ok',
        'Successfully retrieved active players');
      start();
    });
});

asyncTest("test_Api.parseActivePlayers", function() {
  Api.getActivePlayers(20,
    function() {
      ok(Api.active_players.players.length,
        "Successfully parsed active players info");
      start();
    });
});

asyncTest("test_Api.loadProfileInfo", function() {
  Api.loadProfileInfo('tester',
    function() {
      equal(Api.profile_info.load_status, 'ok',
        'Successfully retrieved profile info');
      start();
    });
});

asyncTest("test_Api.searchGameHistory", function() {
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
      equal(Api.game_history.load_status, 'ok',
        'Successfully performed search');
    start();
  });
});

asyncTest("test_Api.parseOpenGames", function() {
  Api.getOpenGamesData(function() {
    ok(Api.open_games.games.length > 0, "Successfully parsed open games");
    start();
  });
});

asyncTest("test_Api.joinOpenGame", function() {
  Api.joinOpenGame(21, 'Avis',
    function() {
      equal(Api.join_game_result.load_status, 'ok',
        'Successfully retrieved open games');
      start();
    },
    function() {
      ok(false, 'Retrieving game data should succeed');
      start();
    });
});

asyncTest("test_Api.parseJoinGameResult", function() {
  Api.joinOpenGame(21, 'Avis',
    function() {
      equal(Api.join_game_result.success, true,
        "Successfully parsed join game result");
      start();
    },
    function() {
      ok(false, 'Retrieving game data should succeed');
      start();
    });
});

asyncTest("test_Api.parseNextGameId", function() {
  Api.loadProfileInfo('tester',
    function() {
      equal(Api.profile_info.name_ingame, 'tester',
        "Successfully parsed profile info");
      start();
    });
});


asyncTest("test_Api.parseSearchResults_games", function() {
  var searchParameters = {
            'sortColumn': 'lastMove',
            'sortDirection': 'DESC',
            'numberOfResults': '20',
            'page': '1',
            'playerNameA': 'tester',
            'status': 'COMPLETE',
  };

  Api.searchGameHistory(searchParameters, function() {
    equal(Api.game_history.games.length, 1,
      "Successfully parsed search results games list");
    start();
  });
});

asyncTest("test_Api.parseSearchResults_summary", function() {
  var searchParameters = {
            'sortColumn': 'lastMove',
            'sortDirection': 'DESC',
            'numberOfResults': '20',
            'page': '1',
            'playerNameA': 'tester2',
  };

  Api.searchGameHistory(searchParameters, function() {
    equal(Api.game_history.summary.matchesFound, 2,
      "Successfully parsed search results summary data");
    start();
  });
});

asyncTest("test_Api.getPendingGameCount", function() {
  Api.getPendingGameCount(function() {
    equal(Api.pending_games.load_status, 'ok',
      "Successfully retrieved count of pending games");
    ok(typeof Api.pending_games.count === 'number',
      "Successfully retrieved valid count of pending games");
    start();
  });
});
