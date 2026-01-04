class cloudwatch::buttonmen_site {
  package {
    "awscli": ensure => installed;
    "python-boto": ensure => installed;
  }

  file {
    # Install script to record cloudwatch metrics based on recent site API accesses
    "/usr/local/bin/record_buttonmen_cloudwatch_metrics":
      ensure => file,
      content => template("cloudwatch/record_buttonmen_metrics.erb"),
      mode => "0555";
  }

  # Record cloudwatch metrics from apache logs every five minutes
  cron {
    "record_buttonmen_cloudwatch_metrics":
      command => "/usr/bin/timeout 15s /usr/local/bin/record_buttonmen_cloudwatch_metrics ${ec2_instance_id} ${ec2_placement_availability_zone}",
      minute => '*/5';
  }
}
