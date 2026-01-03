#!/usr/bin/python3
##### A utility to verify that all code files use Unix-style newlines

import os
import sys

SKIPPATHS = [

  # non-ascii files/directories
  './notes',
  './src/dev_favicon.ico',
  './src/favicon.ico',
  './src/favicon_monitor.ico',
  './src/staging_favicon.ico',
  './src/apple-touch-icon.png',
  './src/ui/images',
  './src/ui/js/extern/images',

  # git artifacts
  './.git',

  # vagrant artifacts
  './deploy/vagrant',

  # test artifacts
  # TODO: some of these might be jenkins-only and thus no longer needed
  './build',
  './stop.txt',
  './util/grunt/node_modules',
]

def find_files(dirname):
  files = []
  for item in os.listdir(dirname):
    itempath = '%s/%s' % (dirname, item)
    if itempath in SKIPPATHS:
      continue
    if os.path.isdir(itempath):
      files.extend(find_files(itempath))
    elif os.path.isfile(itempath):
      files.append(itempath)
    else:
      raise ValueError("%s is neither a directory nor a file" % itempath)
  return files

def file_has_unix_endings(filepath):
  f = open(filepath)
  f.readlines()
  if f.newlines == '\n':
    return True
  return False

badfiles = []
for filepath in find_files('.'):
  if not file_has_unix_endings(filepath):
    badfiles.append(filepath)
if len(badfiles) > 0:
  print("Some files lack Unix-style line termination: %s" % badfiles)
  sys.exit(1)
print("OK")
sys.exit(0)
