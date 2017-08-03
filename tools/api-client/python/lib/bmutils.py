##### bmutils.py
# This module provides a wrapper to the bmapi client which may (or
# may not) report the API data in a more user-friendly form.

import bmapi
import os
import json

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
  '!': 'Turbo',
  'v': 'Value',
  'h': 'Weak',
  '`': 'Warrior',
}

class BMClientParser(bmapi.BMClient):

  ## Simple wrappers which just call a function and reflow the result

  def wrap_load_button_names(self):
    retval = self.load_button_names()
    if not retval.status == 'ok':
      raise ValueError, "Failed to get button data, got: %s" % retval.message
    data = retval.data
    buttons = {}
    for i in range(len(data)):
      buttons[data[i]['buttonName']] = data[i]
    return buttons

  def wrap_load_player_names(self):
    retval = self.load_player_names()
    if not retval.status == 'ok':
      raise ValueError, "Failed to get player data, got: " + retval.message
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
      raise ValueError, "Failed to call loadActiveGames, got: " + retval.message
    return self._wrap_game_list_data(retval.data)

  def wrap_load_new_games(self):
    retval = self.load_new_games()
    if not retval.status == 'ok':
      raise ValueError, "Failed to call loadNewGames, got: " + retval.message
    return self._wrap_game_list_data(retval.data)

  def wrap_react_to_new_game(self, game, accept):
    retval = self.react_to_new_game(game, 'accept' if accept else 'reject')
    if not retval.status == 'ok':
      raise ValueError, "Failed to call reactToNewGame, got: " + retval.message
    return retval.data

  def wrap_load_completed_games(self):
    retval = self.load_completed_games()
    if not retval.status == 'ok':
      raise ValueError, "Failed to call loadCompletedGames, got: " + retval.message
    return self._wrap_game_list_data(retval.data)

  def wrap_create_game(self, pbutton, obutton='', player='', opponent='', description=''):
    retval = self.create_game(pbutton, obutton, player, opponent, description)
    if not retval.status == 'ok':
      raise ValueError, "Failed to call createGame, got: " + retval.message
    return retval.data

  def wrap_load_game_data(self, game):
    # if we're using a cache directory
    if self.cachedir:
      # if the cache directory doesn't exist, create it
      if not os.path.isdir(self.cachedir):
        os.makedirs(self.cachedir)
      # set the path to the cache file for this game
      cachefile = self.cachedir + "/{0:010d}.json".format(int(game))
      # if the cache already has a file for this game
      if os.path.isfile(cachefile):
        # get the game data from the cache
        with open(cachefile) as cache_fh:
          data = json.load(cache_fh)
      # otherwise (the cache didn't already have a file for this game)
      else:
        # load the game
        retval = self.load_game_data(game)
        # if that didn't work, raise an exception
        if not retval.status == 'ok':
          raise ValueError, "Failed to call loadGameData, got: " + retval.message
        # if we're still here, we have the game data
        data = retval.data
        # if the game is completed
        if data['gameState'] == "END_GAME":
          # put the data for this game into the cache
          with open(cachefile, 'w') as cache_fh:
            json.dump(data, cache_fh, indent=1, sort_keys=True)
    # otherwise (we aren't using a cache directory), load the game
    else:
      retval = self.load_game_data(game)
      if not retval.status == 'ok':
        raise ValueError, "Failed to call loadGameData, got: " + retval.message
      data = retval.data
    # either way, at this point we have the game data in 'data', and we're
    # done doing anything cache-related
    playerIdx = int(data['currentPlayerIdx'])
    opponentIdx = 1 - playerIdx
    data['player'] = data['playerDataArray'][playerIdx]
    data['opponent'] = data['playerDataArray'][opponentIdx]
    return data
