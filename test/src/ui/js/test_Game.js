module("Game", {
  'setup': function() {
    BMTestUtils.GamePre = BMTestUtils.getAllElements();

    // Override Env.getParameterByName to set the game
    Env.getParameterByName = function(name) {
      if (name == 'game') {
        if (BMTestUtils.GameType == 'newgame') { return '1'; }
        if (BMTestUtils.GameType == 'swingset') { return '2'; }
      }
    }

    // Create the game_page div so functions have something to modify
    if (document.getElementById('game_page') == null) {
      $('body').append($('<div>', {'id': 'game_page', }));
    }
  },
  'teardown': function() {

    // Delete all elements we expect this module to create

    // JavaScript variables
    delete Game.api;
    delete Game.game;
    delete Game.page;
    delete Game.form;

    // Page elements
    // FIXME: why do we have to remove this twice?
    $('#game_page').remove();
    $('#game_page').remove();
    $('#game_page').empty();

    BMTestUtils.deleteEnvMessage();

    // Fail if any other elements were added or removed
    BMTestUtils.GamePost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.GamePost, BMTestUtils.GamePre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Game module has been loaded
test("test_Game_is_loaded", function() {
  ok(Game, "The Game namespace exists");
});

asyncTest("test_Game.showGamePage", function() {
  BMTestUtils.GameType = 'newgame';
  Game.showGamePage();
  var item = document.getElementById('game_page');
  equal(item.nodeName, "DIV",
        "#game_page is a div after showGamePage() is called");
  start();
});

// N.B. Almost all of these tests should use asyncTest, set a test
// game type, and invoke Game.getCurrentGame(), because that's the
// way to get the dummy responder data which all the other functions
// need.  Then run tests against the function itself.  So the typical
// format will be:
//
// asyncTest("test_Game.someFunction", function() {
//   BMTestUtils.GameType = '<sometype>';
//   Game.getCurrentGame(function() {
//     <setup any additional prereqs for someFunction>
//     Game.someFunction();
//     <run tests against state changes made by someFunction>
//     start();
//   });
// });

asyncTest("test_Game.getCurrentGame", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    equal(Game.game, '1', "Set expected game number");
    equal(Game.api.load_status, 'ok', 'Successfully loaded game data');
    equal(Game.api.gameId, Game.game, 'Parsed correct game number from API');
    start();
  });
});

asyncTest("test_Game.showStatePage", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

asyncTest("test_Game.layoutPage", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {

    $('body').append($('<div>', {'id': 'game_page', }));
    Game.page = $('<div>');
    Game.page.append($('<p>', {'text': 'hi world', }));
    Game.layoutPage();
    var item = document.getElementById('game_page');
    equal(item.nodeName, "DIV",
          "#game_page is a div after layoutPage() is called");
//    var htmlout = Game.page.html();
//    ok(htmlout.length > 0,
//       "The created page should have nonzero contents");
    start();
  });
});

asyncTest("test_Game.parseGameData", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    equal(Game.parseGameData(false, ["tester1", "tester2"]), false,
          "parseGameData() fails if currentPlayerIdx is not set");
    equal(Game.api.gameId, '1', "parseGameData() set gameId");
    equal(Game.api.opponentIdx, 1, "parseGameData() set opponentIdx");
    start();
  });
});

// N.B. use Game.getCurrentGame() to query dummy_responder, but
// test any details of parsePlayerData()'s processing here
asyncTest("test_Game.parsePlayerData", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    deepEqual(Game.api.player.dieRecipeArray, ["(4)","(4)","(10)","(12)","(X)"],
              "player die recipe array should be parsed correctly");
    start();
  });
});

asyncTest("test_Game.actionChooseSwingActive", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.actionChooseSwingActive();
    item = document.getElementById('swing_table');
    equal(item.nodeName, "TABLE",
          "#swing_table is a table after actionChooseSwingActive() is called");
    start();
  });
});

asyncTest("test_Game.actionChooseSwingInactive", function() {
  BMTestUtils.GameType = 'swingset';
  Game.getCurrentGame(function() {
    Game.actionChooseSwingInactive();
    item = document.getElementById('swing_table');
    equal(item, null, "#swing_table is NULL");
    equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

test("test_Game.actionPlayTurnActive", function() {
  ok(null, "Test of Game.actionPlayTurnActive not implemented");
});

test("test_Game.actionPlayTurnInactive", function() {
  ok(null, "Test of Game.actionPlayTurnInactive not implemented");
});

test("test_Game.actionShowFinishedGame", function() {
  ok(null, "Test of Game.actionShowFinishedGame not implemented");
});

test("test_Game.formChooseSwingActive", function() {
  ok(null, "Test of Game.formChooseSwingActive not implemented");
});

test("test_Game.formPlayTurnActive", function() {
  ok(null, "Test of Game.formPlayTurnActive not implemented");
});

asyncTest("test_Game.pageAddGameHeader", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddGameHeader();
    deepEqual(
      Game.page.html(),
      "<div id=\"game_id\">Game #1</div>" +
      "<div id=\"round_number\">Round #1</div>",
      "Correct header text is added to Game.page"
    );
    start();
  });
});

asyncTest("test_Game.pageAddFooter", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddFooter();
    ok(true, "No special testing of pageAddFooter() as a whole is done");
    start();
  });
});

asyncTest("test_Game.pageAddTimestampFooter", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddTimestampFooter();
    var htmlout = Game.page.html();
    ok(htmlout.match('<br>'), "Timestamp footer should insert line break");
    ok(htmlout.match('<div>Last action time: '),
       "Timestamp footer text seems reasonable");
    start();
  });
});

asyncTest("test_Game.pageAddActionLogFooter", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddActionLogFooter();
    var htmlout = Game.page.html();
    deepEqual(htmlout, "", "Action log footer for a new game should be empty");
    start();
  });
});

test("test_Game.dieRecipeTable", function() {
  ok(null, "Test of Game.dieRecipeTable not implemented");
});

test("test_Game.dieTableEntry", function() {
  ok(null, "Test of Game.dieTableEntry not implemented");
});

test("test_Game.pageAddDieBattleTable", function() {
  ok(null, "Test of Game.pageAddDieBattleTable not implemented");
});

test("test_Game.pageAddGamePlayerStatus", function() {
  ok(null, "Test of Game.pageAddGamePlayerStatus not implemented");
});

test("test_Game.pageAddGamePlayerDice", function() {
  ok(null, "Test of Game.pageAddGamePlayerDice not implemented");
});

test("test_Game.pageAddGameWinner", function() {
  ok(null, "Test of Game.pageAddGameWinner not implemented");
});

test("test_Game.dieIndexId", function() {
  ok(null, "Test of Game.dieIndexId not implemented");
});

test("test_Game.dieBorderToggleHandler", function() {
  ok(null, "Test of Game.dieBorderToggleHandler not implemented");
});

