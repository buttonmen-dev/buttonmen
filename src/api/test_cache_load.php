<?php

//require_once '../engine/BMButton.php';
//require_once '../engine/BMGame.php';

require_once '../engine/BMInterface.php';

$gameId = '424242';
$gameInterface = new BMInterface;
$game = $gameInterface->load_game('424242');
//$gamefile = "/var/www/bmgame/$gameId.data";
//$gameInt = file_get_contents($gamefile);
//$game = unserialize($gameInt);

header('Content-Type: text/plain');

echo $gameInterface->message;

//echo "Loaded data for game $gameId from file $gamefile.  Data:\n";

var_dump($game);

?>
