module("Help", {
  'setup': function() {
    BMTestUtils.HelpPre = BMTestUtils.getAllElements();

    // Override Env.getParameterByName to get verification parameters
    BMTestUtils.overrideGetParameterByName();

    // Create the help_page div so functions have something to modify
    if (document.getElementById('help_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'help_page', }));
    }
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JS objects
    delete Help.page;

    // Page elements
    $('#help_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.restoreGetParameterByName();

    BMTestUtils.restoreGetParameterByName();

    // Fail if any other elements were added or removed
    BMTestUtils.HelpPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.HelpPost, BMTestUtils.HelpPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Help module has been loaded
test("test_Help_is_loaded", function(assert) {
  assert.ok(Help, "The Help namespace exists");
});

test("test_Help.showLoggedInPage", function(assert) {
  BMTestUtils.setupFakeLogin();

  Help.showLoggedInPage();
  var item = document.getElementById('help_page');
  assert.equal(item.nodeName, "DIV",
        "#help_page is a div after showLoggedInPage() is called");

  BMTestUtils.cleanupFakeLogin();
});

test("test_Help.showLoggedOutPage", function(assert) {
  Help.showLoggedOutPage();
  var item = document.getElementById('help_page');
  assert.equal(item.nodeName, "DIV",
        "#help_page is a div after showLoggedOutPage() is called");
});

test("test_Help.showPage", function(assert) {
  Help.showPage();
  var item = document.getElementById('help_page');
  assert.equal(item.nodeName, "DIV",
        "#help_page is a div after showPage() is called");
});

test("test_Help.bodyText", function(assert) {
  var text = Help.bodyText();
  assert.equal(text[0].nodeName, "DIV", "bodyText returns a div");
});

test("test_Help.generalInfo", function(assert) {
  var text = Help.generalInfo();
  assert.equal(text[0].nodeName, "DIV", "generalInfo returns a div");
});

test("test_Help.developerInfo", function(assert) {
  var text = Help.developerInfo();
  assert.equal(text[0].nodeName, "DIV", "developerInfo returns a div");
});