module("Profile", {
  'setup': function() {
    BMTestUtils.ProfilePre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the profile_page div so functions have something to modify
    if (document.getElementById('profile_page') == null) {
      $('body').append($('<div>', {'id': 'env_message', }));
      $('body').append($('<div>', {'id': 'profile_page', }));
    }
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JavaScript variables
    delete Api.profile_info;
    delete Env.window.location.search;
    delete Profile.page;

    // Page elements
    $('#profile_page').remove();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.ProfilePost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.ProfilePost, BMTestUtils.ProfilePre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Profile module has been loaded
test("test_Profile_is_loaded", function(assert) {
  assert.ok(Profile, "The Profile namespace exists");
});

// The purpose of this test is to demonstrate that the flow of
// Profile.showLoggedInPage() is correct for a showXPage function, namely
// that it calls an API getter with a showStatePage function as a
// callback.
//
// Accomplish this by mocking the invoked functions
test("test_Profile.showLoggedInPage", function(assert) {
  expect(5);
  var cached_getProfile = Profile.getProfile;
  var cached_showStatePage = Profile.showPage;
  var getProfileCalled = false;
  Profile.showPage = function() {
    assert.ok(getProfileCalled, "Profile.getProfile is called before Profile.showPage");
  };
  Profile.getProfile = function(callback) {
    getProfileCalled = true;
    assert.equal(callback, Profile.showPage,
      "Profile.getProfile is called with Profile.showPage as an argument");
    callback();
  };

  Profile.showLoggedInPage();
  var item = document.getElementById('profile_page');
  assert.equal(item.nodeName, "DIV",
        "#profile_page is a div after showLoggedInPage() is called");

  Profile.getProfile = cached_getProfile;
  Profile.showPage = cached_showStatePage;
});

test("test_Profile.getProfile", function(assert) {
  stop();
  Env.window.location.search = '?player=tester';
  Profile.getProfile(function() {
    assert.ok(Api.profile_info, "Profile info parsed from server");
    if (Api.profile_info) {
      assert.equal(Api.profile_info.load_status, 'ok',
        "Profile info parsed successfully from server");
    }
    start();
  });
});

test("test_Profile.showPage", function(assert) {
  stop();
  Env.window.location.search = '?player=tester';
  Profile.getProfile(function() {
    Profile.showPage();
    var htmlout = Profile.page.html();
    assert.ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

test("test_Profile.buildProfileTable", function(assert) {
  stop();
  Env.window.location.search = '?player=tester';
  Profile.getProfile(function() {
    var table = Profile.buildProfileTable();
    var htmlout = table.html();
    assert.ok(htmlout.match('February 29'), "Profile table content was generated");
    start();
  });
});

test("test_Profile.buildProfileTableRow", function(assert) {
  var tr = Profile.buildProfileTableRow('Things', 'something', 'nothing', true);
  var valueTd = tr.find('td.partialValue');
  assert.equal(valueTd.text(), 'something', 'Value should be in partialValue cell');

  var tr = Profile.buildProfileTableRow('Things', 'something', 'nothing', false);
  var valueTd = tr.find('td.value');
  assert.equal(valueTd.text(), 'something', 'Value should be in value cell');

  tr = Profile.buildProfileTableRow('Things', null, 'nothing', false);
  valueTd = tr.find('td.missingValue');
  assert.equal(valueTd.text(), 'nothing', 'Missing value should be in missingValue cell');

  tr = Profile.buildProfileTableRow('A', 'B', 'C', true, 'SomeClass');
  valueTd = tr.find('td.SomeClass');
  assert.equal(valueTd.text(), 'B', 'Row should include SomeClass as style');
});
