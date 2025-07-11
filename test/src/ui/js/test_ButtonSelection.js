module("ButtonSelection", {
  'setup': function() {
    BMTestUtils.ButtonSelectionPre = BMTestUtils.getAllElements();

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
    Login.pageModule = null;
    ButtonSelection.activity = {};
    //Newgame.activity = {};

    // Page elements
    $('#newgame_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.ButtonSelectionPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.ButtonSelectionPost, BMTestUtils.ButtonSelectionPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the ButtonSelection module has been loaded
test("test_ButtonSelection_is_loaded", function(assert) {
  assert.ok(ButtonSelection, "The ButtonSelection namespace exists");
});

test("test_ButtonSelection.getButtonSelectionData", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    assert.ok(Api.player, "player list is parsed from server");
    assert.ok(Api.button, "button list is parsed from server");
    start();
  });
});

test("test_ButtonSelection.getSelectRow", function(assert) {
  assert.ok(
    true, 
    "INCOMPLETE: Test of ButtonSelection.getSelectRow not implemented"
  );
});

test("test_ButtonSelection.getSelectTd", function(assert) {
  var item = ButtonSelection.getSelectTd(
    'test items',
    'test_select',
    { 'a': 'First Value', 'b': 'Second Value', },
    { 'b': true, },
    'a',
    null);
  assert.equal(item[0].tagName, "TD", "Return value is of type td");
});

test("test_ButtonSelection.getSelectOptionList", function(assert) {
  var optionlist = ButtonSelection.getSelectOptionList(
    'test items',
    { 'a': 'First Value', 'b': 'Second Value', },
    { 'b': true, },
    'a');
  assert.equal(optionlist[0].html(), "First Value",
        "Element in option list has expected value");
});

test("test_ButtonSelection.getCustomRecipeTd", function(assert) {
  // currently empty
});

test("test_ButtonSelection.getButtonSelectTd", function(assert) {
  ButtonSelection.activity = {
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
    'buttonLimits': {
      'player': {
        'button_sets': {
          'ANY': true
        },
        'tourn_legal': {
          'ANY': true
        },
        'die_skills': {
          'ANY': true
        }
      },
      'opponent': {
        'button_sets': {
          'ANY': true,
          'limit_opponent_button_sets_50_states': false
        },
        'tourn_legal': {
          'ANY': true
        },
        'die_skills': {
          'ANY': true
        }
      }
    },
    'playerButton': null,
    'opponentButton': null,
  };
  var playerTd = ButtonSelection.getButtonSelectTd('player')[0];
  assert.equal(playerTd.tagName, 'TD', 'Return value is of type td');
  assert.equal(playerTd.childElementCount, 1, 'player TD should have one child');
  var playerTdSelect = playerTd.firstChild;
  assert.equal(playerTdSelect.childElementCount, 3, 'player TD select should have three children');
  assert.equal(playerTdSelect.children[0].text, 'Choose your button', 'The first row in the player TD select should ask the player to select a button');
  assert.equal(playerTdSelect.children[0].value, '', 'The first row in the player TD select should have an empty value');
  assert.equal(playerTdSelect.children[1].text, 'Avis: (4) (4) (10) (12) (X)', 'The second row in the player TD select should show the recipe for Avis');
  assert.equal(playerTdSelect.children[1].value, 'Avis', 'The second row in the player TD select should have a value of Avis');
  assert.equal(playerTdSelect.children[2].text, 'Jellybean: p(20) s(20) (V) (X)', 'The third row in the player TD select should show the recipe for Jellybean');
  assert.equal(playerTdSelect.children[2].value, 'Jellybean', 'The third row in the player TD select should have a value of Jellybean');

  var opponentTd = ButtonSelection.getButtonSelectTd('opponent')[0];
  assert.equal(opponentTd.tagName, 'TD', 'Return value is of type td');
  assert.equal(opponentTd.childElementCount, 1, 'opponent TD should have one child');
  var opponentTdSelect = opponentTd.firstChild;
  assert.equal(opponentTdSelect.childElementCount, 3, 'opponent TD select should have three children');
  assert.equal(opponentTdSelect.children[0].text, 'Any button', 'The first row in the opponent TD select should be any button');
  assert.equal(opponentTdSelect.children[0].value, '', 'The first row in the opponent TD select should have an empty value');
  assert.equal(opponentTdSelect.children[1].text, 'Avis: (4) (4) (10) (12) (X)', 'The second row in the opponent TD select should show the recipe for Avis');
  assert.equal(opponentTdSelect.children[1].value, 'Avis', 'The second row in the opponent TD select should have a value of Avis');
  assert.equal(opponentTdSelect.children[2].text, '-- Adam Spam: F(4) F(6) (6) (12) (X)', 'The third row in the opponent TD select should show the recipe for Adam Spam');
  assert.equal(opponentTdSelect.children[2].value, 'Adam Spam', 'The third row in the opponent TD select should have a value of Adam Spam');

  ButtonSelection.activity.buttonLimits.opponent.button_sets.ANY = false;
  ButtonSelection.activity.buttonLimits.opponent.button_sets.limit_opponent_button_sets_50_states = true;

  var opponentTdLimited = ButtonSelection.getButtonSelectTd('opponent')[0];
  assert.equal(opponentTdLimited.tagName, 'TD', 'Return value is of type td');
  assert.equal(opponentTdLimited.childElementCount, 1, 'opponent TD should have one child');
  var opponentTdLimitedSelect = opponentTdLimited.firstChild;
  assert.equal(opponentTdLimitedSelect.childElementCount, 3, 'limited opponent TD select should have three children');
  assert.equal(opponentTdLimitedSelect.children[0].text, 'Choose opponent\'s button', 'The first row in the limited opponent TD select should ask the player to select a button for the opponent');
  assert.equal(opponentTdLimitedSelect.children[0].value, '', 'The first row in the limited opponent TD select should have an empty value');
  assert.equal(opponentTdLimitedSelect.children[1].text, 'Avis: (4) (4) (10) (12) (X)', 'The second row in the limited opponent TD select should show the recipe for Avis');
  assert.equal(opponentTdLimitedSelect.children[1].value, 'Avis', 'The second row in the limited opponent TD select should have a value of Avis');
  assert.equal(opponentTdLimitedSelect.children[2].text, '-- Adam Spam: F(4) F(6) (6) (12) (X)', 'The third row in the limited opponent TD select should show the recipe for Adam Spam');
  assert.equal(opponentTdLimitedSelect.children[2].value, 'Adam Spam', 'The third row in the limited opponent TD select should have a value of Adam Spam');
});

test("test_ButtonSelection.reactToButtonChange", function(assert) {
  // currently empty
});

test("test_ButtonSelection.updateButtonSelectTd", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    ButtonSelection.loadButtonsIntoDicts();
    $('#newgame_page').append(
      ButtonSelection.getSingleButtonOptionsTable('player')        
    );
    $('#newgame_page').append(
      ButtonSelection.getSingleButtonOptionsTable('opponent')        
    );

    var item1 = $('#player_button');
    assert.equal(item1[0].tagName, "SELECT",
      "Player button select is a select before update");
    assert.ok(item1.html().match("Avis"),
      "before update, Avis is in the list of button options");
    assert.ok(item1.html().match("John Kovalic"),
      "before update, John Kovalic is in the list of button options");
    delete(ButtonSelection.activity.buttonList.player["John Kovalic"]);
    ButtonSelection.updateButtonSelectTd('player');
    assert.ok(item1.html().match("Avis"),
      "after update, Avis is in the list of button options");
    assert.ok(!item1.html().match("John Kovalic"),
      "after update, John Kovalic is not in the list of button options");
    start();
  });
});

