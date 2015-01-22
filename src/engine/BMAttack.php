<?php
/**
 * BMAttack: attack validation and committal code
 *
 * @author Julian
 */

/**
 * This class is the parent class for all attack types
 */
abstract class BMAttack {
    protected static $instance = array();

    // True for attacks that do something besides simple capture,
    // because the player may have to choose which attack type to
    // use. Captures are indistinguishable among attacks with no
    // side effects
    public $sideEffect = FALSE;

    public $type;

    public $validationMessage = '';

    // Dice that effect or affect this attack
    protected $validDice = array();

    /**
     * Constructor
     *
     * This is private, thus disabled, since this is a Singleton.
     */
    private function __construct() {
    }

    public static function get_instance($type = NULL) {
        if ($type) {
            $cname = "BMAttack" . ucfirst(strtolower($type));
            if (class_exists($cname)) {
                return $cname::get_instance();
            } else {
                return NULL;
            }
        }

        $class = get_called_class();
        if (!isset(static::$instance[$class])) {
            static::$instance[$class] = new $class;
        }
        static::$instance[$class]->validDice = array();

        if (!empty(static::$instance[$class]->resolvedType)) {
            static::$instance[$class]->resolvedType = '';
        }

        return static::$instance[$class];
    }

    public static function possible_attack_types(array $attackers) {
        $allAttackTypesArray = array();

        foreach ($attackers as $attacker) {
            $attackTypeArray = array();
            $attackTypeArray['Power'] = 'Power';
            $attackTypeArray['Skill'] = 'Skill';
            $attacker->run_hooks(
                'attack_list',
                array('attackTypeArray' => &$attackTypeArray,
                      'value' => (int)$attacker->value,
                      'nAttDice' => (int)count($attackers))
            );

            foreach ($attackTypeArray as $attackType) {
                $allAttackTypesArray[$attackType] = $attackType;
            }
        }

        uksort($allAttackTypesArray, 'BMAttack::display_cmp');

        // james: deliberately ignore Default and Surrender attacks here,
        //        so that they do not appear in the list of attack types

        return $allAttackTypesArray;
    }

    protected static function display_cmp($str1, $str2) {
        if ($str1 == $str2) {
            return 0;
        }

        // force Power attacks to be displayed first
        if ('Power' == $str1) {
            return -1;
        } elseif ('Power' == $str2) {
            return 1;
        }

        // force Skill attacks to be displayed first, except for Power
        if ('Skill' == $str1) {
            return -1;
        } elseif ('Skill' == $str2) {
            return 1;
        }

        return strcasecmp($str1, $str2);
    }

    public function add_die(BMDie $die) {
        // need to search with strict on to avoid identical-valued
        // objects matching
        if (!in_array($die, $this->validDice, TRUE)) {
            if (is_array($die->skillList)) {
                foreach ($die->skillList as $skill) {
                    if (FALSE !== array_search($this->type, $skill::incompatible_attack_types())) {
                        return;
                    }
                }
            }
            $this->validDice[] = $die;
        }
    }

    // Figure out what help can be added to the total
    //
    // Returns the minimum and maximum values that can be contributed.
    //
    // $helpers is an array of the sets of returned values from
    // assist_values; we don't need to know which die contributes what
    // here.

    public function help_bounds(array $helpers, array $firingTargetMaxima) {
        $helpMin = $helpMax = 0;

        if ((0 == count($helpers)) ||
            (0 == count($firingTargetMaxima))) {
            return array($helpMin, $helpMax);
        }

        // Help values are sorted lowest to highest, and we enforce
        // some assumptions about the values to simplify this code a lot
        foreach ($helpers as $helpVals) {
            $min = $helpVals[0];
            $max = end($helpVals);

            if ($max > 0) {
                if ($helpMax > 0) {
                    $helpMax += $max;
                } else {
                    $helpMax = $max;
                }
            } elseif ($max < 0 && $helpMax < 1) {
                // Simplifying assumption here, but life's a lot more
                // complex if there can be gaps in the help coverage.
                $helpMax = -1;
            }

            if ($min < 0) {
                if ($helpMin < 0) {
                    $helpMin += $min;
                } else {
                    $helpMin = $min;
                }
            } elseif ($min > 0 && $helpMin > -1) {
                // Simplifying assumption here, but life's a lot more
                // complex if there can be gaps in the help coverage.
                $helpMin = 1;
            }
        }

        $firingMax = array_sum($firingTargetMaxima);
        $helpMax = min($helpMax, $firingMax);

        return array($helpMin, $helpMax);
    }

    // uses the dice in validDice to find a single valid attack within the game
    abstract public function find_attack($game);

    // confirm that an attack is legal
    abstract public function validate_attack($game, array $attackers, array $defenders);

    abstract protected function are_skills_compatible(array $attArray, array $defArray);

    // check if any of the attackers is dizzy
    public function has_dizzy_attackers(array $attackers) {
        foreach ($attackers as $attacker) {
            if ($attacker->has_flag('Dizzy')) {
                $this->validationMessage = 'Dizzy dice cannot be used as attacking dice.';
                return TRUE;
            }
        }
        return FALSE;
    }

