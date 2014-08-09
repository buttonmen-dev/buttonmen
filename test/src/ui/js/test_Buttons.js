module("Buttons", {
  'setup': function() {
    BMTestUtils.ButtonsPre = BMTestUtils.getAllElements();

    // Back up any methods that we might decide to replace with mocks
    BMTestUtils.ApiBackup = { };
    BMTestUtils.CopyAllMethods(Api, BMTestUtils.ApiBackup);

    BMTestUtils.setupFakeLogin();

    // Create the buttons_page div so functions have something to modify
    if (document.getElementById('buttons_page') == null) {
      $('body').append($('<div>', {'id': 'buttons_page', }));
    }
  },
  'teardown': function() {

    // Delete all elements we expect this module to create

    // JavaScript variables
    delete Api.button;
    delete Api.buttonSet;
    delete Env.window.location.search;
    delete Buttons.buttonName;
    delete Buttons.setName;
    delete Buttons.page;

    // Page elements
    $('#buttons_page').remove();
    $('#buttons_page').empty();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Restore any methods that we might have replaced with mocks
    BMTestUtils.CopyAllMethods(BMTestUtils.ApiBackup, Api);

    // Fail if any other elements were added or removed
    BMTestUtils.ButtonsPost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.ButtonsPost, BMTestUtils.ButtonsPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Buttons module has been loaded
test("test_Buttons_is_loaded", function() {
  ok(Buttons, "The Buttons namespace exists");
});

test("test_Buttons.showButtonsPage", function() {
  expect(2); // Tests plus teardown test

  Api.getButtonSetData = function(passedSetName) {
    equal(passedSetName, null,
      'No set name should get passed to Api.getButtonSetData');
  }

  Api.getButtonData = function() {
    ok(false, 'Api.getButtonData should not be invoked');
  }

  Buttons.showButtonsPage();
});

test("test_Buttons.showButtonsPage_set", function() {
  expect(2); // Tests plus teardown test

  var expectedSetName = 'Soldiers';
  Env.window.location.search = '?set=' + expectedSetName;

  Api.getButtonSetData = function(passedSetName) {
    equal(passedSetName, expectedSetName,
      'Set name should get passed to Api.getButtonSetData');
  }

  Api.getButtonData = function() {
    ok(false, 'Api.getButtonData should not be invoked');
  }

  Buttons.showButtonsPage();
});

test("test_Buttons.showButtonsPage_button", function() {
  expect(2); // Tests plus teardown test

  var expectedButtonName = 'Avis';
  Env.window.location.search = '?button=' + expectedButtonName;

  Api.getButtonData = function(passedButtonName) {
    equal(passedButtonName, expectedButtonName,
      'Button name should get passed to Api.getButtonData');
  }

  Api.getButtonSetData = function() {
    ok(false, 'Api.getButtonSetData should not be invoked');
  }

  Buttons.showButtonsPage();
});

test("test_Buttons.showButton", function() {
  Buttons.buttonName = 'Avis';

  Api.button = {
    'load_status': 'ok',
    'list': {
      'Avis': {
        'buttonName': 'Avis',
        'hasUnimplementedSkill': false,
        'recipe': '(4) (4) (10) (12) (X)',
        'buttonSet': 'Soldiers',
        'dieTypes': {
          'X Swing': {
            'code': 'X',
            'swingMin': 4,
            'swingMax': 20,
            'description':
                'X Swing Dice can be any die between 4 and 20. Swing Dice ' +
                'are allowed to be any integral size between their upper and ' +
                'lower limit, including both ends, and including nonstandard ' +
                'die sizes like 17 or 9. Each player chooses his or her ' +
                'Swing Die in secret at the beginning of the match, and ' +
                'thereafter the loser of each round may change their Swing ' +
                'Die between rounds. If a character has any two Swing Dice ' +
                'of the same letter, they must always be the same size.',
          },
        },
        'dieSkills': [ ],
        'isTournamentLegal': true,
        'artFilename': 'avis.png',
        'flavorText': null,
        'specialText': null,
      },
    },
  };

  Buttons.showButton();

  var buttonName = $('div.singleButton div.buttonName');
  equal(buttonName.text(), 'Avis',
    'Button name should be displayed in single button mode');
});

test("test_Buttons.showSet", function() {
  Buttons.setName = 'Soldiers';

  Api.buttonSet = {
    'load_status': 'ok',
    'list': {
      'Soldiers': {
        'setName': 'Soldiers',
        'buttons': {
          'Avis': {
            'buttonName': 'Avis',
            'hasUnimplementedSkill': false,
            'recipe': '(4) (4) (10) (12) (X)',
            'buttonSet': 'Soldiers',
            'dieSkills': [ ],
            'isTournamentLegal': true,
            'artFilename': 'avis.png',
          },
          'Kublai': {
            'buttonName': 'Kublai',
            'hasUnimplementedSkill': false,
            'recipe': '(4) (8) (12) (20) (X)',
            'buttonSet': 'Soldiers',
            'dieSkills': [ ],
            'isTournamentLegal': true,
            'artFilename': 'kublai.png',
          },
        },
      },
    },
  };

  Buttons.showSet();

  var setName = $('div.singleSet > h2');
  var buttonNames = $('div.singleSet a.buttonName');
  equal(setName.text(), 'Soldiers',
    'Set name should be displayed in single set mode');
  equal(buttonNames.length, 2,
    'All buttons in set should be displayed in single set mode');
});

test("test_Buttons.showSetList", function() {
  Api.buttonSet = {
    'load_status': 'ok',
    'list': {
      'Soldiers': {
        'setName': 'Soldiers',
      },
      'Fantasy': {
        'setName': 'Fantasy',
      },
      'Samurai': {
        'setName': 'Samurai',
      },
    },
  };

  Buttons.showSetList();

  var setNames = $('div.allSets a.buttonSetLink');
  equal(setNames.length, 3, 'All sets should be displayed in all sets mode');
});

test("test_Buttons.buildButtonBox", function() {
  Buttons.buttonName = 'Avis';
  var button = {
    'buttonName': 'Avis',
    'hasUnimplementedSkill': false,
    'recipe': '(4) (4) (10) (12) (X)',
    'buttonSet': 'Soldiers',
    'dieSkills': [ ],
    'isTournamentLegal': true,
    'artFilename': 'avis.png',
  };

  var buttonBox = Buttons.buildButtonBox(button);

  var img = buttonBox.find('img');
  ok(img.attr('src').match(/avis\.png/),
    'Button box should contain the correct image');
  var name = buttonBox.find('.buttonName');
  equal(name.text(), 'Avis',
    'Button box should contain the correct name');
});

test("test_Buttons.arrangePage", function() {
    Buttons.page = $('<div>');
    Buttons.page.append($('<p>', {'text': 'hi world', }));
    Buttons.arrangePage();
    var pageElement = $('body #buttons_page p');
    equal(pageElement.text(), 'hi world',
          "Page elements should exist in DOM after page is arranged");
});
