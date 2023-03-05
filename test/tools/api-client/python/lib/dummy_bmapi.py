##### dummy_bmapi.py
# This is a drop-in replacement for bmapi that talks to the dummy
# endpoint which vends canned output from responder tests

import os
import sys

mydir = os.path.dirname(os.path.realpath(sys.argv[0]))
tooldir = mydir + '/../../../../../tools/api-client/python/lib/'
sys.path.append(tooldir)
import bmapi

TEST_URLS = {
  'vagrant_local': 'http://localhost/api/dummy_responder',
  'circleci': 'http://localhost/api/dummy_responder.php',
}

# Alternate BMClient which overrides rcfile processing and cookie/login
# use, in order to work correctly for the dummy responder case
class BMClient(bmapi.BMClient):
  def __init__(self, test_type):
    assert test_type in TEST_URLS, "Unknown dummy responder test type %s" % test_type
    self.url = TEST_URLS[test_type]
    self.username = 'tester1'
    self.password = None
    self.cookiefile = None
    self.cachedir = None
    self._setup_cookies()
