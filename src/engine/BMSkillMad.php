<?php
/**
 * BMSkillMad: Code specific to the mad die skill
 *
 * @author james
 */

/**
 * This class contains code specific to the mad die skill
 */
class BMSkillMad extends BMSkillMood {

    public static function valid_die_sizes(array $swingRange) {
        $dieSizeArray = range($swingRange[0], $swingRange[1]);

        foreach ($dieSizeArray as $sizeIdx => $size) {
            // remove odd values
            if ($size & 1) {
                unset($dieSizeArray[$sizeIdx]);
            }
        }

        return array_values($dieSizeArray);
    }

    public static function add_skill($args) {
        if (!is_array($args) ||
            !($args['die'] instanceof BMDie)) {
            return;
        }
    }

    protected static function get_description() {
        return 'These are a subcategory of Swing dice, whose size ' .
               'changes randomly when rerolled. At the very start of the ' .
               'game (and again after any round they lose, just as with ' .
               'normal Swing dice) the player sets the initial size of Mad ' .
               'Swing dice, but from then on whenever they are rolled their ' .
               'size is set randomly to any even-numbered legal size for ' .
               'that Swing type. The initial size of a Mad Swing die may ' .
               'be set to an odd number.';
    }

    protected static function get_interaction_descriptions() {
        return array(
            'Ornery' => 'Dice with both Ornery and Mad Swing have their sizes randomized during ornery rerolls',
        );
    }
}
