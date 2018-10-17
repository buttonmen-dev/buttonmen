class sudo::buttonmen-devs {
  package {
    "sudo": ensure => installed;
  }

  file {
    "/etc/sudoers.d/99-buttonmen-dev":
      ensure => file,
      mode => 0440,
      content => "%admin ALL=(ALL) NOPASSWD:ALL\n";
  }
}
