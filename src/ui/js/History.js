// namespace for this "module"
var History = {};

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * History.showHistoryPage() is the landing function.  Always call
//   this first
////////////////////////////////////////////////////////////////////////

History.showHistoryPage = function() {
  // Setup necessary elements for displaying status messages
  $.getScript('js/Env.js');
  Env.setupEnvStub();

  // Make sure the div element that we will need exists in the page body
  if ($('#history_page').length === 0) {
    $('body').append($('<div>', {'id': 'history_page', }));
  }

  // Make sure the user is logged in before trying to hit the API
  if (Login.logged_in !== true) {
    Env.message = {
      'type': 'error',
      'text': 'You must be logged in in order to view game history.',
    };
    History.page = $('<div>');
    History.layoutPage();
    return;
  }

  History.readSearchParametersFromUrl();
  if (History.searchParameters != undefined) {
    // Get all needed information, then display History page
    History.getHistory(History.showPage);
  } else {
    History.showPage();
  }
};

History.getHistory = function(callback) {
  if (Login.logged_in) {
    Api.searchGameHistory(
      History.searchParameters,
      callback
    );
  } else {
    return callback();
  }
};

History.readSearchParametersFromUrl = function() {
  if (Env.window.location.hash == '') {
    History.searchParameters = undefined;
    return;
  }

  History.searchParameters = { };
  History.readSingleSearchParameterFromUrl('gameId');
  History.readSingleSearchParameterFromUrl('playerNameA');
  History.readSingleSearchParameterFromUrl('buttonNameA');
  History.readSingleSearchParameterFromUrl('playerNameB');
  History.readSingleSearchParameterFromUrl('buttonNameB');
  History.readSingleSearchParameterFromUrl('gameStartMin');
  History.readSingleSearchParameterFromUrl('gameStartMax');
  History.readSingleSearchParameterFromUrl('lastMoveMin');
  History.readSingleSearchParameterFromUrl('lastMoveMax');
  History.readSingleSearchParameterFromUrl('winningPlayer');
  History.readSingleSearchParameterFromUrl('status');
};

History.readSingleSearchParameterFromUrl = function(parameterName) {
  var value = Env.getParameterByName(parameterName);
  if (value !== undefined && value !== null && value != '') {
    History.searchParameters[parameterName] = value;
  }
};

History.showPage = function() {
  History.page = $('<div>');

  if (Api.search_results !== undefined &&
    Api.search_results.load_status != 'ok') {
    // An error has occurred, and we've presumably already registered the
    // error message, so we should just display it.
    History.layoutPage();
    return;
  }

  History.page.append($('<h2>', { 'text': 'Game History', }));

  var resultsTable = $('<table>', { 'class': 'gameList', });
  History.page.append(resultsTable);

  // Display the column headers and search filters
  resultsTable.append(History.buildResultsTableHeader());

  if (Api.search_results !== undefined) {
    // List the games that were returned
    resultsTable.append(History.buildResultsTableBody());
    // Show summary data
    resultsTable.append(History.buildResultsTableFooter());
  }

  // Actually lay out the page
  History.layoutPage();
};

History.layoutPage = function() {
  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  $('#history_page').empty();
  $('#history_page').append(History.page);
};

History.buildResultsTableHeader = function() {
  var head = $('<thead>');
  var headerRow = $('<tr>');
  head.append(headerRow);

  headerRow.append($('<th>', { 'text': 'Game #' }));
  headerRow.append($('<th>', { 'text': 'Player A' }));
  headerRow.append($('<th>', { 'text': 'Button A' }));
  headerRow.append($('<th>', { 'text': 'Player B' }));
  headerRow.append($('<th>', { 'text': 'Button B' }));
  headerRow.append($('<th>', { 'text': 'Game Start' }));
  headerRow.append($('<th>', { 'text': 'Last Move' }));
  headerRow.append($('<th>', { 'text': 'Round Score' }));
  headerRow.append($('<th>', { 'text': 'Completed?' }));

  //TODO add a row for search filters

  return head;
};

