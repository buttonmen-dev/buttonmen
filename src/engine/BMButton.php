<?php
/**
 * BMButton: Instantiated button as existing at the beginning of a round
 *
 * @author james
 */

/**
 * This class contains all the logic to do with instantiating and activating buttons
 *
 * @property      string  $name                  Name of button
 * @property      string  $originalRecipe        String representation of the original button recipe
 * @property      string  $recipe                String representation of the current button recipe
 * @property-read string  $artFilename           Filename in the image directory containing button art
 * @property-read array   $dieArray              Array of BMDie
 * @property      BMGame  $ownerObject           BMGame that owns the BMButton
 * @property      BMGame  $playerIdx             BMGame index of the player that owns the BMButton
 * @property      bool    $hasUnimplementedSkill Flag signalling if the recipe has an unimplemented skill
 * @property      bool    $hasAlteredRecipe      Flag signalling if the recipe has changed
 */
class BMButton extends BMCanHaveSkill {
    // properties

    /**
     * Button name
     *
     * @var string
     */
    protected $name;

    /**
     * String representation of the original button recipe
     *
     * @var string
     */
    protected $originalRecipe;

    /**
     * String representation of the current button recipe
     *
     * @var string
     */
    protected $recipe;


    /**
     * Filename in the image directory containing button art
     *
     * Note that this declaration is only needed so that get_filename()
     * triggers correctly in the getter. The property should not be used
     * directly.
     *
     * @var string
     */
    protected $artFilename;

    /**
     * Array of BMDie
     *
     * @var array
     */
    protected $dieArray;

    /**
     * An array of the skills of all the dice in dieArray.
     *
     * The keys are the names of the skills, and the values are the class names.
     *
     * @var array
     */
    protected $dieSkills;

    /**
     * An array of the die types (e.g., swing, option, twin) of all the dice in dieArray.
     *
     * The keys are the names of the die types, and the values are the class names.
     *
     * @var array
     */
    protected $dieTypes;

    /**
     * BMGame that owns this particular instance of the BMButton
     *
     * @var BMGame
     */
    protected $ownerObject;

    /**
     * BMGame index of the player that owns this particular instance of the
     * BMButton
     *
     * @var int
     */
    protected $playerIdx;

    /**
     * Flag signalling whether there is an unimplemented skill in the recipe,
     * which will prevent the dice from being loaded into the button
     *
     * @var bool
     */
    protected $hasUnimplementedSkill;

    /**
     * Flag signalling whether the current recipe of the button has changed
     * from the initial recipe, and thus whether it needs to be loaded from
     * and saved explicitly to the database
     *
     * @var bool
     */
    protected $hasAlteredRecipe;

