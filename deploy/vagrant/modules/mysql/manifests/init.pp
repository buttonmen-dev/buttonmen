# Configuration for a buttonmen mysql server
class mysql::server {

  package {
    # Install mysql-server and php5-mysql packages
    "mysql-server": ensure => installed;
    "php5-mysql": ensure => installed;

    # Install python-mysqldb for use by helper scripts
    "python-mysqldb": ensure => installed;
  }
}