test("test_ButtonSelection.updateButtonList", function(assert) {
  stop();
  ButtonSelection.getButtonSelectionData(function() {
    ButtonSelection.loadButtonsIntoDicts();
    $('#newgame_page').append(
      ButtonSelection.getSingleButtonOptionsTable('player')        
    );
    $('#newgame_page').append(
      ButtonSelection.getSingleButtonOptionsTable('opponent')        
    );

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
    assert.ok(("Avis" in ButtonSelection.activity.buttonList.opponent),
      "before update, Avis is included in the set of available buttons for the opponent");
    assert.ok(("Jellybean" in ButtonSelection.activity.buttonList.opponent),
      "before update, Jellybean is included in the set of available buttons for the opponent");

    // now deselect the ANY button set and select the BROM button set
    anyOption.removeAttr('selected');
    bromOption.attr('selected', 'selected');

    // now call updateButtonList, and make sure Jellybean (in BROM) is still in the button list,
    // but Avis (in Soldiers, not in BROM) is gone
    ButtonSelection.updateButtonList('opponent', 'button_sets');
    assert.ok(!("Avis" in ButtonSelection.activity.buttonList.opponent),
      "after list update, Avis is not included in the set of available buttons for the opponent");
    assert.ok(("Jellybean" in ButtonSelection.activity.buttonList.opponent),
      "after update, Jellybean is still included in the set of available buttons for the opponent");
    start();
  });
});

