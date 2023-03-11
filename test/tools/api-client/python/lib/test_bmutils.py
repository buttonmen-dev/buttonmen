import unittest

import datetime
import os
import sys

# Use dummy_bmapi to get a bmapi.BMClient that works for responder testing,
# then use that with bmutils itself to test bmutils
import dummy_bmapi

mydir = os.path.dirname(os.path.realpath(sys.argv[0]))
tooldir = mydir + '/../../../../../tools/api-client/python/lib/'
sys.path.append(tooldir)
import bmutils

TEST_TYPE = None

WRAPPED_GAME_DATA_KEYS = [
  'gameId', 'gameState', 'inactivity', 'isAwaitingAction',
  'myButtonName', 'nDraws', 'nLosses', 'nTargetWins', 'nWins',
  'opponentButtonName', 'opponentId', 'opponentName', 'status',
]

class TestBMUtils(unittest.TestCase):
  def setUp(self):
    self.bmapi_obj = dummy_bmapi.BMClient(TEST_TYPE)
    self.obj = bmutils.BMClientParser(None, None, client=self.bmapi_obj)
    self.now = datetime.datetime.now().strftime('%s')

  def tearDown(self):
    self.bmapi_obj.session.close()

  def test_init(self):
    self.assertTrue(self.obj, "Initialized BMClientParser object with dummy bmapi")

  def test_verify_login(self):
    r = self.obj.verify_login()
    self.assertEqual(r, True,
      'verify_login returns successfully')

  def test_wrap_create_game(self):
    known_keys = [
      'gameId',
    ]
    r = self.obj.wrap_create_game('Avis', 'Avis', 'tester2')
    self.assertEqual(sorted(r.keys()), known_keys)
    r = self.obj.wrap_create_game('Avis', 'Avis', None)
    self.assertEqual(sorted(r.keys()), known_keys)
    r = self.obj.wrap_create_game('Avis', None, None)
    self.assertEqual(sorted(r.keys()), known_keys)
    with self.assertRaises(ValueError):
        r = self.obj.wrap_create_game(None, None, None)

  def test_wrap_load_active_games(self):
    r = self.obj.wrap_load_active_games()
    self.assertTrue(isinstance(r, list))
    self.assertEqual(len(r), 9)
    self.assertEqual(sorted(r[0].keys()), WRAPPED_GAME_DATA_KEYS)

  def test_wrap_load_new_games(self):
    r = self.obj.wrap_load_new_games()
    self.assertTrue(isinstance(r, list))
    self.assertEqual(len(r), 0)

  def test_wrap_load_completed_games(self):
    r = self.obj.wrap_load_completed_games()
    self.assertTrue(isinstance(r, list))
    self.assertEqual(len(r), 4)
    self.assertEqual(sorted(r[0].keys()), WRAPPED_GAME_DATA_KEYS)

  def test_load_button_names(self):
    r = self.obj.wrap_load_button_names()
    known_keys = [
      'artFilename', 'buttonId', 'buttonName', 'buttonSet', 'dieSkills',
      'dieTypes', 'hasUnimplementedSkill', 'isTournamentLegal', 'recipe', 'tags'
    ]
    self.assertTrue(isinstance(r, dict))
    self.assertTrue('CactusJack' in r)
    testButton = r['CactusJack']
    self.assertEqual(testButton['buttonSet'], 'Classic Fanatics')
    self.assertEqual(testButton['dieSkills'], ['Shadow', 'Speed'])
    self.assertEqual(testButton['dieTypes'], ['Option', 'X Swing', 'U Swing'])
    self.assertEqual(testButton['hasUnimplementedSkill'], False)
    self.assertEqual(testButton['isTournamentLegal'], False)
    self.assertEqual(testButton['recipe'], 'z(8/12) (4/16) s(6/10) z(X) s(U)')
    self.assertEqual(testButton['artFilename'], 'cactusjack.png')
    self.assertEqual(testButton['tags'], [])

  def test_wrap_load_game_data(self):
    known_keys = [
      'activePlayerIdx', 'creatorDataArray', 'currentPlayerIdx', 'description',
      'dieBackgroundType', 'gameActionLog', 'gameActionLogCount',
      'gameChatEditable', 'gameChatLog', 'gameChatLogCount',
      'gameId', 'gameSkillsInfo', 'gameState', 'maxWins', 'opponent',
      'player', 'playerDataArray', 'playerWithInitiativeIdx',
      'previousGameId', 'roundNumber', 'timestamp', 'validAttackTypeArray'
    ]
    self.obj.username = 'responder001'
    r = self.obj.wrap_load_game_data(101)
    self.assertEqual(sorted(r.keys()), known_keys)
    self.assertTrue(type(r['activePlayerIdx']) in [int, type(None)])
    self.assertTrue(type(r['currentPlayerIdx']) in [int, type(None)])
    self.assertEqual(r['player'], r['playerDataArray'][0])

    player_data_keys = [
      'activeDieArray', 'button', 'canStillWin', 'capturedDieArray',
      'gameScoreArray', 'hasDismissedGame', 'isChatPrivate',
      'isOnVacation', 'lastActionTime',
      'optRequestArray', 'outOfPlayDieArray',
      'playerColor', 'playerId', 'playerName',
      'prevOptValueArray', 'prevSwingValueArray', 'roundScore', 'sideScore',
      'swingRequestArray', 'turboSizeArray', 'waitingOnAction'
    ]
    self.assertEqual(sorted(r['player'].keys()), player_data_keys)


  def test_wrap_load_player_names(self):
    r = self.obj.wrap_load_player_names()
    self.assertTrue(isinstance(r, dict))
    self.assertTrue('responder003' in r.keys())
    self.assertEqual(sorted(r['responder003'].keys()), ['status'])


  def test_wrap_react_to_new_game(self):
    r = self.obj.wrap_react_to_new_game(6, True)
    self.assertEqual(r, True)

    r = self.obj.wrap_react_to_new_game(8, False)
    self.assertEqual(r, True)


if __name__ == '__main__':
  if (not os.getenv('BMAPI_TEST_TYPE') or
      os.getenv('BMAPI_TEST_TYPE') not in dummy_bmapi.TEST_URLS):
    raise ValueError(
      "Set BMAPI_TEST_TYPE environment variable.  Valid choices: %s" % (
        (" ".join(sorted(TEST_URLS.keys())))))
  TEST_TYPE = os.getenv('BMAPI_TEST_TYPE')
  unittest.main()
