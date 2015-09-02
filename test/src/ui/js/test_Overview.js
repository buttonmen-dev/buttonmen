module("Overview", {
  'setup': function() {
    BMTestUtils.OverviewPre = BMTestUtils.getAllElements();

    // Back up any properties that we might decide to replace with mocks
    BMTestUtils.OverviewBackup = { };
    BMTestUtils.CopyAllMethods(Overview, BMTestUtils.OverviewBackup);
    BMTestUtils.ApiBackup = { };
    BMTestUtils.CopyAllMethods(Api, BMTestUtils.ApiBackup);
    BMTestUtils.LoginBackup = { };
    BMTestUtils.CopyAllMethods(Login, BMTestUtils.LoginBackup);

    BMTestUtils.setupFakeLogin();

    // Create the overview_page div so functions have something to modify
    if (document.getElementById('overview_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'overview_page', }));
    }

    Login.pageModule = { 'bodyDivId': 'overview_page' };
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JavaScript variables
    delete Api.active_games;
    delete Api.completed_games;
    delete Api.gameNavigation;
    delete Api.forumNavigation;
    delete Api.user_prefs;
    delete Overview.page;
    delete Overview.monitorIsOn;
    delete Env.window.location.href;

    Api.automatedApiCall = false;
    Login.pageModule = null;
    Login.nextGameRefreshCallback = false;

    // Page elements
    $('#overview_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Restore any properties that we might have replaced with mocks
    BMTestUtils.CopyAllMethods(BMTestUtils.OverviewBackup, Overview);
    BMTestUtils.CopyAllMethods(BMTestUtils.ApiBackup, Api);
    BMTestUtils.CopyAllMethods(BMTestUtils.LoginBackup, Login);

    // Fail if any other elements were added or removed
    BMTestUtils.OverviewPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.OverviewPost, BMTestUtils.OverviewPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Overview module has been loaded
test("test_Overview_is_loaded", function(assert) {
  assert.ok(Overview, "The Overview namespace exists");
});

// The purpose of this test is to demonstrate that the flow of
// Overview.showLoggedInPage() is correct for a showXPage function, namely
// that it calls an API getter with a showStatePage function as a
// callback.
//
// Accomplish this by mocking the invoked functions
test("test_Overview.showLoggedInPage", function(assert) {
  expect(5);
  var cached_getOverview = Overview.getOverview;
  var cached_showStatePage = Overview.showPage;
  var getOverviewCalled = false;
  Overview.showPage = function() {
    assert.ok(getOverviewCalled, "Overview.getOverview is called before Overview.showPage");
  }
  Overview.getOverview = function(callback) {
    getOverviewCalled = true;
    assert.equal(callback, Overview.showPage,
      "Overview.getOverview is called with Overview.showPage as an argument");
    callback();
  }

  Overview.showLoggedInPage();
  var item = document.getElementById('overview_page');
  assert.equal(item.nodeName, "DIV",
        "#overview_page is a div after showLoggedInPage() is called");

  Overview.getOverview = cached_getOverview;
  Overview.showPage = cached_showStatePage;
});

test("test_Overview.showPreferredOverview", function(assert) {
  expect(4); // tests + 2 teardown

  Api.getUserPrefsData = function(callback) {
    Api.user_prefs = { 'automatically_monitor': false };
    callback();
  };

  Overview.executeMonitor = function() {
    assert.ok(!Overview.monitorIsOn, 'Monitor should be off');
    assert.ok(false, 'Monitor should not be executed');
  };

  Overview.getOverview = function() {
    assert.ok(!Overview.monitorIsOn, 'Monitor should be off');
    assert.ok(true, 'Overview should be displayed');
  };

  Overview.showPreferredOverview();
});

test("test_Overview.showPreferredOverview_monitor", function(assert) {
  expect(4); // tests + 2 teardown

  Api.getUserPrefsData = function(callback) {
    Api.user_prefs = { 'automatically_monitor': true };
    callback();
  };

  Overview.executeMonitor = function() {
    assert.ok(Overview.monitorIsOn, 'Monitor should be on');
    assert.ok(true, 'Monitor should be executed');
  };

  Overview.getOverview = function() {
    assert.ok(Overview.monitorIsOn, 'Monitor should be on');
    assert.ok(false, 'Overview should not be displayed yet');
  };

  Overview.showPreferredOverview();
});

test("test_Overview.getOverview", function(assert) {
  stop();
  Overview.getOverview(function() {
    assert.ok(Api.active_games, "active games are parsed from server");
    assert.ok(Api.completed_games, "active games are parsed from server");
    start();
  });
});

test("test_Overview.showLoggedOutPage", function(assert) {
  // Undo the fake login data
  Login.player = null;
  Login.logged_in = false;

  Overview.showLoggedOutPage();
  assert.equal(Env.message, undefined,
        "No Env.message when logged out");
  var item = document.getElementById('overview_page');
  assert.ok(item.innerHTML.match('Welcome to Button Men'),
        "#overview_page contains some welcoming text");
});

test("test_Overview.showPage", function(assert) {
  stop();
  Overview.getOverview(function() {
    Overview.showPage();
    var htmlout = Overview.page.html();
    assert.ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

test("test_Overview.pageAddGameTables", function(assert) {
  stop();
  Overview.getOverview(function() {
    Overview.page = $('<div>');
    Overview.pageAddGameTables();
    assert.ok(true, "No special testing of pageAddGameTables() as a whole is done");
    start();
  });
});

// The default overview data contains games awaiting both the player and the opponent
test("test_Overview.pageAddNewgameLink", function(assert) {
  stop();
  Overview.getOverview(function() {
    Overview.page = $('<div>');
    Overview.pageAddNewgameLink();
    var htmlout = Overview.page.html();
    assert.ok(!htmlout.match("Create a new game"),
      "Overview page does not contain new game creation message when active games exist");
    assert.ok(!htmlout.match("join an open game"),
      "Overview page does not contain message about joining open games when active games exist");
    assert.ok(htmlout.match("Go to your next pending game"),
      "Overview page contains a link to the 'next game' function when a game is awaiting action");
    start();
  });
});

test("test_Overview.pageAddNewgameLink_noactive", function(assert) {
  stop();
  Overview.getOverview(function() {
    Api.active_games.games.awaitingPlayer = [];
    Overview.page = $('<div>');
    Overview.pageAddNewgameLink();
    var htmlout = Overview.page.html();
    assert.ok(!htmlout.match("Create a new game"),
      "Overview page does not contain new game creation message when active games exist");
    assert.ok(!htmlout.match("join an open game"),
      "Overview page does not contain message about joining open games when active games exist");
    assert.ok(!htmlout.match("Go to your next pending game"),
      "Overview page does not contain a link to the 'next game' function when no game is awaiting action");
    start();
  });
});

test("test_Overview.pageAddNewgameLink_nogames", function(assert) {
  stop();
  Overview.getOverview(function() {
    Api.active_games.games = {
      'awaitingOpponent': [],
      'awaitingPlayer': [],
    };
    Overview.page = $('<div>');
    Overview.pageAddNewgameLink();
    assert.ok(Overview.page.html().match("Create a new game"),
      "Overview page contains new game creation message");
    assert.ok(Overview.page.html().match("join an open game"),
      "Overview page contains message about joining open games");
    start();
  });
});

test("test_Overview.pageAddGameTable", function(assert) {
  stop();
  Overview.getOverview(function() {
    Overview.page = $('<div>');
    Overview.pageAddGameTable('awaitingPlayer', 'Active games');
    var htmlout = Overview.page.html();
    assert.ok(htmlout.match('<h2>Active games'), "Section header should be set");
    assert.ok(htmlout.match('<table class="gameList activeGames">'), "A table is created");
    start();
  });
});

test("test_Overview.toggleStaleGame", function(assert) {
  // james: currently don't know how to do this testing
})

test("test_Overview.pageAddIntroText", function(assert) {
  Overview.page = $('<div>');
  Overview.pageAddIntroText();
  var htmlout = Overview.page.html();
  assert.ok(htmlout.match(
    'Button Men is copyright 1999, 2015 James Ernest and Cheapass Games'),
    'Page intro text contains the Button Men copyright');
});

test("test_Overview.formDismissGame", function(assert) {
  stop();
  expect(3);
  // Temporarily back up Overview.showLoggedInPage and replace it with
  // a mocked version for testing
  var showLoggedInPage = Overview.showLoggedInPage;
  Overview.showLoggedInPage = function() {
    Overview.showLoggedInPage = showLoggedInPage;
    assert.equal(Env.message.text, 'Successfully dismissed game',
      'Dismiss game should succeed');
    start();
  };
  var link = $('<a>', { 'data-gameId': 5 });
  Overview.formDismissGame.call(link, $.Event());
});

test("test_Overview.goToNextNewForumPost", function(assert) {
  var initialUrl = "/ui/game.html?game=1";
  Env.window.location.href = initialUrl;
  Api.forumNavigation = {
    'load_status': 'ok',
    'nextNewPostId': 3,
    'nextNewPostThreadId': 2,
  };

  Overview.goToNextNewForumPost();
  notEqual(Env.window.location.href, initialUrl, "The page has been redirected");
  if (Env.window.location.href !== null && Env.window.location.href !== undefined)
  {
    assert.ok(Env.window.location.href.match(/forum\.html#!threadId=2&postId=3/),
      "The page has been redirected to the new post");
  }
});

test("test_Overview.executeMonitor", function(assert) {
  stop();
  expect(3);

  Api.user_prefs = {
    'monitor_redirects_to_game': false,
    'monitor_redirects_to_forum': false,
  };

  // Replacing functions with mocks, for testing purposes
  Overview.getOverview = function() {
    assert.ok(true, 'Should go straight to re-displaying the Overview');
    start();
  };
  Overview.completeMonitor = function() {
    assert.ok(false, 'Should go straight to re-displaying the Overview');
    start();
  };

  Overview.executeMonitor();
});

test("test_Overview.executeMonitor_withRedirects", function(assert) {
  stop();
  expect(4);

  Api.user_prefs = {
    'monitor_redirects_to_game': true,
    'monitor_redirects_to_forum': true,
  };

  // Replacing functions with mocks, for testing purposes
  Overview.getOverview = function() {
    assert.ok(false, 'Should go retrieve pending game data for redirect');
    assert.ok(false, 'Should go retrieve new psot data for redirect');
    start();
  };
  Overview.completeMonitor = function() {
    assert.ok(Api.gameNavigation, 'Should go retrieve pending game data for redirect');
    assert.ok(Api.forumNavigation, 'Should go retrieve new psot data for redirect');
    start();
  };

  Overview.executeMonitor();
});

test("test_Overview.completeMonitor", function(assert) {
  stop();
  expect(3);

  Api.user_prefs = {
    'monitor_redirects_to_game': true,
    'monitor_redirects_to_forum': true,
  };

  Api.gameNavigation = { 'nextGameId': null };
  Api.forumNavigation = { 'nextNewPostId': null };

  // Replacing functions with mocks, for testing purposes
  Overview.getOverview = function() {
    assert.ok(true, 'Should go straight to re-displaying the Overview');
    start();
  };
  Login.goToNextPendingGame = function() {
    assert.ok(false, 'Should go straight to re-displaying the Overview');
    start();
  };
  Overview.goToNextNewForumPost = function() {
    assert.ok(false, 'Should go straight to re-displaying the Overview');
    start();
  };

  Overview.completeMonitor();
});

test("test_Overview.completeMonitor_withPendingGame", function(assert) {
  stop();
  expect(3);

  Api.user_prefs = {
    'monitor_redirects_to_game': true,
    'monitor_redirects_to_forum': true,
  };

  Api.gameNavigation = { 'nextGameId': 7 };
  Api.forumNavigation = { 'nextNewPostId': null };

  // Replacing functions with mocks, for testing purposes
  Overview.getOverview = function() {
    assert.ok(false, 'Should redirect to pending game');
    start();
  };
  Login.goToNextPendingGame = function() {
    assert.ok(true, 'Should redirect to pending game');
    start();
  };
  Overview.goToNextNewForumPost = function() {
    assert.ok(false, 'Should redirect to pending game');
    start();
  };

  Overview.completeMonitor();
});

test("test_Overview.completeMonitor_withNewPost", function(assert) {
  stop();
  expect(3);

  Api.user_prefs = {
    'monitor_redirects_to_game': true,
    'monitor_redirects_to_forum': true,
  };

  Api.gameNavigation = { 'nextGameId': null };
  Api.forumNavigation = { 'nextNewPostId': 3 };

  // Replacing functions with mocks, for testing purposes
  Overview.getOverview = function() {
    assert.ok(false, 'Should redirect to new post');
    start();
  };
  Login.goToNextPendingGame = function() {
    assert.ok(false, 'Should redirect to new post');
    start();
  };
  Overview.goToNextNewForumPost = function() {
    assert.ok(true, 'Should redirect to new post');
    start();
  };

  Overview.completeMonitor();
});
