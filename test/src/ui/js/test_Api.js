module("Api", {
  'setup': function() {
    BMTestUtils.ApiPre = BMTestUtils.getAllElements();
  },
  'teardown': function() {

    // Delete all elements we expect this module to create
    delete Api.button;
    delete Api.player;
    delete Api.active_games;
    delete Api.completed_games;
    delete Api.user_prefs;
    delete Api.game;
    delete Api.gameNavigation;
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

asyncTest("test_Api.getButtonData", function() {
  expect(5); // number of tests plus 1 for the teardown test
  Api.getButtonData(function() {
    equal(Api.button.load_status, "ok", "Api.button.load_status should be ok");
    equal(typeof Api.button.list, "object",
          "Api.button.list should be an object");
    deepEqual(
      Api.button.list["Avis"],
      {'hasUnimplementedSkill': false, 'recipe': '(4) (4) (10) (12) (X)',},
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
  var retval = Api.parseButtonData({
    'buttonNameArray': ['Avis', 'Adam Spam', 'Jellybean' ],
    'recipeArray': ['(4) (4) (10) (12) (X)',
                    'F(4) F(6) (6) (12) (X)',
                    'p(20) s(20) (V) (X)' ],
    'hasUnimplementedSkillArray': [ false, true, false ]
  });
  equal(retval, true, "Api.parseButtonData() returns true");
  deepEqual(
    Api.button.list,
    { 'Adam Spam': {
        'hasUnimplementedSkill': true,
        'recipe': 'F(4) F(6) (6) (12) (X)',
      },
      'Avis': {
        'hasUnimplementedSkill': false,
        'recipe': '(4) (4) (10) (12) (X)',
      },
      'Jellybean': {
        'hasUnimplementedSkill': false,
        'recipe': 'p(20) s(20) (V) (X)'
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
    equal(Api.active_games.nGames, 13, 'Got expected number of active games');
    start();
  });
});

asyncTest("test_Api.parseActiveGamesData", function() {
  Api.getActiveGamesData(function() {
    equal(Api.active_games.games.awaitingPlayer.length, 7,
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
    equal(Api.game.gameId, '1', "parseGameData() set gameId");
    equal(Api.game.opponentIdx, 1, "parseGameData() set opponentIdx");
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
    deepEqual(Api.game.player.dieRecipeArray, ["(4)","(4)","(10)","(12)","(X)"],
              "player die recipe array should be parsed correctly");
    deepEqual(Api.game.player.capturedValueArray, [],
              "array of captured dice should be parsed");
    deepEqual(Api.game.player.dieDescriptionArray[0], '4-sided die',
              "array of die descriptions should be parsed");
    deepEqual(
      Api.game.player.swingRequestArray['X'],
      {'min': 4, 'max': 20},
      "swing request array should contain X entry with correct min/max");
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
