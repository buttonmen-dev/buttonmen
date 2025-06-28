#!/bin/bash
##### Services docker containers need to run

set -e
set -x

# System services
/etc/init.d/rsyslog start
/etc/init.d/cron start
/etc/init.d/ssh start
/etc/init.d/postfix start

# Host identity variables
FQDN=$(cat /usr/local/etc/bmsite_fqdn)
HOSTNAME_LOCALIP_PART=$(/bin/hostname | awk -F\. '{print $1}')

## Remote filesystem setup
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

# Replace the /var/log/apache2 in the image with a remotely-mounted one
# Add a per-container piece because logs may be written during deployments
APACHE_LOG_DIR="${MNT_DIR}/apache_logs/${HOSTNAME_LOCALIP_PART}"
if [ ! -e "${APACHE_LOG_DIR}" ]; then
  mkdir -p ${APACHE_LOG_DIR}
  chown root:adm ${APACHE_LOG_DIR}
  chmod 750 ${APACHE_LOG_DIR}
fi
mv -f /var/log/apache2/* ${APACHE_LOG_DIR}/
rmdir /var/log/apache2
ln -s ${APACHE_LOG_DIR} /var/log/apache2

# Replace the /etc/letsencrypt in the image with a remotely-mounted one
if [ ! -e "${MNT_DIR}/letsencrypt" ]; then
  mkdir ${MNT_DIR}/letsencrypt
fi
mv /etc/letsencrypt/* ${MNT_DIR}/letsencrypt/
rmdir /etc/letsencrypt
ln -s ${MNT_DIR}/letsencrypt /etc/letsencrypt

# If there's an existing cert for this domain, install it to apache
/usr/local/bin/apache_setup_certbot existing_cert

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
