<?php

require_once __DIR__.'/TestDummyPDOConn.php';

class BMDBTest extends PHPUnit\Framework\TestCase {
    /**
     * @var BMDB
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() : void {
        $this->conn = new TestDummyPDOConn;
        $this->object = new BMDB($this->conn);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() : void {
    }

    /**
     * @covers BMDB::select_single_value
     */
    public function testSelect_single_value()
    {
        $parameters = array(':player_id' => 3);
        $query =
            'SELECT COUNT(*) '.
            'FROM game_player_map AS gpm '.
               'LEFT JOIN game AS g ON g.id = gpm.game_id '.
            'WHERE gpm.player_id = :player_id '.
               'AND gpm.is_awaiting_action = 1 '.
               'AND g.status_id IN '.
                   '(SELECT id FROM game_status WHERE name IN (\'NEW\', \'ACTIVE\'))';

        $this->conn->setNextExpectedReturnValue(array(array(18)));
        $this->assertEquals($this->object->select_single_value($query, $parameters, 'int'), 18);
    }

    /**
     * @covers BMDB::select_rows
     */
    public function testSelect_rows()
    {
        $parameters = array(':game_id' => 3);
        $query =
            'SELECT s.name AS status_name,' .
            'v.player_id'.
            'v.autopass '.
            'FROM game AS g '.
            'LEFT JOIN game_status AS s '.
            'ON s.id = g.status_id '.
            'LEFT JOIN game_player_view AS v '.
            'ON g.id = v.game_id '.
            'WHERE g.id = :game_id '.
            'ORDER BY g.id;';
        $columnReturnTypes = array(
            'status_name' => 'str',
            'player_id' => 'int_or_null',
            'autopass' => 'bool',
        );

        // Fake value for the database query to return to PDO::fetch()
        $this->conn->setNextExpectedReturnValue(array(
            array('status_name' => 'OPEN', 'player_id' => '0', 'autopass' => TRUE),
            array('status_name' => 'OPEN', 'player_id' => NULL, 'autopass' => FALSE)));

        $rows = $this->object->select_rows($query, $parameters, $columnReturnTypes);
        $this->assertEquals(2, count($rows));
        $this->assertEquals(array('status_name' => 'OPEN', 'player_id' => 0, 'autopass' => TRUE), $rows[0]);
        $this->assertEquals(array('status_name' => 'OPEN', 'player_id' => NULL, 'autopass' => FALSE), $rows[1]);
    }

    /**
     * @covers BMDB::cast_db_column
     */
    public function testCast_db_column()
    {
        // Get an accessible copy of the protected cast_db_column method so we can test it
        $class = new ReflectionClass('BMDB');
        $method = $class->getMethod('cast_db_column');
        $method->setAccessible(true);

        // use ReflectionClass::invokeArgs() to call cast_db_column with various args

        // 'int' column type
        foreach (array('3', 3) as $input) {
            $retval = $method->invokeArgs($this->object, array($input, 'int'));
            $this->assertEquals(3, $retval);
            $this->assertEquals('integer', gettype($retval));
        }
        try {
            $retval = $method->invokeArgs($this->object, array(NULL, 'int'));
            $this->fail("Exception should have been thrown");
        } catch (BMExceptionDatabase $e) {
            $this->assertEquals('Found non-set value in DB when expecting integer', $e->getMessage());
        }

        // 'int_null_becomes_zero' column type
        foreach (array('3', 3) as $input) {
            $retval = $method->invokeArgs($this->object, array($input, 'int_null_becomes_zero'));
            $this->assertEquals(3, $retval);
            $this->assertEquals('integer', gettype($retval));
        }
        foreach (array(NULL) as $input) {
            $retval = $method->invokeArgs($this->object, array($input, 'int_null_becomes_zero'));
            $this->assertEquals(0, $retval);
            $this->assertEquals('integer', gettype($retval));
        }

        // 'int_or_null' column type
        foreach (array('3', 3) as $input) {
            $retval = $method->invokeArgs($this->object, array($input, 'int_or_null'));
            $this->assertEquals(3, $retval);
            $this->assertEquals('integer', gettype($retval));
        }
        foreach (array(NULL) as $input) {
            $retval = $method->invokeArgs($this->object, array($input, 'int_or_null'));
            $this->assertEquals(NULL, $retval);
            $this->assertEquals('NULL', gettype($retval));
        }

        // 'bool' column type
        foreach (array(TRUE, '1', 1) as $input) {
            $retval = $method->invokeArgs($this->object, array($input, 'bool'));
            $this->assertEquals(TRUE, $retval);
            $this->assertEquals('boolean', gettype($retval));
        }
        foreach (array(FALSE, '0', 0, NULL) as $input) {
            $retval = $method->invokeArgs($this->object, array($input, 'bool'));
            $this->assertEquals(FALSE, $retval);
            $this->assertEquals('boolean', gettype($retval));
        }

        // 'str' column type
        foreach (array('1', 1, TRUE) as $input) {
            $retval = $method->invokeArgs($this->object, array($input, 'str'));
            $this->assertEquals('1', $retval);
            $this->assertEquals('string', gettype($retval));
        }
        try {
            $retval = $method->invokeArgs($this->object, array(NULL, 'str'));
            $this->fail("Exception should have been thrown");
        } catch (BMExceptionDatabase $e) {
            $this->assertEquals('Found non-set value in DB when expecting string', $e->getMessage());
        }

        // 'str_or_null' column type
        foreach (array('1', 1, TRUE) as $input) {
            $retval = $method->invokeArgs($this->object, array($input, 'str_or_null'));
            $this->assertEquals('1', $retval);
            $this->assertEquals('string', gettype($retval));
        }
        foreach (array(NULL) as $input) {
            $retval = $method->invokeArgs($this->object, array($input, 'str_or_null'));
            $this->assertEquals(NULL, $retval);
            $this->assertEquals('NULL', gettype($retval));
        }
    }
}
