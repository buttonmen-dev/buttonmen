#!/bin/bash
##### Verify dummy responder files are available for QUnit testing

# This script attempts to workaround some hypothesized virtual disk
# caching used within CircleCI, and make sure the dummy data files
# produced by phpunit testing are available on disk before they
# need to be used by QUnit testing.
#
# The assumption is that if this script, which is being run in a
# separate process from the rsync that wrote the files, can see the
# files and see that they have nonzero size, then it is safe to proceed

# echo what the script does
set -x

# target directory
SOURCEDIR=$1
TARGETDIR=$2

srccount=$(find ${SOURCEDIR} | wc -l | awk '{print $1}')
srcsize=$(du -sm ${SOURCEDIR} | awk '{print $1}')

for retry in 1 2 3 4 5 6 7 8 9 10; do
  if [ -d "${TARGETDIR}" ]; then
    tgtcount=$(find ${TARGETDIR} | wc -l | awk '{print $1}')
    if [ "${srccount}" = "${tgtcount}" ]; then
      tgtsize=$(du -sm ${TARGETDIR} | awk '{print $1}')
      if [ "${srcsize}" = "${tgtsize}" ]; then
        exit 0
      fi
    fi
  fi
  sleep 5
done

echo "Could not verify existence of files in ${TARGETDIR} after 10 attempts"
exit 1
