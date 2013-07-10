<?php

class BMInterfaceTest extends PHPUnit_Framework_TestCase {

    /**
     * @var BMInterface
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        require 'src/database/mysql.test.inc.php';
        $this->object = new BMInterface(TRUE);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @covers BMInterface::create_game
     * @covers BMInterface::load_game
     */
    public function test_create_and_load_new_game() {
        $gameId = $this->object->create_game(array(1, 2), array('Bauer', 'Stark'), 4);
        $game = $this->object->load_game($gameId);

        // check player info
        $this->assertEquals(2, count($game->playerIdArray));
        $this->assertEquals(2, $game->nPlayers);
        $this->assertEquals(1, $game->playerIdArray[0]);
        $this->assertEquals(2, $game->playerIdArray[1]);
        $this->assertEquals(BMGameState::startGame, $game->gameState);
        $this->assertFalse(isset($game->activePlayerIdx));
        $this->assertFalse(isset($game->playerWithInitiativeIdx));
        $this->assertFalse(isset($game->attackerPlayerIdx));
        $this->assertFalse(isset($game->defenderPlayerIdx));
        $this->assertEquals(array(FALSE, FALSE), $game->isPrevRoundWinnerArray);

        // check buttons
        $this->assertEquals(2, count($game->buttonArray));
        $this->assertTrue(is_a($game->buttonArray[0], 'BMButton'));
        $this->assertEquals('Bauer', $game->buttonArray[0]->name);
        $this->assertEquals('(8) (10) (12) (20) (X)', $game->buttonArray[0]->recipe);

        $this->assertTrue(is_a($game->buttonArray[1], 'BMButton'));
        $this->assertEquals('Stark', $game->buttonArray[1]->name);
        $this->assertEquals('(4) (6) (8) (X) (X)', $game->buttonArray[1]->recipe);

        // check dice
        $this->assertFalse(isset($game->activeDieArrayArray));
        $this->assertFalse(isset($game->attackerAllDieArray));
        $this->assertFalse(isset($game->defenderAllDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->attackerAttackDieArray));
        $this->assertFalse(isset($game->auxiliaryDieDecisionArrayArray));
        $this->assertFalse(isset($game->capturedDieArrayArray));
        $this->assertFalse(isset($game->swingRequestArrayArray));
        $this->assertFalse(isset($game->swingValueArrayArray));
        $this->assertFalse($game->allValuesSpecified);

        // check round info
        $this->assertEquals(1, $game->roundNumber);
        $this->assertEquals(4, $game->maxWins);

        // check action info
        $this->assertFalse(isset($game->attack));
        $this->assertFalse(isset($game->passStatusArray));
        $this->assertEquals(array(FALSE, FALSE), $game->waitingOnActionArray);

        // check score
        $this->assertFalse(isset($game->roundScoreArray));
        $this->assertEquals(2, count($game->gameScoreArrayArray));
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['W']);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['L']);
        $this->assertEquals(0, $game->gameScoreArrayArray[0]['D']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['W']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['L']);
        $this->assertEquals(0, $game->gameScoreArrayArray[1]['D']);


// * @property      array $swingRequestArrayArray  Swing requests for all players
// * @property      array $swingValueArrayArray    Swing values for all players
// * @property    boolean $allValuesSpecified      Boolean flag of whether all swing values have been specified
// *

    }
}
