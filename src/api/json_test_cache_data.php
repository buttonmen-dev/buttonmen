<?php

require_once 'loadMockGameData.php';

$game = loadMockGameData();
$gameDataJson = json_encode($game->getJsonData());

file_put_contents('/shared/bmgame/gamedata.json', $gameDataJson);

header('Content-Type: application/json');
echo $gameDataJson;
?>
