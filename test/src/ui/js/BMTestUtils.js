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
         (elemClass == "failed"))) {
      continue;
    }

    elementInfo.push(
      "node=" + elemNode + ", id=" + elemId + ", class=" + elemClass
    );
  }

  // Populate javascript variable info
  var jsInfo = {
    'Api':      JSON.stringify(Api, null, "  "),
    'Env':      JSON.stringify(Env, null, "  "),
    'Game':     JSON.stringify(Game, null, "  "),
    'Login':    JSON.stringify(Login, null, "  "),
    'Newgame':  JSON.stringify(Newgame, null, "  "),
    'Newuser':  JSON.stringify(Newuser, null, "  "),
    'Overview': JSON.stringify(Overview, null, "  "),
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
