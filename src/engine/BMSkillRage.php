<?php
/**
 * BMSkillRage: Code specific to the rage die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the rage die skill
 */
class BMSkillRage extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('capture');

    /**
     * Hooked method applied during capture
     *
     * @param array $args
     */
    public static function capture(&$args) {
//        $attacker = &$args['attackers'][0];
//        $attacker->roll(TRUE);
//        $attacker->add_flag('JustPerformedTripAttack', $attacker->value);
//
//        if ($attacker instanceof BMDieTwin) {
//            foreach ($attacker->dice as $subdie) {
//                $subdie->add_flag('JustPerformedTripAttack', $subdie->value);
//            }
//        }
//
//        $defender = &$args['defenders'][0];
//        $defender->roll(TRUE);
//
//        $defender->captured = ($defender->value <= $attacker->value);
//        if (!$defender->captured) {
//            $defender->remove_flag('WasJustCaptured');
//            $attacker->add_flag('JustPerformedUnsuccessfulAttack');
//        }
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return '';
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
        return array();
    }
}
