node default {

  ## Defaults

  # Always run apt-get update before trying to install packages
  Package {
    require => Exec["apt_client_update"],
  }

  # The deploy_stats_lambda wrapper should set these correctly
  # for ECS, based on the config file contents.
  $puppet_hostname = "REPLACE_WITH_PUPPET_HOSTNAME"
  $database_fqdn = "REPLACE_WITH_DATABASE_FQDN"
  $buttonmen_site_type = "REPLACE_WITH_BUTTONMEN_SITE_TYPE"
  $remote_database_password = "REPLACE_WITH_REMOTE_DATABASE_PASSWORD"

  $puppet_timestamp = generate('/bin/date', '+%s')

  # Generic node configuration
  include "apt::client::stats"
  include "fqdn::base"

  # Node configuration needed for stats
  include "mysql::server"
  include "lambda::stats"
}
