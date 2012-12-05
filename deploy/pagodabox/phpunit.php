<?php
/* 
 * Run all unit tests
 * Modelled on /usr/bin/phpunit CLI utility
 */

set_include_path( "/var/www:" . ini_get( "include_path" ) );

// Tell PHPUnit to run tests relative to the current directory
$_SERVER['argv'] = array('phpunit.php', '.');

include "phpunit.phar";

PHPUnit_TextUI_Command::main();
