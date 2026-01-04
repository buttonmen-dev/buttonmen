class sudo::buttonmen_devs {

  package {
    "sudo": ensure => installed;
  }

  file {
    "/etc/sudoers.d/99-buttonmen-dev":
      ensure => file,
      mode => "0440",
      content => "%admin ALL=(ALL) NOPASSWD:ALL\n",
      require => Package["sudo"];
  }
}
