##### create_build_dir.sh
# This script creates a clean build directory in a known tmpfs
# location if one exists, or creates the build directory locally
# if there is no tmpfs, so that builds don't fail if the tmpfs is
# missing

BASEDIR=$1
TMPDIR=/tmp/tmpfs/jenkins

# If the tmpdir exists, use it, and fail if something goes wrong
if [ -d "${TMPDIR}" ]; then

  # Blow away any stale temporary build directory, and fail if
  # that's not possible
  if [ -d "${TMPDIR}/build" ]; then
    rm -rf ${TMPDIR}/build
  fi
  if [ -e "${TMPDIR}/build" ]; then
    echo "${TMPDIR}/build exists and could not be deleted"
    exit 1
  fi

  # Create a new build directory
  mkdir ${TMPDIR}/build
  if [ ! -d "${TMPDIR}/build" ]; then
    echo "${TMPDIR}/build could not be created"
    exit 1
  fi

  ln -s ${TMPDIR}/build ${BASEDIR}/build  

# If the tmpdir doesn't exist, emit a warning and use basedir directly
else
  echo "WARNING: no tmpfs available in ${TMPDIR} - using disk for ./build"
  mkdir ${BASEDIR}/build
fi
