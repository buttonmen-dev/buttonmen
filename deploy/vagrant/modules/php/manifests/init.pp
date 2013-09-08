class php::base {

  # Make sure php5 and libapache2-mod-php5 are installed
  package {
    "php5": ensure => installed;
    "libapache2-mod-php5": ensure => installed;
  }
}