    // actually make the attack
    // Some of this should perhaps be in the game, rather than here.
    public function commit_attack(&$game, array &$attackers, array &$defenders) {
        // Paranoia
        if (!$this->validate_attack($game, $attackers, $defenders, $game->firingAmount)) {
            return FALSE;
        }

        if ('Surrender' == $game->attack['attackType']) {
            $game->waitingOnActionArray = array_fill(0, $game->nPlayers, FALSE);
            $winnerArray = array_fill(0, $game->nPlayers, FALSE);
            $winnerArray[$game->attack['defenderPlayerIdx']] = TRUE;
            $game->forceRoundResult = $winnerArray;
            return TRUE;
        }

        if ('Pass' == $game->attack['attackType']) {
            $game->nRecentPasses += 1;
        } else {
            $game->nRecentPasses = 0;
        }

        // set attack defaults
        foreach ($attackers as &$att) {
            $att->add_flag('IsAttacker');
        }

        foreach ($defenders as &$def) {
            $def->captured = TRUE;
            $def->add_flag('WasJustCaptured');
        }

        // allow attack type to modify default behaviour
        $activeDiceNew = array();
        foreach ($attackers as &$att) {
            $att->capture($this->type, $attackers, $defenders);
        }

        foreach ($defenders as &$def) {
            $def->be_captured($this->type, $attackers, $defenders);
        }

        // reroll all (possibly changed) attacking dice except
        // trip dice that have already rerolled
        foreach ($attackers as &$att) {
            $att->roll(TRUE);
        }

        if (isset($activeDiceNew)) {
            $this->assign_new_active_dice($game, $activeDiceNew);
        }

        $this->process_captured_dice($game, $defenders);

        return TRUE;
    }

    public function resolve_default_attack(&$game) {
        if ('Default' == $game->attack['attackType'] &&
            !empty($this->resolvedType)) {
            $attack = $game->attack;
            $attack['attackType'] = $this->resolvedType;
            $game->attack = $attack;
        }
    }

    protected function process_captured_dice($game, array $defenders) {
        // james: currently only defenders, but could conceivably also include attackers
        foreach ($defenders as &$def) {
            if ($def->captured) {
                $game->capture_die($def);
            }
        }
    }

    protected function assign_new_active_dice($game, array $activeDiceNew) {
        $activeDiceCopy = $game->activeDieArrayArray;
        foreach ($activeDiceNew as $playerIdx => $activeDieArray) {
            foreach ($activeDieArray as $dieIdx => $newDie) {
                $activeDiceCopy[$playerIdx][$dieIdx] = $newDie;
            }
        }
        $game->activeDieArrayArray = $activeDiceCopy;
    }

    // methods to find that there is a valid attack
    //
    // If anybody wants to add a many dice vs many dice attack, I will
    // cut then. (It'd _work_, but the words "combinatoric explosion"
    // are deeply relevant.)


    protected function search_onevone($game, $attackers, $defenders) {
        // Sanity check

        if (count($attackers) < 1 || count($defenders) < 1) {
            return FALSE;
        }

        // OK, these aren't necessary for this one, but it's consistent.
        $aIt = new BMUtilityXCYIterator($attackers, 1);
        $dIt = new BMUtilityXCYIterator($defenders, 1);

        foreach ($aIt as $att) {
            foreach ($dIt as $def) {
                if ($this->validate_attack($game, $att, $def)) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    // Combine the logic for onevmany and manyvone by use of a
    // comparison function.
    protected function search_ovm_helper($game, $one, $many, $compare) {
        // Sanity check

        if (count($many) < 1 || count($one) < 1) {
            return FALSE;
        }

        $count = count($many);

        $oneIt = new BMUtilityXCYIterator($one, 1);

        $checkedSizes = array_fill(1, $count, FALSE);

        for ($i = 1; $i <= $count; $i++) {
            if ($checkedSizes[$i]) {
                continue;
            }

            $manyIt = new BMUtilityXCYIterator($many, $i);

            foreach ($manyIt as $m) {
                foreach ($oneIt as $o) {
                    if ($compare($game, $o, $m)) {
                        return TRUE;
                    }
                }
            }
        }

        return FALSE;
    }

    // $this may not be used in anonymous functions in PHP 5.3. Bastards.
    protected function search_onevmany($game, array $attackers, array $defenders) {
        $myself = $this;
        $compare = function ($gameVar, $att, $def) use ($myself) {
            return $myself->validate_attack($gameVar, $att, $def);
        };

        return $this->search_ovm_helper($game, $attackers, $defenders, $compare);
    }

    // It is entirely possible this method will never be used, since
    // skill attacks build a hit table instead. (For hopefully
    // improved efficiency.)
    protected function search_manyvone($game, array $attackers, array $defenders) {
        $myself = $this;
        $compare = function ($gameVar, $def, $att) use ($myself) {
            return $myself->validate_attack($gameVar, $att, $def);
        };

        return $this->search_ovm_helper($game, $defenders, $attackers, $compare);
    }

    // returns a list of possible values that can aid an attack
    protected function collect_helpers($game, array $attackers, array $defenders) {
        if (is_null($game->attackerAllDieArray)) {
            return array();
        }

        $helpers = array();
        foreach ($game->attackerAllDieArray as $die) {
            $helpVals = $die->assist_values($this->type, $attackers, $defenders);
            if ($helpVals[0] != 0) {
                $helpers[] = $helpVals;
            }
        }
        return $helpers;
    }

    // returns a list of maximum values that each die can be fired
    protected function collect_firing_maxima(array $attackers) {
        $firingMaxima = array();

        if (empty($attackers)) {
            return $firingMaxima;
        }

        foreach ($attackers as $attacker) {
            $firingMaxima[] = $attacker->firingMax;
        }

        return $firingMaxima;
    }

    public function type_for_log() {
        return $this->type;
    }

    /**
     * Getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property) {
        if (property_exists($this, $property)) {
            switch ($property) {
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
        throw new LogicException(
            "BMAttack->$property cannot be set (attempting to set value $value)."
        );
//        switch ($property) {
//            default:
//                $this->$property = $value;
//        }
    }

    /**
     * Define behaviour of isset()
     *
     * @param string $property
     * @return boolean
     */
    public function __isset($property) {
        return isset($this->$property);
    }
}
