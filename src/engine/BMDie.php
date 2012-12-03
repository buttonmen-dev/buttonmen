<?php
class BMDie
{
    // properties
    private $mSides;
    private $mRecipe;

    // methods
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            switch ($property) {
                case '$mSides':
                    return getSidesFromRecipe($this->mRecipe);
                    break;

                default:
                    return $property;
                    break;
            }

            return $this->$property;
        }

    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            switch ($property) {
                case '$mSides':
                    throw new Exception("Cannot set number of sides directly.");
                    break;

                case '$mRecipe':
                    if (isValidRecipe($value)) {
                        $this->mRecipe = $value;
                    } else {
                        throw new Exception("Invalid recipe.");
                    }
                    break;

                default:
                    $this->$property = $value;
                    break;
            }
        }
    }

    public function __toString()
    {
        print($this->mRecipe);
    }

    public function isValidRecipe($recipe)
    {
        return true;
    }

    public function getSidesFromRecipe($recipe)
    {
        return 0;
    }
}
?>
