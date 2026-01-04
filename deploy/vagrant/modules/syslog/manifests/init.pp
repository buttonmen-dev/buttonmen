# Run rsyslog with a minimal configuration
class syslog::base {

  # Install the rsyslog package
  package {
    "rsyslog": ensure => installed;
  }

  # Replace the postrotate script with one that works on containers
  file {
    "/usr/lib/rsyslog/rsyslog-rotate":
      ensure => file,
      mode => "0555",
      content => template("syslog/rsyslog_rotate.erb"),
      require => Package["rsyslog"];
  }
}
