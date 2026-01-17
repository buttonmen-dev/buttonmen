module("TournamentOverview", {
  'setup': function() {
    BMTestUtils.TournamentOverviewPre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the tournament_page div so functions have something to modify
    if (document.getElementById('tournament_overview_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'tournament_overview_page', }));
    }
//
//    // set colors for use in game, since tests don't always traverse showStatePage()
//    Tournament.color = {
//      'player': '#dd99dd',
//      'opponent': '#ddffdd',
//    };

    Login.pageModule = { 'bodyDivId': 'tournament_overview_page' };
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JavaScript variables
    delete Api.new_tournaments;
//    delete TournamentOverview.tournament;
    delete TournamentOverview.page;
    delete TournamentOverview.form;

    Login.pageModule = null;
    TournamentOverview.activity = {};

    // Page elements
    $('#tournament_overview_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.TournamentOverviewPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.TournamentOverviewPost, BMTestUtils.TournamentOverviewPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the TournamentOverview module has been loaded
test("test_TournamentOverview_is_loaded", function(assert) {
  assert.ok(TournamentOverview, "The TournamentOverview namespace exists");
});

// The purpose of this test is to demonstrate that the flow of
// TournamentOverview.showLoggedInPage() is correct for a showXPage function, namely
// that it calls an API getter with a showStatePage function as a
// callback.
//
// Accomplish this by mocking the invoked functions
test("test_TournamentOverview.showLoggedInPage", function(assert) {
  expect(5);
  var cached_getOverview = TournamentOverview.getOverview;
  var cached_showStatePage = TournamentOverview.showStatePage;
  var getOverviewCalled = false;
  TournamentOverview.showPage = function() {
    assert.ok(
      getOverviewCalled,
      "TournamentOverview.getOverview is called before TournamentOverview.showStatePage"
    );
  };
  TournamentOverview.getOverview = function(callback) {
    getOverviewCalled = true;
    assert.equal(callback, TournamentOverview.showPage,
      "TournamentOverview.getOverview is called with TournamentOverview.showPage as an argument");
    callback();
  };

  TournamentOverview.showLoggedInPage();
  var item = document.getElementById('tournament_overview_page');
  console.log(document);
  assert.equal(item.nodeName, "DIV",
        "#tournament_overview_page is a div after showLoggedInPage() is called");

  TournamentOverview.getOverview = cached_getOverview;
  TournamentOverview.showPage = cached_showStatePage;
});

//// Use stop()/start() because the AJAX-using operation needs to
//// finish before its results can be tested
//test("test_Tournament.redrawTournamentPageSuccess", function(assert) {
////  $.ajaxSetup({ async: false });
////  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
////  Tournament.redrawTournamentPageSuccess();
////  var item = document.getElementById('tournament_page');
////  assert.equal(item.nodeName, "DIV",
////        "#tournament_page is a div after redrawTournamentPageSuccess() is called");
////  assert.deepEqual(Tournament.activity, {},
////        "Tournament.activity is cleared by redrawTournamentPageSuccess()");
////  $.ajaxSetup({ async: true });
//});
//
//// Use stop()/start() because the AJAX-using operation needs to
//// finish before its results can be tested
//test("test_Tournament.redrawTournamentPageFailure", function(assert) {
////  $.ajaxSetup({ async: false });
////  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
////  Tournament.activity.chat = "Some chat text";
////  Tournament.redrawGamePageFailure();
////  var item = document.getElementById('tournament_page');
////  assert.equal(item.nodeName, "DIV",
////        "#tournament_page is a div after redrawGamePageFailure() is called");
////  assert.equal(Tournament.activity.chat, "Some chat text",
////        "Tournament.activity.chat is retained by redrawTournamentPageSuccess()");
////  $.ajaxSetup({ async: true });
//});
//
//// N.B. Almost all of these tests should use stop(), set a test
//// game type, and invoke Tournament.getCurrentTournament(), because that's the
//// way to get the dummy responder data which all the other functions
//// need.  Then run tests against the function itself, and end with
//// start().  So the typical format will be:
////
//// test("test_Tournament.someFunction", function(assert) {
////   stop();
////   BMTestUtils.GameType = '<sometype>';
////   Tournament.getCurrentTournament(function() {
////     <setup any additional prereqs for someFunction>
////     Tournament.someFunction();
////     <run tests against state changes made by someFunction>
////     start();
////   });
//// });
//
//test("test_Tournament.getCurrentTournament", function(assert) {
////  stop();
////  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
////  var gameId = BMTestUtils.testGameId(BMTestUtils.GameType);
////  Tournament.getCurrentTournament(function() {
////    assert.equal(Tournament.tournament, gameId, "Set expected game number");
////    assert.equal(Api.tournament.load_status, 'ok', 'Successfully loaded game data');
////    assert.equal(Api.tournament.gameId, Tournament.tournament, 'Parsed correct game number from API');
////    start();
////  });
//});
//
//test("test_Tournament.showStatePage", function(assert) {
////  stop();
////  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
////  Tournament.getCurrentTournament(function() {
////    Tournament.showStatePage();
////    var htmlout = Tournament.page.html();
////    assert.ok(htmlout.length > 0,
////      "The created page should have nonzero contents");
////    assert.ok(htmlout.match('vacation16.png'),
////      "The game UI contains a vacation icon when the API data reports that one player is on vacation");
////    start();
////  });
//});

test("test_TournamentOverview.getOverview", function(assert) {

});

test("test_TournamentOverview.showPage", function(assert) {

});

test("test_TournamentOverview.pageAddNewtournamentLink", function(assert) {

});

test("test_TournamentOverview.pageAddTournamentTables", function(assert) {

});

test("test_TournamentOverview.pageAddTournamentTable", function(assert) {

});

test("test_TournamentOverview.addTableStructure", function(assert) {

});

test("test_TournamentOverview.addTableRows", function(assert) {

});

test("test_TournamentOverview.addTournamentCol", function(assert) {

});

test("test_TournamentOverview.addTypeCol", function(assert) {

});

test("test_TournamentOverview.addDescCol", function(assert) {

});

test("test_TournamentOverview.addPlayerCol", function(assert) {

});

test("test_TournamentOverview.addNPlayersCol", function(assert) {

});

test("test_TournamentOverview.addDismissCol", function(assert) {

});

test("test_TournamentOverview.addFollowCol", function(assert) {

});

test("test_TournamentOverview.formFollowTournament", function(assert) {

});

test("test_TournamentOverview.formUnfollowTournament", function(assert) {

});

test("test_TournamentOverview.addUnfollowCol", function(assert) {

});

test("test_TournamentOverview.linkTextStub", function(assert) {

});

test("test_TournamentOverview.formDismissTournament", function(assert) {

});

test("test_TournamentOverview.updateSectionHeaderCounts", function(assert) {

});

test("test_TournamentOverview.updateVisibilityOfTables", function(assert) {

});
