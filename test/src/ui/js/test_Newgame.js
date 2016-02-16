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
  var cached_getNewgameData = Newgame.getNewgameData;
  var cached_showStatePage = Newgame.showPage;
  var getNewgameDataCalled = false;
  Newgame.showPage = function() {
    assert.ok(getNewgameDataCalled, "Newgame.getNewgameData is called before Newgame.showPage");
  }
  Newgame.getNewgameData = function(callback) {
    getNewgameDataCalled = true;
    assert.equal(callback, Newgame.showPage,
      "Newgame.getNewgameData is called with Newgame.showPage as an argument");
    callback();
  }

  Newgame.showLoggedInPage();

  Newgame.getNewgameData = cached_getNewgameData;
  Newgame.showPage = cached_showStatePage;
});

test("test_Newgame.showLoggedInPage_logged_out", function(assert) {
  expect(4);

  // Undo the fake login data
  Login.player = null;
  Login.logged_in = false;

  var cached_getNewgameData = Newgame.getNewgameData;
  var cached_showStatePage = Newgame.showPage;
  var getNewgameDataCalled = false;
  Newgame.showPage = function() {
    assert.ok(getNewgameDataCalled, "Newgame.getNewgameData is called before Newgame.showPage");
  }
  Newgame.getNewgameData = function(callback) {
    getNewgameDataCalled = true;
    assert.equal(callback, Newgame.showPage,
      "Newgame.getNewgameData is called with Newgame.showPage as an argument");
    callback();
  }

  Newgame.showLoggedInPage();

  Newgame.getNewgameData = cached_getNewgameData;
  Newgame.showPage = cached_showStatePage;
});

test("test_Newgame.getNewgameData", function(assert) {
  stop();
  Newgame.getNewgameData(function() {
    assert.ok(Api.player, "player list is parsed from server");
    assert.ok(Api.button, "button list is parsed from server");
    start();
  });
});

