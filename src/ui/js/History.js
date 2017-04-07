// namespace for this "module"
var History = {};

History.bodyDivId = 'history_page';

// These are the parameters that we need to pass to the API. Having this
// information about them here helps to read them from the page, write them to
// the URL, build the table columns for them, etc.
History.searchParameterInfo = {
  'gameId': {
    'text': 'Game #',
    'inputType': 'text',
    'dataType': 'number',
    'toolTip': 'Search or sort by the game number',
  },
  'playerNameA': {
    'text': 'Player A',
    'inputType': 'select',
    'source': { },
    'dataType': 'string',
    'toolTip': 'Search or sort by one of the players in the game',
  },
  'buttonNameA': {
    'text': 'Button A',
    'inputType': 'select',
    'source': { },
    'dataType': 'string',
    'toolTip':
      'Search or sort by the button used by one of the players in the game',
  },
  'buttonNameB': {
    'text': 'Button B',
    'inputType': 'select',
    'source': { },
    'dataType': 'string',
    'toolTip': 'Search or sort by the button used by Player A\'s opponent',
  },
  'playerNameB': {
    'text': 'Player B',
    'inputType': 'select',
    'source': { },
    'dataType': 'string',
    'toolTip': 'Search or sort by Player A\'s opponent',
  },
  'gameStart': {
    'text': 'Game Start',
    'inputType': 'dateRange',
    'dataType': 'date',
    'toolTip': 'Search or sort by the date the game started',
  },
  'lastMove': {
    'text': 'Last Move',
    'inputType': 'dateRange',
    'dataType': 'date',
    'toolTip':
      'Search or sort by the date the most recent move was made in the game ' +
      '(for completed games, this is when the game ended)',
  },
  'winningPlayer': {
    'text': 'Round Score',
    'inputType': 'select',
    'source': {
      'A': 'A Winning',
      'B': 'B Winning',
      'Tie': 'Tie',
    },
    'dataType': 'string',
    'toolTip':
      'Search or sort by which player is closer to winning the game ' +
      '(for completed games, this is the player who won)',
  },
  'status': {
    'text': 'Game status',
    'inputType': 'select',
    'source': {
      'COMPLETE': 'Completed',
      'ACTIVE': 'In Progress',
      'CANCELLED': 'Cancelled',
    },
    'dataType': 'string',
    'toolTip': 'Search or sort by the game status',
  },
  'sortColumn': {
    'inputType': 'hidden',
    'dataType': 'string',
    'defaultValue': 'lastMove',
  },
  'sortDirection': {
    'inputType': 'hidden',
    'dataType': 'string',
    'defaultValue': 'DESC',
  },
  'numberOfResults': {
    'inputType': 'special',
    'dataType': 'number',
    'source': {
      10: '10',
      20: '20',
      50: '50',
      100: '100'
    },
    'defaultValue': 20,
  },
  'page': {
    'inputType': 'hidden',
    'dataType': 'number',
    'defaultValue': 1,
  },
};

////////////////////////////////////////////////////////////////////////
// Primary flow through this page:
// * History.showLoggedInPage() is the landing function. Always call
//   this first. On the initial page load, it sets History.searchParameters
//   based on the hashbang of the incoming URL, then calls History.getHistory().
// * History.getFilters() gets data from the API to populate the filters
//   with. It sets Api.player and Api.button, then calls History.getHistory()
// * History.getHistory() calls the API, passing it the History.searchParameters
//   collection and causing Api.game_history to be set. It then calls
//   History.showPage()
// * History.showPage() uses the data returned by the API to build the contents
//   of the page as History.page and calls Login.arrangePage()
//
// Events:
// * History.performManualSearch() is called whenever the search button, a
//   sorting arrow or a paging link is clicked. It sets History.searchParameters
//   based on the form inputs, writes those parameters into the page history
//   and the hashbang URL, and then calls History.getHistory()
// * History.performAutomaticSearch() is called whenever the user clicks on the
//   forward or back button. It sets History.searchParameters based on the page
//   history state associated with the new URL and calls History.getHistory()
////////////////////////////////////////////////////////////////////////

