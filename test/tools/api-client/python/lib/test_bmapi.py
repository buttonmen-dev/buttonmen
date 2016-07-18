import unittest

import os
import sys

mydir = os.path.dirname(os.path.realpath(sys.argv[0]))
tooldir = mydir + '/../../../../../tools/api-client/python/lib/'
sys.path.append(tooldir)
import bmapi

TEST_URLS = {
  'vagrant_local': 'http://localhost/api/dummy_responder',
  'jenkins': 'http://localhost:8082/dummy_responder.php',
}
TEST_TYPE = None

# Alternate BMClient which overrides rcfile processing and cookie/login
# use, in order to work correctly for the dummy responder case
class BMDummyClient(bmapi.BMClient):
  def __init__(self, url):
    self.url = url
    self.username = 'tester1'
    self.password = None
    self.cookiefile = None

class TestBMClient(unittest.TestCase):
  def setUp(self):
    responder_url = TEST_URLS[TEST_TYPE]
    self.obj = BMDummyClient(responder_url)

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
      'activePlayerIdx', 'currentPlayerIdx', 'description',
      'gameActionLog', 'gameActionLogCount',
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
      'dieBackgroundType', 'gameScoreArray', 'hasDismissedGame',
      'isOnVacation', 'lastActionTime',
      'optRequestArray', 'outOfPlayDieArray',
      'playerColor', 'playerId', 'playerName',
      'prevOptValueArray', 'prevSwingValueArray', 'roundScore', 'sideScore',
      'swingRequestArray', 'turboSizeArray', 'waitingOnAction'
    ]
    player_data = r.data['playerDataArray'][0]
    self.assertEqual(sorted(player_data.keys()), player_data_keys)

if __name__ == '__main__':
  if (not os.getenv('BMAPI_TEST_TYPE') or
      os.getenv('BMAPI_TEST_TYPE') not in TEST_URLS):
    raise ValueError, \
      "Set BMAPI_TEST_TYPE environment variable.  Valid choices: %s" % (
      (" ".join(sorted(TEST_URLS.keys()))))
  TEST_TYPE = os.getenv('BMAPI_TEST_TYPE')
  unittest.main()
