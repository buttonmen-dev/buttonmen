node default {

  ## Defaults

  # Always run apt-get update before trying to install packages
  Package {
    require => Exec["apt_client_update"],
  }

  # Don't use facter to get hostname and domain because these are
  # wrong for EC2, and don't bother to lookup IPs in DNS because
  # we have very few hosts.  Just hardcode the list of roles.
  case "$ec2_public_ipv4" {
    "54.147.204.115": {
      $puppet_hostname = "ec2-54-147-204-115.compute-1.amazonaws.com"
      $database_fqdn = "buttonmen-cgolubi1-2523-rds.cyk4kpmwmefe.us-east-1.rds.amazonaws.com"
    }
    "174.129.14.204": {
      $puppet_hostname = "alpha.buttonweavers.com"
      $database_fqdn = "127.0.0.1"
    }
    "54.83.36.215": {
      $puppet_hostname = "dev.buttonweavers.com"
      $database_fqdn = "127.0.0.1"
    }
    "54.235.150.227": {
      $puppet_hostname = "staging.buttonweavers.com"
      $database_fqdn = "buttonmen-staging.cyk4kpmwmefe.us-east-1.rds.amazonaws.com"
    }
    "54.83.36.209": {
      $puppet_hostname = "www.buttonweavers.com"
      $database_fqdn = "buttonmen-prod.cyk4kpmwmefe.us-east-1.rds.amazonaws.com"
    }
    default: {
      $puppet_hostname = "sandbox.buttonweavers.com"
      $database_fqdn = "127.0.0.1"
    }
  }
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
  include "user::buttonmen-devs"
  include "sudo::buttonmen-devs"

  # Node configuration needed for the buttonmen server
  include "apache::server::vagrant"
  include "php::base"
  include "mysql::server"  
  include "buttonmen::server"

  # location-specific configuration
  case "${ec2_services_partition}" {
    "aws": {
      include "cloudwatch::buttonmen-site"
    }
  }
}
