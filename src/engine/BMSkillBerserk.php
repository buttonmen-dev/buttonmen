<?php

class BMSkillBerserk extends BMSkill {
    public static $hooked_methods = array('attack_list', 'capture');

    public static function attack_list($args) {
        if (!is_array($args)) {
            return;
        }

        $attackTypeArray = &$args['attackTypeArray'];

        foreach (BMSkillBerserk::incompatible_attack_types() as $attackType) {
            if (array_key_exists($attackType, $attackTypeArray)) {
                unset($attackTypeArray[$attackType]);
            }
        }

        $attackTypeArray['Berserk'] = 'Berserk';
    }

    public static function incompatible_attack_types($args = NULL) {
        return array('Skill');
    }

    public static function capture($args) {
        if (!is_array($args)) {
            return;
        }

        if (!array_key_exists('type', $args)) {
            return;
        }

        if ('Berserk' != $args['type']) {
            return;
        }

        if (!array_key_exists('attackers', $args)) {
            return;
        }

        assert(1 == count($args['attackers']));

        $attacker = $args['attackers'][0];
        $game = $attacker->ownerObject;
        $activeDieArrayArray = $game->activeDieArrayArray;
        $attackerPlayerIdx = $game->attack['attackerPlayerIdx'];

        $dieIdx = array_search(
            $attacker,
            $activeDieArrayArray[$attackerPlayerIdx],
            TRUE
        );
        assert(FALSE !== $dieIdx);

        // james: which other skills need to be lost after a Berserk attack?
        $attacker->remove_skill('Berserk');

        // force removal of swing, twin die, and option status
        $splitDieArray = $attacker->split();
        $newAttackDie = $splitDieArray[0];
        $newAttackDie->roll(TRUE);
        $activeDieArrayArray[$attackerPlayerIdx][$dieIdx] = $newAttackDie;
        $args['attackers'][0] = $newAttackDie;
        $game->activeDieArrayArray = $activeDieArrayArray;
    }

    protected function get_description() {
	return 'These dice cannot participate in Skill Attacks; ' .
               'instead they can make a Berserk Attack. These work exactly ' .
               'like Speed Attacks - one Berserk Die can capture any number ' .
               'of dice which add up exactly to its value. Once a Berserk ' .
               'Die performs a Berserk Attack, it is replaced with a ' .
               'non-berserk die with half the number of sides it previously ' .
               'had, rounding up.';
    }

    protected function get_interaction_descriptions() {
        return array(
	    'Speed' => 'Dice with both Berserk and Speed skills may ' .
	               'choose to make either kind of attack',
        );
    }
}
