node default {

  ## Defaults

  # Always run apt-get update before trying to install packages
  Package {
    require => Exec["apt_client_update"],
  }

  # The deploy_buttonmen_site wrapper should set these correctly
  # for ECS, based on the config file contents.
  # If you are deploying locally and don't have access to that wrapper, try:
  #   $puppet_hostname = "sandbox.buttonweavers.com"
  #   $database_fqdn = "127.0.0.1"
  $puppet_hostname = "REPLACE_WITH_PUPPET_HOSTNAME"
  $database_fqdn = "REPLACE_WITH_DATABASE_FQDN"
  $buttonmen_site_type = "REPLACE_WITH_BUTTONMEN_SITE_TYPE"
  $remote_database_password = "REPLACE_WITH_REMOTE_DATABASE_PASSWORD"
  $email_relay_host = "REPLACE_WITH_EMAIL_RELAY_HOST"
  $email_relay_sasl_creds = "REPLACE_WITH_EMAIL_RELAY_SASL_CREDS"

  $puppet_timestamp = generate('/bin/date', '+%s')

  case "$operatingsystemrelease" {
    "14.04", "16.04": {
      $puppet_apache_sitesdir = "sites-enabled"
    }
    default: {
      $puppet_apache_sitesdir = "conf.d"
    }
  }

  # Generic node configuration
  include "apt::client"
  include "ntp::client"
  include "postfix::base"
  include "syslog::base"
  include "user::buttonmen-devs"
  include "sudo::buttonmen-devs"
  include "fqdn::base"

  # Node configuration needed for the buttonmen server
  include "apache::server::vagrant"
  include "php::base"
  include "mysql::server"  
  include "buttonmen::python-api-client"
  include "buttonmen::server"

  # location-specific configuration
  case "${ec2_services_partition}" {
    "aws": {
      include "cloudwatch::buttonmen-site"
    }
  }
}
