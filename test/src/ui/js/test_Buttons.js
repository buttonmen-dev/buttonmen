module("Buttons", {
  'setup': function(assert) {
    BMTestUtils.ButtonsPre = BMTestUtils.getAllElements();

    // Back up any methods that we might decide to replace with mocks
    BMTestUtils.ApiBackup = { };
    BMTestUtils.CopyAllMethods(Api, BMTestUtils.ApiBackup);

    BMTestUtils.setupFakeLogin();

    // Create the buttons_page div so functions have something to modify
    if (document.getElementById('buttons_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'buttons_page', }));
    }

    Login.pageModule = { 'bodyDivId': 'buttons_page' };
  },
  'teardown': function(assert) {
    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JavaScript variables
    delete Api.button;
    delete Api.buttonSet;
    delete Env.window.location.search;
    delete Buttons.buttonName;
    delete Buttons.setName;
    delete Buttons.page;

    Login.pageModule = null;

    // Page elements
    $('#buttons_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Restore any methods that we might have replaced with mocks
    BMTestUtils.CopyAllMethods(BMTestUtils.ApiBackup, Api);

    // Fail if any other elements were added or removed
    BMTestUtils.ButtonsPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.ButtonsPost, BMTestUtils.ButtonsPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Buttons module has been loaded
test("test_Buttons_is_loaded", function(assert) {
  assert.ok(Buttons, "The Buttons namespace exists");
});

test("test_Buttons.showLoggedInPage", function(assert) {
  expect(3); // Tests plus teardown 2 tests

  Api.getButtonSetData = function(passedSetName) {
    assert.equal(passedSetName, null,
      'No set name should get passed to Api.getButtonSetData');
  };

  Api.getButtonData = function() {
    assert.ok(false, 'Api.getButtonData should not be invoked');
  };

  Buttons.showLoggedInPage();
});

test("test_Buttons.showLoggedInPage_set", function(assert) {
  expect(3); // Tests plus 2 teardown tests

  var expectedSetName = 'Soldiers';
  Env.window.location.search = '?set=' + expectedSetName;

  Api.getButtonSetData = function(passedSetName) {
    assert.equal(passedSetName, expectedSetName,
      'Set name should get passed to Api.getButtonSetData');
  };

  Api.getButtonData = function() {
    assert.ok(false, 'Api.getButtonData should not be invoked');
  };

  Buttons.showLoggedInPage();
});

test("test_Buttons.showLoggedInPage_button", function(assert) {
  expect(3); // Tests plus 2 teardown tests

  var expectedButtonName = 'Avis';
  Env.window.location.search = '?button=' + expectedButtonName;

  Api.getButtonData = function(passedButtonName) {
    assert.equal(passedButtonName, expectedButtonName,
      'Button name should get passed to Api.getButtonData');
  };

  Api.getButtonSetData = function() {
    assert.ok(false, 'Api.getButtonSetData should not be invoked');
  };

  Buttons.showLoggedInPage();
});

test("test_Buttons.showButton", function(assert) {
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
        'tags': [ ],
        'flavorText': null,
        'specialText': null,
      },
    },
  };

  Buttons.showButton();

  var buttonName = $('div.singleButton div.buttonName');
  assert.equal(buttonName.text(), 'Avis',
    'Button name should be displayed in single button mode');
});

test("test_Buttons.showSet", function(assert) {
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
            'tags': [ ],
          },
          'Kublai': {
            'buttonName': 'Kublai',
            'hasUnimplementedSkill': false,
            'recipe': '(4) (8) (12) (20) (X)',
            'buttonSet': 'Soldiers',
            'dieSkills': [ ],
            'isTournamentLegal': true,
            'artFilename': 'kublai.png',
            'tags': [ ],
          },
        },
      },
    },
  };

  Buttons.showSet();

  var setName = $('div.singleSet > h2');
  var buttonNames = $('div.singleSet a.buttonName');
  assert.equal(setName.text(), 'Soldiers',
    'Set name should be displayed in single set mode');
  assert.equal(buttonNames.length, 2,
    'All buttons in set should be displayed in single set mode');
});

test("test_Buttons.showSetList", function(assert) {
  Api.buttonSet = {
    'load_status': 'ok',
    'list': {
      'Soldiers': {
        'setName': 'Soldiers',
        'numberOfButtons': '13',
        'dieSkills': [ ],
        'dieTypes': [ 'X Swing' ],
        'onlyHasUnimplementedButtons': false,
      },
      'Fantasy': {
        'setName': 'Fantasy',
        'numberOfButtons': '14',
        'dieSkills': [ ],
        'dieTypes': [ 'Option' ],
        'onlyHasUnimplementedButtons': false,
      },
      'Samurai': {
        'setName': 'Samurai',
        'numberOfButtons': '7',
        'dieSkills': [ 'Focus' ],
        'dieTypes': [ 'V Swing', 'X Swing' ],
        'onlyHasUnimplementedButtons': false,
      },
    },
  };

  Buttons.showSetList();

  var setNames = $('div.allSets a.buttonSetLink');
  assert.equal(setNames.length, 3, 'All sets should be displayed in all sets mode');
});

test("test_Buttons.buildButtonBox", function(assert) {
  Buttons.buttonName = 'Avis';
  var button = {
    'buttonName': 'Avis',
    'hasUnimplementedSkill': false,
    'recipe': '(4) (4) (10) (12) (X)',
    'buttonSet': 'Soldiers',
    'dieSkills': [ ],
    'isTournamentLegal': true,
    'artFilename': 'avis.png',
    'tags': [ ],
  };

  var buttonBox = Buttons.buildButtonBox(button);

  var img = buttonBox.find('img');
  assert.ok(img.attr('src').match(/avis\.png/),
    'Button box should contain the correct image');
  var name = buttonBox.find('.buttonName');
  assert.equal(name.text(), 'Avis',
    'Button box should contain the correct name');
});
