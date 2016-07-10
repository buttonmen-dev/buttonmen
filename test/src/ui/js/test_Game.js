module("Game", {
  'setup': function() {
    BMTestUtils.GamePre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Override Env.getParameterByName to set the game
    BMTestUtils.overrideGetParameterByName();

    // Create the game_page div so functions have something to modify
    if (document.getElementById('game_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'game_page', }));
    }

    // set colors for use in game, since tests don't always traverse showStatePage()
    Game.color = {
      'player': '#dd99dd',
      'opponent': '#ddffdd',
    };

    Login.pageModule = { 'bodyDivId': 'game_page' };
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // Revert cookies
    Env.setCookieNoImages(false);
    Env.setCookieCompactMode(false);

    // JavaScript variables
    delete Api.game;
    delete Api.pending_games;
    delete Game.game;
    delete Game.page;
    delete Game.form;
    delete Game.color;

    Login.pageModule = null;
    Game.activity = {};

    // Page elements
    $('#game_page').remove();

    BMTestUtils.restoreGetParameterByName();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();
    BMTestUtils.restoreGetParameterByName();

    // Fail if any other elements were added or removed
    BMTestUtils.GamePost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.GamePost, BMTestUtils.GamePre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Game module has been loaded
test("test_Game_is_loaded", function(assert) {
  assert.ok(Game, "The Game namespace exists");
});

// The purpose of this test is to demonstrate that the flow of
// Game.showLoggedInPage() is correct for a showXPage function, namely
// that it calls an API getter with a showStatePage function as a
// callback.
//
// Accomplish this by mocking the invoked functions
test("test_Game.showLoggedInPage", function(assert) {
  expect(5);
  var cached_getCurrentGame = Game.getCurrentGame;
  var cached_showStatePage = Game.showStatePage;
  var getCurrentGameCalled = false;
  Game.showStatePage = function() {
    assert.ok(getCurrentGameCalled, "Game.getCurrentGame is called before Game.showStatePage");
  }
  Game.getCurrentGame = function(callback) {
    getCurrentGameCalled = true;
    assert.equal(callback, Game.showStatePage,
      "Game.getCurrentGame is called with Game.showStatePage as an argument");
    callback();
  }

  Game.showLoggedInPage();
  var item = document.getElementById('game_page');
  assert.equal(item.nodeName, "DIV",
        "#game_page is a div after showLoggedInPage() is called");
  Game.getCurrentGame = cached_getCurrentGame;
  Game.showStatePage = cached_showStatePage;
});

// Use stop()/start() because the AJAX-using operation needs to
// finish before its results can be tested
test("test_Game.redrawGamePageSuccess", function(assert) {
  $.ajaxSetup({ async: false });
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
  Game.activity.chat = "Some chat text";
  Game.redrawGamePageSuccess();
  var item = document.getElementById('game_page');
  assert.equal(item.nodeName, "DIV",
        "#game_page is a div after redrawGamePageSuccess() is called");
  assert.deepEqual(Game.activity, {},
        "Game.activity is cleared by redrawGamePageSuccess()");
  $.ajaxSetup({ async: true });
});

// Use stop()/start() because the AJAX-using operation needs to
// finish before its results can be tested
test("test_Game.redrawGamePageFailure", function(assert) {
  $.ajaxSetup({ async: false });
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
  Game.activity.chat = "Some chat text";
  Game.redrawGamePageFailure();
  var item = document.getElementById('game_page');
  assert.equal(item.nodeName, "DIV",
        "#game_page is a div after redrawGamePageFailure() is called");
  assert.equal(Game.activity.chat, "Some chat text",
        "Game.activity.chat is retained by redrawGamePageSuccess()");
  $.ajaxSetup({ async: true });
});

// N.B. Almost all of these tests should use stop(), set a test
// game type, and invoke Game.getCurrentGame(), because that's the
// way to get the dummy responder data which all the other functions
// need.  Then run tests against the function itself, and end with
// start().  So the typical format will be:
//
// test("test_Game.someFunction", function(assert) {
//   stop();
//   BMTestUtils.GameType = '<sometype>';
//   Game.getCurrentGame(function() {
//     <setup any additional prereqs for someFunction>
//     Game.someFunction();
//     <run tests against state changes made by someFunction>
//     start();
//   });
// });

test("test_Game.getCurrentGame", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
  var gameId = BMTestUtils.testGameId(BMTestUtils.GameType);
  Game.getCurrentGame(function() {
    assert.equal(Game.game, gameId, "Set expected game number");
    assert.equal(Api.game.load_status, 'ok', 'Successfully loaded game data');
    assert.equal(Api.game.gameId, Game.game, 'Parsed correct game number from API');
    start();
  });
});

test("test_Game.showStatePage", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    assert.ok(htmlout.length > 0,
      "The created page should have nonzero contents");
    assert.ok(htmlout.match('vacation16.png'),
      "The game UI contains a vacation icon when the API data reports that one player is on vacation");
    start();
  });
});

test("test_Game.showStatePage_no_such_game", function(assert) {
  stop();
  BMTestUtils.GameType = 'NOGAME';
  var gameId = BMTestUtils.testGameId(BMTestUtils.GameType);
  Game.getCurrentGame(function() {
    Game.showStatePage();
    assert.equal(Game.page, null, "The game page should be null for an undefined game");
    assert.deepEqual(
      Env.message,
      {"type": "error", "text": "Error from loadGameData: Game " + gameId + " does not exist."},
      "A reasonable failure message is set on load of nonexistent game");
    start();
  });
});

test("test_Game.showStatePage_chooseaux_active", function(assert) {
  stop();
  BMTestUtils.GameType = 'merlin_crane_reacttoauxiliary_active';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    assert.ok(htmlout.length > 0,
      "The created page should have nonzero contents");
    assert.equal(htmlout.match('figure out what action to take next'), null,
      "The game action should be defined");
    start();
  });
});

test("test_Game.showStatePage_reserve_active", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_reacttoreserve_active';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    assert.ok(htmlout.length > 0,
      "The created page should have nonzero contents");
    assert.equal(htmlout.match('figure out what action to take next'), null,
      "The game action should be defined");
    start();
  });
});

test("test_Game.showStatePage_reserve_inactive", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_reacttoreserve_inactive';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    assert.ok(htmlout.length > 0,
      "The created page should have nonzero contents");
    assert.equal(htmlout.match('figure out what action to take next'), null,
      "The game action should be defined");
    start();
  });
});

test("test_Game.showStatePage_reserve_nonplayer", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_reacttoreserve_nonplayer';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    assert.ok(htmlout.length > 0,
      "The created page should have nonzero contents");
    assert.equal(htmlout.match('figure out what action to take next'), null,
      "The game action should be defined");
    start();
  });
});

test("test_Game.showStatePage_swingset", function(assert) {
  stop();
  BMTestUtils.GameType = 'jellybean_dirgo_specifydice_inactive';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    assert.ok(htmlout.length > 0,
      "The created page should have nonzero contents");
    assert.ok(!htmlout.match('vacation16.png'),
      "The game UI does not contain a vacation icon when the API data reports that neither player is on vacation");
    start();
  });
});

test("test_Game.showStatePage_newgame_nonplayer", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice_nonplayer';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    assert.ok(htmlout.length > 0,
      "The created page should have nonzero contents");
    start();
  });
});

test("test_Game.showStatePage_turn_active", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    assert.ok(htmlout.length > 0,
      "The created page should have nonzero contents");
    assert.ok(!Game.page.is('.compactMode'),
      "The created page should be in normal mode")
    start();
  });
});

test("test_Game.showStatePage_turn_active_compactMode", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Env.setCookieCompactMode(true);
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    assert.ok(htmlout.length > 0,
      "The created page should have nonzero contents");
    assert.ok(Game.page.is('.compactMode'),
      "The created page should be in compact mode")
    start();
  });
});

// EOT

test("test_Game.showStatePage_turn_inactive", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_first_comments_inactive';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    assert.ok(htmlout.length > 0,
      "The created page should have nonzero contents");
    start();
  });
});

test("test_Game.showStatePage_turn_nonplayer", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_startturn_nonplayer';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    assert.ok(htmlout.length > 0,
      "The created page should have nonzero contents");
    start();
  });
});

test("test_Game.parseValidInitiativeActions", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
  Game.getCurrentGame(function() {
    Game.parseValidInitiativeActions();
    assert.deepEqual(Api.game.player.initiativeActions, {},
      "No valid initiative actions during choose swing phase");
    start();
  });
});

test("test_Game.parseValidReserveOptions", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
  Game.getCurrentGame(function() {
    Game.parseValidReserveOptions();
    assert.deepEqual(Api.game.player.reserveOptions, {},
      "No valid reserve die options during choose swing phase");
    start();
  });
});

test("test_Game.parseValidReserveOptions_reserve_active", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_reacttoreserve_active';
  Game.getCurrentGame(function() {
    Game.parseValidReserveOptions();
    assert.deepEqual(Api.game.player.reserveOptions,
      {'4': true, '5': true, '6': true, '7': true, },
      "Four valid reserve die options during first choose reserve phase");
    start();
  });
});

test("test_Game.parseValidFireOptions", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
  Game.getCurrentGame(function() {
    Game.parseValidFireOptions();
    assert.deepEqual(Api.game.player.fireOptions, {},
      "No valid fire die options during choose swing phase");
    start();
  });
});

test("test_Game.parseValidFireOptions_fire_active", function(assert) {
  stop();
  BMTestUtils.GameType = 'blackomega_tamiya_adjustfire_active';
  Game.getCurrentGame(function() {
    Game.parseValidFireOptions();
    assert.deepEqual(Api.game.player.fireOptions,
      {'1': [6, 5, 4, 3, 2, 1, ], },
      "One valid fire die option during adjust fire phase");
    start();
  });
});

test("test_Game.parseValidInitiativeActions_focus", function(assert) {
  stop();
  BMTestUtils.GameType = 'blackomega_thefool_reacttoinitiative';
  Game.getCurrentGame(function() {
    Game.parseValidInitiativeActions();
    assert.deepEqual(
      Api.game.player.initiativeActions,
        {'focus': {
          '1': [7, 6, 5, 4, 3, 2, 1]
         },
         'decline': true },
        "Correct valid initiative actions identified for Crab");
    start();
  });
});

test("test_Game.parseValidInitiativeActions_chance", function(assert) {
  stop();
  BMTestUtils.GameType = 'pikathulhu_phoenix_reacttoinitiative_active';
  Game.getCurrentGame(function() {
    Game.parseValidInitiativeActions();
    assert.deepEqual(
      Api.game.player.initiativeActions,
        {'chance': { '1': true, '4': true }, 'decline': true },
        "Correct valid initiative actions identified for Pikathulhu");
    start();
  });
});

test("test_Game.parseAuxiliaryDieOptions", function(assert) {
  stop();
  BMTestUtils.GameType = 'merlin_crane_reacttoauxiliary_active';
  Game.getCurrentGame(function() {
    Game.parseAuxiliaryDieOptions();
    assert.equal(Api.game.player.auxiliaryDieRecipe, "+s(X)",
      "Correct auxiliary die option for player");
    assert.equal(Api.game.opponent.auxiliaryDieRecipe, "+s(X)",
      "Correct auxiliary die option for opponent");
    start();
  });
});

test("test_Game.actionChooseJoinGameActive", function(assert) {
//  james: incomplete for the moment
});

test("test_Game.buttonTableWithoutDice", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_game_over';
  Game.getCurrentGame(function() {
    var table = Game.buttonTableWithoutDice();
    assert.ok(table.is('table'), 'button table without dice has correct type');
    assert.ok(table.children().is('tbody'),'button table has tbody');
    assert.equal(table.children().length, 1, 'button table has only one tbody');
    var tbody = table.children();
    assert.ok(tbody.children().is('tr'),'button table has tr');
    assert.equal(tbody.children().length, 1, 'button table has only one tr');
    var tr = tbody.children();
    assert.ok(tr.children().is('td'),'button table has td');
    assert.equal(tr.children().length, 2, 'button table has two trs');
    var tds = tr.children();
    tds.each(function(idx) {
      if (idx === 0) {
        assert.ok($(this).hasClass('button_player'));
      } else if (idx === 1) {
        assert.ok($(this).hasClass('button_opponent'));
      }
    })
    // the contents of the tds is further tested in test_Game.buttonImageDisplay
    start();
  });
});

