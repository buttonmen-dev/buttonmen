<?php
/**
 * BMSkillTimeAndSpace: Code specific to the jolt die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the jolt die skill
 */
class BMSkillJolt extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('capture', 'be_captured');

    /**
     * Hooked method applied during capture
     *
     * @param array $args
     */
    public static function capture($args) {
        if (!is_array($args)) {
            return;
        }

        if (!array_key_exists('attackers', $args)) {
            return;
        }

        $dieFound = FALSE;
        foreach ($args['attackers'] as $die) {
            if ($die->has_skill('Jolt') && $die->has_flag('IsAttacker')) {
                $dieFound = TRUE;
                $die->remove_skill('Jolt');
            }
        }

        if (!$dieFound) {
            throw new LogicException('Attacking Jolt die not found');
        }

        $game = $die->ownerObject;

        if (!($die instanceof BMDie) || !($game instanceof BMGame)) {
            return;
        }

        $game->nextPlayerIdx = $die->playerIdx;
        $game->nextPlayerCause = 'JoltAttacker';
    }

    /**
     * Hooked method applied when die is being captured
     *
     * @param array $args
     */
    public static function be_captured($args) {
        if (!self::validate_be_captured_args($args)) {
            return;
        }

        $dieFound = FALSE;
        $dieCaptured = FALSE;
        foreach ($args['defenders'] as $defendingDie) {
            if ($defendingDie->has_skill('Jolt')) {
                $dieFound = TRUE;

                if ($defendingDie->has_flag('WasJustCaptured')) {
                    $dieCaptured = TRUE;
                    break;
                }
            }
        }
        if (!$dieFound) {
            throw new LogicException('Defending Jolt die not found');
        }

        if (!$dieCaptured) {
            return;
        }

        foreach ($args['attackers'] as $attackingDie) {
            if (!$attackingDie->has_flag('IsAttacker')) {
                continue;
            }

            $game = $attackingDie->ownerObject;

            if (!($attackingDie instanceof BMDie) || !($game instanceof BMGame)) {
                return;
            }

            $game->nextPlayerIdx = $attackingDie->playerIdx;
            $game->nextPlayerCause = 'JoltCaptured';
            break;
        }
    }

    protected static function validate_be_captured_args($args) {
        if (!is_array($args)) {
            return FALSE;
        }

        if (!array_key_exists('attackers', $args)) {
            return FALSE;
        }

        if (!array_key_exists('defenders', $args)) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'If a Jolt Die participates in an attack, ' .
               'then the player will take another turn ' .
               'and the Jolt die loses the Jolt skill. ' .
               'If a Jolt die is captured, then the player who captured it ' .
               'takes another turn.';
    }

    /**
     * Descriptions of interactions between this skill and other skills
     *
     * An array, indexed by other skill name, whose values are descriptions of
     * interactions between the relevant skills
     *
     * @return array
     */
    protected static function get_interaction_descriptions() {
        return array(
            'TimeAndSpace' => 'If a die with both the Jolt and TimeAndSpace skills rerolls to an odd number, ' .
                              'it still only gives one extra turn.',
        );
    }
}
