<?php
/**
 * BMBtnSkill: Used to modify the operation of BMButton
 *
 * @author: james
 */

/**
 * This class is the parent class for all button skills
 */
class BMBtnSkill {
    /**
     * Description of skill
     *
     * Each skill class must override this with a meaningful
     * description of how the skill works
     *
     * @return string
     */
    public static function get_description() {
        return NULL;
    }
}
