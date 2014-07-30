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

        $att = self::create_morphing_clone_target($args['caller'], $args['defenders'][0]);
        $att->copy_skills_from_die($args['caller']);
        $att->roll(TRUE);

        return $att;
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
        $newDie->remove_all_flags();

        // convert swing and option dice back to normal dice
        if ($newDie instanceof BMDieSwing ||
            $newDie instanceof BMDieOption) {
            $newDie = $newDie->cast_as_BMDie();
        } elseif ($newDie instanceof BMDieTwin) {
            foreach ($newDie->dice as &$subDie) {
                if ($subDie instanceof BMDieSwing) {
                    $subDie = $subDie->cast_as_BMDie();
                }
            }
        }

        $newDie->captured = FALSE;
        $newDie->ownerObject = $att->ownerObject;
        $newDie->playerIdx = $att->playerIdx;
        $newDie->originalPlayerIdx = $att->originalPlayerIdx;
        $newDie->hasAttacked = TRUE;

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
        return array();
    }

    public static function prevents_win_determination() {
        return TRUE;
    }
}
