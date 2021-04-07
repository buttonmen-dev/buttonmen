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

  # Monitor the error log
  include "apache::server::feature::monitor-logs"

  # Install and configure letsencrypt (SSL/certbot) for AWS instances 
  case "${ec2_services_partition}" {
    "aws": {
      include "apache::server::feature::letsencrypt"
    }
  }
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

class apache::server::feature::monitor-logs {

  # Install the logtail package
  package {
    "logtail": ensure => installed;
  }

  file {
    # Setup a directory for logtail2 to use for its offset files
    "/var/spool/logtail":
      ensure => directory,
      mode => 0755;

    # Install a script to use for monitoring logs
    "/usr/local/sbin/monitor_apache_logs":
      ensure => file,
      content => template("apache/monitor_apache_logs.erb"),
      mode => 0555;
  }

  # Run the log-monitoring script from a nightly cron job
  cron {
    "apache_monitor_logs":
      command => "/usr/local/sbin/monitor_apache_logs",
      hour => 0,
      minute => 5;
  }
}

class apache::server::feature::letsencrypt {

  # Install the certbot package
  package {
    "python-certbot-apache": ensure => installed;
  }

  exec {
    # Run certbot to configure LetsEncrypt
    # If the site has the special FQDN indicating it's a non-networked sandbox, don't run certbot
    "apache_certbot_setup":
      command => "/usr/bin/certbot --apache -d $(/bin/cat /usr/local/etc/bmsite_fqdn) -n --email help@buttonweavers.com --agree-tos",
      require => [ Exec["fqdn_populate_etc_file"], Package["python-certbot-apache"] ],
      creates => "/etc/letsencrypt/live",
      unless => "/bin/grep -q sandbox.buttonweavers.com /usr/local/etc/bmsite_fqdn";
  }
}
