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
/etc/init.d/mysql start

# Container should keep running
sleep infinity
