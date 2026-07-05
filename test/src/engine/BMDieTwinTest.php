<?php

class BMDieTwinTest extends PHPUnit\Framework\TestCase {

    /**
     * @var BMDieTwin
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void {
        $this->object = new BMDieTwin;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void {

    }

    protected static function getMethod($name) {
        $class = new ReflectionClass('BMDieTwin');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @covers BMDieTwin::init
     */
    public function testInit() {
        try {
            $this->object->init(1,
                                array("TestDummyBMSkillTesting" => "Testing"));
            $this->fail('sidesArray must be an array.');
        } catch (InvalidArgumentException $e) {
        }

        try {
            $this->object->init(array(2, 3, 4),
                                array("TestDummyBMSkillTesting" => "Testing"));
            $this->fail('sidesArray must have exactly two elements.');
        } catch (InvalidArgumentException $e) {
        }

        $this->object->init(array(6, 8),
                            array("TestDummyBMSkillTesting" => "Testing"));

        $this->assertTrue($this->object->has_skill("Testing"));
        $this->assertCount(2, $this->object->dice);
        $this->assertInstanceOf('BMDie', $this->object->dice[0]);
        $this->assertInstanceOf('BMDie', $this->object->dice[1]);
        $this->assertEquals(1, $this->object->dice[0]->min);
        $this->assertEquals(6, $this->object->dice[0]->max);
        $this->assertEquals(1, $this->object->dice[1]->min);
        $this->assertEquals(8, $this->object->dice[1]->max);
        $this->assertEquals($this->object->min, 2);
        $this->assertEquals($this->object->max, 14);

        $this->object->init(array(2, 'X'),
                            array("TestDummyBMSkillTesting2" => "Testing2"));

        $this->assertTrue($this->object->has_skill("Testing2"));
        $this->assertCount(2, $this->object->dice);
        $this->assertInstanceOf('BMDie', $this->object->dice[0]);
        $this->assertNotInstanceOf('BMDieSwing', $this->object->dice[0]);
        $this->assertInstanceOf('BMDieSwing', $this->object->dice[1]);
        $this->assertEquals(1, $this->object->dice[0]->min);
        $this->assertEquals(2, $this->object->dice[0]->max);
        $this->assertEquals(1, $this->object->dice[1]->min);
        $this->assertNull($this->object->dice[1]->max);
        $this->assertNull($this->object->min);
        $this->assertNull($this->object->max);

        // init does not remove old skills, or otherwise reset variables
        // at the moment. It's for working on brand-new dice
        $this->assertTrue($this->object->has_skill("Testing"));

        $this->object->init(array('X', 2),
                            array("TestDummyBMSkillTesting2" => "Testing2"));

        $this->assertTrue($this->object->has_skill("Testing2"));
        $this->assertCount(2, $this->object->dice);
        $this->assertInstanceOf('BMDieSwing', $this->object->dice[0]);
        $this->assertInstanceOf('BMDie', $this->object->dice[1]);
        $this->assertNotInstanceOf('BMDieSwing', $this->object->dice[1]);
        $this->assertEquals(1, $this->object->dice[0]->min);
        $this->assertNull($this->object->dice[0]->max);
        $this->assertEquals(1, $this->object->dice[1]->min);
        $this->assertEquals(2, $this->object->dice[1]->max);
        $this->assertNull($this->object->min);
        $this->assertNull($this->object->max);

        try {
            $this->object->init(array('R', 'S'), array());
            $this->fail('A twin die can only have one swing type.');
        } catch (InvalidArgumentException $e) {
        }

        $this->object->init(array('R', 'R'), array());

        $this->assertCount(2, $this->object->dice);
        $this->assertInstanceOf('BMDieSwing', $this->object->dice[0]);
        $this->assertInstanceOf('BMDieSwing', $this->object->dice[1]);
        $this->assertEquals(1, $this->object->dice[0]->min);
        $this->assertNull($this->object->dice[0]->max);
        $this->assertEquals(1, $this->object->dice[1]->min);
        $this->assertNull($this->object->dice[1]->max);
        $this->assertNull($this->object->min);
        $this->assertNull($this->object->max);
    }

