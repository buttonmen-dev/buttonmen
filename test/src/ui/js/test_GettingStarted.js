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

test("test_GettingStarted.loggedOutBodyText", function(assert) {
  var text = GettingStarted.loggedOutBodyText();
  assert.equal(text[0].nodeName, "DIV", "loggedOutBodyText returns a div");
});

test("test_GettingStarted.loggedOutInfo", function(assert) {
  var text = GettingStarted.loggedOutInfo();
  assert.equal(text[0].nodeName, "DIV", "loggedOutGeneralInfo returns a div");
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

test("test_GettingStarted.tableOfContents", function(assert) {
  var node = GettingStarted.tableOfContents();
  assert.equal(node[0].nodeName, "UL", "tableOfContents returns a DIV");
  assert.equal(node[0].childNodes[0].nodeName, "LI", "toc start with an LI");
  assert.equal(node[0].childNodes[1].nodeName, "LI", "toc contain a LI");
});

test("test_GettingStarted.buttonmenLinks", function(assert) {
  var node = GettingStarted.buttonmenLinks();
  assert.equal(node[0].nodeName, "LI", "buttonmenLinks returns a LI");
  assert.equal(node[0].childNodes[0].nodeName, "A", "links start with an A");
  assert.equal(node[0].childNodes[1].nodeName, "UL", "links contain a UL");
});

test("test_GettingStarted.siteLinks", function(assert) {
  var node = GettingStarted.siteLinks();
  assert.equal(node[0].nodeName, "LI", "siteLinks returns a LI");
  assert.equal(node[0].childNodes[0].nodeName, "A", "links start with an A");
  assert.equal(node[0].childNodes[1].nodeName, "UL", "links contain a UL");
});

test("test_GettingStarted.whatNowLinks", function(assert) {
  var node = GettingStarted.whatNowLinks();
  assert.equal(node[0].nodeName, "LI", "whatNowLinks returns a LI");
  assert.equal(node[0].childNodes[0].nodeName, "A", "links start with an A");
});

test("test_GettingStarted.content", function(assert) {
  var node = GettingStarted.content();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
});

test("test_GettingStarted.buttonmenContent", function(assert) {
  var node = GettingStarted.buttonmenContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H2", "content contains a H2");
});

test("test_GettingStarted.basicRulesContent", function(assert) {
  var node = GettingStarted.basicRulesContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});

test("test_GettingStarted.strategyContent", function(assert) {
  var node = GettingStarted.strategyContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});

test("test_GettingStarted.siteContent", function(assert) {
  var node = GettingStarted.siteContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H2", "content contains a H2");
});

test("test_GettingStarted.joinGameContent", function(assert) {
  var node = GettingStarted.joinGameContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});

test("test_GettingStarted.buttonProgressionContent", function(assert) {
  var node = GettingStarted.buttonProgressionContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});

test("test_GettingStarted.startGameContent", function(assert) {
  var node = GettingStarted.startGameContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});

test("test_GettingStarted.profileContent", function(assert) {
  var node = GettingStarted.profileContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});

test("test_GettingStarted.preferencesContent", function(assert) {
  var node = GettingStarted.preferencesContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});

test("test_GettingStarted.whatNowContent", function(assert) {
  var node = GettingStarted.whatNowContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H2", "content contains a H2");
});
