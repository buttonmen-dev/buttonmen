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
    self.assertEqual(r.status, 'ok',
      'loadButtonNames returns successfully')
#    self.assertEqual(r.data, 'foobar')

if __name__ == '__main__':
  if (not os.getenv('BMAPI_TEST_TYPE') or
      os.getenv('BMAPI_TEST_TYPE') not in TEST_URLS):
    raise ValueError, \
      "Set BMAPI_TEST_TYPE environment variable.  Valid choices: %s" % (
      (" ".join(sorted(TEST_URLS.keys()))))
  TEST_TYPE = os.getenv('BMAPI_TEST_TYPE')
  unittest.main()