    /**
     * @covers BMDieTwin::create
     *
     * @depends testInit
     */
    public function testCreate() {
        try {
            $die = BMDieTwin::create(9, array());
            $this->fail('sidesArray must be an array');
        }
        catch (InvalidArgumentException $e) {
        }

        try {
            $die = BMDieTwin::create(array(9), array());
            $this->fail('A twin die must be created with two values.');
        }
        catch (InvalidArgumentException $e) {
        }

        $die = BMDieTwin::create(array(4, 7), array());

        $this->assertInstanceOf('BMDieTwin', $die);
        $this->assertCount(2, $die->dice);
        $this->assertInstanceOf('BMDie', $die->dice[0]);
        $this->assertInstanceOf('BMDie', $die->dice[1]);
        $this->assertEquals( 1, $die->dice[0]->min);
        $this->assertEquals( 4, $die->dice[0]->max);
        $this->assertEquals( 1, $die->dice[1]->min);
        $this->assertEquals( 7, $die->dice[1]->max);
        $this->assertEquals( 2, $die->min);
        $this->assertEquals(11, $die->max);

        $die = BMDieTwin::create(array('X', 7), array('Shadow'));

        $this->assertInstanceOf('BMDieTwin', $die);
        $this->assertCount(2, $die->dice);
        $this->assertTrue($die->has_skill('Shadow'));
        $this->assertInstanceOf('BMDieSwing', $die->dice[0]);
        $this->assertInstanceOf('BMDie', $die->dice[1]);
        $this->assertEquals( 1, $die->dice[0]->min);
        $this->assertNull($die->dice[0]->max);
        $this->assertEquals( 1, $die->dice[1]->min);
        $this->assertEquals( 7, $die->dice[1]->max);
        $this->assertTrue($die->dice[0]->has_skill('Shadow'));
        $this->assertTrue($die->dice[1]->has_skill('Shadow'));
        $this->assertNull($die->min);
        $this->assertNull($die->max);

        $die = BMDieTwin::create(array('R', 'R'), array());

        $this->assertInstanceOf('BMDieTwin', $die);
        $this->assertCount(2, $die->dice);
        $this->assertInstanceOf('BMDieSwing', $die->dice[0]);
        $this->assertInstanceOf('BMDieSwing', $die->dice[1]);
        $this->assertEquals(1, $die->dice[0]->min);
        $this->assertNull($die->dice[0]->max);
        $this->assertEquals(1, $die->dice[1]->min);
        $this->assertNull($die->dice[1]->max);
        $this->assertNull($die->min);
        $this->assertNull($die->max);
    }

    /*
     * @covers BMDie::create_from_recipe
     */
    public function testCreate_from_recipe() {
        $die = BMDieTwin::create_from_recipe('ps(6,8)');
        $this->assertTrue($die->has_skill('Poison'));
        $this->assertTrue($die->has_skill('Shadow'));
        $this->assertEquals(6, $die->dice[0]->max);
        $this->assertEquals(8, $die->dice[1]->max);
        $this->assertEquals(14, $die->max);

        $die = BMDie::create_from_recipe('ps(X,X)');
        $this->assertInstanceOf('BMDieSwing', $die->dice[0]);
        $this->assertInstanceOf('BMDieSwing', $die->dice[1]);
        $this->assertTrue($die->has_skill('Poison'));
        $this->assertTrue($die->has_skill('Shadow'));
        $this->assertNull($die->max);
        $this->assertTrue($die->dice[0]->has_skill('Poison'));
        $this->assertTrue($die->dice[0]->has_skill('Shadow'));
        $this->assertTrue($die->dice[1]->has_skill('Poison'));
        $this->assertTrue($die->dice[1]->has_skill('Shadow'));
        $this->assertEquals('X', $die->dice[0]->swingType);
        $this->assertEquals('X', $die->dice[1]->swingType);
    }

