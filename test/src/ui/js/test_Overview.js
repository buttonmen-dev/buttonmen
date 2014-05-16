module("Overview", {
  'setup': function() {
    BMTestUtils.OverviewPre = BMTestUtils.getAllElements();

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
    delete Overview.page;

    // Page elements
    $('#overview_page').remove();
    $('#overview_page').empty();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

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

asyncTest("test_Overview.layoutPage", function() {
  Overview.getOverview(function() {
    Overview.page = $('<div>');
    Overview.page.append($('<p>', {'text': 'hi world', }));
    Overview.layoutPage();
    var item = document.getElementById('overview_page');
    equal(item.nodeName, "DIV",
          "#overview_page is a div after layoutPage() is called");
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

asyncTest("test_Overview.pageAddNewgameLink", function() {
  Overview.getOverview(function() {
    Overview.page = $('<div>');
    Overview.pageAddNewgameLink();
    deepEqual(Overview.page.html(),
      "<div><p><a href=\"create_game.html\">Create a new game</a></p></div>",
      "Page link contents are correct");
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
