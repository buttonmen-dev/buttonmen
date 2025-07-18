module("Newgame", {
  'setup': function() {
    BMTestUtils.NewgamePre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the newgame_page div so functions have something to modify
    if (document.getElementById('newgame_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'newgame_page', }));
    }

    Login.pageModule = { 'bodyDivId': 'newgame_page' };
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JS objects
    delete Api.button;
    delete Api.player;
    delete Newgame.page;
    delete Newgame.form;
    delete Newgame.justCreatedGame;

    Login.pageModule = null;
    Newgame.activity = {};
    ButtonSelection.activity = {};

    // Page elements
    $('#newgame_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.NewgamePost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.NewgamePost, BMTestUtils.NewgamePre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Newgame module has been loaded
test("test_Newgame_is_loaded", function(assert) {
  assert.ok(Newgame, "The Newgame namespace exists");
});

// The purpose of these tests is to demonstrate that the flow of
// Newgame.showLoggedInPage() is correct for a showXPage function, namely
// that it calls an API getter with a showStatePage function as a
// callback.
//
// Accomplish this by mocking the invoked functions

test("test_Newgame.showLoggedInPage", function(assert) {
  expect(4);
  var cached_getButtonSelectionData = ButtonSelection.getButtonSelectionData;
  var cached_showStatePage = Newgame.showPage;
  var getButtonSelectionDataCalled = false;
  Newgame.showPage = function() {
    assert.ok(getButtonSelectionDataCalled, "ButtonSelection.getButtonSelectionData is called before Newgame.showPage");
  };
  ButtonSelection.getButtonSelectionData = function(callback) {
    getButtonSelectionDataCalled = true;
    assert.equal(callback, Newgame.showPage,
      "ButtonSelection.getButtonSelectionData is called with Newgame.showPage as an argument");
    callback();
  };

  Newgame.showLoggedInPage();

  ButtonSelection.getButtonSelectionData = cached_getButtonSelectionData;
  Newgame.showPage = cached_showStatePage;
});

test("test_Newgame.showLoggedInPage_logged_out", function(assert) {
  expect(4);

  // Undo the fake login data
  Login.player = null;
  Login.logged_in = false;

  var cached_getButtonSelectionData = ButtonSelection.getButtonSelectionData;
  var cached_showStatePage = Newgame.showPage;
  var getButtonSelectionDataCalled = false;
  Newgame.showPage = function() {
    assert.ok(getButtonSelectionDataCalled, "ButtonSelection.getButtonSelectionData is called before Newgame.showPage");
  };
  ButtonSelection.getButtonSelectionData = function(callback) {
    getButtonSelectionDataCalled = true;
    assert.equal(callback, Newgame.showPage,
      "ButtonSelection.getButtonSelectionData is called with Newgame.showPage as an argument");
    callback();
  };

  Newgame.showLoggedInPage();

  ButtonSelection.getButtonSelectionData = cached_getButtonSelectionData;
  Newgame.showPage = cached_showStatePage;
});

test("test_Newgame.showPage", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    Newgame.showPage();
    var htmlout = Newgame.page.html();
    assert.ok(htmlout.length > 0,
       "The created page should have nonzero contents");
       
    start();
  });
});

test("test_Newgame.showPage_button_load_failed", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    Api.button.load_status = 'failed';
    Newgame.showPage();
    var htmlout = Newgame.page.html();
    assert.ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

test("test_Newgame.showPage_player_load_failed", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    Api.player.load_status = 'failed';
    Newgame.showPage();
    var htmlout = Newgame.page.html();
    assert.ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

test("test_Newgame.actionLoggedOut", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    Newgame.actionLoggedOut();
    assert.equal(Newgame.form, null,
          "Form is null after the 'logged out' action is processed");
    start();
  });
});

test("test_Newgame.actionInternalErrorPage", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    Newgame.actionInternalErrorPage();
    assert.equal(Newgame.form, null,
          "Form is null after the 'internal error' action is processed");
    start();
  });
});

