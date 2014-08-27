<?php
/**
 * BMBtnSkillEcho: Code specific to Echo
 *
 * @author: james
 */

/**
 * This class currently supports the special skills of Echo
 */
class BMBtnSkillEcho extends BMBtnSkill {
    public static $hooked_methods = array('load_buttons');

    public static function load_buttons(array $args) {
        if (
            !array_key_exists('name', $args) ||
            !array_key_exists('recipe', $args) ||
            !array_key_exists('oppname', $args) ||
            !array_key_exists('opprecipe', $args)
        ) {
            throw new LogicException('load_buttons die hook is missing required input arguments');
        }

        if (empty($args['name'])) {
            throw new LogicException('Button name may not be empty.');
        }

        // don't do any recipe copying until the opponent button exists
        if (empty($args['oppname'])) {
            return;
        }

        $newRecipe = $args['recipe'];

        // copy opponent's recipe only if Echo hasn't yet got a recipe
        if ('' == $args['recipe']) {
            if ('' == $args['opprecipe']) {
                // opponent's button recipe is empty, which only occurs with Echo and Zero
                // thus choose a default recipe
                $newRecipe = '(4) (4) (10) (12) (X)';
            } else {
                $newRecipe = $args['opprecipe'];
            }
        }

        return array('recipe' => $newRecipe);
    }
}
