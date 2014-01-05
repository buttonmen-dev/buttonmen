class javascript::type::jenkins {

  # Need a special repo to get a recent node.js
  include "apt::repo::nodejs-legacy"

  package {
    "npm": ensure => installed, require => Class["apt::repo::nodejs-legacy"];
  }

  exec {
    "javascript_npm_install_grunt":
      command => "/usr/bin/npm install -g grunt-cli",
      require => Package["npm"];
  }
}
