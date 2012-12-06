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

# Basic facts about the die
	public $min;
	public $max;

	private $scoreValue;

    private $mRecipe;
    private $mSides;
    private $mSkills;

	private $doesReroll = true;
	public $captured = false;

# This is set when the button may not attack (sleep or focus, for instance)
# It is set to a string, so the cause may be described. It is cleared at
# the end of each of your turns.
	private $inactive = "";

# Set when the button isn't in the game for whatever reason, but could
#  suddenly join (Warrior Dice)
	private $unavailable = false;

    // unhooked methods

# Run the skill hooks for a given function. $args is an array of
#  argumentsfor the function. The array itself is passed by value. The
#  contents of that array will often be references.
#
# By using a static method call, the skill hook methods can use $this
#  to refer to the die that called them.

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

# This one may need to be hookable. So might add_skill, depending on
#  how Chaotic shakes out.
	public function remove_skill($skill)
	{

	}

	public function has_skill($skill)
	{

	}

# This needs to be fixed to work properly within PHP's magic method semantics
#
# will need an init_from_db method, too (eventually)
	public function init($sides, $skills)
	{
		$this->min = 1;
		$this->max = $sides;

		$this->scoreValue = $sides;

		foreach ($skills as $s)
		{
			$this->add_skill($s);
		}
	}


	// hooked methods

# When a die is "woken up" from its container to be used in a
#  game. Does not roll the die

	public function activate()
	{
		$this->run_hooks(__METHOD__, array());
	}

# Roll the die into a game. Clone self, (possibly into the playDie
# subclass), roll, return the clone.
	public function first_roll()
	{
		$this->run_hooks(__METHOD__, array());
	}


	public function roll($successfulAttack)
	{
		$this->run_hooks(__METHOD__, array($successfulAttack));
	}

	public function attack_list()
	{
		$list = array("Power", "Skill");

		$this->run_hooks(__METHOD__, array(&$list));

		return $list;
	}


	public function attack_values($type)
	{
		$attackValueList = array($this->value);

		$this->run_hooks(__METHOD__, array($type, &$attackValueList));
	}

	public function defense_value($type)
	{
		$this->run_hooks(__METHOD__, array($type));
	}

	public function get_scoreValue()
	{
		$this->run_hooks(__METHOD__, array());
	}

	public function initiative_value()
	{
		$this->run_hooks(__METHOD__, array());
	}


	public function validAttack($type, $attackers, $defenders)
	{
		$this->run_hooks(__METHOD__, array());
	}


	public function validTarget($type, $attackers, $defenders)
	{
		$this->run_hooks(__METHOD__, array());
	}

	public function capture($type, $attackers, $victims)
	{
		$this->run_hooks(__METHOD__, array());
	}


	public function beCaptured($type, $attackers, $victims)
	{
		$this->run_hooks(__METHOD__, array());
	}

# Print long description
	public function describe()
	{
		$this->run_hooks(__METHOD__, array());
	}

	public function split()
	{
		$this->run_hooks(__METHOD__, array());
	}

	public function start_turn($player)
	{
		$this->run_hooks(__METHOD__, array());
	}

	public function end_turn($player)
	{
		$this->run_hooks(__METHOD__, array());
	}

	public function start_round($player)
	{
		$this->run_hooks(__METHOD__, array());
	}

	public function end_round($player)
	{
		$this->run_hooks(__METHOD__, array());
	}


	// utility methods

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
