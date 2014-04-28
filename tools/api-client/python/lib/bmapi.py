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
      return retval
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
    if retval['status'] == 'ok':
      self.cookiejar.save(ignore_discard=True)
      return True
    return False

  def load_player_name(self):
    args = {
      'type': 'loadPlayerName',
    }
    retval = self._make_request(args)
    return retval

  def verify_login(self):
    retval = self.load_player_name()
    if retval['status'] == 'ok':
      return True
    return self.login()

  def load_button_names(self):
    args = {
      'type': 'loadButtonNames',
    }
    retval = self._make_request(args)
    return retval

  def load_player_names(self):
    args = {
      'type': 'loadPlayerNames',
    }
    retval = self._make_request(args)
    return retval

  def load_active_games(self):
    args = {
      'type': 'loadActiveGames',
    }
    retval = self._make_request(args)
    return retval

  def load_completed_games(self):
    args = {
      'type': 'loadCompletedGames',
    }
    retval = self._make_request(args)
    return retval

  def load_game_data(self, gameId):
    args = {
      'type': 'loadGameData',
      'game': gameId,
    }
    retval = self._make_request(args)
    return retval

  def create_game(self, opponent, pbutton, obutton):
    args = {
      'type': 'createGame',
      'playerNameArray[]': [self.username, opponent],
      'buttonNameArray[]': [pbutton, obutton],
      'maxWins': 3,
    }
    retval = self._make_request(args)
    return retval
