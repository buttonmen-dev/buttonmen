module("Login", {
  'setup': function() {
    BMTestUtils.LoginPre = BMTestUtils.getAllElements();
  },
  'teardown': function() {

    // Delete all elements we expect this module to create
    BMTestUtils.deleteEnvMessage();
    delete Api.gameNavigation;
    delete Api.forumNavigation;
    delete Env.window.location.href;
    delete Login.message;

    // Fail if any other elements were added or removed
    BMTestUtils.LoginPost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.LoginPost, BMTestUtils.LoginPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Login module has been loaded
test("test_Login_is_loaded", function() {
  ok(Login, "The Login namespace exists");
});

test("test_Login.getLoginHeader", function() {
  ok(true, "INCOMPLETE: Test of Login.getLoginHeader not implemented");
});

test("test_Login.showLoginHeader", function() {
  ok(true, "INCOMPLETE: Test of Login.showLoginHeader not implemented");
});

test("test_Login.layoutHeader", function() {
  ok(true, "INCOMPLETE: Test of Login.layoutHeader not implemented");
});

test("test_Login.getLoginForm", function() {
  ok(true, "INCOMPLETE: Test of Login.getLoginForm not implemented");
});

test("test_Login.stateLoggedIn", function() {
  ok(true, "INCOMPLETE: Test of Login.stateLoggedIn not implemented");
});

test("test_Login.stateLoggedOut", function() {
  ok(true, "INCOMPLETE: Test of Login.stateLoggedOut not implemented");
});

test("test_Login.addMainNavbar", function() {
  ok(true, "INCOMPLETE: Test of Login.addMainNavbar not implemented");
});

test("test_Login.addNewPostLink", function() {
  Login.message = $('<table>');
  var navRow  = $('<tr>', { 'class': 'headerNav' });
  Login.message.append(navRow);
  var navTd = $('<td>');
  navRow.append(navTd);
  navTd.append($('<a>', { 'href': '#', 'text': 'Forum', }));

  Api.forumNavigation = {
    'nextNewPostId': 7,
    'nextNewPostThreadId': 3,
  };

  Login.addNewPostLink();
  ok(navRow.find('a:contains("(New post)")').length,
    'Link should be created to new post when there is a new post');
});

test("test_Login.addNewPostLink_noNewPost", function() {
  Login.message = $('<table>');
  var navRow  = $('<tr>', { 'class': 'headerNav' });
  var navTd = $('<td>');
  navRow.append(navTd);
  navTd.append($('<a>', { 'href': '#', 'text': 'Forum', }));

  Api.forumNavigation = {
    'nextNewPostId': null,
    'nextNewPostThreadId': null,
  };

  Login.addNewPostLink();
  ok(!navRow.find('a:contains("(New post)")').length,
    'Link should not be created to new post when there is no new post');
});

test("test_Login.postToResponder", function() {
  ok(true, "INCOMPLETE: Test of Login.postToResponder not implemented");
});

test("test_Login.formLogout", function() {
  ok(true, "INCOMPLETE: Test of Login.formLogout not implemented");
});

test("test_Login.formLogin", function() {
  ok(true, "INCOMPLETE: Test of Login.formLogin not implemented");
});

test("test_Login.goToNextPendingGame", function() {
  Env.window.location.href = "/ui/game.html?game=1";
  Api.gameNavigation = {
    'load_status': 'ok',
    'nextGameId': 7,
  };

  Login.goToNextPendingGame();
  notEqual(Env.window.location.href, null, "The page has been redirected");
  if (Env.window.location.href !== null && Env.window.location.href !== undefined)
  {
    ok(Env.window.location.href.match(/game\.html\?game=7/),
      "The page has been redirected to the next game");
  }
});

test("test_Login.goToNextPendingGame_no_next_game", function() {
  Env.window.location.href = "/ui/game.html?game=1";

  Api.gameNavigation = {
    'load_status': 'ok',
    'nextGameId': null,
  };

  Login.goToNextPendingGame();
  notEqual(Env.window.location.href, null, "The page has been redirected");
  if (Env.window.location.href !== null && Env.window.location.href !== undefined)
  {
    ok(Env.window.location.href.match(/\/ui(\/(index\.html)?)?$/),
      "The page has been redirected to the Overview page");
  }
});
