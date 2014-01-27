<?php

class BMAttackSkill extends BMAttack {
    public $type = "Skill";

    // Especially once you take stinger and constant into account,
    // searching the full attack space is slow and complex
    //
    // Building a hit table once trades some increased setup cost for
    // a much reduced search cost
    //
    // Fire still makes life more complex than it might be.
    //
    // "Premature optimization is the root of all evil." -- Knuth
    protected $hit_table = NULL;

    protected function generate_hit_table() {
        $this->hit_table = new BMUtilityHitTable($this->validDice);
    }

    public function find_attack($game) {
        $targets = $game->defenderAllDieArray;

        if (count($targets) < 1) {
            return FALSE;
        }

        $this->generate_hit_table();

        // Check all precise hits before trying any with help, because
        // help is slow
        foreach ($targets as $def) {
            if ($this->hit_table->find_hit($def->defense_value($this->type))) {
                return TRUE;
            }
        }

        // Potentially save some time by checking for the possibility of help

        $help_found = FALSE;
        foreach ($targets as $def) {
            foreach ($game->attackerAllDieArray as $att) {
                if ($this->collect_helpers($game, array($att), array($def))) {
                    $help_found = TRUE;
                    break 2;
                }
            }
        }

        if (!$help_found) {
            return FALSE;
        }

        $hits = $this->hit_table->list_hits();
        sort($hits);

        // Should perhaps start around the defending die's value and
        // work outward, but probably not worth the extra overhead to
        // do so. We half-ass it by starting in the middle.

        // PHP, why do you have no integer division operator?
        $idx1 = (int)(count($hits) / 2);
        $idx2 = $idx1 - 1;
        $idx2 += count($hits) % 2; // Start in the exact middle of an odd count
        while ($idx2 >= 0) {
            foreach ($targets as $t) {
                $def = array($t);
                $combos = $this->hit_table->find_hit($hits[$idx1]);
                foreach ($combos as $att) {
                    if ($this->validate_attack($game, $att, $def)) {
                        return TRUE;
                    }
                }
                if ($idx1 == $idx2) {
                    continue;
                }
                $combos = $this->hit_table->find_hit($hits[$idx2]);
                foreach ($combos as $att) {
                    if ($this->validate_attack($game, $att, $def)) {
                        return TRUE;
                    }
                }
            }
            $idx2--;
            $idx1++;
        }

        return FALSE;
    }

    public function validate_attack($game, array $attackers, array $defenders) {
        if (count($attackers) < 1 || count($defenders) != 1) {
            return FALSE;
        }

        if ($this->has_disabled_attackers($attackers)) {
            return FALSE;
        }

        if (!$this->are_skills_compatible($attackers, $defenders)) {
            return FALSE;
        }

        // array_intersect tries to convert to strings, so we
        // use array_uintersect, which needs a comparison
        // function
        $cmp = function ($var1, $var2) {
            if ($var1===$var2) {
                return 0;
            }
            if ($var1 > $var2) {
                return 1;
            }
            return -1;
        };

        $dval = $defenders[0]->defense_value($this->type);

        $this->generate_hit_table();

        // exact hits
        $combos = $this->hit_table->find_hit($dval);
        if ($combos) {
            foreach ($combos as $c) {
                if (count($c) == count($attackers) &&
                    count(array_uintersect($c, $attackers, $cmp)) ==
                    count($c)) {
                    return TRUE;
                }
            }
        }

        // assisted attacks
        $helpers = $this->collect_helpers($game, $attackers, $defenders);
        $bounds = $this->help_bounds($helpers);
        if ($bounds[0] == 0 && $bounds[1] == 0) {
            return FALSE;
        }
        for ($i = $bounds[0]; $i <= $bounds[1]; $i++) {
            $combos = $this->hit_table->find_hit($dval + $i);
            if ($combos) {
                foreach ($combos as $c) {
                    if (count($c) == count($attackers) &&
                        count(array_uintersect($c, $attackers, $cmp)) ==
                        count($c)) {
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
    }

    protected function are_skills_compatible(array $attArray, array $defArray) {
        if (0 == count($attArray)) {
            throw new InvalidArgumentException('attArray must have at least one element.');
        }

        if (1 != count($defArray)) {
            throw new InvalidArgumentException('defArray must have one element.');
        }

        $returnVal = TRUE;

        $def = $defArray[0];

        if (1 == count($attArray) &&
            $attArray[0]->has_skill('Stealth')) {
            $returnVal = FALSE;
        }

        foreach ($attArray as $att) {
            if ($att->has_skill('Berserk') ||
                // do not allow single-die skill attacks from konstant dice
                ($att->has_skill('Konstant') && (1 == count($attArray)))
            ) {
                $returnVal = FALSE;
            }
        }

        if ($def->has_skill('Stealth') &&
            1 == count($attArray)) {
            $returnVal = FALSE;
        }

        return $returnVal;
    }
}
