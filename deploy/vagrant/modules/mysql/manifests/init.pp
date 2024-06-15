# Configuration for a buttonmen mysql server
class mysql::server {

  # Prerequisites for upgrade to MySQL 8.0
  file {
    "/usr/local/etc/mysql_apt_config.dat":
      ensure => file,
      content => template("mysql/mysql_apt_config.dat.erb");

    "/etc/init.d/mysql":
      ensure => file,
      mode => 0544,
      content => template("mysql/init_mysql.erb");

    "/usr/local/sbin/configure_mysql_apt":
      ensure => file,
      mode => 0544,
      content => template("mysql/configure_mysql_apt.erb");
  }

  exec {
    "mysql_configure_apt":
      command => "/usr/local/sbin/configure_mysql_apt",
      require => [ File["/usr/local/etc/mysql_apt_config.dat"], File["/usr/local/sbin/configure_mysql_apt"] ];
  }

  # Install mysql-server only if this site doesn't use RDS
  # Always install a mysql client for e.g. database backups
  case "$database_fqdn" {
    "127.0.0.1": {
      package {
        "mysql-server":
          ensure => installed,
          install_options => ["--allow-unauthenticated", "-f"],
          require => Exec["mysql_configure_apt"];
      }
    }
    default: {
      package {
        "mysql-server": ensure => absent;
        "mysql-client":
          ensure => installed,
          install_options => ["--allow-unauthenticated", "-f"],
          require => Exec["mysql_configure_apt"];
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
  case "$database_fqdn" {
    "127.0.0.1": {
      file {
        "/etc/mysql/mysql.conf.d/buttonmen.cnf":
          ensure => file,
          content => template("mysql/buttonmen.cnf.erb"),
          notify => Service["mysql"];
      }
    }
  }
}
