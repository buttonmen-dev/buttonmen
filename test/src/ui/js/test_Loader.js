module("Loader", {
  'setup': function() {
    // The usual default scripts would already have loaded with the testing
    // harness, so we'll load a special dummy one instead
    Loader.defaultScripts = [ 'js/dummy1.js', ];
    Loader.defaultBMModules = [ ];

    BMTestUtils.LoaderPre = BMTestUtils.getAllElements();
  },
  'teardown': function(assert) {

    // Do not ignore intermittent failures in this test --- you
    // risk breaking the entire suite in hard-to-debug ways
    assert.equal(jQuery.active, 0,
      "All test functions MUST complete jQuery activity before exiting");

    // Delete all elements we expect this module to create

    // JS objects
    delete Loader.callback;
    delete Loader.loadStatus;
    delete Dummy;

    // Fail if any other elements were added or removed
    BMTestUtils.LoaderPost = BMTestUtils.getAllElements();
    assert.deepEqual(
      BMTestUtils.LoaderPost, BMTestUtils.LoaderPre,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Loader module has been loaded
test("test_Loader_is_loaded", function(assert) {
  assert.ok(Verify, "The Loader namespace exists");
});

test("test_Loader.loadScripts", function(assert) {
  stop();
  Loader.loadScripts([ 'js/dummy2.js' ], function () {
    assert.equal(Dummy.One, 1, "default script was loaded");
    assert.equal(Dummy.Two, 2, "explicit script was loaded");
    start();
  });
});