test("test_ButtonSelection.getButtonLimitRow", function(assert) {
  ButtonSelection.activity = {
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
  var itemPlayer = ButtonSelection.getButtonLimitRow(
    'Description text',
    'test_limit',
    { 'A': true,
      'B C': true,
    });
  assert.equal(itemPlayer[0].tagName, "TR", "result is a TR");
  
  var itemOpponent = ButtonSelection.getButtonLimitRow(
    'Description text',
    'test_limit',
    { 'A': true,
      'B C': true,
    },
    true,
    'opponent');
  assert.equal(itemOpponent[0].tagName, "TR", "result is a TR");
});

test("test_ButtonSelection.getButtonLimitTd", function(assert) {
  ButtonSelection.activity = {
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
  var item = ButtonSelection.getButtonLimitTd(
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

test("test_ButtonSelection.getButtonLimitTd_prevvals", function(assert) {
  ButtonSelection.activity = {
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
  var item = ButtonSelection.getButtonLimitTd(
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

test("test_ButtonSelection.getLimitSelectid", function(assert) {
  var item = ButtonSelection.getLimitSelectid('opponent', 'test');
  assert.equal(item, 'limit_opponent_test', "Expected ID is returned");
});

test("test_ButtonSelection.getChoiceId", function(assert) {
  var item = ButtonSelection.getChoiceId('opponent', 'test', 'Weird iteM?.');
  assert.equal(item, 'limit_opponent_test_weird_item', "Expected ID is returned");
});

test("test_ButtonSelection.initializeButtonLimits", function(assert) {
  ButtonSelection.activity = {
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

  ButtonSelection.initializeButtonLimits();
  assert.equal(ButtonSelection.activity.buttonLimits.opponent.tourn_legal.ANY, true,
    "First initialization of button limits sets expected value for ANY");
  assert.equal(ButtonSelection.activity.buttonLimits.opponent.tourn_legal.limit_opponent_tourn_legal_no, false,
    "First initialization of button limits sets expected value for other options");

  ButtonSelection.activity.buttonLimits.opponent.tourn_legal.limit_player_tourn_legal_no = true;
  ButtonSelection.initializeButtonLimits();
  assert.equal(ButtonSelection.activity.buttonLimits.opponent.tourn_legal.limit_player_tourn_legal_no, true,
    "Second initialization of button limits does not override modified values");
});

test("test_ButtonSelection.loadButtonsIntoDicts", function(assert) {
  // currently empty
});

test("test_ButtonSelection.getSingleButtonOptionsTable", function(assert) {
  stop();
  
  ButtonSelection.activity = {
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
    'buttonLimits': {
      'player': {
        'button_sets': {
          'ANY': true
        },
        'tourn_legal': {
          'ANY': true
        },
        'die_skills': {
          'ANY': true
        }
      },
      'opponent': {
        'button_sets': {
          'ANY': true,
          'limit_opponent_button_sets_50_states': false
        },
        'tourn_legal': {
          'ANY': true
        },
        'die_skills': {
          'ANY': true
        }
      }
    },
    'buttonRecipe': {
      'Avis': 'Avis: (4) (4) (10) (12) (X)',
      'Adam Spam': '-- Adam Spam: F(4) F(6) (6) (12) (X)',
      'Jellybean': 'Jellybean: p(20) s(20) (V) (X)',
    },
    'buttonSets': {
      'Soldiers': 'Soldiers',
      'Brom': 'Brom',
    },
    'tournLegal': {
      'yes': 'yes',
      'no': 'no',
    },
    'dieSkills': {
      'Fire': 'Fire',
      'Poison': 'Poison',
      'Shadow': 'Shadow',
    },
    'playerButton': null,
    'opponentButton': null,
  };
  
  ButtonSelection.getButtonSelectionData(function() {

    var table = ButtonSelection.getSingleButtonOptionsTable('player');
    assert.ok(table.is('table'), 'Function should return a table');

    assert.equal(
      table.prop('rows').length, 5, 
      'Default button options table should have three restriction rows, ' +
      'one button choice row, and one custom recipe row'
    );

    var tableBody = $(table.children()[0]);
    
    var tableRow1 = $(tableBody.children()[0]);
    var tableRow2 = $(tableBody.children()[1]);
    var tableRow3 = $(tableBody.children()[2]);
    var tableRow4 = $(tableBody.children()[3]);
    var tableRow5 = $(tableBody.children()[4]);
    
    assert.equal(tableRow1.children().length, 1, 'First row should have one column');
    assert.equal(tableRow2.children().length, 1, 'Second row should have one column');
    assert.equal(tableRow3.children().length, 1, 'Third row should have one column');
    assert.equal(tableRow4.children().length, 1, 'Fourth row should have one column');
    assert.equal(tableRow5.children().length, 1, 'Fifth row should have one column');
    
    var cell1 = $(tableRow1.children()[0]);
    var cell2 = $(tableRow2.children()[0]);
    var cell3 = $(tableRow3.children()[0]);
    var cell4 = $(tableRow4.children()[0]);
    var cell5 = $(tableRow5.children()[0]);
    
    assert.ok($(cell1.children()[0]).is('table'), 'First cell should contain a table');
    assert.ok($(cell2.children()[0]).is('table'), 'Second cell should contain a table');
    assert.ok($(cell3.children()[0]).is('table'), 'Third cell should contain a table');
    assert.ok($(cell4.children()[0]).is('select'), 'Fourth cell should contain a select');
    assert.ok($(cell5.children()[0]).is('span'), 'Fifth cell should contain a span');
    assert.ok($(cell5.children()[1]).is('input'), 'Fifth cell should contain an input');

    start();
  });
});