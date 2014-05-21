module("Loader", {
  'setup': function() {
    // The usual default scripts would already have loaded with the testing
    // harness, so we'll load a special dummy one instead
    Loader.defaultScripts = [ 'js/dummy1.js', ];

    BMTestUtils.LoaderPre = BMTestUtils.getAllElements();
  },
  'teardown': function() {

    // Delete all elements we expect this module to create

    // JS objects
    delete Loader.callback;
    delete Loader.loadStatus;
    delete Dummy;

    // Fail if any other elements were added or removed
    BMTestUtils.LoaderPost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.LoaderPost, BMTestUtils.LoaderPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Loader module has been loaded
test("test_Loader_is_loaded", function() {
  ok(Verify, "The Loader namespace exists");
});

asyncTest("test_Loader.loadScripts", function() {
  Loader.loadScripts([ 'js/dummy2.js' ], function () {
    equal(Dummy.One, 1, "default script was loaded");
    equal(Dummy.Two, 2, "explicit script was loaded");
    start();
  });
});
