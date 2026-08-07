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
    Api.tournament.description = 'description';
    Tournament.showStatePage();
    Tournament.pageAddTournamentHeader();
    var htmlout = Tournament.page.html();
    assert.ok(htmlout.length > 0,
          "The created page should have nonzero contents");

    var tournHeader = $('#tournament_id');
    var tournDesc = $('#tournament_desc');
    var tournInfo = $('#tournament_info');

    assert.ok(tournHeader.is('div'), 'Tournament header should be a div');
    assert.ok(tournDesc.is('div'),   'Tournament description should be a div');
    assert.ok(tournInfo.is('div'),   'Tournament info should be a div');

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

test("test_Tournament.pageAddTournamentDescription", function(assert) {
  stop();
  BMTestUtils.TournamentType = 'default';
  Tournament.getCurrentTournament(function() {
    Api.tournament.description =
      '[forum=1,6]text[/forum]456789012345678901234567890' +
      '[forum=1,6]text[/forum]456789012345678901234567890' +
      '[forum=1,6]text[/forum]456789012345678901234567890' +
      '[forum=1,6]text[/forum]456789012345678901234567890' +
      '[forum=1,6]text[/forum]4567890...';
    Tournament.showStatePage();
    Tournament.pageAddTournamentDescription();

    var tournDesc = $('#tournament_desc');

    var convertedDescription =
      '<a class=\"chatForumLink\" href=\"forum.html#!threadId=1&amp;postId=6\">text</a>' +
      '456789012345678901234567890' +
      '<a class=\"chatForumLink\" href=\"forum.html#!threadId=1&amp;postId=6\">text</a>' +
      '456789012345678901234567890' +
      '<a class=\"chatForumLink\" href=\"forum.html#!threadId=1&amp;postId=6\">text</a>' +
      '456789012345678901234567890' +
      '<a class=\"chatForumLink\" href=\"forum.html#!threadId=1&amp;postId=6\">text</a>' +
      '456789012345678901234567890' +
      '<a class=\"chatForumLink\" href=\"forum.html#!threadId=1&amp;postId=6\">text</a>' +
      '4567890...';

    assert.equal(
      tournDesc.html(),
      convertedDescription,
      'Description text should be correct'
    );

    start();
  });
});

test("test_Tournament.pageAddTournamentInfo", function(assert) {

});

test("test_Tournament.friendlyTournamentType", function(assert) {

});

test("test_Tournament.pageAddActions", function(assert) {

});

test("test_Tournament.formEditTournDesc", function(assert) {
  stop();
  BMTestUtils.TournamentType = 'default';
  Tournament.getCurrentTournament(function() {
    // test the page seen by the creator while people are still joining
    Api.tournament.description = 'Initial description';
    Api.tournament.isCreator = true;
    Api.tournament.tournamentState = Tournament.TOURN_STATE_JOIN_TOURNAMENT;
    Tournament.showStatePage();
    assert.ok($('#tournament_desc').is(':visible'), 'Tournament description should be visible');
    assert.equal($('#tournament_desc').text(), 'Initial description', 'Initial tournament description should be correct');
    assert.ok(!($('#tournament_desc_input').is(':visible')), 'Input box should not be visible');
    assert.ok($('#editLink').is(':visible'), 'Edit link should be visible');
    assert.ok(!($('#submitLink').is(':visible')), 'Submit link should not be visible');

    Tournament.formEditTournDesc();
    assert.ok(!($('#tournament_desc').is(':visible')), 'Tournament description should not be visible');
    assert.ok($('#tournament_desc_input').is(':visible'), 'Input box should be visible');
    assert.ok(!($('#editLink').is(':visible')), 'Edit link should not be visible');
    assert.ok($('#submitLink').is(':visible'), 'Submit link should be visible');

    // test the page seen by players that are not the creator while people are still joining
    Api.tournament.isCreator = false;
    Tournament.showStatePage();
    assert.ok($('#tournament_desc').is(':visible'), 'Tournament description should be visible');
    assert.equal($('#tournament_desc').text(), 'Initial description', 'Initial tournament description should be correct');
    assert.ok(!($('#tournament_desc_input').is(':visible')), 'Input box should not be visible');
    assert.ok(!($('#editLink').is(':visible')), 'Edit link should only be visible for the creator');
    assert.ok(!($('#submitLink').is(':visible')), 'Submit link should not be visible');

    // test the page seen by the creator when the tournament has started
    Api.tournament.isCreator = true;
    Api.tournament.tournamentState = Tournament.TOURN_STATE_START_ROUND;
    Tournament.showStatePage();
    assert.ok($('#tournament_desc').is(':visible'), 'Tournament description should be visible');
    assert.equal($('#tournament_desc').text(), 'Initial description', 'Initial tournament description should be correct');
    assert.ok(!($('#tournament_desc_input').is(':visible')), 'Input box should not be visible');
    assert.ok(!($('#editLink').is(':visible')), 'Edit link should only be visible for the creator');
    assert.ok(!($('#submitLink').is(':visible')), 'Submit link should not be visible');

    // test the page seen by players that are not the creator when the tournament has started
    Api.tournament.isCreator = false;
    Tournament.showStatePage();
    assert.ok($('#tournament_desc').is(':visible'), 'Tournament description should be visible');
    assert.equal($('#tournament_desc').text(), 'Initial description', 'Initial tournament description should be correct');
    assert.ok(!($('#tournament_desc_input').is(':visible')), 'Input box should not be visible');
    assert.ok(!($('#editLink').is(':visible')), 'Edit link should only be visible for the creator');
    assert.ok(!($('#submitLink').is(':visible')), 'Submit link should not be visible');

    start();
  });
});

test("test_Tournament.formSubmitTournDesc", function(assert) {
  stop();
  BMTestUtils.TournamentType = 'default';
  Tournament.getCurrentTournament(function() {
    Api.tournament.description = 'Initial description';
    Api.tournament.isCreator = true;
    Api.tournament.tournamentState = Tournament.TOURN_STATE_JOIN_TOURNAMENT;
    Tournament.showStatePage();
    Tournament.formEditTournDesc();
    assert.ok($('#tournament_desc_input').is(':visible'), 'Input box should be visible');

    $('#tournament_desc_input').val('New description');

    Tournament.formSubmitTournDesc();
    Login.arrangePage(Tournament.page, Tournament.form, '#submitLink');
    $.ajaxSetup({ async: false });
    $('#submitLink').trigger('click');
    assert.deepEqual(
      Env.message,
      {"type": "success", "text": "Tournament description saved"},
      "Tournament description save action succeeded when expected arguments were set"
    );
    assert.equal($('#tournament_desc').text(), 'New description', 'New description should be correct');
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_Tournament.formCancelTournament", function(assert) {

});

test("test_Tournament.formJoinTournament", function(assert) {

});

test("test_Tournament.formLeaveTournament", function(assert) {

});

test("test_Tournament.pageAddPlayerInfo", function(assert) {

});
