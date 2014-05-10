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

  // Get all needed information, then display History page
  History.getHistory(History.showPage);
};

History.getHistory = function(callback) {
  if (Login.logged_in) {
    Api.searchGameHistory(
      { 'playerNameA': Login.player, 'status': 'Completed', },
      callback
    );
  } else {
    return callback();
  }
};

History.showPage = function() {
  History.page = $('<div>');

  if (Login.logged_in === true) {
    var resultsTable = $('<table>');
    History.page.append(resultsTable);

    resultsTable.append(History.buildResultsTableHeader());
    resultsTable.append(History.buildResultsTableBody());
    resultsTable.append(History.buildResultsTableFooter());
  } else {
    Env.message = {
      'type': 'error',
      'text': 'You must be logged in in order to view game history.',
    };
  }

  // Actually layout the page
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

  $.each(Api.search_results.games, function(index, game) {
    var gameRow = $('<tr>');
    body.append(gameRow);

    var idTd = $('<td>');
    idTd.append($('<a>', {
      'href': 'game.html?game=' + game.gameId,
      'text': 'View Game ' + game.gameId,
    }));
    gameRow.append(idTd);

    gameRow.append($('<td>', { 'text': game.playerNameA, }));
    gameRow.append($('<td>', { 'text': game.buttonNameA, }));
    gameRow.append($('<td>', { 'text': game.playerNameB, }));
    gameRow.append($('<td>', { 'text': game.buttonNameB, }));
    gameRow.append($('<td>', {
      'text': Env.formatTimestamp(game.gameStart, 'date'),
    }));
    gameRow.append($('<td>', {
      'text': Env.formatTimestamp(game.lastMove, 'date'),
    }));

    var score = game.roundsWonA + '/' + game.roundsWonB + '/' +
      game.roundsDrawn + ' (' + game.targetWins + ')';
    gameRow.append($('<td>', { 'text': score, }));

    var status;
    if (game.status == 'COMPLETE') {
      status = 'Complete';
    } else {
      status = 'In Progress';
    }
    gameRow.append($('<td>', {
      'text': status,
      'style': 'font-style: italic;'
    }));
  });

  return body;

};

History.buildResultsTableFooter = function() {
  var foot = $('<tfoot>');

  //TODO generate footer stuff

  return foot;
};
