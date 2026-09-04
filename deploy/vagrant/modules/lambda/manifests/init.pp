class lambda::stats {
  include "python::venv::lambda_ic"

  file {
    "/opt/stats":
      ensure => directory,
      mode => "0755";

    "/opt/stats/lambda_function.py":
      ensure => file,
      content => template("lambda/lambda_function.py.stats.erb"),
      mode => "0444";

    "/opt/stats/requirements.txt":
      ensure => file,
      content => template("lambda/requirements.txt.stats.erb"),
      mode => "0444";
  }
}