    /**
     * @covers BMDie::create_from_string_components
     *
     * @depends testCreate
     */
    public function testCreate_from_string_components() {
        $create = self::getMethod('create_from_string_components');

        $die = $create->invokeArgs(NULL, array('4,6', array('Shadow')));
        $this->assertInstanceOf('BMDieTwin', $die);
        $this->assertCount(2, $die->dice);
        $this->assertTrue($die->has_skill('Shadow'));
        $this->assertInstanceOf('BMDie', $die->dice[0]);
        $this->assertInstanceOf('BMDie', $die->dice[1]);
        $this->assertEquals(1, $die->dice[0]->min);
        $this->assertEquals(4, $die->dice[0]->max);
        $this->assertEquals(1, $die->dice[1]->min);
        $this->assertEquals(6, $die->dice[1]->max);
        $this->assertTrue($die->dice[0]->has_skill('Shadow'));
        $this->assertTrue($die->dice[0]->has_skill('Shadow'));
        $this->assertEquals(2, $die->min);
        $this->assertEquals(10, $die->max);
    }

    /**
     * @covers BMDieTwin::roll
     *
     * @depends testInit
     */
    public function testRoll() {
        $this->object->init(array('X', 4), array());
        $this->object->roll();
        $this->assertNull($this->object->value);

        $this->object->init(array(4, 2), array());
        // check value distribution between 2 and 6
        $rolls = array_fill(2, 5, 0);


        for ($i = 0; $i < 300; $i++) {
            $this->object->roll(FALSE);
            if ($this->object->value < 2 || $this->object->value > 6) {
                $this->assertFalse(TRUE, "Die rolled out of bounds during FALSE.");
            }

            $rolls[$this->object->value]++;
        }

        for ($i = 0; $i < 300; $i++) {
            $this->object->roll(TRUE);
            if ($this->object->value < 2 || $this->object->value > 6) {
                $this->assertFalse(TRUE, "Die rolled out of bounds during TRUE.");
            }

            $rolls[$this->object->value]++;
        }

        $this->assertGreaterThan($rolls[2], $rolls[3]);
        $this->assertGreaterThan($rolls[2], $rolls[4]);
        $this->assertGreaterThan($rolls[2], $rolls[5]);
        $this->assertGreaterThan($rolls[6], $rolls[3]);
        $this->assertGreaterThan($rolls[6], $rolls[4]);
        $this->assertGreaterThan($rolls[6], $rolls[5]);
    }

    /**
     * @covers BMDie::get_scoreValueTimesTen
     *
     * @depends testInit
     */
    public function testGet_scoreValueTimesTen() {
        $this->object->init(array(5, 7), array());

        $this->assertEquals(60, $this->object->get_scoreValueTimesTen());

        $this->object->captured = TRUE;

        $this->assertEquals(120, $this->object->get_scoreValueTimesTen());

    }

    /**
     * @covers BMDie::initiative_value
     *
     * @depends testInit
     * @depends testRoll
     */
    public function testInitiative_value() {
        $this->object->init(array(3, 8), array());
        $this->object->roll(FALSE);

        $val = $this->object->initiative_value();
        $this->assertEquals($val, $this->object->value);
    }