History.buildResultsTableBody = function() {
  var body = $('<tbody>');

  if (Api.search_results === undefined ||
    Api.search_results.games === undefined)  {
    return body;
  }

  $.each(Api.search_results.games, function(index, game) {
    var gameRow = $('<tr>');
    body.append(gameRow);

    var winnerColor;
    if (game.roundsWonA > game.roundsWonB) {
      winnerColor = game.colorA;
    } else if (game.roundsWonB > game.roundsWonA) {
      winnerColor = game.colorB;
    } else {
      winnerColor = '#ffffff';
    }

    var idTd = $('<td>', {
      'style': 'background-color: ' + winnerColor + ';',
    });
    idTd.append($('<a>', {
      'href': 'game.html?game=' + game.gameId,
      'text': 'View Game ' + game.gameId,
    }));
    gameRow.append(idTd);

    gameRow.append($('<td>', {
      'text': game.playerNameA,
      'style': 'background-color: ' + game.colorA + ';',
    }));
    gameRow.append($('<td>', {
      'text': game.buttonNameA,
      'style': 'background-color: ' + game.colorA + ';',
    }));
    gameRow.append($('<td>', {
      'text': game.playerNameB,
      'style': 'background-color: ' + game.colorB + ';',
    }));
    gameRow.append($('<td>', {
      'text': game.buttonNameB,
      'style': 'background-color: ' + game.colorB + ';',
    }));
    gameRow.append($('<td>', {
      'text': Env.formatTimestamp(game.gameStart, 'date'),
    }));
    gameRow.append($('<td>', {
      'text': Env.formatTimestamp(game.lastMove, 'date'),
    }));

    var score = game.roundsWonA + '/' + game.roundsWonB + '/' +
      game.roundsDrawn + ' (' + game.targetWins + ')';
    gameRow.append($('<td>', {
      'text': score,
      'style': 'background-color: ' + winnerColor + ';',
    }));

    var status;
    var statusColor;
    if (game.status == 'COMPLETE') {
      status = 'Complete';
      statusColor = winnerColor;
    } else {
      status = 'In Progress';
      statusColor = '#ffffff'
    }
    gameRow.append($('<td>', {
      'text': status,
      'style': 'font-style: italic;',
      'style': 'background-color: ' + statusColor + ';',
    }));
  });

  return body;
};

History.buildResultsTableFooter = function() {
  var foot = $('<tfoot>');

  if (Api.search_results === undefined ||
    Api.search_results.summary === undefined)  {
    return foot;
  }

  var footerHeaderRow = $('<tr>');
  foot.append(footerHeaderRow);

  footerHeaderRow.append($('<th>', { 'text': 'Matches Found' }));
  footerHeaderRow.append($('<th>', { 'colspan': '4' }));
  footerHeaderRow.append($('<th>', { 'text': 'Earliest Start' }));
  footerHeaderRow.append($('<th>', { 'text': 'Latest Move' }));
  footerHeaderRow.append($('<th>', { 'text': 'Games W/L/T' }));
  footerHeaderRow.append($('<th>', { 'text': '% Completed' }));

  var footerDataRow = $('<tr>');
  foot.append(footerDataRow);

  var summary = Api.search_results.summary;

  footerDataRow.append($('<td>', { 'text': summary.matchesFound }));
  footerDataRow.append($('<td>', { 'colspan': '4' }));
  footerDataRow.append($('<td>', { 'text':
      Env.formatTimestamp(summary.earliestStart, 'date')
  }));
  footerDataRow.append($('<td>', { 'text':
      Env.formatTimestamp(summary.latestMove, 'date')
  }));

  var scores =
      summary.gamesWinningA + '/' + summary.gamesWinningB + '/' +
      summary.gamesDrawn;
  footerDataRow.append($('<td>', { 'text': scores }));

  var percentCompleted = '';
  if (summary.matchesFound > 0) {
    percentCompleted = (summary.gamesCompleted * 100) / summary.matchesFound;
    percentCompleted = Math.round(percentCompleted) + '%';
  }
  footerDataRow.append($('<td>', { 'text': percentCompleted }));

  return foot;
};
