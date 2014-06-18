module("Game", {
  'setup': function() {
    BMTestUtils.GamePre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Override Env.getParameterByName to set the game
    BMTestUtils.overrideGetParameterByName();

    // Create the game_page div so functions have something to modify
    if (document.getElementById('game_page') == null) {
      $('body').append($('<div>', {'id': 'game_page', }));
    }

    // set colors for use in game, since tests don't always traverse showStatePage()
    Game.color = Game.COLORS.players;
  },
  'teardown': function() {

    // Delete all elements we expect this module to create

    // Revert cookies
    Env.setCookieNoImages(false);
    Env.setCookieCompactMode(false);

    // JavaScript variables
    delete Api.game;
    delete Game.game;
    delete Game.page;
    delete Game.form;
    delete Game.color;
    Game.activity = {};

    // Page elements
    // FIXME: why do we have to remove this twice?
    $('#game_page').remove();
    $('#game_page').remove();
    $('#game_page').empty();

    BMTestUtils.restoreGetParameterByName();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();
    BMTestUtils.restoreGetParameterByName();

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

asyncTest("test_Game.redrawGamePageSuccess", function() {
  BMTestUtils.GameType = 'newgame';
  Game.activity.chat = "Some chat text";
  Game.redrawGamePageSuccess();
  var item = document.getElementById('game_page');
  equal(item.nodeName, "DIV",
        "#game_page is a div after redrawGamePageSuccess() is called");
  deepEqual(Game.activity, {},
        "Game.activity is cleared by redrawGamePageSuccess()");
  start();
});

asyncTest("test_Game.redrawGamePageFailure", function() {
  BMTestUtils.GameType = 'newgame';
  Game.activity.chat = "Some chat text";
  Game.redrawGamePageFailure();
  var item = document.getElementById('game_page');
  equal(item.nodeName, "DIV",
        "#game_page is a div after redrawGamePageFailure() is called");
  equal(Game.activity.chat, "Some chat text",
        "Game.activity.chat is retained by redrawGamePageSuccess()");
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
    equal(Api.game.load_status, 'ok', 'Successfully loaded game data');
    equal(Api.game.gameId, Game.game, 'Parsed correct game number from API');
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

asyncTest("test_Game.showStatePage_chooseaux_active", function() {
  BMTestUtils.GameType = 'chooseaux_active';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    equal(htmlout.match('figure out what action to take next'), null,
          "The game action should be defined");
    start();
  });
});

asyncTest("test_Game.showStatePage_reserve_active", function() {
  BMTestUtils.GameType = 'reserve_active';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    equal(htmlout.match('figure out what action to take next'), null,
          "The game action should be defined");
    start();
  });
});

asyncTest("test_Game.showStatePage_reserve_inactive", function() {
  BMTestUtils.GameType = 'reserve_inactive';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    equal(htmlout.match('figure out what action to take next'), null,
          "The game action should be defined");
    start();
  });
});

asyncTest("test_Game.showStatePage_reserve_nonplayer", function() {
  BMTestUtils.GameType = 'reserve_nonplayer';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    equal(htmlout.match('figure out what action to take next'), null,
          "The game action should be defined");
    start();
  });
});

asyncTest("test_Game.showStatePage_swingset", function() {
  BMTestUtils.GameType = 'swingset';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

asyncTest("test_Game.showStatePage_newgame_nonplayer", function() {
  BMTestUtils.GameType = 'newgame_nonplayer';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

asyncTest("test_Game.showStatePage_turn_active", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    ok(!Game.page.is('.compactMode'),
      "The created page should be in normal mode")
    start();
  });
});

asyncTest("test_Game.showStatePage_turn_active_compactMode", function() {
  BMTestUtils.GameType = 'turn_active';
  Env.setCookieCompactMode(true);
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    ok(Game.page.is('.compactMode'),
      "The created page should be in compact mode")
    start();
  });
});

