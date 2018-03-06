class javascript::type::circleci {
  package {
    "phantomjs": ensure => installed;
    "jscoverage": ensure => installed;
  }

  file {
    "/usr/local/etc/run-jscover-qunit.js":
      ensure => file,
      content => template("javascript/run-jscover-qunit.js.erb");
  }
}

class javascript::type::jenkins {

  # Need a special repo to get a recent node.js
  include "apt::repo::nodejs-legacy"

  package {
    "npm": ensure => installed, require => Class["apt::repo::nodejs-legacy"];

    # phantomjs and its undocumented dependency
    # N.B. we actually just install phantomjs as a package to get
    # its dependencies --- the one jenkins uses is the newer one
    # downloaded and installed by hand
    "phantomjs": ensure => installed;
    "libicu48": ensure => installed;

    # unzip needed to unpack JSCover
    "unzip": ensure => installed;

    # lighttpd and php5-cgi needed to serve dummy_responder.php for
    # automated QUnit tests
    "lighttpd": ensure => installed;
    "php5-cgi": ensure => installed;
  }

  # install lighttpd, but don't let it run as a service
  service {
    "lighttpd":
      ensure => stopped,
      enable => false,
      require => Package["lighttpd"];
  }

  file {
    "/opt/JSCover": ensure => directory;

    "/opt/phantomjs":
      ensure => "/opt/phantomjs-1.9.2-linux-i686",
      require => Exec["javascript_untar_phantomjs"];

    "/usr/local/share/jenkins-lighttpd":
      ensure => directory, owner => "jenkins", require => Package["jenkins"];

    "/usr/local/share/jenkins-lighttpd/var":
      ensure => directory, owner => "jenkins";

    "/usr/local/share/jenkins-lighttpd/www":
      ensure => directory, owner => "jenkins";

    "/usr/local/etc/jenkins-lighttpd.conf":
      ensure => file,
      content => template("javascript/jenkins-lighttpd.conf.erb");

    "/usr/local/etc/run-jscover-qunit.js":
      ensure => file,
      content => template("javascript/run-jscover-qunit.js.erb");
  }

  exec {
    "javascript_npm_install_grunt":
      command => "/usr/bin/npm install -g grunt-cli",
      require => Package["npm"],
      creates => "/usr/bin/grunt";

    "javascript_download_jscover":
      command => "/usr/bin/wget -O /root/JSCover-1.0.6.zip http://downloads.sourceforge.net/project/jscover/JSCover-1.0.6.zip?use_mirror=autoselect",
      creates => "/root/JSCover-1.0.6.zip",
      notify => Exec["javascript_unzip_jscover"];

    "javascript_unzip_jscover":
      command => "/usr/bin/unzip /root/JSCover-1.0.6.zip",
      cwd => "/opt/JSCover",
      creates => "/opt/JSCover/target",
      require => [ Package["unzip"], File["/opt/JSCover"] ];

    "javascript_download_phantomjs":
      command => "/usr/bin/wget -O /root/phantomjs-1.9.2-linux-i686.tar.bz2 https://phantomjs.googlecode.com/files/phantomjs-1.9.2-linux-i686.tar.bz2",
      creates => "/root/phantomjs-1.9.2-linux-i686.tar.bz2",
      notify => Exec["javascript_untar_phantomjs"];

    "javascript_untar_phantomjs":
      command => "/bin/tar xjf /root/phantomjs-1.9.2-linux-i686.tar.bz2",
      cwd => "/opt",
      creates => "/opt/phantomjs-1.9.2-linux-i686";
  }
}

