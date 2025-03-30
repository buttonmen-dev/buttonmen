import unittest

import datetime
import os
import sys

# dummy_bmapi just wraps bmapi's BMClient for use against dummy
# responder, so we can use it directly when running bmapi tests
import dummy_bmapi

TEST_TYPE = None

class TestBMClient(unittest.TestCase):
  def setUp(self):
    self.obj = dummy_bmapi.BMClient(TEST_TYPE)
    self.now = datetime.datetime.now().strftime('%s')

  def tearDown(self):
    self.obj.session.close()

  def test_init(self):
    self.assertTrue(self.obj, "Initialized BMDummyClient object")

  def test_load_player_name(self):
    r = self.obj.load_player_name()
    self.assertEqual(r.status, 'ok',
      'loadPlayerName returns successfully')
    self.assertEqual(r.data['userName'], 'tester1',
      'Dummy username is tester1')

  def test_load_button_names(self):
    r = self.obj.load_button_names()
    self.assertEqual(r.status, 'ok', 'loadButtonData returns successfully')
    known_keys = [
      'artFilename', 'buttonId', 'buttonName', 'buttonSet', 'dieSkills',
      'dieTypes', 'hasUnimplementedSkill', 'isTournamentLegal', 'recipe', 'tags'
    ]
    self.assertTrue(len(r.data) > 0)
    testButton = None
    for i in range(len(r.data)):
      self.assertEqual(sorted(r.data[i].keys()), known_keys)
      if r.data[i]['buttonName'] == 'CactusJack':
        testButton = r.data[i]
    self.assertNotEqual(testButton, None)
    self.assertEqual(testButton['buttonSet'], 'Classic Fanatics')
    self.assertEqual(testButton['dieSkills'], ['Shadow', 'Speed'])
    self.assertEqual(testButton['dieTypes'], ['Option', 'X Swing', 'U Swing'])
    self.assertEqual(testButton['hasUnimplementedSkill'], False)
    self.assertEqual(testButton['isTournamentLegal'], False)
    self.assertEqual(testButton['recipe'], 'z(8/12) (4/16) s(6/10) z(X) s(U)')
    self.assertEqual(testButton['artFilename'], 'cactusjack.png')
    self.assertEqual(testButton['tags'], [ ])

  def test_load_player_names(self):
    r = self.obj.load_player_names()
    self.assertEqual(r.status, 'ok', 'loadPlayerNames returns successfully')
    known_keys = [
      'nameArray', 'statusArray',
    ]
    self.assertEqual(sorted(r.data.keys()), known_keys)
    for key in sorted(r.data.keys()):
      if key == 'nameArray':
        self.assertTrue(len(r.data[key]) > 0)
      else:
        self.assertEqual(len(r.data[key]), len(r.data['nameArray']))

  def test_load_active_games(self):
    r = self.obj.load_active_games()
    self.assertEqual(r.status, 'ok', 'loadActiveGames returns successfully')
    known_keys = [
      'gameDescriptionArray', 'gameIdArray', 'gameStateArray',
      'inactivityArray', 'inactivityRawArray',
      'isAwaitingActionArray', 'isOpponentOnVacationArray', 'myButtonNameArray',
      'nDrawsArray', 'nLossesArray', 'nTargetWinsArray', 'nWinsArray',
      'opponentButtonNameArray', 'opponentColorArray', 'opponentIdArray',
      'opponentNameArray', 'playerColorArray', 'statusArray'
    ]
    self.assertEqual(sorted(r.data.keys()), known_keys)
    for key in sorted(r.data.keys()):
      if key == 'gameIdArray':
        self.assertTrue(len(r.data[key]) > 0)
      else:
        self.assertEqual(len(r.data[key]), len(r.data['gameIdArray']))

  def test_load_completed_games(self):
    r = self.obj.load_completed_games()
    self.assertEqual(r.status, 'ok', 'loadCompletedGames returns successfully')
    known_keys = [
      'gameDescriptionArray', 'gameIdArray', 'gameStateArray',
      'inactivityArray', 'inactivityRawArray',
      'isAwaitingActionArray', 'isOpponentOnVacationArray', 'myButtonNameArray',
      'nDrawsArray', 'nLossesArray', 'nTargetWinsArray', 'nWinsArray',
      'opponentButtonNameArray', 'opponentColorArray', 'opponentIdArray',
      'opponentNameArray', 'playerColorArray', 'statusArray'
    ]
    self.assertEqual(sorted(r.data.keys()), known_keys)
    for key in sorted(r.data.keys()):
      if key == 'gameIdArray':
        self.assertTrue(len(r.data[key]) > 0)
      else:
        self.assertEqual(len(r.data[key]), len(r.data['gameIdArray']))

  def test_load_new_games(self):
    r = self.obj.load_new_games()
    self.assertEqual(r.status, 'ok', 'loadNewGames returns successfully')
    known_keys = [
      'gameDescriptionArray', 'gameIdArray', 'gameStateArray',
      'inactivityArray', 'inactivityRawArray',
      'isAwaitingActionArray', 'isOpponentOnVacationArray', 'myButtonNameArray',
      'nDrawsArray', 'nLossesArray', 'nTargetWinsArray', 'nWinsArray',
      'opponentButtonNameArray', 'opponentColorArray', 'opponentIdArray',
      'opponentNameArray', 'playerColorArray', 'statusArray'
    ]
    self.assertEqual(sorted(r.data.keys()), known_keys)

  def test_create_game(self):
    known_keys = [
      'gameId',
    ]
    r = self.obj.create_game('Avis', 'Avis', 'tester2')
    self.assertEqual(r.status, 'ok')
    self.assertEqual(sorted(r.data.keys()), known_keys)
    r = self.obj.create_game('Avis', 'Avis', None)
    self.assertEqual(r.status, 'ok')
    self.assertEqual(sorted(r.data.keys()), known_keys)
    r = self.obj.create_game('Avis', None, None)
    self.assertEqual(r.status, 'ok')
    self.assertEqual(sorted(r.data.keys()), known_keys)

  def test_load_game_data(self):
    known_keys = [
      'activePlayerIdx', 'creatorDataArray', 'currentPlayerIdx', 'description',
      'dieBackgroundType', 'gameActionLog', 'gameActionLogCount',
      'gameChatEditable', 'gameChatLog', 'gameChatLogCount',
      'gameId', 'gameSkillsInfo',
      'gameState', 'maxWins', 'playerDataArray', 'playerWithInitiativeIdx',
      'previousGameId', 'roundNumber', 'timestamp', 'validAttackTypeArray'
    ]
    self.obj.username = 'responder001'
    r = self.obj.load_game_data(101)
    self.assertEqual(r.status, 'ok')
    self.assertEqual(sorted(r.data.keys()), known_keys)
    self.assertTrue(type(r.data['activePlayerIdx']) in [int, type(None)])
    self.assertTrue(type(r.data['currentPlayerIdx']) in [int, type(None)])

    player_data_keys = [
      'activeDieArray', 'button', 'canStillWin', 'capturedDieArray',
      'gameScoreArray', 'hasDismissedGame', 'isChatPrivate',
      'isOnVacation', 'lastActionTime',
      'optRequestArray', 'outOfPlayDieArray',
      'playerColor', 'playerId', 'playerName',
      'prevOptValueArray', 'prevSwingValueArray', 'roundScore', 'sideScore',
      'swingRequestArray', 'turboSizeArray', 'waitingOnAction'
    ]
    player_data = r.data['playerDataArray'][0]
    self.assertEqual(sorted(player_data.keys()), player_data_keys)

  def test_react_to_new_game(self):
    r = self.obj.react_to_new_game(6, 'accept')
    self.assertEqual(r.status, 'ok')
    self.assertEqual(r.message, 'Joined game 7')
    self.assertEqual(r.data, True)

    r = self.obj.react_to_new_game(9, 'reject')
    self.assertEqual(r.status, 'ok')
    self.assertEqual(r.message, 'Rejected game 9')
    self.assertEqual(r.data, True)

  def test_submit_turn(self):
    die_select_status = {
      "playerIdx_0_0": True,
      "playerIdx_1_0": True,
    }
    r = self.obj.submit_turn(302, 1, 0, die_select_status, 'Power', 1, self.now, [])
    self.assertEqual(r.status, 'ok')
    self.assertEqual(r.message, 'responder003 performed Power attack using [(99):54] against [(99):42]; Defender (99) was captured; Attacker (99) rerolled 54 => 10. End of round: responder003 won round 1 (148.5 vs. 0). ')
    self.assertEqual(r.data, True)

  def test_submit_die_values(self):
    swingArray = {
      'V': 6,
      'W': 4,
      'X': 4,
      'Y': 1,
      'Z': 4,
    }
    r = self.obj.submit_die_values(401, swingArray, {}, 1, self.now)
    self.assertEqual(r.status, 'ok')
    self.assertEqual(r.message, 'Successfully set die sizes')
    self.assertEqual(r.data, True)

  def test_react_to_initiative(self):
    r = self.obj.react_to_initiative(915, 'focus', [1, 2], [1, 1], 2, self.now)
    self.assertEqual(r.status, 'ok')
    self.assertEqual(r.message, 'Successfully gained initiative')
    self.assertEqual(r.data, {'gainedInitiative': True})

  def test_adjust_fire_dice(self):
    r = self.obj.adjust_fire_dice(808, 'cancel', [], [], 1, self.now)
    self.assertEqual(r.status, 'ok')
    self.assertEqual(r.message, 'responder003 chose to abandon this attack and start over. ')
    self.assertEqual(r.data, True)

  def test_choose_reserve_dice(self):
    r = self.obj.choose_reserve_dice(1007, 'add', 7)
    self.assertEqual(r.status, 'ok')
    self.assertEqual(r.message, 'responder003 added a reserve die: r(20). ')
    self.assertEqual(r.data, True)

  def test_choose_auxiliary_dice(self):
    r = self.obj.choose_auxiliary_dice(901, 'add', 5)
    self.assertEqual(r.status, 'ok')
    self.assertEqual(r.message, 'Chose to add auxiliary die')
    self.assertEqual(r.data, True)

if __name__ == '__main__':
  if (not os.getenv('BMAPI_TEST_TYPE') or
      os.getenv('BMAPI_TEST_TYPE') not in dummy_bmapi.TEST_URLS):
    raise ValueError(
      "Set BMAPI_TEST_TYPE environment variable.  Valid choices: %s" % (
        (" ".join(sorted(TEST_URLS.keys())))))
  TEST_TYPE = os.getenv('BMAPI_TEST_TYPE')
  unittest.main()
