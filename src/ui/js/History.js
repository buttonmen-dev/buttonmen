// namespace for this "module"
var History = {};

// The parameters that we collect and pass to the API
History.searchParameterNames = {
  'gameId': 'number',
  'playerNameA': 'string',
  'buttonNameA': 'string',
  'playerNameB': 'string',
  'buttonNameB': 'string',
  'playerIdA': 'date',
  'gameStartMin': 'date',
  'gameStartMax': 'date',
  'lastMoveMin': 'date',
  'lastMoveMax': 'date',
  'winningPlayer': 'string',
  'status': 'string',
};

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
  if (History.searchParameters !== undefined) {
    // Get all needed information, then display History page
    History.getHistory(function() {
      History.getFilters(History.showPage);
    });
  } else {
    History.getFilters(History.showPage);
  }
};

History.readSearchParametersFromUrl = function() {
  if (Env.window.location.hash === '') {
    History.searchParameters = undefined;
    return;
  }

  History.searchParameters = { };
  $.each(History.searchParameterNames, function(name, type) {
    var value = Env.getParameterByName(name);
    if (value !== undefined && value !== null && value !== '') {
      History.searchParameters[name] = value;
    }
  });
};

History.getHistory = function(callback) {
  if (Login.logged_in) {
    $('#searchButton').attr('disabled', 'disabled');
    Api.searchGameHistory(
      History.searchParameters,
      callback
    );
  } else {
    return callback();
  }
};

History.getFilters = function(callback) {
    Api.getButtonData(function() {
      Api.getPlayerData(callback);
    });
}

