# Be an apache server
class apache::server {

  # Install the apache2 Ubuntu package
  package {
    "apache2": ensure => installed;
  }

  # Run a webserver
  service {
    "apache2":
      ensure => running,
      enable => true,
      require => Package["apache2"];
  }

  # Use mod_pagespeed to handle caching/refreshing of pages
  include "apache::server::feature::mod_pagespeed"

  # Monitor the error log
  include "apache::server::feature::monitor_logs"

  # Install letsencrypt
  include "apache::server::feature::letsencrypt"
}

class apache::server::vagrant {
  # include the base class
  include "apache::server"

  file {
    # Customize apache default site
    "/etc/apache2/sites-available/000-default.conf":
      ensure => file,
      content => template("apache/site_default.erb"),
      notify => Service["apache2"];

    # Enable the default site under the default name
    "/etc/apache2/sites-enabled/000-default.conf":
      ensure => link,
      target => "/etc/apache2/sites-available/000-default.conf",
      notify => Service["apache2"];
  }
}

class apache::server::circleci {
  # same configuration as vagrant
  include "apache::server::vagrant"
}

class apache::server::feature::monitor_logs {

  # Install the logtail package
  package {
    "logtail": ensure => installed;
  }

  file {
    # Setup a directory for logtail2 to use for its offset files
    "/var/spool/logtail":
      ensure => directory,
      mode => "0755";

    # Install a script to use for monitoring logs
    "/usr/local/sbin/monitor_apache_logs":
      ensure => file,
      content => template("apache/monitor_apache_logs.erb"),
      mode => "0555";
  }

  # Run the log-monitoring script from a nightly cron job
  #
  # Note: assuming this is the first cron job to be installed, the
  # environment variable will be applied to all jobs.  Hackishly
  # taking advantage of the fact that this appears to be the case,
  # because puppet provides no clean way to get an envvar installed
  # at the top of /var/spool/cron/crontabs/root exactly once.
  cron {
    "apache_monitor_logs":
      command => "/usr/local/sbin/monitor_apache_logs",
      environment => "BMSITE=${puppet_hostname}",
      hour => 0,
      minute => 5;
  }
}

class apache::server::feature::mod_pagespeed {
  exec {
    "apache_mod_pagespeed_pkg_download":
      command => "/usr/bin/wget --no-verbose -O /usr/local/src/mod-pagespeed-stable_current.deb https://dl-ssl.google.com/dl/linux/direct/mod-pagespeed-stable_current_${facts['os']['architecture']}.deb",
      creates => "/usr/local/src/mod-pagespeed-stable_current.deb";

    "apache_mod_pagespeed_pkg_install":
      command => "/usr/bin/dpkg -i /usr/local/src/mod-pagespeed-stable_current.deb",
      require => [ Exec["apache_mod_pagespeed_pkg_download"], Package["apache2"] ],
      unless => "/usr/bin/dpkg -l mod-pagespeed-stable",
      notify => File["/etc/apache2/mods-available/pagespeed.conf"];
  }

  file {
    "/etc/apache2/mods-available/pagespeed.conf":
      ensure => file,
      content => template("apache/mod_pagespeed.conf.erb"),
      mode => "0444",
      notify => Service["apache2"];
  }
}

# This configuration is only meaningful if we're on a non-sandboxed
# site, but the logic to enforce that is within the apache_setup_certbot
# script, so it's harmless to run this configuration anywhere
class apache::server::feature::letsencrypt {

  # Install the certbot package
  package {
    "python3-certbot-apache": ensure => installed;
  }

  file {
    "/usr/local/bin/apache_setup_certbot":
      ensure => file,
      content => template("apache/setup_certbot.erb"),
      mode => "0555";
  }
}
