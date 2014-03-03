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

  # Customize apache default site
  file {
    "/etc/apache2/sites-available/default":
      ensure => file,
      content => template("apache/site_default.erb"),
      notify => Service["apache2"];
  }

  # Monitor the error log
  include "apache::server::feature::monitor-logs"
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