asyncTest("test_Game.showStatePage_turn_inactive", function() {
  BMTestUtils.GameType = 'turn_inactive';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

asyncTest("test_Game.showStatePage_turn_nonplayer", function() {
  BMTestUtils.GameType = 'turn_nonplayer';
  Game.getCurrentGame(function() {
    Game.showStatePage();
    var htmlout = Game.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

asyncTest("test_Game.arrangePage", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {

    $('body').append($('<div>', {'id': 'game_page', }));
    Game.page = $('<div>');
    Game.page.append($('<p>', {'text': 'hi world', }));
    Game.arrangePage();
    var item = document.getElementById('game_page');
    equal(item.nodeName, "DIV",
          "#game_page is a div after arrangePage() is called");
    start();
  });
});

asyncTest("test_Game.parseValidInitiativeActions", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.parseValidInitiativeActions();
    deepEqual(Api.game.player.initiativeActions, {},
              "No valid initiative actions during choose swing phase");
    start();
  });
});

asyncTest("test_Game.parseValidReserveOptions", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.parseValidReserveOptions();
    deepEqual(Api.game.player.reserveOptions, {},
              "No valid reserve die options during choose swing phase");
    start();
  });
});

asyncTest("test_Game.parseValidReserveOptions_reserve_active", function() {
  BMTestUtils.GameType = 'reserve_active';
  Game.getCurrentGame(function() {
    Game.parseValidReserveOptions();
    deepEqual(Api.game.player.reserveOptions,
              {'4': true, '5': true, '6': true, '7': true, },
              "Four valid reserve die options during first choose reserve phase");
    start();
  });
});

asyncTest("test_Game.parseValidInitiativeActions_focus", function() {
  BMTestUtils.GameType = 'focus';
  Game.getCurrentGame(function() {
    Game.parseValidInitiativeActions();
    deepEqual(
      Api.game.player.initiativeActions,
        {'focus': {
          '3': [5, 4, 3, 2, 1],
          '4': [17, 16, 15, 14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1]
         },
         'decline': true },
        "Correct valid initiative actions identified for Crab");
    start();
  });
});

asyncTest("test_Game.parseValidInitiativeActions_chance", function() {
  BMTestUtils.GameType = 'chance_active';
  Game.getCurrentGame(function() {
    Game.parseValidInitiativeActions();
    deepEqual(
      Api.game.player.initiativeActions,
        {'chance': { '1': true, '4': true }, 'decline': true },
        "Correct valid initiative actions identified for John Kovalic");
    start();
  });
});

asyncTest("test_Game.parseAuxiliaryDieOptions", function() {
  BMTestUtils.GameType = 'chooseaux_active';
  Game.getCurrentGame(function() {
    Game.parseAuxiliaryDieOptions();
    equal(Api.game.player.auxiliaryDieRecipe, "+(20)",
          "Correct auxiliary die option for player");
    equal(Api.game.opponent.auxiliaryDieRecipe, "+(20)",
          "Correct auxiliary die option for opponent");
    start();
  });
});

asyncTest("test_Game.actionSpecifyDiceActive", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.actionSpecifyDiceActive();
    var item = document.getElementById('die_specify_table');
    equal(item.nodeName, "TABLE",
          "#die_specify_table is a table after actionSpecifyDiceActive() is called");
    ok(item.innerHTML.match(/X \(4-20\):/),
       "swing table should contain request to set X swing");

    var item = document.getElementById('opponent_swing');
    equal(item.nodeName, "TABLE",
          "#opponent_swing is a table after actionSpecifyDiceActive() is called");
    start();
  });
});

asyncTest("test_Game.actionSpecifyDiceActive_option", function() {
  BMTestUtils.GameType = 'option_active';
  Game.getCurrentGame(function() {
    Game.actionSpecifyDiceActive();
    var item = document.getElementById('die_specify_table');
    equal(item.nodeName, "TABLE",
          "#die_specify_table is a table after actionSpecifyDiceActive() is called");
    var item = document.getElementById('option_3');
    ok(item, "#option_3 select is set");
//    $.each(item.childNodes, function(childid, child) {
//      if (child.getAttribute('label') == '6') {
//        deepEqual(child.getAttribute('selected'), 'selected',
//         'Focus die is initially set to maximum value');
//      }
//    });

    var item = document.getElementById('opponent_swing');
    equal(item.nodeName, "TABLE",
          "#opponent_swing is a table after actionSpecifyDiceActive() is called");
    start();
  });
});

