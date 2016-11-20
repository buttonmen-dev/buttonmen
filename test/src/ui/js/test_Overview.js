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
    delete Api.new_games;
    delete Api.active_games;
    delete Api.completed_games;
    delete Api.cancelled_games;
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
    $("#favicon").attr("href","/favicon.ico");

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

test("test_Overview.addTypeToGameSource", function(assert) {
  var gamesource = [[], [], []];
  var gameType = 'testType';
  Overview.addTypeToGameSource(gamesource, gameType);
  assert.equal(gamesource[0].gameType, 'testType', 'First element is assigned type');
  assert.equal(gamesource[1].gameType, 'testType', 'Second element is assigned type');
  assert.equal(gamesource[2].gameType, 'testType', 'Third element is assigned type');
});

test("test_Overview.addTableStructure", function(assert) {
  stop();
  Overview.getOverview(function() {
    Overview.page = $('<div>');
    // check table creation without dismiss column
    var tableBody = Overview.addTableStructure('testTableClass', 'testHeader', false);
    assert.equal(
      Overview.page.html(),
      '<div>' +
        '<h2>testHeader</h2>' +
        '<table class="gameList testTableClass">' +
          '<thead>' +
            '<tr>' +
              '<th>Game</th>' +
              '<th>Your Button</th>' +
              '<th>Opponent\'s Button</th>' +
              '<th>Opponent</th>' +
              '<th>Score<br>W/L/T (Max)</th>' +
              '<th>Description</th>' +
              '<th>Inactivity</th>' +
            '</tr>' +
          '</thead>' +
          '<tbody>' +
          '</tbody>' +
        '</table>' +
      '</div>',
      'Table structure without dismiss column is correct'
    );
    assert.ok(tableBody.is('tbody'));

    Overview.page = $('<div>');
    // check table creation with dismiss column
    var tableBody = Overview.addTableStructure('testTableClass', 'testHeader', true);
    assert.equal(
      Overview.page.html(),
      '<div>' +
        '<h2>testHeader</h2>' +
        '<table class="gameList testTableClass">' +
          '<thead>' +
            '<tr>' +
              '<th>Game</th>' +
              '<th>Your Button</th>' +
              '<th>Opponent\'s Button</th>' +
              '<th>Opponent</th>' +
              '<th>Score<br>W/L/T (Max)</th>' +
              '<th>Description</th>' +
              '<th>Inactivity</th>' +
              '<th>Dismiss</th>' +
            '</tr>' +
          '</thead>' +
          '<tbody>' +
          '</tbody>' +
        '</table>' +
      '</div>',
      'Table structure with dismiss column is correct'
    );
    assert.ok(tableBody.is('tbody'));

    // check addition of table spacer
    tableBody = Overview.addTableStructure('testTableClass', 'testHeader', true);
    assert.equal(
      tableBody.html(),
      '<tr class="spacer">' +
        '<td colspan="8">&nbsp;</td>' +
      '</tr>',
      'Table body spacer is present'
    );

    start();
  });
});

test("test_Overview.addTableRows", function(assert) {
  // james: currently a stub
});

test("test_Overview.addGameCol", function(assert) {
  var gameInfo;
  var gameRow;
  var gameType;

  // active game awaiting player
  gameInfo = [];
  gameInfo.gameId = 500;
  gameInfo.inactivityRaw = 400000;
  gameInfo.playerColor = '#fedcba';
  gameInfo.opponentColor = '#abcdef';
  gameRow = $('<tr>');
  gameType = 'awaitingPlayer';
  Overview.addGameCol(gameRow, gameInfo, gameType);
  assert.equal(
    gameRow.html(),
    '<td style=\"background-color: #fedcba\">' +
      '<a href=\"game.html?game=500\">Play Game 500</a>' +
    '</td>'
  )

  // active game awaiting opponent, not stale
  gameInfo = [];
  gameInfo.gameId = 500;
  gameInfo.inactivityRaw = 400;
  gameInfo.playerColor = '#fedcba';
  gameInfo.opponentColor = '#abcdef';
  gameRow = $('<tr>');
  gameType = 'awaitingOpponent';
  Overview.addGameCol(gameRow, gameInfo, gameType);
  assert.equal(
    gameRow.html(),
    '<td style=\"background-color: #abcdef\">' +
      '<a href=\"game.html?game=500\">View Game 500</a>' +
    '</td>'
  )
  assert.ok(!gameRow.hasClass('staleGame'));

  // active game awaiting opponent, stale
  gameInfo = [];
  gameInfo.gameId = 500;
  gameInfo.inactivityRaw = Overview.STALENESS_SECS + 1000;
  gameInfo.playerColor = '#fedcba';
  gameInfo.opponentColor = '#abcdef';
  gameRow = $('<tr>');
  gameType = 'awaitingOpponent';
  Overview.addGameCol(gameRow, gameInfo, gameType);
  assert.equal(
    gameRow.html(),
    '<td style=\"background-color: #abcdef\">' +
      '<a href=\"game.html?game=500\">View Game 500</a>' +
    '</td>'
  )
  assert.ok(gameRow.hasClass('staleGame'));
});

