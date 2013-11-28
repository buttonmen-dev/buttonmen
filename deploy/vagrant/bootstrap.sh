#!/bin/sh
##### Minimal bootstrap to install puppet on each vagrant node

# Exit if anything goes wrong
set -e

# Make sure puppet is installed
if which puppet > /dev/null ; then
  echo "Puppet is already installed"
else
  apt-get -y install puppet
fi

# grab the git tag and put it in a version file
if [ -f /buttonmen/.git/FETCH_HEAD ]; then
  cut -f 1 /buttonmen/.git/FETCH_HEAD /etc/buttonmen_version
else
  touch /etc/buttonmen_version
fi 