asyncTest("test_Game.actionSpecifyDiceInactive", function() {
  BMTestUtils.GameType = 'swingset';
  Game.getCurrentGame(function() {
    Game.actionSpecifyDiceInactive();
    var item = document.getElementById('die_specify_table');
    equal(item, null, "#die_specify_table is NULL");
    equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

asyncTest("test_Game.actionSpecifyDiceNonplayer", function() {
  BMTestUtils.GameType = 'newgame_nonplayer';
  Game.getCurrentGame(function() {
    Game.actionSpecifyDiceNonplayer();
    var item = document.getElementById('die_specify_table');
    equal(item, null, "#die_specify_table is NULL");
    equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

asyncTest("test_Game.actionChooseAuxiliaryDiceActive", function() {
  BMTestUtils.GameType = 'chooseaux_active';
  Game.getCurrentGame(function() {
    Game.actionChooseAuxiliaryDiceActive();
    ok(Game.form, "Game.form is set");
    start();
  });
});

asyncTest("test_Game.actionChooseAuxiliaryDiceInactive", function() {
  BMTestUtils.GameType = 'chooseaux_inactive';
  Game.getCurrentGame(function() {
    Game.actionChooseAuxiliaryDiceInactive();
    equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

asyncTest("test_Game.actionChooseAuxiliaryDiceNonplayer", function() {
  BMTestUtils.GameType = 'chooseaux_nonplayer';
  Game.getCurrentGame(function() {
    Game.actionChooseAuxiliaryDiceNonplayer();
    equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

asyncTest("test_Game.actionChooseReserveDiceActive", function() {
  BMTestUtils.GameType = 'reserve_active';
  Game.getCurrentGame(function() {
    Game.actionChooseReserveDiceActive();
    ok(Game.form, "Game.form is set");
    start();
  });
});

asyncTest("test_Game.actionChooseReserveDiceInactive", function() {
  BMTestUtils.GameType = 'reserve_inactive';
  Game.getCurrentGame(function() {
    Game.actionChooseReserveDiceInactive();
    equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

asyncTest("test_Game.actionChooseReserveDiceNonplayer", function() {
  BMTestUtils.GameType = 'reserve_nonplayer';
  Game.getCurrentGame(function() {
    Game.actionChooseReserveDiceNonplayer();
    equal(Game.form, null, "Game.form is NULL");
    start();
  });
});

asyncTest("test_Game.actionReactToInitiativeActive", function() {
  BMTestUtils.GameType = 'focus';
  Game.getCurrentGame(function() {
    Game.actionReactToInitiativeActive();
    var item = document.getElementById('init_react_3');
    ok(item, "#init_react_3 select is set");
    $.each(item.childNodes, function(childid, child) {
      if (child.getAttribute('label') == '6') {
        deepEqual(child.getAttribute('selected'), 'selected',
         'Focus die is initially set to maximum value');
      }
    });
    item = document.getElementById('init_react_4');
    ok(item, "#init_react_4 select is set");
    ok(Game.form, "Game.form is set");
    start();
  });
});

asyncTest("test_Game.actionReactToInitiativeActive_prevvals", function() {
  BMTestUtils.GameType = 'focus';
  Game.activity.initiativeDieIdxArray = [ 3, ];
  Game.activity.initiativeDieValueArray = [ 2, ];
  Game.getCurrentGame(function() {
    Game.actionReactToInitiativeActive();
    var item = document.getElementById('init_react_3');
    ok(item, "#init_react_3 select is set");
    $.each(item.childNodes, function(childid, child) {
      if (child.getAttribute('label') == '2') {
        deepEqual(child.getAttribute('selected'), 'selected',
         'Focus die is turned down to previously chosen value');
      }
    });
    item = document.getElementById('init_react_4');
    ok(item, "#init_react_4 select is set");
    ok(Game.form, "Game.form is set");
    start();
  });
});

asyncTest("test_Game.actionReactToInitiativeInactive", function() {
  BMTestUtils.GameType = 'chance_inactive';
  Game.getCurrentGame(function() {
    Game.actionReactToInitiativeInactive();
    var item = document.getElementById('die_recipe_table');
    ok(item, "page contains die recipe table");
    item = document.getElementById('init_react_1');
    equal(item, null, "#init_react_1 select is not set");
    equal(Game.form, null, "Game.form is not set");
    start();
  });
});

asyncTest("test_Game.actionReactToInitiativeNonplayer", function() {
  BMTestUtils.GameType = 'chance_nonplayer';
  Game.getCurrentGame(function() {
    Game.actionReactToInitiativeNonplayer();
    var item = document.getElementById('die_recipe_table');
    ok(item, "page contains die recipe table");
    item = document.getElementById('init_react_1');
    equal(item, null, "#init_react_1 select is not set");
    equal(Game.form, null, "Game.form is not set");
    start();
  });
});

asyncTest("test_Game.actionPlayTurnActive", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnActive();
    var item = document.getElementById('playerIdx_0_dieIdx_0');
    equal(item.innerHTML.match('selected'), null,
      'No attacking die is initially selected');
    equal(item.tabIndex, 0,
      'Attacking die should be accessible via the keyboard');

    var item = document.getElementById('attack_type_select');
    ok(item, "#attack_type_select is set");
    equal(item.innerHTML.match('selected'), null,
      'No attack type is initially selected');
    var item = document.getElementById('game_chat');
    equal(item.innerHTML, '',
      'Chat box is empty when there is no previous text');
    equal(item.tabIndex, 0,
      'Chat box should be accessible via the keyboard');
    var item = document.getElementById('game_action_button');
    ok(item.innerHTML.match('Beat People UP!'),
       "Attack submit button says Beat People UP!");
    ok(Game.form, "Game.form is set");
    start();
  });
});

asyncTest("test_Game.actionPlayTurnActive_prevvals", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.activity.chat = 'I had previously typed some text';
  Game.activity.attackType = 'Skill';
  Game.activity.dieSelectStatus = {
    'playerIdx_0_dieIdx_0': true,
    'playerIdx_0_dieIdx_1': false,
    'playerIdx_1_dieIdx_0': false,
    'playerIdx_1_dieIdx_1': false,
    'playerIdx_1_dieIdx_2': true,
  };

  Game.getCurrentGame(function() {
    Game.actionPlayTurnActive();
    var item = document.getElementById('playerIdx_0_dieIdx_0');
    deepEqual(item.className, 'die_container die_alive selected',
      'Previous attacking die selection is retained');
    var item = document.getElementById('attack_type_select');
    ok(item.innerHTML.match('selected'),
      'Previous attack type selection is retained');
    var item = document.getElementById('game_chat');
    equal($(item).val(), 'I had previously typed some text',
      'Previous text is retained by game chat');
    ok(Game.form, "Game.form is set");
    start();
  });
});

asyncTest("test_Game.actionPlayTurnInactive", function() {
  BMTestUtils.GameType = 'turn_inactive';
  Game.activity.chat = 'I had previously typed some text';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnInactive();
    var item = document.getElementById('attack_type_select');
    equal(item, null, "#attack_type_select is not set");
    var item = document.getElementById('game_chat');
    equal($(item).val(), 'I had previously typed some text',
      'Previous text is retained by game chat');
    ok(Game.form, "Game.form is set");
    start();
  });
});

asyncTest("test_Game.actionPlayTurnNonplayer", function() {
  BMTestUtils.GameType = 'turn_nonplayer';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnNonplayer();
    var item = document.getElementById('attack_type_select');
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
    equal(Game.logEntryLimit, undefined, "Log history is assumed to be full");
    start();
    Game.logEntryLimit = 10;
  });
});

// The logic here is a little hairy: since Game.getCurrentGame()
// takes a callback, we can use the normal asynchronous logic there.
// However, the POST done by our forms doesn't take a callback (it
// just redraws the page), so turn off asynchronous handling in
// AJAX while we test that, to make sure the test sees the return
// from the POST.
asyncTest("test_Game.formSpecifyDiceActive", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.actionSpecifyDiceActive();
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

asyncTest("test_Game.formChooseAuxiliaryDiceActive", function() {
  BMTestUtils.GameType = 'chooseaux_active';
  Game.getCurrentGame(function() {
    Game.actionChooseAuxiliaryDiceActive();
    $('#auxiliary_die_select').val('add');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    deepEqual(
      Env.message,
      {"type": "success",
       "text": "Auxiliary die chosen successfully"},
      "Game action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

asyncTest("test_Game.formChooseReserveDiceActive", function() {
  BMTestUtils.GameType = 'reserve_active';
  Game.getCurrentGame(function() {
    Game.actionChooseReserveDiceActive();
    $('#reserve_select').val('add');
    $('#choose_reserve_5').prop('checked', true);
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    deepEqual(
      Env.message,
      {"type": "success",
       "text": "Reserve die chosen successfully"},
      "Game action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

asyncTest("test_Game.formChooseReserveDiceActive_decline", function() {
  BMTestUtils.GameType = 'reserve_active';
  Game.getCurrentGame(function() {
    Game.actionChooseReserveDiceActive();
    $('#reserve_select').val('decline');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    deepEqual(
      Env.message,
      {"type": "success",
       "text": "Reserve die chosen successfully"},
      "Game action succeeded when decline argument was set and no dice were chosen");
    $.ajaxSetup({ async: true });
    start();
  });
});

asyncTest("test_Game.formReactToInitiativeActive", function() {
  BMTestUtils.GameType = 'focus';
  Game.getCurrentGame(function() {
    Game.actionReactToInitiativeActive();
    $('#react_type_select').val('focus');
    $('#init_react_3').val('5');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    deepEqual(
      Env.message,
      {"type": "success",
       "text": "Successfully gained initiative using focus dice"},
      "Game action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

asyncTest("test_Game.formReactToInitiativeActive_decline_invalid", function() {
  BMTestUtils.GameType = 'focus';
  Game.getCurrentGame(function() {
    Game.actionReactToInitiativeActive();
    $('#react_type_select').val('decline');
    $('#init_react_3').val('5');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    deepEqual(
      Env.message,
      {"type": "error",
       "text": "Chose not to react to initiative, but modified a die value"},
      "Game action failed when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

asyncTest("test_Game.formPlayTurnActive", function() {
  BMTestUtils.GameType = 'turn_active';
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

asyncTest("test_Game.formPlayTurnActive_surrender_dice", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnActive();
    $('#playerIdx_1_dieIdx_0').click();
    $('#attack_type_select').val('Surrender');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    deepEqual(
      Env.message,
      {"type": "error", "text": "Please deselect all dice before surrendering."},
      "UI rejects surrender action when dice are selected");
    $.ajaxSetup({ async: true });
    start();
  });
});

asyncTest("test_Game.formPlayTurnInactive", function() {
  BMTestUtils.GameType = 'turn_inactive';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnInactive();
    $('#game_chat').val('hello world');
    $.ajaxSetup({ async: false });
    $('#game_action_button').trigger('click');
    deepEqual(
      Env.message,
      {"type": "success", "text": "Added game message"},
      "Game action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

asyncTest("test_Game.readCurrentGameActivity", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    Game.actionPlayTurnActive();
    $('#playerIdx_1_dieIdx_0').click();
    $('#game_chat').val('hello world');
    Game.readCurrentGameActivity();
    ok(Game.activity.dieSelectStatus['playerIdx_1_dieIdx_0'],
      "Player 1's die 0 is selected");
    ok(!Game.activity.dieSelectStatus['playerIdx_0_dieIdx_0'],
      "Player 0's die 0 is not selected");
    equal(Game.activity.chat, 'hello world', "chat is correctly set");
    start();
  });
});

asyncTest("test_Game.showFullLogHistory", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    $.ajaxSetup({ async: false });
    Game.showFullLogHistory();
    ok(Api.game.chatLog.length > 10, "Full chat log was returned");
    $.ajaxSetup({ async: true });
    start();
    Game.logEntryLimit = 10;
  });
});

asyncTest("test_Game.pageAddGameHeader", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddGameHeader('Howdy, world');
    var html = Game.page.html();

    ok(html.match(/Game #1/), "Game header should contain game number");
    ok(html.match(/Round #1/), "Game header should contain round number");
    ok(html.match(/class="action_desc_span"/),
       "Action description span class should be defined");
    ok(html.match(/Howdy, world/),
       "Action description should contain specified text");
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

test("test_Game.pageAddUnhideChatButton", function() {
  Game.page = $('<div>');
  Game.pageAddUnhideChatButton(true);
  var unhideButton = Game.page.find('.unhideChat');
  ok(unhideButton.length, 'Add/Edit Chat button should appear when requested');

  Game.page.empty();
  Game.pageAddUnhideChatButton(false);
  var unhideButton = Game.page.find('.unhideChat');
  ok(!unhideButton.length,
    'Add/Edit Chat button should not appear when not requested');
});

asyncTest("test_Game.pageAddGameNavigationFooter", function() {
  BMTestUtils.GameType = 'turn_inactive';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddGameNavigationFooter();
    var htmlout = Game.page.html();
    ok(htmlout.match('<br>'), "Game navigation footer should insert line break");
    ok(htmlout.match('Go to your next pending game'),
       "Next game link exists");
    start();
  });
});

asyncTest("test_Game.pageAddGameNavigationFooter_turn_active", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddGameNavigationFooter();
    var htmlout = Game.page.html();
    ok(!htmlout.match('Go to your next pending game'),
       "Next game link is correctly suppressed");
    start();
  });
});

asyncTest("test_Game.pageAddGameNavigationFooter_turn_nonplayer", function() {
  BMTestUtils.GameType = 'turn_nonplayer';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddGameNavigationFooter();
    var htmlout = Game.page.html();
    ok(!htmlout.match('Go to your next pending game'),
       "Next game link is correctly suppressed");
    start();
  });
});

asyncTest("test_Game.pageAddSkillListFooter", function() {
  BMTestUtils.GameType = 'focus';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddSkillListFooter();
    var htmlout = Game.page.html();
    ok(htmlout.match('<br>'), "Skill list footer should insert line break");
    ok(htmlout.match('<div>Die skills in this game: '),
       "Die skills footer text is present");
    ok(htmlout.match('Focus'),
       "Die skills footer text lists the Focus skill");
    start();
  });
});

asyncTest("test_Game.pageAddLogFooter", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddLogFooter();
    var htmlout = Game.page.html();
    deepEqual(htmlout, "", "Action log footer for a new game should be empty");
    start();
  });
});

asyncTest("test_Game.pageAddLogFooter_actionlog", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddLogFooter();
    var htmlout = Game.page.html();
    ok(htmlout.match("tester2 performed Power attack"),
       "Action log footer for a game in progress should contain entries");
    start();
  });
});

asyncTest("test_Game.pageAddLogFooter_chatlog", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddLogFooter();
    var htmlout = Game.page.html();
    ok(!htmlout.match("<script"), "Chat log does not contain unencoded HTML.");
    ok(htmlout.match("&lt;script"), "Chat log does contain encoded HTML.");
    ok(htmlout.match("<br"), "Chat log contain HTML newlines.");
    ok(htmlout.match("&nbsp;&nbsp;&nbsp;"), "Chat contains HTML spaces.");
    start();
  });
});

asyncTest("test_Game.dieRecipeTable", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    var dietable = Game.dieRecipeTable(false);
    Game.page.append(dietable);
    Game.arrangePage();

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

asyncTest("test_Game.dieRecipeTable_focus", function() {
  BMTestUtils.GameType = 'focus';
  Game.getCurrentGame(function() {
    Game.parseValidInitiativeActions();
    Game.page = $('<div>');
    var dietable = Game.dieRecipeTable('react_to_initiative', true);
    Game.page.append(dietable);
    Game.arrangePage();

    var item = document.getElementById('die_recipe_table');
    ok(item, "Document should contain die recipe table");
    equal(item.nodeName, "TABLE",
          "Die recipe table should be a table element");
    ok(item.innerHTML.match('Crab'),
       "Die recipe table should contain button names");
    ok(item.innerHTML.match('Value'),
       "Die recipe table should contain header for table of values");
    ok(item.innerHTML.match(/7/),
       "Die recipe table should contain entries for table of values");
    ok(item.innerHTML.match(/id="init_react_3"/),
       "Die recipe table should contain an init reaction entry for die idx 3");
    ok(item.innerHTML.match(/id="init_react_4"/),
       "Die recipe table should contain an init reaction entry for die idx 4");
    start();
  });
});

asyncTest("test_Game.dieRecipeTable_chance", function() {
  BMTestUtils.GameType = 'chance_active';
  Game.getCurrentGame(function() {
    Game.parseValidInitiativeActions();
    Game.page = $('<div>');
    var dietable = Game.dieRecipeTable('react_to_initiative', true);
    Game.page.append(dietable);
    Game.arrangePage();

    var item = document.getElementById('die_recipe_table');
    ok(item, "Document should contain die recipe table");
    equal(item.nodeName, "TABLE",
          "Die recipe table should be a table element");
    ok(item.innerHTML.match('John Kovalic'),
       "Die recipe table should contain button names");
    ok(item.innerHTML.match('Value'),
       "Die recipe table should contain header for table of values");
    ok(item.innerHTML.match(/id="init_react_1"/),
       "Die recipe table should contain an init reaction entry for die idx 1");
    start();
  });
});

asyncTest("test_Game.dieTableEntry", function() {
  BMTestUtils.GameType = 'swingset';
  Game.getCurrentGame(function() {
    var htmlobj = Game.dieTableEntry(
      4,
      Api.game.player.nDie,
      Api.game.player.dieRecipeArray,
      Api.game.player.sidesArray,
      Api.game.player.diePropertiesArray,
      Api.game.player.dieSkillsArray,
      Api.game.player.dieDescriptionArray
    );
    // jQuery trick to get the full HTML including the object itself
    var html = $('<div>').append(htmlobj.clone()).remove().html();
    deepEqual(html, '<td title="X Swing Die">(X=4)</td>',
      "Die table entry has expected contents");
    start();
  });
});

asyncTest("test_Game.dieTableEntry_empty", function() {
  BMTestUtils.GameType = 'swingset';
  Game.getCurrentGame(function() {
    var htmlobj = Game.dieTableEntry(
      6,
      Api.game.player.nDie,
      Api.game.player.dieRecipeArray,
      Api.game.player.sidesArray,
      Api.game.player.diePropertiesArray,
      Api.game.player.dieSkillsArray,
      Api.game.player.dieDescriptionArray
    );
    // jQuery trick to get the full HTML including the object itself
    var html = $('<div>').append(htmlobj.clone()).remove().html();
    deepEqual(html, "<td></td>",
      "Empty die table entry has expected contents");
    start();
  });
});

asyncTest("test_Game.pageAddDieBattleTable", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.pageAddDieBattleTable();
    var htmlout = Game.page.html();
    ok(Game.page.find('div.battle_mat_player').length > 0,
      "die battle table should insert player battle mat");
    ok(Game.page.find('div.battle_mat_opponent').length > 0,
      "die battle table should insert opponent battle mat");
    start();
  });
});

asyncTest("test_Game.gamePlayerStatus", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.gamePlayerStatus('player', false, true));
    var htmlout = Game.page.html();
    ok(htmlout.match('W/L/T'), "game player status should insert W/L/T text");
    ok(htmlout.match('Dice captured'),
       "game player status should report captured dice");
    ok(htmlout.match('(X=4)'),
       "status should report that player captured an X=4");
    start();
  });
});

asyncTest("test_Game.gamePlayerDice", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.gamePlayerDice('opponent', true));
    var htmlout = Game.page.html();
    ok(htmlout.match('die_container die_alive unselected'),
       "dice should include some text with the correct CSS class");
    start();
  });
});

asyncTest("test_Game.gamePlayerDice_disabled", function() {
  BMTestUtils.GameType = 'turn_inactive';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.gamePlayerDice('player', false));
    var htmlout = Game.page.html();
    ok(htmlout.match('die_img die_greyed'),
       "dice should include some text with the correct CSS class");
    start();
  });
});

asyncTest("test_Game.gamePlayerDice_captured", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.gamePlayerDice('player', true));
    ok(Game.page.find('.die_dead').length > 0,
       "dice should include one that's been captured");
    start();
  });
});

