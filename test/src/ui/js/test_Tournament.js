module("Tournament", {
  'setup': function() {
    BMTestUtils.TournamentPre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Override Env.getParameterByName to set the game
    BMTestUtils.overrideGetParameterByName();

    // Create the tournament_page div so functions have something to modify
    if (document.getElementById('tournament_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'tournament_page', }));
    }
//
//    // set colors for use in game, since tests don't always traverse showStatePage()
//    Tournament.color = {
//      'player': '#dd99dd',
//      'opponent': '#ddffdd',
//    };

    Login.pageModule = { 'bodyDivId': 'tournament_page' };
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
    delete Api.tournament;
    delete Tournament.tournament;
    delete Tournament.page;
    delete Tournament.form;

    Login.pageModule = null;
    Tournament.activity = {};

    // Page elements
    $('#tournament_page').remove();

    BMTestUtils.restoreGetParameterByName();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();
    BMTestUtils.restoreGetParameterByName();

    // Fail if any other elements were added or removed
    BMTestUtils.TournamentPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.TournamentPost, BMTestUtils.TournamentPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Tournament module has been loaded
test("test_Tournament_is_loaded", function(assert) {
  assert.ok(Tournament, "The Tournament namespace exists");
});

// The purpose of this test is to demonstrate that the flow of
// Tournament.showLoggedInPage() is correct for a showXPage function, namely
// that it calls an API getter with a showStatePage function as a
// callback.
//
// Accomplish this by mocking the invoked functions
test("test_Tournament.showLoggedInPage", function(assert) {
  expect(5);
  var cached_getCurrentTournament = Tournament.getCurrentTournament;
  var cached_showStatePage = Tournament.showStatePage;
  var getCurrentTournamentCalled = false;
  Tournament.showStatePage = function() {
    assert.ok(getCurrentTournamentCalled, "Tournament.getCurrentTournament is called before Tournament.showStatePage");
  };
  Tournament.getCurrentTournament = function(callback) {
    getCurrentTournamentCalled = true;
    assert.equal(callback, Tournament.showStatePage,
      "Tournament.getCurrentTournament is called with Tournament.showStatePage as an argument");
    callback();
  };

  Tournament.showLoggedInPage();
  var item = document.getElementById('tournament_page');
  assert.equal(item.nodeName, "DIV",
        "#tournament_page is a div after showLoggedInPage() is called");
  Tournament.getCurrentTournament = cached_getCurrentTournament;
  Tournament.showStatePage = cached_showStatePage;
});

// Use stop()/start() because the AJAX-using operation needs to
// finish before its results can be tested
test("test_Tournament.redrawTournamentPageSuccess", function(assert) {
//  $.ajaxSetup({ async: false });
//  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
//  Tournament.redrawTournamentPageSuccess();
//  var item = document.getElementById('tournament_page');
//  assert.equal(item.nodeName, "DIV",
//        "#tournament_page is a div after redrawTournamentPageSuccess() is called");
//  assert.deepEqual(Tournament.activity, {},
//        "Tournament.activity is cleared by redrawTournamentPageSuccess()");
//  $.ajaxSetup({ async: true });
});

// Use stop()/start() because the AJAX-using operation needs to
// finish before its results can be tested
test("test_Tournament.redrawTournamentPageFailure", function(assert) {
//  $.ajaxSetup({ async: false });
//  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
//  Tournament.activity.chat = "Some chat text";
//  Tournament.redrawGamePageFailure();
//  var item = document.getElementById('tournament_page');
//  assert.equal(item.nodeName, "DIV",
//        "#tournament_page is a div after redrawGamePageFailure() is called");
//  assert.equal(Tournament.activity.chat, "Some chat text",
//        "Tournament.activity.chat is retained by redrawTournamentPageSuccess()");
//  $.ajaxSetup({ async: true });
});

// N.B. Almost all of these tests should use stop(), set a test
// game type, and invoke Tournament.getCurrentTournament(), because that's the
// way to get the dummy responder data which all the other functions
// need.  Then run tests against the function itself, and end with
// start().  So the typical format will be:
//
// test("test_Tournament.someFunction", function(assert) {
//   stop();
//   BMTestUtils.GameType = '<sometype>';
//   Tournament.getCurrentTournament(function() {
//     <setup any additional prereqs for someFunction>
//     Tournament.someFunction();
//     <run tests against state changes made by someFunction>
//     start();
//   });
// });

test("test_Tournament.getCurrentTournament", function(assert) {
//  stop();
//  BMTestUtils.GameType = 'frasquito_wiseman_specifydice';
//  var gameId = BMTestUtils.testGameId(BMTestUtils.GameType);
//  Tournament.getCurrentTournament(function() {
//    assert.equal(Tournament.tournament, gameId, "Set expected game number");
//    assert.equal(Api.tournament.load_status, 'ok', 'Successfully loaded game data');
//    assert.equal(Api.tournament.gameId, Tournament.tournament, 'Parsed correct game number from API');
//    start();
//  });
});

test("test_Tournament.showStatePage", function(assert) {
  stop();
  BMTestUtils.TournamentType = 'default';
  Tournament.getCurrentTournament(function() {
    Tournament.showStatePage();
    var htmlout = Tournament.page.html();
    assert.ok(htmlout.length > 0,
          "The created page should have nonzero contents");
    start();
  });
});

test("test_Tournament.showTournamentContents", function(assert) {

});

test("test_Tournament.pageAddTournamentHeader", function(assert) {
  stop();
  BMTestUtils.TournamentType = 'default';
  Tournament.getCurrentTournament(function() {
    Api.tournament.description = 'header';
    Tournament.showStatePage();
    Tournament.pageAddTournamentHeader();
    var htmlout = Tournament.page.html();
    assert.ok(htmlout.length > 0,
          "The created page should have nonzero contents");
    // now test the header contents
    //console.log(Api.tournament);
    //console.log(htmlout);


    var item = document.getElementById('tournament_desc');
    assert.equal(item.nodeName, "DIV",
          "#tournament_desc is a div after redrawTournamentPageSuccess() is called");
    assert.equal($(item).html(), 'header', 'Header text should be correct');

    start();
  });
});

test("test_Tournament.pageAddDismissTournamentLink", function(assert) {

});

test("test_Tournament.formDismissTournament", function(assert) {

});

test("test_Tournament.pageAddUnfollowTournamentLink", function(assert) {

});

test("test_Tournament.formUnfollowTournament", function(assert) {

});

test("test_Tournament.pageAddFollowTournamentLink", function(assert) {

});

test("test_Tournament.formFollowTournament", function(assert) {

});

test("test_Tournament.pageAddTournamentInfo", function(assert) {

});

test("test_Tournament.friendlyTournamentType", function(assert) {

});

test("test_Tournament.pageAddActions", function(assert) {

});

test("test_Tournament.formCancelTournament", function(assert) {

});

test("test_Tournament.formJoinTournament", function(assert) {

});

test("test_Tournament.formLeaveTournament", function(assert) {

});

test("test_Tournament.pageAddPlayerInfo", function(assert) {

});
