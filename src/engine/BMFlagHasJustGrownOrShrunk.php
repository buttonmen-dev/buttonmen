<?php
/**
 * BMFlagHasJustGrownOrShrunk: Used to signal that a die has just grown or shrunk
 *
 * @author: james
 */

/**
 * This class is a flag that signals that a die has just grown or shrunk.
 * It stores the initial die recipe for logging purposes.
 */
class BMFlagHasJustGrownOrShrunk extends BMFlag {
    /**
     * Initial die recipe before changing size, stored in flag
     *
     * @var string
     */
    protected $preChangeRecipe;

    /**
     * Value stored in flag
     *
     * @return mixed
     */
    public function value() {
        return $this->preChangeRecipe;
    }

    /**
     * Constructor
     *
     * @param string $recipe
     */
    public function __construct($recipe) {
        $this->preChangeRecipe = NULL;
        if (isset($recipe)) {
            $this->preChangeRecipe = $recipe;
        }
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function __toString() {
        $string = $this->type();
        if (!empty($this->preChangeRecipe)) {
            $string .= '__' . $this->preChangeRecipe;
        }

        return $string;
    }
}
