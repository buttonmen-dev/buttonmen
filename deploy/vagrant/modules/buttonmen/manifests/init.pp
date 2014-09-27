class buttonmen::server {

  # Passwords for buttonmen databases
  # Note that buttonmen::jenkins also uses the db2 passwords
  $buttonmen_db1_name = "buttonmen"
  $buttonmen_db1_user = "bmuser1"
  $buttonmen_db1_pass = "79eWZGs2RohkIZMVElU6"
  $buttonmen_db2_name = "buttonmen_test"
  $buttonmen_db2_user = "bmtest"
  $buttonmen_db2_pass = "bmtestpass"

  file {

    # Install a .htaccess file containing buttonmen variables
    "/etc/apache2/conf.d/buttonmen":
      ensure => file,
      content => template("buttonmen/apache.conf.erb"),
      notify => Service["apache2"],
      require => Package["apache2"];

    "/usr/local/bin/create_buttonmen_databases":
      ensure => file,
      content => template("buttonmen/create_databases.erb"),
      mode => 0555;

    "/usr/local/bin/backup_buttonmen_database":
      ensure => file,
      content => template("buttonmen/backup_database.erb"),
      mode => 0555;

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
      require => Package["apache2"];

    "buttonmen_uitest_rsync":
      command => "/usr/bin/rsync -a --delete /buttonmen/test/src/ui/ /var/www/test-ui/",
      require => Exec["buttonmen_src_rsync"];

    "buttonmen_create_databases":
      command => "/usr/local/bin/create_buttonmen_databases",
      require => [ Package["mysql-server"],
                   Exec["buttonmen_src_rsync"] ];
  }

  # After updating source code, override the Config.js site type
  # for the dev site
  case $puppet_hostname {
    "dev.buttonweavers.com": {
      exec {
        "buttonmen_update_config_sitetype":
          command =>
            "/bin/sed -i -e '/^Config.siteType =/s/production/development/' /var/www/ui/js/Config.js",
          require => Exec["buttonmen_src_rsync"];
      }
    }
  }

  cron {
    "buttonmen_backup_database":
      command => "/usr/local/bin/backup_buttonmen_database",
      minute => "1",
      hour => "0";
  }
}

class buttonmen::jenkins {

  # passwords for test database, which phpunit needs
  $buttonmen_db2_name = "buttonmen_test"
  $buttonmen_db2_user = "bmtest"
  $buttonmen_db2_pass = "bmtestpass"

  file {
    "/usr/local/bin/create_buttonmen_test_database":
      ensure => file,
      content => template("buttonmen/create_test_database.erb"),
      mode => 0555;
  }
}