test("test_Game.actionChooseJoinGameInactive", function(assert) {
//  james: incomplete for the moment
});

test("test_Game.actionChooseJoinGameNonplayer", function(assert) {
//  james: incomplete for the moment
});

test("test_Game.actionShowCancelledGame", function(assert) {
//  james: incomplete for the moment
});

test("test_Game.actionSpecifyDiceActive", function(assert) {
  stop();
  BMTestUtils.GameType = 'jellybean_dirgo_specifydice';
  Game.getCurrentGame(function() {
    Game.actionSpecifyDiceActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('die_specify_table');
    assert.equal(item.nodeName, "TABLE",
      "#die_specify_table is a table after actionSpecifyDiceActive() is called");
    assert.ok(item.innerHTML.match(/X \(4-20\):/),
      "swing table should contain request to set X swing");

    var item = document.getElementById('opponent_swing');
    assert.equal(item.nodeName, "TABLE",
      "#opponent_swing is a table after actionSpecifyDiceActive() is called");
    start();
  });
});

test("test_Game.actionSpecifyDiceActive_option", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
  Game.getCurrentGame(function() {
    Game.actionSpecifyDiceActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('die_specify_table');
    assert.equal(item.nodeName, "TABLE",
      "#die_specify_table is a table after actionSpecifyDiceActive() is called");
    var item = document.getElementById('option_4');
    assert.ok(item, "#option_4 select is set");

    var item = document.getElementById('opponent_swing');
    assert.equal(item.nodeName, "TABLE",
      "#opponent_swing is a table after actionSpecifyDiceActive() is called");
    start();
  });
});

