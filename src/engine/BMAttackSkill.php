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

    protected function generate_hit_table($game) {
        $this->hit_table = new BMUtilityHitTable($this->validDice);
    }

    public function find_attack($game) {
        $targets = $game->defenderAllDieArray;

        if (count($targets) < 1) { return FALSE; }

        $this->generate_hit_table($game);

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

        if (!$help_found) { return FALSE; }

        $hits = $this->hit_table->list_hits();
        sort($hits);

        // Should perhaps start around the defending die's value and
        // work outward, but probably not worth the extra overhead to
        // do so. We half-ass it by starting in the middle.

        // PHP, why do you have no integer division operator?
        $i = (int)(count($hits) / 2);
        $j = $i - 1;
        $j += count($hits) % 2; // Start in the exact middle of an odd count
        while ($j >= 0) {
            foreach ($targets as $t) {
                $def = array($t);
                $combos = $this->hit_table->find_hit($hits[$i]);
                foreach ($combos as $att) {
                    if ($this->validate_attack($game, $att, $def)) {
                        return TRUE;
                    }
                }
                if ($i == $j) { continue; }
                $combos = $this->hit_table->find_hit($hits[$j]);
                foreach ($combos as $att) {
                    if ($this->validate_attack($game, $att, $def)) {
                        return TRUE;
                    }
                }
            }
            $j--; $i++;
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

        foreach ($attackers as $attacker) {
            if ($attacker->has_skill('Berserk')) {
                return FALSE;
            }
        }

        // do not allow single-die skill attacks from konstant dice
        if (1 == count($attackers) &&
            $attackers[0]->has_skill('Konstant')) {
            return FALSE;
        }

        // array_intersect tries to convert to strings, so we
        // use array_uintersect, which needs a comparison
        // function
        $cmp = function ($v1,$v2) {
            if ($v1===$v2) { return 0; }
            if ($v1 > $v2) { return 1; }
            return -1;
        };

        $dval = $defenders[0]->defense_value($this->type);

        $this->generate_hit_table($game);

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
        if ($bounds[0] == 0 && $bounds[1] == 0) { return FALSE; }
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

    // return how much help is needed and who can contribute
    public function calculate_contributions($game, array $attackers, array $defenders) {
        return array(0, array());
    }
}

?>
