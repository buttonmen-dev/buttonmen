  module("Profile", {
  'setup': function() {
    BMTestUtils.ProfilePre = BMTestUtils.getAllElements();

    BMTestUtils.setupFakeLogin();

    // Create the profile_page div so functions have something to modify
    if (document.getElementById('profile_page') == null) {
      $('body').append($('<div>', {'id': 'profile_page', }));
    }
  },
  'teardown': function() {

    // Delete all elements we expect this module to create

    // JavaScript variables
    delete Api.profile_info;
    delete Env.window.location.search;
    delete Profile.page;

    // Page elements
    $('#profile_page').remove();
    $('#profile_page').empty();

    BMTestUtils.deleteEnvMessage();
    BMTestUtils.cleanupFakeLogin();

    // Fail if any other elements were added or removed
    BMTestUtils.ProfilePost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.ProfilePost, BMTestUtils.ProfilePre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Profile module has been loaded
test("test_Profile_is_loaded", function() {
  ok(Profile, "The Profile namespace exists");
});

test("test_Profile.showProfilePage", function() {
  $.ajaxSetup({ async: false });
  Profile.showProfilePage();
  var item = document.getElementById('profile_page');
  equal(item.nodeName, "DIV",
        "#profile_page is a div after showProfilePage() is called");
  $.ajaxSetup({ async: true });
});

asyncTest("test_Profile.getProfile", function() {
  Env.window.location.search = '?player=tester';
  Profile.getProfile(function() {
    ok(Api.profile_info, "Profile info parsed from server");
    if (Api.profile_info) {
      equal(Api.profile_info.load_status, 'ok',
        "Profile info parsed successfully from server");
    }
    start();
  });
});

asyncTest("test_Profile.showPage", function() {
  Env.window.location.search = '?player=tester';
  Profile.getProfile(function() {
    Profile.showPage();
    var htmlout = Profile.page.html();
    ok(htmlout.length > 0,
       "The created page should have nonzero contents");
    start();
  });
});

asyncTest("test_Profile.arrangePage", function() {
  Env.window.location.search = '?player=tester';
  Profile.getProfile(function() {
    Profile.page = $('<div>');
    Profile.page.append($('<p>', {'text': 'hi world', }));
    Profile.arrangePage();
    var item = document.getElementById('profile_page');
    equal(item.nodeName, "DIV",
          "#profile_page is a div after arrangePage() is called");
    start();
  });
});

asyncTest("test_Profile.buildProfileTable", function() {
  Env.window.location.search = '?player=tester';
  Profile.getProfile(function() {
    var table = Profile.buildProfileTable();
    var htmlout = table.html();
    ok(htmlout.match('February 29'), "Profile table content was generated");
    start();
  });
});

test("test_Profile.buildProfileTableRow", function() {
  var tr = Profile.buildProfileTableRow('Things', 'something', 'nothing', true);
  var valueTd = tr.find('td.partialValue');
  equal(valueTd.text(), 'something', 'Value should be in partialValue cell');

  var tr = Profile.buildProfileTableRow('Things', 'something', 'nothing', false);
  var valueTd = tr.find('td.value');
  equal(valueTd.text(), 'something', 'Value should be in value cell');

  tr = Profile.buildProfileTableRow('Things', null, 'nothing', false);
  valueTd = tr.find('td.missingValue');
  equal(valueTd.text(), 'nothing', 'Missing value should be in missingValue cell');
});
