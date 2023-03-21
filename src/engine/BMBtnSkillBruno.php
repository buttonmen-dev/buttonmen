<?php
/**
 * BMBtnSkillPappy: Code specific to Bruno
 *
 */

/**
 * This class currently supports the special skills of Bruno.
 *
 */
class BMBtnSkillBruno extends BMBtnSkill {
    /**
     * An array containing the names of functions run by
     * BMCanHaveSkill->run_hooks()
     *
     * @var array
     */
    public static $hooked_methods = array('specify_recipes');

    /**
     * Hooked method applied when specifying recipes
     *
     * @param array $args
     */
    public static function specify_recipes(array $args) {
        $areAllArgsPresent =
            array_key_exists('button', $args) &&
            array_key_exists('oppbutton', $args);

        if (!$areAllArgsPresent) {
            throw new LogicException('specify_recipes die hook is missing required input arguments');
        }

        $button = $args['button'];
        $oppbutton = $args['oppbutton'];

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

        // check if opponent is Pappy, only alter recipe if that is the case
        if ('Pappy' == $oppbutton->name) {
                //  Add the new die to Bruno's recipe
                $newRecipe = $button->recipe . ' (X)';
        }
       

        $button->recipe = $newRecipe;
        $button->hasAlteredRecipe = TRUE;
    }

    /**
     * Description of skill
     *
     * @return string
     */
    public static function get_description() {
        return 'Bruno gets an extra die, a (X), if his opponent is Pappy.';
    }
}
