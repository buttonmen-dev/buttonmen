<?php

require_once 'loadMockGameData.php';

$game = loadMockGameData();
//$gameInt = serialize($game);

$gameInterface = new BMInterface;
$gameInterface->save_game($game);

header('Content-Type: text/plain');
//$gamefile = "/var/www/bmgame/$game->gameId.data";

echo $gameInterface->message;

//echo "Generated game $game->gameId: caching data in file: $gamefile\n";

//file_put_contents($gamefile, $gameInt);

echo "Done\n";

?>
