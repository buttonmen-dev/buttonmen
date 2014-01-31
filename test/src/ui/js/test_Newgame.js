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

test("test_Newgame.showNewgamePage_no_page_element", function() {

  // Remove page element to make sure the function readds it
  $('#newgame_page').remove();
  $('#newgame_page').empty();

  $.ajaxSetup({ async: false });
  Newgame.showNewgamePage();
  var item = document.getElementById('newgame_page');
  equal(item.nodeName, "DIV",
        "#newgame_page is a div after showNewgamePage() is called");
  $.ajaxSetup({ async: true });
});

test("test_Newgame.showNewgamePage_logged_out", function() {

  // Undo the fake login data
  Login.player = null;
  Login.logged_in = false;

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

asyncTest("test_Newgame.showPage_button_load_failed", function() {
  Newgame.getNewgameData(function() {
    Api.button.load_status = 'failed';
    Newgame.showPage();
    var htmlout = Newgame.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

asyncTest("test_Newgame.showPage_player_load_failed", function() {
  Newgame.getNewgameData(function() {
    Api.player.load_status = 'failed';
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

// The logic here is a little hairy: since Newgame.getNewgameData()
// takes a callback, we can use the normal asynchronous logic there.
// However, the POST done by our forms doesn't take a callback (it
// just redraws the page), so turn off asynchronous handling in
// AJAX while we test that, to make sure the test sees the return
// from the POST.
asyncTest("test_Newgame.formCreateGame", function() {
  Newgame.getNewgameData(function() {
    Newgame.actionCreateGame();
    $('#opponent_name').val('tester2');
    $('#player_button').val('Crab');
    $('#opponent_button').val('John Kovalic');
    $.ajaxSetup({ async: false });
    $('#newgame_action_button').trigger('click');
    equal(
      Env.message.type, "success",
      "Newgame action succeeded when expected arguments were set");
    $.ajaxSetup({ async: true });
    start();
  });
});

asyncTest("test_Newgame.formCreateGame_no_vals", function() {
  Newgame.getNewgameData(function() {
    Newgame.actionCreateGame();
    $.ajaxSetup({ async: false });
    $('#newgame_action_button').trigger('click');
    equal(
      Env.message.type, "error",
      "Newgame action failed when expected arguments were not set");
    $.ajaxSetup({ async: true });
    start();
  });
});

asyncTest("test_Newgame.formCreateGame_no_buttons", function() {
  Newgame.getNewgameData(function() {
    Newgame.actionCreateGame();
    $('#opponent_name').val('tester2');
    $.ajaxSetup({ async: false });
    $('#newgame_action_button').trigger('click');
    equal(
      Env.message.type, "error",
      "Newgame action failed when expected arguments were not set");
    $.ajaxSetup({ async: true });
    start();
  });
});

asyncTest("test_Newgame.formCreateGame_no_opponent_button", function() {
  Newgame.getNewgameData(function() {
    Newgame.actionCreateGame();
    $('#opponent_name').val('tester2');
    $('#player_button').val('Crab');
    $.ajaxSetup({ async: false });
    $('#newgame_action_button').trigger('click');
    equal(
      Env.message.type, "error",
      "Newgame action failed when expected arguments were not set");
    $.ajaxSetup({ async: true });
    start();
  });
});

asyncTest("test_Newgame.formCreateGame_invalid_player", function() {
  Newgame.getNewgameData(function() {
    Newgame.actionCreateGame();
    $('#opponent_name').append(
      $('<option>', {
        'value': 'nontester1',
        'text': 'nontester1',
        'label': 'nontester1',
      })
    );
    $('#opponent_name').val('nontester1');
    $('#player_button').val('Crab');
    $('#opponent_button').val('John Kovalic');
    $.ajaxSetup({ async: false });
    $('#newgame_action_button').trigger('click');
    equal(
      Env.message.type, "error",
      "Newgame action failed when opponent was not a known player");
    equal(
      Env.message.text, "Specified opponent nontester1 is not recognized",
      "Newgame action failed when opponent was not a known player");
    $.ajaxSetup({ async: true });
    start();
  });
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
