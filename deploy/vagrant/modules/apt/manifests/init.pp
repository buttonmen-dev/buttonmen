class apt::client {
  exec {
    "apt_client_update":
      command => "/usr/bin/apt-get update";
  }

  # Make sure generic base packages are installed, for the benefit
  # of minimal environments like CircleCI docker
  package {
    "bzip2": ensure => installed;
    "cron": ensure => installed;
    "openssh-server": ensure => installed;
    "rsync": ensure => installed;
    "wget": ensure => installed;

    # These packages aren't strictly needed for execution, so we
    # can remove them if they cause a problem, but they're handy for diagnostics
    "less": ensure => installed;
    "lsof": ensure => installed;
    "strace": ensure => installed;
    "vim-tiny": ensure => installed;
  }

  # Configure periodic apt cron job
  file {
    "/etc/apt/apt.conf.d/10periodic":
      ensure => file,
      content => template("apt/10periodic.erb");
  }
}
