<?php

class BMSkillFocus extends BMSkill {
    public static $hooked_methods = array("react_to_initiative");

    public static function react_to_initiative($args) {
        $activeDieArrayArray = $args['activeDieArrayArray'];
        $playerIdx = $args['playerIdx'];
        $activeDieArray = $activeDieArrayArray[$playerIdx];

        // cache focus die values and then set the die value to minimum
        $valueCacheArray = array();
        foreach ($activeDieArray as $dieIdx => &$activeDie) {
            if (array_search('BMSkillFocus', $activeDie->skillList)) {
                $valueCacheArray[$dieIdx] = $activeDie->value;
                $activeDie->value = $activeDie->min;
            }
        }

        // calculate initiative now, with all focus dice set to minimum
        $doesPlayerHaveInitiativeArray =
          BMGame::does_player_have_initiative_array($activeDieArrayArray);

        // reset die values
        foreach ($activeDieArray as $dieIdx => &$activeDie) {
            if (array_search('Focus', $activeDie->skillList)) {
                $activeDie->value = $valueCacheArray[$dieIdx];
            }
        }

        // the player must be able to win initiative unambiguously to be able to
        // change the value of focus dice
        return ($doesPlayerHaveInitiativeArray[$playerIdx] &&
                1 == array_sum($doesPlayerHaveInitiativeArray));
    }

}

?>
