#####
# This provides information about the FQDN used for an EC2 site,
# so that consumers like letsencrypt or cloudwatch can access that
# information if it exists.
#
# This module does not change /etc/hosts or /etc/hostname

class fqdn::base {
  file {
    # Set the FQDN which is configured in puppet via manifests/init.pp, within the container
    "/usr/local/etc/bmsite_fqdn":
      ensure => file,
      content => template("fqdn/bmsite_fqdn.erb"),
      mode => 444;
  }
}
