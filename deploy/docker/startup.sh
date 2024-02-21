#!/bin/bash
##### Services docker containers need to run

set -e
set -x

# System services
/etc/init.d/rsyslog start
/etc/init.d/cron start
/etc/init.d/ssh start
/etc/init.d/postfix start

# Buttonmen services
/etc/init.d/apache2 start
if [ -f /etc/init.d/mysql ]; then
  /etc/init.d/mysql start
fi

# Set site type so it's correct in UI JS
/usr/local/bin/set_buttonmen_config

# Container should keep running
sleep infinity