test("test_Overview.addButtonCol", function(assert) {
  var gameRow = $('<tr>');
  Overview.addButtonCol(gameRow, 'testButtonName');
  assert.equal(
    gameRow.html(),
    '<td>' +
      '<a href=\"buttons.html?button=testButtonName\">testButtonName</a>' +
    '</td>'
  )
});

test("test_Overview.addPlayerCol", function(assert) {
  var gameRow = $('<tr>');
  Overview.addPlayerCol(gameRow, 'testPlayerName', 0, '#fdcfff');
  assert.equal(
    gameRow.html(),
    '<td style=\"background-color: #fdcfff\">' +
      '<a href=\"profile.html?player=testPlayerName\">testPlayerName</a>' +
    '</td>'
  )
  var gameRow = $('<tr>');
  Overview.addPlayerCol(gameRow, 'testPlayerName', 1, '#fdcfff');
  assert.ok(gameRow.html().match('vacation16.png'),
	    'Player column should contain vacation image');
});

test("test_Overview.addScoreCol", function(assert) {
  var gameInfo = [];
  gameInfo.maxWins = 5;
  gameInfo.gameScoreDict = [];
  gameInfo.gameScoreDict.W = 2;
  gameInfo.gameScoreDict.L = 1;
  gameInfo.gameScoreDict.D = 4;
  gameInfo.playerColor = '#fedcba';
  gameInfo.opponentColor = '#abcdef';
  var gameRow;

  gameRow = $('<tr>');
  gameInfo.gameType = 'cancelled';
  Overview.addScoreCol(gameRow, gameInfo);
  assert.equal(
    gameRow.html(),
    '<td>–/–/– (5)</td>',
    'Cancelled games have a dashed score'
  );

  gameRow = $('<tr>');
  gameInfo.gameType = 'new';
  Overview.addScoreCol(gameRow, gameInfo);
  assert.equal(
    gameRow.html(),
    '<td>–/–/– (5)</td>',
    'New games have a dashed score'
  );

  gameRow = $('<tr>');
  gameInfo.gameType = 'active';
  Overview.addScoreCol(gameRow, gameInfo);
  assert.equal(
    gameRow.html(),
    '<td style=\"background-color: #fedcba\">2/1/4 (5)</td>',
    'Active games show the score on the winner\'s background'
  );

  gameRow = $('<tr>');
  gameInfo.gameScoreDict.W = 2;
  gameInfo.gameScoreDict.L = 3;
  gameInfo.gameScoreDict.D = 4;
  gameInfo.gameType = 'active';
  Overview.addScoreCol(gameRow, gameInfo);
  assert.equal(
    gameRow.html(),
    '<td style=\"background-color: #abcdef\">2/3/4 (5)</td>',
    'Active games show the score on the winner\'s background'
  );
});

test("test_Overview.addDescCol", function(assert) {
  var gameRow = $('<tr>');
  Overview.addDescCol(gameRow, 'short description');
  assert.equal(
    gameRow.html(),
    '<td class="gameDescDisplay">short description</td>',
    'Description column'
  );

  gameRow = $('<tr>');
  Overview.addDescCol(gameRow, '1234567890123456789012345678901234567890');
  assert.equal(
    gameRow.html(),
    '<td class="gameDescDisplay">123456789012345678901234567890...</td>',
    'Description column truncates correctly'
  );
});

