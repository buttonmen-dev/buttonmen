// namespace for this "module"
var Overview = {};

Overview.bodyDivId = 'overview_page';

// We only need one game state for this module, so just reproduce the
// setting here rather than importing Game.js
Overview.GAME_STATE_END_GAME = 60;

Overview.MONITOR_TIMEOUT = 60;

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Overview.showLoggedInPage() is the landing function.  Always call this
//   first when logged in. This will either call Login.goToNextPendingGame,
//   Overview.executeMonitor() or Overview.getOverview() and
//   Overview.showPage() (depending on the "mode" parameter).
// * Overview.showLoggedInPage() is the landing function.  Always call this
//   first when logged out. This calls Overview.pageAddIntroText()
// * Overview.getOverview() asks the API for information about the
//   player's overview status (currently, the lists of active and completed
//   games, and potentially the user's preferences).
//   It sets Api.active_games, Api.completed_games and potentially
//   Api.user_prefs.  If successful, it calls Overview.showPage().
// * Overview.showPage() assembles the page contents as a variable.
//
// N.B. There is no form submission on this page (aside from the [Dismiss]
// links); it's just a landing page with links to other pages. So it's
// logically somewhat simpler than e.g. Game.js.
////////////////////////////////////////////////////////////////////////

Overview.showLoggedInPage = function() {
  // Set up the callback for refreshing the page if there's no next game
  Login.nextGameRefreshCallback = Overview.showPreferredOverview;

  var mode = Env.getParameterByName('mode');

  switch (mode) {
  case 'nextGame':
    // Try to go to the next game
    Api.getNextGameId(Login.goToNextPendingGame);
    break;
  case 'monitor':
    Overview.monitorIsOn = true;
    // If we're in monitor mode, run the monitor first
    Api.getUserPrefsData(function() {
      Overview.executeMonitor();
    });
    break;
  case 'preference':
    Overview.showPreferredOverview();
    break;
  default:
    Overview.monitorIsOn = false;
    // Get all needed information, then display overview page
    Overview.getOverview(Overview.showPage);
    break;
  }
};

Overview.showLoggedOutPage = function() {
  Overview.page = $('<div>');
  Overview.pageAddIntroText();
  // Actually lay out the page
  Login.arrangePage(Overview.page);
};

Overview.showPreferredOverview = function() {
  Api.getUserPrefsData(function() {
    if (Api.user_prefs.automatically_monitor) {
      Overview.monitorIsOn = true;
      // If we're in monitor mode, run the monitor first
      Overview.executeMonitor();
    } else {
      Overview.monitorIsOn = false;
      // Get all needed information, then display overview page
      Overview.getOverview(Overview.showPage);
    }
  });
};

Overview.getOverview = function(callback) {
  Env.callAsyncInParallel([
    Api.getActiveGamesData,
    Api.getCompletedGamesData,
  ], callback);
};

Overview.showPage = function() {
  Overview.page = $('<div>');

  Overview.pageAddNewgameLink();

  if (Overview.monitorIsOn) {
    Overview.page.append($('<h2>', {
      'text': '* Monitor Active *',
      'class': 'monitorMessage',
    }));
    // Convert milliseconds (javascript-style) to seconds (unix-style)
    var currentTimestamp = new Date().getTime() / 1000;
    Overview.page.append($('<div>', {
      'text': 'Last refresh: ' + Env.formatTimestamp(currentTimestamp),
      'class': 'monitorTimestamp',
    }));
    Overview.page.append($('<div>').append($('<a>', {
      'text': 'Disable Monitor',
      'href': Env.ui_root,
    })));

    // Times 1000 because setTimeout expects milliseconds
    setTimeout(Overview.executeMonitor, Overview.MONITOR_TIMEOUT * 1000);
  }

  if ((Api.active_games.nGames === 0) && (Api.completed_games.nGames === 0)) {
    Env.message = {
      'type': 'none',
      'text': 'You have no games',
    };
  } else {
    Overview.pageAddGameTables();
  }

  // Actually lay out the page
  Login.arrangePage(Overview.page);
};

