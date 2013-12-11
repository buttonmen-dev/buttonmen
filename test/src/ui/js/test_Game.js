module("Game", {
  'setup': function() {
    BMTestUtils.GamePre = BMTestUtils.getAllElements();
  },
  'teardown': function() {

    // Delete all elements we expect this module to create
    delete Game.api;
    delete Game.game;
    BMTestUtils.deleteEnvMessage();

    // Fail if any other elements were added or removed
    BMTestUtils.GamePost = BMTestUtils.getAllElements();
    deepEqual(
      BMTestUtils.GamePre, BMTestUtils.GamePost,
      "After testing, the page should have no unexpected element changes");
  }
});

// Fake game data loader - FIXME, use dummy_responder
BMTestUtils.GameFakeData = function(gametype) {
  Game.api = {
    'load_status': 'failed',
  }
  if (gametype == 'newgame') {
    Game.game = 2;
    Game.api.gameData = {"status":"ok","data":{"gameId":2,"gameState": 24,"roundNumber":1,"maxWins":"3","activePlayerIdx":null,"playerWithInitiativeIdx":null,"playerIdArray":["1","2"],"buttonNameArray":["Clare","Kublai"],"waitingOnActionArray":[true,true],"nDieArray":[5,5],"valueArrayArray":[[null,null,null,null,null],[null,null,null,null,null]],"sidesArrayArray":[[6,8,8,20,null],[null,null,null,null,null]],"dieRecipeArrayArray":[["(6)","(8)","(8)","(20)","(X)"],["(4)","(8)","(12)","(20)","(X)"]],"swingRequestArrayArray":[["X"],["X"]],"validAttackTypeArray":[],"roundScoreArray":[21,22],"gameScoreArrayArray":[{"W":0,"L":0,"D":0},{"W":0,"L":0,"D":0}]}};
    Game.api.timestamp = "Wed, 11 Dec 2013 03:09:11 +0000";
    Game.api.actionLog = [];
    BMTestUtils.playerNameArray = ["tester1", "tester2"];
    return true;
  }
}

// pre-flight test of whether the Game module has been loaded
test("test_Game_is_loaded", function() {
  ok(Game, "The Game namespace exists");
});

test("test_Game.showGamePage", function() {
  ok(null, "Test of Game.showGamePage not implemented");
});

test("test_Game.getCurrentGame", function() {
  ok(null, "Test of Game.getCurrentGame not implemented");
});

test("test_Game.showStatePage", function() {
  ok(null, "Test of Game.showStatePage not implemented");
});

test("test_Game.layoutPage", function() {
  ok(null, "Test of Game.layoutPage not implemented");
});

test("test_Game.parseGameData", function() {
  BMTestUtils.GameFakeData('newgame');
  equal(Game.parseGameData(false, BMTestUtils.playerNameArray), false,
        "parseGameData() fails if currentPlayerIdx is not set");
  ok(Game.parseGameData(0, BMTestUtils.playerNameArray),
     "parseGameData() succeeds");
  equal(Game.api.gameId, '2', "parseGameData() set gameId");
  equal(Game.api.opponentIdx, 1, "parseGameData() set opponentIdx");
});

test("test_Game.parsePlayerData", function() {
  ok(null, "Test of Game.parsePlayerData not implemented");
});

test("test_Game.actionChooseSwingActive", function() {
  ok(null, "Test of Game.actionChooseSwingActive not implemented");
});

test("test_Game.actionChooseSwingInactive", function() {
  ok(null, "Test of Game.actionChooseSwingInactive not implemented");
});

test("test_Game.actionPlayTurnActive", function() {
  ok(null, "Test of Game.actionPlayTurnActive not implemented");
});

test("test_Game.actionPlayTurnInactive", function() {
  ok(null, "Test of Game.actionPlayTurnInactive not implemented");
});

test("test_Game.actionShowFinishedGame", function() {
  ok(null, "Test of Game.actionShowFinishedGame not implemented");
});

test("test_Game.formChooseSwingActive", function() {
  ok(null, "Test of Game.formChooseSwingActive not implemented");
});

test("test_Game.formPlayTurnActive", function() {
  ok(null, "Test of Game.formPlayTurnActive not implemented");
});

test("test_Game.pageAddGameHeader", function() {
  ok(null, "Test of Game.pageAddGameHeader not implemented");
});

test("test_Game.pageAddFooter", function() {
  ok(null, "Test of Game.pageAddFooter not implemented");
});

test("test_Game.pageAddTimestampFooter", function() {
  ok(null, "Test of Game.pageAddTimestampFooter not implemented");
});

test("test_Game.pageAddActionLogFooter", function() {
  ok(null, "Test of Game.pageAddActionLogFooter not implemented");
});

test("test_Game.dieRecipeTable", function() {
  ok(null, "Test of Game.dieRecipeTable not implemented");
});

test("test_Game.dieTableEntry", function() {
  ok(null, "Test of Game.dieTableEntry not implemented");
});

test("test_Game.pageAddDieBattleTable", function() {
  ok(null, "Test of Game.pageAddDieBattleTable not implemented");
});

test("test_Game.pageAddGamePlayerStatus", function() {
  ok(null, "Test of Game.pageAddGamePlayerStatus not implemented");
});

test("test_Game.pageAddGamePlayerDice", function() {
  ok(null, "Test of Game.pageAddGamePlayerDice not implemented");
});

test("test_Game.pageAddGameWinner", function() {
  ok(null, "Test of Game.pageAddGameWinner not implemented");
});

test("test_Game.dieIndexId", function() {
  ok(null, "Test of Game.dieIndexId not implemented");
});

test("test_Game.dieBorderToggleHandler", function() {
  ok(null, "Test of Game.dieBorderToggleHandler not implemented");
});

