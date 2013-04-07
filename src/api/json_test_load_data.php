<?php

require_once '../engine/BMButton.php';
require_once '../engine/BMGame.php';

$gameDataJson = file_get_contents('/var/www/bmgame/gamedata.json');
$gameData = json_decode($gameDataJson);
header('Content-Type: text/plain');

if ($gameData["status"] == "ok") {
  echo "Game status is ok.  Game data:";
  var_dump($gameData["data"]);
} else {
  echo "Game status is NOT ok.  Loaded object data:";
  var_dump($gameData);
}

?>
