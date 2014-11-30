<?php
/**
 * BMBtnSkillRandomBM: Code specific to RandomBM
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of Echo
 */
class BMBtnSkillRandomBM extends BMBtnSkill {
    public static $hooked_methods = array('specify_recipes');

    public static function specify_recipes(array $args) {

    }
}
