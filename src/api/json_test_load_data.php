<?php

require_once '../engine/BMButton.php';
require_once '../engine/BMGame.php';

$gameInt = file_get_contents('tmp1');
$game = unserialize($gameInt);

//$gameDataJson = file_get_contents('/var/www/bmgame/gamedata.json');
//$gameData = json_decode($gameDataJson);
header('Content-Type: text/plain');

var_dump($game);

?>
