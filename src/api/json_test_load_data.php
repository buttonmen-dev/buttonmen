<?php

require_once '../engine/BMButton.php';
require_once '../engine/BMGame.php';

$gameDataJson = file_get_contents('/var/www/bmgame/gamedata.json');
$gameData = json_decode($gameDataJson);

header('Content-Type: text/plain');
var_dump($gameData);

?>
