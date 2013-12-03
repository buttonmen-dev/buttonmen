module("Api", {
  'setup': function() {
    BMTestUtils.ApiPre = BMTestUtils.getAllElements();
  },
  'teardown': function() {

    // Delete all elements we expect this module to create
    delete Api.button;
    delete Api.player;
    BMTestUtils.deleteEnvMessage();

    // Fail if any other elements were added or removed
    BMTestUtils.ApiPost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.ApiPre, BMTestUtils.ApiPost,
      "After testing, the page should have no unexpected element changes");
  }
});

// pre-flight test of whether the Api module has been loaded
test("test_Api_is_loaded", function() {
  expect(2); // number of tests plus 1 for the teardown test
  ok(Api, "The Api namespace exists");
});

asyncTest("test_Api.getButtonData", function() {
  expect(5); // number of tests plus 1 for the teardown test
  Api.getButtonData(function() {
    equal(Api.button.load_status, "ok", "Api.button.load_status should be ok");
    equal(typeof Api.button.list, "object",
          "Api.button.list should be an object");
    deepEqual(
      Api.button.list["Avis"],
      {'hasUnimplementedSkill': false, 'recipe': '(4) (4) (10) (12) (X)',},
      "Button Avis should have correct contents");
    deepEqual(Env.message, undefined,
              "Api.getButtonData should not set Env.message");
    start();
  });
});

asyncTest("test_Api.getPlayerData", function() {
  expect(5); // number of tests plus 1 for the teardown test
  Api.getPlayerData(function() {
    equal(Api.player.load_status, "ok", "Api.player.load_status should be ok");
    equal(typeof Api.player.list, "object",
          "Api.player.list should be an object");
    deepEqual(
      Api.player.list["tester2"],
      {},
      "Player tester2 should have correct contents");
    deepEqual(Env.message, undefined,
              "Api.getPlayerData should not set Env.message");
    start();
  });
});

test("test_Api.parseButtonData", function() {
  expect(2); // number of tests plus 1 for the teardown test
  ok(null, "Test of Api.parseButtonData not implemented");
});

test("test_Api.parsePlayerData", function() {
  expect(2); // number of tests plus 1 for the teardown test
  ok(null, "Test of Api.parsePlayerData not implemented");
});