History.showLoggedInPage = function() {
  // When the user hits the back button to retrace their path through the
  // hashbang URL's, load the search results that belong to that "page"
  $(window).bind('popstate', History.performAutomaticSearch);

  History.readSearchParametersFromUrl();
  if (History.searchParameters !== undefined) {
    // Since we just entered the page, we should set up a history state for
    // this URL, in case the user presses the back button
    Env.history.replaceState(History.searchParameters,
        'Button Men Online &mdash; History', Env.window.location.hash);

    // Get all needed information, then display History page
    History.getFilters(function() {
      History.getHistory(History.showPage);
    });
  } else {
    History.getFilters(History.showPage);
  }
};

History.getFilters = function(callback) {
  Env.callAsyncInParallel(
    [
      Api.getPlayerData,
      { 'func': Api.getButtonData, 'args': [ null ] },
    ], function() {
      var playerValues = { };
      $.each(Api.player.list, function(name, playerInfo) {
        if (playerInfo.status == 'ACTIVE') {
          playerValues[name] = name;
        }
      });
      History.searchParameterInfo.playerNameA.source = playerValues;
      History.searchParameterInfo.playerNameB.source = playerValues;

      var buttonValues = { };
      $.each(Api.button.list, function(name) {
        buttonValues[name] = name;
      });
      History.searchParameterInfo.buttonNameA.source = buttonValues;
      History.searchParameterInfo.buttonNameB.source = buttonValues;

      callback();
    });
};

History.getHistory = function(callback) {
  // Validate the search parameters that are supposed to be derived from set
  // lists of values (like player names).
  var validationError = '';
  $.each(History.searchParameterInfo, function(name, info) {
    if (History.searchParameters[name] !== undefined &&
      info.source !== undefined) {
      if (info.source[History.searchParameters[name]] === undefined) {
        validationError += name + ' is not recognized. ';
      }
    }
  });

  if (validationError) {
    Env.message = {
      'type': 'error',
      'text': validationError,
    };
    History.page = $('<div>');
    Login.arrangePage(History.page);
    return;
  }

  $('#searchButton').attr('disabled', 'disabled');

  // For the required fields, set the default values
  $.each(History.searchParameterInfo, function(name, info) {
    if (info.defaultValue !== undefined &&
      History.searchParameters[name] === undefined) {
      History.searchParameters[name] = info.defaultValue;
    }
  });

  Api.searchGameHistory(
    History.searchParameters,
    callback
  );
};

History.showPage = function() {
  History.page = $('<div>');

  if (Api.game_history !== undefined &&
    Api.game_history.load_status != 'ok') {
    // An error has occurred, and we've presumably already registered the
    // error message, so we should just display it.
    Login.arrangePage(History.page);
    return;
  }

  History.page.append(History.buildSearchButtonDiv());
  History.page.append($('<h2>', { 'text': 'Game History', }));
  History.page.append(History.buildHiddenFields());

  var resultsTable = $('<table>', { 'class': 'gameList', });
  History.page.append(resultsTable);

  // Display the column headers and search filters
  resultsTable.append(History.buildResultsTableHeader());

  if (Api.game_history !== undefined) {
    // List the games that were returned
    resultsTable.append(History.buildResultsTableBody());
    // Show summary data
    resultsTable.append(History.buildResultsTableFooter());
  }

  // Actually lay out the page
  Login.arrangePage(History.page);
};

History.performManualSearch = function() {
  History.readSearchParametersFromForm();
  if (History.searchParameters === undefined) {
    // A validation error must have occurred
    Env.showStatusMessage();
    return;
  }

  // We want a shareable URL that points to this particular set of search
  // results.
  History.writeSearchParametersToUrl();

  // Get all needed information, then display History page
  History.getFilters(function() {
    History.getHistory(History.showPage);
  });
};

