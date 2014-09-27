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
        $hasPlayerInitArray =
          BMGame::does_player_have_initiative_array($activeDieArrayArray);

        // reset die values
        foreach ($activeDieArray as $dieIdx => &$activeDie) {
            if (array_search('BMSkillFocus', $activeDie->skillList)) {
                $activeDie->value = $valueCacheArray[$dieIdx];
            }
        }

        // the player must be able to win initiative unambiguously to be able to
        // change the value of focus dice
        return ($hasPlayerInitArray[$playerIdx] &&
                1 == array_sum($hasPlayerInitArray));
    }

    protected static function get_description() {
        return 'If you do not have the initiative at the start of ' .
               'a round you may reduce the values showing on one or more ' .
               'of your Focus Dice. You may only do this if it results in ' .
               'your gaining the initiative. If your opponent has Focus ' .
               'Dice, they may now do the same, and each player may respond ' .
               'by turning down one or more Focus Dice until no further ' .
               'moves are legal, or until one player allows the other to ' .
               'take the first turn. IMPORTANT: If you go first, any Focus ' .
               'Dice you have reduced may not be used as part of your first ' .
               'attack. (The second player has no such restriction.)';
    }

    protected static function get_interaction_descriptions() {
        return array(
            'Chance' => 'Dice with both Chance and Focus skills may choose either skill to gain initiative',
        );
    }
}
