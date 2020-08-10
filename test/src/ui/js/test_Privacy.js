module("Privacy", {
  'setup': function() {
    BMTestUtils.PrivacyPre = BMTestUtils.getAllElements();

    // Override Env.getParameterByName to get verification parameters
    BMTestUtils.overrideGetParameterByName();

    // Create the getting_started_page div so functions have something to modify
    if (document.getElementById('privacy_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'privacy_page', }));
    }
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JS objects
    delete Privacy.page;

    // Page elements
    $('#privacy_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.restoreGetParameterByName();

    BMTestUtils.restoreGetParameterByName();

    // Fail if any other elements were added or removed
    BMTestUtils.PrivacyPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.PrivacyPost, BMTestUtils.PrivacyPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Privacy module has been loaded
test("test_Privacy_is_loaded", function(assert) {
  assert.ok(Privacy, "The Privacy namespace exists");
});

test("test_Privacy.showLoggedInPage", function(assert) {
  BMTestUtils.setupFakeLogin();

  Privacy.showLoggedInPage();
  var node = document.getElementById('privacy_page');
  assert.equal(node.nodeName, "DIV",
        "#privacy_page is a div after showLoggedInPage() is called");

  BMTestUtils.cleanupFakeLogin();
});

test("test_Privacy.showLoggedOutPage", function(assert) {
  Privacy.showLoggedOutPage();
  var node = document.getElementById('privacy_page');
  assert.equal(node.nodeName, "DIV",
        "#privacy_page is a div after showLoggedOutPage() is called");
});

test("test_Privacy.showPage", function(assert) {
  Privacy.showPage();
  var node = document.getElementById('privacy_page');
  assert.equal(node.nodeName, "DIV",
        "#privacy_page is a div after showPage() is called");
});

test("test_Privacy.bodyText", function(assert) {
  var node = Privacy.bodyText();
  assert.equal(node[0].nodeName, "DIV", "bodyText returns a div");
});

test("test_Privacy.generalInfo", function(assert) {
  var node = Privacy.generalInfo();
  assert.equal(node[0].nodeName, "DIV", "generalInfo returns a div");
});

test("test_Privacy.tableOfContents", function(assert) {
  var node = Privacy.generalInfo();
  assert.equal(node[0].nodeName, "DIV", "tableOfContents returns a DIV");
  assert.equal(node[0].childNodes[0].nodeName, "H1", "toc start with an H1");
  assert.equal(node[0].childNodes[1].nodeName, "UL", "toc contain a UL");
});

test("test_Privacy.content", function(assert) {
  var node = Privacy.content();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
});

test("test_Privacy.personalDataContent", function(assert) {
  var node = Privacy.personalDataContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H2", "content contains a H2");
});

test("test_Privacy.cookiesContent", function(assert) {
  var node = Privacy.cookiesContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H2", "content contains a H2");
});

test("test_Privacy.commentsContent", function(assert) {
  var node = Privacy.commentsContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H2", "content contains a H2");
});

test("test_Privacy.retainContent", function(assert) {
  var node = Privacy.retainContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H2", "content contains a H2");
});

test("test_Privacy.rightsContent", function(assert) {
  var node = Privacy.rightsContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H2", "content contains a H2");
});
