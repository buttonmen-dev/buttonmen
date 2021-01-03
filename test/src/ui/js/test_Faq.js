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
  var node = document.getElementById('faq_page');
  assert.equal(node.nodeName, "DIV",
        "#faq_page is a div after showLoggedInPage() is called");

  BMTestUtils.cleanupFakeLogin();
});

test("test_Faq.showLoggedOutPage", function(assert) {
  Faq.showLoggedOutPage();
  var node = document.getElementById('faq_page');
  assert.equal(node.nodeName, "DIV",
        "#faq_page is a div after showLoggedOutPage() is called");
});

test("test_Faq.showPage", function(assert) {
  Faq.showPage();
  var node = document.getElementById('faq_page');
  assert.equal(node.nodeName, "DIV",
        "#faq_page is a div after showPage() is called");
});

test("test_Faq.bodyText", function(assert) {
  var node = Faq.bodyText();
  assert.equal(node[0].nodeName, "DIV", "bodyText returns a div");
});

test("test_Faq.generalInfo", function(assert) {
  var node = Faq.generalInfo();
  assert.equal(node[0].nodeName, "DIV", "generalInfo returns a div");
});

test("test_Faq.tableOfContents", function(assert) {
  var node = Faq.generalInfo();
  assert.equal(node[0].nodeName, "DIV", "tableOfContents returns a DIV");
  assert.equal(node[0].childNodes[0].nodeName, "H1", "toc start with an H1");
  assert.equal(node[0].childNodes[1].nodeName, "UL", "toc contain a UL");
});

test("test_Faq.gameplayLinks", function(assert) {
  var node = Faq.gameplayLinks();
  assert.equal(node[0].nodeName, "LI", "gameplayLinks returns a LI");
  assert.equal(node[0].childNodes[0].nodeName, "A", "links start with an A");
  assert.equal(node[0].childNodes[1].nodeName, "UL", "links contain a UL");
});

test("test_Faq.userprefsLinks", function(assert) {
  var node = Faq.userprefsLinks();
  assert.equal(node[0].nodeName, "LI", "userprefsLinks returns a LI");
  assert.equal(node[0].childNodes[0].nodeName, "A", "links start with an A");
  assert.equal(node[0].childNodes[1].nodeName, "UL", "links contain a UL");
});

test("test_Faq.forumLinks", function(assert) {
  var node = Faq.forumLinks();
  assert.equal(node[0].nodeName, "LI", "forumLinks returns a LI");
  assert.equal(node[0].childNodes[0].nodeName, "A", "links start with an A");
  assert.equal(node[0].childNodes[1].nodeName, "UL", "links contain a UL");
});

test("test_Faq.unansweredLink", function(assert) {
  var node = Faq.unansweredLink();
  assert.equal(node[0].nodeName, "LI", "link is a LI");
});

test("test_Faq.content", function(assert) {
  var node = Faq.content();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
});

test("test_Faq.gameplayContent", function(assert) {
  var node = Faq.gameplayContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H2", "content contains a H2");
});

test("test_Faq.defaultAttackContent", function(assert) {
  var node = Faq.defaultAttackContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});

test("test_Faq.buttonSpecialContent", function(assert) {
  var node = Faq.buttonSpecialContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});

test("test_Faq.flyingSquirrelContent", function(assert) {
  var node = Faq.flyingSquirrelContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});

test("test_Faq.japaneseBeetleContent", function(assert) {
  var node = Faq.japaneseBeetleContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});

test("test_Faq.userprefsContent", function(assert) {
  var node = Faq.userprefsContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H2", "content contains a H2");
});

test("test_Faq.monitorContent", function(assert) {
  var node = Faq.monitorContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});

test("test_Faq.fireOvershootingContent", function(assert) {
  var node = Faq.fireOvershootingContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});

test("test_Faq.forumContent", function(assert) {
  var node = Faq.forumContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H2", "content contains a H2");
});

test("test_Faq.forumLinkContent", function(assert) {
  var node = Faq.forumLinkContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});

test("test_Faq.unansweredContent", function(assert) {
  var node = Faq.unansweredContent();
  assert.equal(node[0].nodeName, "#document-fragment",
    "content contains a document fragment");
  assert.equal(node[0].childNodes[0].nodeName, "A", "content starts with an A");
  assert.equal(node[0].childNodes[1].nodeName, "H3", "content contains a H3");
});
