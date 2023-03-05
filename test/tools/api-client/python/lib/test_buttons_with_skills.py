import os
import sys
import tempfile
import unittest

import script_test_helpers

class TestButtonsWithSkills(unittest.TestCase):
  def setUp(self):
    script_test_helpers.copy_replace_script(
      'buttons-with-skills',
      '  bmconn = bmutils.BMClientParser(os.path.expanduser(args.config), args.site)\n',
      ['  ' + x for x in script_test_helpers.DUMMY_SETUP_LINES] + [
        '  bmconn = bmutils.BMClientParser(None, None, d)\n',
      ]
    )
    self.tempdir = tempfile.mkdtemp()

  def tearDown(self):
    script_test_helpers.remove_script_copy('buttons-with-skills')

  def test_buttons_with_skills(self):
    output = script_test_helpers.execute_script_copy(self.tempdir, ["Jolt"])
    self.assertEqual(
      output, 
      "Button of Loathing: Jk(13) (6) (6) (20) (R,R)\n" +
      "devious: dv(S) (16) (16) pqr(S,S) Jm`(0) Jm`(0) Jm`(0)\n" +
      "jimmosk: (4) %(8) g(12) JIMmo(S) k(2)\n")

if __name__ == '__main__':
  if not os.getenv('BMAPI_TEST_TYPE'):
    raise ValueError("Set BMAPI_TEST_TYPE environment variable")
  unittest.main()
