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
    }

    /**
     * @covers BMGameAction::friendly_message()
     */
    public function test_friendly_message() {
        $attackStr = "performed Power attack using [(X):1] against [(4):1]; Defender (4) was captured; Attacker (X) rerolled 1 => 2";
        $this->object = new BMGameAction(40, 'attack', 1, $attackStr);
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames),
            "gameaction01 performed Power attack using [(X):1] against [(4):1]; Defender (4) was captured; Attacker (X) rerolled 1 => 2"
        );
    }

    /**
     * @covers BMGameAction::friendly_message_end_draw()
     */
    public function test_friendly_message_end_draw() {
        $this->object = new BMGameAction(50, 'end_draw', 0, array('roundNumber' => 2, 'roundScoreArray' => array(23, 23)));
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames),
            "Round 2 ended in a draw (23 vs. 23)"
        );
    }

    /**
     * @covers BMGameAction::friendly_message_end_winner()
     */
    public function test_friendly_message_end_winner() {
        $this->object = new BMGameAction(50, 'end_winner', 2, array('roundNumber' => 1, 'roundScoreArray' => array(24, 43)));
        $this->assertEquals(
            $this->object->friendly_message($this->playerIdNames),
            "End of round: gameaction02 won round 1 (43 vs. 24)"
        );
    }
}

?>
