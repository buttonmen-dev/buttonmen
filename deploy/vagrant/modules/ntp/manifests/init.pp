class ntp::client {
  package {
    "ntp": ensure => installed;
  }
}
