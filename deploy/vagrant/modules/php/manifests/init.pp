class php::base {

  # Make sure php5 and libapache2-mod-php5 are installed
  package {
    "php5": ensure => installed;
    "libapache2-mod-php5": ensure => installed;
  }

  file {
    "/etc/php5/apache2/conf.d/buttonmen.ini":
      ensure => file,
      content => template("php/buttonmen.ini.erb"),
      notify => Service["apache2"],
      require => Package["libapache2-mod-php5"];

    "/etc/php5/deploy-includes":
      ensure => directory,
      require => Package["libapache2-mod-php5"];
  }

  exec {
    "php_wget_phpunit":
      command => "/usr/bin/wget --no-verbose -O /etc/php5/deploy-includes/phpunit.phar http://phar.phpunit.de/phpunit.phar",
      creates => "/etc/php5/deploy-includes/phpunit.phar",
      require => File["/etc/php5/deploy-includes"];
  }
}