test("test_Newgame.showPage", function(assert) {
  stop();
  Newgame.getNewgameData(function() {
    Newgame.showPage();
    var htmlout = Newgame.page.html();
    assert.ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

test("test_Newgame.showPage_button_load_failed", function(assert) {
  stop();
  Newgame.getNewgameData(function() {
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
  Newgame.getNewgameData(function() {
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
  Newgame.getNewgameData(function() {
    Newgame.actionLoggedOut();
    assert.equal(Newgame.form, null,
          "Form is null after the 'logged out' action is processed");
    start();
  });
});

test("test_Newgame.actionInternalErrorPage", function(assert) {
  stop();
  Newgame.getNewgameData(function() {
    Newgame.actionInternalErrorPage();
    assert.equal(Newgame.form, null,
          "Form is null after the 'internal error' action is processed");
    start();
  });
});

test("test_Newgame.actionCreateGame", function(assert) {
  stop();
  Newgame.getNewgameData(function() {
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
    'playerButton': 'Avis',
    'opponentButton': 'Crab',
    'nRounds': '4',
  };
  Newgame.getNewgameData(function() {
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
    start();
  });
});

// The logic here is a little hairy: since Newgame.getNewgameData()
// takes a callback, we can use the normal asynchronous logic there.
// However, the POST done by our forms doesn't take a callback (it
// just redraws the page), so turn off asynchronous handling in
// AJAX while we test that, to make sure the test sees the return
// from the POST.
test("test_Newgame.formCreateGame", function(assert) {
  stop();
  Newgame.getNewgameData(function() {
    Newgame.actionCreateGame();
    $('#opponent_name').val('tester2');
    $('#player_button').val('Avis');
    $('#opponent_button').val('Avis');
    $.ajaxSetup({ async: false });
    $('#newgame_action_button').trigger('click');
    assert.equal(
      Env.message.type, "success",
      "Newgame action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Newgame.formCreateGame_no_vals", function(assert) {
  stop();
  Newgame.getNewgameData(function() {
    Newgame.actionCreateGame();
    $.ajaxSetup({ async: false });
    $('#newgame_action_button').trigger('click');
    assert.equal(
      Env.message.type, "error",
      "Newgame action failed when expected arguments were not set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Newgame.formCreateGame_no_buttons", function(assert) {
  stop();
  Newgame.getNewgameData(function() {
    Newgame.actionCreateGame();
    $('#opponent_name').val('tester2');
    $.ajaxSetup({ async: false });
    $('#newgame_action_button').trigger('click');
    assert.equal(
      Env.message.type, "error",
      "Newgame action failed when expected arguments were not set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Newgame.formCreateGame_no_opponent_button", function(assert) {
  stop();
  Newgame.getNewgameData(function() {
    Newgame.actionCreateGame();
    $('#opponent_name').val('tester2');
    $('#player_button').val('Crab');
    $.ajaxSetup({ async: false });
    $('#newgame_action_button').trigger('click');
    assert.equal(
      Env.message.type, "error",
      "Newgame action failed when expected arguments were not set");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Newgame.formCreateGame_invalid_player", function(assert) {
  stop();
  Newgame.getNewgameData(function() {
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
      "Newgame action failed when opponent was not a known player");
    assert.equal(
      Env.message.text, "Specified opponent nontester1 is not recognized",
      "Newgame action failed when opponent was not a known player");
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

test("test_Newgame.getSelectRow", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Newgame.getSelectRow not implemented");
});

test("test_Newgame.setCreateGameSuccessMessage", function(assert) {
  Newgame.setCreateGameSuccessMessage(
    'test invocation succeeded',
    { 'gameId': 8, }
  );
  assert.equal(Env.message.type, 'success', "set Env.message to a successful type");
});

test("test_Newgame.getSelectTd", function(assert) {
  var item = Newgame.getSelectTd(
    'test items',
    'test_select',
    { 'a': 'First Value', 'b': 'Second Value', },
    { 'b': true, },
    'a');
  assert.equal(item[0].tagName, "TD", "Return value is of type td");
});

test("test_Newgame.getSelectOptionList", function(assert) {
  var optionlist = Newgame.getSelectOptionList(
    'test items',
    { 'a': 'First Value', 'b': 'Second Value', },
    { 'b': true, },
    'a');
  assert.equal(optionlist[0].html(), "First Value",
        "Element in option list has expected value");
});

test("test_Newgame.getButtonSelectTd", function(assert) {
  Newgame.activity = {
    'buttonList': {
      'player': {
        'Avis': 'Avis: (4) (4) (10) (12) (X)',
        'Jellybean': 'Jellybean: p(20) s(20) (V) (X)',
      },
      'opponent': {
        'Avis': 'Avis: (4) (4) (10) (12) (X)',
        'Adam Spam': '-- Adam Spam: F(4) F(6) (6) (12) (X)',
      },
    },
    'buttonGreyed': { 'Adam Spam': true, },
    'playerButton': null,
    'opponentButton': null,
  };
  var item = Newgame.getButtonSelectTd();
  assert.equal(item[0].tagName, "TD", "Return value is of type td");
});

test("test_Newgame.updateButtonSelectTd", function(assert) {
  stop();
  Newgame.getNewgameData(function() {
    Newgame.actionCreateGame();
    var item1 = $('#player_button');
    assert.equal(item1[0].tagName, "SELECT",
      "Player button select is a select before update");
    assert.ok(item1.html().match("Avis"),
      "before update, Avis is in the list of button options");
    assert.ok(item1.html().match("John Kovalic"),
      "before update, John Kovalic is in the list of button options");
    delete(Newgame.activity.buttonList.player["John Kovalic"]);
    Newgame.updateButtonSelectTd('player');
    assert.ok(item1.html().match("Avis"),
      "after update, Avis is in the list of button options");
    assert.ok(!item1.html().match("John Kovalic"),
      "after update, John Kovalic is not in the list of button options");
    start();
  });
});

test("test_Newgame.updateButtonList", function(assert) {
  stop();
  Newgame.getNewgameData(function() {
    Newgame.actionCreateGame();

    // baseline checks before update
    var anyOption = null;
    var bromOption = null;
    $.each($('#limit_opponent_button_sets option'), function() {
      if ($(this).val() == "limit_opponent_button_sets_brom") {
        bromOption = $(this);
      } else if ($(this).val() == "ANY") {
        anyOption = $(this);
      }
    });
    assert.ok(("Avis" in Newgame.activity.buttonList.opponent),
      "before update, Avis is included in the set of available buttons for the opponent");
    assert.ok(("Jellybean" in Newgame.activity.buttonList.opponent),
      "before update, Jellybean is included in the set of available buttons for the opponent");

    // now deselect the ANY button set and select the BROM button set
    anyOption.removeAttr('selected');
    bromOption.attr('selected', 'selected');

    // now call updateButtonList, and make sure Jellybean (in BROM) is still in the button list,
    // but Avis (in Soldiers, not in BROM) is gone
    Newgame.updateButtonList('opponent', 'button_sets');
    assert.ok(!("Avis" in Newgame.activity.buttonList.opponent),
      "after list update, Avis is not included in the set of available buttons for the opponent");
    assert.ok(("Jellybean" in Newgame.activity.buttonList.opponent),
      "after update, Jellybean is still included in the set of available buttons for the opponent");
    start();
  });
});

test("test_Newgame.getButtonLimitTd", function(assert) {
  Newgame.activity = {
    'buttonLimits': {
      'player': {
        'test_limit': {
          'ANY': true,
          'limit_player_test_limit_a': false,
          'limit_player_test_limit_b_c': false,
        },
      },
    },
  };
  var item = Newgame.getButtonLimitTd(
    'player',
    'Description text',
    'test_limit',
    { 'A': true,
      'B C': true,
    });
  assert.equal(item[0].tagName, "TD", "result is a TD");
  var buttonSelect = item.find('select');
  assert.ok(buttonSelect, "TD contains a select");
  var foundLabels = { 'ANY': 0, 'A': 0, 'B C': 0, };
  $.each(buttonSelect.children(), function(idx, child) {
    foundLabels[child.label] += 1;
    if (child.label == 'ANY') {
      assert.equal(child.selected, true, 'ANY option is initially selected');
    } else {
      assert.equal(child.selected, false, 'Other options are not initially selected');
    }
  });
  assert.deepEqual(foundLabels, { 'ANY': 1, 'A': 1, 'B C': 1, },
    "Expected set of option labels was found");
});

test("test_Newgame.getButtonLimitTd_prevvals", function(assert) {
  Newgame.activity = {
    'buttonLimits': {
      'player': {
        'test_limit': {
          'ANY': false,
          'limit_player_test_limit_a': false,
          'limit_player_test_limit_b_c': true,
        },
      },
    },
  };
  var item = Newgame.getButtonLimitTd(
    'player',
    'Description text',
    'test_limit',
    { 'A': true,
      'B C': true,
    });
  assert.equal(item[0].tagName, "TD", "result is a TD");
  var buttonSelect = item.find('select');
  assert.ok(buttonSelect, "TD contains a select");
  var foundLabels = { 'ANY': 0, 'A': 0, 'B C': 0, };
  $.each(buttonSelect.children(), function(idx, child) {
    foundLabels[child.label] += 1;
    if (child.label == 'B C') {
      assert.equal(child.selected, true, 'Previously specified "B C" option is initially selected');
    } else {
      assert.equal(child.selected, false, 'Other options are not initially selected');
    }
  });
  assert.deepEqual(foundLabels, { 'ANY': 1, 'A': 1, 'B C': 1, },
    "Expected set of option labels was found");
});

test("test_Newgame.getButtonLimitRow", function(assert) {
  Newgame.activity = {
    'buttonLimits': {
      'player': {
        'test_limit': {
          'A': false,
          'B C': true,
        },
      },
      'opponent': {
        'test_limit': {
          'A': true,
          'B C': true,
        },
      },
    },
  };
  var item = Newgame.getButtonLimitRow(
    'Description text',
    'test_limit',
    { 'A': true,
      'B C': true,
    });
  assert.equal(item[0].tagName, "TR", "result is a TR");
});

test("test_Newgame.getLimitSelectid", function(assert) {
  var item = Newgame.getLimitSelectid('opponent', 'test');
  assert.equal(item, 'limit_opponent_test', "Expected ID is returned");
});

test("test_Newgame.getChoiceId", function(assert) {
  var item = Newgame.getChoiceId('opponent', 'test', 'Weird iteM?.');
  assert.equal(item, 'limit_opponent_test_weird_item', "Expected ID is returned");
});

test("test_Newgame.initializeButtonLimits", function(assert) {
  Newgame.activity = {
    'buttonSets': {
      'Set 1': true,
      'Set 2': true,
    },
    'tournLegal': {
      'yes': true,
      'no': true,
    },
    'dieSkills': {
      'Test': true,
    },
  };

  Newgame.initializeButtonLimits();
  assert.equal(Newgame.activity.buttonLimits.opponent.tourn_legal.ANY, true,
    "First initialization of button limits sets expected value for ANY");
  assert.equal(Newgame.activity.buttonLimits.opponent.tourn_legal.limit_opponent_tourn_legal_no, false,
    "First initialization of button limits sets expected value for other options");

  Newgame.activity.buttonLimits.opponent.tourn_legal.limit_player_tourn_legal_no = true;
  Newgame.initializeButtonLimits();
  assert.equal(Newgame.activity.buttonLimits.opponent.tourn_legal.limit_player_tourn_legal_no, true,
    "Second initialization of button limits does not override modified values");
});
