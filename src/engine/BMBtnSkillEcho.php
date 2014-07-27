<?php
/*
 * BMBtnSkillEcho: Code specific to Echo
 *
 * @author: james
 */

/* This class currently supports the special skills of Echo
 */
class BMBtnSkillEcho extends BMBtnSkill {
    public static $hooked_methods = array('load_buttons');

    public static function load_buttons($args) {
        $newRecipe = $args['recipe'];

        if ('' == $args['recipe']) {
            if ('' == $args['opprecipe']) {
                $newRecipe = '(4) (4) (10) (12) (X)';
            } else {
                $newRecipe = $args['opprecipe'];
            }
        }

        return array('recipe' => $newRecipe);
    }
}
