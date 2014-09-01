<?php
/* 
 * Bootstrap for phpunit under Jenkins
 */

// Setup bm_rand() override for unrandom tests
$BM_RAND_VALS = array();
$BM_RAND_REQUIRE_OVERRIDE = FALSE;

function bm_rand($min, $max) {
    global $BM_RAND_VALS, $BM_RAND_REQUIRE_OVERRIDE;

    if (count($BM_RAND_VALS) > 0) {
        return array_shift($BM_RAND_VALS);
    }
    if ($BM_RAND_REQUIRE_OVERRIDE) {
        throw new Exception("Called bm_rand() from a test requiring overrides, but BM_RAND_VALS is empty");
    }
    return mt_rand($min, $max);
}

// Now include the bootstrap file from the code itself
require_once( "./src/lib/bootstrap.php" );
