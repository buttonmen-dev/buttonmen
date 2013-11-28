module("Api");

// pre-flight test of whether the Api module has been loaded
test("test_Api_is_loaded", function() {
  ok(Api, "The Api namespace exists");
});

asyncTest("test_Api.getButtonData", function() {
  Api.getButtonData(function() {
    equal(Api.button.load_status, "ok", "Api.button.load_status should be ok");
    deepEqual(
      Api.button.list["Avis"],
      {'hasUnimplementedSkill': false, 'recipe': '(4) (4) (10) (12) (X)',},
      "Avis button in list should have correct contents");
    deepEqual(Env.message, undefined,
              "Api.getButtonData should not set Env.message");
    start();
  });
});

// FIXME: the second test will fail when players exist in the DB
asyncTest("test_Api.getPlayerData", function() {
  Api.getPlayerData(function() {
    equal(Api.player.load_status, "ok", "Api.player.load_status should be ok");
    deepEqual(
      Api.player.list,
      {},
      "Player in list should have correct contents");
    deepEqual(Env.message, undefined,
              "Api.getPlayerData should not set Env.message");
    start();
  });
});