test("test_Newgame.actionCreateGame", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    Newgame.actionCreateGame();
    assert.equal(Newgame.form, Newgame.formCreateGame,
          "Form is set after the 'create game' action is processed");
    assert.equal($('#n_rounds').val(), 3, 'Rounds should default to 3');
    start();
  });
});

test("test_Newgame.actionCreateGame_prevvals", function(assert) {
  stop();
  Newgame.activity = {
    'opponentName': 'tester2',
    'nRounds': '4',
    'isPlayer1Unlocked': true,
    'playerName': 'responder006',
  };
  ButtonSelection.activity = {
    'playerButton': 'Avis',
    'opponentButton': 'Crab',
  };
  ButtonSelection.getButtonSelectionData(function() {
    Newgame.actionCreateGame();
    assert.equal(Newgame.form, Newgame.formCreateGame,
          "Form is set after the 'create game' action is processed");
    assert.equal($('#opponent_name').val(), 'tester2',
          "Opponent name is retained from previous page activity");
    assert.equal($('#player_button').val(), 'Avis',
          "Player button is retained from previous page activity");
    assert.equal($('#opponent_button').val(), 'Crab',
          "Opponent button is retained from previous page activity");
    assert.equal($('#n_rounds').val(), '4',
          "Number of rounds is retained from previous page activity");
    assert.equal($('#player_name').length, 1,
          "Player 1 selector should have been regenerated");
    assert.equal($('#player_name').val(), 'responder006',
          "Player name is retained from previous page activity");
    start();
  });
});

test("test_Newgame.createPlayerLists", function(assert) {
  stop();

  ButtonSelection.getButtonSelectionData(function() {
    Login.player = 'tester3';
    Api.player.list = {
      'tester1' : {'status' : 'ACTIVE'},
      'tester2' : {'status' : 'ACTIVE'},
      'tester3' : {'status' : 'ACTIVE'},
    };

    Newgame.createPlayerLists();

    assert.deepEqual(
      Newgame.activity.opponentNames,
      {
        'tester1' : 'tester1',
        'tester2' : 'tester2',
      },
      'List of opponent names must be correct'
    );

    assert.deepEqual(
      Newgame.activity.allPlayerNames,
      {
        'tester1' : 'tester1',
        'tester2' : 'tester2',
        'tester3' : 'tester3',
      },
      'List of all player names must be correct'
    );

    start();
  });
});

test("test_Newgame.createMiscOptionsTable", function(assert) {
  stop();

  Newgame.activity = {
    'opponentNames': {
      tester1 : 'tester1',
      tester2 : 'tester2',
      tester3 : 'tester3',
    },
  };

  ButtonSelection.getButtonSelectionData(function() {
    var table = Newgame.createMiscOptionsTable();
    assert.ok(table.is('table'), 'Function should return a table');

    start();
  });
});

test("test_Newgame.createPlayer1Row", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    var row = Newgame.createPlayer1Row();
    assert.ok(row.is('tr'), 'Function should return a row');
    assert.equal(row.children().length, 2, 'Row should have two children');

    var header = $(row.children()[0]);
    assert.ok(header.is('th'), 'First element should be a header cell');
    assert.equal(header.text(), 'You:', 'Header cell should contain correct text');

    var contents = $(row.children()[1]);
    assert.ok(contents.is('td'), 'Second element should be a standard cell');
    assert.equal(contents.text(), 'tester1', 'Standard cell should contain correct text');
    assert.equal(contents.children().length, 1, 'Contents should have a child');

    var toggle = $(contents.children()[0]);
    assert.ok(toggle.is('input'), 'Contents should contain an input toggle');

    start();
  });
});

test("test_Newgame.createPlayer1Toggle", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    var toggle = Newgame.createPlayer1Toggle();
    assert.ok(toggle.is('input'), 'Function should return an input');
    assert.equal(toggle.prop('type'), 'button', 'Function should return a button');
    assert.equal(toggle.prop('value'), 'Change first player', 'Button value should be correct');

    start();
  });
});

