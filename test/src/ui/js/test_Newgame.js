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
    delete Newgame.page;
    delete Newgame.form;

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

// Newgame.showNewgamePage() does not directly take a callback,
// but, under the hood, it calls a function (Newgame.getNewgameData())
// which calls a chain of two callbacks in succession.
// It appears that QUnit's asynchronous testing framework can't
// handle that situation, so don't use it --- instead turn off
// asynchronous processing in AJAX while we test this one.
test("test_Newgame.showNewgamePage", function() {
  $.ajaxSetup({ async: false });
  Newgame.showNewgamePage();
  var item = document.getElementById('newgame_page');
  equal(item.nodeName, "DIV",
        "#newgame_page is a div after showNewgamePage() is called");
  $.ajaxSetup({ async: true });
});

asyncTest("test_Newgame.getNewgameData", function() {
  Newgame.getNewgameData(function() {
    ok(Api.player, "player list is parsed from server");
    ok(Api.button, "button list is parsed from server");
    start();
  });
});

asyncTest("test_Newgame.showPage", function() {
  Newgame.getNewgameData(function() {
    Newgame.showPage();
    var htmlout = Newgame.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

asyncTest("test_Newgame.layoutPage", function() {
  Newgame.getNewgameData(function() {
    Newgame.page = $('<div>');
    Newgame.page.append($('<p>', {'text': 'hi world', }));
    Newgame.layoutPage();
    var item = document.getElementById('newgame_page');
    equal(item.nodeName, "DIV",
          "#newgame_page is a div after layoutPage() is called");
    start();
  });     
});

asyncTest("test_Newgame.actionLoggedOut", function() {
  Newgame.getNewgameData(function() {
    Newgame.actionLoggedOut();
    equal(Newgame.form, null,
          "Form is null after the 'logged out' action is processed");
    start();
  });     
});

asyncTest("test_Newgame.actionInternalErrorPage", function() {
  Newgame.getNewgameData(function() {
    Newgame.actionInternalErrorPage();
    equal(Newgame.form, null,
          "Form is null after the 'internal error' action is processed");
    start();
  });     
});

asyncTest("test_Newgame.actionCreateGame", function() {
  Newgame.getNewgameData(function() {
    Newgame.actionCreateGame();
    equal(Newgame.form, Newgame.formCreateGame,
          "Form is set after the 'create game' action is processed");
    start();
  });     
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
