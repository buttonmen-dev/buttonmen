module("Newuser", {
  'setup': function() {
    BMTestUtils.NewuserPre = BMTestUtils.getAllElements();

    // Create the newuser_page div so functions have something to modify
    if (document.getElementById('newuser_page') == null) {
      $('body').append($('<div>', {'id': 'newuser_page', }));
    }
  },
  'teardown': function() {

    // Delete all elements we expect this module to create

    // JS objects
    delete Newuser.page;
    delete Newuser.form;

    // Page elements
    $('#newuser_page').remove();
    $('#newuser_page').empty();

    BMTestUtils.deleteEnvMessage();

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
  Newuser.showNewuserPage();
  var item = document.getElementById('newuser_page');
  equal(item.nodeName, "DIV",
        "#newuser_page is a div after showNewuserPage() is called");
  start();
});

asyncTest("test_Newuser.showNewuserPage_logged_in", function() {

  BMTestUtils.setupFakeLogin();

  Newuser.showNewuserPage();
  var item = document.getElementById('newuser_page');
  equal(item.nodeName, "DIV",
        "#newuser_page is a div after showNewuserPage() is called");
  start();

  BMTestUtils.cleanupFakeLogin();
});

asyncTest("test_Newuser.showNewuserPage_no_page_element", function() {

  // Remove page element to make sure the function readds it
  $('#newuser_page').remove();
  $('#newuser_page').empty();

  Newuser.showNewuserPage();
  var item = document.getElementById('newuser_page');
  equal(item.nodeName, "DIV",
        "#newuser_page is a div after showNewuserPage() is called");
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

test("test_Newuser.setCreateUserSuccessMessage", function() {
  Newuser.setCreateUserSuccessMessage(
    'test invocation succeeded',
    { }
  );
  equal(Env.message.type, 'success', "set Env.message to a successful type");
});
