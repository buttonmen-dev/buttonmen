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

    /**
     * Complete description of skill, packaged for front end
     *
     * @param string $skill
     * @param bool $includeEmpty
     * @return array|NULL
     */
    public static function describe($skill, $includeEmpty = TRUE) {
        $skillClass = "BMBtnSkill$skill";
        $description = $skillClass::get_description();

        if (!$includeEmpty) {
            if (empty($description)) {
                return NULL;
            }

            $skillInstance = new $skillClass;
            if ($skillInstance instanceof BMBtnSkillArtificialReenable) {
                return NULL;
            }
        }

        $skillDescription = array(
            'code' => '',
            'description' => $description,
            'interacts' => array(),
        );

        return $skillDescription;
    }
}
