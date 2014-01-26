module("Newgame", {
  'setup': function() {
    BMTestUtils.NewgamePre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the newgame_page div so functions have something to modify
    if (document.getElementById('newgame_page') == null) {
      $('body').append($('<div>', {'id': 'newgame_page', }));
    }
  },
  'teardown': function() {

    // Delete all elements we expect this module to create

    // JS objects
    delete Api.button;
    delete Api.player;

    // Page elements
    $('#newgame_page').remove();
    $('#newgame_page').empty();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.NewgamePost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.NewgamePost, BMTestUtils.NewgamePre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Newgame module has been loaded
test("test_Newgame_is_loaded", function() {
  ok(Newgame, "The Newgame namespace exists");
});

asyncTest("test_Newgame.showNewgamePage", function() {
  ok(true,
    "INCOMPLETE: Test of Newgame.showNewgamePage not implemented");
  start();
});

asyncTest("test_Newgame.showNewgamePageLoadedButtons", function() {
  ok(true,
    "INCOMPLETE: Test of Newgame.showNewgamePageLoadedButtons not implemented");
  start();
});

asyncTest("test_Newgame.showNewgamePageLoadedPlayers", function() {
  ok(true,
    "INCOMPLETE: Test of Newgame.showNewgamePageLoadedPlayers not implemented");
  start();
});

asyncTest("test_Newgame.layoutPage", function() {
  ok(true, "INCOMPLETE: Test of Newgame.layoutPage not implemented");
  start();
});

asyncTest("test_Newgame.actionLoggedOut", function() {
  ok(true, "INCOMPLETE: Test of Newgame.actionLoggedOut not implemented");
  start();
});

asyncTest("test_Newgame.actionInternalErrorPage", function() {
  ok(true, "INCOMPLETE: Test of Newgame.actionInternalErrorPage not implemented");
  start();
});

asyncTest("test_Newgame.actionCreateGame", function() {
  ok(true, "INCOMPLETE: Test of Newgame.actionCreateGame not implemented");
  start();
});

asyncTest("test_Newgame.formCreateGame", function() {
  ok(true, "INCOMPLETE: Test of Newgame.formCreateGame not implemented");
  start();
});

asyncTest("test_Newgame.addLoggedOutPage", function() {
  ok(true, "INCOMPLETE: Test of Newgame.addLoggedOutPage not implemented");
  start();
});

asyncTest("test_Newgame.addInternalErrorPage", function() {
  ok(true, "INCOMPLETE: Test of Newgame.addInternalErrorPage not implemented");
  start();
});

asyncTest("test_Newgame.getSelectRow", function() {
  ok(true, "INCOMPLETE: Test of Newgame.getSelectRow not implemented");
  start();
});

test("test_Newgame.setCreateGameSuccessMessage", function() {
  Newgame.setCreateGameSuccessMessage(
    'test invocation succeeded',
    { 'gameId': 8, }
  );
  equal(Env.message.type, 'success', "set Env.message to a successful type");
});
