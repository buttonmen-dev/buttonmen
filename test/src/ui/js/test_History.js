module("History", {
  'setup': function() {
    BMTestUtils.HistoryPre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the history_page div so functions have something to modify
    if (document.getElementById('history_page') == null) {
      $('body').append($('<div>', {'id': 'history_page', }));
    }
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JavaScript variables
    delete Env.window.location.href;
    delete Env.window.location.search;
    delete Env.window.location.hash;
    delete Env.history.state;
    delete Api.game_history;
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
    assert.deepEqual(
      BMTestUtils.HistoryPost, BMTestUtils.HistoryPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the History module has been loaded
test("test_History_is_loaded", function(assert) {
  assert.ok(History, "The History namespace exists");
});

// The purpose of this test is to demonstrate that the flow of
// History.showHistoryPage() is correct for a showXPage function, namely
// that it calls an API getter with a showStatePage function as a
// callback.
//
// Accomplish this by mocking the invoked functions
test("test_History.showHistoryPage", function(assert) {
  expect(7);
  var cached_getFilters = History.getFilters;
  var cached_getHistory = History.getHistory;
  var cached_showStatePage = History.showPage;
  var getFiltersCalled = false;
  var getHistoryCalled = false;
  History.showPage = function() {
    assert.ok(getFiltersCalled, "History.getFilters is called before History.showPage");
    assert.ok(getHistoryCalled, "History.getHistory is called before History.showPage");
  }
  History.getFilters = function(callback) {
    getFiltersCalled = true;
    assert.ok(true, "History.getFilters is called");
    callback();
  }
  History.getHistory = function(callback) {
    getHistoryCalled = true;
    assert.equal(callback, History.showPage,
      "History.getHistory is called with History.showPage as an argument");
    callback();
  }

  // Remove #history_page so that showHistoryPage will have something to add
  $('#history_page').remove();
  History.showHistoryPage();
  var item = document.getElementById('history_page');
  assert.equal(item.nodeName, "DIV",
        "#history_page is a div after showHistoryPage() is called");

  History.getFilters = cached_getFilters;
  History.getHistory = cached_getHistory;
  History.showPage = cached_showStatePage;
});

test("test_History.getHistory", function(assert) {
  stop();
  History.searchParameters = {
    'sortColumn': 'lastMove',
    'sortDirection': 'DESC',
    'numberOfResults': '20',
    'page': '1',
    'playerNameA': 'tester',
    'status': 'COMPLETE',
  };
  History.searchParameterInfo.playerNameA.source = { 'tester': { }, };

  History.getHistory(function() {
    assert.ok(Api.game_history.games, "games list is parsed from server");
    assert.ok(Api.game_history.summary, "summary data is parsed from server");
    start();
  });
});

test("test_History.getFilters", function(assert) {
  stop();
  History.getFilters(function() {
    assert.ok(Api.player.list, "The list of players should be loaded");
    assert.ok(Api.button.list, "The list of players should be loaded");
    start();
  });
});

test("test_History.showPage", function(assert) {
  History.searchParameters = {
    'sortColumn': 'lastMove',
    'sortDirection': 'DESC',
    'numberOfResults': '20',
    'page': '1',
    'playerNameA': 'tester',
    'status': 'COMPLETE',
  };
  History.searchParameterInfo.playerNameA.source = { 'tester': { }, };

  // showPage is apparently just too amazing for qunit to handle testing
  // it asynchronously
  $.ajaxSetup({ async: false });
  History.getHistory(function() {
    History.getFilters(History.showPage);
    var htmlout = History.page.html();
    assert.ok(htmlout.length > 0,
       "The created page should have nonzero contents");
  });
  $.ajaxSetup({ async: true });
});

test("test_History.arrangePage", function(assert) {
  stop();
  History.searchParameters = {
    'sortColumn': 'lastMove',
    'sortDirection': 'DESC',
    'numberOfResults': '20',
    'page': '1',
    'playerNameA': 'tester',
    'status': 'COMPLETE',
  };
  History.searchParameterInfo.playerNameA.source = { 'tester': { }, };

  History.getHistory(function() {
    History.page = $('<div>');
    History.page.append($('<p>', {'text': 'hi world', }));
    History.arrangePage();
    var item = document.getElementById('history_page');
    assert.equal(item.nodeName, "DIV",
          "#overview_page is a div after arrangePage() is called");
    start();
  });
});

test("test_History.performManualSearch", function(assert) {
  // There are AJAX calls within the method that we can't pass a callback to,
  // so we need to make them run async in order for this to work reliably
  $.ajaxSetup({ async: false });
  History.page = $('<div>');
  History.performManualSearch();
  assert.ok(Api.game_history.games, "games list is parsed from server");
  assert.ok(Api.game_history.summary, "summary data is parsed from server");
  $.ajaxSetup({ async: true });
});

test("test_History.performAutomaticSearch", function(assert) {
  // There are AJAX calls within the method that we can't pass a callback to,
  // so we need to make them run async in order for this to work reliably
  $.ajaxSetup({ async: false });
  History.page = $('<div>');
  Env.history.pushState({ }, null, '#!hash');
  History.performAutomaticSearch();
  assert.ok(Api.game_history.games, "games list is parsed from server");
  assert.ok(Api.game_history.summary, "summary data is parsed from server");
  $.ajaxSetup({ async: true });
});

test("test_History.readSearchParametersFromUrl", function(assert) {
  Env.window.location.search = '';
  Env.window.location.hash =
    '#!playerNameA=tester&buttonNameB=Bunnies&WinningPlayer=A';
  History.readSearchParametersFromUrl();
  assert.equal(History.searchParameters.buttonNameB, 'Bunnies',
    "search parameters should be correctly read from the URL");
});

test("test_History.readSearchParametersFromForm", function(assert) {
  History.page = $('<div>');
  History.page.append($('<input>', {
    'id': 'parameter_playerNameA',
    'value': 'tester2'
  }));
  History.readSearchParametersFromForm();
  assert.equal(History.searchParameters.playerNameA, 'tester2',
    "search parameters should be correctly read from the form");
});

test("test_History.assignSearchParameter", function(assert) {
  History.searchParameters = { };
  var expected = 'Tied';
  History.assignSearchParameter('winningPlayer', 'string', expected);
  assert.equal(History.searchParameters.winningPlayer, expected,
    "search parameter value should be correctly assigned");
});

test("test_History.writeSearchParametersToUrl", function(assert) {
  History.searchParameters = {
    'playerNameA': 'tester',
    'numberOfResults': 50,
  };
  History.writeSearchParametersToUrl();
  assert.equal(Env.window.location.hash, '#!playerNameA=tester&numberOfResults=50',
    "search parameters should be correctly written to the URL");
});

test("test_History.buildSearchButtonDiv", function(assert) {
  History.page = $('<div>');
  History.page.append($('<input>', {
    'id': 'parameter_page',
    'type': 'text',
  }));
  var div = History.buildSearchButtonDiv();
  var button = div.find('#searchButton');
  assert.ok(button.length > 0, "Search button is created")
});

test("test_History.buildHiddenFields", function(assert) {
  var div = History.buildHiddenFields();
  var button = div.find('#parameter_page');
  assert.ok(button.length > 0, "Page number field is created")
});

test("test_History.buildResultsTableHeader", function(assert) {
  stop();
  History.getFilters(function() {
    var thead = History.buildResultsTableHeader();

    assert.ok(thead.find('th').length > 0, "Header cells are created");
    assert.ok(thead.find('th:contains("Game #")').length == 1,
      "Game # header exists");
    start();
  });
});

test("test_History.buildResultsTableBody", function(assert) {
  stop();
  History.searchParameters = {
    'sortColumn': 'lastMove',
    'sortDirection': 'DESC',
    'numberOfResults': '20',
    'page': '1',
    'playerNameA': 'tester',
    'status': 'COMPLETE',
  };
  History.searchParameterInfo.playerNameA.source = { 'tester': { }, };

  History.getHistory(function() {
    var tbody = History.buildResultsTableBody();
    var htmlout = tbody.html();
    assert.ok(htmlout.match('<td>Avis</td>'),
      'Table body contains game information.');
    start();
  });
});

test("test_History.buildResultsTableFooter", function(assert) {
  stop();
  History.searchParameters = {
    'sortColumn': 'lastMove',
    'sortDirection': 'DESC',
    'numberOfResults': '20',
    'page': '1',
    'playerNameA': 'tester',
    'status': 'COMPLETE',
  };
  History.searchParameterInfo.playerNameA.source = { 'tester': { }, };

  History.getHistory(function() {
    var tfoot = History.buildResultsTableFooter();

    var scoreFound = false;
    tfoot.find('td').each(function() {
      if ($(this).text().match('\\d\\d?\\d?%')) {
        scoreFound = true;
      }
    });
    start();
  });
});
