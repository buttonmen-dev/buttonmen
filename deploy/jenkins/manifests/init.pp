node default {

  ## Defaults

  # Always run apt-get update before trying to install packages
  Package {
    require => Exec["apt_client_update"],
  }

  # jenkins hostname is always jenkins.buttonweavers.com
  $puppet_hostname = "jenkins.buttonweavers.com"

  # Generic node configuration
  include "apt::client"
  include "postfix::base"
  include "user::buttonmen-devs"
  include "sudo::buttonmen-devs"

  # Node configuration needed for the jenkins server
  include "jenkins::server"
  include "mysql::server"
  include "buttonmen::jenkins"
}
