<?php

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

        return $newDie;
    }
}