    /**
     * Set button recipe, add button skill (if it exists), and add dice to button
     *
     * @param string $recipe
     * @param string $name
     * @param bool $isRecipeAltered
     * @param bool $doPropagateErrors
     * @param bool $doValidate
     */
    public function load(
        $recipe,
        $name = NULL,
        $isRecipeAltered = FALSE,
        $doPropagateErrors = FALSE,
        $doValidate = FALSE
    ) {
        if (!is_null($name)) {
            $this->name = $name;
            $standardName = preg_replace('/[^a-zA-Z0-9]/', '', $name);

            if (class_exists("BMBtnSkill$standardName")) {
                $this->add_skill($standardName);
            }
        }

        if ($doValidate) {
            $this->validate_recipe($recipe);
        }

        $this->recipe = $recipe;
        $this->dieArray = array();
        $this->dieSkills = array();
        $this->dieTypes = array();
        $this->hasUnimplementedSkill = FALSE;
        $this->hasAlteredRecipe = $isRecipeAltered;

        if (empty($recipe)) {
            return;
        }

        // split by spaces
        $dieRecipeArray = preg_split('/[[:space:]]+/', $recipe);

        // set die sides and skills, one die at a time
        foreach ($dieRecipeArray as $dieRecipe) {
            try {
                $die = BMDie::create_from_recipe($dieRecipe, $doPropagateErrors);
            } catch (BMExceptionUnimplementedDie $e) {
                $this->hasUnimplementedSkill = TRUE;
                continue;
            } catch (BMExceptionUnimplementedSkill $e) {
                $this->hasUnimplementedSkill = TRUE;
                continue;
            } catch (Exception $e) {
                if ($doPropagateErrors) {
                    throw $e;
                } else {
                    throw new InvalidArgumentException(
                        'Error loading die ' . $dieRecipe . ' for button ' . $name . '.'
                    );
                }
            }
            if (isset($this->ownerObject)) {
                $die->ownerObject = $this->ownerObject;
                $die->playerIdx = $this->playerIdx;
                $die->originalPlayerIdx = $this->playerIdx;
            }
            $this->dieArray[] = $die;
            if (is_null($die)) {
                $this->hasUnimplementedSkill = TRUE;
            } else {
                if (BMDie::unimplemented_skill_in_recipe($dieRecipe)) {
                    $this->hasUnimplementedSkill = TRUE;
                }
                $this->dieSkills += $die->skillList;
                $this->dieTypes += $die->getDieTypes();
            }
        }
    }

    /**
     * Discard button state and reload a fresh copy
     */
    public function reload() {
        $this->load($this->recipe, $this->name, $this->hasAlteredRecipe);
    }

    /**
     * Load values into the dice in dieArray
     *
     * @param array $valueArray
     */
    public function load_values(array $valueArray) {
        if (count($this->dieArray) != count($valueArray)) {
            throw new InvalidArgumentException('Invalid number of values.');
        }

        foreach ($valueArray as $dieIdx => $tempValue) {
            if (($tempValue < 1)
                | ($tempValue > $this->dieArray[$dieIdx]->max)
                ) {
                throw new InvalidArgumentException('Invalid values.');
            }
            $this->dieArray[$dieIdx]->value = $tempValue;
        }
    }

    /**
     * Add a die to dieArray
     *
     * @param BMDie $die
     */
    public function add_die($die) {
        $this->dieArray[] = $die;
    }

    /**
     * Check that a recipe is valid. If it is invalid, a
     * BMExceptionButtonRecipe is thrown.
     *
     * @param string $recipe
     */
    protected function validate_recipe($recipe) {
        if (empty($recipe)) {
            return;
        }

        if (strlen($recipe) >= 150) {
            throw new BMExceptionButtonRecipe(
                'Button recipes must be less than 150 characters long.'
            );
        }

        if (substr_count($recipe, '+') > 1) {
            throw new BMExceptionButtonRecipe(
                'Button recipes may include no more than one auxiliary die.'
            );
        }

        if (preg_match('/\+.* .*\(/', $recipe)) {
            throw new BMExceptionButtonRecipe(
                'Currently, any auxiliary die must be the last die in the recipe.'
            );
        }

        if (substr_count($recipe, '(') > 11) {
            throw new BMExceptionButtonRecipe(
                'Button recipes may include no more than eleven dice.'
            );
        }

        $this->validate_die_recipes($recipe);
        $this->validate_total_sides($recipe);
    }

