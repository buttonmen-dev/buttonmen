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
    public static function capture(&$args) {
        self::radioactive_split($args);
    }

    /**
     * Hooked method applied when a die is being captured
     *
     * @param array $args
     */
    public static function be_captured(&$args) {
        self::radioactive_split($args);
    }

    /**
     * Hooked method applied when a die splits due to radioactive
     *
     * @param array $args
     */
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

        if (!self::has_single_defender($args['defenders'])) {
            return;
        }

        $attacker = &$args['attackers'][0];

        if ($attacker->outOfPlay) {
            return;
        }

        $game = $attacker->ownerObject;
        $activeDieArrayArray = $game->activeDieArrayArray;

        $attacker->remove_skill('Radioactive');
        $attacker->remove_skill('Turbo');
        $attacker->remove_skill('Mood');
        $attacker->remove_skill('Mad');
        $attacker->remove_skill('Jolt');
        $attacker->remove_skill('TimeAndSpace');

        $newAttackerDieArray = $attacker->split();

        array_splice(
            $activeDieArrayArray[$attacker->playerIdx],
            $attacker->activeDieIdx,
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

    /**
     * Description of skill
     *
     * @return string
     */
    protected static function get_description() {
        return 'If a radioactive die is either the attacking die or the target die in an attack with a ' .
               'single attacking die and a single target die, the attacking die splits, or "decays", ' .
               'into two as-close-to-equal-sized-as-possible dice that add up to its original size. All ' .
               'dice that decay lose the following skills: Radioactive (%), Turbo (!), ' .
               'Mad Swing (&), Mood ' .
               'Swing (?), Time and Space (^), [and, not yet implemented: Jolt (J)]. For example, ' .
               'a s(X=15)! (Shadow Turbo X Swing with 15 sides) that shadow attacked a radioactive die ' .
               'would decay into a s(X=7) die and a s(X=8) die, losing the turbo skill. A %p(7,13) on a ' .
               'power attack would decay into a p(3,7) and a p(4,6), losing the radioactive skill.';
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
            'Berserk' => 'Dice with both Radioactive and Berserk skills making a berserk attack ' .
                         'targeting a SINGLE die are first replaced with non-berserk dice with half ' .
                         'their previous number of sides, rounding up, and then decay',
            'Doppelganger' => 'Dice with both Radioactive and Doppelganger first decay, then ' .
                              'each of the "decay products" are replaced by exact copies of the ' .
                              'die they captured',
            'Mad' => 'Dice with the Mad skill lose Mad when they decay due to Radioactive',
            'Mood' => 'Dice with the Mood skill lose Mood when they decay due to Radioactive',
            'Morphing' => 'Dice with both Radioactive and Morphing skills first morph into the ' .
                          'size of the captured die, and then decay',
            'TimeAndSpace' => 'Dice with the TimeAndSpace skill lose TimeAndSpace ' .
                              'when they decay due to Radioactive',
            'Turbo' => 'Dice with the Turbo skill lose Turbo when they decay due to Radioactive',
        );
    }
}