History.performAutomaticSearch = function() {
  History.searchParameters = Env.history.state;
  if (History.searchParameters === undefined) {
    History.getFilters(History.showPage);
  }

  // Get all needed information, then display History page
  History.getFilters(function() {
    History.getHistory(History.showPage);
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
  $.each(History.searchParameterInfo, function(name, info) {
    if (info.inputType == 'dateRange') {
      var minValue = Env.getParameterByName(name + 'Min');
      History.assignSearchParameter(name + 'Min', info.dataType, minValue);
      var maxValue = Env.getParameterByName(name + 'Max');
      History.assignSearchParameter(name + 'Max', info.dataType, maxValue);
    } else {
      var value = Env.getParameterByName(name);
      History.assignSearchParameter(name, info.dataType, value);
    }
  });
};

History.readSearchParametersFromForm = function() {
  History.searchParameters = { };

  $.each(History.searchParameterInfo, function(name, info) {
    if (info.inputType == 'dateRange') {
      var minValue = History.page.find('#parameter_' + name + 'Min').val();
      History.assignSearchParameter(name + 'Min', info.dataType, minValue);
      var maxValue = History.page.find('#parameter_' + name + 'Max').val();
      History.assignSearchParameter(name + 'Max', info.dataType, maxValue);
    } else {
      var value = History.page.find('#parameter_' + name).val();
      History.assignSearchParameter(name, info.dataType, value);
    }
  });
};

History.assignSearchParameter = function(name, type, value) {
  // If we've already encountered one validation error, skip the rest
  if (History.searchParameters === undefined) {
    return;
  }

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
    value = Env.parseDateTime(value, 'date');
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
};

History.writeSearchParametersToUrl = function() {
  var parameterHash = '#!';
  $.each(History.searchParameterInfo, function(name, info) {
    var value;
    if (info.dataType == 'date') {
      if (History.searchParameters[name + 'Min'] !== undefined) {
        value = History.searchParameters[name + 'Min'];
        value = Env.formatTimestamp(value, 'date');
        parameterHash += name + 'Min' + '=' + encodeURIComponent(value) + '&';
      }
      if (History.searchParameters[name + 'Max'] !== undefined) {
        value = History.searchParameters[name + 'Max'];
        value = Env.formatTimestamp(value, 'date');
        parameterHash += name + 'Max' + '=' + encodeURIComponent(value) + '&';
      }
    } else {
      if (History.searchParameters[name] !== undefined &&
        History.searchParameters[name] !=
          History.searchParameterInfo[name].defaultValue) {
        value = History.searchParameters[name];
        parameterHash += name + '=' + encodeURIComponent(value) + '&';
      }
    }
  });

  // Trim off the trailing &
  parameterHash = parameterHash.replace(/&$/, '');
  Env.history.pushState(History.searchParameters,
    'Button Men Online &mdash; History', parameterHash);
};

////////////////////////////////////////////////////////////////////////
// Helper routines to generate HTML to populate the page

History.buildSearchButtonDiv = function() {
  var buttonDiv = $('<div>');

  buttonDiv.append($('<text>', { 'text': 'Display up to ', }));
  var pageSizeSelect = $('<select>', { 'id': 'parameter_numberOfResults', });
  buttonDiv.append(pageSizeSelect);
  $.each(History.searchParameterInfo.numberOfResults.source,
    function (value, name) {
      pageSizeSelect.append($('<option>', {
        'value': value,
        'text': name,
      }));
    });
  if (History.searchParameters === undefined ||
    History.searchParameters.numberOfResults === undefined) {
    pageSizeSelect
      .val(History.searchParameterInfo.numberOfResults.defaultValue);
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
    History.page.find('#parameter_page')
      .val(History.searchParameterInfo.page.defaultValue);
    History.performManualSearch();
  });

  return buttonDiv;
};

History.buildHiddenFields = function() {
  var hiddenDiv = $('<div>', { 'style': 'display: none; ' });

  $.each(History.searchParameterInfo, function(name, info) {
    if (info.inputType == 'hidden') {
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

  $.each(History.searchParameterInfo, function(columnId, columnInfo) {
    if (columnInfo.inputType == 'hidden' || columnInfo.inputType == 'special') {
      return;
    }

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
      'title': 'Sort by ' + columnInfo.text + ' in ascending order',
    }));

    titleTh.append($('<span>', {
      'text': columnInfo.text,
      'title': columnInfo.toolTip,
    }));

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
      'title': 'Sort by ' + columnInfo.text + ' in descending order',
    }));

    var filterTd = $('<th>');
    filterRow.append(filterTd);

    switch (columnInfo.inputType) {
    case 'select':
      var selectList = $('<select>', {
        'name': columnId,
        'id': 'parameter_' + columnId,
        'title': columnInfo.toolTip,
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
      var range = { 'Min': 'Minimum', 'Max': 'Maximum' };
      $.each(range, function(abbrev, full) {
        var dateDiv = $('<div>');
        filterTd.append(dateDiv);

        var dateElement = $('<input>', {
          'type': 'text',
          'name': columnId + abbrev,
          'id': 'parameter_' + columnId + abbrev,
          'maxlength': '10',
          'class': 'dateInput',
          'title': full +  ' ' + columnInfo.text + ' Date',
        });
        dateDiv.append(dateElement);
        if (History.searchParameters !== undefined) {
          var date =
            Env.formatTimestamp(History.searchParameters[columnId + abbrev],
              'date');
          dateElement.val(date);
        }
        dateElement.datepicker({ 'dateFormat': 'yy-mm-dd', });

        var calendar = $('<img>', {
          'src': Env.ui_root + 'images/calendar.png',
          'class': 'calendar',
          'title': full +  ' ' + columnInfo.text + ' Date',
        });
        dateDiv.append(calendar);
        calendar.click(function() {
          dateElement.datepicker('show');
        });
      });
      break;
    default:
      var inputElement = $('<input>', {
        'type': columnInfo.inputType,
        'name': columnId,
        'id': 'parameter_' + columnId,
        'title': columnInfo.toolTip,
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

  if (Api.game_history === undefined ||
    Api.game_history.games === undefined)  {
    return body;
  }

  $.each(Api.game_history.games, function(index, game) {
    var gameRow = $('<tr>');
    body.append(gameRow);

    var $nextPlayerColor = '#ffffff';
    if (game.playerNameA == Login.player) {
      if (game.waitingOnA) {
        $nextPlayerColor = game.colorA;
      } else if (game.waitingOnB) {
        $nextPlayerColor = game.colorB;
      }
    } else if (game.playerNameB == Login.player) {
      if (game.waitingOnB) {
        $nextPlayerColor = game.colorB;
      } else if (game.waitingOnA) {
        $nextPlayerColor = game.colorA;
      }
    }

    var idTd = $('<td>', {
      'style': 'background-color: ' + $nextPlayerColor + ';',
    });
    idTd.append($('<a>', {
      'href': 'game.html?game=' + game.gameId,
      'text': 'Game ' + game.gameId,
    }));
    gameRow.append(idTd);

    gameRow.append($('<td>', {
      'style': 'background-color: ' + game.colorA + ';',
    }).append(Env.buildProfileLink(game.playerNameA)));
    gameRow.append($('<td>').append(
      Env.buildButtonLink(game.buttonNameA)
    ));
    gameRow.append($('<td>').append(
      Env.buildButtonLink(game.buttonNameB)
    ));
    gameRow.append($('<td>', {
      'style': 'background-color: ' + game.colorB + ';',
    }).append(Env.buildProfileLink(game.playerNameB)));
    gameRow.append($('<td>', {
      'text': Env.formatTimestamp(game.gameStart, 'date'),
    }));
    gameRow.append($('<td>', {
      'text': Env.formatTimestamp(game.lastMove, 'date'),
    }));

    gameRow.append(History.scoreCol(game));

    if (game.status == 'COMPLETE') {
      gameRow.append($('<td>', {
        'text': 'Completed',
        'style': 'font-weight: bold;'
      }));
    } else if (game.status == 'CANCELLED') {
      gameRow.append($('<td>', {
        'text': 'Cancelled',
        'style': 'color: #aaaaaa;'
      }));
    } else {
      gameRow.append($('<td>', {
        'text': 'In Progress',
        'style': 'font-style: italic;'
      }));
    }
  });

  return body;
};

History.scoreCol = function(game) {
  var score;
  if (game.status == 'CANCELLED') {
    score = '–/–/–';
  } else {
    score = game.roundsWonA + '/' + game.roundsWonB + '/' + game.roundsDrawn;
  }
  score += ' (' + game.targetWins + ')';

  var winnerColor;
  if (game.roundsWonA >= game.targetWins) {
    winnerColor = game.colorA;
  } else if (game.roundsWonB >= game.targetWins) {
    winnerColor = game.colorB;
  } else {
    winnerColor = '#ffffff';
  }

  var column = $('<td>', {
    'text': score,
    'style': 'background-color: ' + winnerColor + ';',
  });

  return column;
};

History.buildResultsTableFooter = function() {
  var foot = $('<tfoot>');

  if (Api.game_history === undefined ||
    Api.game_history.summary === undefined)  {
    return foot;
  }

  var summary = Api.game_history.summary;
  var lastPage =
    Math.ceil(summary.matchesFound / History.searchParameters.numberOfResults);

  var footerHeaderRow = $('<tr>');
  foot.append(footerHeaderRow);

  var matchesToolTip;
  if (summary.matchesFound == 1) {
    matchesToolTip = 'There is 1 matching game';
  } else {
    matchesToolTip = 'There are ' + summary.matchesFound + ' matching games';
  }
  footerHeaderRow.append($('<th>', {
    'text': 'Matches Found',
    'title': matchesToolTip,
  }));

  var pagesToolTip;
  if (lastPage == 1) {
    pagesToolTip = 'There is 1 page of matching games';
  } else {
    pagesToolTip = 'There are ' + lastPage + ' pages of matching games';
  }
  footerHeaderRow.append($('<th>', {
    'text': 'Pages',
    'colspan': '4',
    'style': 'text-align: left;',
    'title': pagesToolTip,
  }));
  footerHeaderRow.append($('<th>', {
    'text': 'Earliest Start',
    'title': 'The earliest Game Start date for any matching game',
  }));
  footerHeaderRow.append($('<th>', {
    'text': 'Latest Move',
    'title': 'The latest Last Move date for any matching game',
  }));
  footerHeaderRow.append($('<th>', {
    'text': 'Games W/L/I',
    'title': 'Won by Player A / Lost by Player A / Incomplete',
  }));
  footerHeaderRow.append($('<th>', {
    'text': '% Completed',
    'title': 'What fraction of the matching games have been completed so far',
  }));

  var footerDataRow = $('<tr>');
  foot.append(footerDataRow);

  footerDataRow.append($('<td>', { 'text': summary.matchesFound }));

  var pagingTd = $('<td>', {
    'colspan': '4',
    'style': 'text-align: left;',
  });

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

  footerDataRow.append($('<td>', {
    'text': Env.formatTimestamp(summary.earliestStart, 'date'),
    'title': 'The earliest Game Start date for any matching game',
  }));
  footerDataRow.append($('<td>', {
    'text': Env.formatTimestamp(summary.latestMove, 'date'),
    'title': 'The latest Last Move date for any matching game',
  }));

  var scores =
    summary.gamesWonA + '/' + summary.gamesWonB + '/' +
    (summary.matchesFound - summary.gamesCompleted);
  footerDataRow.append($('<td>', {
    'text': scores,
    'title': 'Won by Player A / Lost by Player A / Incomplete',
  }));

  var percentCompleted = '';
  if (summary.matchesFound > 0) {
    percentCompleted = (summary.gamesCompleted * 100) / summary.matchesFound;
    percentCompleted = Math.round(percentCompleted) + '%';
  }
  footerDataRow.append($('<td>', {
    'text': percentCompleted,
    'title': 'What fraction of the matching games have been completed so far',
  }));

  return foot;
};