test("test_Game.actionSpecifyDiceInactive", function(assert) {
  stop();
  BMTestUtils.GameType = 'jellybean_dirgo_specifydice_inactive';
  Game.getCurrentGame(function() {
    Game.actionSpecifyDiceInactive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('die_specify_table');
    assert.equal(item, null, "#die_specify_table is NULL");
    assert.equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

test("test_Game.actionSpecifyDiceNonplayer", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice_nonplayer';
  Game.getCurrentGame(function() {
    Game.actionSpecifyDiceNonplayer();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('die_specify_table');
    assert.equal(item, null, "#die_specify_table is NULL");
    assert.equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

test("test_Game.actionChooseAuxiliaryDiceActive", function(assert) {
  stop();
  BMTestUtils.GameType = 'merlin_crane_reacttoauxiliary_active';
  Game.getCurrentGame(function() {
    Game.actionChooseAuxiliaryDiceActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    assert.ok(Game.form, "Game.form is set");
    start();
  });
});

test("test_Game.actionChooseAuxiliaryDiceInactive", function(assert) {
  stop();
  BMTestUtils.GameType = 'merlin_ein_reacttoauxiliary_inactive';
  Game.getCurrentGame(function() {
    Game.actionChooseAuxiliaryDiceInactive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    assert.equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

test("test_Game.actionChooseAuxiliaryDiceNonplayer", function(assert) {
  stop();
  BMTestUtils.GameType = 'merlin_ein_reacttoauxiliary_nonplayer';
  Game.getCurrentGame(function() {
    Game.actionChooseAuxiliaryDiceNonplayer();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    assert.equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

test("test_Game.actionChooseReserveDiceActive", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_reacttoreserve_active';
  Game.getCurrentGame(function() {
    Game.actionChooseReserveDiceActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    assert.ok(Game.form, "Game.form is set");
    start();
  });
});

test("test_Game.actionChooseReserveDiceInactive", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_reacttoreserve_inactive';
  Game.getCurrentGame(function() {
    Game.actionChooseReserveDiceInactive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    assert.equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

test("test_Game.actionChooseReserveDiceNonplayer", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_reacttoreserve_nonplayer';
  Game.getCurrentGame(function() {
    Game.actionChooseReserveDiceNonplayer();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    assert.equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

test("test_Game.actionReactToInitiativeActive", function(assert) {
  stop();
  BMTestUtils.GameType = 'blackomega_thefool_reacttoinitiative';
  Game.getCurrentGame(function() {
    Game.actionReactToInitiativeActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('init_react_1');
    assert.ok(item, "#init_react_1 select is set");
    $.each(item.childNodes, function(childid, child) {
      if (child.getAttribute('label') == '8') {
        assert.deepEqual(child.getAttribute('selected'), 'selected',
         'Focus die is initially set to maximum value');
      }
    });
    assert.ok(Game.form, "Game.form is set");
    start();
  });
});

test("test_Game.actionReactToInitiativeActive_prevvals", function(assert) {
  stop();
  BMTestUtils.GameType = 'blackomega_thefool_reacttoinitiative';
  Game.activity.initiativeDieIdxArray = [ 1, ];
  Game.activity.initiativeDieValueArray = [ 2, ];
  Game.getCurrentGame(function() {
    Game.actionReactToInitiativeActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('init_react_1');
    assert.ok(item, "#init_react_1 select is set");
    $.each(item.childNodes, function(childid, child) {
      if (child.getAttribute('label') == '2') {
        assert.deepEqual(child.getAttribute('selected'), 'selected',
         'Focus die is turned down to previously chosen value');
      }
    });
    assert.ok(Game.form, "Game.form is set");
    start();
  });
});

test("test_Game.actionReactToInitiativeInactive", function(assert) {
  stop();
  BMTestUtils.GameType = 'pikathulhu_phoenix_reacttoinitiative_inactive';
  Game.getCurrentGame(function() {
    Game.actionReactToInitiativeInactive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('die_recipe_table');
    assert.ok(item, "page contains die recipe table");
    item = document.getElementById('init_react_1');
    assert.equal(item, null, "#init_react_1 select is not set");
    assert.equal(Game.form, null, "Game.form is not set");
    start();
  });
});

test("test_Game.actionReactToInitiativeNonplayer", function(assert) {
  stop();
  BMTestUtils.GameType = 'pikathulhu_phoenix_reacttoinitiative_nonplayer';
  Game.getCurrentGame(function() {
    Game.actionReactToInitiativeNonplayer();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('die_recipe_table');
    assert.ok(item, "page contains die recipe table");
    item = document.getElementById('init_react_1');
    assert.equal(item, null, "#init_react_1 select is not set");
    assert.equal(Game.form, null, "Game.form is not set");
    start();
  });
});

test("test_Game.actionPlayTurnActive", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('playerIdx_0_dieIdx_0');
    assert.equal(item.innerHTML.match('selected'), null,
      'No attacking die is initially selected');
    assert.equal(item.tabIndex, 0,
      'Attacking die should be accessible via the keyboard');

    var item = document.getElementById('attack_type_select');
    assert.ok(item, "#attack_type_select is set");
    assert.equal($(item).val(), 'Default',
      'Default attack type is initially selected');
    var item = document.getElementById('game_chat');
    assert.equal(item.innerHTML, '',
      'Chat box is empty when there is no previous text');
    assert.equal(item.tabIndex, 0,
      'Chat box should be accessible via the keyboard');
    var item = document.getElementById('game_action_button');
    assert.ok(item.innerHTML.match('Beat People UP!'),
       "Attack submit button says Beat People UP!");
    assert.ok(Game.form, "Game.form is set");
    start();
  });
});

test("test_Game.actionAdjustFireDiceActive", function(assert) {
  stop();
  BMTestUtils.GameType = 'blackomega_tamiya_adjustfire_active';
  Game.getCurrentGame(function() {
    Game.actionAdjustFireDiceActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var htmlout = Game.page.html();
    assert.ok(htmlout.match('Turn down Fire dice by a total of 4'),
      'Page describes the necessary Fire die turndown');
    var item = document.getElementById('fire_adjust_1');
    assert.ok(item, "#fire_adjust_1 select is set");
    $.each(item.childNodes, function(childid, child) {
      if (child.getAttribute('label') == '7') {
        assert.deepEqual(child.getAttribute('selected'), 'selected',
         'Fire die is initially set to current (maximum) value');
      }
    });
    item = document.getElementById('fire_adjust_0');
    assert.ok(!item, "#fire_adjust_0 select is not set");
    assert.ok(Game.form, "Game.form is set");
    start();
  });
});

test("test_Game.actionAdjustFireDiceInactive", function(assert) {
  stop();
  BMTestUtils.GameType = 'beatnikturtle_firebreather_adjustfire_inactive';
  Game.getCurrentGame(function() {
    Game.actionAdjustFireDiceInactive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('die_recipe_table');
    assert.ok(item, "page contains die recipe table");
    item = document.getElementById('fire_adjust_2');
    assert.equal(item, null, "#fire_adjust_2 select is not set");
    assert.equal(Game.form, null, "Game.form is not set");
    start();
  });
});

test("test_Game.actionAdjustFireDiceNonplayer", function(assert) {
  stop();
  BMTestUtils.GameType = 'blackomega_tamiya_adjustfire_nonplayer';
  Game.getCurrentGame(function() {
    Game.actionAdjustFireDiceNonplayer();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('die_recipe_table');
    assert.ok(item, "page contains die recipe table");
    item = document.getElementById('fire_adjust_1');
    assert.equal(item, null, "#fire_adjust_1 select is not set");
    assert.equal(Game.form, null, "Game.form is not set");
    start();
  });
});

test("test_Game.actionPlayTurnActive_prevvals", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.activity.chat = 'I had previously typed some text';
  Game.activity.attackType = 'Skill';
  Game.activity.dieSelectStatus = {
    'playerIdx_0_dieIdx_0': true,
    'playerIdx_0_dieIdx_1': false,
    'playerIdx_1_dieIdx_0': false,
    'playerIdx_1_dieIdx_1': false,
    'playerIdx_1_dieIdx_2': true,
    'playerIdx_1_dieIdx_3': true,
  };

  Game.getCurrentGame(function() {
    Game.actionPlayTurnActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('playerIdx_0_dieIdx_0');
    assert.deepEqual(item.className, 'die_container die_container_alive hide_focus selected',
      'Previous attacking die selection is retained');
    var item = document.getElementById('attack_type_select');
    assert.ok(item.innerHTML.match('selected'),
      'Previous attack type selection is retained');
    var item = document.getElementById('game_chat');
    assert.equal($(item).val(), 'I had previously typed some text',
      'Previous text is retained by game chat');
    assert.ok(Game.form, "Game.form is set");
    start();
  });
});

test("test_Game.actionPlayTurnInactive", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_startturn_inactive';
  Game.activity.chat = 'I had previously typed some text';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnInactive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('attack_type_select');
    assert.equal(item, null, "#attack_type_select is not set");
    var item = document.getElementById('game_chat');
    assert.equal($(item).val(), 'I had previously typed some text',
      'Previous text is retained by game chat');
    assert.ok(Game.form, "Game.form is set");
    start();
  });
});

test("test_Game.actionPlayTurnInactive_chat_editable", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_first_comments_inactive';
  var serverPrevChat = 'This is [b]my[/b] first comment';
  var activityPrevChat = 'I had previously typed some text';
  Game.activity.chat = activityPrevChat;
  Game.getCurrentGame(function() {
    Game.actionPlayTurnInactive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('attack_type_select');
    assert.equal(item, null, "#attack_type_select is not set");
    var item = document.getElementById('game_chat');
    assert.equal($(item).val(), activityPrevChat,
      'Previous text is retained by game chat');
    assert.ok(Game.form, "Game.form is set");
    start();
  });
});

test("test_Game.actionPlayTurnNonplayer", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_startturn_nonplayer';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnNonplayer();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = document.getElementById('attack_type_select');
    assert.equal(item, null, "#attack_type_select is not set");
    assert.equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

test("test_Game.actionShowFinishedGame", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_game_over';
  Game.getCurrentGame(function() {
    Game.actionShowFinishedGame();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    assert.equal(Game.form, null, "Game.form is NULL");
    assert.equal(Game.logEntryLimit, undefined, "Log history is assumed to be full");
    start();
    Game.logEntryLimit = 10;
  });
});

test("test_Game.formCancelGame", function(assert) {
  //  james: incomplete for the moment
});

test("test_Game.formAcceptGame", function(assert) {
  //  james: incomplete for the moment
});

test("test_Game.formRejectGame", function(assert) {
  //  james: incomplete for the moment
});

// The logic here is a little hairy: since Game.getCurrentGame()
// takes a callback, we can use the normal asynchronous logic there.
// However, the POST done by our forms doesn't take a callback (it
// just redraws the page), so turn off asynchronous handling in
// AJAX while we test that, to make sure the test sees the return
// from the POST.
test("test_Game.formSpecifyDiceActive", function(assert) {
  stop();
  BMTestUtils.GameType = 'jellybean_dirgo_specifydice';
  Game.getCurrentGame(function() {
    Game.actionSpecifyDiceActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    $('#swing_X').val('7');
    $('#swing_V').val('12');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    assert.deepEqual(
      Env.message,
      {"type": "success", "text": "Successfully set swing values"},
      "Game action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Game.formChooseAuxiliaryDiceActive", function(assert) {
  stop();
  BMTestUtils.GameType = 'merlin_crane_reacttoauxiliary_active';
  Game.getCurrentGame(function() {
    Game.actionChooseAuxiliaryDiceActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    $('#auxiliary_die_select').val('add');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    assert.deepEqual(
      Env.message,
      {"type": "success",
       "text": "Chose to add auxiliary die"},
      "Game action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Game.formChooseReserveDiceActive", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_reacttoreserve_active';
  Game.getCurrentGame(function() {
    Game.actionChooseReserveDiceActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    $('#reserve_select').val('add');
    $('#choose_reserve_5').prop('checked', true);
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    assert.deepEqual(
      Env.message,
      {"type": "success",
       "text": "responder003 added a reserve die: r(20). "},
      "Game action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Game.formChooseReserveDiceActive_decline", function(assert) {
  stop();
  BMTestUtils.GameType = 'bobby5150_wiseman_reacttoreserve_active';
  Game.getCurrentGame(function() {
    Game.actionChooseReserveDiceActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    $('#reserve_select').val('decline');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    assert.deepEqual(
      Env.message,
      {"type": "success",
       "text": "responder003 chose not to add a reserve die. "},
      "Game action succeeded when decline argument was set and no dice were chosen");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Game.formReactToInitiativeActive", function(assert) {
  stop();
  BMTestUtils.GameType = 'blackomega_thefool_reacttoinitiative';
  Game.getCurrentGame(function() {
    Game.actionReactToInitiativeActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    $('#react_type_select').val('focus');
    $('#init_react_1').val('3');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    assert.deepEqual(
      Env.message,
      {"type": "success",
       "text": "Successfully gained initiative using focus dice"},
      "Game action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Game.formReactToInitiativeActive_decline_invalid", function(assert) {
  stop();
  BMTestUtils.GameType = 'blackomega_thefool_reacttoinitiative';
  Game.getCurrentGame(function() {
    Game.actionReactToInitiativeActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    $('#react_type_select').val('decline');
    $('#init_react_1').val('3');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    assert.deepEqual(
      Env.message,
      {"type": "error",
       "text": "Chose not to react to initiative, but modified a die value"},
      "Game action failed when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Game.formAdjustFireDiceActive", function(assert) {
  stop();
  BMTestUtils.GameType = 'beatnikturtle_firebreather_adjustfire_active';
  Game.getCurrentGame(function() {
    Game.actionAdjustFireDiceActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    $('#fire_action_select').val('turndown');
    $('#fire_adjust_0').val('2');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    assert.deepEqual(
      Env.message,
      {"type": "success",
       "text": "responder003 turned down fire dice: wHF(4) from 4 to 2; Defender (12) was captured; Attacker (10) rerolled 6 => 2. "},
      "Game action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Game.formPlayTurnActive", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_startturn_active';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    assert.deepEqual(
      Env.message,
      {"type": "success",
       "text": "responder003 performed Skill attack using [(12):4,(20):8] against [q(Z=28):12]; Defender q(Z=28) was captured; Attacker (12) rerolled 4 => 6; Attacker (20) rerolled 8 => 5. "},
      "Game action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Game.formPlayTurnActive_surrender_dice", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    $('#playerIdx_1_dieIdx_0').click();
    $('#attack_type_select').val('Surrender');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    assert.deepEqual(
      Env.message,
      {"type": "error", "text": "Please deselect all dice before surrendering."},
      "UI rejects surrender action when dice are selected");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Game.formPlayTurnInactive", function(assert) {
  stop();
  BMTestUtils.GameType = 'haruspex_haruspex_inactive';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnInactive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    $('#game_chat').val('hello world');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    assert.deepEqual(
      Env.message,
      {"type": "success", "text": "Added game message"},
      "Game action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Game.formDismissGame", function(assert) {
  stop();
  assert.expect(3); // test plus 2 teardown
  // Temporarily back up Api.apiFormPost and replace it with
  // a mocked version for testing
  var apiFormPost = Api.apiFormPost;
  Api.apiFormPost = function(args) {
    Api.apiFormPost = apiFormPost;
    assert.deepEqual(args, { 'type': 'dismissGame', 'gameId': '5' },
      'Dismiss game should try to dismiss the game');
    start();
  };
  var link = $('<a>', { 'data-gameId': 5 });
  Game.formDismissGame.call(link, $.Event());
});

test("test_Game.readCurrentGameActivity", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    $('#playerIdx_1_dieIdx_0').click();
    $('#game_chat').val('hello world');
    Game.readCurrentGameActivity();
    assert.ok(Game.activity.dieSelectStatus['playerIdx_1_dieIdx_0'],
      "Player 1's die 0 is selected");
    assert.ok(!Game.activity.dieSelectStatus['playerIdx_0_dieIdx_0'],
      "Player 0's die 0 is not selected");
    assert.equal(Game.activity.chat, 'hello world', "chat is correctly set");
    start();
  });
});

// another cheat - the fix would be to mock the submission function
// for testing and not use an API response at all
test("test_Game.showFullLogHistory", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win_fulllogs';
  Game.getCurrentGame(function() {
    $.ajaxSetup({ async: false });
    Game.showFullLogHistory();
    assert.ok(Api.game.chatLog.length > 10, "Full chat log was returned");
    $.ajaxSetup({ async: true });
    start();
    Game.logEntryLimit = 10;
  });
});

test("test_Game.pageAddGameHeader", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddGameHeader('Howdy, world');
    var html = Game.page.html();

    assert.ok(html.match(/Game #1/), "Game header should contain game number");
    assert.ok(html.match(/Round #1/), "Game header should contain round number");
    assert.ok(html.match(/class="action_desc_span"/),
      "Action description span class should be defined");
    assert.ok(html.match(/Howdy, world/),
      "Action description should contain specified text");
    start();
  });
});

test("test_Game.pageAddFooter", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddFooter();
    assert.ok(true, "No special testing of pageAddFooter() as a whole is done");
    start();
  });
});

test("test_Game.pageAddUnhideChatButton", function(assert) {
  Game.page = $('<div>');
  Game.pageAddUnhideChatButton(true);
  var unhideButton = Game.page.find('.unhideChat');
  assert.ok(unhideButton.length, 'Add/Edit Chat button should appear when requested');

  Game.page.empty();
  Game.pageAddUnhideChatButton(false);
  var unhideButton = Game.page.find('.unhideChat');
  assert.ok(!unhideButton.length,
    'Add/Edit Chat button should not appear when not requested');
});

test("test_Game.pageAddGameNavigationFooter", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_startturn_inactive';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddGameNavigationFooter();
    var htmlout = Game.page.html();
    assert.ok(htmlout.match('<br>'), "Game navigation footer should insert line break");
    assert.ok(htmlout.match('Go to your next pending game \\(at least '),
      "Next game link exists and reflects a count of pending games");
    start();
  });
});

test("test_Game.pageAddGameNavigationFooter_pendingGames", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_game_over';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Api.pending_games.count = 3;
    Game.pageAddGameNavigationFooter();
    var htmlout = Game.page.html();
    assert.ok(htmlout.match('<br>'), "Game navigation footer should insert line break");
    assert.ok(htmlout.match('Go to your next pending game \\(at least 3\\)'),
      "Next game link exists and reflects pending games");
    start();
  });
});

test("test_Game.pageAddGameNavigationFooter_turn_active", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddGameNavigationFooter();
    var htmlout = Game.page.html();
    assert.ok(!htmlout.match('Go to your next pending game'),
      "Next game link is correctly suppressed");
    start();
  });
});

test("test_Game.pageAddGameNavigationFooter_turn_nonplayer", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_startturn_nonplayer';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddGameNavigationFooter();
    var htmlout = Game.page.html();
    assert.ok(!htmlout.match('Go to your next pending game'),
      "Next game link is correctly suppressed");
    start();
  });
});

test("test_Game.pageAddSkillListFooter", function(assert) {
  stop();
  BMTestUtils.GameType = 'blackomega_thefool_reacttoinitiative';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddSkillListFooter();
    var htmlout = Game.page.html();
    assert.ok(htmlout.match('<br>'), "Skill list footer should insert line break");
    assert.ok(htmlout.match('<div><span class="skill_title">Die skills: </span>'),
      "Die skills footer text is present");
    assert.ok(htmlout.match('Focus'),
      "Die skills footer text lists the Focus skill");
    start();
  });
});

test("test_Game.createSkillDiv", function(assert) {
  var skillDiv = Game.createSkillDiv(['1', '23', ' 4 5 '], 'hello');
  assert.ok(skillDiv.is('div'));
  assert.equal(skillDiv.html(),
    '<span class="skill_title">hello: </span>1&nbsp;&nbsp;23&nbsp;&nbsp; 4 5 '
  );
});

test("test_Game.pageAddLogFooter", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddLogFooter();
    var htmlout = Game.page.html();
    assert.deepEqual(htmlout, "", "Action log footer for a new game should be empty");
    start();
  });
});

test("test_Game.pageAddLogFooter_actionlog", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddLogFooter();
    var htmlout = Game.page.html();
    assert.ok(htmlout.match("responder003 performed Power attack"),
      "Action log footer for a game in progress should contain entries");
    start();
  });
});

test("test_Game.pageAddLogFooter_chatlog", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_first_comments_inactive';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddLogFooter();
    var htmlout = Game.page.html();
    assert.ok(!htmlout.match("<script"), "Chat log does not contain unencoded HTML.");
    assert.ok(htmlout.match("&lt;script"), "Chat log does contain encoded HTML.");
    assert.ok(htmlout.match("<br"), "Chat log contain HTML newlines.");
    assert.ok(htmlout.match("&nbsp;&nbsp;&nbsp;"), "Chat contains HTML spaces.");
    start();
  });
});

test("test_Game.dieRecipeTable", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    var dietable = Game.dieRecipeTable(false);
    Game.page.append(dietable);
    Login.arrangePage(Game.page, null, null);

    var item = document.getElementById('die_recipe_table');
    assert.ok(item, "Document should contain die recipe table");
    assert.equal(item.nodeName, "TABLE",
      "Die recipe table should be a table element");
    assert.ok(item.innerHTML.match('Wiseman'),
      "Die recipe table should contain button names");
    assert.ok(item.innerHTML.match('0/0/0'),
      "Die recipe table should contain game state");
    start();
  });
});

test("test_Game.playerWLTText", function(assert) {
  stop();
  var gameId = BMTestUtils.testGameId('washu_hooloovoo_game_over');
  Api.getGameData(gameId, 10, function() {
    var text = Game.playerWLTText('opponent');
    assert.ok(text.match('3/1/0'),
       "opponent WLT text should contain opponent's view of WLT state");
    start();
  });

  // james: we'll need an extra test here of new/cancelled game, once we have an
  //        appropriate test game to use
});

test("test_Game.dieRecipeTable_focus", function(assert) {
  stop();
  BMTestUtils.GameType = 'blackomega_thefool_reacttoinitiative';
  Game.getCurrentGame(function() {
    Game.parseValidInitiativeActions();
    Game.page = $('<div>');
    var dietable = Game.dieRecipeTable('react_to_initiative', true);
    Game.page.append(dietable);
    Login.arrangePage(Game.page, null, null);

    var item = document.getElementById('die_recipe_table');
    assert.ok(item, "Document should contain die recipe table");
    assert.equal(item.nodeName, "TABLE",
      "Die recipe table should be a table element");
    assert.ok(item.innerHTML.match('BlackOmega'),
      "Die recipe table should contain button names");
    assert.ok(item.innerHTML.match('Value'),
      "Die recipe table should contain header for table of values");
    assert.ok(item.innerHTML.match(/7/),
      "Die recipe table should contain entries for table of values");
    assert.ok(item.innerHTML.match(/id="init_react_1"/),
      "Die recipe table should contain an init reaction entry for die idx 1");
    start();
  });
});

test("test_Game.dieRecipeTable_chance", function(assert) {
  stop();
  BMTestUtils.GameType = 'pikathulhu_phoenix_reacttoinitiative_active';
  Game.getCurrentGame(function() {
    Game.parseValidInitiativeActions();
    Game.page = $('<div>');
    var dietable = Game.dieRecipeTable('react_to_initiative', true);
    Game.page.append(dietable);
    Login.arrangePage(Game.page, null, null);

    var item = document.getElementById('die_recipe_table');
    assert.ok(item, "Document should contain die recipe table");
    assert.equal(item.nodeName, "TABLE",
      "Die recipe table should be a table element");
    assert.ok(item.innerHTML.match('Pikathulhu'),
      "Die recipe table should contain button names");
    assert.ok(item.innerHTML.match('Value'),
      "Die recipe table should contain header for table of values");
    assert.ok(item.innerHTML.match(/id="init_react_1"/),
      "Die recipe table should contain an init reaction entry for die idx 1");
    assert.ok(item.innerHTML.match(/id="init_react_4"/),
      "Die recipe table should contain an init reaction entry for die idx 4");
    start();
  });
});

test("test_Game.swingRangeTable", function(assert) {
  stop();
  BMTestUtils.GameType = 'merlin_ein_reacttoauxiliary_player';
  Game.getCurrentGame(function() {

    // First test without allowInput or showPrev
    var htmlobj = Game.swingRangeTable(
      Api.game.player.swingRequestArray, 'swing_range_table', false, false
    );
    var html = $('<div>').append(htmlobj.clone()).remove().html();

    assert.deepEqual(html, '<table id="swing_range_table"><tbody><tr><td>X: (4-20)</td></tr><tr><td>Y: (1-20)</td></tr></tbody></table>',
      "Swing range table has the expected contents without allowInput or showPrev");

    // Second test with allowInput
    htmlobj = Game.swingRangeTable(
      Api.game.player.swingRequestArray, 'swing_range_table', true, false
    );

    // two rows
    assert.equal(2, htmlobj[0].rows.length);

    // first row deals with X swing
    assert.equal(2, htmlobj[0].rows[0].cells.length);
    assert.equal('X (4-20):', htmlobj[0].rows[0].cells[0].innerHTML);
    assert.equal(2, htmlobj[0].rows[0].cells[1].firstChild.getAttribute('maxlength'));
    assert.equal('swing_X', htmlobj[0].rows[0].cells[1].firstChild.getAttribute('id'));
    assert.equal('swing', htmlobj[0].rows[0].cells[1].firstChild.getAttribute('class'));
    assert.equal('text', htmlobj[0].rows[0].cells[1].firstChild.getAttribute('type'));

    // second row deals with Y swing
    assert.equal(2, htmlobj[0].rows[1].cells.length);
    assert.equal('Y (1-20):', htmlobj[0].rows[1].cells[0].innerHTML);
    assert.equal(2, htmlobj[0].rows[1].cells[1].firstChild.getAttribute('maxlength'));
    assert.equal('swing_Y', htmlobj[0].rows[1].cells[1].firstChild.getAttribute('id'));
    assert.equal('swing', htmlobj[0].rows[1].cells[1].firstChild.getAttribute('class'));
    assert.equal('text', htmlobj[0].rows[1].cells[1].firstChild.getAttribute('type'));

    start();
  });
});

test("test_Game.dieTableEntry", function(assert) {
  stop();
  BMTestUtils.GameType = 'jellybean_dirgo_specifydice_inactive';
  Game.getCurrentGame(function() {
    var htmlobj = Game.dieTableEntry(3, Api.game.player.activeDieArray);
    // jQuery trick to get the full HTML including the object itself
    var html = $('<div>').append(htmlobj.clone()).remove().html();
    assert.deepEqual(html, '<td title="X Swing Die (with 10 sides)">(X=10)</td>',
      "Die table entry has expected contents");
    start();
  });
});

test("test_Game.dieTableEntry_empty", function(assert) {
  stop();
  BMTestUtils.GameType = 'jellybean_dirgo_specifydice_inactive';
  Game.getCurrentGame(function() {
    var htmlobj = Game.dieTableEntry(4, Api.game.player.activeDieArray);
    // jQuery trick to get the full HTML including the object itself
    var html = $('<div>').append(htmlobj.clone()).remove().html();
    assert.deepEqual(html, "<td></td>",
      "Empty die table entry has expected contents");
    start();
  });
});

test("test_Game.activeDieFieldString", function(assert) {
  stop();
  BMTestUtils.GameType = 'pikathulhu_phoenix_reacttoinitiative_active';
  Game.getCurrentGame(function() {
    var valstr = Game.activeDieFieldString(4, 'value', Api.game.player.activeDieArray);
    assert.deepEqual(valstr, 2, "Die value string has expected contents for an existing die");

    valstr = Game.activeDieFieldString(6, 'value', Api.game.player.activeDieArray);
    assert.deepEqual(valstr, '', "Die value string has expected contents for a nonexistent die");
    start();
  });
});

test("test_Game.pageAddDieBattleTable", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddDieBattleTable();
    var htmlout = Game.page.html();
    assert.ok(Game.page.find('div.battle_mat_player').length > 0,
      "die battle table should insert player battle mat");
    assert.ok(Game.page.find('div.battle_mat_opponent').length > 0,
      "die battle table should insert opponent battle mat");
    start();
  });
});

test("test_Game.gamePlayerStatus", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    var statusJQuery = Game.gamePlayerStatus('player', false, true);
    var statusPropArr = BMTestUtils.DOMNodePropArray(statusJQuery[0]);
    var expectedPropArr = [
      "DIV", { "class": "status_player" }, [
        [ "DIV", {}, [ "W/L/T: 1/1/0 (3)", "foobar", [ "B", {}, [ "Score: 13 (-30.7 sides)" ] ] ] ],
        [ "DIV", {}, [ [ "SPAN", {}, [ "Dice captured: q(Z=4)" ] ] ] ],
      ]
    ];
    assert.equal(statusPropArr[0], "DIV", "Game.gamePlayerStatus() returns a DIV");
    assert.equal(statusPropArr[1]['class'], "status_player", "Game.gamePlayerStatus() sets correct class for active player");

    var wltScoreDivContents = statusPropArr[2][0][2];
    assert.equal(wltScoreDivContents[0], "W/L/T: 1/1/0 (3)", "Game.gamePlayerStatus() sets expected WLT for a game in progress");
    assert.equal(wltScoreDivContents[2][0], "B", "Game.gamePlayerStatus() typesets the round score in boldface");
    assert.equal(wltScoreDivContents[2][2], "Score: 13 (-30.7 sides)", "Game.gamePlayerStatus() sets expected round score for a game in progress");

    var capturedDivContents = statusPropArr[2][1][2][0];
    assert.equal(capturedDivContents[2][0], "Dice captured: q(Z=4)", "Game.gamePlayerStatus() lists the expected set of captured dice");
    start();
  });
});