test("test_Overview.addInactiveCol", function(assert) {
  var gameRow = $('<tr>');
  Overview.addInactiveCol(gameRow, '21 days');
  assert.equal(
    gameRow.html(),
    '<td>21 days</td>',
    'Inactivity column'
  );
});

test("test_Overview.addDismissCol", function(assert) {
  var gameInfo;
  var gameRow;
  var column;
  var links;

  // closed game
  gameInfo = [];
  gameInfo.gameId = 500;
  gameRow = $('<tr>');
  Overview.addDismissCol(gameRow, gameInfo);
  assert.equal(
    gameRow.children().length,
    1,
    'add exactly one dismiss column'
  );
  column = gameRow.children();
  assert.ok(
    column.is('td'),
    'dismiss column is correct type'
  );
  assert.equal(
    column.text(),
    '[Dismiss]',
    'dismiss text is correct'
  );
  links = column.children();
  assert.equal(
    links.length,
    1,
    'add exactly one link'
  );
  assert.ok(
    links.is('a'),
    'dismiss link is correct type'
  );
  assert.equal(
    links.attr('data-gameid'),
    '500',
    'dismiss data-gameid is correct'
  );
  assert.equal(
    links.attr('href'),
    '#',
    'dismiss href is correct'
  );
  assert.equal(
    links.text(),
    'Dismiss',
    'dismiss text is correct'
  );
});

test("test_Overview.linkTextStub", function(assert) {
  var gameInfo = [];
  var gameType = '';

  gameInfo.gameType = 'new';
  assert.equal(
    Overview.linkTextStub(gameInfo, gameType),
    'NEW',
    'new game stub'
  );

  gameInfo.gameType = 'cancelled';
  assert.equal(
    Overview.linkTextStub(gameInfo, gameType),
    'CANCELLED',
    'cancelled game stub'
  );

  gameInfo.gameType = 'completed';
  gameInfo.gameScoreDict = [];
  gameInfo.gameScoreDict.W = 1;
  gameInfo.gameScoreDict.L = 0;
  gameInfo.gameScoreDict.D = 2;
  assert.equal(
    Overview.linkTextStub(gameInfo, gameType),
    'WON',
    'won game stub'
  );

  gameInfo.gameScoreDict.L = 3;
  assert.equal(
    Overview.linkTextStub(gameInfo, gameType),
    'LOST',
    'lost game stub'
  );

  gameInfo.gameScoreDict.L = 1;
  assert.equal(
    Overview.linkTextStub(gameInfo, gameType),
    'TIED',
    'tied game stub'
  );

  gameInfo.gameType = 'active';
  gameType = 'awaitingPlayer';
  assert.equal(
    Overview.linkTextStub(gameInfo, gameType),
    'Play',
    'active game stub, waiting on player'
  );

  gameInfo.gameType = 'active';
  gameType = 'awaitingOpponent';
  assert.equal(
    Overview.linkTextStub(gameInfo, gameType),
    'View',
    'active game stub, waiting on opponent'
  );
});

test("test_Overview.staleGameFooter", function(assert) {
  var tableFoot = Overview.staleGameFooter(8);

  assert.ok(tableFoot.is('tfoot'), 'tableFoot is correct type');
  var row = tableFoot.children();
  assert.ok(
    row.is('tr'),
    'stale games footer row is correct type'
  );
  var col = row.children();
  assert.ok(
    col.is('td'),
    'stale games footer col is correct type'
  );
  assert.equal(
    col.length,
    1,
    'exactly one col in the stale games footer row'
  );
  assert.equal(
    col.text(),
    '[Show stale games]',
    'stale games footer has correct text'
  );
  assert.equal(
    col.attr('colspan'),
    '8',
    'stale games footer col has correct colspan'
  );
  var link = col.children();
  assert.ok(
    link.is('a'),
    'stale games link is correct type'
  );
  assert.equal(
    link.length,
    1,
    'exactly one link in the stale games footer row'
  );
  assert.equal(
    link.text(),
    'Show stale games',
    'stale games footer link has correct text'
  );
  assert.equal(
    link.attr('href'),
    'javascript:Overview.toggleStaleGame();',
    'stale games footer has correct href'
  );
  assert.equal(
    link.attr('id'),
    'staleToggle',
    'stale games footer has correct id'
  );
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
  assert.ok(htmlout.match('Buttonweavers implementation of'),
    'Preamble should be visible');
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
