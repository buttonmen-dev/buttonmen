#!/bin/sh
##### Minimal bootstrap to install puppet on each vagrant node

# Exit if anything goes wrong
set -e

export DEBIAN_FRONTEND=noninteractive

# Make sure puppet is installed
if which puppet > /dev/null ; then
  echo "Puppet is already installed"
else
  apt-get update
  apt-get -y install puppet
fi