asyncTest("test_Game.buttonImageDisplay", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.buttonImageDisplay('player'));
    var htmlout = Game.page.html();
    ok(htmlout.match('avis.png'),
       "page should include a link to the button image");
    start();
  });
});

asyncTest("test_Game.buttonImageDisplay_noImage", function() {
  BMTestUtils.GameType = 'turn_active';
  Env.setCookieNoImages(true);
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.buttonImageDisplay('player'));
    var htmlout = Game.page.html();
    ok(!htmlout.match('avis.png'),
       "page should not include a link to the button image");
    start();
  });
});

asyncTest("test_Game.gameWinner", function() {
  BMTestUtils.GameType = 'finished';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.gameWinner());
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
    Game.arrangePage();

    var item = document.getElementById('game_page');
    ok(item.innerHTML.match('<th>'),
       "header row should contain <th> entries");
    ok(item.innerHTML.match('Avis'),
       "header row should contain button names");
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

test("test_Game.dieValidTurndownValues", function() {
  deepEqual(Game.dieValidTurndownValues("s(4)", "3"), [],
            "An arbitrary non-focus die has no valid turndown values");
  deepEqual(Game.dieValidTurndownValues("f(7)", "5"), [4, 3, 2, 1],
            "A focus die has valid turndown values");
  deepEqual(Game.dieValidTurndownValues("f(7)", "1"), [],
            "A focus die showing 1 has no valid turndown values");
  deepEqual(Game.dieValidTurndownValues("f(7,7)", "4"), [3, 2],
            "A twin focus die can only turn down as far as 2");
});

test("test_Game.dieCanRerollForInitiative", function() {
  equal(Game.dieCanRerollForInitiative("s(4)"), false,
        "An arbitrary non-chance die cannot reroll for initiative");
  equal(Game.dieCanRerollForInitiative("c(5,5)"), true,
        "An arbitrary chance die can reroll for initiative");
});

test("test_Game.chatBox", function() {
  var obj = Game.chatBox();
  var html = obj.html();
  ok(html.match(/"game_chat"/), "Game chat box has correct ID in page");
});

asyncTest("test_Game.dieBorderTogglePlayerHandler", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.gamePlayerDice('player', true));
    Game.arrangePage();

    // test the toggle handler by seeing if a die becomes selected
    // and unselected on click
    var dieobj = $('#playerIdx_0_dieIdx_0');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    ok(html.match('die_container die_alive unselected_player'),
       "die is unselected before click");

    $('#playerIdx_0_dieIdx_0').trigger('click');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    ok(html.match('die_container die_alive selected'), "die is selected after first click");

    $('#playerIdx_0_dieIdx_0').trigger('click');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    ok(html.match('die_container die_alive unselected_player'),
       "die is unselected after second click");

    start();
  });
});

