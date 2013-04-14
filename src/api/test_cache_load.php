<?php

require_once '../engine/BMInterface.php';

$gameId = '424242';
$gameInterface = new BMInterface;
$game = $gameInterface->load_game('424242');

header('Content-Type: text/plain');

echo $gameInterface->message;

var_dump($game);

?>
