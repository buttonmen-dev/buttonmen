module("Login", {
  'setup': function() {
    BMTestUtils.LoginPre = BMTestUtils.getAllElements();
  },
  'teardown': function() {

    // Delete all elements we expect this module to create
    BMTestUtils.deleteEnvMessage();

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

test("test_Login.postToResponder", function() {
  ok(true, "INCOMPLETE: Test of Login.postToResponder not implemented");
});

test("test_Login.formLogout", function() {
  ok(true, "INCOMPLETE: Test of Login.formLogout not implemented");
});

test("test_Login.formLogin", function() {
  ok(true, "INCOMPLETE: Test of Login.formLogin not implemented");
});
