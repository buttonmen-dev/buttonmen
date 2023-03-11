<?php
/**
 * BMDieWildcard: Code specific to wildcard dice
 *
 * @author james
 */

/**
 * This class contains all the logic to do with instantiating wildcard dice
 */
class BMDieWildcard extends BMDie {

    /**
     * Full identifier of card:
     *    0 is unspecified
     *
     *    1-13 are clubs     (1-10, J, Q, K)
     *   14-26 are diamonds  (1-10, J, Q, K)
     *   27-39 are hearts    (1-10, J, Q, K)
     *   40-52 are spades    (1-10, J, Q, K)
     *   53-54 are jokers
     *
     * @var int
     */
    public $currentCardId;

    /**
     * Array of card identifiers that have already been drawn
     *
     * @var array
     */
    protected $usedCardIdArray;

    protected function suit($isShort = FALSE, $isHtml = FALSE) {
        if (($this->currentCardId <= 0) || ($this->currentCardId > 52)) {
            return '';
        } elseif ($this->currentCardId >= 40) {
            $fullStr = 'Spades';
        } elseif ($this->currentCardId >= 27) {
            $fullStr = 'Hearts';
        } elseif ($this->currentCardId >= 14) {
            $fullStr = 'Diamonds';
        } else {
            $fullStr = 'Clubs';
        }

        if ($isShort) {
            return $fullStr[0];
        } elseif ($isHtml) {
            $htmlStr = strtolower($fullStr);

            if ('diamonds' == $htmlStr) {
                $htmlStr = 'diams';
            }

            return '&' . $htmlStr . ';';
        } else {
            return $fullStr;
        }
    }

    protected function colour() {
        if (($this->currentCardId <= 0) || ($this->currentCardId > 54)) {
            return '';
        } elseif ($this->currentCardId == 53) {
            return 'red';
        } elseif ($this->currentCardId == 54) {
            return 'black';
        } elseif ($this->currentCardId >= 40) {
            return 'black';
        } elseif ($this->currentCardId >= 27) {
            return 'red';
        } elseif ($this->currentCardId >= 14) {
            return 'red';
        } else {
            return 'black';
        }
    }

    public function displayed_value($isHtml = FALSE) {
        if (($this->currentCardId <= 0) || ($this->currentCardId > 54)) {
            return '';
        } elseif ($this->currentCardId >= 53) {
            $textStr = 'Jkr (' . $this->colour() . ')';
            $htmlStr = '<span class="suit_' . $this->colour() . '">Jkr</span>';
        } else {
            $suit = $this->suit(TRUE);
            $htmlSuit = '<span class="suit_' . $this->colour() . '">' .
                        $this->suit(FALSE, TRUE) . '</span>';

            if (1 === $this->currentCardId % 13) {
                $value = 'A';
            } elseif (11 === $this->currentCardId % 13) {
                $value = 'J';
            } elseif (12 === $this->currentCardId % 13) {
                $value = 'Q';
            } elseif (0 === $this->currentCardId % 13) {
                $value = 'K';
            } else {
                $value = $this->currentCardId % 13;
            }

            $htmlStr = $value . $htmlSuit;
            $textStr = $value . $suit;
        }

        if ($isHtml) {
            return $htmlStr;
        } else {
            return $textStr;
        }
    }

    public function numeric_value() {
        if (($this->currentCardId <= 0) || ($this->currentCardId > 54)) {
            $value = NULL;
        } elseif (53 === $this->currentCardId) {
            $value = 20;
        } elseif (54 === $this->currentCardId) {
            $value = 20;
        } else {
            $value = $this->currentCardId % 13;
            if (0 == $value) {
                $value = 13;
            }
        }

        return $value;
    }

    public function wildcard_properties() {
        return array(
            'type' => 'Wildcard',
            'suit' => $this->suit(),
            'colour' => $this->colour(),
            'displayedValue' => $this->displayed_value(TRUE)
        );
    }

    /**
     * Set number of sides of the die, and add die skills
     *
     * @param int $sides
     * @param array $skills
     */
    public function init($sides, array $skills = NULL) {
        $this->currentCardId = 0;
        $this->min = 1;
        $this->max = 20;

        $this->add_multiple_skills($skills);
    }

    /**
     * Create a BMDieWildcard, then add skills to the die.
     *
     * @param array $skills
     * @return BMDieWildcard
     */
    public static function create($size, array $skills = NULL) {
        $die = new BMDieWildcard;

        $die->init($size, $skills);

        return $die;
    }