    public function testDescribe() {
        $die1 = new BMDieTwin;

        $die1->init(array(1,1));
        $this->assertEquals('Twin Die (both with 1 side)', $die1->describe(TRUE));
        $this->assertEquals('Twin Die (both with 1 side)', $die1->describe(FALSE));

        $die1->init(array(6,6));
        $this->assertEquals('Twin Die (both with 6 sides)', $die1->describe(TRUE));
        $this->assertEquals('Twin Die (both with 6 sides)', $die1->describe(FALSE));

        $die1->roll();
        $value = $die1->value;
        $this->assertEquals(
            "Twin Die (both with 6 sides) showing {$value}",
            $die1->describe(TRUE)
        );
        $this->assertEquals('Twin Die (both with 6 sides)', $die1->describe(FALSE));

        $die1->add_skill('Poison');
        $die1->add_skill('Shadow');
        $this->assertEquals(
            "Poison Shadow Twin Die (both with 6 sides) showing {$value}",
            $die1->describe(TRUE)
        );
        $this->assertEquals(
            'Poison Shadow Twin Die (both with 6 sides)', $die1->describe(FALSE));

        $die2 = new BMDieTwin;

        $die2->init(array(1,2));
        $this->assertEquals('Twin Die (with 1 and 2 sides)', $die2->describe(TRUE));
        $this->assertEquals('Twin Die (with 1 and 2 sides)', $die2->describe(FALSE));

        $die2->init(array(2,1));
        $this->assertEquals('Twin Die (with 2 and 1 sides)', $die2->describe(TRUE));
        $this->assertEquals('Twin Die (with 2 and 1 sides)', $die2->describe(FALSE));

        $die2->init(array(6,12));
        $this->assertEquals('Twin Die (with 6 and 12 sides)', $die2->describe(TRUE));
        $this->assertEquals('Twin Die (with 6 and 12 sides)', $die2->describe(FALSE));

        $die2->roll();
        $value = $die2->value;
        $this->assertEquals(
            "Twin Die (with 6 and 12 sides) showing {$value}",
            $die2->describe(TRUE)
        );
        $this->assertEquals('Twin Die (with 6 and 12 sides)', $die2->describe(FALSE));

        $die2->add_skill('Poison');
        $die2->add_skill('Shadow');
        $this->assertEquals(
            "Poison Shadow Twin Die (with 6 and 12 sides) showing {$value}",
            $die2->describe(TRUE)
        );
        $this->assertEquals(
            'Poison Shadow Twin Die (with 6 and 12 sides)', $die2->describe(FALSE));

        $die3 = new BMDieTwin;
        $die3->init(array('Y','Y'));
        $this->assertEquals('Twin Y Swing Die', $die3->describe(TRUE));
        $this->assertEquals('Twin Y Swing Die', $die3->describe(FALSE));

        $die3->set_swingValue(array('Y' => 7));
        $this->assertEquals("Twin Y Swing Die (both with 7 sides)", $die3->describe(TRUE));
        $this->assertEquals("Twin Y Swing Die (both with 7 sides)", $die3->describe(FALSE));

        $die3->roll();
        $value = $die3->value;
        $this->assertEquals(
            "Twin Y Swing Die (both with 7 sides) showing {$value}",
            $die3->describe(TRUE)
        );
        $this->assertEquals('Twin Y Swing Die (both with 7 sides)', $die3->describe(FALSE));

        $die3->add_skill('Poison');
        $die3->add_skill('Shadow');
        $this->assertEquals(
            "Poison Shadow Twin Y Swing Die (both with 7 sides) showing {$value}",
            $die3->describe(TRUE)
        );
        $this->assertEquals(
            'Poison Shadow Twin Y Swing Die (both with 7 sides)', $die3->describe(FALSE));

        $die3->add_skill('Mood');
        $this->assertEquals(
            "Poison Shadow Twin Y Mood Swing Die (both with 7 sides) showing {$value}",
            $die3->describe(TRUE)
        );
        $this->assertEquals(
            'Poison Shadow Twin Y Mood Swing Die (both with 7 sides)', $die3->describe(FALSE));
    }