Overview.executeMonitor = function() {
  Api.automatedApiCall = true;
  if (Api.user_prefs.monitor_redirects_to_game &&
      Api.user_prefs.monitor_redirects_to_forum) {
    Env.callAsyncInParallel([
      Api.getNextGameId,
      Api.getNextNewPostId,
    ], Overview.completeMonitor);
  } else if (Api.user_prefs.monitor_redirects_to_game) {
    Api.getNextGameId(Overview.completeMonitor);
  } else if (Api.user_prefs.monitor_redirects_to_forum) {
    Api.getNextNewPostId(Overview.completeMonitor);
  } else {
    Overview.getOverview(Overview.showPage);
  }
};

Overview.completeMonitor = function() {
  if (Api.user_prefs.monitor_redirects_to_game &&
      Api.gameNavigation !== undefined && Api.gameNavigation.nextGameId) {
    Login.goToNextPendingGame();
    return;
  }
  if (Api.user_prefs.monitor_redirects_to_forum &&
      Api.forumNavigation !== undefined && Api.forumNavigation.nextNewPostId) {
    Overview.goToNextNewForumPost();
    return;
  }

  Overview.getOverview(Overview.showPage);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

// Add tables for types of existing games
Overview.pageAddGameTables = function() {
  Overview.pageAddGameTable('finished', 'Completed games', false);
  Overview.pageAddGameTable('awaitingPlayer', 'Active games', false);
  Overview.pageAddGameTable('awaitingOpponent', 'Active games', true);
};

Overview.pageAddNewgameLink = function() {
  var newgameDiv = $('<div>');
  var newgamePar = $('<p>');
  if (Api.active_games.games.awaitingPlayer.length > 0) {
    var newgameLink = $('<a>', {
      'href': Env.ui_root + 'index.html?mode=nextGame',
      'text': 'Go to your next pending game',
    });
    newgamePar.append(newgameLink);
    newgameLink.click(function(e) {
      e.preventDefault();
      Api.getNextGameId(Login.goToNextPendingGame);
    });
  } else if (Api.active_games.games.awaitingOpponent.length > 0) {
    // just return in this case, and don't add a message at all
    return;

  } else {
    newgamePar.append($('<a>', {
      'href': 'create_game.html',
      'text': 'Create a new game',
    }));
    newgamePar.append(' or ');
    newgamePar.append($('<a>', {
      'href': 'open_games.html',
      'text': 'join an open game',
    }));
  }
  newgameDiv.append(newgamePar);
  Overview.page.append(newgameDiv);
};

Overview.pageAddGameTable = function(gameType, sectionHeader, reverseSortOrder) {
  if (typeof reverseSortOrder === 'undefined') {
      reverseSortOrder = false;
  }

  var gamesource;
  var tableClass;
  if (gameType == 'finished') {
    gamesource = Api.completed_games.games;
    tableClass = 'finishedGames';
  } else {
    gamesource = Api.active_games.games[gameType];
    tableClass = 'activeGames';
  }

  if (gamesource.length === 0) {
    return;
  }

  if (reverseSortOrder) {
      gamesource.reverse();
  }

  var tableBody = Overview.page.find('table.' + tableClass + ' tbody');
  if (tableBody.length > 0) {
    var spacerRow = $('<tr>', { 'class': 'spacer' });
    tableBody.append(spacerRow);
    spacerRow.append($('<td>', { 'html': '&nbsp;', 'colspan': '6', }));
  } else {
    var tableDiv = $('<div>');
    tableDiv.append($('<h2>', {'text': sectionHeader, }));
    var table = $('<table>', { 'class': 'gameList ' + tableClass, });
    tableDiv.append(table);
    if (gameType == 'finished') {
      tableDiv.append($('<hr>'));
    }
    Overview.page.append(tableDiv);

    var tableHead = $('<thead>');
    var headerRow = $('<tr>');
    headerRow.append($('<th>', {'text': 'Game #', }));
    headerRow.append($('<th>', {'text': 'Your Button', }));
    headerRow.append($('<th>', {'text': 'Opponent\'s Button', }));
    headerRow.append($('<th>', {'text': 'Opponent', }));
    headerRow.append($('<th>', {'text': 'Score (W/L/T (Max))', }));
    if (gameType == 'finished') {
      headerRow.append($('<th>', {'text': 'Completed', 'colspan': '2', }));
    } else {
      headerRow.append($('<th>', {'text': 'Inactivity', 'colspan': '2', }));
    }
    tableHead.append(headerRow);
    table.append(tableHead);

    tableBody = $('<tbody>');
    table.append(tableBody);
  }

  var i = 0;
  while (i < gamesource.length) {
    var gameInfo = gamesource[i];
    var playerColor = gameInfo.playerColor;
    var opponentColor = gameInfo.opponentColor;

    var gameRow = $('<tr>');
    var gameLinkTd;
    if (gameType == 'awaitingPlayer') {
      gameLinkTd =
        $('<td>', { 'style': 'background-color: ' + playerColor, });
      gameLinkTd.append($('<a>', {'href': 'game.html?game=' + gameInfo.gameId,
                                  'text': 'Play Game ' + gameInfo.gameId,}));
    } else if (gameType == 'awaitingOpponent') {
      gameLinkTd =
        $('<td>', { 'style': 'background-color: ' + opponentColor, });
      gameLinkTd.append($('<a>', {'href': 'game.html?game=' + gameInfo.gameId,
                                  'text': 'View Game ' + gameInfo.gameId,}));
    } else {
      gameLinkTd = $('<td>');
      if (gameInfo.gameScoreDict.W > gameInfo.gameScoreDict.L) {
        gameLinkTd.append($('<a>', {
          'href': 'game.html?game=' + gameInfo.gameId,
          'text': 'WON Game ' + gameInfo.gameId,
        }));
      } else if (gameInfo.gameScoreDict.W < gameInfo.gameScoreDict.L) {
        gameLinkTd.append($('<a>', {
          'href': 'game.html?game=' + gameInfo.gameId,
          'text': 'LOST Game ' + gameInfo.gameId,
        }));
      } else {
        gameLinkTd.append($('<a>', {
          'href': 'game.html?game=' + gameInfo.gameId,
          'text': 'TIED Game ' + gameInfo.gameId,
        }));
      }
    }
    gameRow.append(gameLinkTd);
    gameRow.append($('<td>').append(
      Env.buildButtonLink(gameInfo.playerButtonName)
    ));
    gameRow.append($('<td>').append(
      Env.buildButtonLink(gameInfo.opponentButtonName)
    ));
    gameRow.append($('<td>', {
      'style': 'background-color: ' + opponentColor,
    }).append(Env.buildProfileLink(gameInfo.opponentName)));

    var wldColor = '#ffffff';
    if (gameInfo.gameScoreDict.W > gameInfo.gameScoreDict.L) {
      wldColor = playerColor;
    } else if (gameInfo.gameScoreDict.W < gameInfo.gameScoreDict.L) {
      wldColor = opponentColor;
    }
    gameRow.append($('<td>', {
      'text': gameInfo.gameScoreDict.W + '/' +
              gameInfo.gameScoreDict.L + '/' +
              gameInfo.gameScoreDict.D + ' (' + gameInfo.maxWins + ')',
      'style': 'background-color: ' + wldColor,
    }));

    var inactivityTd = $('<td>', { 'text': gameInfo.inactivity, });
    gameRow.append(inactivityTd);

    if (gameType == 'finished') {
      var dismissTd = $('<td>');
      gameRow.append(dismissTd);
      var dismissLink = $('<a>', {
        'text': '[Dismiss]',
        'href': '#',
        'data-gameId': gameInfo.gameId,
      });
      dismissLink.click(Overview.formDismissGame);
      dismissTd.append(dismissLink);
    } else {
      inactivityTd.attr('colspan', '2');
    }

    i += 1;
    tableBody.append(gameRow);
  }
};

Overview.pageAddIntroText = function() {
  Overview.page.append($('<h1>', {'text': 'Welcome to Button Men!', }));

  var infopar;
  if (Config.siteType == 'development') {
    infopar = $('<p>');
    infopar.append(
      'This is the <span style="color: red;">DEVELOPMENT</span> version of ' +
      'the Buttonweavers implementation of ');
    infopar.append($('<a>', {
      'href': 'http://www.cheapass.com/node/39',
      'text': 'Button Men',
    }));
    infopar.append('.');
    Overview.page.append(infopar);

    Overview.page.append($('<br />'));

    infopar = $('<p>');
    infopar.append(
      'If you\r looking for the Button Men open alpha, please head over to ');
    infopar.append($('<a>', {
      'href': 'http://www.buttonweavers.com',
      'text': 'www.buttonweavers.com',
    }));
    infopar.append(' to start beating people up!');
    Overview.page.append(infopar);

    infopar = $('<p>');
    infopar.append(
      'If you\'re interested in joining the development site as a tester, ' +
      'then check out our ');
    infopar.append($('<a>', {
      'href': 'https://github.com/buttonmen-dev/buttonmen/wiki/Tester-guide',
      'text': 'tester guide',
    }));
    infopar.append(', then Login using the menubar above, or ');
    infopar.append($('<a>', {
      'href': '/ui/create_user.html',
      'text': 'create an account',
    }));
    infopar.append('.');
    Overview.page.append(infopar);
  } else {
    infopar = $('<p>');
    infopar.append(
      'This is the alpha version of the Buttonweavers implementation of ');
    infopar.append($('<a>', {
      'href': 'http://www.cheapass.com/node/39',
      'text': 'Button Men',
    }));
    infopar.append('.');
    Overview.page.append(infopar);

    infopar = $('<p>');
    infopar.append(
      'Want to start beating people up?  Login using the menubar above, or ');
    infopar.append($('<a>', {
      'href': '/ui/create_user.html',
      'text': 'create an account',
    }));
    infopar.append('.');
    Overview.page.append(infopar);

    infopar = $('<p>');
    infopar.append(
      'We wanted to make this site publically available as soon as possible, ' +
      'so there are still a lot of bugs!  If you find anything broken or ' +
      'hard to use, or if you have any questions, please get in touch, ' +
      'either by opening a ticket at ');
    infopar.append($('<a>', {
      'href': 'https://github.com/buttonmen-dev/buttonmen/issues/new',
      'text': 'the buttonweavers issue tracker',
    }));
    infopar.append(' or by e-mailing us at help@buttonweavers.com.');
    Overview.page.append(infopar);
  }

  infopar = $('<p>');
  infopar.append(
    'Button Men is copyright 1999, 2014 James Ernest and Cheapass Games: ');
  infopar.append($('<a>', {
    'href': 'http://www.cheapass.com',
    'text': 'www.cheapass.com',
  }));
  infopar.append(' and ');
  infopar.append($('<a>', {
    'href': 'http://www.beatpeopleup.com',
    'text': 'www.beatpeopleup.com',
  }));
  infopar.append(', and is used with permission.');
  Overview.page.append(infopar);
};

Overview.formDismissGame = function(e) {
  e.preventDefault();
  var args = { 'type': 'dismissGame', 'gameId': $(this).attr('data-gameId'), };
  var messages = {
    'ok': { 'type': 'fixed', 'text': 'Successfully dismissed game', },
    'notok': { 'type': 'server' },
  };
  Api.apiFormPost(args, messages, $(this), Overview.showLoggedInPage,
    Overview.showLoggedInPage);
};

// Redirect to the next new forum post if there is one
Overview.goToNextNewForumPost = function() {
  // If we're making this call automatically for the monitor, keep track of that
  var appendix = '';
  if (Api.automatedApiCall) {
    appendix = '?auto=true';
  }

  if (Api.forumNavigation.load_status == 'ok') {
    if (Api.forumNavigation.nextNewPostId !== null &&
        $.isNumeric(Api.forumNavigation.nextNewPostId)) {
      Env.window.location.href =
        'forum.html' + appendix +
          '#!threadId=' + Api.forumNavigation.nextNewPostThreadId +
          '&postId=' + Api.forumNavigation.nextNewPostId;
    }
  } else {
    // If there are no new posts (which presumably means the user read them but
    // left this page open while doing so), just show the forum overview
    Env.window.location.href = 'forum.html' + appendix;
  }
};
