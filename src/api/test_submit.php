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
    $arrayLength = count($stringArray);

    // determine attacker and defender indices from POST
    $attackerIdx = intval($_POST['attackerIdx']);
    $defenderIdx = intval($_POST['defenderIdx']);
    $attackers = array();
    $defenders = array();
    $attackerDieIdx = array();
    $defenderDieIdx = array();

    // divide selected dice up into attackers and defenders
    $arrayIdx = 0;
    while ($arrayIdx < $arrayLength) {
        if ($attackerIdx == $stringArray[$arrayIdx]) {
            $arrayIdx++;
            $attackers[] = $game->activeDieArrayArray[$attackerIdx][$stringArray[$arrayIdx]];
            $attackerDieIdx[] = intval($stringArray[$arrayIdx]);
        } elseif ($defenderIdx == $stringArray[$arrayIdx]) {
            $arrayIdx++;
            $defenders[] = $game->activeDieArrayArray[$defenderIdx][$stringArray[$arrayIdx]];
            $defenderDieIdx[] = intval($stringArray[$arrayIdx]);
        } else {
            throw new LogicException('There can only be one attacker and one defender.');
        }

        $arrayIdx++;
    }

    // validate attack
    $success = FALSE;
    $attackArray = array(BMAttackPower::get_instance(),
                         BMAttackSkill::get_instance());
    $attackTypeArray = array('power', 'skill');

    $success = FALSE;

    foreach ($attackArray as $idx => $attack) {
        // find out if the chosen dice form a valid attack
        $game->attack = array($attackerIdx, $defenderIdx, $attackerDieIdx, $defenderDieIdx, $attackTypeArray[$idx]);
        foreach ($attackers as $attackDie) {
            $attack->add_die($attackDie);
        }
        if ($attack->find_attack($game)) {
            if ($attack->validate_attack($game, $attackers, $defenders)) {
                $success = TRUE;
                break;
            }
        }
    }

    // james: maybe the following code needs to be in the logic for the pass
    //        attack validation
    if (!$success &&
        (0 == count($attackerDieIdx)) &&
        (0 == count($defenderDieIdx))) {
        $success = TRUE;

        // find out if there are any possible attacks with any combination of
        // the attacker's and defender's dice
        foreach ($attackArray as $idx => $attack) {
            $game->attack = array($attackerIdx,
                                  $defenderIdx,
                                  range(0, count($game->attackerAllDieArray) - 1),
                                  range(0, count($game->defenderAllDieArray) - 1),
                                  $attackTypeArray[$idx]);
            foreach ($game->attackerAllDieArray as $attackDie) {
                $attack->add_die($attackDie);
            }
            if ($attack->find_attack($game)) {
                // a pass attack is invalid
                $success = FALSE;
                break;
            }
        }
    }

    // output the result of the attack
    if ($success) {
      echo 'attack valid';
    } else {
      echo 'attack invalid';
    }
?>
