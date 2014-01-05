// namespace for this "module"
var Overview = {};

// We only need one game state for this module, so just reproduce the
// setting here rather than importing Game.js
Overview.GAME_STATE_END_GAME = 60;

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * Overview.showOverviewPage() is the landing function.  Always call
//   this first
// * Overview.getOverview() asks the API for information about the
//   player's overview status (currently, the list of active games).
//   It sets Api.active_games and Api.completed_games.  If successful,
//   it calls
// * Overview.showPage() assembles the page contents as a variable
// * Overview.layoutPage() sets the contents of <div id="overview_page">
//   on the live page
//
// N.B. There is no form submission on this page, it's just a landing
// page with links to other pages, so it's logically somewhat simpler
// than e.g. Game.js.
////////////////////////////////////////////////////////////////////////

Overview.showOverviewPage = function() {

  // Setup necessary elements for displaying status messages
  $.getScript('js/Env.js');
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#overview_page').length == 0) {
    $('body').append($('<div>', {'id': 'overview_page', }));
  }

  // Get all needed information, then display overview page
  Overview.getOverview(Overview.showPage);
};

Overview.getOverview = function(callback) {

  if (Login.logged_in) {

    Api.getActiveGamesData(function() {
      Api.getCompletedGamesData(callback);
    });
  } else {
    Env.message = {
      'type': 'none',
      'text': 'Please login to start beating people up',
    };
    return callback();
  }
};

Overview.showPage = function() {

  Overview.page = $('<div>');

  if (Login.logged_in == true) {
    Overview.pageAddNewgameLink();

    if ((Api.active_games.nGames == 0) && (Api.completed_games.nGames == 0)) {
      Env.message = {
        'type': 'none',
        'text': 'You have no games',
      };
    } else {
      Overview.pageAddGameTables();
    }
  }

  // Actually layout the page
  Overview.layoutPage();
};

Overview.layoutPage = function() {

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  $('#overview_page').empty();
  $('#overview_page').append(Overview.page);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

// Add tables for types of existing games
Overview.pageAddGameTables = function() {
  Overview.pageAddGameTable('awaitingPlayer', 'Games waiting for you');
  Overview.pageAddGameTable('awaitingOpponent', 'Games waiting for your opponent');
  Overview.pageAddGameTable('finished', 'Completed games');
};

Overview.pageAddNewgameLink = function() {
  var newgameDiv = $('<div>');
  var newgamePar = $('<p>');
  newgamePar.append($('<a>', {
    'href': 'create_game.html',
    'text': 'Create a new game',
  }));
  newgameDiv.append(newgamePar);
  Overview.page.append(newgameDiv);
};

Overview.pageAddGameTable = function(gameType, sectionHeader) {
  var gamesource;
  if (gameType == 'finished') {
    gamesource = Api.completed_games.games;
  } else {
    gamesource = Api.active_games.games[gameType];
  }

  if (gamesource.length == 0) {
     return;
  }
  var tableDiv = $('<div>');  
  tableDiv.append($('<h2>', {'text': sectionHeader, }));
  var table = $('<table>');
  headerRow = $('<tr>');
  headerRow.append($('<th>', {'text': 'Game #', }));
  headerRow.append($('<th>', {'text': 'Opponent', }));
  headerRow.append($('<th>', {'text': 'Your Button', }));
  headerRow.append($('<th>', {'text': "Opponent's Button", }));
  headerRow.append($('<th>', {'text': 'Score (W/L/T (Max))', }));
  table.append(headerRow);
  var i = 0;
  while (i < gamesource.length) {
    var gameInfo = gamesource[i];
    gameRow = $('<tr>');
    var gameLinkTd = $('<td>');
    gameLinkTd.append($('<a>', {'href': 'game.html?game=' + gameInfo.gameId,
                                'text': gameInfo.gameId,}));
    gameRow.append(gameLinkTd);
    gameRow.append($('<td>', {'text': gameInfo.opponentName, }));
    gameRow.append($('<td>', {'text': gameInfo.playerButtonName, }));
    gameRow.append($('<td>', {'text': gameInfo.opponentButtonName, }));
    gameRow.append($('<td>', {'text': gameInfo.gameScoreDict['W'] + '/' +
                                      gameInfo.gameScoreDict['L'] + '/' +
                                      gameInfo.gameScoreDict['D'] +
                                      ' (' + gameInfo.maxWins + ')', }));
    i += 1;
    table.append(gameRow);
  }

  tableDiv.append(table);
  tableDiv.append($('<hr>'));
  Overview.page.append(tableDiv);
};
