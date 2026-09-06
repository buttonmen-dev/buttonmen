class python::venv {
  package {
    "python3-full": ensure => installed;
    "python3-pip": ensure => installed;
  }

  file {
    # Make a parent for venvs
    "/opt/venv":
      ensure => directory,
      mode => "0755";
  }
}

class python::venv::lambda_ic {
  include "python::venv"

  exec {
    "python_venv_lambda_ic_create":
      command => "/usr/bin/python3 -m venv /opt/venv/lambda_ic",
      require => [ Package["python3-full"], Package["python3-pip"], File["/opt/venv"] ],
      creates => "/opt/venv/lambda_ic";

    "python_venv_lambda_ic_install_awslambdaric":
      command => "/opt/venv/lambda_ic/bin/pip install awslambdaric",
      require => [ Exec["python_venv_lambda_ic_create"] ],
      unless => "/opt/venv/lambda_ic/bin/pip list | /bin/grep -q awslambdaric";
  }
}
