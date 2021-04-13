#####
# This provides information about the FQDN used for an EC2 site,
# so that consumers like letsencrypt or cloudwatch can access that
# information if it exists.
#
# This module does not change /etc/hosts or /etc/hostname

class fqdn::base {
  file {
    # The script that queries EC2 instance tags to get an FQDN 
    "/usr/local/bin/fqdn_from_ec2_tags":
      ensure => file,
      content => template("fqdn/from_ec2_tags.erb"),
      mode => 555;
  }

  # Use fqdn_from_ec2_tags to populate or update a site file
  exec {
    "fqdn_populate_etc_file":
      command => "/usr/local/bin/fqdn_from_ec2_tags /usr/local/etc/bmsite_fqdn",
      require => [ File["/usr/local/bin/fqdn_from_ec2_tags"] ];
  }
}
