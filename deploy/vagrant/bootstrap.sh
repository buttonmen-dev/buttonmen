#!/bin/sh
##### Minimal bootstrap to install puppet on each vagrant node

# Exit if anything goes wrong
set -e

if which puppet > /dev/null ; then
  echo "Puppet is already installed"
  exit 0
fi

apt-get -y install puppet