    /**
     * @covers BMDieTwin::split
     * @covers BMDieTwin::recalc_max_min
     *
     * @depends testInit
     * @depends testRoll
     */
    public function testSplit() {
        // (1,1) splits into (1,0) and (0,1)
        $this->object->init(array(1, 1), array());
        $this->object->roll(FALSE);

        $splitDice = $this->object->split();

        $this->assertFalse($splitDice[0] === $splitDice[1]);
        $this->assertEquals(1, $splitDice[0]->dice[0]->max);
        $this->assertEquals(0, $splitDice[0]->dice[1]->max);
        $this->assertEquals(0, $splitDice[1]->dice[0]->max);
        $this->assertEquals(1, $splitDice[1]->dice[1]->max);
        $this->assertEquals(1, $splitDice[0]->min);
        $this->assertEquals(1, $splitDice[0]->max);
        $this->assertEquals(1, $splitDice[1]->min);
        $this->assertEquals(1, $splitDice[1]->max);

        // even-sided split
        $this->object->init(array(12, 16), array());
        $this->object->roll(FALSE);

        $splitDice = $this->object->split();

        $this->assertFalse($splitDice[0] === $splitDice[1]);
        $this->assertTrue($this->object === $splitDice[0]);
        $this->assertEquals(6, $splitDice[0]->dice[0]->max);
        $this->assertEquals(8, $splitDice[0]->dice[1]->max);
        $this->assertEquals(6, $splitDice[1]->dice[0]->max);
        $this->assertEquals(8, $splitDice[1]->dice[1]->max);
        $this->assertEquals(2, $splitDice[0]->min);
        $this->assertEquals(14, $splitDice[0]->max);
        $this->assertEquals(2, $splitDice[1]->min);
        $this->assertEquals(14, $splitDice[1]->max);

        // odd-sided split
        $this->object->init(array(5, 9), array());
        $this->object->roll(FALSE);

        $splitDice = $this->object->split();

        $this->assertFalse($splitDice[0] === $splitDice[1]);
        $this->assertTrue($this->object === $splitDice[0]);
        $this->assertEquals(3, $splitDice[0]->dice[0]->max);
        $this->assertEquals(4, $splitDice[0]->dice[1]->max);
        $this->assertEquals(2, $splitDice[1]->dice[0]->max);
        $this->assertEquals(5, $splitDice[1]->dice[1]->max);
        $this->assertEquals(2, $splitDice[0]->min);
        $this->assertEquals(7, $splitDice[0]->max);
        $this->assertEquals(2, $splitDice[1]->min);
        $this->assertEquals(7, $splitDice[1]->max);

        // swing split
        $this->object->init(array('X', 'X'));
        $this->object->set_swingValue(array('X' => 5));
        $splitDice = $this->object->split();

        $this->assertFalse($splitDice[0] === $splitDice[1]);
        $this->assertTrue($this->object === $splitDice[0]);
        $this->assertInstanceOf('BMDieSwing', $splitDice[0]->dice[0]);
        $this->assertInstanceOf('BMDieSwing', $splitDice[0]->dice[1]);
        $this->assertInstanceOf('BMDieSwing', $splitDice[1]->dice[0]);
        $this->assertInstanceOf('BMDieSwing', $splitDice[1]->dice[1]);
        $this->assertEquals('X', $splitDice[0]->dice[0]->swingType);
        $this->assertEquals('X', $splitDice[0]->dice[1]->swingType);
        $this->assertEquals('X', $splitDice[1]->dice[0]->swingType);
        $this->assertEquals('X', $splitDice[1]->dice[1]->swingType);
        $this->assertEquals(3, $splitDice[0]->dice[0]->max);
        $this->assertEquals(5, $splitDice[0]->dice[0]->swingValue);
        $this->assertEquals(2, $splitDice[0]->dice[1]->max);
        $this->assertEquals(5, $splitDice[0]->dice[1]->swingValue);
        $this->assertEquals(2, $splitDice[1]->dice[0]->max);
        $this->assertEquals(5, $splitDice[1]->dice[0]->swingValue);
        $this->assertEquals(3, $splitDice[1]->dice[1]->max);
        $this->assertEquals(5, $splitDice[1]->dice[1]->swingValue);
        $this->assertEquals(2, $splitDice[0]->min);
        $this->assertEquals(5, $splitDice[0]->max);
        $this->assertEquals(2, $splitDice[1]->min);
        $this->assertEquals(5, $splitDice[1]->max);
    }

