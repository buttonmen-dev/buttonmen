<?php
/* 
 * Run all unit tests
 * Modelled on /usr/bin/phpunit CLI utility
 */

set_include_path( "./../../src:" . ini_get( "include_path" ) );

// Tell PHPUnit to run tests relative to the current directory
array_push($_SERVER['argv'], '.');

include "phpunit.phar";

PHPUnit_TextUI_Command::main();
