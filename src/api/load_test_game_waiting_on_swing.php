<?php

require_once 'loadMockGameData.php';

$game = loadMockGameDataWaitingForSwing();
$gameDataJson = json_encode($game->getJsonData());

header('Content-Type: application/json');
echo $gameDataJson;
?>