History.showPage = function() {
  History.page = $('<div>');

  if (Api.search_results !== undefined &&
    Api.search_results.load_status != 'ok') {
    // An error has occurred, and we've presumably already registered the
    // error message, so we should just display it.
    History.layoutPage();
    return;
  }

  var buttonDiv = $('<div>');
  History.page.append(buttonDiv);
  var searchButton = $('<input>', {
    'type': 'button',
    'id': 'searchButton',
    'value': 'Search',
  });
  buttonDiv.append(searchButton);
  searchButton.click(History.performManualSearch);

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

History.performManualSearch = function() {
  History.readSearchParametersFromForm();
  if (History.searchParameters === undefined) {
    // A validation error must have occurred
    Env.showStatusMessage();
    return;
  }

  // We want a shareable URL that points to this particular set of search
  // results
  History.writeSearchParametersToUrl();

  History.getHistory(function() {
      History.getFilters(History.showPage);
    });
}

History.readSearchParametersFromForm = function() {
  History.searchParameters = { };

  $.each(History.searchParameterNames, function(name, type) {
    // If we've already encountered one validation error, skip the rest
    if (History.searchParameters === undefined) {
      return;
    }

    var value = $('#parameter_' + name).val();
    if (value === undefined || value === null || value === '') {
      return;
    }

    if (type == 'number') {
      if (!value.match(/^\d+$/)) {
        Env.message = {
          'type': 'error',
          'text': name + ' must be a number.',
        };
        History.searchParameters = undefined;
        return;
      }
    } else if (type == 'date') {
      value = Env.parseDateTime(value);
      if (value === null) {
        Env.message = {
          'type': 'error',
          'text': name + ' is not a valid date.',
        };
        History.searchParameters = undefined;
        return;
      }
    }
    History.searchParameters[name] = value;
  });
};

History.writeSearchParametersToUrl = function() {
  var parameterString = '#!';
  $.each(History.searchParameterNames, function(name, type) {
    if (History.searchParameters[name] !== undefined) {
      parameterString +=
        name + '=' + encodeURIComponent(History.searchParameters[name]) + '&';
    }
  });

  // Trim off the trailing &
  parameterString = parameterString.replace(/&$/, "");
  Env.window.location.hash = parameterString;
};

History.buildResultsTableHeader = function() {
  var head = $('<thead>');

  var headerRow = $('<tr>');
  head.append(headerRow);
  var filterRow = $('<tr>');
  head.append(filterRow);

  var playerValues = { };
  $.each(Api.player.list, function(name, playerInfo) {
    if (playerInfo.status == 'active') {
      playerValues[name] = name;
    }
  });

  var buttonValues = { };
  $.each(Api.button.list, function(name, buttonInfo) {
    buttonValues[name] = name;
  });

  var winningPlayerValues = {
    'A': 'A Winning',
    'B': 'B Winning',
    'Tie': 'Tie',
  };

  var statusValues = {
    'COMPLETE': 'Completed',
    'ACTIVE': 'In Progress',
  };

  var columns = {
    'gameId': {
      'text': 'Game #',
      'type': 'text',
    },
    'playerNameA': {
      'text': 'Player A',
      'type': 'select',
      'source': playerValues,
    },
    'buttonNameA': {
      'text': 'Button A',
      'type': 'select',
      'source': buttonValues,
    },
    'playerNameB': {
      'text': 'Player B',
      'type': 'select',
      'source': playerValues,
    },
    'buttonNameB': {
      'text': 'Button B',
      'type': 'select',
      'source': buttonValues,
    },
    'gameStart': {
      'text': 'Game Start',
      'type': 'dateRange',
    },
    'lastMove': {
      'text': 'Last Move',
      'type': 'dateRange',
    },
    'winningPlayer': {
      'text': 'Round Score',
      'type': 'select',
      'source': winningPlayerValues,
    },
    'status': {
      'text': 'Completed?',
      'type': 'select',
      'source': statusValues,
    },
  };

  $.each(columns, function(columnId, columnInfo) {
    headerRow.append($('<th>', { 'text': columnInfo.text }));
    var filterTd = $('<th>');
    filterRow.append(filterTd);

    switch (columnInfo.type) {
    case 'select':
      var selectList = $('<select>', {
        'name': columnId,
        'id': 'parameter_' + columnId,
      });
      filterTd.append(selectList);

      selectList.append($('<option>', {
        'value': '',
        'text': '',
      }));

      $.each(columnInfo.source, function (value, name) {
        selectList.append($('<option>', {
          'value': value,
          'text': name,
        }));
      });

      if (History.searchParameters !== undefined) {
        selectList.val(History.searchParameters[columnId]);
      }
      break;
    case 'dateRange':
      var minElement = $('<input>', {
        'type': 'text',
        'name': columnId + 'Min',
        'id': 'parameter_' + columnId + 'Min',
        'maxlength': '10',
        'class': 'dateInput',
      });
      filterTd.append($('<div>').append(minElement));
      if (History.searchParameters !== undefined) {
        var minDate =
          Env.formatTimestamp(History.searchParameters[columnId + 'Min'],
            'date');
        minElement.val(minDate);
      }
      var maxElement = $('<input>', {
        'type': 'text',
        'name': columnId + 'Max',
        'id': 'parameter_' + columnId + 'Max',
        'maxlength': '10',
        'class': 'dateInput',
      });
      filterTd.append($('<div>').append(maxElement));
      if (History.searchParameters !== undefined) {
        var maxDate =
          Env.formatTimestamp(History.searchParameters[columnId + 'Max'],
            'date');
        maxElement.val(maxDate);
      }

      //TODO add calendar icons
      //  also, attach datepicker
      break;
    default:
      var inputElement = $('<input>', {
        'type': columnInfo.type,
        'name': columnId,
        'id': 'parameter_' + columnId,
      });
      filterTd.append(inputElement);
      if (History.searchParameters !== undefined) {
        inputElement.val(History.searchParameters[columnId]);
      }
    }
  });

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
      statusColor = '#ffffff';
    }
    gameRow.append($('<td>', {
      'text': status,
      'style': 'font-style: italic; background-color: ' + statusColor + ';'
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

History.layoutPage = function() {
  // If there is a message from a current or previous invocation of this
  // page, display it now
  Env.showStatusMessage();

  $('#history_page').empty();
  $('#history_page').append(History.page);
};
