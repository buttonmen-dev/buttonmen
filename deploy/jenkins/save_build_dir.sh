##### save_build_dir.sh
# Check whether the build dir is in tmpfs, and copy it back to disk
# if so

BASEDIR=$1
TMPDIR=/tmp/tmpfs/jenkins

if [ -h "${BASEDIR}/build" ]; then
  mv ${BASEDIR}/build ${BASEDIR}/build.tmp
  mkdir ${BASEDIR}/build
  rsync -a ${BASEDIR}/build.tmp/ ${BASEDIR}/build/
  rm ${BASEDIR}/build.tmp
fi
