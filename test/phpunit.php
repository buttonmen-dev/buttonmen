<?php
/* 
 * Run all unit tests
 * Modelled on /usr/bin/phpunit CLI utility
 */

define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');

set_include_path( "./../../src:" . ini_get( "include_path" ) );
include "phpunit.phar";

PHPUnit_TextUI_Command::main();
