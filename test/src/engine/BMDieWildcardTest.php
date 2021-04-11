<?php

class BMDieWildcardTest extends PHPUnit_Framework_TestCase {

    /**
     * @var BMDieWildcard
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new BMDieWildcard;
        $this->object->init(0);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    protected static function getMethod($name) {
        $class = new ReflectionClass('BMDieWildcard');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @covers BMDieWildcard::displayed_value
     */
    public function testDisplayed_value() {
        $this->object->currentCardId = 0;
        $this->assertEquals('', $this->object->displayed_value(TRUE));
        $this->assertEquals('', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 1;
        $this->assertEquals('A<span class="suit_black">&clubs;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('AC', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 2;
        $this->assertEquals('2<span class="suit_black">&clubs;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('2C', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 9;
        $this->assertEquals('9<span class="suit_black">&clubs;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('9C', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 10;
        $this->assertEquals('10<span class="suit_black">&clubs;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('10C', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 11;
        $this->assertEquals('J<span class="suit_black">&clubs;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('JC', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 12;
        $this->assertEquals('Q<span class="suit_black">&clubs;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('QC', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 13;
        $this->assertEquals('K<span class="suit_black">&clubs;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('KC', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 14;
        $this->assertEquals('A<span class="suit_red">&diams;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('AD', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 15;
        $this->assertEquals('2<span class="suit_red">&diams;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('2D', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 22;
        $this->assertEquals('9<span class="suit_red">&diams;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('9D', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 23;
        $this->assertEquals('10<span class="suit_red">&diams;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('10D', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 24;
        $this->assertEquals('J<span class="suit_red">&diams;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('JD', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 25;
        $this->assertEquals('Q<span class="suit_red">&diams;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('QD', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 26;
        $this->assertEquals('K<span class="suit_red">&diams;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('KD', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 27;
        $this->assertEquals('A<span class="suit_red">&hearts;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('AH', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 28;
        $this->assertEquals('2<span class="suit_red">&hearts;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('2H', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 35;
        $this->assertEquals('9<span class="suit_red">&hearts;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('9H', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 36;
        $this->assertEquals('10<span class="suit_red">&hearts;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('10H', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 37;
        $this->assertEquals('J<span class="suit_red">&hearts;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('JH', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 38;
        $this->assertEquals('Q<span class="suit_red">&hearts;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('QH', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 39;
        $this->assertEquals('K<span class="suit_red">&hearts;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('KH', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 40;
        $this->assertEquals('A<span class="suit_black">&spades;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('AS', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 41;
        $this->assertEquals('2<span class="suit_black">&spades;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('2S', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 48;
        $this->assertEquals('9<span class="suit_black">&spades;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('9S', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 49;
        $this->assertEquals('10<span class="suit_black">&spades;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('10S', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 50;
        $this->assertEquals('J<span class="suit_black">&spades;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('JS', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 51;
        $this->assertEquals('Q<span class="suit_black">&spades;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('QS', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 52;
        $this->assertEquals('K<span class="suit_black">&spades;</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('KS', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 53;
        $this->assertEquals('<span class="suit_red">Jkr</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('Jkr (red)', $this->object->displayed_value(FALSE));

        $this->object->currentCardId = 54;
        $this->assertEquals('<span class="suit_black">Jkr</span>', $this->object->displayed_value(TRUE));
        $this->assertEquals('Jkr (black)', $this->object->displayed_value(FALSE));
    }

    /**
     * @covers BMDieWildcard::numeric_value
     */
    public function testNumeric_value() {
        $this->object->currentCardId = 0;
        $this->assertEquals(NULL, $this->object->numeric_value());

        $this->object->currentCardId = 1;
        $this->assertEquals(1, $this->object->numeric_value());

        $this->object->currentCardId = 2;
        $this->assertEquals(2, $this->object->numeric_value());

        $this->object->currentCardId = 9;
        $this->assertEquals(9, $this->object->numeric_value());

        $this->object->currentCardId = 10;
        $this->assertEquals(10, $this->object->numeric_value());

        $this->object->currentCardId = 11;
        $this->assertEquals(11, $this->object->numeric_value());

        $this->object->currentCardId = 12;
        $this->assertEquals(12, $this->object->numeric_value());

        $this->object->currentCardId = 13;
        $this->assertEquals(13, $this->object->numeric_value());

        $this->object->currentCardId = 14;
        $this->assertEquals(1, $this->object->numeric_value());

        $this->object->currentCardId = 15;
        $this->assertEquals(2, $this->object->numeric_value());

        $this->object->currentCardId = 22;
        $this->assertEquals(9, $this->object->numeric_value());

        $this->object->currentCardId = 23;
        $this->assertEquals(10, $this->object->numeric_value());

        $this->object->currentCardId = 24;
        $this->assertEquals(11, $this->object->numeric_value());

        $this->object->currentCardId = 25;
        $this->assertEquals(12, $this->object->numeric_value());

        $this->object->currentCardId = 26;
        $this->assertEquals(13, $this->object->numeric_value());

        $this->object->currentCardId = 27;
        $this->assertEquals(1, $this->object->numeric_value());

        $this->object->currentCardId = 28;
        $this->assertEquals(2, $this->object->numeric_value());

        $this->object->currentCardId = 35;
        $this->assertEquals(9, $this->object->numeric_value());

        $this->object->currentCardId = 36;
        $this->assertEquals(10, $this->object->numeric_value());

        $this->object->currentCardId = 37;
        $this->assertEquals(11, $this->object->numeric_value());

        $this->object->currentCardId = 38;
        $this->assertEquals(12, $this->object->numeric_value());

        $this->object->currentCardId = 39;
        $this->assertEquals(13, $this->object->numeric_value());

        $this->object->currentCardId = 40;
        $this->assertEquals(1, $this->object->numeric_value());

        $this->object->currentCardId = 41;
        $this->assertEquals(2, $this->object->numeric_value());

        $this->object->currentCardId = 48;
        $this->assertEquals(9, $this->object->numeric_value());

        $this->object->currentCardId = 49;
        $this->assertEquals(10, $this->object->numeric_value());

        $this->object->currentCardId = 50;
        $this->assertEquals(11, $this->object->numeric_value());

        $this->object->currentCardId = 51;
        $this->assertEquals(12, $this->object->numeric_value());

        $this->object->currentCardId = 52;
        $this->assertEquals(13, $this->object->numeric_value());

        $this->object->currentCardId = 53;
        $this->assertEquals(20, $this->object->numeric_value());

        $this->object->currentCardId = 54;
        $this->assertEquals(20, $this->object->numeric_value());
    }

    /**
     * @covers BMDieWildcard::init
     */
    public function testInit() {
        $this->object->init(53, array('Poison', 'Fire'));

        $this->assertEquals(0, $this->object->currentCardId);
        $this->assertEquals(1, $this->object->min);
        $this->assertEquals(20, $this->object->max);
        $this->assertTrue($this->object->has_skill('Poison'));
        $this->assertTrue($this->object->has_skill('Fire'));
        $this->assertFalse($this->object->has_skill('Shadow'));
    }

    /**
     * @covers BMDieWildcard::create
     */
    public function testCreate() {
        $die = BMDieWildcard::create(5, array('Poison', 'Fire'));

        $this->assertEquals(0, $die->currentCardId);
        $this->assertEquals(1, $die->min);
        $this->assertEquals(20, $die->max);
        $this->assertTrue($die->has_skill('Poison'));
        $this->assertTrue($die->has_skill('Fire'));
        $this->assertFalse($die->has_skill('Shadow'));
    }

    /**
     * @covers BMDieWildcard::select_new_value
     */
    public function testSelect_new_value() {
        $select_new_value = self::getMethod('select_new_value');

        // check that nothing breaks when there is no owner object
        $select_new_value->invoke($this->object);

        // now check with a proper owner object
        $game = new BMGame;
        $player0 = $game->playerArray[0];
        $player1 = $game->playerArray[1];
        $this->object->ownerObject = $game;
        $this->object->playerIdx = 1;

        // test for the case where the whole deck has been drawn
        $cardsDrawn = array();
        for ($val = 1; $val <= 54; $val++) {
          $cardsDrawn[$val] = TRUE;
        }
        $player1->cardsDrawn = $cardsDrawn;
        $game->playerArray = array($player0, $player1);
        $select_new_value->invoke($this->object);
        $this->assertCount(0, $game->playerArray[0]->cardsDrawn);
        $this->assertCount(1, $game->playerArray[1]->cardsDrawn);
        $this->assertTrue($game->playerArray[1]->cardsDrawn[$this->object->currentCardId]);

        // test for the case where the whole deck bar one has been drawn
        $cardsDrawn = array();
        for ($val = 1; $val <= 54; $val++) {
          if (35 === $val) {
              continue;
          }
          $cardsDrawn[$val] = TRUE;
        }
        $player1->cardsDrawn = $cardsDrawn;
        $game->playerArray = array($player0, $player1);
        $select_new_value->invoke($this->object);
        $this->assertCount(0, $game->playerArray[0]->cardsDrawn);
        $this->assertCount(54, $game->playerArray[1]->cardsDrawn);
        $this->assertEquals(35, $this->object->currentCardId);

        // test for the case where the deck is fresh
        $player1->cardsDrawn = array();
        $game->playerArray = array($player0, $player1);
        $select_new_value->invoke($this->object);
        $this->assertCount(0, $game->playerArray[0]->cardsDrawn);
        $this->assertCount(1, $game->playerArray[1]->cardsDrawn);
        $this->assertTrue($game->playerArray[1]->cardsDrawn[$this->object->currentCardId]);
    }

    /**
     * @covers BMDieWildcard::attack_values
     */
    public function testAttack_values() {
        $this->object->currentCardId = 1;
        $this->object->set_value_from_id();
        $this->assertEquals(array(14), $this->object->attack_values('Power'));
        $this->assertEquals(array(1), $this->object->attack_values('Skill'));

        $this->object->currentCardId = 2;
        $this->object->set_value_from_id();
        $this->assertEquals(array(2), $this->object->attack_values('Power'));
        $this->assertEquals(array(2), $this->object->attack_values('Skill'));

        $this->object->currentCardId = 10;
        $this->object->set_value_from_id();
        $this->assertEquals(array(10), $this->object->attack_values('Power'));
        $this->assertEquals(array(10), $this->object->attack_values('Skill'));

        $this->object->currentCardId = 13;
        $this->object->set_value_from_id();
        $this->assertEquals(array(13), $this->object->attack_values('Power'));
        $this->assertEquals(array(13), $this->object->attack_values('Skill'));

        $this->object->currentCardId = 14;
        $this->object->set_value_from_id();
        $this->assertEquals(array(14), $this->object->attack_values('Power'));
        $this->assertEquals(array(1), $this->object->attack_values('Skill'));

        $this->object->currentCardId = 15;
        $this->object->set_value_from_id();
        $this->assertEquals(array(2), $this->object->attack_values('Power'));
        $this->assertEquals(array(2), $this->object->attack_values('Skill'));

        $this->object->currentCardId = 27;
        $this->object->set_value_from_id();
        $this->assertEquals(array(14), $this->object->attack_values('Power'));
        $this->assertEquals(array(1), $this->object->attack_values('Skill'));

        $this->object->currentCardId = 28;
        $this->object->set_value_from_id();
        $this->assertEquals(array(2), $this->object->attack_values('Power'));
        $this->assertEquals(array(2), $this->object->attack_values('Skill'));

        $this->object->currentCardId = 40;
        $this->object->set_value_from_id();
        $this->assertEquals(array(14), $this->object->attack_values('Power'));
        $this->assertEquals(array(1), $this->object->attack_values('Skill'));

        $this->object->currentCardId = 41;
        $this->object->set_value_from_id();
        $this->assertEquals(array(2), $this->object->attack_values('Power'));
        $this->assertEquals(array(2), $this->object->attack_values('Skill'));

        $this->object->currentCardId = 52;
        $this->object->set_value_from_id();
        $this->assertEquals(array(13), $this->object->attack_values('Power'));
        $this->assertEquals(array(13), $this->object->attack_values('Skill'));

        $this->object->currentCardId = 53;
        $this->object->set_value_from_id();
        $this->assertEquals(array(20), $this->object->attack_values('Power'));
        $this->assertEquals(array(20), $this->object->attack_values('Skill'));

        $this->object->currentCardId = 54;
        $this->object->set_value_from_id();
        $this->assertEquals(array(20), $this->object->attack_values('Power'));
        $this->assertEquals(array(20), $this->object->attack_values('Skill'));
    }

    /**
     * @covers BMDieWildcard::set_value_from_id
     */
    public function testSet_value_from_id() {
        $this->object->currentCardId = 0;
        $this->object->set_value_from_id();
        $this->assertNull($this->object->value);

        $this->object->currentCardId = 55;
        try {
            $this->object->set_value_from_id();
            $this->fail('Over-large currentCardId did not throw an exception when converting to value');
        } catch (LogicException $e) {
        }

        $this->object->currentCardId = 19.5;
        try {
            $this->object->set_value_from_id();
            $this->fail('Non-integer currentCardId did not throw an exception when converting to value');
        } catch (LogicException $e) {
        }

        $this->object->currentCardId = 1;
        $this->object->set_value_from_id();
        $this->assertEquals(1, $this->object->value);

        $this->object->currentCardId = 10;
        $this->object->set_value_from_id();
        $this->assertEquals(10, $this->object->value);

        $this->object->currentCardId = 13;
        $this->object->set_value_from_id();
        $this->assertEquals(13, $this->object->value);

        $this->object->currentCardId = 14;
        $this->object->set_value_from_id();
        $this->assertEquals(1, $this->object->value);

        $this->object->currentCardId = 26;
        $this->object->set_value_from_id();
        $this->assertEquals(13, $this->object->value);

        $this->object->currentCardId = 27;
        $this->object->set_value_from_id();
        $this->assertEquals(1, $this->object->value);

        $this->object->currentCardId = 39;
        $this->object->set_value_from_id();
        $this->assertEquals(13, $this->object->value);

        $this->object->currentCardId = 40;
        $this->object->set_value_from_id();
        $this->assertEquals(1, $this->object->value);

        $this->object->currentCardId = 52;
        $this->object->set_value_from_id();
        $this->assertEquals(13, $this->object->value);

        $this->object->currentCardId = 53;
        $this->object->set_value_from_id();
        $this->assertEquals(20, $this->object->value);

        $this->object->currentCardId = 54;
        $this->object->set_value_from_id();
        $this->assertEquals(20, $this->object->value);
    }

    /**
     * @covers BMDieWildcard::get_raw_score_value
     */
    public function testGet_raw_score_value() {
        $select_new_value = self::getMethod('get_raw_score_value');
        $this->assertEquals(16, $select_new_value->invoke($this->object));
    }

    /**
     * @covers BMDieWildcard::die_size_string
     */
    public function testDie_size_string() {
        $select_new_value = self::getMethod('die_size_string');
        $this->assertEquals('Wildcard die', $select_new_value->invoke($this->object));
    }

    /**
     * @covers BMDieWildcard::split
     */
    public function testSplit() {
        $splitDice = $this->object->split();
        $this->assertCount(1, $splitDice);
        $this->assertSame($this->object, $splitDice[0]);
    }

    /**
     * @covers BMDieWildcard::shrink
     */
    public function testShrink() {
        $this->object->shrink();
        $this->assertEquals(20, $this->object->max);
    }

    /**
     * @covers BMDieWildcard::grow
     */
    public function testGrow() {
        $this->object->grow();
        $this->assertEquals(20, $this->object->max);
    }

    /**
     * @covers BMDie::describe
     */
    public function testDescribe() {
        $this->assertEquals('Wildcard die', $this->object->describe(FALSE));
        $this->assertEquals('Wildcard die', $this->object->describe(TRUE));
    }

    /**
     * @covers BMDieWildcard::getDieTypes
     */
    public function testGetDieTypes() {
        $dieTypes = $this->object->getDieTypes();
        $this->assertTrue(array_key_exists('Wildcard', $dieTypes));
        $this->assertEquals('C', $dieTypes['Wildcard']['code']);
    }

    /**
     * @covers BMDieWildcard::getDescription
     */
    public function testGetDescription() {
        $this->assertNotEquals('', BMDieWildcard::getDescription());
    }
}
