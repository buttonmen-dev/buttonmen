##### bmutils.py
# This module provides a wrapper to the bmapi client which may (or
# may not) report the API data in a more user-friendly form.

import bmapi

SkillName = {
  '+': 'Auxiliary',
  'B': 'Berserk',
  'b': 'Boom',
  'c': 'Chance',
  'D': 'Doppelganger',
  'F': 'Fire',
  'f': 'Focus',
  'I': 'Insult',
  'k': 'Konstant',
  '&': 'Mad',
  'M': 'Maximum',
  'H': 'Mighty',
  '?': 'Mood',
  'm': 'Morphing',
  'n': 'Null',
  'o': 'Ornery',
  'p': 'Poison',
  'q': 'Queer',
  '%': 'Radioactive',
  'G': 'Rage',
  'r': 'Reserve',
  's': 'Shadow',
  'w': 'Slow',
  'z': 'Speed',
  'd': 'Stealth',
  'g': 'Stinger',
  '^': 'TimeAndSpace',
  't': 'Trip',
  'v': 'Value',
  'h': 'Weak',
  '`': 'Warrior',
}

class BMClientParser(bmapi.BMClient):

  ## Simple wrappers which just call a function and reflow the result

  def wrap_load_button_names(self):
    retval = self.load_button_names()
    if not retval.status == 'ok':
      raise ValueError, "Failed to get button data, got: %s" % retval
    data = retval.data
    buttons = {}
    for i in range(len(data)):
      buttons[data[i]['buttonName']] = data[i]
    return buttons

  def wrap_load_player_names(self):
    retval = self.load_player_names()
    if not retval.status == 'ok':
      raise ValueError, "Failed to get player data, got: " + retval
    data = retval.data
    players = {}
    for i in range(len(data['nameArray'])):
      players[data['nameArray'][i]] = {
        'status': data['statusArray'][i],
      }
    return players

  def _wrap_game_list_data(self, data):
    games = []
    for i in range(len(data['gameIdArray'])):
      gamedata = {}
      for item in [
	'gameState', 'opponentName', 'myButtonName', 'status',
	'opponentButtonName', 'inactivity']:
        gamedata[item] = data[item + 'Array'][i]
      for item in [
	'gameId', 'nWins', 'nLosses', 'nTargetWins', 'opponentId',
	'isAwaitingAction', 'nDraws']:
        gamedata[item] = int(data[item + 'Array'][i])
      games.append(gamedata)
    return games

  def wrap_load_active_games(self):
    retval = self.load_active_games()
    if not retval.status == 'ok':
      raise ValueError, "Failed to call loadActiveGames, got: " + retval
    return self._wrap_game_list_data(retval.data)

  def wrap_load_completed_games(self):
    retval = self.load_completed_games()
    if not retval.status == 'ok':
      raise ValueError, "Failed to call loadCompletedGames, got: " + retval
    return self._wrap_game_list_data(retval.data)

  def wrap_create_game(self, pbutton, obutton='', opponent=''):
    retval = self.create_game(pbutton, obutton, opponent)
    if not retval.status == 'ok':
      raise ValueError, "Failed to call createGame, got: " + retval
    return retval.data

  def wrap_load_game_data(self, game):
    retval = self.load_game_data(game)
    if not retval.status == 'ok':
      raise ValueError, "Failed to call loadGameData, got: " + retval
    data = retval.data
    playerIdx = int(data['currentPlayerIdx'])
    opponentIdx = 1 - playerIdx
    data['player'] = data['playerDataArray'][playerIdx]
    data['opponent'] = data['playerDataArray'][opponentIdx]
    return data
