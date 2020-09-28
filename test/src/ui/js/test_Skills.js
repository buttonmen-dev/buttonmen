module("Skills", {
  'setup': function() {
    BMTestUtils.SkillsPre = BMTestUtils.getAllElements();

    // Override Env.getParameterByName to get verification parameters
    BMTestUtils.overrideGetParameterByName();

    // Create the skills_page div so functions have something to modify
    if (document.getElementById('skills_page') == null) {
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
    delete Api.dieSkills;
    delete Api.dieTypes;

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
  stop();
  BMTestUtils.setupFakeLogin();

  expect(5);
  var cached_getInfo = Skills.getInfo;
  var cached_showPage = Skills.showPage;
  var getInfoCalled = false;
  Skills.showPage = function() {
    assert.ok(getInfoCalled, "Skills.getInfo is called before Skills.showPage");
  };
  Skills.getInfo = function(callback) {
    getInfoCalled = true;
    assert.equal(callback, Skills.showPage,
      "Skills.getInfo is called with Skills.showPage as an argument");
    callback();
  };

  Skills.showLoggedInPage();
  var item = document.getElementById('skills_page');
  assert.equal(item.nodeName, "DIV",
    "#skills_page is a div after showLoggedInPage() is called");

  Skills.getInfo = cached_getInfo;
  Skills.showPage = cached_showPage;

  BMTestUtils.cleanupFakeLogin();
  start();
});

test("test_Skills.getInfo", function(assert) {
  stop();
  Skills.getInfo(function() {
    assert.ok('dieSkills' in Api, 'Api should now contain dieSkills');
    assert.ok('dieTypes' in Api, 'Api should now contain dieTypes');
    start();
  });
});

test("test_Skills.showLoggedOutPage", function(assert) {
  stop();

  expect(5);
  var cached_getInfo = Skills.getInfo;
  var cached_showPage = Skills.showPage;
  var getInfoCalled = false;
  Skills.showPage = function() {
    assert.ok(getInfoCalled, "Skills.getInfo is called before Skills.showPage");
  };
  Skills.getInfo = function(callback) {
    getInfoCalled = true;
    assert.equal(callback, Skills.showPage,
      "Skills.getInfo is called with Skills.showPage as an argument");
    callback();
  };

  Skills.showLoggedInPage();
  var item = document.getElementById('skills_page');
  assert.equal(item.nodeName, "DIV",
    "#skills_page is a div after showLoggedInPage() is called");

  Skills.getInfo = cached_getInfo;
  Skills.showPage = cached_showPage;

  start();
});

test("test_Skills.showPage", function(assert) {
  stop();
  Skills.getInfo(function() {
    Skills.showPage();
    var item = document.getElementById('skills_page');
    assert.equal(item.nodeName, "DIV",
      "#skills_page is a div after showPage() is called");
    start();
  });
});

test("test_Skills.directoryDiv", function(assert) {
  stop();
  Skills.getInfo(function() {
    var text = Skills.directoryDiv();
    assert.equal(text[0].nodeName, "DIV", "directoryDiv returns a div");
    start();
  });
});

test("test_Skills.orderAlphabeticallyByCode", function(assert) {
  assert.equal(Skills.orderAlphabeticallyByCode('a', 'a'), 0, "Identical skills should compare as equal.");
  assert.ok(Skills.orderAlphabeticallyByCode('a', 'b') < 0, "Skills should order alphabetically.");
  assert.ok(Skills.orderAlphabeticallyByCode('B', 'b') < 0, "Skills should order uppercase before lowercase.");
  assert.ok(Skills.orderAlphabeticallyByCode('z', '(A)') < 0, "Skills should order short to long.");
});

test("test_Skills.skillDescriptionsDiv", function(assert) {
  stop();
  Skills.getInfo(function() {
    var text = Skills.skillDescriptionsDiv();
    assert.equal(text[0].nodeName, "DIV", "skillDescriptionsDiv returns a div");
    start();
  });
});

test("test_Skills.dieTypeDescriptionsDiv", function(assert) {
  stop();
  Skills.getInfo(function() {
    var text = Skills.dieTypeDescriptionsDiv();
    assert.equal(text[0].nodeName, "DIV", "dieTypeDescriptionsDiv returns a div");
    start();
  });
});
