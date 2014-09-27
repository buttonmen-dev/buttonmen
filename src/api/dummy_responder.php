<?php

/** Alternative responder which doesn't use real databases or
 *  sessions, but rather exists only to send dummy data used for
 *  automated testing of API compliance
 */

require_once 'DummyApiResponder.php';
require_once 'ApiSpec.php';

// If dummy_responder was called via a POST request (rather than
// by test code), the $_POST variable will be set
if ($_POST) {
    $spec = new ApiSpec();
    $dummy_responder = new DummyApiResponder($spec, FALSE);
    $dummy_responder->process_request($_POST);
}
