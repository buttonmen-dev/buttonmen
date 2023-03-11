import os
import sys
import tempfile
import unittest

import script_test_helpers

class TestGameInfo(unittest.TestCase):
  def setUp(self):
    script_test_helpers.copy_replace_script(
      'game_info',
      'b = bmutils.BMClientParser(opts.config, opts.site)\n',
      script_test_helpers.DUMMY_SETUP_LINES + [
        'b = bmutils.BMClientParser(None, None, d)\n',
      ]
    )
    self.tempdir = tempfile.mkdtemp()

  def tearDown(self):
    script_test_helpers.remove_script_copy('game_info')

  def test_game_info(self):
    output = script_test_helpers.execute_script_copy(self.tempdir, ["1915"])
    self.assertEqual(
      output, 
      "responder003: Pikathulhu: (6) c(6) (10) (12) c(X) \n" +
      "responder004: Phoenix:    (4) (6) f(8) (10) f(20) \n")

if __name__ == '__main__':
  if not os.getenv('BMAPI_TEST_TYPE'):
    raise ValueError("Set BMAPI_TEST_TYPE environment variable")
  unittest.main()
