import os
import sys
import tempfile
import unittest

import script_test_helpers

class TestGameData(unittest.TestCase):
  def setUp(self):
    script_test_helpers.copy_replace_script(
      'game-data',
      '  bmconn = bmutils.BMClientParser(os.path.expanduser(args.config), args.site)\n',
      ['  ' + x for x in script_test_helpers.DUMMY_SETUP_LINES] + [
        '  bmconn = bmutils.BMClientParser(None, None, d)\n',
      ]
    )
    self.tempdir = tempfile.mkdtemp()

  def tearDown(self):
    script_test_helpers.remove_script_copy('game-data')

  def test_buttons_with_skills(self):
    output = script_test_helpers.execute_script_copy(self.tempdir, ["--format", "json", "1915"])
    self.assertTrue(output.startswith('{\n "activePlayerIdx": null,'))

if __name__ == '__main__':
  if not os.getenv('BMAPI_TEST_TYPE'):
    raise ValueError("Set BMAPI_TEST_TYPE environment variable")
  unittest.main()
