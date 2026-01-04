# Be a localhost-only postfix server
class postfix::base {

  # Install the postfix package
  package {
    "postfix": ensure => installed;
  }

  service {
    "postfix": ensure => running, enable => true;
  }

  # Install a creds file if we are using creds
  if "${email_relay_sasl_creds}" != "NOCREDS" {
    file {
      "/etc/postfix/sasl_passwd":
        ensure => file,
        content => template("postfix/sasl_passwd.erb"),
        mode => 0400,
        require => Package["postfix"];
    }

    exec {
      "postmap_sasl_passwd":
        command => "/usr/sbin/postmap hash:/etc/postfix/sasl_passwd",
        refreshonly => true,
        subscribe => File["/etc/postfix/sasl_passwd"];
    }
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
