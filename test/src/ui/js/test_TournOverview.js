module("TournOverview", {
  'setup': function() {
    BMTestUtils.TournOverviewPre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Override Env.getParameterByName to set the game
    BMTestUtils.overrideGetParameterByName();

    // Create the tourn_page div so functions have something to modify
    if (document.getElementById('tournoverview_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'tournoverview_page', }));
    }
//
//    // set colors for use in game, since tests don't always traverse showStatePage()
//    Tourn.color = {
//      'player': '#dd99dd',
//      'opponent': '#ddffdd',
//    };

    Login.pageModule = { 'bodyDivId': 'tournoverview_page' };
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // Revert cookies
    Env.setCookieNoImages(false);
    Env.setCookieCompactMode(false);

    // JavaScript variables
    delete Api.new_tourns;
//    delete TournOverview.tourn;
    delete TournOverview.page;
    delete TournOverview.form;

    Login.pageModule = null;
    TournOverview.activity = {};

    // Page elements
    $('#tournoverview_page').remove();

    BMTestUtils.restoreGetParameterByName();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();
    BMTestUtils.restoreGetParameterByName();

    // Fail if any other elements were added or removed
    BMTestUtils.TournOverviewPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.TournOverviewPost, BMTestUtils.TournOverviewPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the TournOverview module has been loaded
test("test_TournOverview_is_loaded", function(assert) {
  assert.ok(TournOverview, "The TournOverview namespace exists");
});

//// The purpose of this test is to demonstrate that the flow of
//// TournOverview.showLoggedInPage() is correct for a showXPage function, namely
//// that it calls an API getter with a showStatePage function as a
//// callback.
////
//// Accomplish this by mocking the invoked functions
test("test_TournOverview.showLoggedInPage", function(assert) {
////  expect(5);
////  var cached_getCurrentTourn = Tourn.getCurrentTourn;
////  var cached_showStatePage = Tourn.showStatePage;
////  var getCurrentTournCalled = false;
////  Tourn.showStatePage = function() {
////    assert.ok(getCurrentTournCalled, "Tourn.getCurrentTourn is called before Tourn.showStatePage");
////  };
////  Tourn.getCurrentTourn = function(callback) {
////    getCurrentTournCalled = true;
////    assert.equal(callback, Tourn.showStatePage,
////      "Tourn.getCurrentTourn is called with Tourn.showStatePage as an argument");
////    callback();
////  };
////
////  Tourn.showLoggedInPage();
////  var item = document.getElementById('tourn_page');
////  assert.equal(item.nodeName, "DIV",
////        "#tourn_page is a div after showLoggedInPage() is called");
////  Tourn.getCurrentTourn = cached_getCurrentTourn;
////  Tourn.showStatePage = cached_showStatePage;
});

//// Use stop()/start() because the AJAX-using operation needs to
//// finish before its results can be tested
//test("test_Tourn.redrawTournPageSuccess", function(assert) {
////  $.ajaxSetup({ async: false });
////  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
////  Tourn.redrawTournPageSuccess();
////  var item = document.getElementById('tourn_page');
////  assert.equal(item.nodeName, "DIV",
////        "#tourn_page is a div after redrawTournPageSuccess() is called");
////  assert.deepEqual(Tourn.activity, {},
////        "Tourn.activity is cleared by redrawTournPageSuccess()");
////  $.ajaxSetup({ async: true });
//});
//
//// Use stop()/start() because the AJAX-using operation needs to
//// finish before its results can be tested
//test("test_Tourn.redrawTournPageFailure", function(assert) {
////  $.ajaxSetup({ async: false });
////  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
////  Tourn.activity.chat = "Some chat text";
////  Tourn.redrawGamePageFailure();
////  var item = document.getElementById('tourn_page');
////  assert.equal(item.nodeName, "DIV",
////        "#tourn_page is a div after redrawGamePageFailure() is called");
////  assert.equal(Tourn.activity.chat, "Some chat text",
////        "Tourn.activity.chat is retained by redrawTournPageSuccess()");
////  $.ajaxSetup({ async: true });
//});
//
//// N.B. Almost all of these tests should use stop(), set a test
//// game type, and invoke Tourn.getCurrentTourn(), because that's the
//// way to get the dummy responder data which all the other functions
//// need.  Then run tests against the function itself, and end with
//// start().  So the typical format will be:
////
//// test("test_Tourn.someFunction", function(assert) {
////   stop();
////   BMTestUtils.GameType = '<sometype>';
////   Tourn.getCurrentTourn(function() {
////     <setup any additional prereqs for someFunction>
////     Tourn.someFunction();
////     <run tests against state changes made by someFunction>
////     start();
////   });
//// });
//
//test("test_Tourn.getCurrentTourn", function(assert) {
////  stop();
////  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
////  var gameId = BMTestUtils.testGameId(BMTestUtils.GameType);
////  Tourn.getCurrentTourn(function() {
////    assert.equal(Tourn.tourn, gameId, "Set expected game number");
////    assert.equal(Api.tourn.load_status, 'ok', 'Successfully loaded game data');
////    assert.equal(Api.tourn.gameId, Tourn.tourn, 'Parsed correct game number from API');
////    start();
////  });
//});
//
//test("test_Tourn.showStatePage", function(assert) {
////  stop();
////  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
////  Tourn.getCurrentTourn(function() {
////    Tourn.showStatePage();
////    var htmlout = Tourn.page.html();
////    assert.ok(htmlout.length > 0,
////      "The created page should have nonzero contents");
////    assert.ok(htmlout.match('vacation16.png'),
////      "The game UI contains a vacation icon when the API data reports that one player is on vacation");
////    start();
////  });
//});

test("test_TournOverview.getOverview", function(assert) {

});

test("test_TournOverview.showPage", function(assert) {

});

test("test_TournOverview.pageAddNewtournLink", function(assert) {

});

test("test_TournOverview.pageAddTournTables", function(assert) {

});

test("test_TournOverview.pageAddTournTable", function(assert) {

});

test("test_TournOverview.addTableStructure", function(assert) {

});

test("test_TournOverview.addTableRows", function(assert) {

});

test("test_TournOverview.addTournCol", function(assert) {

});

test("test_TournOverview.addTypeCol", function(assert) {

});

test("test_TournOverview.addDescCol", function(assert) {

});

test("test_TournOverview.addPlayerCol", function(assert) {

});

test("test_TournOverview.addNPlayersCol", function(assert) {

});

test("test_TournOverview.addDismissCol", function(assert) {

});

test("test_TournOverview.linkTextStub", function(assert) {

});

test("test_TournOverview.formDismissTourn", function(assert) {

});

test("test_TournOverview.updateSectionHeaderCounts", function(assert) {

});

test("test_TournOverview.updateVisibilityOfTables", function(assert) {

});
