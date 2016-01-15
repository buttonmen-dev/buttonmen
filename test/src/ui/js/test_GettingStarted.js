module("GettingStarted", {
  'setup': function() {
    BMTestUtils.GettingStartedPre = BMTestUtils.getAllElements();

    // Override Env.getParameterByName to get verification parameters
    BMTestUtils.overrideGetParameterByName();

    // Create the getting_started_page div so functions have something to modify
    if (document.getElementById('getting_started_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'getting_started_page', }));
    }
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JS objects
    delete GettingStarted.page;

    // Page elements
    $('#getting_started_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.restoreGetParameterByName();

    BMTestUtils.restoreGetParameterByName();

    // Fail if any other elements were added or removed
    BMTestUtils.GettingStartedPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.GettingStartedPost, BMTestUtils.GettingStartedPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the GettingStarted module has been loaded
test("test_GettingStarted_is_loaded", function(assert) {
  assert.ok(GettingStarted, "The GettingStarted namespace exists");
});

test("test_GettingStarted.showLoggedInPage", function(assert) {
  BMTestUtils.setupFakeLogin();

  GettingStarted.showLoggedInPage();
  var item = document.getElementById('getting_started_page');
  assert.equal(item.nodeName, "DIV",
        "#getting_started_page is a div after showLoggedInPage() is called");

  BMTestUtils.cleanupFakeLogin();
});

test("test_GettingStarted.showLoggedOutPage", function(assert) {
  GettingStarted.showLoggedOutPage();
  var item = document.getElementById('getting_started_page');
  assert.equal(item.nodeName, "DIV",
        "#getting_started_page is a div after showLoggedOutPage() is called");
});

test("test_GettingStarted.showPage", function(assert) {
  GettingStarted.showPage();
  var item = document.getElementById('getting_started_page');
  assert.equal(item.nodeName, "DIV",
        "#getting_started_page is a div after showPage() is called");
});

test("test_GettingStarted.bodyText", function(assert) {
  var text = GettingStarted.bodyText();
  assert.equal(text[0].nodeName, "DIV", "bodyText returns a div");
});

test("test_GettingStarted.generalInfo", function(assert) {
  var text = GettingStarted.generalInfo();
  assert.equal(text[0].nodeName, "DIV", "generalInfo returns a div");
});
