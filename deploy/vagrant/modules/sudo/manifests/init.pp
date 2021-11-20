class sudo::buttonmen-devs {
  file {
    "/etc/sudoers.d/99-buttonmen-dev":
      ensure => file,
      mode => "0440",
      content => "%admin ALL=(ALL) NOPASSWD:ALL\n";
  }
}
