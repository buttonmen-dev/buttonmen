<?php

class BMDieTwinTest extends PHPUnit_Framework_TestCase {

    /**
     * @var BMDieTwin
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new BMDieTwin;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }


//    public function testAdd_skill() {
//        // Check that the skill list is indeed empty
//        $sl = PHPUnit_Framework_Assert::readAttribute($this->object, "skillList");
//        $hl = PHPUnit_Framework_Assert::readAttribute($this->object, "hookList");
//
//        $this->assertEmpty($sl, "Skill list not initially empty.");
//        $this->assertFalse(array_key_exists("test", $hl), "Hook list not initially empty.");
//
//        $this->object->add_skill("Testing", "TestDummyBMSkillTesting");
//
//        $sl = PHPUnit_Framework_Assert::readAttribute($this->object, "skillList");
//        $this->assertNotEmpty($sl, "Skill list should not be empty.");
//        $this->assertEquals(count($sl), 1, "Skill list contains more than it should.");
//        $this->assertArrayHasKey('Testing', $sl, "Skill list doesn't contain 'Testing'");
//        $this->assertEquals($sl["Testing"], "TestDummyBMSkillTesting", "Incorrect stored classname for 'Testing'");
//
//        // Proper maintenance of the hook lists
//        $hl = PHPUnit_Framework_Assert::readAttribute($this->object, "hookList");
//        $this->assertArrayHasKey("test", $hl, "Hook list missing test hooks.");
//
//        $this->assertContains("TestDummyBMSkillTesting", $hl["test"], "Hook list missing 'Testing' hook.");
//
//        $this->assertEquals(1, count($hl), "Hook list contains something extra.");
//        $this->assertEquals(1, count($hl["test"]), "Hook list for function 'test' contains something extra.");
//
//
//
//        // Another skill
//
//        $this->object->add_skill("Testing2", "TestDummyBMSkillTesting2");
//
//        $sl = PHPUnit_Framework_Assert::readAttribute($this->object, "skillList");
//        $this->assertNotEmpty($sl, "Skill list should not be empty.");
//        $this->assertEquals(count($sl), 2, "Skill list contains more than it should.");
//        $this->assertArrayHasKey('Testing', $sl, "Skill list doesn't contain 'Testing'");
//        $this->assertArrayHasKey('Testing2', $sl, "Skill list doesn't contain 'Testing2'");
//        $this->assertEquals($sl["Testing2"], "TestDummyBMSkillTesting2", "Incorrect stored classname for 'Testing2'");
//
//
//        // Redundancy
//
//        $this->object->add_skill("Testing", "TestDummyBMSkillTesting");
//
//        $sl = PHPUnit_Framework_Assert::readAttribute($this->object, "skillList");
//        $this->assertEquals(count($sl), 2, "Skill list contains more than it should.");
//        $this->assertArrayHasKey('Testing', $sl, "Skill list doesn't contain 'Testing'");
//        $this->assertArrayHasKey('Testing2', $sl, "Skill list doesn't contain 'Testing2'");
//
//        // Proper maintenance of the hook lists
//        $hl = PHPUnit_Framework_Assert::readAttribute($this->object, "hookList");
//        $this->assertArrayHasKey("test", $hl, "Hook list missing test hooks.");
//
//        $this->assertContains("TestDummyBMSkillTesting", $hl["test"], "Hook list missing 'Testing' hook.");
//        $this->assertContains("TestDummyBMSkillTesting2", $hl["test"], "Hook list missing 'Testing2' hook.");
//
//        $this->assertEquals(1, count($hl), "Hook list contains something extra.");
//        $this->assertEquals(2, count($hl["test"]), "Hook list for function 'test' contains something extra.");
//
//
//
//    }
//
//    /**
//     * @depends testAdd_skill
//     */
//    public function testHas_skill() {
//        $this->object->add_skill("Testing", "TestDummyBMSkillTesting");
//        $this->object->add_skill("Testing2", "TestDummyBMSkillTesting2");
//        $this->assertTrue($this->object->has_skill("Testing"));
//        $this->assertTrue($this->object->has_skill("Testing2"));
//        $this->assertFalse($this->object->has_skill("Testing3"));
//    }
//
//    /**
//     * @depends testAdd_skill
//     * @depends testHas_skill
//     */
//    public function testRemove_skill() {
//
//        // simple
//        $this->object->add_skill("Testing", "TestDummyBMSkillTesting");
//        $this->assertTrue($this->object->remove_skill("Testing"));
//        $this->assertFalse($this->object->has_skill("Testing"));
//
//        // multiple skills
//        $this->object->add_skill("Testing", "TestDummyBMSkillTesting");
//        $this->object->add_skill("Testing2", "TestDummyBMSkillTesting2");
//        $this->assertTrue($this->object->remove_skill("Testing"));
//        $this->assertFalse($this->object->has_skill("Testing"));
//        $this->assertTrue($this->object->has_skill("Testing2"));
//
//        // fail to remove non-existent skills
//        $this->object->add_skill("Testing", "TestDummyBMSkillTesting");
//        $this->assertFalse($this->object->remove_skill("Testing3"));
//        $this->assertTrue($this->object->has_skill("Testing"));
//        $this->assertTrue($this->object->has_skill("Testing2"));
//
//        // examine the hook list for proper editing
//        $this->assertTrue($this->object->remove_skill("Testing2"));
//        $this->assertTrue($this->object->has_skill("Testing"));
//        $this->assertFalse($this->object->has_skill("Testing2"));
//
//        $hl = PHPUnit_Framework_Assert::readAttribute($this->object, "hookList");
//        $this->assertArrayHasKey("test", $hl, "Hook list missing test hooks.");
//
//        $this->assertContains("TestDummyBMSkillTesting", $hl["test"], "Hook list missing 'Testing' hook.");
//        $this->assertNotContains("TestDummyBMSkillTesting2", $hl["test"], "Hook list _not_ missing 'Testing2' hook.");
//
//        $this->assertEquals(1, count($hl), "Hook list contains something extra.");
//        $this->assertEquals(1, count($hl["test"]), "Hook list for function 'test' contains something extra.");
//    }
//
//    /**
//     * @depends testAdd_skill
//     * @depends testHas_skill
//     * @depends testRemove_skill
//     */
//    public function testRun_hooks() {
//        $die = new TestDummyBMDieTesting;
//
//        $die->add_skill("Testing", "TestDummyBMSkillTesting");
//
//        $die->test();
//
//        $this->assertEquals("testing", $die->testvar);
//
//        $die->remove_skill("Testing");
//        $die->add_skill("Testing2", "TestDummyBMSkillTesting2");
//
//        $die->test();
//        $this->assertEquals("still testing", $die->testvar);
//
//        $die->add_skill("Testing", "TestDummyBMSkillTesting");
//
//        $die->test();
//        // order in which hooks run is not guaranteed
//        $this->assertRegExp('/testingstill testing|still testingtesting/', $die->testvar);
//    }
//
//
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
        $this->assertEquals(1, $this->object->dice[0]->min);
        $this->assertNull($this->object->dice[0]->max);
        $this->assertEquals(1, $this->object->dice[1]->min);
        $this->assertEquals(2, $this->object->dice[1]->max);
        $this->assertNull($this->object->min);
        $this->assertNull($this->object->max);

