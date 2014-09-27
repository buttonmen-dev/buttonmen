#!/usr/bin/python
##### A utility to verify code coverage of javascript unit tests

JSMODDIR = './src/ui/js'
JSTESTDIR = './test/src/ui/js'
JSTESTINDEX = './test/src/ui/index.html'
JSTESTPHANTOMINDEX = './test/src/ui/phantom-index.html'

import os
import re
import sys

errors = []

def find_test_files():
  testpairs = []
  for filename in os.listdir(JSMODDIR):
    if not filename.endswith('.js'): continue
    modname = filename[:-3]
    srcpath = JSMODDIR + "/" + filename
    testpath = JSTESTDIR + "/test_" + filename
    if not os.path.isfile(srcpath):
      errors.append("Source dir entry %s is not a file" % srcpath)
      continue
    if not os.path.isfile(testpath):
      errors.append("Source file %s has no test file" % srcpath)
      continue
    testpairs.append([modname, srcpath, testpath])
  return testpairs

def check_test_file_functions(modname, srcpath, testpath):
  function_name_re = re.compile('^(%s\.\S+) = function\(' % modname)
  test_name_re = re.compile('^(test|asyncTest)\("test_([^"]+)"')

  # Populate the list of functions we expect will be tested
  functions_missing = ['%s_is_loaded' % modname, ]
  f = open(srcpath)
  for line in f.readlines():
    mobj = function_name_re.match(line)
    if mobj:
      funcname = mobj.group(1)
      functions_missing.append(funcname)
  f.close()

  # Any functions that are tested, remove from the list of missing
  # functions.  For now, assume additional tests are harmless, and
  # don't comment on them
  f = open(testpath)
  for line in f.readlines():
    mobj = test_name_re.match(line)
    if mobj:
      function_tested = mobj.group(2)
      if function_tested in functions_missing:
        functions_missing.remove(function_tested)

  # Untested functions are an error
  for function_missing in functions_missing:
    errors.append(
      "Function %s in source file %s has no test" % (function_missing, srcpath))

def check_index_file_inclusions(testpairs):
  script_include_re = re.compile('^ *<script src="([^"]+)"')

  includes_missing = {}
  for [modname, srcpath, testpath] in testpairs:
    includes_missing["/ui/js/" + modname + ".js"] = srcpath
    includes_missing["js/test_" + modname + ".js"] = testpath
  f = open(JSTESTINDEX)
  for line in f.readlines():
    mobj = script_include_re.match(line)
    if mobj:
      file_included = mobj.group(1) 
      if file_included in includes_missing:
        includes_missing.pop(file_included)

  for file_included in sorted(includes_missing.keys()):
    errors.append(
      "Test index %s is missing a script include for %s corresponding to %s" \
      % (JSTESTINDEX, file_included, includes_missing[file_included]))

def check_phantom_index_file_inclusions(testpairs):
  script_include_re = re.compile('^ *<script src="([^"]+)"')

  includes_missing = {}
  for [modname, srcpath, testpath] in testpairs:
    includes_missing["../../../src/ui/js/" + modname + ".js"] = srcpath
    includes_missing["js/test_" + modname + ".js"] = testpath
  f = open(JSTESTPHANTOMINDEX)
  for line in f.readlines():
    mobj = script_include_re.match(line)
    if mobj:
      file_included = mobj.group(1) 
      if file_included in includes_missing:
        includes_missing.pop(file_included)

  for file_included in sorted(includes_missing.keys()):
    errors.append(
      "Test index %s is missing a script include for %s corresponding to %s" \
      % (JSTESTPHANTOMINDEX, file_included, includes_missing[file_included]))

testpairs = find_test_files()
print "Looking for JavaScript unit tests to match Button Men spec: %s" % testpairs
for [modname, srcpath, testpath] in testpairs:
  check_test_file_functions(modname, srcpath, testpath)
check_index_file_inclusions(testpairs)
check_phantom_index_file_inclusions(testpairs)

if len(errors) > 0:
  print "JavaScript code coverage problems were found:"
  for errtext in errors:
    print "  " + errtext
  sys.exit(1)
print "OK"
sys.exit(0)
