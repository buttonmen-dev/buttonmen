node default {

  ## Defaults

  # Don't need apt-get update on circleci because it was run by the bootstrap

  $puppet_hostname = "sandbox.buttonweavers.com"
  $database_fqdn = "127.0.0.1"
  $buttonmen_site_type = "production"
  $puppet_timestamp = generate('/bin/date', '+%s')

  $puppet_apache_sitesdir = "sites-enabled"

  # Generic node configuration
  include "apt::client"
  include "ntp::client"
  include "postfix::base"
  include "fqdn::base"

  # Node configuration needed for the circleci buttonmen server
  include "apache::server::circleci"
  include "php::type::circleci"
  include "mysql::server"  
  include "buttonmen::python-api-client"
  include "buttonmen::server"
  include "javascript::type::circleci"
}
