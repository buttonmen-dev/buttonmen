#!/bin/bash
##### Services docker containers need to run

set -e
set -x

# System services
/etc/init.d/rsyslog start
/etc/init.d/cron start
/etc/init.d/ssh start
/etc/init.d/postfix start

## Remote filesystem setup
FQDN=$(cat /usr/local/etc/bmsite_fqdn)
MNT_DIR="/mnt/efs/${FQDN}"
if [ ! -e "${MNT_DIR}" ]; then
  mkdir ${MNT_DIR}
fi

# Replace the /srv/backup in the image with a remotely-mounted one
if [ ! -e "${MNT_DIR}/backup" ]; then
  mkdir ${MNT_DIR}/backup
  chown root:adm ${MNT_DIR}/backup
  chmod 750 ${MNT_DIR}/backup
fi
rmdir /srv/backup
ln -s ${MNT_DIR}/backup /srv/backup

# Replace the /etc/letsencrypt in the image with a remotely-mounted one
if [ ! -e "${MNT_DIR}/letsencrypt" ]; then
  mkdir ${MNT_DIR}/letsencrypt
fi
mv /etc/letsencrypt/* ${MNT_DIR}/letsencrypt/
rmdir /etc/letsencrypt
ln -s ${MNT_DIR}/letsencrypt /etc/letsencrypt

# If /etc/letsencrypt/live exists, there's an existing cert for
# this domain, and we need to install it for apache.
# (If it doesn't exist, we may not have DNS yet, so it's not safe to run certbot.)
if [ -d /etc/letsencrypt/live ]; then
  /usr/local/bin/apache_setup_certbot
fi

# Buttonmen services
/etc/init.d/apache2 start
if [ -f /etc/init.d/mysql ]; then
  /etc/init.d/mysql start
fi

# Set site type so it's correct in UI JS
/usr/local/bin/set_buttonmen_config

# Send hello world e-mail
/usr/local/bin/send_hello_world_email

# Container should keep running
sleep infinity
