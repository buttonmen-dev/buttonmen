module("OpenGames", {
  'setup': function() {
    BMTestUtils.OpenGamesPre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the opengames_page div so functions have something to modify
    if (document.getElementById('opengames_page') == null) {
      $('body').append($('<div>', {'id': 'opengames_page', }));
    }
  },
  'teardown': function(assert) {

    // Delete all elements we expect this module to create

    // JavaScript variables
    delete Api.button;
    delete Api.open_games;
    delete Api.join_game_result;
    delete OpenGames.page;

    // Page elements
    $('#opengames_page').remove();
    $('#opengames_page').empty();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.OpenGamesPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.OpenGamesPost, BMTestUtils.OpenGamesPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the OpenGames module has been loaded
test("test_OpenGames_is_loaded", function(assert) {
  assert.ok(OpenGames, "The OpenGames namespace exists");
});

test("test_OpenGames.showOpenGamesPage", function(assert) {
  $.ajaxSetup({ async: false });
  OpenGames.showOpenGamesPage();
  var item = document.getElementById('opengames_page');
  assert.equal(item.nodeName, "DIV",
        "#opengames_page is a div after showOpenGamesPage() is called");
  $.ajaxSetup({ async: true });
});

test("test_OpenGames.getOpenGames", function(assert) {
  stop();
  OpenGames.getOpenGames(function() {
    assert.ok(Api.open_games, "open games are parsed from server");
    if (Api.open_games) {
      assert.equal(Api.open_games.load_status, 'ok',
        "open games are parsed successfully from server");
    }
    start();
  });
});

test("test_OpenGames.showPage", function(assert) {
  stop();
  OpenGames.getOpenGames(function() {
    OpenGames.showPage();
    var htmlout = OpenGames.page.html();
    assert.ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

test("test_OpenGames.arrangePage", function(assert) {
  stop();
  OpenGames.getOpenGames(function() {
    OpenGames.page = $('<div>');
    OpenGames.page.append($('<p>', {'text': 'hi world', }));
    OpenGames.arrangePage();
    var item = document.getElementById('opengames_page');
    assert.equal(item.nodeName, "DIV",
          "#opengames_page is a div after arrangePage() is called");
    start();
  });
});

test("test_OpenGames.buildGameTable", function(assert) {
  stop();
  var buttons = {
    'Avis': {
      'recipe': 'Avis: (4) (4) (10) (12) (X)',
      'greyed': false,
    },
  };
  Api.getOpenGamesData(function() {
    var table = OpenGames.buildGameTable('joinable', buttons);
    assert.ok(table.find('td.gameAction').length > 0,
      "Table rows were generated");
    start();
  });
});

test("test_OpenGames.joinOpenGame", function(assert) {
  var gameId = 21;

  var gameRow = $('<tr>');
  var gameActionTd = $('<td>');
  gameRow.append(gameActionTd);
  var joinButton = $('<button>', {
    'type': 'button',
    'text': 'Join Game ' + gameId,
    'data-gameId': gameId,
  });
  gameActionTd.append(joinButton);
  joinButton.click(OpenGames.joinOpenGame);

  // We can't inject our callback into the API call made by this event, so
  // we'll just make the call asynchronously and run our tests afterward
  $.ajaxSetup({ async: false });
  joinButton.click();
  $.ajaxSetup({ async: true });

  var gameLink = gameActionTd.find('a');
  assert.ok(gameLink.length > 0, "link to game was created");
});

test("test_OpenGames.displayJoinResult", function(assert) {
  var gameId = 21;

  var gameActionTd = $('<td>');
  var joinButton = $('<button>', {
    'type': 'button',
    'text': 'Join Game ' + gameId,
    'data-gameId': gameId,
  });
  gameActionTd.append(joinButton);

  var victimButtonTd = $('<td>', { 'class': 'victimButton', });
  var buttonSelect = $('<select>');
  victimButtonTd.append(buttonSelect);

  OpenGames.displayJoinResult(joinButton, buttonSelect, gameId, 'Avis');

  var buttonSpan = victimButtonTd.find('span');
  assert.ok(buttonSpan.length > 0, "button name is displayed");
  assert.equal(buttonSpan.text(), 'Avis', "correct button name is displayed");
});
