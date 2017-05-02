module("UserPrefs", {
  'setup': function() {
    BMTestUtils.UserPrefsPre = BMTestUtils.getAllElements();
    BMTestUtils.setupFakeLogin();

    // Create the userprefs_page div so functions have something to modify
    if (document.getElementById('userprefs_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'userprefs_page', }));
    }

    Login.pageModule = { 'bodyDivId': 'userprefs_page' };
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JS objects
    delete Api.button;
    delete Api.player;
    delete Api.user_prefs;
    delete UserPrefs.page;
    delete UserPrefs.form;

    Login.pageModule = null;

    // Page elements
    $('#userprefs_page').remove();
    // Controls added to the page by the color picker library we use
    $('.sp-container').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.UserPrefsPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.UserPrefsPost, BMTestUtils.UserPrefsPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the UserPrefs module has been loaded
test("test_UserPrefs_is_loaded", function(assert) {
  assert.ok(UserPrefs, "The UserPrefs namespace exists");
});

// The purpose of this test is to demonstrate that the flow of
// UserPrefs.showLoggedInPage() is correct for a showXPage function, namely
// that it calls an API getter with a showStatePage function as a
// callback.
//
// Accomplish this by mocking the invoked functions
test("test_UserPrefs.showLoggedInPage", function(assert) {
  expect(5);
  var cached_getter = Env.callAsyncInParallel;
  var cached_showStatePage = UserPrefs.assemblePage;
  var getterCalled = false;
  UserPrefs.assemblePage = function() {
    assert.ok(getterCalled, "Env.callAsyncInParallel is called before UserPrefs.assemblePage");
  };
  Env.callAsyncInParallel = function(scripts, callback) {
    getterCalled = true;
    assert.equal(callback, UserPrefs.assemblePage,
      "Env.callAsyncInParallel is called with UserPrefs.assemblePage as an argument");
    callback();
  };

  UserPrefs.showLoggedInPage();
  var item = document.getElementById('userprefs_page');
  assert.equal(item.nodeName, "DIV",
        "#userprefs_page is a div after showLoggedInPage() is called");

  Env.callAsyncInParallel = cached_getter;
  UserPrefs.assemblePage = cached_showStatePage;
});

test("test_UserPrefs.assemblePage", function(assert) {
  stop();
  Env.callAsyncInParallel([
    { 'func': Api.getButtonData, 'args': [ null ] },
    Api.getUserPrefsData,
  ], function() {
    UserPrefs.assemblePage();
    var htmlout = UserPrefs.page.html();
    assert.ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

test("test_UserPrefs.actionFailed", function(assert) {
  UserPrefs.actionFailed();
  assert.equal(UserPrefs.form, null, "The failing action does not set a form");
});

test("test_UserPrefs.actionSetPrefs", function(assert) {
  stop();
  Env.callAsyncInParallel([
    { 'func': Api.getButtonData, 'args': [ null ] },
    Api.getUserPrefsData,
  ], function() {
    UserPrefs.actionSetPrefs();
    var autopass_checked = $('#userprefs_autopass').prop('checked');
    assert.ok(autopass_checked,
       "The autopass button should be checked in the prefs table");
    start();
  });
});

test("test_UserPrefs.actionSetPrefsFireOvershooting", function(assert) {
  stop();
  Env.callAsyncInParallel([
    { 'func': Api.getButtonData, 'args': [ null ] },
    Api.getUserPrefsData,
  ], function() {
    UserPrefs.actionSetPrefs();
    var fire_overshooting_checked = $('#userprefs_fire_overshooting').prop('checked');
    assert.ok(!fire_overshooting_checked,
       "The fire overshooting button should not be checked in the prefs table");
    start();
  });
});

// The logic here is a little hairy: since Api.getUserPrefsData()
// takes a callback, we can use the normal asynchronous logic there.
// However, the POST done by our forms doesn't take a callback (it
// just redraws the page), so turn off asynchronous handling in
// AJAX while we test that, to make sure the test sees the return
// from the POST.
test("test_UserPrefs.formSetPrefs", function(assert) {
  stop();
  Env.callAsyncInParallel([
    { 'func': Api.getButtonData, 'args': [ null ] },
    Api.getUserPrefsData,
  ], function() {
    UserPrefs.actionSetPrefs();
    $.ajaxSetup({ async: false });
    $('#userprefs_action_button').trigger('click');
    assert.deepEqual(
      Env.message,
      {"type": "success", "text": "User details set successfully."},
      "User preferences save succeeded");
    $.ajaxSetup({ async: true });
    start();
  });
});

test("test_UserPrefs.appendToPreferencesTable", function(assert) {
  var table = $('<table>');
  var prefs = {
    'testing' : {
      'text': 'Testing',
      'type': 'checkbox',
      'checked': true,
    },
  };

  UserPrefs.appendToPreferencesTable(table, 'Test Preferences',
    'These are not real. There is no spoon.', prefs);
  var checkbox = table.find('input#userprefs_testing');

  assert.ok(checkbox.val(), 'User preference control created and populated');
});
