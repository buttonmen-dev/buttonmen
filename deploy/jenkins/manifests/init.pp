node default {

  ## Defaults

  # Always run apt-get update before trying to install packages
  Package {
    require => Exec["apt_client_update"],
  }

  # Generic node configuration
  include "apt::client"
  include "postfix::base"
  include "user::buttonmen-devs"
  include "sudo::buttonmen-devs"

  # Node configuration needed for the buttonmen server
#  include "apache::server"  
#  include "php::base"
#  include "mysql::server"  
#  include "buttonmen::server"
}
