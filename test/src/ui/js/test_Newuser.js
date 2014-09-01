module("Newuser", {
  'setup': function() {
    BMTestUtils.NewuserPre = BMTestUtils.getAllElements();

    // Create the newuser_page div so functions have something to modify
    if (document.getElementById('newuser_page') == null) {
      $('body').append($('<div>', {'id': 'newuser_page', }));
    }

    Login.pageModule = { 'bodyDivId': 'newuser_page' };
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JS objects
    delete Newuser.page;
    delete Newuser.form;
    delete Newuser.justCreatedAccount;

    Login.pageModule = null;

    // Page elements
    $('#newuser_page').remove();
    $('#newuser_page').empty();
    $('#footer_separator').remove();
    $('#footer').remove();
    $('#footer').empty();

    BMTestUtils.deleteEnvMessage();

    // Fail if any other elements were added or removed
    BMTestUtils.NewuserPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.NewuserPost, BMTestUtils.NewuserPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Newuser module has been loaded
test("test_Newuser_is_loaded", function(assert) {
  assert.ok(Newuser, "The Newuser namespace exists");
});

test("test_Newuser.showLoggedInPage", function(assert) {
  BMTestUtils.setupFakeLogin();

  Newuser.showLoggedInPage();
  var item = document.getElementById('newuser_page');
  assert.equal(item.nodeName, "DIV",
        "#newuser_page is a div after showLoggedInPage() is called");

  BMTestUtils.cleanupFakeLogin();
});

test("test_Newuser.showLoggedOutPage", function(assert) {
  Newuser.showLoggedOutPage();
  var item = document.getElementById('newuser_page');
  assert.equal(item.nodeName, "DIV",
        "#newuser_page is a div after showLoggedOutPage() is called");
});

test("test_Newuser.arrangePage", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Newuser.arrangePage not implemented");
});

test("test_Newuser.actionLoggedIn", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Newuser.actionLoggedIn not implemented");
});

test("test_Newuser.actionCreateUser", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Newuser.actionCreateUser not implemented");
});

test("test_Newuser.formCreateUser", function(assert) {
  stop();
  Newuser.actionCreateUser();
  $('#newuser_username').val('tester5');
  $('#newuser_password').val('testpass');
  $('#newuser_password_confirm').val('testpass');
  $('#newuser_email').val('tester5@example.com');
  $('#newuser_email_confirm').val('tester5@example.com');
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  assert.equal(Env.message.type, "success",
    "Newuser action succeeded when expected arguments were set");
  start();
});

test("test_Newuser.formCreateUser_no_username", function(assert) {
  stop();
  Newuser.actionCreateUser();
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  assert.equal(Env.message.type, "error",
    "Newuser action fails when username is not set");
  start();
});

test("test_Newuser.formCreateUser_invalid_username", function(assert) {
  stop();
  Newuser.actionCreateUser();
  $('#newuser_username').val('test-8');
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  assert.equal(Env.message.type, "error",
    "Newuser action fails when username is not set");
  assert.equal(Env.message.text,
    "Usernames may only contain letters, numbers, and underscores",
    "Newuser shows reasonable error when username is invalid");
  start();
});

test("test_Newuser.formCreateUser_no_password", function(assert) {
  stop();
  Newuser.actionCreateUser();
  $('#newuser_username').val('tester5');
  $('#newuser_email').val('tester@example.com');
  $('#newuser_email_confirm').val('tester@example.com');
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  assert.equal(Env.message.type, "error",
    "Newuser action fails when password is not set");
  assert.equal(Env.message.text, "You need to set a password",
    "Newuser show reasonable error when password is not set");
  start();
});

test("test_Newuser.formCreateUser_no_password_confirm", function(assert) {
  stop();
  Newuser.actionCreateUser();
  $('#newuser_username').val('tester5');
  $('#newuser_password').val('testpass');
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  assert.equal(Env.message.type, "error",
    "Newuser action fails when confirmation password is not set");
  start();
});

test("test_Newuser.formCreateUser_password_mismatch", function(assert) {
  stop();
  Newuser.actionCreateUser();
  $('#newuser_username').val('tester5');
  $('#newuser_password').val('testpass');
  $('#newuser_password_confirm').val('testpass2');
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  assert.equal(Env.message.type, "error",
    "Newuser action fails when confirmation password does not match");
  start();
});

test("test_Newuser.formCreateUser_no_email", function(assert) {
  stop();
  Newuser.actionCreateUser();
  $('#newuser_username').val('tester5');
  $('#newuser_password').val('testpass');
  $('#newuser_password_confirm').val('testpass');
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  assert.equal(Env.message.type, "error",
    "Newuser action fails when email is not provided not match");
  start();
});

test("test_Newuser.formCreateUser_password_mismatch", function(assert) {
  stop();
  Newuser.actionCreateUser();
  $('#newuser_username').val('tester5');
  $('#newuser_password').val('testpass');
  $('#newuser_password_confirm').val('testpass');
  $('#newuser_email').val('tester5@example.com');
  $('#newuser_email_confirm').val('tester5@example.con');
  $.ajaxSetup({ async: false });
  $('#newuser_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  assert.equal(Env.message.type, "error",
    "Newuser action fails when confirmation e-mail does not match");
  start();
});

test("test_Newuser.addLoggedInPage", function(assert) {
  assert.ok(true, "INCOMPLETE: Test of Newuser.addLoggedInPage not implemented");
});

test("test_Newuser.setCreateUserSuccessMessage", function(assert) {
  Newuser.setCreateUserSuccessMessage(
    'test invocation succeeded',
    { }
  );
  assert.equal(Env.message.type, 'success', "set Env.message to a successful type");
});
