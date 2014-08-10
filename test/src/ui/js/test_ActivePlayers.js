  module("ActivePlayers", {
  'setup': function() {
    BMTestUtils.ActivePlayersPre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the activeplayers_page div so functions have something to modify
    if (document.getElementById('activeplayers_page') == null) {
      $('body').append($('<div>', {'id': 'activeplayers_page', }));
    }
  },
  'teardown': function(assert) {

    // Delete all elements we expect this module to create

    // JavaScript variables
    delete Api.active_players;
    delete ActivePlayers.page;

    // Page elements
    $('#activeplayers_page').remove();
    $('#activeplayers_page').empty();

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

test("test_ActivePlayers.showActivePlayersPage", function(assert) {
  stop();
  ActivePlayers.showActivePlayersPage();
  var item = document.getElementById('activeplayers_page');
  assert.equal(item.nodeName, "DIV",
        "#activeplayers_page is a div after showActivePlayersPage() is called");
  start();
});

test("test_ActivePlayers.getActivePlayers", function(assert) {
  stop();
  ActivePlayers.getActivePlayers(function() {
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
  ActivePlayers.getActivePlayers(function() {
    ActivePlayers.showPage();
    var htmlout = ActivePlayers.page.html();
    assert.ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

test("test_ActivePlayers.arrangePage", function(assert) {
  stop();
  ActivePlayers.getActivePlayers(function() {
    ActivePlayers.page = $('<div>');
    ActivePlayers.page.append($('<p>', {'text': 'hi world', }));
    ActivePlayers.arrangePage();
    var item = document.getElementById('activeplayers_page');
    assert.equal(item.nodeName, "DIV",
          "#activeplayers_page is a div after arrangePage() is called");
    start();
  });
});

test("test_ActivePlayers.buildPlayersTable", function(assert) {
  stop();
  ActivePlayers.getActivePlayers(function() {
    var table = ActivePlayers.buildPlayersTable();
    var htmlout = table.html();
    assert.ok(htmlout.match('12 minutes'), "Players table content was generated");
    start();
  });
});
