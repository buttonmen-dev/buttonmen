module("ForgotPassword", {
  'setup': function() {
    BMTestUtils.ForgotPasswordPre = BMTestUtils.getAllElements();

    // Create the forgot_password_page div so functions have something to modify
    if (document.getElementById('forgot_password_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'forgot_password_page', }));
    }

    Login.pageModule = { 'bodyDivId': 'forgot_password_page' };
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JS objects
    delete ForgotPassword.page;
    delete ForgotPassword.form;
    delete ForgotPassword.justCreatedAccount;

    Login.pageModule = null;

    // Page elements
    $('#forgot_password_page').remove();

    BMTestUtils.deleteEnvMessage();

    // Fail if any other elements were added or removed
    BMTestUtils.ForgotPasswordPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.ForgotPasswordPost, BMTestUtils.ForgotPasswordPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the ForgotPassword module has been loaded
test("test_ForgotPassword_is_loaded", function(assert) {
  assert.ok(ForgotPassword, "The ForgotPassword namespace exists");
});

test("test_ForgotPassword.showLoggedInPage", function(assert) {
  BMTestUtils.setupFakeLogin();

  ForgotPassword.showLoggedInPage();
  var item = document.getElementById('forgot_password_page');
  assert.equal(item.nodeName, "DIV",
        "#forgot_password_page is a div after showLoggedInPage() is called");

  BMTestUtils.cleanupFakeLogin();
});

test("test_ForgotPassword.showLoggedOutPage", function(assert) {
  ForgotPassword.showLoggedOutPage();
  var item = document.getElementById('forgot_password_page');
  assert.equal(item.nodeName, "DIV",
        "#forgot_password_page is a div after showLoggedOutPage() is called");
});

test("test_ForgotPassword.arrangePage", function(assert) {
  ForgotPassword.page = $('<div>');
  ForgotPassword.page.append($('<p>', { 'text': 'hi world', }));
  ForgotPassword.page.append($('<a>', { 'class': 'pseudoLink', }));
  ForgotPassword.arrangePage();
  var pseudoLink = $('a.pseudoLink');
  assert.equal(pseudoLink.length, 1, 'There should be one pseudoLink on the page.');
});

test("test_ForgotPassword.actionLoggedIn", function(assert) {
  ForgotPassword.actionLoggedIn();
  assert.equal(ForgotPassword.form, null, "The logged in action should not use a form");
});

test("test_ForgotPassword.actionRequestReset", function(assert) {
  ForgotPassword.actionRequestReset();
  assert.equal(ForgotPassword.form, ForgotPassword.formRequestReset, "The RequestReset action should use the RequestReset form");
});

test("test_ForgotPassword.formRequestReset", function(assert) {
  stop();
  ForgotPassword.actionRequestReset();
  $('#forgot_password_username').val('tester5');
  $.ajaxSetup({ async: false });
  $('#forgot_password_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  assert.equal(Env.message.type, "success",
    "ForgotPassword action succeeded when expected arguments were set");
  start();
});

test("test_ForgotPassword.addLoggedInPage", function(assert) {
  ForgotPassword.page = $('<div>');
  ForgotPassword.addLoggedInPage();
  assert.ok(ForgotPassword.page.html().match("you are already logged in"), "'Already logged in' message should appear when logged in");
});

test("test_ForgotPassword.setRequestedResetSuccessMessage", function(assert) {
  ForgotPassword.setRequestedResetSuccessMessage(
    'test invocation succeeded',
    { }
  );
  assert.equal(Env.message.type, 'success', "set Env.message to a successful type");
});