asyncTest("test_Game.dieBorderToggleOpponentHandler", function() {
  BMTestUtils.GameType = 'turn_active';
  Game.getCurrentGame(function() {
    Game.page = $('<div>');
    Game.page.append(Game.gamePlayerDice('opponent', true));
    Game.arrangePage();

    // test the toggle handler by seeing if a die becomes selected
    // and unselected on click
    var dieobj = $('#playerIdx_1_dieIdx_0');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    ok(html.match('die_container die_alive unselected_opponent'),
       "die is unselected before click");

    $('#playerIdx_1_dieIdx_0').trigger('click');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    ok(html.match('die_container die_alive selected'), "die is selected after first click");

    $('#playerIdx_1_dieIdx_0').trigger('click');
    var html = $('<div>').append(dieobj.clone()).remove().html();
    ok(html.match('die_container die_alive unselected_opponent'),
       "die is unselected after second click");

    start();
  });
});

asyncTest("test_Game.waitingOnPlayerNames", function() {
  BMTestUtils.GameType = 'newgame';
  Game.getCurrentGame(function() {
    var namesString = Game.waitingOnPlayerNames();
    equal(namesString, "tester1 and tester2",
      "String with name(s) of active player(s) has expected contents");
    start();
  });
});

asyncTest("test_Game.waitingOnPlayerNames_inactive", function() {
  BMTestUtils.GameType = 'swingset';
  Game.getCurrentGame(function() {
    var namesString = Game.waitingOnPlayerNames();
    equal(namesString, "tester2",
      "String with name(s) of active player(s) has expected contents");
    start();
  });
});

