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
    # TODO: once we upgrade PHP, start getting the latest version of phpunit again
    "php_wget_phpunit":
      command => "/usr/bin/wget --no-verbose -O /etc/php5/deploy-includes/phpunit.phar http://www.glassonion.org/misc/phpunit-4.8.36.phar",
      creates => "/etc/php5/deploy-includes/phpunit.phar",
      require => File["/etc/php5/deploy-includes"];
  }
}

# Include both "base" and "code test" PHP features
class php::type::circleci {
  include "php::base"

  package {
    "php-pear": ensure => installed;
    "php5-xdebug": ensure => installed;
    "php5-xsl": ensure => installed;
  }

  exec {
    "php_pear_set_auto_discover":
      command => "/usr/bin/pear config-set auto_discover 1",
      unless => "/usr/bin/pear config-get auto_discover | /bin/grep -q 1";

    "php_pear_install_pdepend":
      command => "/usr/bin/pear install pear.pdepend.org/PHP_Depend",
      require => Exec["php_pear_set_auto_discover"],
      creates => "/usr/bin/pdepend";

    "php_wget_install_phpmd":
      command => "/usr/bin/wget --no-verbose -O /etc/php5/deploy-includes/phpmd.phar http://static.phpmd.org/php/latest/phpmd.phar",
      creates => "/etc/php5/deploy-includes/phpmd.phar",
      require => File["/etc/php5/deploy-includes"];

    "php_wget_install_phpcpd":
      command => "/usr/bin/wget --no-verbose -O /etc/php5/deploy-includes/phpcpd.phar https://phar.phpunit.de/phpcpd.phar",
      creates => "/etc/php5/deploy-includes/phpcpd.phar",
      require => File["/etc/php5/deploy-includes"];

    "php_wget_install_phploc":
      command => "/usr/bin/wget --no-verbose -O /etc/php5/deploy-includes/phploc.phar https://phar.phpunit.de/phploc.phar",
      creates => "/etc/php5/deploy-includes/phploc.phar",
      require => File["/etc/php5/deploy-includes"];

    "php_wget_install_phpcb":
      command => "/usr/bin/wget --no-verbose -O /etc/php5/deploy-includes/phpcb.phar https://github.com/bytepark/php-phar-qatools/blob/master/phpcb.phar",
      creates => "/etc/php5/deploy-includes/phpcb.phar",
      require => File["/etc/php5/deploy-includes"];

    "php_pear_discover_phpdoc":
      command => "/usr/bin/pear channel-discover pear.phpdoc.org",
      require => Exec["php_pear_set_auto_discover"],
      unless => "/usr/bin/pear list-channels | /bin/grep pear.phpdoc.org";

    "php_pear_install_phpcs":
      command => "/usr/bin/pear install pear.php.net/PHP_CodeSniffer",
      require => Exec["php_pear_set_auto_discover"],
      creates => "/usr/bin/phpcs";

    "php_pear_install_phpdocumenter":
      command => "/usr/bin/pear install phpdoc/phpDocumentor-2.7.0",
      require => Exec["php_pear_discover_phpdoc"],
      creates => "/usr/share/php/phpDocumentor";
  }
}
