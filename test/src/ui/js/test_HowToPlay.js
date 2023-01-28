module("HowToPlay", {
  'setup': function() {
    BMTestUtils.HowToPlayPre = BMTestUtils.getAllElements();

    // Override Env.getParameterByName to get verification parameters
    BMTestUtils.overrideGetParameterByName();

    // Create the howtoplay_page div so functions have something to modify
    if (document.getElementById('howtoplay_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'howtoplay_page', }));
    }
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JS objects
    delete HowToPlay.page;

    // Page elements
    $('#howtoplay_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.restoreGetParameterByName();

    BMTestUtils.restoreGetParameterByName();

    // Fail if any other elements were added or removed
    BMTestUtils.HowToPlayPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.HowToPlayPost, BMTestUtils.HowToPlayPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the HowToPlay module has been loaded
test("test_HowToPlay_is_loaded", function(assert) {
  assert.ok(HowToPlay, "The HowToPlay namespace exists");
});

test("test_HowToPlay.showLoggedInPage", function(assert) {
  BMTestUtils.setupFakeLogin();

  HowToPlay.showLoggedInPage();
  var item = document.getElementById('howtoplay_page');
  assert.equal(item.nodeName, "DIV",
        "#howtoplay_page is a div after showLoggedInPage() is called");

  BMTestUtils.cleanupFakeLogin();
});

test("test_HowToPlay.showLoggedOutPage", function(assert) {
  HowToPlay.showLoggedOutPage();
  var item = document.getElementById('howtoplay_page');
  assert.equal(item.nodeName, "DIV",
        "#howtoplay_page is a div after showLoggedOutPage() is called");
});

test("test_HowToPlay.showPage", function(assert) {
  HowToPlay.showPage();
  var item = document.getElementById('howtoplay_page');
  assert.equal(item.nodeName, "DIV",
        "#howtoplay_page is a div after showPage() is called");
});

test("test_HowToPlay.bodyText", function(assert) {
  var text = HowToPlay.bodyText();
  assert.equal(text[0].nodeName, "DIV", "bodyText returns a div");
});

test("test_HowToPlay.tableOfContents", function(assert) {
  var text = HowToPlay.tableOfContents();
  assert.equal(text[0].nodeName, "UL", "tableOfContents returns a list");
});

test("test_HowToPlay.info", function(assert) {
  var text = HowToPlay.info();
  assert.equal(text[0].nodeName, "DIV", "info returns a div");
});