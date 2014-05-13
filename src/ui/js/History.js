// namespace for this "module"
var History = {};

// The parameters that we collect and pass to the API
History.searchParameterNames = {
  'gameId': 'number',
  'playerNameA': 'string',
  'buttonNameA': 'string',
  'playerNameB': 'string',
  'buttonNameB': 'string',
  'gameStartMin': 'date',
  'gameStartMax': 'date',
  'lastMoveMin': 'date',
  'lastMoveMax': 'date',
  'winningPlayer': 'string',
  'status': 'string',
  'sortColumn': 'hidden',
  'sortDirection': 'hidden',
  'numberOfResults': 'number',
  'page': 'hidden',
};

////////////////////////////////////////////////////////////////////////
// Action flow through this page:
// * History.showHistoryPage() is the landing function.  Always call
//   this first. On the initial page load, it sets History.searchParameters
//   based on the hashbang of the incoming URL, then calls History.getHistory().
//   It is also called by the windows's popstate event when the user clicks
//   the back button (to refresh the page from the new URL values).
// * History.getHistory() gets the results for the current search
//   parameters from the API. It sets Api.search_results and calls
//   History.getFilters()
// * History.getFilters() gets data from the API to populate the filters
//   with. It sets Api.player and Api.button, then calls History.showPage()
// * History.showPage() builds the contents of the page as History.page
//   and calls History.layoutPage()
// * History.layoutPage() sets the contents of <div id="history_page">
//   on the live page
//
// * History.performManualSearch() is called whenever the search button, a
//   sort button or a paging link is clicked. It sets History.searchParameters
//   based on the form inputs, writes those parameters into the hashbang URL,
//   and calls History.getHistory()
////////////////////////////////////////////////////////////////////////

