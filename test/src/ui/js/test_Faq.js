module("Faq", {
  'setup': function() {
    BMTestUtils.FaqPre = BMTestUtils.getAllElements();

    // Override Env.getParameterByName to get verification parameters
    BMTestUtils.overrideGetParameterByName();

    // Create the getting_started_page div so functions have something to modify
    if (document.getElementById('faq_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'faq_page', }));
    }
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JS objects
    delete Faq.page;

    // Page elements
    $('#faq_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.restoreGetParameterByName();

    BMTestUtils.restoreGetParameterByName();

    // Fail if any other elements were added or removed
    BMTestUtils.FaqPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.FaqPost, BMTestUtils.FaqPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Faq module has been loaded
test("test_Faq_is_loaded", function(assert) {
  assert.ok(Faq, "The Faq namespace exists");
});

test("test_Faq.showLoggedInPage", function(assert) {
  BMTestUtils.setupFakeLogin();

  Faq.showLoggedInPage();
  var item = document.getElementById('faq_page');
  assert.equal(item.nodeName, "DIV",
        "#faq_page is a div after showLoggedInPage() is called");

  BMTestUtils.cleanupFakeLogin();
});

test("test_Faq.showLoggedOutPage", function(assert) {
  Faq.showLoggedOutPage();
  var item = document.getElementById('faq_page');
  assert.equal(item.nodeName, "DIV",
        "#faq_page is a div after showLoggedOutPage() is called");
});

test("test_Faq.showPage", function(assert) {
  Faq.showPage();
  var item = document.getElementById('faq_page');
  assert.equal(item.nodeName, "DIV",
        "#faq_page is a div after showPage() is called");
});

test("test_Faq.bodyText", function(assert) {
  var text = Faq.bodyText();
  assert.equal(text[0].nodeName, "DIV", "bodyText returns a div");
});

test("test_Faq.generalInfo", function(assert) {
  var text = Faq.generalInfo();
  assert.equal(text[0].nodeName, "DIV", "generalInfo returns a div");
});
