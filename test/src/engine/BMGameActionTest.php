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
            $this->object->friendly_message($this->playerIdNames, 0, 0),
            "gameaction01 performed Power attack using [(X):1] against [(4):1]; Defender (4) was captured; Attacker (X) rerolled 1 => 2"
        );
    }

    /**
     * @covers BMGameAction::friendly_message_end_draw()
     */
    public function test_friendly_message_end_draw() {
        $this->object = new BMGameAction(50, 'end_draw', 0, array('roundNumber' => 2, 'roundScoreArray' => array(23, 23)));
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames, 0, 0),
            "Round 2 ended in a draw (23 vs. 23)"
        );
    }

    /**
     * @covers BMGameAction::friendly_message_end_winner()
     */
    public function test_friendly_message_end_winner() {
        $this->object = new BMGameAction(50, 'end_winner', 2, array('roundNumber' => 1, 'roundScoreArray' => array(24, 43), 'resultForced' => NULL));
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames, 0, 0),
            "End of round: gameaction02 won round 1 (43 vs. 24)"
        );

        $this->object = new BMGameAction(50, 'end_winner', 2, array('roundNumber' => 2, 'roundScoreArray' => array(25, 23), 'resultForced' => array(FALSE, TRUE)));
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames, 0, 0),
            "End of round: gameaction02 won round 2 because opponent surrendered"
        );
    }

    /**
     * @covers BMGameAction::friendly_message_attack()
     */
    public function test_friendly_message_attack() {
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
            $this->object->friendly_message($this->playerIdNames, 0, 0),
            "gameaction01 performed Power attack using [(4):3] against [(10):1]; Defender (10) was captured; Attacker (4) rerolled 3 => 2"
        );

        $this->object = new BMGameAction(40, 'attack', 2, array(
            'attackType' => 'Surrender',
            'preAttackDice' => array( 'attacker' => array(), 'defender' => array(), ),
            'postAttackDice' => array( 'attacker' => array(), 'defender' => array(), ),
        ));
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames, 0, 0),
            "gameaction02 surrendered"
        );

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
                    array('recipe' => 't(2)', 'min' => 1, 'max' => 2, 'value' => 2, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => 't(2):2'),
                ),
                'defender' => array(
                    array('recipe' => '(4)', 'min' => 1, 'max' => 4, 'value' => 1, 'doesReroll' => TRUE, 'captured' => TRUE, 'recipeStatus' => '(4):1'),
                ),
            )
        ));
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames, 0, 0),
            "gameaction01 performed Trip attack using [t(2):1] against [(4):3]; Defender (4) rerolled 3 => 1, was captured; Attacker t(2) rerolled 1 => 2"
        );
    }

    /**
     * @covers BMGameAction::friendly_message_choose_swing()
     */
    public function test_friendly_message_choose_swing() {
        $this->object = new BMGameAction(24, 'choose_swing', 1, array('roundNumber' => 1, 'swingValues' => array('X' => 5, 'Y' => 13)));
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames, 2, 24),
            "gameaction01 set swing values: X=5, Y=13"
        );
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames, 1, 24),
            "gameaction01 set swing values"
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
            $this->object->friendly_message($this->playerIdNames, 0, 0),
            "gameaction02 rerolled a chance die, but did not gain initiative: c(20) rerolled 4 => 11"
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
            $this->object->friendly_message($this->playerIdNames, 0, 0),
            "gameaction01 gained initiative by turning down focus dice: f(20) from 4 to 2"
        );
    }

    /**
     * @covers BMGameAction::friendly_message_init_decline()
     */
    public function test_friendly_message_init_decline() {
        $this->object = new BMGameAction(27, 'init_decline', 2, array('initDecline' => TRUE));
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames, 0, 0),
            "gameaction02 chose not to try to gain initiative using chance or focus dice"
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
            $this->object->friendly_message($this->playerIdNames, 0, 0),
            "gameaction02 added a reserve die: r(6)"
        );
    }

    /**
     * @covers BMGameAction::friendly_message_decline_reserve()
     */
    public function test_friendly_message_decline_reserve() {
        $this->object = new BMGameAction(22, 'decline_reserve', 2, array('declineReserve' => TRUE));
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames, 0, 0),
            "gameaction02 chose not to add a reserve die"
        );
    }

    /**
     * @covers BMGameAction::friendly_message_add_auxiliary()
     */
    public function test_friendly_message_add_auxiliary() {
        $this->object = new BMGameAction(20, 'add_auxiliary', 2, array('roundNumber' => 1,
            'die' => array('recipe' => '+(6)', 'min' => 1, 'max' => 6, 'value' => NULL, 'doesReroll' => TRUE, 'captured' => FALSE, 'recipeStatus' => '+(6):')));
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames, 2, 20),
            "gameaction02 chose to use auxiliary die +(6) in this game"
        );
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames, 1, 20),
            ""
        );
    }

    /**
     * @covers BMGameAction::friendly_message_decline_auxiliary()
     */
    public function test_friendly_message_decline_auxiliary() {
        $this->object = new BMGameAction(20, 'decline_auxiliary', 2, array('declineAuxiliary' => TRUE));
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames, 0, 0),
            "gameaction02 chose not to use auxiliary dice in this game: neither player will get an auxiliary die"
        );
    }
}

?>
