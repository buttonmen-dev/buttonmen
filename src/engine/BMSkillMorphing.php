<?php
/**
 * BMSkillMorphing: Code specific to the morphing die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the morphing die skill
 */
class BMSkillMorphing extends BMSkill {
    public static $hooked_methods = array('capture');

    public static function capture(&$args) {
        if (!self::are_dice_in_attack_valid($args)) {
            return;
        }

        $attacker = $args['caller'];
        if ($attacker->has_flag('JustPerformedUnsuccessfulAttack')) {
            return;
        }

        $game = $attacker->ownerObject;
        $activeDieArrayArray = $game->activeDieArrayArray;

        $attackerDieIdx = array_search(
            $attacker,
            $args['attackers'],
            TRUE
        );
        assert(FALSE !== $attackerDieIdx);

        $newAttackDie = self::create_morphing_clone_target($args['caller'], $args['defenders'][0]);
        $newAttackDie->copy_skills_from_die($args['caller']);

        $activeDieArrayArray[$attacker->playerIdx][$attacker->activeDieIdx] = $newAttackDie;
        $args['attackers'][$attackerDieIdx] = $newAttackDie;
        $game->activeDieArrayArray = $activeDieArrayArray;
    }

    protected static function are_dice_in_attack_valid($args) {
        if (!is_array($args['attackers']) ||
            (0 == count($args['attackers'])) ||
            !is_array($args['defenders']) ||
            (0 == count($args['defenders']))) {
            return FALSE;
        }

        return TRUE;
    }

    protected static function create_morphing_clone_target($att, $def) {
        $newDie = clone $def;
        unset($newDie->value);
        $newDie->remove_all_flags();

        $newDie->captured = FALSE;
        $newDie->ownerObject = $att->ownerObject;
        $newDie->playerIdx = $att->playerIdx;
        $newDie->originalPlayerIdx = $att->originalPlayerIdx;
        $newDie->add_flag('IsAttacker');

        if (!empty($att->flagList)) {
            foreach ($att->flagList as $flagType => $flag) {
                $newDie->add_flag($flagType, $flag->value());
            }
        }

        $newDie->add_flag('HasJustMorphed');

        return $newDie;
    }

    protected static function get_description() {
        return 'When a Morphing Die is used in any attack, it changes ' .
               'size, becoming the same size as the die that was captured. ' .
               'It is then re-rolled. Morphing Dice change size every time ' .
               'they capture another die. If a Morphing die is captured, ' .
               'its scoring value is based on its size at the time of ' .
               'capture; likewise, if it is not captured during a round, ' .
               'its scoring value is based on its size at the end of the ' .
               'round';
    }

    protected static function get_interaction_descriptions() {
        return array(
            'Radioactive' => 'Dice with both Radioactive and Morphing skills first morph into the ' .
                             'size of the captured die, and then decay',
        );
    }

    public static function prevents_win_determination() {
        return TRUE;
    }
}
