class javascript::type::circleci {
  package {
    "npm": ensure => installed;
    "nodejs-legacy": ensure => installed;
  }

  file {
    "/usr/local/etc/run-jscover-qunit.js":
      ensure => file,
      content => template("javascript/run-jscover-qunit.js.erb");

    "/usr/local/bin/install_phantomjs"
      ensure => file,
      mode => 544,
      content => template("javascript/install_phantomjs.erb");
  }

  exec {
    "javascript_install_phantomjs":
      command => "/usr/local/bin/install_phantomjs",
      creates => "/usr/local/bin/phantomjs",
      require => File["/usr/local/bin/install_phantomjs"];
  }
}
