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
    /**
     * True for attacks that do something besides simple capture,
     * because the player may have to choose which attack type to
     * use. Captures are indistinguishable among attacks with no
     * side effects
     *
     * @var bool
     */
    public $sideEffect = FALSE;

    /**
     * Type of attack
     *
     * @var string
     */
    public $type;

    /**
     * Error message shown to the user when the attack parameters are invalid
     *
     * @var string
     */
    public $validationMessage = '';

    /**
     * Dice that effect or affect this attack
     *
     * @var array
     */
    protected $validDice = array();

    /**
     * The standard factory method that generates all BMAttack* objects
     *
     * @param string $type
     * @return BMAttack*
     */
    public static function create($type = NULL) {
        if ($type) {
            $cname = "BMAttack" . ucfirst(strtolower($type));
            if (class_exists($cname)) {
                return $cname::create();
            } else {
                return NULL;
            }
        }

        $class = get_called_class();
        return new $class;
    }

    /**
     * Array of all attack types that are possible with these particular attackers
     *
     * @param array $attackers
     * @return array
     */
    public static function possible_attack_types(array $attackers) {
        $allAttackTypesArray = array();

        foreach ($attackers as $attacker) {
            $attackTypeArray = array();
            $attackTypeArray['Power'] = 'Power';
            $attackTypeArray['Skill'] = 'Skill';
            $attackTypeArray['Rush'] = 'Rush';

            if ($attacker->ownerObject instanceof BMGame) {
                $ownerButton = $attacker->ownerObject->playerArray[$attacker->playerIdx]->button;
                $ownerButton->run_hooks(
                    'attack_list',
                    array('attackTypeArray' => &$attackTypeArray)
                );
            }

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

    /**
     * Comparator used for ordering attack types
     *
     * @param string $str1
     * @param string $str2
     * @return int
     */
    public static function display_cmp($str1, $str2) {
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

        // force Pass attacks to be displayed last
        if ('Pass' == $str1) {
            return 1;
        } elseif ('Pass' == $str2) {
            return -1;
        }

        return strcasecmp($str1, $str2);
    }

    /**
     * Add a die that is involved (directly or indirectly) in this attack
     *
     * @param BMDie $die
     */
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

    /**
     * Figure out what help can be added to the total
     *
     * Returns the minimum and maximum values that can be contributed.
     *
     * $helpers is an array of the sets of returned values from
     * assist_values; we don't need to know which die contributes what
     * here.
     *
     * @param array $helpers
     * @param array $firingTargetMaxima
     * @return array
     */
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

    /**
     * Determine the help bounds for a specific attack in a current BMGame
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @return array
     */
    public function help_bounds_specific($game, array $attackers, array $defenders) {
        return $this->help_bounds(
            $this->collect_helpers($game, $attackers, $defenders),
            $this->collect_firing_maxima($attackers)
        );
    }

    /**
     * Determine if there is at least one valid attack of this type from
     * the set of all possible attackers and defenders.
     *
     * If $includeOptional is FALSE, then optional attacks are excluded.
     * These include skill attacks involving warrior dice.
     *
     * @param BMGame $game
     * @param bool $includeOptional
     * @return bool
     */
    abstract public function find_attack($game, $includeOptional = TRUE);

    /**
     * Determine if specified attack is valid.
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @return bool
     */
    abstract public function validate_attack($game, array $attackers, array $defenders);

    /**
     * Check if skills are compatible with this type of attack.
     *
     * @param array $attArray
     * @param array $defArray
     * @return bool
     */
    abstract protected function are_skills_compatible(array $attArray, array $defArray);

    /**
     * Determine if any of the attackers is dizzy
     *
     * @param array $attackers
     * @return bool
     */
    public function has_dizzy_attackers(array $attackers) {
        foreach ($attackers as $attacker) {
            if ($attacker->has_flag('Dizzy')) {
                $this->validationMessage = 'Dizzy dice cannot be used as attacking dice.';
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Actually commit the attack
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @return bool
     */
    public function commit_attack(&$game, array &$attackers, array &$defenders) {
        // Paranoia
        if (!$this->validate_attack(
            $game,
            $attackers,
            $defenders,
            array('helpValue' => $game->firingAmount)
        )
        ) {
            return FALSE;
        }

        if ('Surrender' == $game->attack['attackType']) {
            foreach ($game->playerArray as $player) {
                $player->waitingOnAction = FALSE;
            }
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

        // this logic is here to allow attacks that might not capture
        // like trip and boom to trigger before rage
        foreach ($attackers as &$att) {
            $att->pre_capture($this->type, $attackers, $defenders);
        }

        // james: it's necessary here to copy the $defenders array
        // because Rage may add dice to $defenders
        $defendersCopy = $defenders;

        // this logic is here to allow rage to trigger at the right time
        foreach ($defendersCopy as &$def) {
            $def->pre_be_captured($this->type, $attackers, $defenders);
        }

        // allow attack type to modify default behaviour
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

        $this->ensure_defenders_have_value($defenders);

        $this->process_captured_dice($game, $defenders);

        return TRUE;
    }

    /**
     * Give defenders a value if they don't already have one,
     * like for a rage replacement die
     *
     * @param array $defenders
     */
    protected function ensure_defenders_have_value(array $defenders) {
        foreach ($defenders as &$def) {
            if (empty($def->value)) {
                $def->roll(FALSE);
            }
        }
    }

    /**
     * Change the attack type specified in $game->attack from 'default' into
     * the actual attack type
     *
     * @param BMGame $game
     */
    public function resolve_default_attack(&$game) {
        if ('Default' == $game->attack['attackType'] &&
            !empty($this->resolvedType)) {
            $attack = $game->attack;
            $attack['attackType'] = $this->resolvedType;
            $game->attack = $attack;
        }
    }

    /**
     * Deal with changes that need to occur for captured dice
     *
     * @param BMGame $game
     * @param array $defenders
     */
    protected function process_captured_dice($game, array $defenders) {
        // james: currently only defenders, but could conceivably also include attackers
        foreach ($defenders as &$def) {
            if ($def->captured) {
                $game->capture_die($def);
            }
        }
    }

    // methods to find that there is a valid attack
    //
    // If anybody wants to add a many dice vs many dice attack, I will
    // cut then. (It'd _work_, but the words "combinatoric explosion"
    // are deeply relevant.)

    /**
     * Search for a valid one-vs-one attack
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @param array $args
     * @return bool
     */
    protected function search_onevone($game, $attackers, $defenders, array $args = array()) {
        // Sanity check

        if (count($attackers) < 1 || count($defenders) < 1) {
            return FALSE;
        }

        // OK, these aren't necessary for this one, but it's consistent.
        $aIt = new BMUtilityXCYIterator($attackers, 1);
        $dIt = new BMUtilityXCYIterator($defenders, 1);

        foreach ($aIt as $att) {
            foreach ($dIt as $def) {
                if ($this->validate_attack($game, $att, $def, $args)) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * Combine the logic for one-vs-many and many-vs-one by use of a
     * comparison function
     *
     * @param BMGame $game
     * @param array $one
     * @param array $many
     * @param function $compare
     * @return bool
     */
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

    /**
     * Search for a valid one-vs-many attack
     *
     * Note: $this may not be used in anonymous functions in PHP 5.3. Bastards.
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @return bool
     */
    protected function search_onevmany($game, array $attackers, array $defenders) {
        $myself = $this;
        $compare = function ($gameVar, $att, $def) use ($myself) {
            return $myself->validate_attack($gameVar, $att, $def);
        };

        return $this->search_ovm_helper($game, $attackers, $defenders, $compare);
    }

    /**
     * Search for a valid many-vs-one attack
     *
     * It is entirely possible this method will never be used, since
     * skill attacks build a hit table instead. (For hopefully
     * improved efficiency.)
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @return bool
     */
    protected function search_manyvone($game, array $attackers, array $defenders) {
        $myself = $this;
        $compare = function ($gameVar, $def, $att) use ($myself) {
            return $myself->validate_attack($gameVar, $att, $def);
        };

        return $this->search_ovm_helper($game, $defenders, $attackers, $compare);
    }

    /**
     * Returns an array of possible values that can aid an attack
     *
     *
     * @param BMGame $game
     * @param array $attackers
     * @param array $defenders
     * @return array
     */
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

    /**
     * Returns a list of maximum values that each die can be fired
     *
     * @param array $attackers
     * @return array
     */
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

    /**
     * Accessor for the attack type, used by logging code
     *
     * @return string
     */
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
     * @return bool
     */
    public function __isset($property) {
        return isset($this->$property);
    }
}
