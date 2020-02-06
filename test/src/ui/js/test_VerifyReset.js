module("VerifyReset", {
  'setup': function() {
    BMTestUtils.VerifyResetPre = BMTestUtils.getAllElements();

    // Override Env.getParameterByName to get verification parameters
    BMTestUtils.overrideGetParameterByName();

    // Create the verifyreset_page div so functions have something to modify
    if (document.getElementById('verifyreset_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'verifyreset_page', }));
    }

    Login.pageModule = { 'bodyDivId': 'verifyreset_page' };
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JS objects
    delete VerifyReset.page;
    delete VerifyReset.form;
    delete VerifyReset.justCreatedAccount;

    Login.pageModule = null;

    // Page elements
    $('#verifyreset_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.restoreGetParameterByName();

    // Fail if any other elements were added or removed
    BMTestUtils.VerifyResetPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.VerifyResetPost, BMTestUtils.VerifyResetPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the VerifyReset module has been loaded
test("test_VerifyReset_is_loaded", function(assert) {
  assert.ok(VerifyReset, "The VerifyReset namespace exists");
});

test("test_VerifyReset.showLoggedInPage", function(assert) {
  BMTestUtils.setupFakeLogin();

  VerifyReset.showLoggedInPage();
  var item = document.getElementById('verifyreset_page');
  assert.equal(item.nodeName, "DIV",
        "#verifyreset_page is a div after showLoggedInPage() is called");

  BMTestUtils.cleanupFakeLogin();
});

test("test_VerifyReset.showLoggedOutPage", function(assert) {
  VerifyReset.showLoggedOutPage();
  var item = document.getElementById('verifyreset_page');
  assert.equal(item.nodeName, "DIV",
        "#verifyreset_page is a div after showLoggedOutPage() is called");
});

test("test_VerifyReset.arrangePage", function(assert) {
  VerifyReset.page = $('<div>');
  VerifyReset.page.append($('<p>', { 'text': 'hi world', }));
  VerifyReset.page.append($('<a>', { 'class': 'pseudoLink', }));
  VerifyReset.arrangePage();
  var pseudoLink = $('a.pseudoLink');
  assert.equal(pseudoLink.length, 1, 'There should be one pseudoLink on the page.');
});

test("test_VerifyReset.actionLoggedIn", function(assert) {
  VerifyReset.actionLoggedIn();
  assert.equal(VerifyReset.form, null, "The logged in action should not use a form");
});

test("test_VerifyReset.actionResetPassword", function(assert) {
  VerifyReset.actionResetPassword();
  assert.equal(VerifyReset.form, VerifyReset.formResetPassword, "The ResetPassword action should use the ResetPassword form");
});

test("test_VerifyReset.formResetPassword", function(assert) {
  stop();
  VerifyReset.activity.playerId = 1;
  VerifyReset.activity.playerKey = 'beadedfacade';
  VerifyReset.actionResetPassword();
  $('#verifyreset_password').val('foobar');
  $('#verifyreset_password_confirm').val('foobar');
  $.ajaxSetup({ async: false });
  $('#verifyreset_action_button').trigger('click');
  $.ajaxSetup({ async: true });
  assert.equal(Env.message.type, "success",
    "VerifyReset action succeeded when expected arguments were set");
  start();
});

test("test_VerifyReset.addLoggedInPage", function(assert) {
  VerifyReset.page = $('<div>');
  VerifyReset.addLoggedInPage();
  assert.ok(VerifyReset.page.html().match("you are already logged in"), "'Already logged in' message should appear when logged in");
});

test("test_VerifyReset.setResetPasswordSuccessMessage", function(assert) {
  VerifyReset.setResetPasswordSuccessMessage(
    'test invocation succeeded',
    { }
  );
  assert.equal(Env.message.type, 'success', "set Env.message to a successful type");
});
