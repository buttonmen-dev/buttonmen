module("Skills", {
  'setup': function() {
    BMTestUtils.SkillsPre = BMTestUtils.getAllElements();

    // Override Env.getParameterByName to get verification parameters
    BMTestUtils.overrideGetParameterByName();

    // Create the skills_page div so functions have something to modify
    if (document.getElementById('skills_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'skills_page', }));
    }
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JS objects
    delete Skills.page;

    // Page elements
    $('#skills_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.restoreGetParameterByName();

    BMTestUtils.restoreGetParameterByName();

    // Fail if any other elements were added or removed
    BMTestUtils.SkillsPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.SkillsPost, BMTestUtils.SkillsPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Skills module has been loaded
test("test_Skills_is_loaded", function(assert) {
  assert.ok(Skills, "The Skills namespace exists");
});

test("test_Skills.showLoggedInPage", function(assert) {
  BMTestUtils.setupFakeLogin();

  Skills.showLoggedInPage();
  var item = document.getElementById('skills_page');
  assert.equal(item.nodeName, "DIV",
        "#skills_page is a div after showLoggedInPage() is called");

  BMTestUtils.cleanupFakeLogin();
});

test("test_Skills.showLoggedOutPage", function(assert) {
  Skills.showLoggedOutPage();
  var item = document.getElementById('skills_page');
  assert.equal(item.nodeName, "DIV",
        "#skills_page is a div after showLoggedOutPage() is called");
});

test("test_Skills.showPage", function(assert) {
  Skills.showPage();
  var item = document.getElementById('skills_page');
  assert.equal(item.nodeName, "DIV",
        "#skills_page is a div after showPage() is called");
});

test("test_Skills.bodyText", function(assert) {
  var text = Skills.bodyText();
  assert.equal(text[0].nodeName, "DIV", "bodyText returns a div");
});

test("test_Skills.generalInfo", function(assert) {
  var text = Skills.generalInfo();
  assert.equal(text[0].nodeName, "DIV", "generalInfo returns a div");
});
