<?php

class BMGameActionTest extends PHPUnit_Framework_TestCase {

    /**
     * @var BMGameAction
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->playerIdNames = array(1 => "gameaction01", 2 => "gameaction02");
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
    }

    /**
     * @covers BMGameAction::__construct()
     */
    public function test_construct() {
        $attackStr = "performed Power attack using [(X):1] against [(4):1]; Defender (4) was captured; Attacker (X) rerolled 1 => 2";
        $this->object = new BMGameAction(40, 'attack', 1, $attackStr);
        $this->assertEquals($this->object->gameState, 40);
        $this->assertEquals($this->object->actionType, 'attack');
        $this->assertEquals($this->object->actingPlayerId, 1);
        $this->assertEquals($this->object->params, $attackStr);

        try {
            $this->object = new BMGameAction(40, 'attack', 1, array());
            $this->fail('BMGameAction should not accept empty params array');
        }
        catch (Exception $expected) {
        }
    }

    /**
     * @covers BMGameAction::friendly_message()
     */
    public function test_friendly_message() {
        $attackStr = "performed Power attack using [(X):1] against [(4):1]; Defender (4) was captured; Attacker (X) rerolled 1 => 2";
        $this->object = new BMGameAction(40, 'attack', 1, $attackStr);
        $this->assertEquals(
            "gameaction01 performed Power attack using [(X):1] against [(4):1]; Defender (4) was captured; Attacker (X) rerolled 1 => 2",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_end_draw()
     */
    public function test_friendly_message_end_draw() {
        $this->object = new BMGameAction(50, 'end_draw', 0, array('roundNumber' => 2, 'roundScoreArray' => array(23, 23)));
        $this->assertEquals(
            "Round 2 ended in a draw (23 vs. 23)",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_end_winner()
     */
    public function test_friendly_message_end_winner() {
        $this->object = new BMGameAction(50, 'end_winner', 2, array('roundNumber' => 1, 'roundScoreArray' => array(24, 43), 'resultForced' => NULL));
        $this->assertEquals(
            "End of round: gameaction02 won round 1 (43 vs. 24)",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );

        $this->object = new BMGameAction(50, 'end_winner', 2, array('roundNumber' => 2, 'roundScoreArray' => array(25, 23), 'resultForced' => array(FALSE, TRUE)));
        $this->assertEquals(
            "End of round: gameaction02 won round 2 because opponent surrendered",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_needs_firing()
     */
    public function test_friendly_message_needs_firing() {
        $this->object = new BMGameAction(40, 'needs_firing', 1, array(
            'attackType' => 'Power',
            'attackDice' => array(
                'attacker' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 3, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(4):3'),
                ),
                'defender' => array(
                    array('recipe' => '(10)', 'min' => 1, 'max' => 10, 'value' => 6, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(10):6'),
                ),
            ),
        ));
        $this->assertEquals(
            "gameaction01 chose to perform a Power attack using [(4):3] against [(10):6]; gameaction01 must turn down fire dice to complete this attack",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_fire_cancel()
     */
    public function test_friendly_message_fire_cancel() {
        $this->object = new BMGameAction(40, 'fire_cancel', 1, array('action' => 'cancel'));
        $this->assertEquals(
            "gameaction01 chose to abandon this attack and start over",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_attack()
     */
    public function test_friendly_message_attack_power() {
        $this->object = new BMGameAction(40, 'attack', 1, array(
            'attackType' => 'Power',
            'preAttackDice' => array(
                'attacker' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 3, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(4):3'),
                ),
                'defender' => array(
                    array('recipe' => '(10)', 'min' => 1, 'max' => 10, 'value' => 1, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(10):1'),
                ),
            ),
            'postAttackDice' => array(
                'attacker' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 2, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(4):2'),
                ),
                'defender' => array(
                    array('recipe' => '(10)', 'min' => 1, 'max' => 10, 'value' => 1, 'doesReroll' => TRUE, 'captured' => TRUE, 'recipeStatus' => '(10):1'),
                ),
            )
        ));
        $this->assertEquals(
            "gameaction01 performed Power attack using [(4):3] against [(10):1]; Defender (10) was captured; Attacker (4) rerolled 3 => 2",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_attack()
     */
    public function test_friendly_message_attack_skill() {
        $this->object = new BMGameAction(40, 'attack', 1, array(
            'attackType' => 'Skill',
            'preAttackDice' => array(
                'attacker' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 1, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(4):1'),
                    array('recipe' => '(5)', 'min' => 1, 'max' => 5, 'value' => 2, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(5):2'),
                    array('recipe' => '(6)', 'min' => 1, 'max' => 6, 'value' => 3, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(6):3'),
                ),
                'defender' => array(
                    array('recipe' => '(10)', 'min' => 1, 'max' => 10, 'value' => 6, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(10):6'),
                ),
            ),
            'postAttackDice' => array(
                'attacker' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 2, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(4):2'),
                    array('recipe' => '(5)', 'min' => 1, 'max' => 5, 'value' => 3, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(5):3'),
                    array('recipe' => '(6)', 'min' => 1, 'max' => 6, 'value' => 5, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(6):5'),
                ),
                'defender' => array(
                    array('recipe' => '(10)', 'min' => 1, 'max' => 10, 'value' => 6, 'doesReroll' => TRUE, 'captured' => TRUE, 'recipeStatus' => '(10):6'),
                ),
            )
        ));
        $this->assertEquals(
            "gameaction01 performed Skill attack using [(4):1,(5):2,(6):3] against [(10):6]; Defender (10) was captured; Attacker (4) rerolled 1 => 2; Attacker (5) rerolled 2 => 3; Attacker (6) rerolled 3 => 5",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_attack()
     */
    public function test_friendly_message_attack_speed() {
        $this->object = new BMGameAction(40, 'attack', 1, array(
            'attackType' => 'Speed',
            'preAttackDice' => array(
                'attacker' => array(
                    array('recipe' => '(10)', 'min' => 1, 'max' => 10, 'value' => 6, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(10):6'),
                ),
                'defender' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 1, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(4):1'),
                    array('recipe' => '(5)', 'min' => 1, 'max' => 5, 'value' => 2, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(5):2'),
                    array('recipe' => '(6)', 'min' => 1, 'max' => 6, 'value' => 3, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(6):3'),
                ),
            ),
            'postAttackDice' => array(
                'attacker' => array(
                    array('recipe' => '(10)', 'min' => 1, 'max' => 10, 'value' => 8, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(10):8'),
                ),
                'defender' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 1, 'doesReroll' => TRUE, 'captured' => TRUE, 'recipeStatus' => '(4):1'),
                    array('recipe' => '(5)', 'min' => 1, 'max' => 5, 'value' => 2, 'doesReroll' => TRUE, 'captured' => TRUE, 'recipeStatus' => '(5):2'),
                    array('recipe' => '(6)', 'min' => 1, 'max' => 6, 'value' => 3, 'doesReroll' => TRUE, 'captured' => TRUE, 'recipeStatus' => '(6):3'),
                ),
            )
        ));
        $this->assertEquals(
            "gameaction01 performed Speed attack using [(10):6] against [(4):1,(5):2,(6):3]; Defender (4) was captured; Defender (5) was captured; Defender (6) was captured; Attacker (10) rerolled 6 => 8",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_attack()
     */
    public function test_friendly_message_attack_power_after_fire_turndown() {
        $this->object = new BMGameAction(40, 'attack', 1, array(
            'attackType' => 'Power',
            'preAttackDice' => array(
                'attacker' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 3, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(4):3'),
                ),
                'defender' => array(
                    array('recipe' => '(10)', 'min' => 1, 'max' => 10, 'value' => 1, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(10):1'),
                ),
            ),
            'postAttackDice' => array(
                'attacker' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 2, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(4):2'),
                ),
                'defender' => array(
                    array('recipe' => '(10)', 'min' => 1, 'max' => 10, 'value' => 1, 'doesReroll' => TRUE, 'captured' => TRUE, 'recipeStatus' => '(10):1'),
                ),
            ),
            'fireCache' => array(
                'fireRecipes' => array('F(4)', 'Fs(6)', 'Fd(15)'),
                'oldValues' => array(4, 5, 9),
                'newValues' => array(4, 3, 8),
            )
        ));

        $this->assertEquals(
            'gameaction01 turned down fire dice: Fs(6) from 5 to 3, Fd(15) from 9 to 8; ' .
            'Defender (10) was captured; Attacker (4) rerolled 3 => 2',
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_attack()
     */
    public function test_friendly_message_attack_power_mood_swing() {
        $this->object = new BMGameAction(40, 'attack', 1, array(
            'attackType' => 'Power',
            'preAttackDice' => array(
                'attacker' => array(
                    array('recipe' => '(X)?', 'min' => 1, 'max' => 4, 'value' => 3, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(X)?:3'),
                ),
                'defender' => array(
                    array('recipe' => '(10)', 'min' => 1, 'max' => 10, 'value' => 1, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(10):1'),
                ),
            ),
            'postAttackDice' => array(
                'attacker' => array(
                    array('recipe' => '(X)?', 'min' => 1, 'max' => 7, 'value' => 2, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(X)?:7'),
                ),
                'defender' => array(
                    array('recipe' => '(10)', 'min' => 1, 'max' => 10, 'value' => 1, 'doesReroll' => TRUE, 'captured' => TRUE, 'recipeStatus' => '(10):1'),
                ),
            )
        ));
        $this->assertEquals(
            "gameaction01 performed Power attack using [(X)?:3] against [(10):1]; Defender (10) was captured; Attacker (X)? changed size from 4 to 7 sides, rerolled 3 => 2",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_attack()
     */
    public function test_friendly_message_attack_surrender() {
        $this->object = new BMGameAction(40, 'attack', 2, array(
            'attackType' => 'Surrender',
            'preAttackDice' => array( 'attacker' => array(), 'defender' => array(), ),
            'postAttackDice' => array( 'attacker' => array(), 'defender' => array(), ),
        ));
        $this->assertEquals(
            "gameaction02 surrendered",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_attack()
     */
    public function test_friendly_message_attack_trip() {
        $this->object = new BMGameAction(40, 'attack', 1, array(
            'attackType' => 'Trip',
            'preAttackDice' => array(
                'attacker' => array(
                    array('recipe' => 't(2)', 'min' => 1, 'max' => 2, 'value' => 1, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => 't(2):1', 'hasJustMorphed' => FALSE),
                ),
                'defender' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 3, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(4):3', 'hasJustMorphed' => FALSE),
                ),
            ),
            'postAttackDice' => array(
                'attacker' => array(
                    array('recipe' => 't(2)', 'min' => 1, 'max' => 2, 'value' => 2, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => 't(2):2', 'valueAfterTripAttack' => 2, 'hasJustMorphed' => FALSE),
                ),
                'defender' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 1, 'doesReroll' => TRUE, 'captured' => TRUE, 'recipeStatus' => '(4):1', 'hasJustMorphed' => FALSE),
                ),
            )
        ));
        $this->assertEquals(
            "gameaction01 performed Trip attack using [t(2):1] against [(4):3]; Attacker t(2) rerolled 1 => 2; Defender (4) rerolled 3 => 1, was captured",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_attack()
     *
     * This test case covers older "attack" type action log entries
     * which may lack information which is later added, to make
     * sure changes to friendly_message_attack() don't break those entries
     */
    public function test_friendly_message_attack_backwards_compatible() {
        $this->object = new BMGameAction(40, 'attack', 1, array(
            'attackType' => 'Trip',
            'preAttackDice' => array(
                'attacker' => array(
                    array('recipe' => 't(2)', 'min' => 1, 'max' => 2, 'value' => 1, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => 't(2):1'),
                ),
                'defender' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 3, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(4):3'),
                ),
            ),
            'postAttackDice' => array(
                'attacker' => array(
                    array('recipe' => 't(2)', 'min' => 1, 'max' => 2, 'value' => 2, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => 't(2):2', 'valueAfterTripAttack' => 2),
                ),
                'defender' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 1, 'doesReroll' => TRUE, 'captured' => TRUE, 'recipeStatus' => '(4):1'),
                ),
            )
        ));
        $this->assertEquals(
            "gameaction01 performed Trip attack using [t(2):1] against [(4):3]; Attacker t(2) rerolled 1 => 2; Defender (4) rerolled 3 => 1, was captured",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_attack()
     */
    public function test_friendly_message_attack_morphing() {
        $this->object = new BMGameAction(40, 'attack', 1, array(
            'attackType' => 'Power',
            'preAttackDice' => array(
                'attacker' => array(
                    array('recipe' => 'm(2)', 'min' => 1, 'max' => 2, 'value' => 3, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => 'm(2):3'),
                ),
                'defender' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 1, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(4):1'),
                ),
            ),
            'postAttackDice' => array(
                'attacker' => array(
                    array('recipe' => 'm(4)', 'min' => 1, 'max' => 4, 'value' => 2, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => 'm(4):2'),
                ),
                'defender' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 1, 'doesReroll' => TRUE, 'captured' => TRUE, 'recipeStatus' => '(4):1'),
                ),
            )
        ));
        $this->assertEquals(
            "gameaction01 performed Power attack using [m(2):3] against [(4):1]; Defender (4) was captured; Attacker m(2) changed size from 2 to 4 sides, recipe changed from m(2) to m(4), rerolled 3 => 2",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_attack()
     */
    public function test_friendly_message_attack_trip_morph() {
        $this->object = new BMGameAction(40, 'attack', 1, array(
            'attackType' => 'Trip',
            'preAttackDice' => array(
                'attacker' => array(
                    array('recipe' => 'mt(2)', 'min' => 1, 'max' => 2, 'value' => 1, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => 'mt(2):1'),
                ),
                'defender' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 3, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(4):3'),
                ),
            ),
            'postAttackDice' => array(
                'attacker' => array(
                    array('recipe' => 'mt(4)', 'min' => 1, 'max' => 4, 'value' => 3, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => 'mt(4):3', 'valueAfterTripAttack' => 2, 'hasJustMorphed' => TRUE),
                ),
                'defender' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 1, 'doesReroll' => TRUE, 'captured' => TRUE, 'recipeStatus' => '(4):1'),
                ),
            )
        ));
        $this->assertEquals(
            "gameaction01 performed Trip attack using [mt(2):1] against [(4):3]; Attacker mt(2) rerolled 1 => 2; Defender (4) rerolled 3 => 1, was captured; Attacker mt(2) changed size from 2 to 4 sides, recipe changed from mt(2) to mt(4), rerolled 2 => 3",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_choose_die_values()
     */
    public function test_friendly_message_choose_die_values() {
        $this->object = new BMGameAction(
            24, 'choose_die_values', 1,
            array('roundNumber' => 1, 'swingValues' => array('X' => 5, 'Y' => 13), 'optionValues' => array()));
        $this->assertEquals(
            "gameaction01 set swing values: X=5, Y=13",
            $this->object->friendly_message($this->playerIdNames, 2, 24)
        );
        $this->assertEquals(
            "gameaction01 set die sizes",
            $this->object->friendly_message($this->playerIdNames, 1, 24)
        );

        $this->object = new BMGameAction(24, 'choose_die_values', 1, array('roundNumber' => 1, 'swingValues' => array(), 'optionValues' => array('(3/6)' => 3, 'z(4/7)' => 7)));
        $this->assertEquals(
            "gameaction01 set option dice: (3/6=3), z(4/7=7)",
            $this->object->friendly_message($this->playerIdNames, 2, 24)
        );
        $this->assertEquals(
            "gameaction01 set die sizes",
            $this->object->friendly_message($this->playerIdNames, 1, 24)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_choose_swing()
     */
    public function test_friendly_message_choose_swing() {
        $this->object = new BMGameAction(24, 'choose_swing', 1, array('roundNumber' => 1, 'swingValues' => array('X' => 5, 'Y' => 13)));
        $this->assertEquals(
            "gameaction01 set swing values: X=5, Y=13",
            $this->object->friendly_message($this->playerIdNames, 2, 24)
        );
        $this->assertEquals(
            "gameaction01 set swing values",
            $this->object->friendly_message($this->playerIdNames, 1, 24)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_reroll_chance()
     */
    public function test_friendly_message_reroll_chance() {
        $this->object = new BMGameAction(27, 'reroll_chance', 2, array(
            'preReroll' => array('recipe' => 'c(20)', 'min' => 1, 'max' => 20, 'value' => 4, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => 'c(20):4'),
            'postReroll' => array('recipe' => 'c(20)', 'min' => 1, 'max' => 20, 'value' => 11, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => 'c(20):11'),
            'gainedInitiative' => FALSE,
        ));
        $this->assertEquals(
            "gameaction02 rerolled a chance die, but did not gain initiative: c(20) rerolled 4 => 11",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_turndown_focus()
     */
    public function test_friendly_message_turndown_focus() {
        $this->object = new BMGameAction(27, 'turndown_focus', 1, array(
            'preTurndown' => array(array('recipe' => 'f(20)', 'min' => 1, 'max' => 20, 'value' => 4, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => 'f(20):4')),
            'postTurndown' => array(array('recipe' => 'f(20)', 'min' => 1, 'max' => 20, 'value' => 2, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => 'f(20):2')),
            'gainedInitiative' => FALSE,
        ));
        $this->assertEquals(
            "gameaction01 gained initiative by turning down focus dice: f(20) from 4 to 2",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_init_decline()
     */
    public function test_friendly_message_init_decline() {
        $this->object = new BMGameAction(27, 'init_decline', 2, array('initDecline' => TRUE));
        $this->assertEquals(
            "gameaction02 chose not to try to gain initiative using chance or focus dice",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_add_reserve()
     */
    public function test_friendly_message_add_reserve() {
        $this->object = new BMGameAction(22, 'add_reserve', 2, array(
            'die' => array('recipe' => 'r(6)', 'min' => 1, 'max' => 6, 'value' => NULL, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => 'r(6):')
        ));
        $this->assertEquals(
            "gameaction02 added a reserve die: r(6)",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_decline_reserve()
     */
    public function test_friendly_message_decline_reserve() {
        $this->object = new BMGameAction(22, 'decline_reserve', 2, array('declineReserve' => TRUE));
        $this->assertEquals(
            "gameaction02 chose not to add a reserve die",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_add_auxiliary()
     */
    public function test_friendly_message_add_auxiliary() {
        $this->object = new BMGameAction(20, 'add_auxiliary', 2, array('roundNumber' => 1,
            'die' => array('recipe' => '+(6)', 'min' => 1, 'max' => 6, 'value' => NULL, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '+(6):')));
        $this->assertEquals(
            "gameaction02 chose to use auxiliary die +(6) in this game",
            $this->object->friendly_message($this->playerIdNames, 2, 20)
        );
        $this->assertEquals(
            "",
            $this->object->friendly_message($this->playerIdNames, 1, 20)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_decline_auxiliary()
     */
    public function test_friendly_message_decline_auxiliary() {
        $this->object = new BMGameAction(20, 'decline_auxiliary', 2, array('declineAuxiliary' => TRUE));
        $this->assertEquals(
            "gameaction02 chose not to use auxiliary dice in this game: neither player will get an auxiliary die",
                $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }

    /**
     * @covers BMGameAction::friendly_message_determine_initiative()
     */
    public function test_friendly_message_determine_initiative() {
        $testParams = array(
            'roundNumber' => 1,
            'playerData' => array(
                '1' => array(
                    'initiativeDice' => array(
                        array('recipe' => '(6)', 'min' => 1, 'max' => 6, 'value' => 3, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(6):3', 'included' => true),
                        array('recipe' => '(10)', 'min' => 1, 'max' => 10, 'value' => 1, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(10):1', 'included' => true),
                    ),
                    'slowButton' => false,
                ),
                '2' => array(
                    'initiativeDice' => array(
                        array('recipe' => '(6)', 'min' => 1, 'max' => 6, 'value' => 2, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(6):2', 'included' => true),
                        array('recipe' => '(10)', 'min' => 1, 'max' => 10, 'value' => 2, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '(10):2', 'included' => true),
                    ),
                    'slowButton' => false,
                ),
            ),
            'initiativeWinnerId' => 1,
        );

        $this->object = new BMGameAction(26, 'determine_initiative', 0, $testParams);
        $this->assertEquals(
            "gameaction01 won initiative for round 1. Initial die values: gameaction01 rolled [(6):3, (10):1], gameaction02 rolled [(6):2, (10):2].",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );

        $testParams['playerData']['1']['slowButton'] = true;
        $testParams['initiativeWinnerId'] = 2;
        $this->object = new BMGameAction(26, 'determine_initiative', 0, $testParams);
        $this->assertEquals(
            "gameaction02 won initiative for round 1. Initial die values: gameaction01 rolled [(6):3, (10):1], gameaction02 rolled [(6):2, (10):2]. gameaction01's button has the \"slow\" button special, and cannot win initiative normally.",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );

        $testParams['playerData']['2']['slowButton'] = true;
        $testParams['tiedPlayerIds'] = array(1, 2);
        $this->object = new BMGameAction(26, 'determine_initiative', 0, $testParams);
        $this->assertEquals(
            "gameaction02 won initiative for round 1. Initial die values: gameaction01 rolled [(6):3, (10):1], gameaction02 rolled [(6):2, (10):2]. Both buttons have the \"slow\" button special, and cannot win initiative normally. Initiative was determined by a coin flip.",
            $this->object->friendly_message($this->playerIdNames, 0, 0)
        );
    }
}

?>
