module("Newtournament", {
  'setup': function() {
    BMTestUtils.NewtournamentPre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the newtournament_page div so functions have something to modify
    if (document.getElementById('newtournament_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'newtournament_page', }));
    }

    Login.pageModule = { 'bodyDivId': 'newtournament_page' };
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
    delete Newtournament.page;
    delete Newtournament.form;
    delete Newtournament.justCreatedTournament;

    Login.pageModule = null;
    Newtournament.activity = {};

    // Page elements
    $('#newtournament_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.NewtournamentPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.NewtournamentPost, BMTestUtils.NewtournamentPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Newtournament module has been loaded
test("test_Newtournament_is_loaded", function(assert) {
  assert.ok(Newtournament, "The Newtournament namespace exists");
});

// The purpose of these tests is to demonstrate that the flow of
// Newtournament.showLoggedInPage() is correct for a showXPage function, namely
// that it calls an API getter with a showStatePage function as a
// callback.
//
// Accomplish this by mocking the invoked functions

test("test_Newtournament.showLoggedInPage", function(assert) {

});

test("test_Newtournament.showLoggedInPage_logged_out", function(assert) {

});

//test("test_Newtournament.getNewtournamentData", function(assert) {
//  stop();
//  Newtournament.getNewtournamentData(function() {
//    start();
//  });
//});

test("test_Newtournament.showPage", function(assert) {
  stop();
  Newtournament.showPage();
  var htmlout = Newtournament.page.html();
  assert.ok(htmlout.length > 0,
     "The created page should have nonzero contents");
  start();
});

test("test_Newtournament.actionLoggedOut", function(assert) {
  stop();
  Newtournament.actionLoggedOut();
  assert.equal(Newtournament.form, null,
        "Form is null after the 'logged out' action is processed");
  start();
});

test("test_Newtournament.actionInternalErrorPage", function(assert) {
  stop();
  Newtournament.actionInternalErrorPage();
  assert.equal(Newtournament.form, null,
        "Form is null after the 'internal error' action is processed");
  start();
});

test("test_Newtournament.actionCreateTournament", function(assert) {
  stop();
  Newtournament.actionCreateTournament();
  assert.equal(Newtournament.form, Newtournament.formCreateTournament,
        "Form is set after the 'create game' action is processed");
//    assert.equal($('#n_rounds').val(), 3, 'Rounds should default to 3');
  start();
});

test("test_Newtournament.actionCreateTournament_prevvals", function(assert) {
  stop();
  Newtournament.activity = {
    'type': 'Single Elimination',
    'nPlayer': '8',
    'nRounds': '4',
  };
  Newtournament.actionCreateTournament();

  assert.equal(Newtournament.form, Newtournament.formCreateTournament,
        "Form is set after the 'create tournament' action is processed");
  assert.equal($('#type').val(), 'Single Elimination',
        "Tournament type is retained from previous page activity");
  assert.equal($('#n_player').val(), '8',
        "Number of players is retained from previous page activity");
  assert.equal($('#n_rounds').val(), '4',
        "Number of rounds is retained from previous page activity");
  start();
});

test("test_Newtournament.createMiscOptionsTable", function(assert) {

});

test("test_Newtournament.createTypeRow", function(assert) {
//  stop();
//  Newtournament.getNewtournamentData(function() {
//    var row = Newtournament.createPlayer1Row();
//    assert.ok(row.is('tr'), 'Function should return a row');
//    assert.equal(row.children().length, 2, 'Row should have two children');
//
//    var header = $(row.children()[0]);
//    assert.ok(header.is('th'), 'First element should be a header cell');
//    assert.equal(header.text(), 'You:', 'Header cell should contain correct text');
//
//    var contents = $(row.children()[1]);
//    assert.ok(contents.is('td'), 'Second element should be a standard cell');
//    assert.equal(contents.text(), 'tester1', 'Standard cell should contain correct text');
//    assert.equal(contents.children().length, 1, 'Contents should have a child');
//
//    var toggle = $(contents.children()[0]);
//    assert.ok(toggle.is('input'), 'Contents should contain an input toggle');
//
//    start();
//  });
});

test("test_Newtournament.createNPlayerRow", function(assert) {

});

//
//test("test_Newtournament.createPlayer2Row", function(assert) {
//  stop();
//  Newtournament.activity = {
//    'opponentNames': {
//      tester1 : 'tester1',
//      tester2 : 'tester2',
//      tester3 : 'tester3',
//    },
//  };
//
//  Newtournament.getNewtournamentData(function() {
//    var row = Newtournament.createPlayer2Row();
//    assert.ok(row.is('tr'), 'Function should return a row');
//    assert.equal(row.children().length, 2, 'Row should have two children');
//
//    var header = $(row.children()[0]);
//    assert.ok(header.is('th'), 'First element should be a header cell');
//    assert.equal(header.text(), 'Opponent:', 'Header cell should contain correct text');
//
//    var contents = $(row.children()[1]);
//    assert.ok(contents.is('td'), 'Second element should be a standard cell');
//    assert.equal(contents.children().length, 1, 'Contents should have a child');
//
//    var select = $(contents.children()[0]);
//    assert.ok(select.is('select'), 'Standard cell should contain a select');
//    assert.equal(select.children().length, 4, 'Select should have four children');
//
//    var firstoption = $(select.children()[0]);
//    assert.ok(firstoption.is('option'), 'Select should contain options');
//    assert.equal(firstoption.prop('value'), '', 'First option should have the correct value');
//    assert.equal(firstoption.text(), 'Anybody', 'First option should have the correct text');
//
//    for (var optionIdx = 1; optionIdx <= 3; optionIdx++) {
//      var option = $(select.children()[optionIdx]);
//      assert.ok(option.is('option'), 'Select should contain options');
//      assert.equal(option.prop('value'), 'tester' + optionIdx, 'Option ' + optionIdx + ' should have the correct value');
//      assert.equal(option.text(), 'tester' + optionIdx, 'Option ' + optionIdx + ' should have the correct text');
//    }
//
//    start();
//  });
//});
//
test("test_Newtournament.createRoundSelectRow", function(assert) {
//  stop();
//  Newtournament.getNewtournamentData(function() {
//    var row = Newtournament.createRoundSelectRow();
//    assert.ok(row.is('tr'), 'Function should return a row');
//    assert.equal(row.children().length, 2, 'Row should have two children');
//
//    var header = $(row.children()[0]);
//    assert.ok(header.is('th'), 'First element should be a header cell');
//    assert.equal(header.text(), 'Winner is first player to win:', 'Header cell should contain correct text');
//
//    var contents = $(row.children()[1]);
//    assert.ok(contents.is('td'), 'Second element should be a standard cell');
//    assert.equal(contents.children().length, 1, 'Contents should have one child');
//
//    var select = $(contents.children()[0]);
//    assert.ok(select.is('select'), 'Contents should contain a select');
//    assert.equal(select.children().length, 5, 'Select should have five children');
//
//    for (var optionIdx = 1; optionIdx <= 5; optionIdx++) {
//      var option = $(select.children()[optionIdx - 1]);
//      assert.ok(option.is('option'), 'Select should contain options');
//      assert.equal(option.prop('value'), optionIdx, 'Option ' + optionIdx + ' should have the correct value');
//      assert.equal(option.text(), optionIdx + ' round' + (optionIdx > 1 ? 's' : ''), 'Option ' + optionIdx + ' should have the correct text');
//    }
//
//    start();
//  });
});
//
//test("test_Newtournament.createPrevGameRow", function(assert) {
//  stop();
//
//  Newtournament.getNewtournamentData(function() {
//    var row = Newtournament.createPrevGameRow();
//
//    assert.equal(row, null, 'No row should be created when there is no previous game ID');
//    assert.equal(Newtournament.activity.previousGameId, null, 'Previous game ID should be null');
//
//    start();
//  });
//});
//
//test("test_Newtournament.createPrevGameRow_prevgame", function(assert) {
//  stop();
//    Newtournament.activity = {
//    'opponentName': 'tester2',
//    'playerButton': 'Avis',
//    'opponentButton': 'Crab',
//    'nRounds': '4',
//    'isPlayer1Unlocked': true,
//    'playerName': 'responder006',
//    'previousGameId': 12345,
//  };
//
//  Newtournament.getNewtournamentData(function() {
//    var row = $(Newtournament.createPrevGameRow());
//    assert.ok(row.is('tr'), 'Function should return a row');
//    assert.equal(row.children().length, 2, 'Row should have two children');
//
//    var header = $(row.children()[0]);
//    assert.ok(header.is('th'), 'First element should be a header cell');
//    assert.equal(header.text(), 'Copy chat from:', 'Header cell should contain correct text');
//
//    var contents = $(row.children()[1]);
//    assert.ok(contents.is('td'), 'Second element should be a standard cell');
//    assert.equal(contents.children().length, 1, 'Contents should have one child');
//    assert.equal(contents.text(), 'Game 12345', 'Contents should have the correct text');
//
//    start();
//  });
//});
//
test("test_Newtournament.createDescRow", function(assert) {
//  stop();
//
//  Newtournament.getNewtournamentData(function() {
//    var row = $(Newtournament.createDescRow());
//    assert.ok(row.is('tr'), 'A row should be created even when there is no description');
//    assert.equal(row.children().length, 2, 'Row should have two children');
//
//    var header = $(row.children()[0]);
//    assert.ok(header.is('th'), 'First element should be a header cell');
//    assert.equal(header.text(), 'Description (optional):', 'Header cell should contain correct text');
//
//    var contents = $(row.children()[1]);
//    assert.ok(contents.is('td'), 'Second element should be a standard cell');
//    assert.equal(contents.children().length, 1, 'Contents should have one child');
//
//    var textarea = $(contents.children()[0]);
//    assert.ok(textarea.is('textarea'), 'Contents should contain a textarea');
//    assert.equal(textarea.text(), '', 'Textarea should contain no text');
//
//    assert.equal(Newtournament.activity.description, '', 'Stored description should be empty');
//
//    start();
//  });
});

//test("test_Newtournament.createDescRow_with_description", function(assert) {
//  stop();
//
//  Newtournament.activity = {
//    'opponentName': 'tester2',
//    'playerButton': 'Avis',
//    'opponentButton': 'Crab',
//    'nRounds': '4',
//    'isPlayer1Unlocked': true,
//    'playerName': 'responder006',
//    'description': 'test descriptor',
//  };
//
//  Newtournament.getNewtournamentData(function() {
//    var row = $(Newtournament.createDescRow());
//
//    var row = $(Newtournament.createDescRow());
//    assert.ok(row.is('tr'), 'A row should be created when there is a description');
//    assert.equal(row.children().length, 2, 'Row should have two children');
//
//    var header = $(row.children()[0]);
//    assert.ok(header.is('th'), 'First element should be a header cell');
//    assert.equal(header.text(), 'Description (optional):', 'Header cell should contain correct text');
//
//    var contents = $(row.children()[1]);
//    assert.ok(contents.is('td'), 'Second element should be a standard cell');
//    assert.equal(contents.children().length, 1, 'Contents should have one child');
//
//    var textarea = $(contents.children()[0]);
//    assert.ok(textarea.is('textarea'), 'Contents should contain a textarea');
//    assert.equal(textarea.text(), 'test descriptor', 'Textarea should contain no text');
//
//    assert.equal(Newtournament.activity.description, 'test descriptor', 'Description should be stored');
//
//    start();
//  });
//});


//// The logic here is a little hairy: since Newtournament.getNewtournamentData()
//// takes a callback, we can use the normal asynchronous logic there.
//// However, the POST done by our forms doesn't take a callback (it
//// just redraws the page), so turn off asynchronous handling in
//// AJAX while we test that, to make sure the test sees the return
//// from the POST.
test("test_Newtournament.formCreateTournament", function(assert) {
//  stop();
//  Newtournament.getNewtournamentData(function() {
//    Newtournament.actionCreateTournament();
//    $('#opponent_name').val('tester2');
//    $('#player_button').val('Avis');
//    $('#opponent_button').val('Avis');
//    $.ajaxSetup({ async: false });
//    $('#Newtournament_action_button').trigger('click');
//    assert.equal(
//      Env.message.type, "success",
//      "Newtournament action succeeded when expected arguments were set");
//    $.ajaxSetup({ async: true });
//    start();
//  });
});
//
//test("test_Newtournament.formCreateGame_no_vals", function(assert) {
//  stop();
//  Newtournament.getNewtournamentData(function() {
//    Newtournament.actionCreateGame();
//    $.ajaxSetup({ async: false });
//    $('#Newtournament_action_button').trigger('click');
//    assert.equal(
//      Env.message.type, "error",
//      "Newtournament action failed when expected arguments were not set");
//    $.ajaxSetup({ async: true });
//    start();
//  });
//});
//
//
//test("test_Newtournament.formCreateGame_no_opponent_button", function(assert) {
//  stop();
//  Newtournament.getNewtournamentData(function() {
//    Newtournament.actionCreateGame();
//    $('#opponent_name').val('tester2');
//    $('#player_button').val('Crab');
//    $.ajaxSetup({ async: false });
//    $('#Newtournament_action_button').trigger('click');
//    assert.equal(
//      Env.message.type, "error",
//      "Newtournament action failed when expected arguments were not set");
//    $.ajaxSetup({ async: true });
//    start();
//  });
//});
//
//test("test_Newtournament.formCreateGame_invalid_player", function(assert) {
//  stop();
//  Newtournament.getNewtournamentData(function() {
//    Newtournament.actionCreateGame();
//    $('#opponent_name').append(
//      $('<option>', {
//        'value': 'nontester1',
//        'text': 'nontester1',
//        'label': 'nontester1',
//      })
//    );
//    $('#opponent_name').val('nontester1');
//    $('#player_button').val('Crab');
//    $('#opponent_button').val('John Kovalic');
//    $.ajaxSetup({ async: false });
//    $('#Newtournament_action_button').trigger('click');
//    assert.equal(
//      Env.message.type, "error",
//      "Newtournament action failed when opponent was not a known player");
//    assert.equal(
//      Env.message.text, "Specified opponent nontester1 is not recognized",
//      "Newtournament action failed when opponent was not a known player");
//    $.ajaxSetup({ async: true });
//    start();
//  });
//});
//
test("test_Newtournament.addLoggedOutPage", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Newtournament.addLoggedOutPage not implemented");
});

test("test_Newtournament.addInternalErrorPage", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Newtournament.addInternalErrorPage not implemented");
});

test("test_Newtournament.getSelectRow", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Newtournament.getSelectRow not implemented");
});

test("test_Newtournament.setCreateTournamentSuccessMessage", function(assert) {
  Newtournament.setCreateTournamentSuccessMessage(
    'test invocation succeeded',
    { 'tournamentId': 8, }
  );
  assert.equal(Env.message.type, 'success', "set Env.message to a successful type");
});

test("test_Newtournament.getSelectTd", function(assert) {
  var item = Newtournament.getSelectTd(
    'test items',
    'test_select',
    { 'a': 'First Value', 'b': 'Second Value', },
    { 'b': true, },
    'a');
  assert.equal(item[0].tagName, "TD", "Return value is of type td");
});

test("test_Newtournament.getSelectOptionList", function(assert) {
  var optionlist = Newtournament.getSelectOptionList(
    'test items',
    { 'a': 'First Value', 'b': 'Second Value', },
    { 'b': true, },
    'a');
  assert.equal(optionlist[0].html(), "First Value",
        "Element in option list has expected value");
});
//
//
//test("test_Newtournament.getLimitSelectid", function(assert) {
//  var item = Newtournament.getLimitSelectid('opponent', 'test');
//  assert.equal(item, 'limit_opponent_test', "Expected ID is returned");
//});
//
//test("test_Newtournament.getChoiceId", function(assert) {
//  var item = Newtournament.getChoiceId('opponent', 'test', 'Weird iteM?.');
//  assert.equal(item, 'limit_opponent_test_weird_item', "Expected ID is returned");
//});
//
