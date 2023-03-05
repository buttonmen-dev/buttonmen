class buttonmen::server {

  # Passwords for buttonmen databases
  $buttonmen_db1_name = "buttonmen"
  $buttonmen_db1_user = "bmuser1"
  $buttonmen_db1_pass = "79eWZGs2RohkIZMVElU6"
  $buttonmen_db2_name = "buttonmen_test"
  $buttonmen_db2_user = "bmtest"
  $buttonmen_db2_pass = "bmtestpass"

  file {

    # Install a .htaccess file containing buttonmen variables
    "/etc/apache2/${puppet_apache_sitesdir}/buttonmen":
      ensure => file,
      content => template("buttonmen/apache.conf.erb"),
      notify => Service["apache2"],
      require => Package["apache2"];

    "/usr/local/bin/create_buttonmen_databases":
      ensure => file,
      content => template("buttonmen/create_databases.erb"),
      mode => 0555;

    "/usr/local/bin/create_rds_database":
      ensure => file,
      content => template("buttonmen/create_rds_database.erb"),
      mode => 0555;

    "/usr/local/bin/backup_buttonmen_database":
      ensure => file,
      content => template("buttonmen/backup_database.erb"),
      mode => 0555;

    "/usr/local/bin/test_buttonmen_config":
      ensure => file,
      content => template("buttonmen/test_config.erb"),
      mode => 0555;

    "/usr/local/bin/branch_database_rebuild_test":
      ensure => file,
      content => template("buttonmen/branch_database_rebuild_test.erb"),
      mode => 0555;

    "/usr/local/bin/mysql_root_cli":
      ensure => file,
      content => template("buttonmen/mysql_root_cli.erb"),
      mode => 0544;

    "/usr/local/bin/run_buttonmen_tests":
      ensure => file,
      content => template("buttonmen/run_buttonmen_tests.erb"),
      mode => 0555;

    "/usr/local/bin/audit_js_unit_test_coverage":
      ensure => file,
      content => template("buttonmen/audit_js_unit_test_coverage.erb"),
      mode => 0555;

    "/usr/local/etc/buttonmen_phpunit.php":
      ensure => file,
      content => template("buttonmen/phpunit.php.erb");

    "/srv/backup":
      ensure => directory,
      group => "adm",
      mode => 0750;
  }

  exec {
    "buttonmen_src_rsync":
      command => "/usr/bin/rsync -a --delete /buttonmen/src/ /var/www/",
      require => [ Package["rsync"], Package["apache2"] ];

    "buttonmen_uitest_rsync":
      command => "/usr/bin/rsync -a --delete /buttonmen/test/src/ui/ /var/www/test-ui/",
      require => Exec["buttonmen_src_rsync"];
  }

  # Create databases only if we're using local database (i.e. for dev/test sites)
  # (See deploy/database/README.RDS_MIGRATION for how to bootstrap a remote database)
  case "$database_fqdn" {
    "127.0.0.1": {
      exec {
        "buttonmen_create_databases":
          command => "/usr/local/bin/create_buttonmen_databases",
          require => [ Service["mysql"],
                       Exec["buttonmen_src_rsync"] ];
      }
    }
  }

  # After updating source code, override the Config.js site type
  # for the dev site
  case $puppet_hostname {
    "dev.buttonweavers.com": {
      exec {
        "buttonmen_update_config_sitetype":
          command =>
            "/bin/sed --follow-symlinks -i -e '/^Config.siteType =/s/production/development/' /var/www/ui/js/Config.js",
          require => Exec["buttonmen_src_rsync"];
      }
    }
    "staging.buttonweavers.com": {
      exec {
        "buttonmen_update_config_sitetype":
          command =>
            "/bin/sed --follow-symlinks -i -e '/^Config.siteType =/s/production/staging/' /var/www/ui/js/Config.js",
          require => Exec["buttonmen_src_rsync"];
      }
    }
  }

  cron {
    "buttonmen_backup_database":
      command => "/usr/local/bin/backup_buttonmen_database",
      minute => "1",
      hour => "0";

    "buttonmen_test_config":
      command => "/usr/local/bin/test_buttonmen_config",
      minute => "5",
      hour => "0";
  }
}

class buttonmen::python-api-client {

  $buttonmen_pyclient_miniconda_version = "py310_22.11.1-1"

  # Use miniconda to install python2 and python3 envs which can be used for testing
  exec {
    "bm_pyclient_download_miniconda":
      command => "/usr/bin/wget -O /usr/local/src/Miniconda3-${buttonmen_pyclient_miniconda_version}-Linux-x86_64.sh https://repo.anaconda.com/miniconda/Miniconda3-${buttonmen_pyclient_miniconda_version}-Linux-x86_64.sh",
      require => [ Package["wget"] ],
      creates => "/usr/local/src/Miniconda3-${buttonmen_pyclient_miniconda_version}-Linux-x86_64.sh";

    "bm_pyclient_install_miniconda":
      command => "/bin/bash /usr/local/src/Miniconda3-${buttonmen_pyclient_miniconda_version}-Linux-x86_64.sh -b -p /opt/conda",
      require => [ Exec["bm_pyclient_download_miniconda"] ],
      creates => "/opt/conda";

    "bm_pyclient_create_python27":
      command => "/opt/conda/bin/conda create python=2.7 --file /buttonmen/tools/api-client/python/requirements.txt -n python27 -y",
      require => [ Exec["bm_pyclient_install_miniconda"] ],
      creates => "/opt/conda/envs/python27";

    # The two envs don't actually depend on each other; the explicit require prevents vagrant from trying to create them simultaneously
    "bm_pyclient_create_python39":
      command => "/opt/conda/bin/conda create python=3.9 --file /buttonmen/tools/api-client/python/requirements.txt -n python39 -y",
      require => [ Exec["bm_pyclient_install_miniconda"], Exec["bm_pyclient_create_python27"] ],
      creates => "/opt/conda/envs/python39";
  }
}
