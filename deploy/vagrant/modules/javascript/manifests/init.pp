class javascript::type::circleci {
  package {
    "npm": ensure => installed;
    "nodejs-legacy": ensure => installed;

    # we'll downgrade phantomjs later, but install it via apt to get its dependencies
    "phantomjs": ensure => installed;
  }

  file {
    "/usr/local/etc/run-jscover-qunit.js":
      ensure => file,
      content => template("javascript/run-jscover-qunit.js.erb");
  }

  exec {
    "javascript_wget_phantomjs":
      command => "/usr/bin/wget https://bitbucket.org/ariya/phantomjs/downloads/phantomjs-1.9.7-linux-x86_64.tar.bz2 -O /usr/local/src/phantomjs-1.9.7-linux-x86_64.tar.bz2",
      creates => "/usr/local/src/phantomjs-1.9.7-linux-x86_64.tar.bz2";
  }
}
