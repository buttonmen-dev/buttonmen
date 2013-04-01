<?php

require_once 'loadMockGameData.php';

$game = loadMockGameData();
$gameDataJson = json_encode($game->getJsonData());

header('Content-Type: application/json');
echo $gameDataJson;

echo readdir('/');

file_put_contents('/bmgame/gamedata.json', $gameDataJson);

?>
