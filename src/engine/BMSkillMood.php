<?php

class BMSkillMood extends BMSkill {
    public static $hooked_methods = array('pre_roll', 'add_skill');

    public static function pre_roll(&$args) {
        if (!($args['die'] instanceof BMDie) ||
            (TRUE !== $args['isTriggeredByAttack'])) {
            return FALSE;
        }

        // do nothing if the die is not a swing die or a
        // twin die with swing components
        $die = $args['die'];
        if (!isset($die->swingType)) {
            return FALSE;
        }

        $swingRange = BMDieSwing::swing_range($die->swingType);
        $newSwingValue = mt_rand($swingRange[0], $swingRange[1]);
        if ($die instanceof BMDieSwing) {
            $die->max = $newSwingValue;
        } elseif ($die instanceof BMDieTwin) {
            foreach ($die->dice as $subdie) {
                if ($subdie instanceof BMDieSwing) {
                    $subdie->max = $newSwingValue;
                }
            }
            $die->recalc_max_min();
        } else {
            throw new LogicException('Mood applied to non-swing die.');
        }

        return TRUE;
    }

    public static function add_skill($args) {
        if (!is_array($args) ||
            !($args['die'] instanceof BMDie)) {
            return;
        }

        // Mood can only be added to swing dice and twin swing dice
        $die = $args['die'];
        $dieCanHaveMood =
            ($die instanceof BMDieSwing) ||
            (($die instanceof BMDieTwin) &&
             (($die->dice[0] instanceof BMDieSwing) ||
              ($die->dice[1] instanceof BMDieSwing)));

        if (!$dieCanHaveMood) {
            $args['die']->remove_skill('Mood');
        }
    }
}