test("test_Game.dieValueSelectTd", function() {
  var td = Game.dieValueSelectTd("hiworld", [2, 3, 4, 5], 1, 3);
  var html = td.html();
  ok(html.match(/<select /), "select row should contain a select");
});

test("test_Game.reactToInitiativeSuccessMsg", function() {
  Game.activity.initiativeReactType = 'chance';
  Game.reactToInitiativeSuccessMsg(
    'look, a message', { 'gainedInitiative': false, });
  equal(
    Env.message.type, 'success',
    'Env.message is set to success when initiative action does not fail');
  equal(
    Env.message.text, 'Rerolled chance die, but did not gain initiative',
    'Correct message text when chance reroll does not gain initiative');

  Game.activity.initiativeReactType = 'chance';
  Game.reactToInitiativeSuccessMsg(
    'look, a message', { 'gainedInitiative': true, });
  equal(
    Env.message.text, 'Successfully gained initiative by rerolling chance die',
    'Correct message text when chance reroll gains initiative');

  Game.activity.initiativeReactType = 'decline';
  Game.reactToInitiativeSuccessMsg(
    'look, a message', { 'gainedInitiative': false, });
  equal(
    Env.message.text, 'Declined to use chance/focus dice',
    'Correct message text when initiative action is declined');

  Game.activity.initiativeReactType = 'focus';
  Game.reactToInitiativeSuccessMsg(
    'look, a message', { 'gainedInitiative': true, });
  equal(
    Env.message.text, 'Successfully gained initiative using focus dice',
    'Correct message text when focus turndown gains initiative');
});
