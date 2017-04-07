##### bmapi.py
# This library strictly implements the Button Men API in python.
# The BMClient class provides one function for each API method,
# which is called with the arguments to be passed to that method,
# and returns the JSON response to the method invocation.
import ConfigParser
import cookielib
import json
import os
import urllib
import urllib2

class BMAPIResponse():
  def __init__(self, response_dict):
    for mandatory_arg in ['data', 'message', 'status']:
      if not mandatory_arg in response_dict:
        raise ValueError, "Malformed API response is missing key '%s': %s" % (
          mandatory_arg, response_dict)
    self.data = response_dict['data']
    self.message = response_dict['message']
    self.status = response_dict['status']
    self.rand_vals = None
    # Only replay testing uses this return value.
    # A production site should never return it.
    if 'BM_RAND_VALS_ROLLED' in response_dict:
      self.rand_vals = response_dict['BM_RAND_VALS_ROLLED']

class BMClient():
  def _read_rcfile(self, rcfile, site):
    config = ConfigParser.ConfigParser()
    config.read(rcfile)
    self.url = config.get(site, "url")
    self.username = config.get(site, "username")
    self.password = config.get(site, "password")
    self.cookiefile = os.path.expanduser(config.get(site, "cookiefile"))
    try:
      self.cachedir = os.path.expanduser(config.get(site, "cachedir"))
    except ConfigParser.NoOptionError:
      pass

  def _setup_cookies(self):
    # all requests should use the same cookie jar
    self.cookiejar = cookielib.LWPCookieJar(self.cookiefile)
    if os.path.isfile(self.cookiefile):
      self.cookiejar.load(ignore_discard=True)
    self.cookieprocessor = urllib2.HTTPCookieProcessor(self.cookiejar)
    self.opener = urllib2.build_opener(self.cookieprocessor)
    urllib2.install_opener(self.opener)

  def __init__(self, rcfile, site):
    self.username = None
    self.password = None
    self.cookiefile = None
    self.cachedir = None
    self._read_rcfile(rcfile, site)
    self._setup_cookies()

  def _make_request(self, args):
    tuples = []
    for [key, value] in sorted(args.items()):
      tuples.append((key, value))
    data = urllib.urlencode(tuples, True)
    headers = {
      'Content-Type': 'application/x-www-form-urlencoded',
    }
    req = urllib2.Request(self.url, data, headers)
    response = urllib2.urlopen(req)
    jsonval = response.read()
    try:
      retval = json.loads(jsonval)
      return BMAPIResponse(retval)
    except Exception, e:
      print "could not parse return: " + jsonval
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

  def create_game(self, pbutton, obutton=None, opponent=None, max_wins=3, use_prev_game=False):
    player_info = [self.username, pbutton, ]
    opponent_info = [opponent, obutton, ]
    args = {
      'type': 'createGame',
      'playerInfoArray[0][]': player_info,
      'playerInfoArray[1][]': opponent_info,
      'maxWins': max_wins,
    }
    if use_prev_game:
      args['previousGameId'] = use_prev_game
    return self._make_request(args)

  def submit_turn(self, gameId, attackerIdx, defenderIdx, dieSelectStatus, attackType, roundNumber, timestamp, chat=''):
    args = {
      'type': 'submitTurn',
      'game': gameId,
      'attackerIdx': attackerIdx,
      'defenderIdx': defenderIdx,
      'attackType': attackType,
      'roundNumber': roundNumber,
      'timestamp': timestamp,
      'chat': chat,
    }
    for statkey in sorted(dieSelectStatus.keys()):
      args['dieSelectStatus[%s]' % statkey] = dieSelectStatus[statkey]
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
        args['swingValueArray[%s]' % key] = value
    if optionArray:
      for [key, value] in sorted(optionArray.items()):
        args['optionValueArray[%s]' % key] = value
    return self._make_request(args)

  def react_to_initiative(self, gameId, action, idxArray, valueArray, roundNumber, timestamp):
    args = {
      'type': 'reactToInitiative',
      'game': gameId,
      'roundNumber': roundNumber,
      'timestamp': timestamp,
      'action': action,
    }
    for i in range(len(idxArray)):
      args['dieIdxArray[%d]' % i] = idxArray[i]
    for i in range(len(valueArray)):
      args['dieValueArray[%d]' % i] = valueArray[i]
    return self._make_request(args)

  def adjust_fire_dice(self, gameId, action, idxArray, valueArray, roundNumber, timestamp):
    args = {
      'type': 'adjustFire',
      'game': gameId,
      'roundNumber': roundNumber,
      'timestamp': timestamp,
      'action': action,
    }
    for i in range(len(idxArray)):
      args['dieIdxArray[%d]' % i] = idxArray[i]
    for i in range(len(valueArray)):
      args['dieValueArray[%d]' % i] = valueArray[i]
    return self._make_request(args)

  def choose_reserve_dice(self, gameId, action, dieIdx):
    args = {
      'type': 'reactToReserve',
      'game': gameId,
      'action': action,
    }
    if dieIdx:
      args['dieIdx'] = dieIdx
    return self._make_request(args)

  def choose_auxiliary_dice(self, gameId, action, dieIdx):
    args = {
      'type': 'reactToAuxiliary',
      'game': gameId,
      'action': action,
    }
    if dieIdx:
      args['dieIdx'] = dieIdx
    return self._make_request(args)
