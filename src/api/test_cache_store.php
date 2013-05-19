<?php

require_once 'loadMockGameData.php';
require_once '../engine/BMInterface.php';

$game = loadMockGameData();

$gameInterface = new BMInterface;
$gameInterface->save_game($game);

header('Content-Type: text/plain');

echo $gameInterface->message;

?>
