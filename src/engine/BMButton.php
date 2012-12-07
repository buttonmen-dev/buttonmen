<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BMButton
 *
 * @author james
 */
class BMButton {
    // properties
    public $recipe;
    public $dieArray;
    // three lists of dice

    // methods
    public function load_from_recipe($recipe) {
        $dieSides = $this->parseRecipeForSides($recipe);
        unset($this->dieArray);
        for ($dieIdx = 0; $dieIdx <= (count($dieSides) - 1); $dieIdx++) {
            $tempBMDie = new BMDie;
            $tempBMDie->mSides = $dieSides[$dieIdx];
            $this->dieArray[] = $tempBMDie;
        }
    }

    private function parseRecipeForSides($recipe) {
        $dieSizeArray = preg_split('/[^[:digit:]]+/', $recipe);

        return $dieSizeArray;
    }

    // create dice

    // load die values
}

?>
