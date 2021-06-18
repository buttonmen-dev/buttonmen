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
     * Constructor
     *
     * @param array $dice
     */
    public function __construct($dice, $maxHitValue = PHP_INT_MAX) {
        // For building hash keys, every die needs a unique
        // identifier, no matter how many there are, but if there are
        // more than 36 dice, something is very, very wrong.
        $ids = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789");
        for ($i = 0; $i < count($dice); $i++) {
            $die = $dice[$i];
            $die_id = $ids[$i];

            $this->dice[] = $die;

            foreach (array_keys($this->hits) as $target) {
                foreach (array_keys($this->hits[$target]) as $key) {
                    // We've already been used in this combo
                    if (FALSE !== strpos($key, $die_id)) {
                        continue;
                    }

                    foreach ($die->attack_values("Skill") as $val) {
                        $newcombo = $this->hits[$target][$key];
                        $newcombo[] = $die;
                        // the new key will always be sorted, since we
                        // process the dice in order
                        $newkey = $key.$die_id;
                        $newtarget = $target + $val;
                        if ($newtarget > $maxHitValue) {
                            continue;
                        }
                        if (array_key_exists($newtarget, $this->hits)) {
                            // If the same die combo makes a number
                            // two ways, we just overwrite the old
                            // entry.
                            $this->hits[$newtarget][$newkey] = $newcombo;
                        } else {
                            $this->hits[$newtarget] = array($newkey => $newcombo);
                        }
                    }
                }
            }

            // Add the unique values the die may provide
            foreach ($die->attack_values("Skill") as $val) {
                if ($val > $maxHitValue) {
                    continue;
                }
                if (array_key_exists($val, $this->hits)) {
                    $this->hits[$val][$die_id] = array($die);
                } else {
                    $this->hits[$val] = array($die_id => array($die));
                }
            }
        }

        foreach ($dice as $dieIdx => $die) {
            $die->run_hooks('hit_table', array('hits' => &$this->hits,
                                               'dieLetter' => $ids[$dieIdx]));
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
