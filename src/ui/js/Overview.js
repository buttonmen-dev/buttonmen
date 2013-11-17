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
//   It clobbers Overview.api.  If successful, it calls
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

  // Find the current game, and invoke that with the "parse game state"
  // callback
  Overview.getOverview(Overview.showPage);
}

Overview.getOverview = function(callbackfunc) {
  Overview.api = {
    'load_status': 'failed',
  }

  if (Login.player == null) {
    Env.message = {
      'type': 'none',
      'text': 'Nothing to display - you are not logged in',
    };
    return callbackfunc();
  }
    
  $.post('../api/responder.php',
         { type: 'loadActiveGames', },
         function(rs) {
           if (rs.message == 'All game details retrieved successfully.') {
             if (Overview.parseActiveGames(rs)) {
               Overview.api.load_status = 'ok';
             } else if (Overview.api.load_status == 'nogames') {
               Env.message = {
                 'type': 'none',
                 'text': 'You have no active games',
               };
             } else {
               Env.message = {
                 'type': 'error',
                 'text':
                   'Active game list received from server could not be parsed!',
               };
             }
           } else {
             Env.message = {
               'type': 'error',
               'text': 'Something is wrong with game list from server',
             };
           }
           return callbackfunc();
         }
  ).fail(function() {
    Env.message = {
      'type': 'error',
      'text': 'Internal error when looking up game list',
    };
    return callbackfunc();
  });
}

Overview.parseActiveGames = function(rs) {
  if (rs.gameIdArray == null) {
    Overview.api.load_status = 'nogames';
    return false;
  }
          
  Overview.api.games = {
    'awaitingPlayer': [],
    'awaitingOpponent': [],
    'finished': [],
  };
  Overview.api.nGames = rs.gameIdArray.length;
  i = 0;
  while (i < Overview.api.nGames) {
    var gameInfo = {
      'gameId': rs.gameIdArray[i],
      'opponentId': rs.opponentIdArray[i],
      'opponentName': rs.opponentNameArray[i],
      'playerButtonName': rs.myButtonNameArray[i],
      'opponentButtonName': rs.opponentButtonNameArray[i],
      'gameScoreDict': {
        'W': rs.nWinsArray[i],
        'L': rs.nLossesArray[i],
        'D': rs.nDrawsArray[i],
      },
      'isAwaitingAction': rs.isAwaitingActionArray[i],
      'gameState': rs.gameStateArray[i],
      'status': rs.statusArray[i],
    };
    if (gameInfo.isAwaitingAction == "1") {
      Overview.api.games['awaitingPlayer'].push(gameInfo);
    } else {
      if (gameInfo.gameState == Overview.GAME_STATE_END_GAME) {
        Overview.api.games['finished'].push(gameInfo);
      } else {
        Overview.api.games['awaitingOpponent'].push(gameInfo);
      }
    }
    i += 1;
  }
  return true;
}

Overview.showPage = function() {

  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  Overview.page = $('<div>');

  if (Login.logged_in == true) {
    Overview.addNewgameLink();

    if (Overview.api.load_status == 'ok') {
      Overview.addGameTables();
    }
  }

  // Actually layout the page
  Overview.layoutPage();
}

Overview.layoutPage = function() {
  $('#overview_page').empty();
  $('#overview_page').append(Overview.page);
}

////////////////////////////////////////////////////////////////////////
// Helper routines to add HTML entities to existing pages

// Add tables for types of existing games
Overview.addGameTables = function() {
  Overview.addGameTable('awaitingPlayer', 'Games waiting for you');
  Overview.addGameTable('awaitingOpponent', 'Games waiting for your opponent');
  Overview.addGameTable('finished', 'Completed games');
}

Overview.addNewgameLink = function() {
  var newgameDiv = $('<div>');
  var newgamePar = $('<p>');
  newgamePar.append($('<a>', {
    'href': 'create_game.html',
    'text': 'Create a new game',
  }));
  newgameDiv.append(newgamePar);
  Overview.page.append(newgameDiv);
}

Overview.addGameTable = function(gameType, sectionHeader) {
  if (Overview.api.games[gameType].length == 0) {
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
  headerRow.append($('<th>', {'text': 'Score (W/L/T)', }));
  table.append(headerRow);
  var i = 0;
  while (i < Overview.api.games[gameType].length) {
    var gameInfo = Overview.api.games[gameType][i];
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
                                      gameInfo.gameScoreDict['D'], }));
    i += 1;
    table.append(gameRow);
  }

  tableDiv.append(table);
  tableDiv.append($('<hr>'));
  Overview.page.append(tableDiv);
}
