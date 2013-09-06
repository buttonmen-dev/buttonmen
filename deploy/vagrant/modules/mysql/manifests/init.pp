# Configuration for a buttonmen mysql server
class mysql::server {

  # Install mysql-server and php5-mysql packages
  package {
    "mysql-server": ensure => installed;
    "php5-mysql": ensure => installed;
  }
}
