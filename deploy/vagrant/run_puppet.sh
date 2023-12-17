#!/bin/sh
##### Apply the puppet configuration and ensure it succeeds

# Run puppet
puppet apply --detailed-exitcodes --modulepath=/buttonmen/deploy/vagrant/modules /buttonmen/deploy/vagrant/manifests/init.pp

# Check the exit code.
# (See https://www.puppet.com/docs/puppet/8/man/apply.html)
# Since the script is intended to be run on a new container, we expect an exit status of 2 (The run succeeded, and some resources were changed)
PUPPET_EXITCODE=$?
if [ "${PUPPET_EXITCODE}" != "2" ]; then
  echo "Expected puppet to exit with code 2, but instead got: ${PUPPET_EXITCODE}"
  exit 1
fi

exit 0