test("test_Game.gamePlayerStatusWithValue", function(assert) {
  stop();
  BMTestUtils.GameType = 'blackomega_thefool_captured_value_die';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.gamePlayerStatus('opponent', false, true));
    var htmlout = Game.page.html();
    assert.ok(htmlout.match('W/L/T'), "game player status should insert W/L/T text");
    assert.ok(htmlout.match('Dice captured'),
      "game player status should report captured dice");
    assert.ok(htmlout.match(/tmv\(R=8,R=8\):6/),
      "status should report that player captured a tmv(R=8,R=8) showing a value of 6");
    start();
  });
});

/**
 * This test uses hand-written test data (not API data)
 */
test("test_Game.dieClickableInfo", function(assert) {
  var die = {
    description: "6-sided die",
    properties: [],
    recipe: '(6)',
    sides: 6,
    skills: [],
    value: 2
  };

  assert.deepEqual(
    Game.dieClickableInfo(die, 'player', 'active', false),
    { "isClickable": false, "reason": "The game is not awaiting action from the player loading the mat." },
    "If the player is not active, the die should not be clickable");
  assert.equal(Game.dieClickableInfo(die, 'player', 'active', true).isClickable, true, "If the player is active, the player's die the should be clickable");
  assert.equal(Game.dieClickableInfo(die, 'opponent', 'active', true).isClickable, true, "If the player is active, the opponent's die the should be clickable");

  die.properties = ['Dizzy'];
  assert.equal(Game.dieClickableInfo(die, 'player', 'active', true).isClickable, false, "If the die is dizzy, it should not be clickable");

  die.properties = ['WasJustCaptured'];
  assert.equal(Game.dieClickableInfo(die, 'player', 'captured', false).isClickable, false, "If the die was captured, it should not be clickable");

  die.properties = [];
  die.skills = ['Warrior'];
  assert.equal(Game.dieClickableInfo(die, 'player', 'active', true).isClickable, true, "The active player's own warrior die should be clickable");
  assert.equal(Game.dieClickableInfo(die, 'opponent', 'active', true).isClickable, false, "The active player's opponent's warrior die should not be clickable");
});

/**
 * This test uses hand-written test data (not API data).
 *
 */
test("test_Game.getDieContainerDivOptions", function(assert) {
  var die = {
    description: "6-sided die",
    properties: [],
    recipe: '(6)',
    sides: 6,
    skills: [],
    value: 2
  };
  var containerDivOpts = Game.getDieContainerDivOptions(
    die, 'player', true, 'captured', null,
    { 'isClickable': false, 'reason': 'because captured', },
    false);
  var expectedDivOpts = {
    "class": "die_container die_container_dead",
    "title": "because captured"
  };
  assert.deepEqual(containerDivOpts, expectedDivOpts, "Got expected div options for a captured die");

  var containerDivOpts = Game.getDieContainerDivOptions(
    die, 'player', true, 'active', 'indexindex',
    { 'isClickable': false, 'reason': 'because not clickable', },
    false);
  var expectedDivOpts = {
    "class": "die_container die_container_alive",
    "id": "indexindex",
    "title": "6-sided die. (because not clickable)"
  };
  assert.deepEqual(containerDivOpts, expectedDivOpts, "Got expected div options for an unclickable die");

  var containerDivOpts = Game.getDieContainerDivOptions(
    die, 'player', true, 'active', 'indexindexindex',
    { 'isClickable': true, 'reason': 'just because', },
    true);
  var expectedDivOpts = {
    "class": "die_container die_container_alive hide_focus selected",
    "id": "indexindexindex",
    "tabIndex": 0,
    "title": "6-sided die",
  };
  assert.deepEqual(containerDivOpts, expectedDivOpts, "Got expected div options for an active selected die");

});

