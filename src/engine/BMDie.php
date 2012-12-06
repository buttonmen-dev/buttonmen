<?php
require_once 'BMSkill.php';

class BMSKill
{
	
}

class BMSkillTest extends BMSkill
{
	public static function test($args)
	{
		echo "foo?";
	}
}


class BMDie
{
    // properties

# an array keyed by function name. Value is an array of the skills
#  that are modifying that function
	private $hookLists;

# Names of the skills that the die has.
	private $skillList;

    private $mRecipe;
    private $mSides;
    private $mSkills;

    // methods

# Run the skill hooks for a given function. $args is an array of the
#  function arguments. The array itself is passed by value. The
#  contents of that array will often be references.

	private function run_hooks($func, $args)
	{
		# get the hooks for the calling function

		foreach ($this->hookLists[$func] as $skill)
		{

			$skillClass = "BMSkill$skill";

			$skillClass::$func($args);
		}
	}

	public function add_skill($skill)
	{

	}



    public function __get($property)
    {
        if (property_exists($this, $property)) {
            switch ($property) {
                case '$mRecipe':
                    return ($this->mSkills).($this->mSides);

                default:
                    return $this->$property;
            }
        }
    }

    public function __set($property, $value)
    {
        switch ($property) {
            case 'mSides':
                // require a positive integer number of sides
                if (filter_var($value,
                               FILTER_VALIDATE_INT,
                               array("options"=>array("min_range"=>1)))) {
                    $this->mSides = $value;
                } else {
                    throw new InvalidArgumentException("Invalid number of sides.");
                }
                break;

            case 'mRecipe':
                throw new Exception("Cannot set recipe directly.");
                break;

            default:
                $this->$property = $value;
        }
    }

    public function __toString()
    {
        print($this->mRecipe);
    }
}


?>
