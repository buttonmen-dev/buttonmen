# Configuration for a buttonmen mysql server
class mysql::server {

  package {
    # Install mysql-server and php5-mysql packages
    "mysql-server": ensure => installed;
    "php-mysql": ensure => installed;

    # Install python-mysqldb for use by helper scripts
    "python-mysqldb": ensure => installed;
  }

  # Run mysqld
  service {
    "mysql":
      ensure => running,
      enable => true,
      require => Package["mysql-server"];
  }

  # Customize mysqld for buttonmen use
  file {
    "/etc/mysql/mysql.conf.d/buttonmen.cnf":
      ensure => file,
      content => template("mysql/buttonmen.cnf.erb"),
      notify => Service["mysql"];
  }
}
