<?php
/**
 * BMSkillTrip: Code specific to the trip die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the trip die skill
 */
class BMSkillTrip extends BMSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('attack_list',
                                          'initiative_value',
                                          'pre_capture');

    /**
     * Hooked method applied when determining possible attack types
     *
     * @param array $args
     */
    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];
        $attackTypeArray['Trip'] = 'Trip';
    }

    /**
     * Hooked method applied when determining the initiative value of a die
     *
     * @param array $args
     */
    public static function initiative_value(&$args) {
        if (!is_array($args)) {
            return;
        }

        // trip dice don't contribute to initiative
        $args['initiativeValue'] = -1;
    }

    /**
     * Hooked method applied before capture
     *
     * @param array $args
     */
    public static function pre_capture(&$args) {
        if ($args['type'] != 'Trip') {
            return;
        }

        assert(array_key_exists('attackers', $args));
        assert(array_key_exists('defenders', $args));

        if (1 != count($args['attackers'])) {
            throw new LogicException('Only one attacker for a trip attack');
        }

        $attacker = &$args['attackers'][0];
        $defender = self::get_single_defender($args['defenders'], TRUE);

        $attacker->add_flag('IsAttacker');
        $attacker->add_flag('IsAboutToPerformTripAttack');
        $attacker->roll(TRUE);
        $attackerValue = '';
        if (!is_null($attacker->value)) {
            $attackerValue = $attacker->value;
        }

        $attacker->remove_flag('IsAboutToPerformTripAttack');
        $attacker->add_flag(
            'JustPerformedTripAttack',
            $attacker->get_recipe(TRUE) . ':' . $attackerValue
        );

        if ($attacker instanceof BMDieTwin) {
            foreach ($attacker->dice as $subdie) {
                $subdieValue = '';

                if (!is_null($subdie->value)) {
                    $subdieValue = $subdie->value;
                }

                $subdie->add_flag(
                    'JustPerformedTripAttack',
                    $subdie->recipe . ':' . $subdieValue
                );
            }
        }

        $defender->roll(TRUE);

        $defender->captured = ($defender->value <= $attacker->value);
        if (!$defender->captured) {
            $defender->remove_flag('WasJustCaptured');
            $attacker->add_flag('JustPerformedUnsuccessfulAttack');
        }
    }

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'These dice can also make Trip Attacks. To make a Trip Attack, choose any one opposing die as the ' .
               'Target. Roll both the Trip Die and the Target, then compare the numbers they show. If the Trip Die ' .
               'now shows an equal or greater number than the Target, the Target is captured. Otherwise, the attack ' .
               'merely has the effect of re-rolling both dice. A Trip Attack is illegal if it has no chance of ' .
               'capturing (this is possible in the case of a Trip-1 attacking a Twin Die). IMPORTANT: Trip Dice do ' .
               'not count for determining who goes first.';
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
            'Konstant' => 'Dice with both Konstant and Trip skills retain their current value when rerolled',
            'Queer' => 'Dice with both Queer and Trip skills always determine their success or failure at Trip ' .
                       'Attacking via a Power Attack',
            'Shadow' => 'Dice with both Shadow and Trip skills always determine their success or failure at Trip ' .
                        'Attacking via a Power Attack',
            'Turbo' => 'If a Turbo Die is rerolled because it is the target ' .
                       'of a Trip attack, then the size cannot be changed.',
        );
    }
}
