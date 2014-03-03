# Be a localhost-only postfix server
class postfix::base {

  # Install the postfix package
  package {
    "postfix": ensure => installed;
  }

  service {
    "postfix": ensure => running, enable => true;
  }

  # Install an aliases file
  file {
    "/etc/aliases":
      ensure => file,
      content => template("postfix/aliases.erb"),
      require => Package["postfix"];

    "/etc/postfix/main.cf":
      ensure => file,
      content => template("postfix/main.cf.erb"),
      require => Package["postfix"],
      notify => Service["postfix"];
  }

  # Run postalias after installing the aliases file
  exec {
    "postfix_postalias":
      command => "/usr/sbin/postalias /etc/aliases",
      refreshonly => true,
      subscribe => File["/etc/aliases"];
  }
}