History.showHistoryPage = function() {
  // Setup necessary elements for displaying status messages
  if ($('#env_message').length == 0) {
    Env.setupEnvStub();
  }

  // When the user hits the back button to retrace their path through the
  // hashbang URL's, load the search results that belong to that "page"
  $(window).bind('popstate', function() {
    Env.message = null;
    History.showHistoryPage();
  });

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

History.getHistory = function(callback) {
  if (Login.logged_in) {
    $('#searchButton').attr('disabled', 'disabled');

    if (History.searchParameters.sortColumn === undefined) {
      History.searchParameters.sortColumn = 'lastMove';
    }
    if (History.searchParameters.sortDirection === undefined) {
      History.searchParameters.sortDirection = 'DESC';
    }
    if (History.searchParameters.numberOfResults === undefined) {
      History.searchParameters.numberOfResults = 20;
    }
    if (History.searchParameters.page === undefined) {
      History.searchParameters.page = 1;
    }

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

  History.page.append(History.buildSearchButtonDiv());
  History.page.append($('<h2>', { 'text': 'Game History', }));
  History.page.append(History.buildHiddenFields());

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
};

////////////////////////////////////////////////////////////////////////
// Helper routines to read and write the search parameter values

History.readSearchParametersFromUrl = function() {
  if (Env.window.location.hash === '') {
    History.searchParameters = undefined;
    return;
  }

  History.searchParameters = { };
  $.each(History.searchParameterNames, function(name) {
    var value = Env.getParameterByName(name);
    if (value !== undefined && value !== null && value !== '') {
      History.searchParameters[name] = value;
    }
  });
};

History.readSearchParametersFromForm = function() {
  History.searchParameters = { };

  $.each(History.searchParameterNames, function(name, type) {
    // If we've already encountered one validation error, skip the rest
    if (History.searchParameters === undefined) {
      return;
    }

    var value = History.page.find('#parameter_' + name).val();
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
  $.each(History.searchParameterNames, function(name) {
    if (History.searchParameters[name] !== undefined) {
      parameterString +=
        name + '=' + encodeURIComponent(History.searchParameters[name]) + '&';
    }
  });

  // Trim off the trailing &
  parameterString = parameterString.replace(/&$/, '');
  Env.window.location.hash = parameterString;
};

////////////////////////////////////////////////////////////////////////
// Helper routines to generate HTML to populate the page

History.buildSearchButtonDiv = function() {
  var buttonDiv = $('<div>');

  buttonDiv.append($('<text>', { 'text': 'Display up to ', }));
  var pageSizeSelect = $('<select>', { 'id': 'parameter_numberOfResults', });
  buttonDiv.append(pageSizeSelect);
  pageSizeSelect.append($('<option>', { 'value': 10, 'text': '10', }));
  pageSizeSelect.append($('<option>', { 'value': 20, 'text': '20', }));
  pageSizeSelect.append($('<option>', { 'value': 50, 'text': '50', }));
  pageSizeSelect.append($('<option>', { 'value': 100, 'text': '100', }));
  if (History.searchParameters === undefined ||
    History.searchParameters.numberOfResults === undefined) {
    pageSizeSelect.val(20);
  } else {
    pageSizeSelect.val(History.searchParameters.numberOfResults);
  }
  buttonDiv.append($('<text>', { 'text': ' games per page: ', }));

  var searchButton = $('<input>', {
    'type': 'button',
    'id': 'searchButton',
    'value': 'Search',
  });
  buttonDiv.append(searchButton);
  searchButton.click(function() {
    History.page.find('#parameter_page').val(1);
    History.performManualSearch();
  });

  return buttonDiv;
};

History.buildHiddenFields = function() {
  var hiddenDiv = $('<div>', { 'style': 'display: none; ' });

  $.each(History.searchParameterNames, function(name, type) {
    if (type == 'hidden') {
      var hiddenInput = $('<input>', {
        'type': 'hidden',
        'id': 'parameter_' + name,
      });
      hiddenDiv.append(hiddenInput);

      if (History.searchParameters !== undefined &&
        History.searchParameters[name] !== undefined) {
        hiddenInput.val(History.searchParameters[name]);
      }
    }
  });

  return hiddenDiv;
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
  $.each(Api.button.list, function(name) {
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
    var titleTh = $('<th>');
    headerRow.append(titleTh);

    var sortButtonColor;
    if (History.searchParameters !== undefined &&
      History.searchParameters.sortColumn !== undefined &&
      History.searchParameters.sortDirection !== undefined &&
      History.searchParameters.sortColumn == columnId &&
      History.searchParameters.sortDirection == 'ASC') {
      sortButtonColor = '#ffffff';
    } else {
      sortButtonColor = '#000000';
    }
    titleTh.append($('<span>', {
      'html': '&#9650',
      'href': 'javascript: void(0);',
      'data-column': columnId,
      'data-direction': 'ASC',
      'class': 'sortButton',
      'style': 'color: ' + sortButtonColor + ';',
    }));

    titleTh.append($('<span>', { 'text': columnInfo.text }));

    if (History.searchParameters !== undefined &&
      History.searchParameters.sortColumn !== undefined &&
      History.searchParameters.sortDirection !== undefined &&
      History.searchParameters.sortColumn == columnId &&
      History.searchParameters.sortDirection == 'DESC') {
      sortButtonColor = '#ffffff';
    } else {
      sortButtonColor = '#000000';
    }
    titleTh.append($('<span>', {
      'html': '&#9660;',
      'href': 'javascript: void(0);',
      'data-column': columnId,
      'data-direction': 'DESC',
      'class': 'sortButton',
      'style': 'color: ' + sortButtonColor + ';',
    }));

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

  head.find('.sortButton').click(function() {
    $('#parameter_sortColumn').val($(this).attr('data-column'));
    $('#parameter_sortDirection').val($(this).attr('data-direction'));
    History.performManualSearch();
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
  footerHeaderRow.append($('<th>', {
    'text': 'Pages',
    'colspan': '4',
    'style': 'text-align: left;',
  }));
  footerHeaderRow.append($('<th>', { 'text': 'Earliest Start' }));
  footerHeaderRow.append($('<th>', { 'text': 'Latest Move' }));
  footerHeaderRow.append($('<th>', { 'text': 'Games W/L/T' }));
  footerHeaderRow.append($('<th>', { 'text': '% Completed' }));

  var footerDataRow = $('<tr>');
  foot.append(footerDataRow);

  var summary = Api.search_results.summary;

  footerDataRow.append($('<td>', { 'text': summary.matchesFound }));

  var pagingTd = $('<td>', {
    'colspan': '4',
    'style': 'text-align: left;',
  });
  var lastPage =
    Math.ceil(summary.matchesFound / History.searchParameters.numberOfResults);

  if (History.searchParameters.page > 1) {
    pagingTd.append($('<span>', {
      'html': '&larr;',
      'class': 'pageLink',
      'style': 'width: 1.2em;',
      'data-page': parseInt(History.searchParameters.page, 10) - 1,
    }));
  } else {
    // We'll add a blank pager so that the paging links stay lined up right
    // whether we're on page one or not
    pagingTd.append($('<span>', {
      'class': 'pageLink',
      'style': 'width: 1.2em;',
      'data-page': History.searchParameters.page,
    }));
  }

  for (var i = 1; i <= lastPage; i++) {
    if (i == History.searchParameters.page) {
      pagingTd.append($('<span>', {
        'text': '[' + i + ']',
        'class': 'pageLink',
        'data-page': i,
        'style': 'font-weight: bold; cursor: default;'
      }));
    } else {
      pagingTd.append($('<span>', {
        'text': i,
        'class': 'pageLink',
        'data-page': i,
      }));
    }
  }

  if (History.searchParameters.page < lastPage) {
    pagingTd.append($('<span>', {
      'html': '&rarr;',
      'class': 'pageLink',
      'style': 'width: 1.2em;',
      'data-page': parseInt(History.searchParameters.page, 10) + 1,
    }));
  }

  pagingTd.find('.pageLink').click(function() {
    var page = $(this).attr('data-page');
    if (page != History.searchParameters.page) {
      $('#parameter_page').val(page);
      History.performManualSearch();
    }
  });
  footerDataRow.append(pagingTd);

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
