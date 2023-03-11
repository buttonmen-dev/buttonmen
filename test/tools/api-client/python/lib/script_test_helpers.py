import os
import sys

BMDIR = '/buttonmen/tools/api-client/python'

DUMMY_SETUP_LINES = [
  'import os\n',
  'TEST_TYPE = os.getenv("BMAPI_TEST_TYPE")\n',
  'import sys\n',
  'sys.path.append("../../../test/tools/api-client/python/lib")\n',
  'import dummy_bmapi\n',
  'd = dummy_bmapi.BMClient(TEST_TYPE)\n',
]

def copy_replace_script(filename, srcline, dstlines):
  curdir = os.getcwd()
  os.chdir(BMDIR)
  dst_filename = 'SUT'
  assert not os.path.exists(dst_filename)
  f = open(filename)
  g = open(dst_filename, 'w')
  for line in f.readlines():
    if line == srcline:
      for dstline in dstlines:
        g.write(dstline)
      continue
    g.write(line)
  g.close()
  f.close()
  os.chdir(curdir)

def remove_script_copy(filename):
  curdir = os.getcwd()
  os.chdir(BMDIR)
  dst_filename = 'SUT'
  os.remove(dst_filename)
  os.chdir(curdir)

def execute_script_copy(tempdir, args):
  outfile = '%s/output' % tempdir
  curdir = os.getcwd()
  os.chdir(BMDIR)
  os.system('%s SUT %s > %s' % (sys.executable, ' '.join(args), outfile))
  os.chdir(curdir)
  f = open(outfile)
  output = f.read()
  f.close()
  return output