test("test_Newgame.createPlayer2Row", function(assert) {
  stop();
  Newgame.activity = {
    'opponentNames': {
      tester1 : 'tester1',
      tester2 : 'tester2',
      tester3 : 'tester3',
    },
  };

  ButtonSelection.getButtonSelectionData(function() {
    var row = Newgame.createPlayer2Row();
    assert.ok(row.is('tr'), 'Function should return a row');
    assert.equal(row.children().length, 2, 'Row should have two children');

    var header = $(row.children()[0]);
    assert.ok(header.is('th'), 'First element should be a header cell');
    assert.equal(header.text(), 'Opponent:', 'Header cell should contain correct text');

    var contents = $(row.children()[1]);
    assert.ok(contents.is('td'), 'Second element should be a standard cell');
    assert.equal(contents.children().length, 1, 'Contents should have a child');

    var select = $(contents.children()[0]);
    assert.ok(select.is('select'), 'Standard cell should contain a select');
    assert.equal(select.children().length, 4, 'Select should have four children');

    var firstoption = $(select.children()[0]);
    assert.ok(firstoption.is('option'), 'Select should contain options');
    assert.equal(firstoption.prop('value'), '', 'First option should have the correct value');
    assert.equal(firstoption.text(), 'Anybody', 'First option should have the correct text');

    for (var optionIdx = 1; optionIdx <= 3; optionIdx++) {
      var option = $(select.children()[optionIdx]);
      assert.ok(option.is('option'), 'Select should contain options');
      assert.equal(option.prop('value'), 'tester' + optionIdx, 'Option ' + optionIdx + ' should have the correct value');
      assert.equal(option.text(), 'tester' + optionIdx, 'Option ' + optionIdx + ' should have the correct text');
    }

    start();
  });
});

test("test_Newgame.createRoundSelectRow", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    var row = Newgame.createRoundSelectRow();
    assert.ok(row.is('tr'), 'Function should return a row');
    assert.equal(row.children().length, 2, 'Row should have two children');

    var header = $(row.children()[0]);
    assert.ok(header.is('th'), 'First element should be a header cell');
    assert.equal(header.text(), 'Winner is first player to win:', 'Header cell should contain correct text');

    var contents = $(row.children()[1]);
    assert.ok(contents.is('td'), 'Second element should be a standard cell');
    assert.equal(contents.children().length, 1, 'Contents should have one child');

    var select = $(contents.children()[0]);
    assert.ok(select.is('select'), 'Contents should contain a select');
    assert.equal(select.children().length, 5, 'Select should have five children');

    for (var optionIdx = 1; optionIdx <= 5; optionIdx++) {
      var option = $(select.children()[optionIdx - 1]);
      assert.ok(option.is('option'), 'Select should contain options');
      assert.equal(option.prop('value'), optionIdx, 'Option ' + optionIdx + ' should have the correct value');
      assert.equal(option.text(), optionIdx + ' round' + (optionIdx > 1 ? 's' : ''), 'Option ' + optionIdx + ' should have the correct text');
    }

    start();
  });
});

test("test_Newgame.createPrevGameRow", function(assert) {
  stop();

  ButtonSelection.getButtonSelectionData(function() {
    var row = Newgame.createPrevGameRow();

    assert.equal(row, null, 'No row should be created when there is no previous game ID');
    assert.equal(Newgame.activity.previousGameId, null, 'Previous game ID should be null');

    start();
  });
});

