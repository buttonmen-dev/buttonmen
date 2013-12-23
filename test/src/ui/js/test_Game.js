module("Game", {
  'setup': function() {
    BMTestUtils.GamePre = BMTestUtils.getAllElements();

    // Override Env.getParameterByName to set the game
    Env.getParameterByName = function(name) {
      if (name == 'game') {
        if (BMTestUtils.GameType == 'newgame') { return '1'; }
        if (BMTestUtils.GameType == 'swingset') { return '2'; }
        if (BMTestUtils.GameType == 'turnactive') { return '3'; }
        if (BMTestUtils.GameType == 'turninactive') { return '4'; }
        if (BMTestUtils.GameType == 'finished') { return '5'; }
        if (BMTestUtils.GameType == 'newgame_twin') { return '6'; }
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
    deepEqual(
      Game.api.player.swingRequestArray['X'],
      {'min': 4, 'max': 20},
      "swing request array should contain X entry with correct min/max");
    start();
  });
});

asyncTest("test_Game.actionChooseSwingActive", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.actionChooseSwingActive();
    var item = document.getElementById('swing_table');
    equal(item.nodeName, "TABLE",
          "#swing_table is a table after actionChooseSwingActive() is called");
    ok(item.innerHTML.match(/X: \(4-20\)/),
       "swing table should contain request to set X swing");
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

asyncTest("test_Game.actionPlayTurnActive", function() {
  BMTestUtils.GameType = 'turnactive';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnActive();
    item = document.getElementById('attack_type_select');
    ok(item, "#attack_type_select is set");
    ok(Game.form, "Game.form is set");
    start();
  });
});

asyncTest("test_Game.actionPlayTurnInactive", function() {
  BMTestUtils.GameType = 'turninactive';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnInactive();
    item = document.getElementById('attack_type_select');
    equal(item, null, "#attack_type_select is not set");
    equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

asyncTest("test_Game.actionShowFinishedGame", function() {
  BMTestUtils.GameType = 'finished';
  Game.getCurrentGame(function() {
    Game.actionShowFinishedGame();
    equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

// The logic here is a little hairy: since Game.getCurrentGame()
// takes a callback, we can use the normal asynchronous logic there.
// However, the POST done by our forms doesn't take a callback (it
// just redraws the page), so turn off asynchronous handling in
// AJAX while we test that, to make sure the test sees the return
// from the POST.
asyncTest("test_Game.formChooseSwingActive", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.actionChooseSwingActive();
    $('#swing_X').val('7');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    deepEqual(
      Env.message,
      {"type": "success", "text": "Successfully set swing values"},
      "Game action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

asyncTest("test_Game.formPlayTurnActive", function() {
  BMTestUtils.GameType = 'turnactive';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnActive();
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    deepEqual(
      Env.message,
      {"type": "success", "text": "Dummy turn submission accepted"},
      "Game action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
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

asyncTest("test_Game.dieRecipeTable", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    var dietable = Game.dieRecipeTable();
    Game.page.append(dietable);
    Game.layoutPage();

    var item = document.getElementById('die_recipe_table');
    ok(item, "Document should contain die recipe table");
    equal(item.nodeName, "TABLE",
          "Die recipe table should be a table element");
    ok(item.innerHTML.match('Avis'),
       "Die recipe table should contain button names");
    ok(item.innerHTML.match('0/0/0'),
       "Die recipe table should contain game state");
    start();
  });
});

asyncTest("test_Game.dieTableEntry", function() {
  BMTestUtils.GameType = 'swingset';
  Game.getCurrentGame(function() {
    var htmlobj = Game.dieTableEntry(
      4,
      Game.api.player.nDie,
      Game.api.player.dieRecipeArray,
      Game.api.player.sidesArray
    );
    // jQuery trick to get the full HTML including the object itself
    var html = $('<div>').append(htmlobj.clone()).remove().html();
    deepEqual(html, "<td>(X=4)</td>",
      "Die table entry has expected contents");
    start();
  });
});

asyncTest("test_Game.pageAddDieBattleTable", function() {
  BMTestUtils.GameType = 'turnactive';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddDieBattleTable();
    var htmlout = Game.page.html();
    ok(htmlout.match('<br>'), "die battle table should insert line break");
    start();
  });
});

asyncTest("test_Game.pageAddGamePlayerStatus", function() {
  BMTestUtils.GameType = 'turnactive';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddGamePlayerStatus('player', false, true);
    var htmlout = Game.page.html();
    ok(htmlout.match('W/L/T'), "game player status should insert W/L/T text");
    start();
  });
});

asyncTest("test_Game.pageAddGamePlayerDice", function() {
  BMTestUtils.GameType = 'turnactive';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddGamePlayerDice('opponent', true);
    var htmlout = Game.page.html();
    ok(htmlout.match('die_img unselected'),
       "dice should include some text with the correct CSS class");
    start();
  });
});

asyncTest("test_Game.pageAddGameWinner", function() {
  BMTestUtils.GameType = 'finished';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddGameWinner();
    var htmlout = Game.page.html();
    ok(htmlout.match('tester1 won!'),
       "correct game winner should be displayed");
    start();
  });
});

asyncTest("test_Game.dieIndexId", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    var idxval = Game.dieIndexId('opponent', 3);
    equal(idxval, 'playerIdx_1_dieIdx_3',
          "die index string should be correct");
    start();
  });
});

asyncTest("test_Game.playerOpponentHeaderRow", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    var row = Game.playerOpponentHeaderRow('Button', 'buttonName');
    var table = $('<table>');
    table.append(row);
    Game.page.append(table);
    Game.layoutPage();

    var item = document.getElementById('game_page');
    ok(item.innerHTML.match('<th>'),
       "header row should contain <th> entries");
    ok(item.innerHTML.match('Avis'),
       "header row should contain button names");
    start();
  });
});

asyncTest("test_Game.playerWLTText", function() {
  BMTestUtils.GameType = 'finished';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    var text = Game.playerWLTText('opponent');
    Game.page.append(text);
    Game.layoutPage();

    var item = document.getElementById('game_page');
    ok(item.innerHTML.match('2/3/0'),
       "opponent WLT text should contain opponent's view of WLT state");
    start();
  });
});

test("test_Game.dieRecipeText", function() {
  var text = Game.dieRecipeText("p(4)", "4");
  equal(text, "p(4)", "text for non-swing die with skills should be correct");

  text = Game.dieRecipeText("zs(X)", "7");
  equal(text, "zs(X=7)",
        "text for swing die with skills should be correct");

  text = Game.dieRecipeText("(W)", null);
  equal(text, "(W)",
        "text for swing die with unknown value should be correct");

  text = Game.dieRecipeText("(6,6)", "12");
  equal(text, "(6,6)", "text for non-swing option die should be correct");

  text = Game.dieRecipeText("(W,W)", "14");
  equal(text, "(W,W=7)", "text for swing option die should be correct");
});

asyncTest("test_Game.dieBorderToggleHandler", function() {
  BMTestUtils.GameType = 'turnactive';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddGamePlayerDice('player', true);
    Game.layoutPage();

    // test the toggle handler by seeing if a die becomes selected
    // and unselected on click
    var dieobj = $('#playerIdx_0_dieIdx_0');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    ok(html.match('die_img unselected'), "die is unselected before click");

    $('#playerIdx_0_dieIdx_0').trigger('click');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    ok(html.match('die_img selected'), "die is selected after first click");

    $('#playerIdx_0_dieIdx_0').trigger('click');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    ok(html.match('die_img unselected'),
       "die is unselected after second click");

    start();
  });
});

