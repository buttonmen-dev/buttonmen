module("Config", {
  'setup': function() {
    BMTestUtils.ConfigPre = BMTestUtils.getAllElements();
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    Config.siteType = 'production';

    // Fail if any other elements were added or removed
    BMTestUtils.ConfigPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.ConfigPost, BMTestUtils.ConfigPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Api module has been loaded
test("test_Config_is_loaded", function(assert) {
  expect(3); // number of tests plus 2 for the teardown test
  assert.ok(Config, "The Config namespace exists");
});