    /*
     * @covers BMDieTwin::shrink
     */
    public function testShrink() {
        $die = $this->object;
        $die->init(array(99, 99));
        $die->shrink();
        $this->assertEquals(2*30, $die->max);
        $die->shrink();
        $this->assertEquals(2*20, $die->max);
        $die->shrink();
        $this->assertEquals(2*16, $die->max);
        $die->shrink();
        $this->assertEquals(2*12, $die->max);
        $die->shrink();
        $this->assertEquals(2*10, $die->max);
        $die->shrink();
        $this->assertEquals(2*8, $die->max);
        $die->shrink();
        $this->assertEquals(2*6, $die->max);
        $die->shrink();
        $this->assertEquals(2*4, $die->max);
        $die->shrink();
        $this->assertEquals(2*2, $die->max);
        $die->shrink();
        $this->assertEquals(2*1, $die->max);
        $die->shrink();
        $this->assertEquals(2*1, $die->max);
    }

    /*
     * @covers BMDieTwin::grow
     */
    public function testGrow() {
        $die = $this->object;
        $die->init(array(1, 1));
        $die->grow();
        $this->assertEquals(2*2, $die->max);
        $die->grow();
        $this->assertEquals(2*4, $die->max);
        $die->grow();
        $this->assertEquals(2*6, $die->max);
        $die->grow();
        $this->assertEquals(2*8, $die->max);
        $die->grow();
        $this->assertEquals(2*10, $die->max);
        $die->grow();
        $this->assertEquals(2*12, $die->max);
        $die->grow();
        $this->assertEquals(2*16, $die->max);
        $die->grow();
        $this->assertEquals(2*20, $die->max);
        $die->grow();
        $this->assertEquals(2*30, $die->max);
        $die->grow();
        $this->assertEquals(2*30, $die->max);
    }

    /*
     * @covers BMDie::get_recipe
     */
    public function testGet_recipe() {
        $die0 = new BMDieTwin;
        $die0->init(array(5, 12), array());
        $this->assertEquals('(5,12)', $die0->get_recipe());

        $die1 = new BMDieTwin;
        $die1->init(array(6, 8), array('Poison'));
        $this->assertEquals('p(6,8)', $die1->get_recipe());

        $die2 = new BMDieTwin;
        $die2->init(array(5, 'X'), array('Shadow'));
        $this->assertEquals('s(5,X)', $die2->get_recipe());

        $die3 = new BMDieTwin;
        $die3->init(array('Y', 13), array('Poison', 'Shadow'));
        $this->assertEquals('ps(Y,13)', $die3->get_recipe());

        $die4 = new BMDieTwin;
        $die4->init(array('X', 'X'), array('Shadow', 'Poison'));
        $this->assertEquals('sp(X,X)', $die4->get_recipe());

        $die5 = new BMDieTwin;
        $die5->init(array(4, 8), array());
        $this->assertEquals('(4,8)', $die5->get_recipe(TRUE));

        $die6 = new BMDieTwin;
        $die6->init(array(5, 5), array('Poison'));
        $this->assertEquals('p(5,5)', $die6->get_recipe(TRUE));

        $die7 = new BMDieTwin;
        $die7->init(array(5, 'Y'), array('Shadow'));
        $this->assertEquals('s(5,Y)', $die7->get_recipe(TRUE));
        $swingList = array('Y' => 3);
        $this->assertTrue($die7->set_swingValue($swingList));
        $this->assertEquals('s(5,Y=3)', $die7->get_recipe(TRUE));

        $die8 = new BMDieTwin;
        $die8->init(array('X', 'X'), array('Shadow', 'Poison'));
        $this->assertEquals('sp(X,X)', $die8->get_recipe(TRUE));
        $swingList = array('X' => 8);
        $this->assertTrue($die8->set_swingValue($swingList));
        $this->assertEquals('sp(X=8,X=8)', $die8->get_recipe(TRUE));
    }

