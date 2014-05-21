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
  Newuser.actionCreateUser();
  $('#newuser_username').val('tester5');
  $('#newuser_password').val('testpass');
  $('#newuser_password_confirm').val('testpass');
  $('#newuser_email').val('tester5@example.com');
  $('#newuser_email_confirm').val('tester5@example.com');
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  equal(Env.message.type, "success",
    "Newuser action succeeded when expected arguments were set");
  start();
});

asyncTest("test_Newuser.formCreateUser_no_username", function() {
  Newuser.actionCreateUser();
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  equal(Env.message.type, "error",
    "Newuser action fails when username is not set");
  start();
});

asyncTest("test_Newuser.formCreateUser_invalid_username", function() {
  Newuser.actionCreateUser();
  $('#newuser_username').val('test-8');
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  equal(Env.message.type, "error",
    "Newuser action fails when username is not set");
  equal(Env.message.text,
    "Usernames may only contain letters, numbers, and underscores",
    "Newuser shows reasonable error when username is invalid");
  start();
});

asyncTest("test_Newuser.formCreateUser_no_password", function() {
  Newuser.actionCreateUser();
  $('#newuser_username').val('tester5');
  $('#newuser_email').val('tester@example.com');
  $('#newuser_email_confirm').val('tester@example.com');
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  equal(Env.message.type, "error",
    "Newuser action fails when password is not set");
  equal(Env.message.text, "You need to set a password",
    "Newuser show reasonable error when password is not set");
  start();
});

asyncTest("test_Newuser.formCreateUser_no_password_confirm", function() {
  Newuser.actionCreateUser();
  $('#newuser_username').val('tester5');
  $('#newuser_password').val('testpass');
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  equal(Env.message.type, "error",
    "Newuser action fails when confirmation password is not set");
  start();
});

asyncTest("test_Newuser.formCreateUser_password_mismatch", function() {
  Newuser.actionCreateUser();
  $('#newuser_username').val('tester5');
  $('#newuser_password').val('testpass');
  $('#newuser_password_confirm').val('testpass2');
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  equal(Env.message.type, "error",
    "Newuser action fails when confirmation password does not match");
  start();
});

asyncTest("test_Newuser.formCreateUser_no_email", function() {
  Newuser.actionCreateUser();
  $('#newuser_username').val('tester5');
  $('#newuser_password').val('testpass');
  $('#newuser_password_confirm').val('testpass');
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  equal(Env.message.type, "error",
    "Newuser action fails when email is not provided not match");
  start();
});

asyncTest("test_Newuser.formCreateUser_password_mismatch", function() {
  Newuser.actionCreateUser();
  $('#newuser_username').val('tester5');
  $('#newuser_password').val('testpass');
  $('#newuser_password_confirm').val('testpass');
  $('#newuser_email').val('tester5@example.com');
  $('#newuser_email_confirm').val('tester5@example.con');
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  equal(Env.message.type, "error",
    "Newuser action fails when confirmation e-mail does not match");
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
