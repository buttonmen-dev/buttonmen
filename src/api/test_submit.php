<?php

    require_once 'loadMockGameData.php';
    require_once '../engine/BMAttack.php';

    $game = loadMockGameData();
    // load POST string, which should be something like:
    // playerIdx_1_dieIdx_1 playerIdx_0_dieIdx_2
    $selectedDiceString = $_POST['selectedDice'];
    // parse string into its component values, which should end up with something like
    // 1 1 0 2
    $stringArray = preg_split("/[^[:digit:]]+/", $selectedDiceString, NULL, PREG_SPLIT_NO_EMPTY);

    // ensure that enough dice have been selected for an attack
    // james: only power attacks are currently implemented here
    $arrayLength = count($stringArray);
    if ($arrayLength < 2) {
        return;
    }

    // determine attacker and defender indices from POST
    $attackerIdx = $_POST['attackerIdx'];
    $defenderIdx = $_POST['defenderIdx'];
    $attackers = array();
    $defenders = array();

    // divide selected dice up into attackers and defenders
    $arrayIdx = 0;
    while ($arrayIdx < $arrayLength) {
        if ($attackerIdx == $stringArray[$arrayIdx]) {
            $arrayIdx++;
            $attackers[] = $game->activeDieArrayArray[$attackerIdx][$stringArray[$arrayIdx]];
        } elseif ($defenderIdx == $stringArray[$arrayIdx]) {
            $arrayIdx++;
            $defenders[] = $game->activeDieArrayArray[$defenderIdx][$stringArray[$arrayIdx]];
        } else {
            throw new LogicException('There can only be one attacker and one defender.');
        }

        $arrayIdx++;
    }

    // validate attack
    $success = FALSE;
    $attackArray = array(BMAttackPass::get_instance(),
                         BMAttackPower::get_instance()
//                         BMAttackSkill::get_instance()
        );
    foreach ($attackArray as $attack) {
        $success = $attack->validate_attack($game, $attackers, $defenders);
        if ($success) {
            break;
        }
    }


    // output the result of the attack
    if ($success) {
      echo 'attack valid';
    } else {
      echo 'attack invalid';
    }
?>
