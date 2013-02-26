<?php

require_once 'loadMockGameData.php';

$game = loadMockGameData();
$gameDataJson = json_encode($game);

header('Content-Type: application/json');
echo $gameDataJson;
?>
