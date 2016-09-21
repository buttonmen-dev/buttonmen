module("Newtourn", {
  'setup': function() {
    BMTestUtils.NewtournPre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the newtourn_page div so functions have something to modify
    if (document.getElementById('newtourn_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'newtourn_page', }));
    }

    Login.pageModule = { 'bodyDivId': 'newtourn_page' };
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
    delete Newtourn.page;
    delete Newtourn.form;
//    delete Newtourn.justCreatedGame;

    Login.pageModule = null;
    Newtourn.activity = {};

    // Page elements
    $('#newtourn_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.NewtournPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.NewtournPost, BMTestUtils.NewtournPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Newtourn module has been loaded
test("test_Newtourn_is_loaded", function(assert) {
  assert.ok(Newtourn, "The Newtourn namespace exists");
});

// The purpose of these tests is to demonstrate that the flow of
// Newtourn.showLoggedInPage() is correct for a showXPage function, namely
// that it calls an API getter with a showStatePage function as a
// callback.
//
// Accomplish this by mocking the invoked functions

test("test_Newtourn.showLoggedInPage", function(assert) {

});

test("test_Newtourn.showLoggedInPage_logged_out", function(assert) {

});

//test("test_Newtourn.getNewtournData", function(assert) {
//  stop();
//  Newtourn.getNewtournData(function() {
//    start();
//  });
//});

test("test_Newtourn.showPage", function(assert) {
  stop();
  Newtourn.showPage();
  var htmlout = Newtourn.page.html();
  assert.ok(htmlout.length > 0,
     "The created page should have nonzero contents");
  start();
});

test("test_Newtourn.actionLoggedOut", function(assert) {
  stop();
  Newtourn.actionLoggedOut();
  assert.equal(Newtourn.form, null,
        "Form is null after the 'logged out' action is processed");
  start();
});

test("test_Newtourn.actionInternalErrorPage", function(assert) {
  stop();
  Newtourn.actionInternalErrorPage();
  assert.equal(Newtourn.form, null,
        "Form is null after the 'internal error' action is processed");
  start();
});

test("test_Newtourn.actionCreateTourn", function(assert) {
  stop();
  Newtourn.actionCreateTourn();
  assert.equal(Newtourn.form, Newtourn.formCreateTourn,
        "Form is set after the 'create game' action is processed");
//    assert.equal($('#n_rounds').val(), 3, 'Rounds should default to 3');
  start();
});

test("test_Newtourn.actionCreateTourn_prevvals", function(assert) {
  stop();
  Newtourn.activity = {
    'type': 'Single Elimination',
    'nPlayer': '8',
    'nRounds': '4',
  };
  Newtourn.actionCreateTourn();

  assert.equal(Newtourn.form, Newtourn.formCreateTourn,
        "Form is set after the 'create tournament' action is processed");
  assert.equal($('#type').val(), 'Single Elimination',
        "Tournament type is retained from previous page activity");
  assert.equal($('#n_player').val(), '8',
        "Number of players is retained from previous page activity");
  assert.equal($('#n_rounds').val(), '4',
        "Number of rounds is retained from previous page activity");
  start();
});

test("test_Newtourn.createMiscOptionsTable", function(assert) {

});

test("test_Newtourn.createTypeRow", function(assert) {
//  stop();
//  Newtourn.getNewtournData(function() {
//    var row = Newtourn.createPlayer1Row();
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

test("test_Newtourn.createNPlayerRow", function(assert) {

});

//
//test("test_Newtourn.createPlayer2Row", function(assert) {
//  stop();
//  Newtourn.activity = {
//    'opponentNames': {
//      tester1 : 'tester1',
//      tester2 : 'tester2',
//      tester3 : 'tester3',
//    },
//  };
//
//  Newtourn.getNewtournData(function() {
//    var row = Newtourn.createPlayer2Row();
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
test("test_Newtourn.createRoundSelectRow", function(assert) {
//  stop();
//  Newtourn.getNewtournData(function() {
//    var row = Newtourn.createRoundSelectRow();
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
//test("test_Newtourn.createPrevGameRow", function(assert) {
//  stop();
//
//  Newtourn.getNewtournData(function() {
//    var row = Newtourn.createPrevGameRow();
//
//    assert.equal(row, null, 'No row should be created when there is no previous game ID');
//    assert.equal(Newtourn.activity.previousGameId, null, 'Previous game ID should be null');
//
//    start();
//  });
//});
//
//test("test_Newtourn.createPrevGameRow_prevgame", function(assert) {
//  stop();
//    Newtourn.activity = {
//    'opponentName': 'tester2',
//    'playerButton': 'Avis',
//    'opponentButton': 'Crab',
//    'nRounds': '4',
//    'isPlayer1Unlocked': true,
//    'playerName': 'responder006',
//    'previousGameId': 12345,
//  };
//
//  Newtourn.getNewtournData(function() {
//    var row = $(Newtourn.createPrevGameRow());
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
test("test_Newtourn.createDescRow", function(assert) {
//  stop();
//
//  Newtourn.getNewtournData(function() {
//    var row = $(Newtourn.createDescRow());
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
//    assert.equal(Newtourn.activity.description, '', 'Stored description should be empty');
//
//    start();
//  });
});

//test("test_Newtourn.createDescRow_with_description", function(assert) {
//  stop();
//
//  Newtourn.activity = {
//    'opponentName': 'tester2',
//    'playerButton': 'Avis',
//    'opponentButton': 'Crab',
//    'nRounds': '4',
//    'isPlayer1Unlocked': true,
//    'playerName': 'responder006',
//    'description': 'test descriptor',
//  };
//
//  Newtourn.getNewtournData(function() {
//    var row = $(Newtourn.createDescRow());
//
//    var row = $(Newtourn.createDescRow());
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
//    assert.equal(Newtourn.activity.description, 'test descriptor', 'Description should be stored');
//
//    start();
//  });
//});


//// The logic here is a little hairy: since Newtourn.getNewtournData()
//// takes a callback, we can use the normal asynchronous logic there.
//// However, the POST done by our forms doesn't take a callback (it
//// just redraws the page), so turn off asynchronous handling in
//// AJAX while we test that, to make sure the test sees the return
//// from the POST.
test("test_Newtourn.formCreateTourn", function(assert) {
//  stop();
//  Newtourn.getNewtournData(function() {
//    Newtourn.actionCreateTourn();
//    $('#opponent_name').val('tester2');
//    $('#player_button').val('Avis');
//    $('#opponent_button').val('Avis');
//    $.ajaxSetup({ async: false });
//    $('#Newtourn_action_button').trigger('click');
//    assert.equal(
//      Env.message.type, "success",
//      "Newtourn action succeeded when expected arguments were set");
//    $.ajaxSetup({ async: true });
//    start();
//  });
});
//
//test("test_Newtourn.formCreateGame_no_vals", function(assert) {
//  stop();
//  Newtourn.getNewtournData(function() {
//    Newtourn.actionCreateGame();
//    $.ajaxSetup({ async: false });
//    $('#Newtourn_action_button').trigger('click');
//    assert.equal(
//      Env.message.type, "error",
//      "Newtourn action failed when expected arguments were not set");
//    $.ajaxSetup({ async: true });
//    start();
//  });
//});
//
//
//test("test_Newtourn.formCreateGame_no_opponent_button", function(assert) {
//  stop();
//  Newtourn.getNewtournData(function() {
//    Newtourn.actionCreateGame();
//    $('#opponent_name').val('tester2');
//    $('#player_button').val('Crab');
//    $.ajaxSetup({ async: false });
//    $('#Newtourn_action_button').trigger('click');
//    assert.equal(
//      Env.message.type, "error",
//      "Newtourn action failed when expected arguments were not set");
//    $.ajaxSetup({ async: true });
//    start();
//  });
//});
//
//test("test_Newtourn.formCreateGame_invalid_player", function(assert) {
//  stop();
//  Newtourn.getNewtournData(function() {
//    Newtourn.actionCreateGame();
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
//    $('#Newtourn_action_button').trigger('click');
//    assert.equal(
//      Env.message.type, "error",
//      "Newtourn action failed when opponent was not a known player");
//    assert.equal(
//      Env.message.text, "Specified opponent nontester1 is not recognized",
//      "Newtourn action failed when opponent was not a known player");
//    $.ajaxSetup({ async: true });
//    start();
//  });
//});
//
test("test_Newtourn.addLoggedOutPage", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Newtourn.addLoggedOutPage not implemented");
});

test("test_Newtourn.addInternalErrorPage", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Newtourn.addInternalErrorPage not implemented");
});

test("test_Newtourn.getSelectRow", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Newtourn.getSelectRow not implemented");
});

test("test_Newtourn.setCreateTournSuccessMessage", function(assert) {
  Newtourn.setCreateTournSuccessMessage(
    'test invocation succeeded',
    { 'tournId': 8, }
  );
  assert.equal(Env.message.type, 'success', "set Env.message to a successful type");
});

test("test_Newtourn.getSelectTd", function(assert) {
  var item = Newtourn.getSelectTd(
    'test items',
    'test_select',
    { 'a': 'First Value', 'b': 'Second Value', },
    { 'b': true, },
    'a');
  assert.equal(item[0].tagName, "TD", "Return value is of type td");
});

test("test_Newtourn.getSelectOptionList", function(assert) {
  var optionlist = Newtourn.getSelectOptionList(
    'test items',
    { 'a': 'First Value', 'b': 'Second Value', },
    { 'b': true, },
    'a');
  assert.equal(optionlist[0].html(), "First Value",
        "Element in option list has expected value");
});
//
//
//test("test_Newtourn.getLimitSelectid", function(assert) {
//  var item = Newtourn.getLimitSelectid('opponent', 'test');
//  assert.equal(item, 'limit_opponent_test', "Expected ID is returned");
//});
//
//test("test_Newtourn.getChoiceId", function(assert) {
//  var item = Newtourn.getChoiceId('opponent', 'test', 'Weird iteM?.');
//  assert.equal(item, 'limit_opponent_test_weird_item', "Expected ID is returned");
//});
//
