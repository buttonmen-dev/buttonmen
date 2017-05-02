<?php
/**
 * BMFlagHasJustBeenMoody: Used to signal that a die has been subject to mood/mad
 *
 * @author: james
 */

/**
 * This class is a flag that signals that a die has potentially changed size
 * due to mood or mad.
 * It stores the initial die recipe for logging purposes.
 */
class BMFlagHasJustBeenMoody extends BMFlag {
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
