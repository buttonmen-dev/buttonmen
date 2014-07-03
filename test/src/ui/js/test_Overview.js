module("Overview", {
  'setup': function() {
    BMTestUtils.OverviewPre = BMTestUtils.getAllElements();

    // Back up any properties that we might decide to replace with mocks
    BMTestUtils.OverviewBackup = { };
    BMTestUtils.CopyAllMethods(Overview, BMTestUtils.OverviewBackup);

    BMTestUtils.setupFakeLogin();

    // Create the overview_page div so functions have something to modify
    if (document.getElementById('overview_page') == null) {
      $('body').append($('<div>', {'id': 'overview_page', }));
    }
  },
  'teardown': function() {

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
    Login.nextGameRefreshCallback = false;

    // Page elements
    $('#overview_page').remove();
    $('#overview_page').empty();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Restore any properties that we might have replaced with mocks
    BMTestUtils.CopyAllMethods(BMTestUtils.OverviewBackup, Overview);

    // Fail if any other elements were added or removed
    BMTestUtils.OverviewPost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.OverviewPost, BMTestUtils.OverviewPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Overview module has been loaded
test("test_Overview_is_loaded", function() {
  ok(Overview, "The Overview namespace exists");
});

// Overview.showOverviewPage() does not directly take a callback,
// but, under the hood, it calls a function (Overview.getOverview())
// which calls a chain of two callbacks in succession.
// It appears that QUnit's asynchronous testing framework can't
// handle that situation, so don't use it --- instead turn off
// asynchronous processing in AJAX while we test this one.
test("test_Overview.showOverviewPage", function() {
  $.ajaxSetup({ async: false });
  Overview.showOverviewPage();
  var item = document.getElementById('overview_page');
  equal(item.nodeName, "DIV",
        "#overview_page is a div after showOverviewPage() is called");
  $.ajaxSetup({ async: true });
});

asyncTest("test_Overview.getOverview", function() {
  Overview.getOverview(function() {
    ok(Api.active_games, "active games are parsed from server");
    ok(Api.completed_games, "active games are parsed from server");
    start();
  });
});

asyncTest("test_Overview.getOverview_logged_out", function() {

  // Undo the fake login data
  Login.player = null;
  Login.logged_in = false;

  Overview.getOverview(function() {
    Overview.showPage();
    equal(Env.message, undefined,
          "No Env.message when logged out");
    var item = document.getElementById('overview_page');
    ok(item.innerHTML.match('Welcome to Button Men'),
          "#overview_page contains some welcoming text");
    start();
  });
});

asyncTest("test_Overview.showPage", function() {
  Overview.getOverview(function() {
    Overview.showPage();
    var htmlout = Overview.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

asyncTest("test_Overview.arrangePage", function() {
  Overview.getOverview(function() {
    Overview.page = $('<div>');
    Overview.page.append($('<p>', {'text': 'hi world', }));
    Overview.arrangePage();
    var item = document.getElementById('overview_page');
    equal(item.nodeName, "DIV",
          "#overview_page is a div after arrangePage() is called");
    start();
  });
});

asyncTest("test_Overview.pageAddGameTables", function() {
  Overview.getOverview(function() {
    Overview.page = $('<div>');
    Overview.pageAddGameTables();
    ok(true, "No special testing of pageAddGameTables() as a whole is done");
    start();
  });
});

// The default overview data contains games awaiting both the player and the opponent
asyncTest("test_Overview.pageAddNewgameLink", function() {
  Overview.getOverview(function() {
    Overview.page = $('<div>');
    Overview.pageAddNewgameLink();
    var htmlout = Overview.page.html();
    ok(!htmlout.match("Create a new game"),
      "Overview page does not contain new game creation message when active games exist");
    ok(!htmlout.match("join an open game"),
      "Overview page does not contain message about joining open games when active games exist");
    ok(htmlout.match("Go to your next pending game"),
      "Overview page contains a link to the 'next game' function when a game is awaiting action");
    start();
  });
});

asyncTest("test_Overview.pageAddNewgameLink_noactive", function() {
  Overview.getOverview(function() {
    Api.active_games.games.awaitingPlayer = [];
    Overview.page = $('<div>');
    Overview.pageAddNewgameLink();
    var htmlout = Overview.page.html();
    ok(!htmlout.match("Create a new game"),
      "Overview page does not contain new game creation message when active games exist");
    ok(!htmlout.match("join an open game"),
      "Overview page does not contain message about joining open games when active games exist");
    ok(!htmlout.match("Go to your next pending game"),
      "Overview page does not contain a link to the 'next game' function when no game is awaiting action");
    start();
  });
});

asyncTest("test_Overview.pageAddNewgameLink_nogames", function() {
  Overview.getOverview(function() {
    Api.active_games.games = {
      'awaitingOpponent': [],
      'awaitingPlayer': [],
    };
    Overview.page = $('<div>');
    Overview.pageAddNewgameLink();
    ok(Overview.page.html().match("Create a new game"),
      "Overview page contains new game creation message");
    ok(Overview.page.html().match("join an open game"),
      "Overview page contains message about joining open games");
    start();
  });
});

asyncTest("test_Overview.pageAddGameTable", function() {
  Overview.getOverview(function() {
    Overview.page = $('<div>');
    Overview.pageAddGameTable('awaitingPlayer', 'Active games');
    var htmlout = Overview.page.html();
    ok(htmlout.match('<h2>Active games'), "Section header should be set");
    ok(htmlout.match('<table class="gameList activeGames">'), "A table is created");
    start();
  });
});

test("test_Overview.pageAddIntroText", function() {
  Overview.page = $('<div>');
  Overview.pageAddIntroText();
  var htmlout = Overview.page.html();
  ok(htmlout.match(
    'Button Men is copyright 1999, 2014 James Ernest and Cheapass Games'),
    'Page intro text contains the Button Men copyright');
});

asyncTest("test_Overview.formDismissGame", function() {
  expect(2);
  // Temporarily back up Overview.showOverviewPage and replace it with
  // a mocked version for testing
  var showOverviewPage = Overview.showOverviewPage;
  Overview.showOverviewPage = function() {
    Overview.showOverviewPage = showOverviewPage;
    equal(Env.message.text, 'Successfully dismissed game',
      'Dismiss game should succeed');
    start();
  };
  var link = $('<a>', { 'data-gameId': 5 });
  Overview.formDismissGame.call(link, $.Event());
});

test("test_Overview.goToNextNewForumPost", function() {
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
    ok(Env.window.location.href.match(/forum\.html#!threadId=2&postId=3/),
      "The page has been redirected to the new post");
  }
});

asyncTest("test_Overview.executeMonitor", function() {
  expect(2);

  Api.user_prefs = {
    'monitorRedirectsToGame': false,
    'monitorRedirectsToForum': false,
  };

  // Replacing functions with mocks, for testing purposes
  Overview.getOverview = function() {
    ok(true, 'Should go straight to re-displaying the Overview');
    start();
  };
  Overview.completeMonitor = function() {
    ok(false, 'Should go straight to re-displaying the Overview');
    start();
  };

  Overview.executeMonitor();
});

asyncTest("test_Overview.executeMonitor_withRedirects", function() {
  expect(3);

  Api.user_prefs = {
    'monitorRedirectsToGame': true,
    'monitorRedirectsToForum': true,
  };

  // Replacing functions with mocks, for testing purposes
  Overview.getOverview = function() {
    ok(false, 'Should go retrieve pending game data for redirect');
    ok(false, 'Should go retrieve new psot data for redirect');
    start();
  };
  Overview.completeMonitor = function() {
    ok(Api.gameNavigation, 'Should go retrieve pending game data for redirect');
    ok(Api.forumNavigation, 'Should go retrieve new psot data for redirect');
    start();
  };

  Overview.executeMonitor();
});

asyncTest("test_Overview.completeMonitor", function() {
  expect(2);

  Api.user_prefs = {
    'monitorRedirectsToGame': true,
    'monitorRedirectsToForum': true,
  };

  Api.gameNavigation = { 'nextGameId': null };
  Api.forumNavigation = { 'nextNewPostId': null };

  // Replacing functions with mocks, for testing purposes
  Overview.getOverview = function() {
    ok(true, 'Should go straight to re-displaying the Overview');
    start();
  };
  Login.goToNextPendingGame = function() {
    ok(false, 'Should go straight to re-displaying the Overview');
    start();
  };
  Overview.goToNextNewForumPost = function() {
    ok(false, 'Should go straight to re-displaying the Overview');
    start();
  };

  Overview.completeMonitor();
});

asyncTest("test_Overview.completeMonitor_withPendingGame", function() {
  expect(2);

  Api.user_prefs = {
    'monitorRedirectsToGame': true,
    'monitorRedirectsToForum': true,
  };

  Api.gameNavigation = { 'nextGameId': 7 };
  Api.forumNavigation = { 'nextNewPostId': null };

  // Replacing functions with mocks, for testing purposes
  Overview.getOverview = function() {
    ok(false, 'Should redirect to pending game');
    start();
  };
  Login.goToNextPendingGame = function() {
    ok(true, 'Should redirect to pending game');
    start();
  };
  Overview.goToNextNewForumPost = function() {
    ok(false, 'Should redirect to pending game');
    start();
  };

  Overview.completeMonitor();
});

asyncTest("test_Overview.completeMonitor_withNewPost", function() {
  expect(2);

  Api.user_prefs = {
    'monitorRedirectsToGame': true,
    'monitorRedirectsToForum': true,
  };

  Api.gameNavigation = { 'nextGameId': null };
  Api.forumNavigation = { 'nextNewPostId': 3 };

  // Replacing functions with mocks, for testing purposes
  Overview.getOverview = function() {
    ok(false, 'Should redirect to new post');
    start();
  };
  Login.goToNextPendingGame = function() {
    ok(false, 'Should redirect to new post');
    start();
  };
  Overview.goToNextNewForumPost = function() {
    ok(true, 'Should redirect to new post');
    start();
  };

  Overview.completeMonitor();
});
