<?php

require_once 'loadMockGameData.php';

$game = loadMockGameDataDeterminingInitiative();
$gameDataJson = json_encode($game->getJsonData());

header('Content-Type: application/json');
echo $gameDataJson;
?>
