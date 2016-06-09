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

  include "php::base::feature::phpunit"
}

class php::base::feature::phpunit {
  exec {
    "php_wget_phpunit":
      command => "/usr/bin/wget --no-verbose -O /etc/php5/deploy-includes/phpunit.phar https://phar.phpunit.de/phpunit-old.phar",
      creates => "/etc/php5/deploy-includes/phpunit.phar",
      require => File["/etc/php5/deploy-includes"];
  }
}

class php::type::jenkins {

  package {
    "php-pear": ensure => installed;
    "php5-xdebug": ensure => installed;
    "php5-xsl": ensure => installed;
  }

  exec {
    "php_pear_set_auto_discover":
      command => "/usr/bin/pear config-set auto_discover 1",
      unless => "/usr/bin/pear config-get auto_discover | /bin/grep -q 1";

    "php_pear_install_phpqatools":
      command => "/usr/bin/pear install pear.phpqatools.org/phpqatools",
      require => Exec["php_pear_set_auto_discover"],
      creates => "/usr/share/doc/php5-common/PEAR/phpqatools";

    "php_pear_discover_phpdoc":
      command => "/usr/bin/pear channel-discover pear.phpdoc.org",
      require => Exec["php_pear_set_auto_discover"],
      unless => "/usr/bin/pear list-channels | /bin/grep pear.phpdoc.org";

    "php_pear_install_phpdocumenter":
      command => "/usr/bin/pear install phpdoc/phpDocumentor",
      require => Exec["php_pear_discover_phpdoc"],
      creates => "/usr/share/php/phpDocumentor";
  }

  file {
    "/etc/php5/deploy-includes":
      ensure => directory,
      require => Package["php5-xdebug"];
  }

  include "php::base::feature::phpunit"
}
