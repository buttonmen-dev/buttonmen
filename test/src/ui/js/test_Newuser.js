module("Newuser", {
  'setup': function() {
    BMTestUtils.NewuserPre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the newuser_page div so functions have something to modify
    if (document.getElementById('newuser_page') == null) {
      $('body').append($('<div>', {'id': 'newuser_page', }));
    }
  },
  'teardown': function() {

    // Delete all elements we expect this module to create

    // JS objects

    // Page elements
    $('#newuser_page').remove();
    $('#newuser_page').empty();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.NewuserPost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.NewuserPost, BMTestUtils.NewuserPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Newuser module has been loaded
test("test_Newuser_is_loaded", function() {
  ok(Newuser, "The Newuser namespace exists");
});

asyncTest("test_Newuser.showNewuserPage", function() {
  ok(true, "INCOMPLETE: Test of Newuser.showNewuserPage not implemented");
  start();
});

asyncTest("test_Newuser.layoutPage", function() {
  ok(true, "INCOMPLETE: Test of Newuser.layoutPage not implemented");
  start();
});

asyncTest("test_Newuser.actionLoggedIn", function() {
  ok(true, "INCOMPLETE: Test of Newuser.actionLoggedIn not implemented");
  start();
});

asyncTest("test_Newuser.actionCreateUser", function() {
  ok(true, "INCOMPLETE: Test of Newuser.actionCreateUser not implemented");
  start();
});

asyncTest("test_Newuser.formCreateUser", function() {
  ok(true, "INCOMPLETE: Test of Newuser.formCreateUser not implemented");
  start();
});

asyncTest("test_Newuser.addLoggedInPage", function() {
  ok(true, "INCOMPLETE: Test of Newuser.addLoggedInPage not implemented");
  start();
});
