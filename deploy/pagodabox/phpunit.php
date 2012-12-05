<?php
/* 
 * Run all unit tests
 * Modelled on /usr/bin/phpunit CLI utility
 */

// Include /var/www/deploy-includes for phpunit itself.
// Include /var/www/src for now, so the individual unit tests can find the base classes
set_include_path( "/var/www/deploy-includes:/var/www/src:" . ini_get( "include_path" ) );

// Tell PHPUnit to run tests relative to the current directory
$_SERVER['argv'] = array('phpunit.php', '.');

include "phpunit.phar";
