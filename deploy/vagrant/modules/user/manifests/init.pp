class user::buttonmen-devs {
  include "user::username::chaos"
  include "user::username::irilyth"
  include "user::username::james"
}

class user::username::chaos {
  group {
    "chaos": ensure => present, gid => 1101;
  }

  user {
    "chaos":
      ensure => present,
      uid => 1101,
      comment => "Chaos Golubitsky",
      gid => "chaos",
      groups => [ "adm", "admin", ],
      shell => "/bin/bash",
      managehome => true,
      require => Group["chaos"];
  }

  file {
    "/home/chaos/.ssh/":
      ensure => directory,
      owner => "chaos",
      group => "chaos",
      require => User["chaos"];

    "/home/chaos/.ssh/authorized_keys":
      ensure => file,
      owner => "chaos",
      group => "chaos",
      content => "# SSH keys for chaos - managed by puppet\nssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQC++z9xJpfNeIoo4Q+KJPqvtnQJebv78IglpbuXmoLSvGMCamO3k61hUznhWE456m+lL8eFvBcHVXAjaH8g+s6avYYhBwzu24I5SjsSjBByJN3GeRrRr/sD/HxN+QBl4Vf7QocJnfyTWECCKiWPVFFW++0msQYSFpNZDBh6V6ptV78KS4iS3UMDzHPMN+0ZEmybI3Ow6MRF3/qfrW7rsEAL9cuzg/8vLQnyypwN/oTWBfT7rG3YFrNpgUWmfL9E1+Em2wFwGBXwY78nJlm1f2grw9LGIjaFK7Ew/CrYBGtu3d1W0bIGAVfXkcsQ0Me/mXHn4nZOHbp6IL6g5ueWVWX3 chaos\n",
      require => User["chaos"];

    "/home/chaos/.forward":
      ensure => file,
      content => "walrus-buttonmen@glassonion.org\n",
      require => User["chaos"];

    "/home/chaos/bin":
      ensure => directory,
      owner => "chaos",
      group => "chaos",
      require => User["chaos"];

    "/home/chaos/bin/install_rcfiles":
      ensure => file,
      owner => "chaos",
      group => "chaos",
      content => template("user/chaos_install_rcfiles.erb"),
      mode => "0544",
      require => User["chaos"];
  }
}

class user::username::irilyth {
  group {
    "irilyth": ensure => present, gid => 1103;
  }

  user {
    "irilyth":
      ensure => present,
      uid => 1103,
      comment => "Josh Smift",
      gid => "irilyth",
      groups => [ "adm", "admin", ],
      shell => "/bin/bash",
      managehome => true,
      require => Group["irilyth"];
  }

  file {
    "/home/irilyth/.ssh/":
      ensure => directory,
      owner => "irilyth",
      group => "irilyth",
      require => User["irilyth"];

    "/home/irilyth/.ssh/authorized_keys":
      ensure => file,
      owner => "irilyth",
      group => "irilyth",
      content => "# SSH keys for irilyth - managed by puppet\nssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDfbVpekKuthQ7sHa0yuSMYy8vIuL4veMuCToOlp31BnyKUOakA2h+gVq6ToQODYIqGEhiODKV7WwJGOhk5vnifEgjl2CS4nyNZ+hjhDAEknU4iKDFDOlxx9UtygCV5w1UnSO3JlXpD7/Vc8QGJLhv1i+I2P46sSzvblSm0fpkpYhWiDIUEjhYD3hykUrkzELq4tYiQ7o3g5TokkKId7Bs+8PdOyBACVGaxR3q+3g5oMkSBlAUJlKIFdcFRO0NayyfMz8t1JbvcMjgFQwkmEeRTkvU9VEoQrSlH2aij531UDXosyuXGd37P4hec+41UWvgyFlzUXNe7VgnZ/hX+9+w/ jsmift_login\n",
      require => User["irilyth"];
  }
}

class user::username::james {
  group {
    "james": ensure => present, gid => 1102;
  }

  user {
    "james":
      ensure => present,
      uid => 1102,
      comment => "James Ong",
      gid => "james",
      groups => [ "adm", "admin", ],
      shell => "/bin/bash",
      managehome => true,
      require => Group["james"];
  }

  file {
    "/home/james/.ssh/":
      ensure => directory,
      owner => "james",
      group => "james",
      require => User["james"];

    "/home/james/.ssh/authorized_keys":
      ensure => file,
      owner => "james",
      group => "james",
      content => "# SSH keys for james - managed by puppet\nssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEArqF2kpbSQUTn3nboWn7hjtMq/TufXpVvY3unKb65bJiguT/OnG0zpyU7/q+ebJVNjaku0XWbVhtGflLTqLKhcwFkfZDOuNN79hkTzptJLyZYj/KTgasMGGHvkK9SKNvYzP0wvSzQNOrTWgiowr5ytFFif1QlhQfj4YBrFWiULKTXeHdZd/WdOgjux7rIn5jfSMW7lpNOkThV9YBVaMCD7JgA/4EL5NZLbf+LhPs0TLSzZVmztZ4wF1C/PYdSQ1AaFHNoys+ylSImSQGBSlE3uuxbBHvqhL0bk43FytytgfoDMHJHPwm3k5V6sYrQcKqAyfcGXlDD4Trx6bDDlJ6X0Q== james\nssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEAwF4MmUw/AmGaiZIfnlW36pkHVkM1DwTuOrdx712qgWnGV9wS+7HmHu9aBdCWkTzSdnAaAKxvpyRdnTKLz12lbDhs2AqV3WkPqYwl/D3wFlLgW9o8o0yQwmgEmSUIxEWPk7mx5MysrdzmMiqbcCSjau5QHzPYwMqpyA7L//dO82H9tj4R3YeNuMvw2IImE3xGHrU+H+MONFLGCjFFawal8GOlvKziuVui6F3WVFQfWyp3hPeGbmWmhUP4tET05++ISQKFMOy4TqIM1m9b1OjW/lgIxDmrwwL6K3T2fNb9dXVJKlvHxUKvvnwEB22MG7sclkKWaJ1t/VhBV8wOaKD4fQ== james@wombat\nssh-rsa AAAAB3NzaC1yc2EAAAABIwAAAQEA1Nft6hCB1LZUvzZBnO7IcC/m8xHhbZa+OLsJZ2+v3SUklOpWeCFvVQ4uaPThZbGGiPZ16RfTAarPgzKOw9qX/Mo/+GYirAuijEL3je30PuhXacnbz6LZePv3L3QeqmJaLxrPYOQogY/pVajB8WNLeIJ5WCLWULhFMVXsyE4czyvpop4cTh/KwsOZWD/DgLe+Eg2fXtbIA/7mw/D9dXvzNA6Do5tsuXLvMjl9TkfwF8CzDMhH567+LsK8b1Y+MeIppgxJTpZC3bOyhEISR7S8NLnfUm8X8AhVIA5DFBpAxHXuOe3X+z0EIHVWjTG8qzhIhagxH8yjQk1tTpdcgkYyDw== james_third_key\n",
      require => User["james"];
  }
}
