class apt::client {
  exec {
    "apt_client_update":
      command => "/usr/bin/apt-get update";
  }
}

class apt::repo::jenkins-ci {
  file {
    "/etc/apt/sources.list.d/jenkins-ci.list":
      ensure => file,
      content => "deb http://pkg.jenkins-ci.org/debian binary/\n";
  }

  exec {
    "apt_repo_jenkins-ci_getkey":
      command => "/usr/bin/wget -O /root/jenkins-ci.org.key http://pkg.jenkins-ci.org/debian/jenkins-ci.org.key",
      creates => "/root/jenkins-ci.org.key",
      require => File["/etc/apt/sources.list.d/jenkins-ci.list"],
      notify => Exec["apt_repo_jenkins-ci_addkey"];

    "apt_repo_jenkins-ci_addkey":
      command => "/usr/bin/apt-key add /root/jenkins-ci.org.key",
      refreshonly => true,
      notify => Exec["apt_repo_jenkins-ci_update"];

    "apt_repo_jenkins-ci_update":
      command => "/usr/bin/apt-get update",
      refreshonly => true;
  }
}

class apt::repo::nodejs-legacy {
  file {
    "/etc/apt/sources.list.d/nodejs-legacy.list":
      ensure => file,
      content => "deb http://ppa.launchpad.net/chris-lea/node.js-legacy/ubuntu precise main\ndeb-src http://ppa.launchpad.net/chris-lea/node.js-legacy/ubuntu precise main\n";

    "/root/nodejs-legacy.key":
      ensure => file,
      require => File["/etc/apt/sources.list.d/nodejs-legacy.list"],
      content => "-----BEGIN PGP PUBLIC KEY BLOCK-----\nVersion: SKS 1.1.4\nComment: Hostname: keyserver.ubuntu.com\n\nmI0ES/EY5AEEAOZl+6Cv7b0fOnXLj8lt1cZiNQHIuOkGRJaMUdvXdrSbtQ4v9GiMWFoFj+9g\ndFN9EjD9JKoXjJb/e/Q9P21uOi0/YmlOfkqWvqm1qsyBXTXTrGx1mghtALPSw0bvYoWZ3aZJ\n3c9VPT5sCdv9IYw6X/+4Z0HoQGvxymbfvRKH3J/xABEBAAG0EkxhdW5jaHBhZCBjaHJpc2xl\nYYi2BBMBAgAgBQJL8RjkAhsDBgsJCAcDAgQVAggDBBYCAwECHgECF4AACgkQuTFqe8eRexLB\nrAQAk9ux3R+k38+dY0f8p3B+0UESy/jNFL/S+t6Fdpw/2qMV1EZohAgJXUw/axmTdr1gKUoy\nGDtE13gebKGy+zqtzsIVo44V0ztC3Z7Kbd9bbiW+wMo7RT4yyi6kURMyE68RrqGbkenZveU6\no2Urq4LW6bfn5fDLVeYQ5GNsrNdSS1k=\n=9f3N\n-----END PGP PUBLIC KEY BLOCK-----\n",
      notify => Exec["apt_repo_nodejs-legacy_addkey"];
  }

  exec {
    "apt_repo_nodejs-legacy_addkey":
      command => "/usr/bin/apt-key add /root/nodejs-legacy.key",
      refreshonly => true,
      notify => Exec["apt_client_update"];
  }
}
