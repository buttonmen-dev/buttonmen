<?php

// Find and set the root directory of ButtonWeavers PHP files, so
// autoload can use it - this assumes this file itself is
//   BW_PHP_ROOT/lib/bootstrap.php
define('BW_PHP_ROOT', realpath(dirname(__DIR__)));

/**
 * Take a class name and try and find the source file
 * 
 * * Designed to be used with the spl_autoload_register() function
 *   to capture calls for classes that don't exist
 */
function buttonweavers_autoload($name) {

    // All of our class names start with BM
    if (substr($name, 0, 2) != 'BM') {
        return false;
    }

    // Lookup BM* classes in engine/
    $classfile = str_replace('\\', DIRECTORY_SEPARATOR, $name) .  '.php';
    $classpath = BW_PHP_ROOT . DIRECTORY_SEPARATOR . "engine" .
                 DIRECTORY_SEPARATOR . $classfile;
    // requires PHP >= 5.3.2
    $path_found = stream_resolve_include_path($classpath);
    if ($path_found) {
        require_once($path_found);
        return true;
    }
}

// Register autoloader
spl_autoload_register('buttonweavers_autoload');