/**
 * This test uses hand-written test data (not API data).
 *
 * It does not test very deeply, because Game.createDieContainerDiv()
 * is primarily a wrapper which invokes other functions.  The major
 * pieces of logic within the function itself are:
 * * adding the keyboard handlers, which is hard to test
 * * placing the recipe above or below the die
 */
test("test_Game.createDieContainerDiv", function(assert) {
  Api.game = { player: { dieBackgroundType: 'symmetric' } };

  var die = {
    description: "6-sided die",
    properties: [],
    recipe: '(6)',
    sides: 6,
    skills: [],
    value: 2
  };
  var dieClickableInfo = {
    'isClickable': 'false',
    'reason': 'no reason',
  };

  var dieContainerDivJQuery = Game.createDieContainerDiv(
    die, 'player', false, 'active', 2, dieClickableInfo,
    false);
  var dieContainerDivProps = BMTestUtils.DOMNodePropArray(dieContainerDivJQuery[0]);
  var expectedDivProps = [ "DIV", {
      "class": "die_container die_container_alive hide_focus unselected_player",
      "id": "2",
      "tabindex": "0",
      "title": "6-sided die"
    }, [
      [ "DIV", {}, [
        [ "SPAN", { "class": "die_recipe_player" }, [ "(6)" ] ] ]
      ],
      [ "DIV", { "class": "die_border", "style": "border: 2px solid #dd99dd" }, [
        [ "DIV", { "class": "die_img", "style": "background-image: url(images/die/symmetric/d6active.png)" }, [
          [ "SPAN", { "class": "die_overlay die_number_player" }, [ "2" ] ] ]
        ] ]
      ]
    ]
  ];
  assert.deepEqual(dieContainerDivProps, expectedDivProps, "Player's die should have expected properties, including recipe above die");

  dieContainerDivJQuery = Game.createDieContainerDiv(
    die, 'opponent', true, 'active', 2, dieClickableInfo,
    false);
  dieContainerDivProps = BMTestUtils.DOMNodePropArray(dieContainerDivJQuery[0]);
  expectedDivProps = [ "DIV", {
      "class": "die_container die_container_alive hide_focus unselected_opponent",
      "id": "2",
      "tabindex": "0",
      "title": "6-sided die"
    }, [
      [ "DIV", { "class": "die_border", "style": "border: 2px solid #ddffdd" }, [
        [ "DIV", { "class": "die_img", "style": "background-image: url(images/die/symmetric/d6active.png)" }, [
          [ "SPAN", { "class": "die_overlay die_number_opponent" }, [ "2" ] ] ]
        ] ]
      ],
      [ "DIV", {}, [
        [ "SPAN", { "class": "die_recipe_opponent" }, [ "(6)" ] ] ]
      ]
    ]
  ];
  assert.deepEqual(dieContainerDivProps, expectedDivProps, "Opponent's die should have expected properties, including recipe below die");
});

/**
 * This test uses API data to test Game.createGameMatDieWithBorderDiv(),
 * a wrapper function which assembles a div of a die surrounded by a border
 */
test("test_Game.createGameMatDieWithBorderDiv", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    var die = Api.game.opponent.activeDieArray[3];
    var dieBorderDivJQuery = Game.createGameMatDieWithBorderDiv(die, 'opponent', 'active', true, false);
    var dieBorderDivProps = BMTestUtils.DOMNodePropArray(dieBorderDivJQuery[0]);

    var expectedDivProps = [ "DIV", {
        "class": "die_border",
        "style": "border: 2px solid #ddffdd"
      }, [
        [ "DIV", { "class": "die_img", "style": "background-image: url(images/die/realistic/d20active.png)" }, [
          [ "SPAN", { "class": "die_overlay die_number_opponent" }, [ "12" ] ] ]
        ]
      ]
    ];
    assert.deepEqual(dieBorderDivProps, expectedDivProps);
    start();
  });
});

/**
 * This test uses hand-created (not API) data to test Game.createGameMatDieDiv()
 */
test("test_Game.createGameMatDieDiv", function(assert) {
  var die = {
    description: "6-sided die",
    properties: [],
    recipe: '(6)',
    sides: 6,
    skills: [],
    value: 2
  };

  Api.game = { player: { dieBackgroundType: 'symmetric' } };

  var dieDivJQuery = Game.createGameMatDieDiv(die, 'player', 'active', true);
  var dieDivProps = BMTestUtils.DOMNodePropArray(dieDivJQuery[0]);
  var expectedDivProps = [ "DIV", { "class": "die_img", "style": "background-image: url(images/die/symmetric/d6active.png)" }, [
    [ "SPAN", { "class": "die_overlay die_number_player" }, [ "2" ] ] ]
  ];
  assert.deepEqual(dieDivProps, expectedDivProps, "dieDiv looks correct for clickable active die");

  var dieDivJQuery = Game.createGameMatDieDiv(die, 'opponent', 'active', false, 'symmetric');
  var dieDivProps = BMTestUtils.DOMNodePropArray(dieDivJQuery[0]);
  var expectedDivProps = [ "DIV", { "class": "die_img die_greyed", "style": "background-image: url(images/die/symmetric/d6inactive.png)" }, [
    [ "SPAN", { "class": "die_overlay die_number_opponent" }, [ "2" ] ] ]
  ];
  assert.deepEqual(dieDivProps, expectedDivProps, "dieDiv looks correct for unclickable active die");

  var dieDivJQuery = Game.createGameMatDieDiv(die, 'player', 'captured', false, 'symmetric');
  var dieDivProps = BMTestUtils.DOMNodePropArray(dieDivJQuery[0]);
  var expectedDivProps = [ "DIV", { "class": "die_img die_dead", "style": "background-image: url(images/die/symmetric/d6taken.png)" }, [
    [ "SPAN", { "class": "die_overlay die_number_player" }, [ "\u00A0" + "2" + "\u00A0" ] ] ]
  ];
  assert.deepEqual(dieDivProps, expectedDivProps, "dieDiv looks correct for captured die");
});

test("test_Game.backgroundImagePath", function(assert) {
  Api.game = { player: { dieBackgroundType: 'symmetric' } };

  assert.ok(Game.backgroundImagePath(1, 'active', true, 'symmetric').indexOf('images/die/symmetric/d2active.png') >= 0, 'active d1');
  assert.ok(Game.backgroundImagePath(2, 'active', true, 'symmetric').indexOf('images/die/symmetric/d2active.png') >= 0, 'active d2');
  assert.ok(Game.backgroundImagePath(3, 'active', true, 'symmetric').indexOf('images/die/symmetric/d4active.png') >= 0, 'active d3');
  assert.ok(Game.backgroundImagePath(4, 'active', true, 'symmetric').indexOf('images/die/symmetric/d4active.png') >= 0, 'active d4');
  assert.ok(Game.backgroundImagePath(5, 'active', true, 'symmetric').indexOf('images/die/symmetric/d6active.png') >= 0, 'active d5');
  assert.ok(Game.backgroundImagePath(6, 'active', true, 'symmetric').indexOf('images/die/symmetric/d6active.png') >= 0, 'active d6');
  assert.ok(Game.backgroundImagePath(7, 'active', true, 'symmetric').indexOf('images/die/symmetric/d8active.png') >= 0, 'active d7');
  assert.ok(Game.backgroundImagePath(8, 'active', true, 'symmetric').indexOf('images/die/symmetric/d8active.png') >= 0, 'active d8');
  assert.ok(Game.backgroundImagePath(9, 'active', true, 'symmetric').indexOf('images/die/symmetric/d10active.png') >= 0, 'active d9');

  Api.game.player.dieBackgroundType = 'realistic';

  assert.ok(Game.backgroundImagePath(10, 'active', true, 'realistic').indexOf('images/die/realistic/d10active.png') >= 0, 'active d10');
  assert.ok(Game.backgroundImagePath(11, 'active', true, 'realistic').indexOf('images/die/realistic/d12active.png') >= 0, 'active d11');
  assert.ok(Game.backgroundImagePath(12, 'active', true, 'realistic').indexOf('images/die/realistic/d12active.png') >= 0, 'active d12');
  assert.ok(Game.backgroundImagePath(13, 'active', true, 'realistic').indexOf('images/die/realistic/d20active.png') >= 0, 'active d13');
  assert.ok(Game.backgroundImagePath(19, 'active', true, 'realistic').indexOf('images/die/realistic/d20active.png') >= 0, 'active d19');
  assert.ok(Game.backgroundImagePath(20, 'active', true, 'realistic').indexOf('images/die/realistic/d20active.png') >= 0, 'active d20');
  assert.ok(Game.backgroundImagePath(21, 'active', true, 'realistic').indexOf('images/die/realistic/d30active.png') >= 0, 'active d21');
  assert.ok(Game.backgroundImagePath(29, 'active', true, 'realistic').indexOf('images/die/realistic/d30active.png') >= 0, 'active d29');
  assert.ok(Game.backgroundImagePath(30, 'active', true, 'realistic').indexOf('images/die/realistic/d30active.png') >= 0, 'active d30');
  assert.ok(Game.backgroundImagePath(31, 'active', true, 'realistic').indexOf('images/die/realistic/d30active.png') >= 0, 'active d31');

  Api.game.player.dieBackgroundType = 'symmetric';

  assert.ok(Game.backgroundImagePath(1, 'active', false, 'symmetric').indexOf('images/die/symmetric/d2inactive.png') >= 0, 'inactive d1');
  assert.ok(Game.backgroundImagePath(2, 'active', false, 'symmetric').indexOf('images/die/symmetric/d2inactive.png') >= 0, 'inactive d2');
  assert.ok(Game.backgroundImagePath(3, 'active', false, 'symmetric').indexOf('images/die/symmetric/d4inactive.png') >= 0, 'inactive d3');
  assert.ok(Game.backgroundImagePath(4, 'active', false, 'symmetric').indexOf('images/die/symmetric/d4inactive.png') >= 0, 'inactive d4');
  assert.ok(Game.backgroundImagePath(5, 'active', false, 'symmetric').indexOf('images/die/symmetric/d6inactive.png') >= 0, 'inactive d5');
  assert.ok(Game.backgroundImagePath(6, 'active', false, 'symmetric').indexOf('images/die/symmetric/d6inactive.png') >= 0, 'inactive d6');
  assert.ok(Game.backgroundImagePath(7, 'active', false, 'symmetric').indexOf('images/die/symmetric/d8inactive.png') >= 0, 'inactive d7');
  assert.ok(Game.backgroundImagePath(8, 'active', false, 'symmetric').indexOf('images/die/symmetric/d8inactive.png') >= 0, 'inactive d8');
  assert.ok(Game.backgroundImagePath(9, 'active', false, 'symmetric').indexOf('images/die/symmetric/d10inactive.png') >= 0, 'inactive d9');
  assert.ok(Game.backgroundImagePath(10, 'active', false, 'symmetric').indexOf('images/die/symmetric/d10inactive.png') >= 0, 'inactive d10');
  assert.ok(Game.backgroundImagePath(11, 'active', false, 'symmetric').indexOf('images/die/symmetric/d12inactive.png') >= 0, 'inactive d11');
  assert.ok(Game.backgroundImagePath(12, 'active', false, 'symmetric').indexOf('images/die/symmetric/d12inactive.png') >= 0, 'inactive d12');
  assert.ok(Game.backgroundImagePath(13, 'active', false, 'symmetric').indexOf('images/die/symmetric/d20inactive.png') >= 0, 'inactive d13');
  assert.ok(Game.backgroundImagePath(19, 'active', false, 'symmetric').indexOf('images/die/symmetric/d20inactive.png') >= 0, 'inactive d19');
  assert.ok(Game.backgroundImagePath(20, 'active', false, 'symmetric').indexOf('images/die/symmetric/d20inactive.png') >= 0, 'inactive d20');
  assert.ok(Game.backgroundImagePath(21, 'active', false, 'symmetric').indexOf('images/die/symmetric/d30inactive.png') >= 0, 'inactive d21');
  assert.ok(Game.backgroundImagePath(29, 'active', false, 'symmetric').indexOf('images/die/symmetric/d30inactive.png') >= 0, 'inactive d29');
  assert.ok(Game.backgroundImagePath(30, 'active', false, 'symmetric').indexOf('images/die/symmetric/d30inactive.png') >= 0, 'inactive d30');
  assert.ok(Game.backgroundImagePath(31, 'active', false, 'symmetric').indexOf('images/die/symmetric/d30inactive.png') >= 0, 'inactive d31');

  Api.game.player.dieBackgroundType = 'realistic';

  assert.ok(Game.backgroundImagePath(1, 'captured', false, 'realistic').indexOf('images/die/realistic/d2taken.png') >= 0, 'taken d1');
  assert.ok(Game.backgroundImagePath(2, 'captured', false, 'realistic').indexOf('images/die/realistic/d2taken.png') >= 0, 'taken d2');
  assert.ok(Game.backgroundImagePath(3, 'captured', false, 'realistic').indexOf('images/die/realistic/d4taken.png') >= 0, 'taken d3');
  assert.ok(Game.backgroundImagePath(4, 'captured', false, 'realistic').indexOf('images/die/realistic/d4taken.png') >= 0, 'taken d4');
  assert.ok(Game.backgroundImagePath(5, 'captured', false, 'realistic').indexOf('images/die/realistic/d6taken.png') >= 0, 'taken d5');
  assert.ok(Game.backgroundImagePath(6, 'captured', false, 'realistic').indexOf('images/die/realistic/d6taken.png') >= 0, 'taken d6');
  assert.ok(Game.backgroundImagePath(7, 'captured', false, 'realistic').indexOf('images/die/realistic/d8taken.png') >= 0, 'taken d7');
  assert.ok(Game.backgroundImagePath(8, 'captured', false, 'realistic').indexOf('images/die/realistic/d8taken.png') >= 0, 'taken d8');
  assert.ok(Game.backgroundImagePath(9, 'captured', false, 'realistic').indexOf('images/die/realistic/d10taken.png') >= 0, 'taken d9');
  assert.ok(Game.backgroundImagePath(10, 'captured', false, 'realistic').indexOf('images/die/realistic/d10taken.png') >= 0, 'taken d10');
  assert.ok(Game.backgroundImagePath(11, 'captured', false, 'realistic').indexOf('images/die/realistic/d12taken.png') >= 0, 'taken d11');
  assert.ok(Game.backgroundImagePath(12, 'captured', false, 'realistic').indexOf('images/die/realistic/d12taken.png') >= 0, 'taken d12');
  assert.ok(Game.backgroundImagePath(13, 'captured', false, 'realistic').indexOf('images/die/realistic/d20taken.png') >= 0, 'taken d13');
  assert.ok(Game.backgroundImagePath(19, 'captured', false, 'realistic').indexOf('images/die/realistic/d20taken.png') >= 0, 'taken d19');
  assert.ok(Game.backgroundImagePath(20, 'captured', false, 'realistic').indexOf('images/die/realistic/d20taken.png') >= 0, 'taken d20');
  assert.ok(Game.backgroundImagePath(21, 'captured', false, 'realistic').indexOf('images/die/realistic/d30taken.png') >= 0, 'taken d21');
  assert.ok(Game.backgroundImagePath(29, 'captured', false, 'realistic').indexOf('images/die/realistic/d30taken.png') >= 0, 'taken d29');
  assert.ok(Game.backgroundImagePath(30, 'captured', false, 'realistic').indexOf('images/die/realistic/d30taken.png') >= 0, 'taken d30');
  assert.ok(Game.backgroundImagePath(31, 'captured', false, 'realistic').indexOf('images/die/realistic/d30taken.png') >= 0, 'taken d31');
});