        $this->object->init(array('R', 'S'), array());

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

        $die = BMDieTwin::create(array('R', 'S'), array());

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
     * @covers BMDie::parse_recipe_for_sides
     */
    public function testParse_recipe_for_sides() {
        $this->assertEquals('', BMDieTwin::parse_recipe_for_sides('(4)'));
        $this->assertEquals('', BMDieTwin::parse_recipe_for_sides('ps(4)'));
        $this->assertEquals('', BMDieTwin::parse_recipe_for_sides('(4)+'));
        $this->assertEquals('', BMDieTwin::parse_recipe_for_sides('ps(4)+'));

        $this->assertEquals('', BMDieTwin::parse_recipe_for_sides('(X)'));
        $this->assertEquals('', BMDieTwin::parse_recipe_for_sides('ps(X)'));
        $this->assertEquals('', BMDieTwin::parse_recipe_for_sides('(X)+'));
        $this->assertEquals('', BMDieTwin::parse_recipe_for_sides('ps(X)+'));

        $this->assertEquals(array(4, 6), BMDieTwin::parse_recipe_for_sides('(4,6)'));
        $this->assertEquals(array(4, 6), BMDieTwin::parse_recipe_for_sides('ps(4,6)'));
        $this->assertEquals(array(4, 6), BMDieTwin::parse_recipe_for_sides('(4,6)+'));
        $this->assertEquals(array(4, 6), BMDieTwin::parse_recipe_for_sides('ps(4,6)+'));

        $this->assertEquals(array(4, 'R'), BMDieTwin::parse_recipe_for_sides('(4,R)'));
        $this->assertEquals(array('R', 6), BMDieTwin::parse_recipe_for_sides('ps(R,6)'));
        $this->assertEquals(array('R', 'S'), BMDieTwin::parse_recipe_for_sides('(R,S)+'));
        $this->assertEquals(array('R', 'S'), BMDieTwin::parse_recipe_for_sides('ps(R,S)+'));
    }