    /**
     * @covers BMDieTwin::recalc_max_min
     *
     * @depends testInit
     */
    public function testRecalc_max_min() {
        $die = new BMDieTwin;
        $die->init(array('X', 'X'));
        $swingList = array('X' => 8);
        $die->set_swingValue($swingList);
        $this->assertEquals(8, $die->dice[0]->max);
        $this->assertEquals(8, $die->dice[1]->max);
        $this->assertEquals(16, $die->max);
        $die->dice[0]->value = 2;
        $die->dice[1]->value = 3;
        $die->value = 5;

        $die->dice[0]->max = 3;
        $die->dice[1]->max = 4;
        $die->recalc_max_min();
        $this->assertEquals(7, $die->max);
        $this->assertEquals(5, $die->value);
        $this->assertEquals(2, $die->dice[0]->value);
        $this->assertEquals(3, $die->dice[1]->value);
    }


    /**
     * @covers BMDieTwin::set_swingValue
     * @covers BMDieTwin::recalc_max_min
     *
     * @depends testInit
     */
    public function testSet_swingValue() {
        foreach (str_split("RSTUVWXYZ") as $swing) {
            $this->object->init(array($swing, 4));
            $range = $this->object->dice[0]->swing_range($swing);
            $swingMin = $range[0];
            $swingMax = $range[1];
            for ($i = $swingMin; $i <= $swingMax; $i++) {
                $swingList = array($swing => $i);
                $this->assertTrue($this->object->set_swingValue($swingList));
                $this->assertEquals($i, $this->object->dice[0]->swingValue);
                $this->assertEquals($i + 4, $this->object->max);

            }
        }

        $this->object->init(array("X", 6));

        // check error checking
        $swingList = array();
        $this->assertFalse($this->object->set_swingValue($swingList));

        $swingList = array("R" => 12);
        $this->assertFalse($this->object->set_swingValue($swingList));

        $swingList = array("R" => 12,
                           "S" => 10,
                           "U" => 15,
                           "Y" => 1,
                           "Z" => 30);
        $this->assertFalse($this->object->set_swingValue($swingList));

        $swingList = array("X" => 3);
        $this->assertFalse($this->object->set_swingValue($swingList));

        $swingList = array("X" => 21);
        $this->assertFalse($this->object->set_swingValue($swingList));

        // needle in a haystack
        $swingList = array("R" => 12,
                           "S" => 10,
                           "X" => 15,
                           "Y" => 1,
                           "Z" => 30);

        $this->assertTrue($this->object->set_swingValue($swingList));
        $this->assertEquals($this->object->dice[0]->swingValue, 15);
        $this->assertEquals($this->object->dice[0]->min, 1);
        $this->assertEquals($this->object->dice[0]->max, 15);
        $this->assertEquals($this->object->dice[1]->min, 1);
        $this->assertEquals($this->object->dice[1]->max, 6);
        $this->assertEquals($this->object->min, 2);
        $this->assertEquals($this->object->max, 21);
    }

    /**
     * @covers BMDieTwin::__clone
     *
     * @depends testInit
     */
    public function testClone_twin() {
        $originalDie = new BMDieTwin;
        $originalDie->init(array(5, 12), array());
        $originalSubdie0 = $originalDie->dice[0];
        $originalSubdie1 = $originalDie->dice[1];
        $this->assertEquals(5, $originalSubdie0->max);
        $this->assertEquals(12, $originalSubdie1->max);

        $cloneDie = clone $originalDie;
        $cloneSubdie0 = $cloneDie->dice[0];
        $cloneSubdie1 = $cloneDie->dice[1];
        $this->assertEquals(5, $cloneSubdie0->max);
        $this->assertEquals(12, $cloneSubdie1->max);

        $this->assertFalse($originalSubdie0 === $cloneSubdie0);
        $this->assertFalse($originalSubdie1 === $cloneSubdie1);
    }
}