/**
 * This test uses hand-created (not API) data to test Game.createGameMatBorderDiv()
 */
test("test_Game.createGameMatBorderDiv", function(assert) {
  var dieBorderJQuery = Game.createGameMatBorderDiv('player', true);
  var dieBorderProps = BMTestUtils.DOMNodePropArray(dieBorderJQuery[0]);
  var expectedDivProps = [ "DIV", { "class": "die_border" }, [] ];
  assert.deepEqual(dieBorderProps, expectedDivProps, "die border looks correct for player's selected die");

  var dieBorderJQuery = Game.createGameMatBorderDiv('player', false);
  var dieBorderProps = BMTestUtils.DOMNodePropArray(dieBorderJQuery[0]);
  var expectedDivProps = [ "DIV", { "class": "die_border", "style": "border: 2px solid #dd99dd" }, [] ];
  assert.deepEqual(dieBorderProps, expectedDivProps, "die border looks correct for player's unselected die");

  var dieBorderJQuery = Game.createGameMatBorderDiv('opponent', false);
  var dieBorderProps = BMTestUtils.DOMNodePropArray(dieBorderJQuery[0]);
  var expectedDivProps = [ "DIV", { "class": "die_border", "style": "border: 2px solid #ddffdd" }, [] ];
  assert.deepEqual(dieBorderProps, expectedDivProps, "die border looks correct for opponent's unselected die");
});


/**
 * This test uses API data to test Game.createGameMatDieRecipeDiv(),
 * a simple wrapper function which assembles a recipe div
 */
test("test_Game.createGameMatDieRecipeDiv", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    var die = Api.game.opponent.activeDieArray[3];
    var recipeDivJQuery = Game.createGameMatDieRecipeDiv(die, 'opponent');
    var recipeDivProps = BMTestUtils.DOMNodePropArray(recipeDivJQuery[0]);

    var expectedDivProps = [ "DIV", {}, [
      [ "SPAN", { "class": "die_recipe_opponent" }, [ "z(S=20)" ] ] ]
    ];
    assert.deepEqual(recipeDivProps, expectedDivProps);
    start();
  });
});

// This test shows a mat of the opponent's dice
// Opponent's dice have the correct set of classes defined for the opponent,
// and the recipe appears after the dice
test("test_Game.gamePlayerDice", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    var diceJQuery = Game.gamePlayerDice('opponent', true);
    var diceProps = BMTestUtils.DOMNodePropArray(diceJQuery[0]);

    // Overall properties of the dice object
    assert.equal(diceProps.length, 3);
    assert.deepEqual(diceProps[0], "DIV");
    assert.deepEqual(diceProps[1], { "class": "dice_opponent", });
    assert.equal(diceProps[2].length, 4);

    // dieIdx 0: q(T=2)
    var expectedDieProps = [ "DIV", {
        "class": "die_container die_container_alive hide_focus unselected_opponent",
        "id": "playerIdx_1_dieIdx_0",
        "tabindex": "0",
        "title": "Queer T Swing Die (with 2 sides)"
      }, [
        [ "DIV", { "class": "die_border", "style": "border: 2px solid #ddffdd" }, [
          [ "DIV", { "class": "die_img" , "style": "background-image: url(images/die/realistic/d2active.png)" }, [
            [ "SPAN", { "class": "die_overlay die_number_opponent" }, [ "1" ] ] ]
          ] ]
        ],
        [ "DIV", {}, [
          [ "SPAN", { "class": "die_recipe_opponent" }, [ "q(T=2)" ] ] ]
        ]
      ]
    ];
    assert.deepEqual(diceProps[2][0], expectedDieProps, "Opponent's unselected die q(T=2) has expected properties");

    // dieIdx 3: z(S=20)
    expectedDieProps = [ "DIV", {
        "class": "die_container die_container_alive hide_focus unselected_opponent",
        "id": "playerIdx_1_dieIdx_3",
        "tabindex": "0",
        "title": "Speed S Swing Die (with 20 sides)"
      }, [
        [ "DIV", { "class": "die_border", "style": "border: 2px solid #ddffdd" }, [
          [ "DIV", { "class": "die_img", "style": "background-image: url(images/die/realistic/d20active.png)" }, [
            [ "SPAN", { "class": "die_overlay die_number_opponent" }, [ "12" ] ] ]
          ] ]
        ],
        [ "DIV", {}, [
          [ "SPAN", { "class": "die_recipe_opponent" }, [ "z(S=20)" ] ] ]
        ]
      ]
    ];
    assert.deepEqual(diceProps[2][3], expectedDieProps);
    start();
  });
});

// This test shows a mat of the player's dice when it is the opponent's turn
// so all the dice are greyed.
// Each die's recipe appears before the die.
test("test_Game.gamePlayerDice_disabled", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_startturn_inactive';
  Game.getCurrentGame(function() {
    var diceJQuery = Game.gamePlayerDice('player', false);
    var diceProps = BMTestUtils.DOMNodePropArray(diceJQuery[0]);

    // Overall properties of the dice object
    assert.equal(diceProps.length, 3);
    assert.deepEqual(diceProps[0], "DIV");
    assert.deepEqual(diceProps[1], { "class": "dice_player", });
    assert.equal(diceProps[2].length, 4);

    // dieIdx 1: (6)
    var expectedDieProps = [ "DIV", {
        "class": "die_container die_container_alive",
        "id": "playerIdx_0_dieIdx_1",
        "title": "6-sided die"
      }, [
        [ "DIV", {}, [
          [ "SPAN", { "class": "die_recipe_player" }, [ "(6)" ] ] ]
        ],
        [ "DIV", { "class": "die_border", "style": "border: 2px solid #dd99dd" }, [
          [ "DIV", { "class": "die_img die_greyed", "style": "background-image: url(images/die/realistic/d6inactive.png)" }, [
            [ "SPAN", { "class": "die_overlay die_number_player" }, [ "1" ] ]
          ] ]
        ] ]
      ]
    ];
    assert.deepEqual(diceProps[2][1], expectedDieProps, "The player's greyed die should have the correct properties");
    start();
  });
});

// This test shows a mat of the player's dice when it is the player's turn,
// so most dice should be active, and one of the player's dice was just captured
// and should be displayed as a dead die.
test("test_Game.gamePlayerDice_captured", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    var diceJQuery = Game.gamePlayerDice('player', true);
    var diceProps = BMTestUtils.DOMNodePropArray(diceJQuery[0]);

    // Overall properties of the dice object
    assert.equal(diceProps.length, 3);
    assert.deepEqual(diceProps[0], "DIV");
    assert.deepEqual(diceProps[1], { "class": "dice_player", });
    assert.equal(diceProps[2].length, 4);

    // dieIdx 0: (6)
    var expectedDieProps = [ "DIV", {
        "class": "die_container die_container_alive hide_focus unselected_player",
        "id": "playerIdx_0_dieIdx_0",
        "tabindex": "0",
        "title": "6-sided die"
      }, [
        [ "DIV", {}, [
          [ "SPAN", { "class": "die_recipe_player" }, [ "(6)" ] ] ]
        ],
        [ "DIV", { "class": "die_border", "style": "border: 2px solid #dd99dd" }, [
          [ "DIV", { "class": "die_img", "style": "background-image: url(images/die/realistic/d6active.png)" }, [
            [ "SPAN", { "class": "die_overlay die_number_player" }, [ "5" ] ] ]
          ] ]
        ]
      ]
    ];
    assert.deepEqual(diceProps[2][0], expectedDieProps);

    // dead die: (4)
    expectedDieProps = [ "DIV", {
        "class": "die_container die_container_dead",
        "title": "This die was just captured in the last attack and is no longer in play."
      }, [
        [ "DIV", {}, [
          [ "SPAN", { "class": "die_recipe_player" }, [ "(4)" ] ] ]
        ],
        [ "DIV", { "class": "die_border", "style": "border: 2px solid #dd99dd" }, [
          [ "DIV", { "class": "die_img die_dead", "style": "background-image: url(images/die/realistic/d4taken.png)" }, [
            [ "SPAN", { "class": "die_overlay die_number_player" }, [ "\u00A0" + "2" + "\u00A0" ] ] ]
          ] ]
        ]
      ]
    ];
    assert.deepEqual(diceProps[2][2], expectedDieProps);

    // dead die: (20)
    expectedDieProps = [ "DIV", {
        "class": "die_container die_container_dead",
        "title": "This die was just captured in the last attack and is no longer in play."
      }, [
        [ "DIV", {}, [
          [ "SPAN", { "class": "die_recipe_player" }, [ "(20)" ] ] ]
        ],
        [ "DIV", { "class": "die_border", "style": "border: 2px solid #dd99dd" }, [
          [ "DIV", { "class": "die_img die_dead", "style": "background-image: url(images/die/realistic/d20taken.png)" }, [
            [ "SPAN", { "class": "die_overlay die_number_player" }, [ "\u00A0" + "8" + "\u00A0" ] ] ]
          ] ]
        ]
      ]
    ];
    assert.deepEqual(diceProps[2][3], expectedDieProps);
    start();
  });
});

