// namespace for this "module"
var Overview = {};

Overview.bodyDivId = 'overview_page';

Overview.MONITOR_TIMEOUT = 60;
Overview.STALENESS_DAYS = 14;
Overview.STALENESS_SECS = Overview.STALENESS_DAYS * 24 * 60 * 60;

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Overview.showLoggedInPage() is the landing function.  Always call this
//   first when logged in. This will either call Login.goToNextPendingGame,
//   Overview.executeMonitor() or Overview.getOverview() and
//   Overview.showPage() (depending on the "mode" parameter).
// * Overview.showLoggedInPage() is the landing function.  Always call this
//   first when logged out. This calls Overview.pageAddIntroText()
// * Overview.getOverview() asks the API for information about the
//   player's overview status (currently, the lists of new, active, completed,
//   and cancelled games, and potentially the user's preferences).
//   It sets Api.new_games, Api.active_games, Api.completed_games,
//   Api.cancelled_games, and potentially Api.user_prefs. If successful, it
//   calls Overview.showPage().
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
    Api.getNewGamesData,
    Api.getActiveGamesData,
    Api.getCompletedGamesData,
    Api.getCancelledGamesData,
  ], callback);
};

Overview.showPage = function() {
  Overview.page = $('<div>');

  var nGamesAwaitingAction = Api.new_games.nGamesAwaitingAction +
    Api.active_games.nGamesAwaitingAction;
  var gameCountText='';
  if (nGamesAwaitingAction > 0) {
    gameCountText = '(' + nGamesAwaitingAction+ ') ';
  }
  $('title').html(gameCountText + 'Button Men Online');

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

  if ((Api.new_games.nGames === 0) &&
      (Api.active_games.nGames === 0) &&
      (Api.completed_games.nGames === 0) &&
      (Api.cancelled_games.nGames === 0)) {
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
  Overview.pageAddGameTable('closed', 'Closed games', false);
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

Overview.pageAddGameTable = function(
    gameType,
    sectionHeader,
    reverseSortOrder
  ) {
  var gamesource;
  var tableClass;
  var showDismiss = false;

  switch (gameType) {
  case 'closed':
    // closed games comprise completed games and cancelled games
    var gamesourceCompleted = Api.completed_games.games;
    Overview.addTypeToGameSource(gamesourceCompleted, 'completed');

    var gamesourceCancelled = Api.cancelled_games.games;
    Overview.addTypeToGameSource(gamesourceCancelled, 'cancelled');

    gamesource = gamesourceCompleted.concat(gamesourceCancelled);
    gamesource.sort(function(a, b) {
      return a.gameId - b.gameId;
    });

    tableClass = 'closedGames';
    showDismiss = true;
    break;
  default:
    // show both active and new games under 'Active' games
    var gamesourceActive = Api.active_games.games[gameType];
    Overview.addTypeToGameSource(gamesourceActive, 'active');

    var gamesourceNew = Api.new_games.games[gameType];
    Overview.addTypeToGameSource(gamesourceNew, 'new');

    gamesource = gamesourceActive.concat(gamesourceNew);
    gamesource.sort(function(a, b) {
      return b.inactivityRaw - a.inactivityRaw;
    });
    tableClass = 'activeGames';
  }

  if (gamesource.length === 0) {
    return;
  }

  if (reverseSortOrder === true) {
    gamesource.reverse();
  }

  Overview.addTableRows(
    Overview.addTableStructure(tableClass, sectionHeader, showDismiss),
    gamesource,
    gameType,
    showDismiss
  );
};

Overview.addTypeToGameSource = function(gamesource, gameType) {
  for (var gameIdx = 0; gameIdx < gamesource.length; gameIdx++) {
    gamesource[gameIdx].gameType = gameType;
  }
};

Overview.addTableStructure = function(tableClass, sectionHeader, showDismiss) {
  var tableBody = Overview.page.find('table.' + tableClass + ' tbody');

  if (tableBody.length === 0) {
    // create table
    var tableDiv = $('<div>');
    tableDiv.append($('<h2>', {'text': sectionHeader, }));
    var table = $('<table>', { 'class': 'gameList ' + tableClass, });
    tableDiv.append(table);
    Overview.page.append(tableDiv);

    // add table header
    var tableHead = $('<thead>');
    var headerRow = $('<tr>');
    headerRow.append($('<th>', {'text': 'Game #', }));
    headerRow.append($('<th>', {'html': 'Your Button', }));
    headerRow.append($('<th>', {'html': 'Opponent\'s Button', }));
    headerRow.append($('<th>', {'text': 'Opponent', }));
    headerRow.append( $('<th>', {'html':
      'Score<br/>W/L/T (Max)',
    }));
    headerRow.append($('<th>', {'text': 'Description', }));
    headerRow.append($('<th>', {'text': 'Inactivity', }));
    if (showDismiss) {
      headerRow.append($('<th>', {'text': 'Dismiss', }));
    }
    tableHead.append(headerRow);
    table.append(tableHead);

    // add table body
    tableBody = $('<tbody>');
    table.append(tableBody);
  } else {
    // add spacer row
    var spacerRow = $('<tr>', { 'class': 'spacer' });
    tableBody.append(spacerRow);
    spacerRow.append($('<td>', {
      'html': '&nbsp;',
      'colspan': tableBody.closest('table')
                          .children('thead')
                          .children('tr:first')
                          .children()
                          .length,
    }));
  }

  return tableBody;
};

Overview.addTableRows = function(tableBody, gamesource, gameType, showDismiss) {
  var gameInfo;
  var gameRow;

  for (var gameIdx = 0; gameIdx < gamesource.length; gameIdx++) {
    gameInfo = gamesource[gameIdx];

    gameRow = $('<tr>');
    Overview.addGameCol(gameRow, gameInfo, gameType);
    Overview.addButtonCol(gameRow, gameInfo.playerButtonName);
    Overview.addButtonCol(gameRow, gameInfo.opponentButtonName);
    Overview.addPlayerCol(
      gameRow,
      gameInfo.opponentName,
      gameInfo.isOpponentOnVacation,
      gameInfo.opponentColor
    );
    Overview.addScoreCol(gameRow, gameInfo);
    Overview.addDescCol(gameRow, gameInfo.gameDescription);
    Overview.addInactiveCol(gameRow, gameInfo.inactivity);
    if (showDismiss) {
      Overview.addDismissCol(gameRow, gameInfo);
    }

    tableBody.append(gameRow);
  }

  if (tableBody.children('tr.staleGame').length > 0) {
    var nCol = gameRow.children().length;
    tableBody.closest('table').append(Overview.staleGameFooter(nCol));
  }
};

Overview.addGameCol = function(gameRow, gameInfo, gameType) {
  var gameLinkTd;

  if (gameType == 'awaitingPlayer') {
    gameLinkTd = $('<td>', {
      'style': 'background-color: ' + gameInfo.playerColor,
    });
  } else if (gameType == 'awaitingOpponent') {
    if (gameInfo.inactivityRaw > Overview.STALENESS_SECS) {
      gameRow.addClass('staleGame');
      gameRow.hide();
    }

    gameLinkTd = $('<td>', {
      'style': 'background-color: ' + gameInfo.opponentColor,
    });
  } else {
    gameLinkTd = $('<td>');
  }

  gameLinkTd.append($('<a>', {
    'href': 'game.html?game=' + gameInfo.gameId,
    'text': Overview.linkTextStub(gameInfo, gameType) +
            ' Game ' + gameInfo.gameId,
  }));

  gameRow.append(gameLinkTd);
};

Overview.addButtonCol = function(gameRow, buttonName) {
  gameRow.append($('<td>').append(
    Env.buildButtonLink(buttonName)
  ));
};

Overview.addPlayerCol = function(gameRow, playerName,
                                 isPlayerOnVacation, playerColor) {
  gameRow.append($('<td>', {
      'style': 'background-color: ' + playerColor,
    }).append((isPlayerOnVacation) ? Env.buildVacationImage() : '')
      .append(Env.buildProfileLink(playerName)));
};

Overview.addScoreCol = function(gameRow, gameInfo) {
  var wldColor = '#ffffff';
  if (gameInfo.gameScoreDict.W > gameInfo.gameScoreDict.L) {
    wldColor = gameInfo.playerColor;
  } else if (gameInfo.gameScoreDict.W < gameInfo.gameScoreDict.L) {
    wldColor = gameInfo.opponentColor;
  }

  if ((gameInfo.gameType == 'cancelled') ||
      (gameInfo.gameType == 'new')) {
    gameRow.append($('<td>', {
      'text': '–/–/–' + ' (' + gameInfo.maxWins + ')',
    }));
  } else {
    gameRow.append($('<td>', {
      'text': gameInfo.gameScoreDict.W + '/' +
              gameInfo.gameScoreDict.L + '/' +
              gameInfo.gameScoreDict.D + ' (' + gameInfo.maxWins + ')',
      'style': 'background-color: ' + wldColor,
    }));
  }
};

Overview.addDescCol = function(gameRow, description) {
  var descText = '';
  if (typeof(description) == "string") {
    descText = description.substring(0, 30) +
               ((description.length > 30) ? '...' : '');
  }
  gameRow.append($('<td>', {
    'class': 'gameDescDisplay',
    'text': descText,
  }));
};

Overview.addInactiveCol = function(gameRow, inactivity) {
  gameRow.append($('<td>', { 'text': inactivity, }));
};

Overview.addDismissCol = function(gameRow, gameInfo) {
  var dismissTd = $('<td>');
  dismissTd.css('white-space', 'nowrap');
  gameRow.append(dismissTd);

  var dismissLink = $('<a>', {
    'text': 'Dismiss',
    'href': '#',
    'data-gameId': gameInfo.gameId,
  });
  dismissLink.click(Overview.formDismissGame);
  dismissTd.append('[')
           .append(dismissLink)
           .append(']');
};

Overview.linkTextStub = function(gameInfo, gameType) {
  if (gameInfo.gameType == 'new') {
    return 'NEW';
  } else if (gameInfo.gameType == 'cancelled') {
    return 'CANCELLED';
  } else if (gameInfo.gameType == 'completed') {
    if (gameInfo.gameScoreDict.W > gameInfo.gameScoreDict.L) {
      return 'WON';
    } else if (gameInfo.gameScoreDict.W < gameInfo.gameScoreDict.L) {
      return 'LOST';
    } else {
      return 'TIED';
    }
  } else if (gameType == 'awaitingPlayer') {
    return 'Play';
  } else if (gameType == 'awaitingOpponent') {
    return 'View';
  }
};

Overview.staleGameFooter = function(nCol) {
  var tableFoot = $('<tfoot>');
  var footRow = $('<tr>');
  var footCol = $('<td>', {'colspan': nCol,});
  var staleToggle = $('<a>', {
    'id': 'staleToggle',
    'href': 'javascript:Overview.toggleStaleGame();',
    'text': 'Show stale games',
  });

  footCol.append('[').append(staleToggle).append(']');
  footRow.append(footCol);
  tableFoot.append(footRow);

  return tableFoot;
};

Overview.toggleStaleGame = function() {
  $('.staleGame').toggle();
  $('#staleToggle').text(
    $('.staleGame').is(':visible') ?
    'Hide stale games' :
    'Show stale games'
  );
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
  }
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
