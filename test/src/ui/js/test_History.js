module("History", {
  'setup': function() {
    BMTestUtils.HistoryPre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the history_page div so functions have something to modify
    if (document.getElementById('history_page') == null) {
      $('body').append($('<div>', {'id': 'history_page', }));
    }
  },
  'teardown': function() {

    // Delete all elements we expect this module to create

    // JavaScript variables
    delete Env.window.location.href;
    delete Env.window.location.search;
    delete Env.window.location.hash;
    delete Env.history.state;
    delete Api.search_results;
    delete Api.player;
    delete Api.button;
    delete History.searchParameters;
    delete History.page;

    History.searchParameterInfo.playerNameA.source = { };
    History.searchParameterInfo.buttonNameA.source = { };
    History.searchParameterInfo.buttonNameB.source = { };
    History.searchParameterInfo.playerNameB.source = { };

    // Page elements
    $('#history_page').remove();
    $('#history_page').empty();
    $('#ui-datepicker-div').remove();
    $('#ui-datepicker-div').empty();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.HistoryPost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.HistoryPost, BMTestUtils.HistoryPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the History module has been loaded
test("test_History_is_loaded", function() {
  ok(History, "The History namespace exists");
});

test("test_History.showHistoryPage", function() {
  // showHistoryPage is apparently just too amazing for qunit to handle testing
  // it asynchronously
  $.ajaxSetup({ async: false });
  // Remove #history_page so that showHistoryPage will have something to add
  $('#history_page').remove();
  History.showHistoryPage();
  var item = document.getElementById('history_page');
  equal(item.nodeName, "DIV",
        "#history_page is a div after showHistoryPage() is called");
  $.ajaxSetup({ async: true });
});

asyncTest("test_History.getHistory", function() {
  History.searchParameters = {
    'sortColumn': 'lastMove',
    'sortDirection': 'DESC',
    'numberOfResults': '20',
    'page': '1',
    'playerNameA': 'tester',
    'status': 'COMPLETE',
  };

  History.getHistory(function() {
    ok(Api.search_results.games, "games list is parsed from server");
    ok(Api.search_results.summary, "summary data is parsed from server");
    start();
  });
});

asyncTest("test_History.getFilters", function() {
  History.getFilters(function() {
    ok(Api.player.list, "The list of players should be loaded");
    ok(Api.button.list, "The list of players should be loaded");
    start();
  });
});

test("test_History.showPage", function() {
  History.searchParameters = {
    'sortColumn': 'lastMove',
    'sortDirection': 'DESC',
    'numberOfResults': '20',
    'page': '1',
    'playerNameA': 'tester',
    'status': 'COMPLETE',
  };

  // showPage is apparently just too amazing for qunit to handle testing
  // it asynchronously
  $.ajaxSetup({ async: false });
  History.getHistory(function() {
    History.getFilters(History.showPage);
    var htmlout = History.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
  });
  $.ajaxSetup({ async: true });
});

asyncTest("test_History.layoutPage", function() {
  History.searchParameters = {
    'sortColumn': 'lastMove',
    'sortDirection': 'DESC',
    'numberOfResults': '20',
    'page': '1',
    'playerNameA': 'tester',
    'status': 'COMPLETE',
  };

  History.getHistory(function() {
    History.page = $('<div>');
    History.page.append($('<p>', {'text': 'hi world', }));
    History.layoutPage();
    var item = document.getElementById('history_page');
    equal(item.nodeName, "DIV",
          "#overview_page is a div after layoutPage() is called");
    start();
  });
});

test("test_History.performManualSearch", function() {
  // There are AJAX calls within the method that we can't pass a callback to,
  // so we need to make them run async in order for this to work reliably
  $.ajaxSetup({ async: false });
  History.page = $('<div>');
  History.performManualSearch();
  ok(Api.search_results.games, "games list is parsed from server");
  ok(Api.search_results.summary, "summary data is parsed from server");
  $.ajaxSetup({ async: true });
});

test("test_History.performAutomaticSearch", function() {
  // There are AJAX calls within the method that we can't pass a callback to,
  // so we need to make them run async in order for this to work reliably
  $.ajaxSetup({ async: false });
  History.page = $('<div>');
  Env.history.pushState({ }, null, '#!hash');
  History.performAutomaticSearch();
  ok(Api.search_results.games, "games list is parsed from server");
  ok(Api.search_results.summary, "summary data is parsed from server");
  $.ajaxSetup({ async: true });
});

test("test_History.readSearchParametersFromUrl", function() {
  Env.window.location.search = '';
  Env.window.location.hash =
    '#!playerNameA=tester&buttonNameB=Bunnies&WinningPlayer=A';
  History.readSearchParametersFromUrl();
  equal(History.searchParameters.buttonNameB, 'Bunnies',
    "search parameters should be correctly read from the URL");
});

test("test_History.readSearchParametersFromForm", function() {
  History.page = $('<div>');
  History.page.append($('<input>', {
    'id': 'parameter_playerNameA',
    'value': 'tester2'
  }));
  History.readSearchParametersFromForm();
  equal(History.searchParameters.playerNameA, 'tester2',
    "search parameters should be correctly read from the form");
});

test("test_History.assignSearchParameter", function() {
  History.searchParameters = { };
  var expected = 'Tied';
  History.assignSearchParameter('winningPlayer', 'string', expected);
  equal(History.searchParameters.winningPlayer, expected,
    "search parameter value should be correctly assigned");
});

test("test_History.writeSearchParametersToUrl", function() {
  History.searchParameters = {
    'playerNameA': 'tester',
    'numberOfResults': 50,
  };
  History.writeSearchParametersToUrl();
  equal(Env.window.location.hash, '#!playerNameA=tester&numberOfResults=50',
    "search parameters should be correctly written to the URL");
});

test("test_History.buildSearchButtonDiv", function() {
  History.page = $('<div>');
  History.page.append($('<input>', {
    'id': 'parameter_page',
    'type': 'text',
  }));
  var div = History.buildSearchButtonDiv();
  var button = div.find('#searchButton');
  ok(button.length > 0, "Search button is created")
});

test("test_History.buildHiddenFields", function() {
  var div = History.buildHiddenFields();
  var button = div.find('#parameter_page');
  ok(button.length > 0, "Page number field is created")
});

asyncTest("test_History.buildResultsTableHeader", function() {
  History.getFilters(function() {
    var thead = History.buildResultsTableHeader();
    var htmlout = thead.html();

    ok(htmlout.match('<th>'), "Header cells are created");
    ok(htmlout.match('<span>Game #</span>'), "Game # header exists");
    start();
  });
});

asyncTest("test_History.buildResultsTableBody", function() {
  History.searchParameters = {
    'sortColumn': 'lastMove',
    'sortDirection': 'DESC',
    'numberOfResults': '20',
    'page': '1',
    'playerNameA': 'tester',
    'status': 'COMPLETE',
  };

  History.getHistory(function() {
    var tbody = History.buildResultsTableBody();
    var htmlout = tbody.html();
    ok(htmlout.match('<td>Avis</td>'),
      'Table body contains game information.');
    start();
  });
});

asyncTest("test_History.buildResultsTableFooter", function() {
  History.searchParameters = {
    'sortColumn': 'lastMove',
    'sortDirection': 'DESC',
    'numberOfResults': '20',
    'page': '1',
    'playerNameA': 'tester',
    'status': 'COMPLETE',
  };

  History.getHistory(function() {
    var tfoot = History.buildResultsTableFooter();
    var htmlout = tfoot.html();
    ok(htmlout.match('<td>\\d\\d?\\d?%</td>'),
      'Table footer contains game information.');
    start();
  });
});
