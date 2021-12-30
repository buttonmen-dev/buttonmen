# Configuration for a buttonmen mysql server
class mysql::server {

  # Install mysql-server only if this site doesn't use RDS
  case "$database_fqdn" {
    "127.0.0.1": {
      package {
        "mysql-server": ensure => installed;
      }
    }
    default: {
      package {
        "mysql-server": ensure => absent;
      }
    }
  }
  package {
    # Install php5-mysql package
    "php-mysql": ensure => installed;

    # Install python-mysqldb for use by helper scripts
    "python-mysqldb": ensure => installed;
  }

  # Run mysqld if we're using a local database; otherwise stop it
  case "$database_fqdn" {
    "127.0.0.1": {
      service {
        "mysql":
          ensure => running,
          enable => true,
          require => Package["mysql-server"];
      }
    }
    default: {
      service {
        "mysql":
          ensure => stopped,
          enable => false,
          require => Package["mysql-server"];
      }
    }
  }

  # Customize mysqld for buttonmen use
  file {
    "/etc/mysql/mysql.conf.d/buttonmen.cnf":
      ensure => file,
      content => template("mysql/buttonmen.cnf.erb"),
      notify => Service["mysql"];
  }
}