// This test demonstrates that when the active player loads the mat
// in a game with warrior dice, the opponent's warrior dice are greyed, while
// the player's warrior dice have the usual properties of active dice
test("test_Game.gamePlayerDice_warrior", function(assert) {
  stop();
  BMTestUtils.GameType = 'shadowwarriors_fernworthy_newgame_active';
  Game.getCurrentGame(function() {

    ///// opponent should have greyed warriors
    var diceJQuery = Game.gamePlayerDice('opponent', true);
    var diceProps = BMTestUtils.DOMNodePropArray(diceJQuery[0]);

    // Overall properties of the dice object
    assert.equal(diceProps.length, 3, "Opponent's dice object should have expected length");
    assert.deepEqual(diceProps[0], "DIV", "Opponent's dice object should be a div");
    assert.deepEqual(diceProps[1], { "class": "dice_opponent", }, "Opponent's dice object should have expected class");
    assert.equal(diceProps[2].length, 8, "Opponent's dice object should contain one entry per die");

    // opponent dieIdx 2: z(12)
    var expectedDieProps = [ "DIV", {
        "class": "die_container die_container_alive hide_focus unselected_opponent",
        "id": "playerIdx_1_dieIdx_2",
        "tabindex": "0",
        "title": "Speed 12-sided die"
      }, [
        [ "DIV", { "class": "die_border", "style": "border: 2px solid #ddffdd" }, [
          [ "DIV", { "class": "die_img", "style": "background-image: url(images/die/realistic/d12active.png)" }, [
            [ "SPAN", { "class": "die_overlay die_number_opponent" }, [ "7" ] ] ]
          ] ]
        ],
        [ "DIV", {}, [
          [ "SPAN", { "class": "die_recipe_opponent" }, [ "z(12)" ] ] ]
        ]
      ]
    ];
    assert.deepEqual(diceProps[2][2], expectedDieProps, "Opponent's non-warrior z(12) should have correct properties");

    // opponent dieIdx 5: `(6)
    expectedDieProps = [ "DIV", {
        "class": "die_container die_container_alive",
        "id": "playerIdx_1_dieIdx_5",
        "title": "Warrior 6-sided die. (This die is a Warrior die and can't be targeted.)"
      }, [
        [ "DIV", { "class": "die_border", "style": "border: 2px solid #ddffdd" }, [
          [ "DIV", { "class": "die_img die_greyed", "style": "background-image: url(images/die/realistic/d6inactive.png)" }, [
            [ "SPAN", { "class": "die_overlay die_number_opponent" }, [ "6" ] ] ]
          ] ]
        ],
        [ "DIV", {}, [
          [ "SPAN", { "class": "die_recipe_opponent" }, [ "`(6)" ] ] ]
        ]
      ]
    ];
    assert.deepEqual(diceProps[2][5], expectedDieProps, "Opponent's warrior `(6) should have correct properties");


    ///// player should have not greyed warriors
    diceJQuery = Game.gamePlayerDice('player', true);
    diceProps = BMTestUtils.DOMNodePropArray(diceJQuery[0]);

    // Overall properties of the dice object
    assert.equal(diceProps.length, 3, "Player's dice object should have expected length");
    assert.deepEqual(diceProps[0], "DIV", "Player's dice object should be a div");
    assert.deepEqual(diceProps[1], { "class": "dice_player", }, "Player's dice object should have expected class");
    assert.equal(diceProps[2].length, 7, "Player's dice object should contain one entry per die");

    // player dieIdx 0: (1)
    expectedDieProps = [ "DIV", {
        "class": "die_container die_container_alive hide_focus unselected_player",
        "id": "playerIdx_0_dieIdx_0",
        "tabindex": "0",
        "title": "1-sided die"
      }, [
        [ "DIV", {}, [
          [ "SPAN", { "class": "die_recipe_player" }, [ "(1)" ] ] ]
        ],
        [ "DIV", { "class": "die_border", "style": "border: 2px solid #dd99dd" }, [
          [ "DIV", { "class": "die_img", "style": "background-image: url(images/die/realistic/d2active.png)" }, [
            [ "SPAN", { "class": "die_overlay die_number_player" }, [ "1" ] ] ]
          ] ]
        ]
      ]
    ];
    assert.deepEqual(diceProps[2][0], expectedDieProps, "Player's non-warrior (1) should have correct properties");

    // player dieIdx 3: `(6)
    expectedDieProps = [ "DIV", {
        "class": "die_container die_container_alive hide_focus unselected_player",
        "id": "playerIdx_0_dieIdx_3",
        "tabindex": "0",
        "title": "Warrior 6-sided die"
      }, [
        [ "DIV", {}, [
          [ "SPAN", { "class": "die_recipe_player" }, [ "`(6)" ] ] ]
        ],
        [ "DIV", { "class": "die_border", "style": "border: 2px solid #dd99dd" }, [
          [ "DIV", { "class": "die_img", "style": "background-image: url(images/die/realistic/d6active.png)" }, [
            [ "SPAN", { "class": "die_overlay die_number_player" }, [ "6" ] ] ]
          ] ]
        ]
      ]
    ];
    assert.deepEqual(diceProps[2][3], expectedDieProps, "Player's warrior `(6) should have correct properties");

    start();
  });
});

// This test demonstrates two things:
// * The active player's turned-down focus dice are greyed
// * If due to previous activity (i.e. an unsuccessful previous
//   attack) some dice are selected when Game.gamePlayerDice() is
//   called, those dice should display the correct properties for selected dice.
test("test_Game.gamePlayerDice_dizzy_selected", function(assert) {
  stop();
  // N.B. This particular game was captured as responder004, so the
  // recording player is playerIdx_1 instead of the usual playerIdx_0
  // (and the opponent is therefore playerIdx_0)
  BMTestUtils.GameType = 'pikathulhu_phoenix_startturn_dizzy_secondplayer';

  // Specify dice which were previously selected
  Game.activity.dieSelectStatus = {
    'playerIdx_0_dieIdx_0': false,
    'playerIdx_0_dieIdx_1': true,
    'playerIdx_0_dieIdx_2': false,
    'playerIdx_0_dieIdx_3': false,
    'playerIdx_0_dieIdx_4': true,
    'playerIdx_1_dieIdx_0': true,
    'playerIdx_1_dieIdx_1': false,
    'playerIdx_1_dieIdx_2': false,
    'playerIdx_1_dieIdx_3': false,
    'playerIdx_1_dieIdx_4': false,
  };

  Game.getCurrentGame(function() {

    ///// opponent's selected dice should be shown as selected
    var diceJQuery = Game.gamePlayerDice('opponent', true);
    var diceProps = BMTestUtils.DOMNodePropArray(diceJQuery[0]);

    // Overall properties of the dice object
    assert.equal(diceProps.length, 3, "Opponent's dice object should have expected length");
    assert.deepEqual(diceProps[0], "DIV", "Opponent's dice object should be a div");
    assert.deepEqual(diceProps[1], { "class": "dice_opponent", }, "Opponent's dice object should have expected class");
    assert.equal(diceProps[2].length, 5, "Opponent's dice object should contain one entry per die");

    // opponent dieIdx 2: z(12)
    var expectedDieProps = [ "DIV", {
        "class": "die_container die_container_alive hide_focus unselected_opponent",
        "id": "playerIdx_0_dieIdx_2",
        "tabindex": "0",
        "title": "10-sided die"
      }, [
        [ "DIV", { "class": "die_border", "style": "border: 2px solid #ddffdd" }, [
          [ "DIV", { "class": "die_img", "style": "background-image: url(images/die/realistic/d10active.png)" }, [
            [ "SPAN", { "class": "die_overlay die_number_opponent" }, [ "5" ] ] ]
          ] ]
        ],
        [ "DIV", {}, [
          [ "SPAN", { "class": "die_recipe_opponent" }, [ "(10)" ] ] ]
        ]
      ]
    ];
    assert.deepEqual(diceProps[2][2], expectedDieProps, "Opponent's non-selected (10) should have correct properties");

    // opponent dieIdx 4: c(X=6)
    var expectedDieProps = [ "DIV", {
        "class": "die_container die_container_alive hide_focus selected",
        "id": "playerIdx_0_dieIdx_4",
        "tabindex": "0",
        "title": "Chance X Swing Die (with 6 sides)",
      }, [
        [ "DIV", { "class": "die_border" }, [
          [ "DIV", { "class": "die_img", "style": "background-image: url(images/die/realistic/d6active.png)" }, [
            [ "SPAN", { "class": "die_overlay die_number_opponent" }, [ "2" ] ] ]
          ] ]
        ],
        [ "DIV", {}, [
          [ "SPAN", { "class": "die_recipe_opponent" }, [ "c(X=6)" ] ] ]
        ]
      ]
    ];
    assert.deepEqual(diceProps[2][4], expectedDieProps, "Opponent's selected c(X=6) should have correct properties");


    ///// player's focus die should be dizzy, and selected dice should look right
    diceJQuery = Game.gamePlayerDice('player', true);
    diceProps = BMTestUtils.DOMNodePropArray(diceJQuery[0]);

    // Overall properties of the dice object
    assert.equal(diceProps.length, 3, "Player's dice object should have expected length");
    assert.deepEqual(diceProps[0], "DIV", "Player's dice object should be a div");
    assert.deepEqual(diceProps[1], { "class": "dice_player", }, "Player's dice object should have expected class");
    assert.equal(diceProps[2].length, 5, "Player's dice object should contain one entry per die");

    // player dieIdx 0: (4)
    expectedDieProps = [ "DIV", {
        "class": "die_container die_container_alive hide_focus selected",
        "id": "playerIdx_1_dieIdx_0",
        "tabindex": "0",
        "title": "4-sided die"
      }, [
        [ "DIV", {}, [
          [ "SPAN", { "class": "die_recipe_player" }, [ "(4)" ] ] ]
        ],
        [ "DIV", { "class": "die_border" }, [
          [ "DIV", { "class": "die_img", "style": "background-image: url(images/die/realistic/d4active.png)" }, [
            [ "SPAN", { "class": "die_overlay die_number_player" }, [ "3" ] ] ]
          ] ]
        ]
      ]
    ];
    assert.deepEqual(diceProps[2][0], expectedDieProps, "Player's selected (4) should have correct properties");

    // player dieIdx 2: f(8)
    expectedDieProps = [ "DIV", {
        "class": "die_container die_container_alive",
        "id": "playerIdx_1_dieIdx_2",
        "title": "Focus 8-sided die. (This die is dizzy because it was turned down.  It can't be used during this attack.)"
      }, [
        [ "DIV", {}, [
          [ "SPAN", { "class": "die_recipe_player" }, [ "f(8)" ] ] ]
        ],
        [ "DIV", { "class": "die_border", "style": "border: 2px solid #dd99dd" }, [
          [ "DIV", { "class": "die_img die_greyed", "style": "background-image: url(images/die/realistic/d8inactive.png)" }, [
            [ "SPAN", { "class": "die_overlay die_number_player" }, [ "1" ] ] ]
          ] ]
        ]
      ]
    ];
    assert.deepEqual(diceProps[2][2], expectedDieProps, "Player's unselected dizzy f(8) should have correct properties");

    start();
  });
});

