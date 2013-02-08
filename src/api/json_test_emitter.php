<?php
header('Content-Type: application/json');
$r = rand(0,10);
if ($r <= 1) {
echo json_encode(array('status' => 'error', 'data' => array('error' => 'I dun goofed!'))); // Simulate an error in the engine
} elseif ($r <= 6) {
echo json_encode(array('status' => 'ok', 'data' => array('name' => 'Morgan')));
} else {
echo json_encode(array('status' => 'ok', 'data' => array('name' => 'Giant')));
}