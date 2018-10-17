class apt::client {
  exec {
    "apt_client_update":
      command => "/usr/bin/apt-get update";
  }

  # Make sure generic base packages are installed, for the benefit
  # of minimal environments like CircleCI docker
  package {
    "rsync": ensure => installed;
    "wget": ensure => installed;
  }
}
