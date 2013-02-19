<?php

require_once '../engine/BMButton.php';
require_once '../engine/BMGame.php';

// load buttons
$button1 = new BMButton;
$button1->load_from_name('Bauer');

$button2 = new BMButton;
$button2->load_from_name('Stark');

// load game
$game = new BMGame(424242, array(123, 456));
$game->buttonArray = array($button1, $button2);
$game->waitingOnActionArray = array(FALSE, FALSE);
$game->proceed_to_next_user_action();

// specify swing dice correctly
$game->swingValueArrayArray = array(array('X'=>19), array('X'=>4));
$game->proceed_to_next_user_action();

$gameDataJson = json_encode(array('status' => 'ok',
                                  'data' => array('name' => 'Morgan',
                                                  'playerId' => $game->playerIdArray)));

header('Content-Type: application/json');
//echo json_encode(array('status' => 'ok', 'data' => array('name' => 'Morgan')));
echo $gameDataJson;
?>