    /**
     * Select a new value
     */
    protected function select_new_value() {
        if (!($this->ownerObject instanceof BMGame)) {
            return;
        }

        $game = $this->ownerObject;
        $playerArray = $game->playerArray;
        $player = $game->playerArray[$this->playerIdx];

        if (count($player->cardsDrawn) >= 54) {
            $player->cardsDrawn = array();
        }

        $cardsDrawn = $player->cardsDrawn;
        $cardsRemaining = array_values(array_diff(range(1, 54), array_keys($cardsDrawn)));
        $newCardId = $cardsRemaining[bm_rand(0, count($cardsRemaining) - 1)];
        $cardsDrawn[$newCardId] = TRUE;
        $this->currentCardId = $newCardId;
        $this->set_value_from_id();

        $playerArray[$this->playerIdx]->cardsDrawn = $cardsDrawn;
        $game->playerArray = $playerArray;
    }

    /**
     * This redirection of the inherited method is used to emphasise the extraordinary
     * way that Wildcard changes its value for things like fire or focus turndown.
     *
     * @param int $value
     */
    public function set_value($value) {
        $this->force_specific_value($value);
    }

    /**
     * Sometimes, we need to keep drawing until we reach a specific value,
     * like in fire turndown, or maximum die reroll. This function lets
     * us specify the value that we want to end up with.
     */
    protected function force_specific_value($value) {
        $isValueValid = is_int($value) &&
                        (($value === 20) ||
                         (($value >= 1) && ($value <= 13)));

        if (!$isValueValid) {
            throw new LogicException('A wildcard can only have an integer value between 1 and 13, or 20.');
        }

        $this->select_new_value();

        $count = 0;

        while ($value != $this->value) {
            $this->select_new_value();
            $count++;
            if ($count > 108) {
                throw new LogicException('Too many redraws required.');
            }
        }
    }

    /**
     * Return all possible values the die may use in this type of attack.
     * The values must be sorted, highest to lowest, with no duplication.
     *
     * @param string $type
     * @return array
     */
    public function attack_values($type) {
        if ((1 === $this->value) && ('Power' === $type)) {
            $list = array(14);
        } else {
            $list = array($this->value);
        }

        $this->run_hooks(__FUNCTION__, array('attackType' => $type,
                                             'attackValues' => &$list,
                                             'minValue' => $this->min,
                                             'value' => $this->value));
        return $list;
    }

    /**
     * Set the value based on the card identifier
     */
    public function set_value_from_id() {
        $value = $this->get_value_from_id($this->currentCardId);

        if (is_int($value)) {
            $this->set__value($value);
        }
    }

    /**
     * Calculate the value from the card identifier
     *
     * @param type $cardId
     * @return int
     */
    protected function get_value_from_id($cardId) {
        if ($cardId <= 0) {
            return;
        } elseif ($cardId >= 55) {
            throw new LogicException('Wildcard cannot have a card ID above 54');
        } elseif (!is_int($cardId)) {
            throw new LogicException('Wildcard must have an integer card ID');
        } elseif ($cardId >= 53) {
            return 20;
        } else {
            return (($cardId - 1) % 13) + 1;
        }
    }

    /**
     * Get the base score value of the die before applying any adjustments
     *
     * @return int
     */
    protected function get_raw_score_value() {
        return 16;
    }

    /**
     * Description of die size
     *
     * @return string
     */
    protected function die_size_string() {
        return "Wildcard die";
    }

    /**
     * Split a Wildcard die in half
     *
     * @return array
     */
    public function split() {
        return array($this);
    }

    /**
     * shrink() is intended to be used for weak dice
     */
    public function shrink() {
        // Wildcard dice do not shrink
    }

    /**
     * grow() is intended to be used for mighty dice
     */
    public function grow() {
        // Wildcard dice do not grow
    }

    /**
     * Get all die types.
     *
     * @return array
     */
    public function getDieTypes() {
        $typesList = array();
        $typesList['Wildcard'] = array(
            'code' => 'C',
            'description' => self::getDescription(),
        );
        return $typesList;
    }

    /**
     * Get description of wildcard dice
     *
     * @return string
     */
    public static function getDescription() {
        return  'A Wildcard die is not rolled. Instead, a card ' .
                'is drawn from a deck of playing cards with two jokers. ' .
                'The value of the Wildcard die is the value of the card, ' .
                'where Jack, Queen, King, and Joker have a value of 11, ' .
                '12, 13, and 20, respectively. ' .
                'A Wildcard die showing an Ace has a value of 1 except ' .
                'when the Wildcard die is performing a Power attack, ' .
                'when it can attack as if it had a value of 14. A ' .
                'Wildcard die is scored as if it were a 16-sided die. ' .
                'All active Wildcard dice owned by a player share the ' .
                'same deck. Cards are drawn without replacement, ' .
                'and the deck is only shuffled during a game if it ' .
                'is exhausted. Wildcard dice do not split, shrink, or grow.';
    }
}
