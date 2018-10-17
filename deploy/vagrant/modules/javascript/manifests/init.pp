class javascript::type::circleci {
  package {
    "phantomjs": ensure => installed;
#    "jscoverage": ensure => installed;
    "npm": ensure => installed;
  }

  file {
    "/usr/local/etc/run-jscover-qunit.js":
      ensure => file,
      content => template("javascript/run-jscover-qunit.js.erb");
  }
}