test("test_Newgame.createPrevGameRow_prevgame", function(assert) {
  stop();
    Newgame.activity = {
    'opponentName': 'tester2',
    'playerButton': 'Avis',
    'opponentButton': 'Crab',
    'nRounds': '4',
    'isPlayer1Unlocked': true,
    'playerName': 'responder006',
    'previousGameId': 12345,
  };

  ButtonSelection.getButtonSelectionData(function() {
    var row = $(Newgame.createPrevGameRow());
    assert.ok(row.is('tr'), 'Function should return a row');
    assert.equal(row.children().length, 2, 'Row should have two children');

    var header = $(row.children()[0]);
    assert.ok(header.is('th'), 'First element should be a header cell');
    assert.equal(header.text(), 'Copy chat from:', 'Header cell should contain correct text');

    var contents = $(row.children()[1]);
    assert.ok(contents.is('td'), 'Second element should be a standard cell');
    assert.equal(contents.children().length, 1, 'Contents should have one child');
    assert.equal(contents.text(), 'Game 12345', 'Contents should have the correct text');

    start();
  });
});

test("test_Newgame.createDescRow", function(assert) {
  stop();

  ButtonSelection.getButtonSelectionData(function() {
    var row = $(Newgame.createDescRow());
    assert.ok(row.is('tr'), 'A row should be created even when there is no description');
    assert.equal(row.children().length, 2, 'Row should have two children');

    var header = $(row.children()[0]);
    assert.ok(header.is('th'), 'First element should be a header cell');
    assert.equal(header.text(), 'Description (optional):', 'Header cell should contain correct text');

    var contents = $(row.children()[1]);
    assert.ok(contents.is('td'), 'Second element should be a standard cell');
    assert.equal(contents.children().length, 1, 'Contents should have one child');

    var textarea = $(contents.children()[0]);
    assert.ok(textarea.is('textarea'), 'Contents should contain a textarea');
    assert.equal(textarea.text(), '', 'Textarea should contain no text');

    assert.equal(Newgame.activity.description, '', 'Stored description should be empty');

    start();
  });
});

test("test_Newgame.createDescRow_with_description", function(assert) {
  stop();

  Newgame.activity = {
    'opponentName': 'tester2',
    'playerButton': 'Avis',
    'opponentButton': 'Crab',
    'nRounds': '4',
    'isPlayer1Unlocked': true,
    'playerName': 'responder006',
    'description': 'test descriptor',
  };

  ButtonSelection.getButtonSelectionData(function() {
    var row = $(Newgame.createDescRow());

    var row = $(Newgame.createDescRow());
    assert.ok(row.is('tr'), 'A row should be created when there is a description');
    assert.equal(row.children().length, 2, 'Row should have two children');

    var header = $(row.children()[0]);
    assert.ok(header.is('th'), 'First element should be a header cell');
    assert.equal(header.text(), 'Description (optional):', 'Header cell should contain correct text');

    var contents = $(row.children()[1]);
    assert.ok(contents.is('td'), 'Second element should be a standard cell');
    assert.equal(contents.children().length, 1, 'Contents should have one child');

    var textarea = $(contents.children()[0]);
    assert.ok(textarea.is('textarea'), 'Contents should contain a textarea');
    assert.equal(textarea.text(), 'test descriptor', 'Textarea should contain no text');

    assert.equal(Newgame.activity.description, 'test descriptor', 'Description should be stored');

    start();
  });
});

test("test_Newgame.createButtonOptionsTable", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {

    var table = Newgame.createButtonOptionsTable();
    assert.ok(table.is('table'), 'Function should return a table');
  
    assert.equal(table.prop('rows').length, 2, 'Default table with player and opponent should have a header row and a body row');
    
    var tableBody = $(table.children()[0]);
    
    var headerRow = $(tableBody.children()[0]);
    assert.equal(headerRow.children().length, 2, 'Header should have two columns');
    assert.ok($(headerRow.children()[0]).is('th'), 'First header element should be a header cell');
    assert.ok($(headerRow.children()[1]).is('th'), 'Second header element should be a header cell');
    
    var bodyRow = $(tableBody.children()[1]);
    assert.equal(bodyRow.children().length, 2, 'Body should have two columns');
    
    var bodyFirstCell = $(bodyRow.children()[0]);
    assert.ok(bodyFirstCell.is('td'), 'First body element should be a standard cell');
    assert.ok($(bodyFirstCell.children()[0]).is('table'), 'First body element should contain a table');

    var bodySecondCell = $(bodyRow.children()[0]);
    assert.ok(bodySecondCell.is('td'), 'Second body element should be a standard cell');
    assert.ok($(bodySecondCell.children()[0]).is('table'), 'Second body element should contain a table');

    start();
  });
});

