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
        $this->validationMessage = '';

        if (count($attackers) < 1) {
            $this->validationMessage = 'There must be at least one attacking die for a skill attack.';
            return FALSE;
        }

        if (count($defenders) != 1) {
            $this->validationMessage = 'There must be exactly one target die for a skill attack.';
            return FALSE;
        }

        if ($this->has_dizzy_attackers($attackers)) {
            // validation message set within $this->has_dizzy_attackers()
            return FALSE;
        }

        if (!$this->are_skills_compatible($attackers, $defenders)) {
            // validation message set within $this->are_skills_compatible()
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
            $this->validationMessage = 'Attacker die values do not sum up to target die value.';
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
        $this->validationMessage = 'Attacker die values do not sum up to target die value.';
        return FALSE;
    }

    protected function are_skills_compatible(array $attArray, array $defArray) {
        if (0 == count($attArray)) {
            throw new InvalidArgumentException('attArray must have at least one element.');
        }

        if (1 != count($defArray)) {
            throw new InvalidArgumentException('defArray must have one element.');
        }

        $def = $defArray[0];

        if (1 == count($attArray)) {
            if ($attArray[0]->has_skill('Stealth')) {
                $this->validationMessage = 'Skill attacks involving a single attacker stealth die are invalid.';
                return FALSE;
            }

            if ($attArray[0]->has_skill('Konstant')) {
                $this->validationMessage = 'Skill attacks involving a single attacker konstant die are invalid.';
                return FALSE;
            }
        }

        if (1 == count($attArray) &&
            $def->has_skill('Stealth')) {
            $this->validationMessage = 'Multiple attacker dice are required to skill attack a stealth die.';
            return FALSE;
        }

        foreach ($attArray as $att) {
            if ($att->has_skill('Berserk')) {
                $this->validationMessage = 'Berserk dice cannot perform skill attacks.';
                return FALSE;
            }
        }

        return TRUE;
    }
}
