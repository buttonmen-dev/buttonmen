<?php
/**
 * BMUtilityHitTable: utility class used for constructing hit tables
 *
 * Hit tables are constructed as follows:
 *
 * - For each die, get a list of its values.
 * - For each value that was in the table, add each of the die’s values to that
 *   value, and make a new table entry for the new value (or add the new combo
 *   to a preexisting entry).
 * - Add each of the die’s values to the table. If the value already exists,
 *   add the die to the list of ways the value can be constructed.
 *
 * @author Julian
 */

/**
 * Class to hold and search all the possible combinations that a skill
 * attack could hit
 *
 * It's huge and complicated, but hopefully better than a naive search.
 */
class BMUtilityHitTable {
    /**
     * Initial array of dice used to form combinations.
     *
     * @var array
     */
    private $dice = array();

    /**
     * $hits is an array keyed by numbers. Values is an array, keyed
     * by the combined unique ids of the sets of dice used to make the value
     *
     * So, if 4 can be made with A and B or C and D,
     * $hits[4] = [ AB => [ dieA, dieB ], CD => [ dieC, dieD ] ]
     *
     * To save on memory when there are many dice present, the hit table is
     * restricted to only include target values up to $maxHitValue.
     *
     * @var array
     */
    private $hits = array();

    /**
     * $die_ids is an array of possible unique ids to use in constructing hits
     *
     * @var array
     */
    private $die_ids = array();

    /**
     * $hitVals is the array of target hit values used to construct this hit table
     *
     * @var array
     */
    private $hitVals = array();

    /**
     * Constructor
     *
     * @param array $dice
     */
    public function __construct($dice, $hitVals = NULL) {
        $this->dice = $dice;
        $this->hitVals = $hitVals;
        $this->die_ids = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789");
        $this->add_dice_to_hit_table($dice, array());

        foreach ($this->dice as $dieIdx => $die) {
            $die->run_hooks('hit_table', array('hits' => &$this->hits,
                                               'dieLetter' => $this->die_ids[$dieIdx]));
        }
    }

    /**
     * Recursively add attacking dice to the hit table
     *
     * Every time this function is called, it moves one die from $diceRemaining (the set of to-be-processed dice)
     * to $prefixDieHits (the set of hits selected from dice).  When processing the die, the function looks at all
     * possible values that die can contribute to an attack, either as an attacker or as a helper, and recursively
     * calls itself once for each of those choices.
     *
     * When $diceRemaining is empty and all dice have had values selected for $prefixDieHits, the function
     * determines the total target value of the hit represented by this combination of dice.  It then checks to
     * see if that total is in $this->hitVals, the set of all target (defender) die values which this hit table
     * instantiation is trying to hit.  If the target value is one of the ones this hit table is looking for,
     * we save the combo of attacking die values participating in the attack (discarding helpers; they can and will
     * be rediscovered later in the process of the actual attack) in $this->hits.  If the target value isn't one
     * this hit table is looking for, we discard the combo to avoid using a huge amount of memory to store data
     * about irrelevant attacks.
     *
     * @param int $target
     * @return bool
     */
    protected function add_dice_to_hit_table($diceRemaining, $prefixDieHits) {
        // There are dice remaining to process; process the next one, and recursively call this function
        if (count($diceRemaining) > 0) {
            $prefixDieHitsCopy = array();
            foreach ($prefixDieHits as $prefixDieHit) {
                $prefixDieHitsCopy[] = $prefixDieHit;
            }
            $firstDie = NULL;
            $diceRemainingCopy = array();
            foreach ($diceRemaining as $dieRemaining) {
                if (is_null($firstDie)) {
                    $firstDie = $dieRemaining;
                } else {
                    $diceRemainingCopy[] = $dieRemaining;
                }
            }
            $prefixLength = count($prefixDieHitsCopy);
            $prefixDieHitsCopy[] = NULL;
            $possibleHits = $firstDie->attack_values("Skill");
            // Values which can be contributed if this die participates in an attack
            foreach ($possibleHits as $firstDieHit) {
                $prefixDieHitsCopy[$prefixLength] = array($firstDieHit, TRUE);
                $this->add_dice_to_hit_table($diceRemainingCopy, $prefixDieHitsCopy);
            }
            // Values which can be contributed if this die does not participate in an attack
            $possibleAssists = $firstDie->assist_values("Skill", array(), array());
            $possibleAssists[] = 0;
            foreach ($possibleAssists as $firstDieAssist) {
                $prefixDieHitsCopy[$prefixLength] = array($firstDieAssist, FALSE);
                $this->add_dice_to_hit_table($diceRemainingCopy, $prefixDieHitsCopy);
            }
            return;
        }

        // There are no dice remaining to process; add this combo to the hit table
        assert(count($prefixDieHits) == count($this->dice));
        $target = 0;
        foreach ($prefixDieHits as $dieHit) {
            $target += $dieHit[0];
        }
        if (isset($this->hitVals) && !(in_array($target, $this->hitVals))) {
            // This combo's target sum isn't needed for the hit table
            return;
        }

        // The logic up to this point collected both direct and indirect die contributions,
        // for the purpose of finding exactly those combinations which could under some circumstances
        // hit a target of interest.  However, we want to install into the hit table the
        // target sum from the direct contributions only, because that's what find_attack() will expect
        $newcombo = array();
        $newkeyParts = array();
        $directTarget = 0;
        for ($i = 0; $i < count($prefixDieHits); $i++) {
            if ($prefixDieHits[$i][1]) {
                $directTarget += $prefixDieHits[$i][0];
                $newcombo[] = $this->dice[$i];
                $newkeyParts[] = $this->die_ids[$i];
            }
        }
        if (count($newcombo) == 0) {
            // This attack contains zero dice
            return;
        }

        // Actually add the combo to the hit table, either as a new key or a new combo for an existing one
        $newkey = implode(".", $newkeyParts);
        if (array_key_exists($directTarget, $this->hits)) {
            $this->hits[$directTarget][$newkey] = $newcombo;
        } else {
            $this->hits[$directTarget] = array($newkey => $newcombo);
        }
    }

    /**
     * Test for a hit. Return all possible sets of dice that can make that hit.
     *
     * @param int $target
     * @return bool
     */
    public function find_hit($target) {
        if (array_key_exists($target, $this->hits)) {
            return array_values($this->hits[$target]);
        }
        return FALSE;
    }

    /**
     * Return a list of all possible hits.
     *
     * @return array
     */
    public function list_hits() {
        return array_keys($this->hits);
    }

    /**
     * Getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        return $this->$property;
    }

    /**
     * Setter
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value) {
        throw new LogicException(
            "BMUtilityHitTable->$property cannot be set (attempting to set value $value)."
        );
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
}
