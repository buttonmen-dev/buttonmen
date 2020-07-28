class cloudwatch::buttonmen-site {
  package {
    "awscli": ensure => installed;
  }

  file {
    # Install script to record cloudwatch metrics based on recent site API accesses
    "/usr/local/bin/record_buttonmen_cloudwatch_metrics":
      ensure => file,
      content => template("cloudwatch/record_buttonmen_metrics.erb"),
      mode => 0555;
  }

  # Record cloudwatch metrics from apache logs every five minutes
  cron {
    "record_buttonmen_cloudwatch_metrics":
      command => "/usr/local/bin/record_buttonmen_cloudwatch_metrics ${ec2_instance_id} ${ec2_placement_region}",
      minute => '*/5';
  }
}
