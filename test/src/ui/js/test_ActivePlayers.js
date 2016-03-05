module("ActivePlayers", {
  'setup': function() {
    BMTestUtils.ActivePlayersPre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the activeplayers_page div so functions have something to modify
    if (document.getElementById('activeplayers_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'activeplayers_page', }));
    }
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JavaScript variables
    delete Api.active_players;
    delete ActivePlayers.page;

    // Page elements
    $('#activeplayers_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.ActivePlayersPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.ActivePlayersPost, BMTestUtils.ActivePlayersPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the ActivePlayers module has been loaded
test("test_ActivePlayers_is_loaded", function(assert) {
  assert.ok(ActivePlayers, "The ActivePlayers namespace exists");
});

// The purpose of this test is to demonstrate that the flow of
// ActivePlayers.showLoggedInPage() is correct for a showXPage function, namely
// that it calls an API getter with a showStatePage function as a
// callback.
//
// Accomplish this by mocking the invoked functions
test("test_ActivePlayers.showLoggedInPage", function(assert) {
  expect(5);
  var cached_getActivePlayers = Api.getActivePlayers;
  var cached_showStatePage = ActivePlayers.showPage;
  var getActivePlayersCalled = false;
  ActivePlayers.showPage = function() {
    assert.ok(getActivePlayersCalled, "ActivePlayers.getActivePlayers is called before ActivePlayers.showPage");
  };
  Api.getActivePlayers = function(number, callback) {
    getActivePlayersCalled = true;
    assert.equal(callback, ActivePlayers.showPage,
      "ActivePlayers.getActivePlayers is called with ActivePlayers.showPage as an argument");
    callback();
  };

  ActivePlayers.showLoggedInPage();
  var item = document.getElementById('activeplayers_page');
  assert.equal(item.nodeName, "DIV",
        "#activeplayers_page is a div after showLoggedInPage() is called");
  Api.getActivePlayers = cached_getActivePlayers;
  ActivePlayers.showPage = cached_showStatePage;
});

test("test_ActivePlayers.getActivePlayers", function(assert) {
  stop();
  Api.getActivePlayers(50, function() {
    assert.ok(Api.active_players, "ActivePlayers info parsed from server");
    if (Api.active_players) {
      assert.equal(Api.active_players.load_status, 'ok',
        "ActivePlayers info parsed successfully from server");
    }
    start();
  });
});

test("test_ActivePlayers.showPage", function(assert) {
  stop();
  Api.getActivePlayers(50, function() {
    ActivePlayers.showPage();
    var htmlout = ActivePlayers.page.html();
    assert.ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

test("test_ActivePlayers.buildPlayersTable", function(assert) {
  stop();
  Api.getActivePlayers(50, function() {
    var table = ActivePlayers.buildPlayersTable();
    var htmlout = table.html();
    assert.ok(htmlout.match('responder0'), "Players table content contains a username");
    assert.ok(htmlout.match(' seconds'), "Players table content contains a timestamp");
    start();
  });
});
