<?php

require_once 'ApiResponder.php';
require_once 'ApiSpec.php';

// If responder was called via a POST request (rather than by
// test code), the $_POST variable will be set
if ($_POST) {
    $spec = new ApiSpec();
    $responder = new ApiResponder($spec, FALSE);
    $responder->process_request($_POST);
}
