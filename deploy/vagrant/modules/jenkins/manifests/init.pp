define jenkins_plugin() {
  exec {
    "jenkins_plugin_${name}_install":
      command => "/usr/local/bin/jkcli install-plugin ${name}",
      require => File["/usr/local/bin/jkcli"],
      creates => "/var/lib/jenkins/plugins/${name}.jpi",
      notify => Exec["jenkins_safe_restart"];
  }
}

class jenkins::server {

  include "apt::repo::jenkins-ci"

  # Jenkins server uses PHP PEAR and some jenkins-related modules
  include "php::type::jenkins"
  
  # Jenkins server uses JS NPM and some jenkins-related modules
  include "javascript::type::jenkins"

  package {
    "jenkins": require => Class["apt::repo::jenkins-ci"];

    # ant is needed to build projects under jenkins
    "ant": ;

    # curl is needed to send commands to jenkins
    "curl": ;

    # git is needed so jenkins can check out git code for testing
    "git": ;

    # Jenkins UI has an undocumented dependency on these fonts
    "ttf-dejavu": ;

    # PHP documentation parser uses graphviz
    "graphviz": ;
  }

  file {
    "/usr/local/bin/jkcli":
      ensure => file,
      mode => "0555",
      content => "#!/bin/sh\n\n/usr/bin/java -jar /usr/local/bin/jenkins-cli.jar -s http://localhost:8080 $@\n",
      require => Exec["jenkins_install_local_cli"];

    "/usr/local/etc/jenkins_buttonmen_config.xml":
      ensure => file,
      content => template("jenkins/buttonmen_config.xml.erb");

    "/usr/local/etc/jenkins_config.xml":
      ensure => file,
      content => template("jenkins/config.xml.erb");
  }

  exec {
    "jenkins_install_local_cli":
      command => "/usr/bin/wget -O /usr/local/bin/jenkins-cli.jar http://localhost:8080/jnlpJars/jenkins-cli.jar",
      require => Package["jenkins"],
      creates => "/usr/local/bin/jenkins-cli.jar";

    "jenkins_download_update_center":
      command => "/usr/bin/wget -O - http://updates.jenkins-ci.org/update-center.json | /usr/bin/head -2 | /usr/bin/tail -1 > /root/update-center-default.json",
      creates => "/root/update-center-default.json",
      notify => Exec["jenkins_install_update_center"];

    "jenkins_install_update_center":
      command => '/usr/bin/curl -X POST -H "Accept: application/json" -d @/root/update-center-default.json http://localhost:8080/updateCenter/byId/default/postBack',
      refreshonly => true,
      require => [ Package["curl"], Package["jenkins"] ];

    "jenkins_safe_restart":
      command => "/usr/local/bin/jkcli safe-restart",
      refreshonly => true,
      require => File["/usr/local/bin/jkcli"];
  }

  jenkins_plugin {
    "build-name-setter": ;
    "checkstyle": ;
    "cloverphp": ;
    "dry": ;
    "htmlpublisher": ;
    "jdepend": ;
    "plot": ;
    "pmd": ;
    "violations": ;
    "xunit": ;

    "git": ;
  }
}
