class javascript::type::circleci {
  package {
    "npm": ensure => installed;
# FIXME: do i need to find this, or will xvfb or phantomjs take care of it?  "nodejs-legacy": ensure => installed;

    # needed to run a headless script on Ubuntu
    "xvfb": ensure => installed;
  }

  exec {
    "npm_install_phantomjs":
      command => "/usr/bin/npm install -g phantomjs-prebuilt@2.1.16",
      require => [ Package["npm"] ],
      creates => "/usr/local/bin/phantomjs";
  }

  file {
    "/usr/local/etc/run-jscover-qunit.js":
      ensure => file,
      content => template("javascript/run-jscover-qunit.js.erb");
  }
}