test("test_Game.buttonImageDisplay", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.buttonImageDisplay('player'));
    var htmlout = Game.page.html();
    assert.ok(htmlout.match('washu.png'),
      "page should include a link to the button image");
    start();
  });
});

test("test_Game.buttonImageDisplay_noImage", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Env.setCookieNoImages(true);
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.buttonImageDisplay('player'));
    var htmlout = Game.page.html();
    assert.ok(!htmlout.match('washu.png'),
      "page should not include a link to the button image");
    start();
  });
});

test("test_Game.gameWinner", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_game_over';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.gameWinner());
    var htmlout = Game.page.html();
    assert.ok(htmlout.match('responder004 won!'),
      "correct game winner should be displayed");
    start();
  });
});

test("test_Game.dieIndexId", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
  Game.getCurrentGame(function() {
    var idxval = Game.dieIndexId('opponent', 3);
    assert.equal(idxval, 'playerIdx_1_dieIdx_3',
      "die index string should be correct");
    start();
  });
});

test("test_Game.playerOpponentHeaderRow", function(assert) {
  stop();
  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    var row = Game.playerOpponentHeaderRow('Button', 'buttonName');
    var table = $('<table>');
    table.append(row);
    Game.page.append(table);
    Login.arrangePage(Game.page, null, null);

    var item = document.getElementById('game_page');
    assert.ok(item.innerHTML.match('<th>'),
      "header row should contain <th> entries");
    assert.ok(item.innerHTML.match('Wiseman'),
      "header row should contain button names");
    start();
  });
});

test("test_Game.dieRecipeText", function(assert) {
  var text = Game.dieRecipeText({'recipe': 'p(4)', 'skills': ['Poison', ], 'sides': 4,});
  assert.equal(text, "p(4)", "text for non-swing die with skills should be correct");

  text = Game.dieRecipeText({'recipe': 'zs(X)', 'skills': ['Speed', 'Shadow',], 'sides': 7,});
  assert.equal(text, "zs(X=7)",
    "text for swing die with skills should be correct");

  text = Game.dieRecipeText({'recipe': '(W)', 'skills': [],});
  assert.equal(text, "(W)",
    "text for swing die with unknown value should be correct");

  text = Game.dieRecipeText({'recipe': '(6,6)', 'skills': [], 'sides': 12,});
  assert.equal(text, "(6,6)", "text for non-swing option die should be correct");

  text = Game.dieRecipeText({'recipe': '(W,W)', 'skills': [], 'sides': 14,});
  assert.equal(text, "(W=7,W=7)", "text for swing option die should be correct");
});

test("test_Game.dieValidTurndownValues", function(assert) {
  assert.deepEqual(Game.dieValidTurndownValues({
      'recipe': 's(4)',
      'skills': ['Shadow', ],
      'value': 3,
    }, 'REACT_TO_INITIATIVE'), [], "An arbitrary non-focus die has no valid turndown values");
  assert.deepEqual(Game.dieValidTurndownValues({
      'recipe': 'f(7)',
      'skills': ['Focus', ],
      'value': 5,
    }, 'REACT_TO_INITIATIVE'), [4, 3, 2, 1], "A focus die has valid turndown values");
  assert.deepEqual(Game.dieValidTurndownValues({
      'recipe': 'f(7)',
      'skills': ['Focus', ],
      'value': 1,
    }, 'REACT_TO_INITIATIVE'), [], "A focus die showing 1 has no valid turndown values");
  assert.deepEqual(Game.dieValidTurndownValues({
      'recipe': 'f(7,7)',
      'skills': ['Focus', ],
      'value': 4,
    }, 'REACT_TO_INITIATIVE'), [3, 2], "A twin focus die can only turn down as far as 2");
  assert.deepEqual(Game.dieValidTurndownValues({
      'recipe': 'F(7)',
      'skills': ['Fire', ],
      'value': 3,
    }, 'REACT_TO_INITIATIVE'), [], "A fire die has no valid turndown values during 'react to initiative' state");
  assert.deepEqual(Game.dieValidTurndownValues({
      'recipe': 'F(7)',
      'skills': ['Fire', ],
      'value': 3,
    }, 'ADJUST_FIRE_DICE'), [2, 1], "A fire die has valid turndown values during 'adjust fire dice' state");
});

test("test_Game.dieCanRerollForInitiative", function(assert) {
  assert.equal(Game.dieCanRerollForInitiative({
      'recipe': 's(4)',
      'skills': ['Shadow', ],
      'value': 3,
    }), false, "An arbitrary non-chance die cannot reroll for initiative");
  assert.equal(Game.dieCanRerollForInitiative({
      'recipe': 'c(5,5)',
      'skills': ['Chance', ],
      'value': 6,
    }), true, "An arbitrary chance die can reroll for initiative");
});

test("test_Game.chatBox", function(assert) {
  var obj = Game.chatBox();
  var html = obj.html();
  assert.ok(html.match(/"game_chat"/), "Game chat box has correct ID in page");
});

test("test_Game.dieBorderTogglePlayerHandler", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.gamePlayerDice('player', true));
    Login.arrangePage(Game.page, null, null);

    // test the toggle handler by seeing if a die becomes selected
    // and unselected on click
    var dieobj = $('#playerIdx_0_dieIdx_0');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    assert.ok(html.match('die_container die_container_alive hide_focus unselected_player'),
      "die is unselected before click");

    $('#playerIdx_0_dieIdx_0').trigger('click');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    assert.ok(html.match('die_container die_container_alive hide_focus selected'), "die is selected after first click");

    $('#playerIdx_0_dieIdx_0').trigger('click');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    assert.ok(html.match('die_container die_container_alive hide_focus unselected_player'),
      "die is unselected after second click");

    start();
  });
});

test("test_Game.dieBorderToggleOpponentHandler", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.gamePlayerDice('opponent', true));
    Login.arrangePage(Game.page, null, null);

    // test the toggle handler by seeing if a die becomes selected
    // and unselected on click
    var dieobj = $('#playerIdx_1_dieIdx_0');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    assert.ok(html.match('die_container die_container_alive hide_focus unselected_opponent'),
      "die is unselected before click");

    $('#playerIdx_1_dieIdx_0').trigger('click');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    assert.ok(html.match('die_container die_container_alive hide_focus selected'), "die is selected after first click");

    $('#playerIdx_1_dieIdx_0').trigger('click');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    assert.ok(html.match('die_container die_container_alive hide_focus unselected_opponent'),
      "die is unselected after second click");

    start();
  });
});

test("test_Game.waitingOnPlayerNames", function(assert) {
  stop();
  BMTestUtils.GameType = 'jellybean_dirgo_specifydice';
  Game.getCurrentGame(function() {
    var namesString = Game.waitingOnPlayerNames();
    assert.equal(namesString, "responder003 and responder004",
      "String with name(s) of active player(s) has expected contents");
    start();
  });
});

test("test_Game.waitingOnPlayerNames_inactive", function(assert) {
  stop();
  BMTestUtils.GameType = 'jellybean_dirgo_specifydice_inactive';
  Game.getCurrentGame(function() {
    var namesString = Game.waitingOnPlayerNames();
    assert.equal(namesString, "responder004",
      "String with name(s) of active player(s) has expected contents");
    start();
  });
});

test("test_Game.dieValueSelectTd", function(assert) {
  var td = Game.dieValueSelectTd("hiworld", [2, 3, 4, 5], 1, 3);
  var html = td.html();
  assert.ok(html.match(/<select /), "select row should contain a select");
});

test("test_Game.reactToInitiativeSuccessMsg", function(assert) {
  Game.activity.initiativeReactType = 'chance';
  Game.reactToInitiativeSuccessMsg(
    'look, a message', { 'gainedInitiative': false, });
  assert.equal(
    Env.message.type, 'success',
    'Env.message is set to success when initiative action does not fail');
  assert.equal(
    Env.message.text, 'Rerolled chance die, but did not gain initiative',
    'Correct message text when chance reroll does not gain initiative');

  Game.activity.initiativeReactType = 'chance';
  Game.reactToInitiativeSuccessMsg(
    'look, a message', { 'gainedInitiative': true, });
  assert.equal(
    Env.message.text, 'Successfully gained initiative by rerolling chance die',
    'Correct message text when chance reroll gains initiative');

  Game.activity.initiativeReactType = 'decline';
  Game.reactToInitiativeSuccessMsg(
    'look, a message', { 'gainedInitiative': false, });
  assert.equal(
    Env.message.text, 'Declined to use chance/focus dice',
    'Correct message text when initiative action is declined');

  Game.activity.initiativeReactType = 'focus';
  Game.reactToInitiativeSuccessMsg(
    'look, a message', { 'gainedInitiative': true, });
  assert.equal(
    Env.message.text, 'Successfully gained initiative using focus dice',
    'Correct message text when focus turndown gains initiative');
});

test("test_Game.dieFocusOutlineHandler", function(assert) {
  stop();
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnActive();
    Login.arrangePage(Game.page, Game.form, '#game_action_button');
    var item = $('#playerIdx_0_dieIdx_0');

    var tabPress = jQuery.Event('keyup');
    tabPress.which = 9;

    assert.ok($('#playerIdx_0_dieIdx_0').hasClass('hide_focus'),
      "Focus outline is hidden before tab is invoked on another die");
    item.trigger(tabPress);
    assert.ok(!$('#playerIdx_0_dieIdx_0').hasClass('hide_focus'),
      "Focus outline is not hidden after tab is invoked on another die");
    start();
  });
});

test("test_Game.pageAddNewGameLinkFooter", function(assert) {
  stop();
  expect(4); // tests plus 2 teardown tests
  BMTestUtils.GameType = 'washu_hooloovoo_game_over';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddNewGameLinkFooter();
    var newGameLinks = Game.page.find('a');
    assert.ok(newGameLinks.length > 0, 'New game links should exist');
    var url = newGameLinks.attr('href');
    assert.ok(url.match('create_game\\.html'),
      'New game links should go to the create game page');
    start();
  });
});

test("test_Game.pageAddNewGameLinkFooter_turn_active", function(assert) {
  stop();
  expect(3); // tests plus 2 teardown tests
  BMTestUtils.GameType = 'washu_hooloovoo_cant_win';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddNewGameLinkFooter();
    var newGameLinks = Game.page.find('a');
    assert.ok(newGameLinks.length == 0, 'New game links should not exist');
    start();
  });
});

test("test_Game.buildNewGameLink", function(assert) {
  Api.game = { 'maxWins': '2' };
  var linkHolder = Game.buildNewGameLink(
    'test game',
    'Zebedee',
    'Krosp',
    'Hooloovoo',
    17);
  var link = linkHolder.find('a');
  var expectedText = 'test game';
  assert.equal(link.text(), expectedText,
    'New Game link should have the correct text');
  var expectedUrl =
    'create_game.html?opponent=Zebedee&playerButton=Krosp&' +
    'opponentButton=Hooloovoo&previousGameId=17&maxWins=2';
  assert.equal(link.attr('href'), expectedUrl,
    'New game link should have the correct URL');
});

test("test_Game.buildNewGameLink_open", function(assert) {
  Api.game = { 'maxWins': '2' };
  var linkHolder = Game.buildNewGameLink(
    'test game',
    null,
    'Krosp',
    'Hooloovoo',
    null);
  var link = linkHolder.find('a');
  var expectedUrl =
    'create_game.html?playerButton=Krosp&opponentButton=Hooloovoo&maxWins=2';
  assert.equal(link.attr('href'), expectedUrl,
    'Open new game link should have the correct URL');
});

test("test_Game.buildNewGameLink_rematch", function(assert) {
  Api.game = { 'maxWins': '2' };
  var linkHolder = Game.buildNewGameLink(
    'test game',
    'Zebedee',
    null,
    null,
    17);
  var link = linkHolder.find('a');
  var expectedUrl =
    'create_game.html?opponent=Zebedee&previousGameId=17&maxWins=2';
  assert.equal(link.attr('href'), expectedUrl,
    'Rematch new game link should have the correct URL');
});
