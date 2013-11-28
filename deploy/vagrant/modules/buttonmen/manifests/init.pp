class buttonmen::server {

  # Passwords for buttonmen databases
  $buttonmen_db1_name = "buttonmen"
  $buttonmen_db1_user = "bmuser1"
  $buttonmen_db1_pass = "79eWZGs2RohkIZMVElU6"
  $buttonmen_db2_name = "buttonmen_test"
  $buttonmen_db2_user = "root"
  $buttonmen_db2_pass = "root"

  $buttonmen_code_githash = generate("/usr/bin/cut", "-f", "1", "/buttonmen/.git/FETCH_HEAD")

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

    "/usr/local/etc/buttonmen_phpunit.php":
      ensure => file,
      content => template("buttonmen/phpunit.php.erb");

    "/var/www/version.html":
      ensure => file,
      content => template("buttonmen/version.html.erb"),
      require => Exec["buttonmen_src_rsync"];
  }

  exec {
    "buttonmen_src_rsync":
      command => "/usr/bin/rsync -a --delete /buttonmen/src/ /var/www/",
      require => Package["apache2"];

    "buttonmen_create_databases":
      command => "/usr/local/bin/create_buttonmen_databases",
      require => [ Package["mysql-server"],
                   Exec["buttonmen_src_rsync"] ];
  }
}
