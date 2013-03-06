<?php

require_once 'loadMockGameData.php';

$game = loadMockGameData();
$gameDataJson = json_encode($game->getJsonData());

header('Content-Type: application/json');
echo $gameDataJson;
?>
