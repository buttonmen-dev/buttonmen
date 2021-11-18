# BMAPI
# This library strictly implements the Button Men API in python.
# The BMClient class provides one function for each API method,
# which is called with the arguments to be passed to that method,
# and returns the JSON response to the method invocation.

# IMPORTS

# Import stuff from the future.
from __future__ import absolute_import, division, print_function, \
  unicode_literals
from future import standard_library
standard_library.install_aliases()


import configparser
import json
import os
from http.cookiejar import LWPCookieJar

import requests


# CLASSES

class BMAPIResponse:
  def __init__(self, response_dict):
    for mandatory_arg in ['data', 'message', 'status']:
      if mandatory_arg not in response_dict:
        raise (
          ValueError,
          "Malformed API response is missing key '%s': %s" % (
            mandatory_arg, response_dict))
    self.data = response_dict['data']
    self.message = response_dict['message']
    self.status = response_dict['status']
    self.rand_vals = None
    self.skill_rand_vals = None
    # Only replay testing uses these return values.
    # A production site should never return them.
    if 'BM_RAND_VALS_ROLLED' in response_dict:
      self.rand_vals = response_dict['BM_RAND_VALS_ROLLED']
    if 'BM_SKILL_RAND_VALS_ROLLED' in response_dict:
      self.skill_rand_vals = response_dict['BM_SKILL_RAND_VALS_ROLLED']


class BMClient:
  def _read_rcfile(self, rcfile, site):
    config = configparser.ConfigParser()
    config.read(rcfile)
    self.url = config.get(site, "url")
    self.username = config.get(site, "username")
    self.password = config.get(site, "password")
    self.cookiefile = os.path.expanduser(config.get(site, "cookiefile"))
    try:
      self.cachedir = os.path.expanduser(config.get(site, "cachedir"))
    except configparser.NoOptionError:
      pass

  def _setup_cookies(self):
    self.session = requests.session()
    # all requests should use the same cookie jar
    if self.cookiefile is not None and os.path.isfile(self.cookiefile):
      self.cookiejar = LWPCookieJar(self.cookiefile)
      self.cookiejar.load(ignore_discard=True)
      self.session.cookies = self.cookiejar

  def __init__(self, rcfile, site):
    self.username = None
    self.password = None
    self.cookiefile = None
    self.cachedir = None
    self._read_rcfile(rcfile, site)
    self._setup_cookies()

  def _make_request(self, args):
    headers = {
      'Content-Type': 'application/x-www-form-urlencoded',
    }
    response = self.session.post(url=self.url, data=json.dumps(args),
                             headers=headers,)

    try:
      retval = response.json()
      return BMAPIResponse(retval)
    except ValueError as e:
      print("could not parse return: " + response.text)
      return False

  def login(self):
    args = {
      'type': 'login',
      'username': self.username,
      'password': self.password,
    }
    retval = self._make_request(args)
    if retval.status == 'ok':
      self.cookiejar.save(ignore_discard=True)
      return True
    return False

  def load_player_name(self):
    args = {
      'type': 'loadPlayerName',
    }
    return self._make_request(args)

  def verify_login(self):
    retval = self.load_player_name()
    if retval.status == 'ok':
      return True
    return self.login()

  def load_button_names(self):
    args = {
      'type': 'loadButtonData',
    }
    return self._make_request(args)

  def load_player_names(self):
    args = {
      'type': 'loadPlayerNames',
    }
    return self._make_request(args)

  def load_active_games(self):
    args = {
      'type': 'loadActiveGames',
    }
    return self._make_request(args)

  def load_new_games(self):
    args = {
      'type': 'loadNewGames',
    }
    return self._make_request(args)

  def react_to_new_game(self, gameId, action):
    args = {
      'type': 'reactToNewGame',
      'gameId': gameId,
      'action':  action
    }
    return self._make_request(args)

  def load_completed_games(self):
    args = {
      'type': 'loadCompletedGames',
    }
    return self._make_request(args)

  def load_game_data(self, gameId, logEntryLimit=10):
    args = {
      'type': 'loadGameData',
      'game': gameId,
      'logEntryLimit': logEntryLimit,
    }
    return self._make_request(args)

  def create_game(self, pbutton, obutton='', player='', opponent='', description='', max_wins=3, use_prev_game=False, custom_recipe_array=None):
    if player is None or player == '':
      player = self.username
    if not obutton:
      obutton = ''
    player_info_array = [
      [ player, pbutton, ],
      [ opponent, obutton, ],
    ]
    args = {
      'type': 'createGame',
      'playerInfoArray': player_info_array,
      'maxWins': max_wins,
    }
    if use_prev_game:
      args['previousGameId'] = use_prev_game
    if description:
      args['description'] = description
    if custom_recipe_array:
      args['customRecipeArray'] = custom_recipe_array
    return self._make_request(args)

  def submit_turn(self, gameId, attackerIdx, defenderIdx, dieSelectStatus, attackType, roundNumber, timestamp, turboVals, chat=''):
    args = {
      'type': 'submitTurn',
      'game': gameId,
      'attackerIdx': attackerIdx,
      'defenderIdx': defenderIdx,
      'attackType': attackType,
      'roundNumber': roundNumber,
      'timestamp': timestamp,
      'chat': chat,
      'dieSelectStatus': dieSelectStatus,
    }
    if turboVals:
      args['turboVals'] = turboVals
    return self._make_request(args)

  def submit_die_values(self, gameId, swingArray, optionArray, roundNumber, timestamp):
    args = {
      'type': 'submitDieValues',
      'game': gameId,
      'roundNumber': roundNumber,
      'timestamp': timestamp,
    }
    if swingArray:
      for [key, value] in sorted(swingArray.items()):
        args['swingValueArray'] = swingArray
    if optionArray:
      for [key, value] in sorted(optionArray.items()):
        args['optionValueArray'] = optionArray
    return self._make_request(args)

  def react_to_new_game(self, gameId, action):
    args = {
      'type': 'reactToNewGame',
      'gameId': gameId,
      'action': action,
    }
    return self._make_request(args)

  def react_to_initiative(self, gameId, action, idxArray, valueArray, roundNumber, timestamp):
    args = {
      'type': 'reactToInitiative',
      'game': gameId,
      'roundNumber': roundNumber,
      'timestamp': timestamp,
      'action': action,
      'dieIdxArray': idxArray,
      'dieValueArray': valueArray,
    }
    return self._make_request(args)

  def adjust_fire_dice(self, gameId, action, idxArray, valueArray, roundNumber, timestamp):
    args = {
      'type': 'adjustFire',
      'game': gameId,
      'roundNumber': roundNumber,
      'timestamp': timestamp,
      'action': action,
      'dieIdxArray': idxArray,
      'dieValueArray': valueArray,
    }
    return self._make_request(args)

  def choose_reserve_dice(self, gameId, action, dieIdx=None):
    args = {
      'type': 'reactToReserve',
      'game': gameId,
      'action': action,
    }
    if dieIdx is not None:
      args['dieIdx'] = dieIdx
    return self._make_request(args)

  def choose_auxiliary_dice(self, gameId, action, dieIdx=None):
    args = {
      'type': 'reactToAuxiliary',
      'game': gameId,
      'action': action,
    }
    if dieIdx is not None:
      args['dieIdx'] = dieIdx
    return self._make_request(args)
