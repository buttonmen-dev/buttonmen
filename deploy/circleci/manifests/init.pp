node default {

  ## Defaults

  # Don't need apt-get update on circleci because it was run by the bootstrap

  $puppet_hostname = "sandbox.buttonweavers.com"
  $puppet_timestamp = generate('/bin/date', '+%s')

  $puppet_apache_sitesdir = "sites-enabled"

  # Generic node configuration
  include "apt::client"
  include "ntp::client"
  include "postfix::base"
  include "user::buttonmen-devs"
  include "sudo::buttonmen-devs"

  # Node configuration needed for the circleci buttonmen server
  include "apache::server::circleci"
  include "php::type::circleci"
  include "mysql::server"  
  include "buttonmen::server"
  include "javascript::type::circleci"
}