    /**
     * @covers BMDie::create_from_string_components
     *
     * @depends testCreate
     */
    public function testCreate_from_string_components() {
        $die = BMDie::create_from_string_components('4,6', array('Shadow'));
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
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers BMDieTwin::split
     *
     * @depends testInit
     * @depends testRoll
     */
    public function testSplit() {
        // 1-siders split into two 1-siders
        $this->object->init(array(1, 1), array());
        $this->object->roll(FALSE);

        $splitDice = $this->object->split();

        $this->assertFalse($splitDice[0] === $splitDice[1]);
        $this->assertEquals(1, $splitDice[0]->dice[0]->max);
        $this->assertEquals(1, $splitDice[0]->dice[1]->max);
        $this->assertEquals(1, $splitDice[1]->dice[0]->max);
        $this->assertEquals(1, $splitDice[1]->dice[1]->max);
        $this->assertEquals(2, $splitDice[0]->min);
        $this->assertEquals(2, $splitDice[0]->max);
        $this->assertEquals(2, $splitDice[1]->min);
        $this->assertEquals(2, $splitDice[1]->max);

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
        $this->assertEquals(5, $splitDice[0]->dice[1]->max);
        $this->assertEquals(2, $splitDice[1]->dice[0]->max);
        $this->assertEquals(4, $splitDice[1]->dice[1]->max);
        $this->assertEquals(2, $splitDice[0]->min);
        $this->assertEquals(8, $splitDice[0]->max);
        $this->assertEquals(2, $splitDice[1]->min);
        $this->assertEquals(6, $splitDice[1]->max);

        // swing split
        $this->object->init(array('X', 'X'));
        $this->object->set_swingValue(array('X' => 5));
        $splitDice = $this->object->split();

        $this->assertFalse($splitDice[0] === $splitDice[1]);
        $this->assertTrue($this->object === $splitDice[0]);
        $this->assertNotInstanceOf('BMDieSwing', $splitDice[0]->dice[0]);
        $this->assertNotInstanceOf('BMDieSwing', $splitDice[0]->dice[1]);
        $this->assertNotInstanceOf('BMDieSwing', $splitDice[1]->dice[0]);
        $this->assertNotInstanceOf('BMDieSwing', $splitDice[1]->dice[1]);
        $this->assertEquals(3, $splitDice[0]->dice[0]->max);
        $this->assertEquals(3, $splitDice[0]->dice[1]->max);
        $this->assertEquals(2, $splitDice[1]->dice[0]->max);
        $this->assertEquals(2, $splitDice[1]->dice[1]->max);
        $this->assertEquals(2, $splitDice[0]->min);
        $this->assertEquals(6, $splitDice[0]->max);
        $this->assertEquals(2, $splitDice[1]->min);
        $this->assertEquals(4, $splitDice[1]->max);
    }

//    /*
//     * @covers BMDie::get_recipe
//     */
//    public function testGet_recipe() {
//        $die0 = new BMDie;
//        $die0->init(51, array());
//        $this->assertEquals('(51)', $die0->get_recipe());
//
//        $die1 = new BMDie;
//        $die1->init(6, array('Poison'));
//        $this->assertEquals('p(6)', $die1->get_recipe());
//
//        $die2 = new BMDie;
//        $die2->init(5, array('Shadow'));
//        $this->assertEquals('s(5)', $die2->get_recipe());
//
//        $die3 = new BMDie;
//        $die3->init(13, array('Poison', 'Shadow'));
//        $this->assertEquals('ps(13)', $die3->get_recipe());
//
//        $die4 = new BMDie;
//        $die4->init(25, array('Shadow', 'Poison'));
//        $this->assertEquals('sp(25)', $die4->get_recipe());
//    }

    /**
     * @depends testInit
     * @covers BMDieTwin::set_swingValue
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

}

?>