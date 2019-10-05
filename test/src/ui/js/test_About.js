module("About", {
  'setup': function() {
    BMTestUtils.AboutPre = BMTestUtils.getAllElements();

    // Override Env.getParameterByName to get verification parameters
    BMTestUtils.overrideGetParameterByName();

    // Create the about_page div so functions have something to modify
    if (document.getElementById('about_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'about_page', }));
    }
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JS objects
    delete About.page;

    // Page elements
    $('#about_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.restoreGetParameterByName();

    BMTestUtils.restoreGetParameterByName();

    // Fail if any other elements were added or removed
    BMTestUtils.AboutPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.AboutPost, BMTestUtils.AboutPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the About module has been loaded
test("test_About_is_loaded", function(assert) {
  assert.ok(About, "The About namespace exists");
});

test("test_About.showLoggedInPage", function(assert) {
  BMTestUtils.setupFakeLogin();

  About.showLoggedInPage();
  var item = document.getElementById('about_page');
  assert.equal(item.nodeName, "DIV",
        "#about_page is a div after showLoggedInPage() is called");

  BMTestUtils.cleanupFakeLogin();
});

test("test_About.showLoggedOutPage", function(assert) {
  About.showLoggedOutPage();
  var item = document.getElementById('about_page');
  assert.equal(item.nodeName, "DIV",
        "#about_page is a div after showLoggedOutPage() is called");
});

test("test_About.showPage", function(assert) {
  About.showPage();
  var item = document.getElementById('about_page');
  assert.equal(item.nodeName, "DIV",
        "#about_page is a div after showPage() is called");
});

test("test_About.bodyText", function(assert) {
  var text = About.bodyText();
  assert.equal(text[0].nodeName, "DIV", "bodyText returns a div");
});

test("test_About.generalInfo", function(assert) {
  var text = About.generalInfo();
  assert.equal(text[0].nodeName, "DIV", "generalInfo returns a div");
});
