module("UserPrefs", {
  'setup': function() {
    BMTestUtils.UserPrefsPre = BMTestUtils.getAllElements();
    BMTestUtils.setupFakeLogin();

    // Create the userprefs_page div so functions have something to modify
    if (document.getElementById('userprefs_page') == null) {
      $('body').append($('<div>', {'id': 'userprefs_page', }));
    }
  },
  'teardown': function() {

    // Delete all elements we expect this module to create

    // JS objects
    delete Api.button;
    delete Api.player;
    delete Api.user_prefs;

    // Page elements
    $('#userprefs_page').remove();
    $('#userprefs_page').empty();
    // Controls added to the page by the color picker library we use
    $('.sp-container').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.UserPrefsPost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.UserPrefsPost, BMTestUtils.UserPrefsPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the UserPrefs module has been loaded
test("test_UserPrefs_is_loaded", function() {
  ok(UserPrefs, "The UserPrefs namespace exists");
});

asyncTest("test_UserPrefs.showUserPrefsPage", function() {
  UserPrefs.showUserPrefsPage();
  var item = document.getElementById('userprefs_page');
  equal(item.nodeName, "DIV",
        "#userprefs_page is a div after showUserPrefsPage() is called");
  start();
});

// We're testing this synchronously, in the hope that this way qunit won't give
// up on it before it finishes loading everything from the API
test("test_UserPrefs.assemblePage", function() {
  $.ajaxSetup({ async: false });
  Api.getButtonData(function() {
    Api.getUserPrefsData(function() {
      UserPrefs.assemblePage();
      var htmlout = UserPrefs.page.html();
      ok(htmlout.length > 0,
         "The created page should have nonzero contents");
    });
  });
  $.ajaxSetup({ async: true });
});

// We're testing this synchronously, in the hope that this way qunit won't give
// up on it before it finishes loading everything from the API
test("test_UserPrefs.arrangePage", function() {
  $.ajaxSetup({ async: false });
  Api.getUserPrefsData(function() {
    UserPrefs.page = $('<div>');
    UserPrefs.page.append($('<p>', {'text': 'hi world', }));
    UserPrefs.arrangePage();
    var item = document.getElementById('userprefs_page');
    equal(item.nodeName, "DIV",
          "#userprefs_page is a div after arrangePage() is called");
  });
  $.ajaxSetup({ async: true });
});

test("test_UserPrefs.actionFailed", function() {
  UserPrefs.actionFailed();
  equal(UserPrefs.form, null, "The failing action does not set a form");
});

asyncTest("test_UserPrefs.actionSetPrefs", function() {
  Api.getButtonData(function() {
    Api.getUserPrefsData(function() {
      UserPrefs.actionSetPrefs();
      var autopass_checked = $('#userprefs_autopass').prop('checked');
      ok(autopass_checked,
         "The autopass button should be checked in the prefs table");
      start();
    });
  });
});

// The logic here is a little hairy: since Api.getUserPrefsData()
// takes a callback, we can use the normal asynchronous logic there.
// However, the POST done by our forms doesn't take a callback (it
// just redraws the page), so turn off asynchronous handling in
// AJAX while we test that, to make sure the test sees the return
// from the POST.
asyncTest("test_UserPrefs.formSetPrefs", function() {
  Api.getButtonData(function() {
    Api.getUserPrefsData(function() {
      UserPrefs.actionSetPrefs();
      $.ajaxSetup({ async: false });
      $('#userprefs_action_button').trigger('click');
      deepEqual(
        Env.message,
        {"type": "success", "text": "User details set successfully."},
        "User preferences save succeeded");
      $.ajaxSetup({ async: true });
      start();
    });
  });
});

test("test_UserPrefs.appendToPreferencesTable", function() {
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

  ok(checkbox.val(), 'User preference control created and populated');
});
