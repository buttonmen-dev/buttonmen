module("Login", {
  'setup': function() {
    BMTestUtils.LoginPre = BMTestUtils.getAllElements();
  },
  'teardown': function() {

    // Delete all elements we expect this module to create
    BMTestUtils.deleteEnvMessage();
    delete Api.gameNavigation;
    delete Env.window.location.href;

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

test("test_Login.postToResponder", function() {
  ok(true, "INCOMPLETE: Test of Login.postToResponder not implemented");
});

test("test_Login.formLogout", function() {
  ok(true, "INCOMPLETE: Test of Login.formLogout not implemented");
});

test("test_Login.formLogin", function() {
  ok(true, "INCOMPLETE: Test of Login.formLogin not implemented");
});

asyncTest("test_Login.goToNextPendingGame", function() {
  // Using similar logic to test_Game.formChooseSwingActive for the async call
  $.ajaxSetup({ async: false });
  Login.goToNextPendingGame();
  notEqual(Env.window.location.href, null, "The page has been redirected");
  if (Env.window.location.href !== null && Env.window.location.href !== undefined)
  {
    ok(Env.window.location.href.match("game\\.html\\?game=7"),
      "The page has been redirected to the next game");
  }
  $.ajaxSetup({ async: true });
  start();
});
