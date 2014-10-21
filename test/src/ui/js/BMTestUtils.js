// Test utilities belong to the BMTestUtils module
var BMTestUtils = {};

// Utility to get all elements in the document DOM and all javascript
// variables used by buttonmen code
// This is used to detect whether modules are erroneously modifying
// the DOM or other modules, and to make sure we're correctly
// cleaning up everything between tests.
BMTestUtils.getAllElements = function() {

  // Populate DOM element info
  var elementInfo = [];
  var allElements = document.getElementsByTagName("*");
  for (var i=0, max=allElements.length; i < max; i++) {
    var elemNode = allElements[i].nodeName;
    var elemId = allElements[i].id;
    var elemClass = allElements[i].className;

    // Skip module-name and test-name SPAN elements created by QUnit itself
    if ((elemNode == "SPAN") && (elemId == "") &&
        ((elemClass == "module-name") || (elemClass == "test-name") ||
         (elemClass == "passed") || (elemClass == "total") ||
         (elemClass == "failed") || (elemClass == "test-message"))) {
      continue;
    }
    if ((elemNode == "LI") && (elemId == "") &&
        ((elemClass == "pass") || (elemClass == "fail"))) {
      continue;
    }

    elementInfo.push(
      "node=" + elemNode + ", id=" + elemId + ", class=" + elemClass
    );
  }

  // Populate javascript variable info
  var jsInfo = {
    'ActivePlayers':  JSON.stringify(ActivePlayers, null, "  "),
    'Api':            JSON.stringify(Api, null, "  "),
    'Buttons':        JSON.stringify(Buttons, null, "  "),
    'Config':         JSON.stringify(Config, null, "  "),
    'Env':            JSON.stringify(Env, null, "  "),
    'Forum':          JSON.stringify(Forum, null, "  "),
    'Game':           JSON.stringify(Game, null, "  "),
    'History':        JSON.stringify(History, null, "  "),
    'Loader':         JSON.stringify(Loader, null, "  "),
    'Login':          JSON.stringify(Login, null, "  "),
    'Newgame':        JSON.stringify(Newgame, null, "  "),
    'Newuser':        JSON.stringify(Newuser, null, "  "),
    'OpenGames':      JSON.stringify(OpenGames, null, "  "),
    'Overview':       JSON.stringify(Overview, null, "  "),
    'Profile':        JSON.stringify(Profile, null, "  "),
    'UserPrefs':      JSON.stringify(UserPrefs, null, "  "),
    'Verify':         JSON.stringify(Verify, null, "  "),
  };

  return {
    'DOM': elementInfo,
    'JS': jsInfo
  };
}

// Other modules may set Env.message, so have a central test utility
// to clean it up
BMTestUtils.deleteEnvMessage = function() {
  delete Env.message;
  $('#env_message').remove();
  $('#env_message').empty();
}

// Fake player login information for other functions to use
BMTestUtils.setupFakeLogin = function() {
  BMTestUtils.OverviewOldLoginPlayer = Login.player;
  BMTestUtils.OverviewOldLoginLoggedin = Login.logged_in;
  Login.player = 'tester1';
  Login.logged_in = true;
}

BMTestUtils.cleanupFakeLogin = function() {
  Login.player = BMTestUtils.OverviewOldLoginPlayer;
  Login.logged_in = BMTestUtils.OverviewOldLoginLoggedin;
}

// For each game/move reported by responderTest which we use in UI
// tests, set a friendly name for tracking purposes.  These values
// need to be kept in sync with responderTest in order for anything
// good to happen.
BMTestUtils.testGameId = function(gameDesc) {
  if (gameDesc == 'newgame') { return '1'; }
  if (gameDesc == 'swingset') { return '2'; }
  if (gameDesc == 'turn_active') { return '3'; }
  if (gameDesc == 'turn_inactive') { return '4'; }
  if (gameDesc == 'finished') { return '5'; }
  if (gameDesc == 'newgame_twin') { return '6'; }
  if (gameDesc == 'focus') { return '7'; }
  if (gameDesc == 'chance_active') { return '8'; }
  if (gameDesc == 'chance_inactive') { return '9'; }
  if (gameDesc == 'newgame_nonplayer') { return '10'; }
  if (gameDesc == 'turn_nonplayer') { return '11'; }
  if (gameDesc == 'chance_nonplayer') { return '12'; }
  if (gameDesc == 'chooseaux_active') { return '13'; }
  if (gameDesc == 'chooseaux_inactive') { return '14'; }
  if (gameDesc == 'chooseaux_nonplayer') { return '15'; }
  if (gameDesc == 'reserve_active') { return '16'; }
  if (gameDesc == 'reserve_inactive') { return '17'; }
  if (gameDesc == 'reserve_nonplayer') { return '18'; }
  if (gameDesc == 'option_active') { return '19'; }
  if (gameDesc == 'fire_active') { return '22'; }
  if (gameDesc == 'fire_inactive') { return '23'; }
  if (gameDesc == 'fire_nonplayer') { return '24'; }

  if (gameDesc == 'frasquito_wiseman_newgame') { return '101'; }
  if (gameDesc == 'frasquito_wiseman_newgame_observer') { return '102'; }
}

// We don't currently usually test reading the URL bar contents, because
// that's hard to do within QUnit, but rather override those contents
// with hardcoded values that we want to test.
BMTestUtils.overrideGetParameterByName = function() {
  BMTestUtils.realGetParameterByName = Env.getParameterByName;

  Env.getParameterByName = function(name) {
    if (name == 'game') {
      return BMTestUtils.testGameId(BMTestUtils.GameType);
    }

    // always return the userid associated with tester1 in the fake data
    if (name == 'id') {
      return '1';
    }

    // Syntactically valid but fake verification key
    if (name == 'key') {
      return 'facadefacadefacadefacadefacade12';
    }
  }
}

// We also need to restore the original version after testing, for the
// benefit of any tests that expect non-dummy data.
BMTestUtils.restoreGetParameterByName = function() {
  if (BMTestUtils.realGetParameterByName !== undefined) {
    Env.getParameterByName = BMTestUtils.realGetParameterByName;
    delete BMTestUtils.realGetParameterByName;
  }
}

// Copies all top-level function-type properties from one object to another,
// so that you can (e.g.) back up an object, replace some of its functions
// with mocked ones for testing, then restore it afterward.
BMTestUtils.CopyAllMethods = function(objA, objB) {
  $.each(objA, function(key, value) {
    if ($.isFunction(value)) {
      objB[key] = value;
    }
  });
};
