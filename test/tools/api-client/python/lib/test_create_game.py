import os
import sys
import tempfile
import unittest

import script_test_helpers

class TestCreateGame(unittest.TestCase):
  def setUp(self):
    script_test_helpers.copy_replace_script(
      'create_game',
      'b = bmutils.BMClientParser(opts.config, opts.site)\n',
      script_test_helpers.DUMMY_SETUP_LINES + [
        'b = bmutils.BMClientParser(None, None, d)\n',
      ]
    )
    self.tempdir = tempfile.mkdtemp()

  def tearDown(self):
    script_test_helpers.remove_script_copy('create_game')

  def test_create_game(self):
    # The script is interactive, and would be a bit hard to test
    # for a couple of reasons:
    # * only allows ACTIVE players, so tester2 and not any of the responderNNN ones
    # * requires interactive input (could override with a flag)
    # * sends inputs to createGame with dummy responder won't be expecting
    # So just test script loading / help text for now
    output = script_test_helpers.execute_script_copy(self.tempdir, ["-h"])
    self.assertEqual(
      output, 
      "Usage: SUT [options]\n\n" +
      "Options:\n" +
      "  -h, --help            show this help message and exit\n" +
      "  -c CONFIG, --config=CONFIG\n" +
      "                        config file containing site parameters\n" +
      "  -s SITE, --site=SITE  buttonmen site to access\n" +
      "  -o OPPONENT, --opponent=OPPONENT\n" +
      "                        opponent to fight\n" +
      "  -p, --play-all        create a game against each opponent you aren't\n" +
      "                        currently playing\n" +
      "  -l, --list-buttons    list buttons with all of the specified skills\n" +
      "  -k, --play-skills     create a game against each opponent using buttons with\n" +
      "                        the specified skills\n")

if __name__ == '__main__':
  if not os.getenv('BMAPI_TEST_TYPE'):
    raise ValueError("Set BMAPI_TEST_TYPE environment variable")
  unittest.main()
