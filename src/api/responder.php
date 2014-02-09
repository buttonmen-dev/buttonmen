<?php

require_once 'ApiResponder.php';

// If responder was called via a POST request (rather than by
// test code), the $_POST variable will be set
if ($_POST) {
    $responder = new ApiResponder(FALSE);
    $responder->process_request($_POST);
}
