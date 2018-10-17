class apt::client {
  exec {
    "apt_client_update":
      command => "/usr/bin/apt-get update";
  }
}
