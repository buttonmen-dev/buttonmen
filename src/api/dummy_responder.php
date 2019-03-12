<?php
/**
 * dummy_responder: Alternative responder which doesn't use real
 * databases or sessions, but rather exists only to send dummy data
 * used for automated testing of API compliance
 *
 * @author chaos
 */

/**
 * Requires both the actual spec and the dummy responder feeding the mock data
 */
require_once 'DummyApiResponder.php';
require_once 'ApiSpec.php';

/**
 * If dummy_responder was called via a POST request (rather than
 * by test code), the $_POST variable will be set
 */
if ($_POST) {
    $spec = new ApiSpec();
    $dummy_responder = new DummyApiResponder($spec, FALSE);
    $json_params = file_get_contents("php://input");
    $args = json_decode($json_params, TRUE);
    $dummy_responder->process_request($args);
}
