<?php

require_once '../engine/BMButton.php';
require_once '../engine/BMGame.php';

$gameId = '424242';
$gamefile = "/var/www/bmgame/$gameId.data";
$gameInt = file_get_contents('$gamefile');
$game = unserialize($gameInt);

header('Content-Type: text/plain');

echo "Loaded data for game $gameId from file $gamefile.  Data:\n";

var_dump($game);

?>
