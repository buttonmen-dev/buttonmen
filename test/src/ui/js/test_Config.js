module("Config", {
  'setup': function() {
    BMTestUtils.ConfigPre = BMTestUtils.getAllElements();
  },
  'teardown': function() {

    // Delete all elements we expect this module to create
    delete Config.siteType;

    // Fail if any other elements were added or removed
    BMTestUtils.ConfigPost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.ConfigPost, BMTestUtils.ConfigPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Api module has been loaded
test("test_Config_is_loaded", function() {
  expect(2); // number of tests plus 1 for the teardown test
  ok(Config, "The Config namespace exists");
});
