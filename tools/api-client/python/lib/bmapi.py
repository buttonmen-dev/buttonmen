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

class BMClient():
  def _read_rcfile(self, rcfile, site):
    config = ConfigParser.ConfigParser()
    config.read(rcfile)
    self.url = config.get(site, "url")
    self.username = config.get(site, "username")
    self.password = config.get(site, "password")
    self.cookiefile = config.get(site, "cookiefile")

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
    self._read_rcfile(rcfile, site)
    self._setup_cookies()

  def _make_request(self, args):
    data = urllib.urlencode(args, True)
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

  def load_game_data(self, gameId):
    args = {
      'type': 'loadGameData',
      'game': gameId,
    }
    return self._make_request(args)

  def create_game(self, pbutton, obutton=None, opponent=None, max_wins=3):
    player_info = [self.username, pbutton, ]
    opponent_info = [opponent, obutton, ]
    args = {
      'type': 'createGame',
      'playerInfoArray[0][]': player_info,
      'playerInfoArray[1][]': opponent_info,
      'maxWins': max_wins,
    }
    return self._make_request(args)
