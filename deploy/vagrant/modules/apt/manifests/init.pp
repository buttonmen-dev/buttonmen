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
