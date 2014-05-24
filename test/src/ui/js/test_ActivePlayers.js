  module("ActivePlayers", {
  'setup': function() {
    BMTestUtils.ActivePlayersPre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the activeplayers_page div so functions have something to modify
    if (document.getElementById('activeplayers_page') == null) {
      $('body').append($('<div>', {'id': 'activeplayers_page', }));
    }
  },
  'teardown': function() {

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
    deepEqual(
      BMTestUtils.ActivePlayersPost, BMTestUtils.ActivePlayersPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the ActivePlayers module has been loaded
test("test_ActivePlayers_is_loaded", function() {
  ok(ActivePlayers, "The ActivePlayers namespace exists");
});

test("test_ActivePlayers.showActivePlayersPage", function() {
  $.ajaxSetup({ async: false });
  ActivePlayers.showActivePlayersPage();
  var item = document.getElementById('activeplayers_page');
  equal(item.nodeName, "DIV",
        "#activeplayers_page is a div after showActivePlayersPage() is called");
  $.ajaxSetup({ async: true });
});

asyncTest("test_ActivePlayers.getActivePlayers", function() {
  ActivePlayers.getActivePlayers(function() {
    ok(Api.active_players, "ActivePlayers info parsed from server");
    if (Api.active_players) {
      equal(Api.active_players.load_status, 'ok',
        "ActivePlayers info parsed successfully from server");
    }
    start();
  });
});

asyncTest("test_ActivePlayers.showPage", function() {
  ActivePlayers.getActivePlayers(function() {
    ActivePlayers.showPage();
    var htmlout = ActivePlayers.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

asyncTest("test_ActivePlayers.layoutPage", function() {
  ActivePlayers.getActivePlayers(function() {
    ActivePlayers.page = $('<div>');
    ActivePlayers.page.append($('<p>', {'text': 'hi world', }));
    ActivePlayers.layoutPage();
    var item = document.getElementById('activeplayers_page');
    equal(item.nodeName, "DIV",
          "#activeplayers_page is a div after layoutPage() is called");
    start();
  });
});

asyncTest("test_ActivePlayers.buildActivePlayersTable", function() {
  ActivePlayers.getActivePlayers(function() {
    var table = ActivePlayers.buildPlayersTable();
    var htmlout = table.html();
    ok(htmlout.match('12 minutes'), "Players table content was generated");
    start();
  });
});
