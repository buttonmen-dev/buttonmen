<?php

    require_once 'loadMockGameData.php';
    require_once '../engine/BMAttack.php';

    $game = loadMockGameData();
    $selectedDiceString = $_POST['selectedDice'];


    $attackerIdx = 1;
    $defenderIdx = 0;

    $string = preg_replace("/[^[:digit:]]+/", " ", $selectedDiceString);
    $stringArray = preg_split("/[^[:digit:]]+/", $selectedDiceString, NULL, PREG_SPLIT_NO_EMPTY);

    $attackers = array($game->activeDieArrayArray[$stringArray[0]][$stringArray[1]]);
    $defenders = array($game->activeDieArrayArray[$stringArray[2]][$stringArray[3]]);

    $attack = BMAttackPower::get_instance();
    $success = $attack->validate_attack($game, $attackers, $defenders);

//    echo var_dump($stringArray);
    if ($success) {
      echo 'attack successful';
    } else {
      echo 'attack failed';
    }
?>