// The logic here is a little hairy: since ButtonSelection.getButtonSelectionData()
// takes a callback, we can use the normal asynchronous logic there.
// However, the POST done by our forms doesn't take a callback (it
// just redraws the page), so turn off asynchronous handling in
// AJAX while we test that, to make sure the test sees the return
// from the POST.
test("test_Newgame.formCreateGame", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    Newgame.actionCreateGame();
    $('#opponent_name').val('tester2');
    $('#player_button').val('Avis');
    $('#opponent_button').val('Avis');
    $.ajaxSetup({ async: false });
    $('#newgame_action_button').trigger('click');
    assert.equal(
      Env.message.type, "success",
      "Newgame action should succeed when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Newgame.formCreateThirdPartyGame", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    Newgame.actionCreateGame();
    $.ajaxSetup({ async: false });
    assert.equal(
      $('#player_name').length, 0,
      "Player 1 selector should not exist yet");
    $('#player1_toggle').trigger('click');
    assert.equal(
      $('#player_name').length, 1,
      "Player 1 selector should exist after being activated");
    $('#player_name').val('responder005');
    $('#opponent_name').val('tester2');
    $('#player_button').val('Avis');
    $('#opponent_button').val('Avis');
    $('#newgame_action_button').trigger('click');
    assert.equal(
      Env.message.type, "success",
      "Newgame action should succeed when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Newgame.formCreateGame_no_vals", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    Newgame.actionCreateGame();
    $.ajaxSetup({ async: false });
    $('#newgame_action_button').trigger('click');
    assert.equal(
      Env.message.type, "error",
      "Newgame action should fail when expected arguments were not set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Newgame.formCreateGame_no_buttons", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    Newgame.actionCreateGame();
    $('#opponent_name').val('tester2');
    $.ajaxSetup({ async: false });
    $('#newgame_action_button').trigger('click');
    assert.equal(
      Env.message.type, "error",
      "Newgame action should fail when expected arguments were not set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Newgame.formCreateGame_no_opponent_button", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    Newgame.actionCreateGame();
    $('#opponent_name').val('tester2');
    $('#player_button').val('Crab');
    $.ajaxSetup({ async: false });
    $('#newgame_action_button').trigger('click');
    assert.equal(
      Env.message.type, "error",
      "Newgame action should fail when expected arguments were not set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Newgame.formCreateGame_invalid_player", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    Newgame.actionCreateGame();
    $('#opponent_name').append(
      $('<option>', {
        'value': 'nontester1',
        'text': 'nontester1',
        'label': 'nontester1',
      })
    );
    $('#opponent_name').val('nontester1');
    $('#player_button').val('Crab');
    $('#opponent_button').val('John Kovalic');
    $.ajaxSetup({ async: false });
    $('#newgame_action_button').trigger('click');
    assert.equal(
      Env.message.type, "error",
      "Newgame action should fail when opponent was not a known player");
    assert.equal(
      Env.message.text, "Specified opponent nontester1 is not recognized",
      "Newgame action should fail when opponent was not a known player");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Newgame.addLoggedOutPage", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Newgame.addLoggedOutPage not implemented");
});

test("test_Newgame.addInternalErrorPage", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Newgame.addInternalErrorPage not implemented");
});

test("test_Newgame.setCreateGameSuccessMessage", function(assert) {
  Newgame.setCreateGameSuccessMessage(
    'test invocation succeeded',
    { 'gameId': 8, }
  );
  assert.equal(Env.message.type, 'success', "set Env.message to a successful type");
});
