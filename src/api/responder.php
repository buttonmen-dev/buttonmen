<?php
/**
 * responder: responds to POST requests from the UI
 *
 * @author chaos
 */

/**
 * Requires both the actual spec and the responder feeding the real data
 */
require_once __DIR__.'/ApiResponder.php';
require_once __DIR__.'/ApiSpec.php';

/**
 * If responder was called via a POST request (rather than by
 * test code), the $_POST variable will be set
 */
if ($_POST) {
    $spec = new ApiSpec();
    $responder = new ApiResponder($spec, FALSE);
    $json_params = file_get_contents("php://input");
    $args = json_decode($json_params, TRUE);
    $responder->process_request($args);
}
