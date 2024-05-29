<?php
/**
 * BMBtnSkillPappy: Code specific to Pappy
 *
 */

/**
 * This class currently supports the special skills of Pappy.
 *
 */
class BMBtnSkillPappy extends BMBtnSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('after_specify_recipes');

    /**
     * Hooked method applied after specifying recipes
     *
     * @param array $args
     */
    public static function after_specify_recipes(array $args) {
        $areAllArgsPresent =
            array_key_exists('button', $args) &&
            array_key_exists('oppbutton', $args) &&
            array_key_exists('game', $args);

        if (!$areAllArgsPresent) {
            throw new LogicException('after_specify_recipes die hook is missing required input arguments');
        }

        $button = $args['button'];
        $oppbutton = $args['oppbutton'];
        $game = $args['game'];

        if (is_null($oppbutton)) {
            return;
        }

        if (!($button instanceof BMButton) ||
            !($oppbutton instanceof BMButton)) {
            throw new LogicException('specify_recipes requires two BMButton input arguments');
        }

        if (empty($button->name)) {
            throw new LogicException('Button name may not be empty.');
        }

        // check if opponent is Bruno, only alter recipe if that is the case
        if ('Bruno' == $oppbutton->name) {
            $oldRecipe = $button->recipe;
            //  Add the new die to Pappy's recipe
            $newRecipe = $button->recipe . ' B(X)';
            $game->log_action(
                'button_recipe_change',
                0,
                array(
                    'buttonName' => $button->name,
                    'preInfo' => array('recipe' => $oldRecipe),
                    'postInfo' => array('recipe' => $newRecipe),
                    'reason' => ' because of Pappy\'s button special against Bruno'
                )
            );

            $button->recipe = $newRecipe;
            $button->hasAlteredRecipe = TRUE;
        }
    }

    /**
     * Description of skill
     *
     * @return string
     */
    public static function get_description() {
        return 'Pappy gets an extra die, a B(X), if his opponent is Bruno.';
    }
}
