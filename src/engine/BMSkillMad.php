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

    /**
     * Gives all valid die sizes in the swing range that are valid for mad swing
     *
     * @param array $swingRange
     * @return array
     */
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

    /**
     * Description of skill
     *
     * @return string
     */
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
            'Ornery' => 'Dice with both Ornery and Mad Swing have their sizes randomized during ornery rerolls',
            'Radioactive' => 'Dice with the Mad skill lose Mad when they decay due to Radioactive',
        );
    }
}