    protected function validate_die_recipes($recipe) {
        $dieArray = preg_split(
            '/[[:space:]]+/',
            $recipe,
            NULL,
            PREG_SPLIT_NO_EMPTY
        );

        foreach ($dieArray as $dieIdx => $dieRecipe) {
            if ((0 === $dieIdx) && (FALSE !== strpos($dieRecipe, '+'))) {
                // see Issue #2636
                // Two issues are fixed with this:
                //   - Button containing only an aux die shouldn't be allowed
                //   - Internal error occurs when the first die is aux and a player declines aux
                throw new BMExceptionButtonRecipe(
                    'Currently, the first die cannot be an aux die.'
                );
            }

            // check for the following pattern:
            //   - possible non-parenthesis characters
            //   - open parenthesis
            //   - required non-parenthesis characters (which are captured in $matches[1])
            //   - close parenthesis
            //   - possible non-parenthesis characters
            $matches = array();
            $dieContainsSides = preg_match('/^[^\(\)]*\(([^\(\)]+)\)[^\(\)]*$/', $dieRecipe, $matches);
            if (1 !== $dieContainsSides) {
                throw new BMExceptionButtonRecipe(
                    'Invalid button recipe. ' .
                    'Check your syntax and make sure that each die recipe contains no whitespace.'
                );
            }

            $dieType = $matches[1];
            $this->validate_turbo_restrictions($dieRecipe, $dieType);

            if ((FALSE !== strpos($dieType, ',')) && (FALSE !== strpos($dieRecipe, 'f'))) {
                $subdice = explode(',', $dieType);
                foreach ($subdice as $subdie) {
                    if ('0' === $subdie) {
                        throw new BMExceptionButtonRecipe(
                            'Currently, we do not support focus twin dice where a subdie is zero.'
                        );
                    }
                }
            }
        }
    }

    protected function validate_turbo_restrictions($dieRecipe, $dieType) {
        // Issue #2676 deals with skills that are incompatible with turbo
        if (FALSE !== strpos($dieRecipe, '!')) {
            if (FALSE !== strpos($dieRecipe, 'm')) {
                throw new BMExceptionButtonRecipe(
                    'Turbo may not be combined with morphing.'
                );
            }

            if (FALSE !== strpos($dieRecipe, 'D')) {
                throw new BMExceptionButtonRecipe(
                    'Turbo may not be combined with doppelganger.'
                );
            }

            if (FALSE !== strpos($dieRecipe, '?')) {
                throw new BMExceptionButtonRecipe(
                    'Turbo may not be combined with mood.'
                );
            }

            if (FALSE !== strpos($dieRecipe, '&')) {
                throw new BMExceptionButtonRecipe(
                    'Turbo may not be combined with mad.'
                );
            }

            if (FALSE !== strpos($dieRecipe, 'k')) {
                throw new BMExceptionButtonRecipe(
                    'Turbo may not be combined with konstant.'
                );
            }

            if (is_numeric(str_replace(',', '', $dieType))) {
                if (FALSE !== strpos($dieRecipe, 'H')) {
                    throw new BMExceptionButtonRecipe(
                        'A static turbo die may not have mighty.'
                    );
                }

                if (FALSE !== strpos($dieRecipe, 'h')) {
                    throw new BMExceptionButtonRecipe(
                        'A static turbo die may not have weak.'
                    );
                }

                if (FALSE !== strpos($dieRecipe, 'B')) {
                    throw new BMExceptionButtonRecipe(
                        'A static turbo die may not have berserk.'
                    );
                }
            }
        }
    }

    /**
     * Check that a recipe does not allow too many sides, which might overwhelm our server when
     * the hit table is being calculated. If it is invalid, a BMExceptionButtonRecipe is thrown.
     *
     * @param string $recipe
     */
    protected function validate_total_sides($recipe) {
        preg_match_all('/\(([^\(\)]*)\)/', $recipe, $matchArray);

        $parenthesisContentsArray = $matchArray[1];

        $totalSideCount = 0;

        foreach ($parenthesisContentsArray as $parenthesisContents) {
            if (FALSE !== strpos($parenthesisContents, ',')) {
                $subdieArray = explode(',', $parenthesisContents);
                foreach ($subdieArray as $subdie) {
                    $totalSideCount += $this->max_side_count($subdie);
                }
            } elseif (FALSE !== strpos($parenthesisContents, '/')) {
                $optionArray = explode('/', $parenthesisContents);
                $maxVal = 0;
                foreach ($optionArray as $option) {
                    $maxVal = max($maxVal, $this->max_side_count($option));
                }
                $totalSideCount += $maxVal;
            } else {
                $totalSideCount += $this->max_side_count($parenthesisContents);
            }
        }

        if ($totalSideCount > 200) {
            throw new BMExceptionButtonRecipe(
                'Currently, a recipe cannot exceed a total of 200 sides.'
            );
        }
    }

