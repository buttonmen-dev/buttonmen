<?php

require_once 'loadMockGameData.php';

$game = loadMockGameData();
$gameDataJson = json_encode($game->getJsonData());

header('Content-Type: application/json');
echo $gameDataJson

//$dir = '/var/www/';
//if (is_dir($dir)) {
//    if ($dh = opendir($dir)) {
//        while (($file = readdir($dh)) !== false) {
//            echo "filename: $file : filetype: " . filetype($dir . $file) . "\n";
//        }
//        closedir($dh);
//    }
//}

file_put_contents('/var/www/bmgame/gamedata.json', $gameDataJson);

?>
