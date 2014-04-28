##### bmutils.py
# This module provides a wrapper to the bmapi client which may (or
# may not) report the API data in a more user-friendly form.

import bmapi

class BMClientParser(bmapi.BMClient):
  def get_game_data(self, status):
    if status == 'completed':
      retval = self.load_completed_games()
    else:
      retval = self.load_active_games()
    if not retval['status'] == 'ok':
      raise ValueError, "Failed to get game data, got: " + retval
    data = retval['data']
    games = []
    for i in range(len(data['gameIdArray'])):
      gamedata = {}
      for item in [
	'gameState', 'opponentName', 'myButtonName', 'status',
	'opponentButtonName']:
        gamedata[item] = data[item + 'Array'][i]
      for item in [
	'gameId', 'nWins', 'nLosses', 'nTargetWins', 'opponentId',
	'isAwaitingAction', 'nDraws']:
        gamedata[item] = int(data[item + 'Array'][i])
        
      games.append(gamedata)
    return games
  
  def get_game_state(self, game):
    retval = self.load_game_data(game)
    if not retval['status'] == 'ok':
      raise ValueError, "Failed to get game data, got: " + retval
    data = retval['data']
    gamedata = data['gameData']['data']
    playerIdx = int(data['currentPlayerIdx'])
    opponentIdx = 1 - playerIdx
    state = {
      'player': {
        'playerName': data['playerNameArray'][playerIdx],
      },
      'opponent': {
        'playerName': data['playerNameArray'][opponentIdx],
      },
    }
    for datatype in [
      'capturedSidesArray', 'capturedValueArray', 'capturedRecipeArray',
      'sidesArray', 'buttonRecipe', 'nCapturedDie', 'sideScore',
      'waitingOnAction', 'diePropertiesArray', 'dieRecipeArray',
      'roundScore', 'dieSkillsArray', 'dieDescriptionArray',
      'valueArray', 'buttonName', 'gameScoreArray', 'playerId',
      'swingRequestArray', 'nDie',
    ]:
      state['player'][datatype] = gamedata[datatype + 'Array'][playerIdx]
      state['opponent'][datatype] = gamedata[datatype + 'Array'][opponentIdx]
    return state
