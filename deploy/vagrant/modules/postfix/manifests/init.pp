# Be a localhost-only postfix server
class postfix::base {

  # Install the postfix package
  package {
    "postfix": ensure => installed;
  }

  # Install an aliases file
  file {
    "/etc/aliases":
      ensure => file,
      content => template("postfix/aliases.erb"),
      require => Package["postfix"];
  }

  # Run postalias after installing the aliases file
  exec {
    "postfix_postalias":
      command => "/usr/sbin/postalias /etc/aliases",
      refreshonly => true,
      subscribe => File["/etc/aliases"];
  }
}
