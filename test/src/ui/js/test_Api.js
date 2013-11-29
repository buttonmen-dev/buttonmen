module("Api");

// pre-flight test of whether the Api module has been loaded
test("test_Api_is_loaded", function() {
  ok(Api, "The Api namespace exists");
});

asyncTest("test_Api.getButtonData", function() {
  Api.getButtonData(function() {
    equal(Api.button.load_status, "ok", "Api.button.load_status should be ok");
    equal(typeof Api.button.list, "object",
          "Api.button.list should be an object");
    if (Api.button.list) {
      deepEqual(
        Api.button.list["Avis"],
        {'hasUnimplementedSkill': false, 'recipe': '(4) (4) (10) (12) (X)',},
        "Button Avis should have correct contents");
    }
    deepEqual(Env.message, undefined,
              "Api.getButtonData should not set Env.message");
    start();
  });
});

asyncTest("test_Api.getPlayerData", function() {
  Api.getPlayerData(function() {
    equal(Api.player.load_status, "ok", "Api.player.load_status should be ok");
    equal(typeof Api.player.list, "object",
          "Api.player.list should be an object");
    if (Api.player.list) {
      deepEqual(
        Api.player.list["tester2"],
        {},
        "Player tester2 should have correct contents");
    }
    deepEqual(Env.message, undefined,
              "Api.getPlayerData should not set Env.message");
    start();
  });
});
