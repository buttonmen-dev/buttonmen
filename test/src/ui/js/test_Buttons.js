module("Buttons", {
  'setup': function() {
    BMTestUtils.ButtonsPre = BMTestUtils.getAllElements();

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

    // Page elements
    $('#buttons_page').remove();
    $('#buttons_page').empty();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

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

asyncTest("test_Buttons.showButtonsPage", function() {
  // Remove #buttons_page so that showButtonsPage will have something to add
  $('#buttons_page').remove();
  Buttons.showButtonsPage();
  var item = document.getElementById('buttons_page');
  equal(item.nodeName, "DIV",
        "#buttons_page is a div after showButtonsPage() is called");
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
                'X Swing Dice can be any die between 4 and 20. Swing Dice ' .
                'are allowed to be any integral size between their upper and ' .
                'lower limit, including both ends, and including nonstandard ' .
                'die sizes like 17 or 9. Each player chooses his or her ' .
                'Swing Die in secret at the beginning of the match, and ' .
                'thereafter the loser of each round may change their Swing ' .
                'Die between rounds. If a character has any two Swing Dice ' .
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
  equal(buttonName.val(), 'Avis',
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
  equal(setName.val(), 'Soldiers',
    'Set name should be displayed in single set mode');
  equal(buttonNames.length, 2,
    'All buttons in set should be displayed in single set mode');
});

test("test_Buttons.showSetList", function() {
  Api.button = {
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

//TODO test the box builder thing

asyncTest("test_Buttons.arrangePage", function() {
    $('body').append($('<div>', { 'id': 'buttons_page', }));
    Buttons.page = $('<div>');
    Buttons.page.append($('<p>', {'text': 'hi world', }));
    Buttons.arrangePage();
    var pageElement = $('body #buttons_page p');
    equal(item.nodeName, pageElement.length,
          "Page elements should exist in DOM after page is arranged");
});
