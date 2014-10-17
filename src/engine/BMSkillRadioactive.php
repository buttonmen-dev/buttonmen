<?php
/**
 * BMSkillRadioactive: Code specific to the radioactive die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the radioactive die skill
 */
class BMSkillRadioactive extends BMSkill {
    public static $hooked_methods = array('capture', 'be_captured');

    public static function capture(&$args) {
        self::radioactive_split($args);
    }

    public static function be_captured(&$args) {
        self::radioactive_split($args);
    }

    protected static function radioactive_split(&$args) {
        if (!is_array($args)) {
            return;
        }

        if (!array_key_exists('attackers', $args)) {
            return;
        }

        if (!array_key_exists('defenders', $args)) {
            return;
        }

        if (count($args['attackers']) != 1) {
            return;
        }

        if (count($args['defenders']) != 1) {
            return;
        }

        $attacker = &$args['attackers'][0];
        $game = $attacker->ownerObject;
        $activeDieArrayArray = $game->activeDieArrayArray;
        $attackerPlayerIdx = $game->attack['attackerPlayerIdx'];

        $dieIdx = array_search(
            $attacker,
            $activeDieArrayArray[$attackerPlayerIdx],
            TRUE
        );
        assert(FALSE !== $dieIdx);

        $attacker->remove_skill('Radioactive');
        $attacker->remove_skill('Turbo');
        $attacker->remove_skill('Mood');
        $attacker->remove_skill('Mad');
        $attacker->remove_skill('Jolt');
        $attacker->remove_skill('TimeAndSpace');

        $newAttackerDieArray = $attacker->split();

        array_splice(
            $activeDieArrayArray[$attackerPlayerIdx],
            $dieIdx,
            1,
            $newAttackerDieArray
        );

        array_splice(
            $args['attackers'],
            0,
            1,
            $newAttackerDieArray
        );

        $game->activeDieArrayArray = $activeDieArrayArray;
    }

    protected static function get_description() {
        return '';
    }

    protected static function get_interaction_descriptions() {
        return array(

        );
    }
}
