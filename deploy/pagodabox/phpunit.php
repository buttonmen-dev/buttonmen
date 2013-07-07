<?php
/* 
 * Run all unit tests
 * Modelled on /usr/bin/phpunit CLI utility
 */

// Include /var/www/deploy-includes for phpunit itself.
// Include /var/www/src for now, so the individual unit tests can find the base classes
set_include_path( "/var/www/deploy-includes:/var/www/src:" . ini_get( "include_path" ) );

// Unit tests use the same bootstrap file as the code itself
require_once( "/var/www/src/lib/bootstrap.php" );

include "phpunit.phar";
