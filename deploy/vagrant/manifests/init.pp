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
    "174.129.14.204": {
      $puppet_hostname = "alpha.buttonweavers.com"
    }
    "54.83.36.215": {
      $puppet_hostname = "dev.buttonweavers.com"
    }
    "54.235.150.227": {
      $puppet_hostname = "staging.buttonweavers.com"
    }
    "54.83.36.209": {
      $puppet_hostname = "www.buttonweavers.com"
    }
    default: {
      $puppet_hostname = "sandbox.buttonweavers.com"
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
  include "apache::server"  
  include "php::base"
  include "mysql::server"  
  include "buttonmen::server"
}
