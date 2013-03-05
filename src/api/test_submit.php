<?php

    require_once 'loadMockGameData.php';
    require_once '../engine/BMAttack.php';

    $game = loadMockGameData();
    // load dieSelectStatus, which should contain boolean values of whether each
    // die is selected, starting with attacker dice and concluding with
    // defender dice
    //
    // note that the object is coerced into an associative array by the second
    // input parameter in json_decode, and then into an indexed array by
    // array_values
    $dieSelectStatus = array_values(json_decode($_POST['dieSelectStatus'], TRUE));

    // determine attacker and defender indices from POST
    $attackerIdx = intval($_POST['attackerIdx']);
    $defenderIdx = intval($_POST['defenderIdx']);
    $attackers = array();
    $defenders = array();
    $attackerDieIdx = array();
    $defenderDieIdx = array();

    // divide selected dice up into attackers and defenders
    $nAttackerDice = count($game->activeDieArrayArray[$attackerIdx]);
    $nDefenderDice = count($game->activeDieArrayArray[$defenderIdx]);

    // determine which die is the defender's first die
    // (used in multiplayer scenarios)
    //
    // In the GUI, the dice must come in the following order:
    // attacker, then increasing idx until player idx ($nPlayers - 1) is reached,
    // then idx 0, then increasing until (attacker idx - 1)
    $defenderStartIdx = 0;
    for ($playerIdx = $attackerIdx; $playerIdx < $game->nPlayers; $playerIdx++) {
        if ($defenderIdx == $playerIdx) {
            break;
        }
        $defenderStartIdx += count($game->activeDieArrayArray[$playerIdx]);
    }
    if ($defenderIdx < $attackerIdx && $defenderIdx > 0) {
        for ($playerIdx = 0; $playerIdx < $defenderIdx; $playerIdx++) {
            $defenderStartIdx += count($game->activeDieArrayArray[$playerIdx]);
        }
    }

    // divide up selections into those for attackers and those for defenders
    $dieSelectStatusForAttacker = array_slice($dieSelectStatus, 0, $nAttackerDice);
    $dieSelectStatusForDefender = array_slice($dieSelectStatus, $defenderStartIdx, $nDefenderDice);

    for ($dieIdx = 0; $dieIdx < $nAttackerDice; $dieIdx++) {
        if ($dieSelectStatusForAttacker[$dieIdx]) {
            $attackers[] = $game->activeDieArrayArray[$attackerIdx][$dieIdx];
            $attackerDieIdx[] = $dieIdx;
        }
    }

    for ($dieIdx = 0; $dieIdx < $nDefenderDice; $dieIdx++) {
        if ($dieSelectStatusForDefender[$dieIdx]) {
            $defenders[] = $game->activeDieArrayArray[$defenderIdx][$dieIdx];
            $defenderDieIdx[] = $dieIdx;
        }
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
