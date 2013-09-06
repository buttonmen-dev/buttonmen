# Be an apache server
class apache::server {

  # Install the apache2 Ubuntu package
  package {
    "apache2": ensure => installed;
  }

  # Run a webserver
  service {
    "apache2":
      ensure => running,
      enable => true,
      require => Package["apache2"];
  }
}
