<?php

require_once '../engine/BMButton.php';
require_once '../engine/BMGame.php';

function loadMockGameDataWaitingForSwing() {
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

    return($game);
}

function loadMockGameData() {
    $game = loadMockGameDataWaitingForSwing();

    // specify swing dice correctly
    $game->swingValueArrayArray = array(array('X'=>19), array('X'=>4));
    $game->proceed_to_next_user_action();

    $game->activePlayerIdx = 1;
    $activeDieArrayArray = $game->activeDieArrayArray;
    $activeDieArrayArray[0][0]->value = 2;
    $activeDieArrayArray[0][1]->value = 1;
    $activeDieArrayArray[0][2]->value = 3;
    $activeDieArrayArray[0][3]->value = 4;
    $activeDieArrayArray[0][4]->value = 6;
    $activeDieArrayArray[1][0]->value = 2;
    $activeDieArrayArray[1][1]->value = 1;
    $activeDieArrayArray[1][2]->value = 3;
    $activeDieArrayArray[1][3]->value = 4;
    $activeDieArrayArray[1][4]->value = 5;

    return($game);
}

?>