    protected function max_side_count($die) {
        if (is_numeric($die)) {
            return $die;
        } elseif (array_key_exists($die, BMDieSwing::$swingRanges)) {
            return BMDieSwing::$swingRanges[$die][1];
        } elseif ('C' === $die) {
            return 20;
        } else {
            throw new BMExceptionButtonRecipe(
                'Cannot determine max side count of this die type.'
            );
        }
    }

    /**
     * Activate all dice in dieArray. This causes clones of the dice to be
     * added to their ownerObjects. Since their ownerObjects should be the
     * BMGame that owns the button, this effectively adds dice to the game.
     *
     * @param bool $forceSwingRequest
     */
    public function activate($forceSwingRequest = FALSE) {
        foreach ($this->dieArray as $die) {
            $die->activate($forceSwingRequest);
        }
    }

    /**
     * Change the button recipe
     */
    public function update_button_recipe() {
        $recipe = '';

        $playerIdx = array_search($this, $this->ownerObject->buttonArray, TRUE);
        if (FALSE === $playerIdx) {
            return;
        }

        foreach ($this->ownerObject->playerArray[$playerIdx]->activeDieArray as $die) {
            $recipe .= ' ' . $die->recipe;
        }

        $recipe = ltrim($recipe);

        if ($this->recipe != $recipe) {
            $this->recipe = $recipe;
            $this->hasAlteredRecipe = TRUE;
        }
    }

    /**
     * Get the filename of the button art
     *
     * @return string
     */
    protected function get_artFilename() {
        $artFilename = preg_replace('/[^a-z0-9]/', '', strtolower($this->name)) . '.png';
        $artFilepath = BW_PHP_ROOT . '/ui/images/button/' . $artFilename;
        if (file_exists($artFilepath)) {
            return $artFilename;
        } else {
            return 'BMdefaultRound.png';
        }
    }

    // utility methods
    /**
     * Getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        if (property_exists($this, $property)) {
            switch ($property) {
                case 'artFilename':
                    return $this->get_artFilename();
                default:
                    return $this->$property;
            }
        }
    }

    /**
     * Setter
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) {
        switch ($property) {
            case 'recipe':
                $this->load($value, $this->name, $this->hasAlteredRecipe);
                break;

            case 'dieArray':
                $this->dieArray = $value;
                foreach ($this->dieArray as $die) {
                    if (isset($this->ownerObject)) {
                        $die->ownerObject = $this->ownerObject;
                    }
                    if (isset($this->playerIdx)) {
                        $die->playerIdx = $this->playerIdx;
                        $die->originalPlayerIdx = $this->playerIdx;
                    }
                }
                break;

            case 'ownerObject':
                $this->ownerObject = $value;
                if (isset($this->dieArray)) {
                    foreach ($this->dieArray as $die) {
                        $die->ownerObject = $this->ownerObject;
                    }
                }
                break;

            case 'playerIdx':
                $this->playerIdx = $value;
                if (isset($this->dieArray)) {
                    foreach ($this->dieArray as $die) {
                        $die->playerIdx = $this->playerIdx;
                        $die->originalPlayerIdx = $this->playerIdx;
                    }
                }
                break;

            default:
                $this->$property = $value;
        }
    }

    /**
     * Define behaviour of isset()
     *
     * @param string $property
     * @return bool
     */
    public function __isset($property) {
        return isset($this->$property);
    }

    /**
     * Define behaviour of unset()
     *
     * @param string $property
     * @return bool
     */
    public function __unset($property) {
        if (isset($this->$property)) {
            $this->$property = NULL;
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
