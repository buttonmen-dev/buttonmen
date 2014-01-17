<?php

class BMSkillMorphing extends BMSkill {
    public static $hooked_methods = array('capture');

    public static function capture($args) {
        if (!self::are_dice_in_attack_valid($args)) {
            return;
        }

        $attackers = &$args['attackers'];

        $cloneTarget = self::create_morphing_clone_target($args['defenders'][0]);

        foreach ($attackers as $dieIdx => &$att) {
            if ($att->has_skill('Morphing')) {
                $attBackup = clone $att;
                $att = clone $cloneTarget;
                $att->copy_skills_from_die($attBackup);
                $att->ownerObject = $attBackup->ownerObject;
                $att->playerIdx = $attBackup->playerIdx;
                $att->originalPlayerIdx = $attBackup->originalPlayerIdx;
                $att->hasAttacked = TRUE;
                $att->roll(TRUE);
            }
        }
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

    protected static function create_morphing_clone_target($die) {
        if ($die instanceof BMDieSwing) {
            $die = $die->cast_as_BMDie();
        } elseif ($die instanceof BMDieTwin) {
            foreach ($die->dice as &$subDie) {
                if ($subDie instanceof BMDieSwing) {
                    $subDie = $subDie->cast_as_BMDie();
                }
            }
        }

        return $die;
    }
}
