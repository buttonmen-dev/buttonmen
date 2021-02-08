<?php
/**
 * BMDB: database interface to standardize types
 *
 * @author: Chaos
 */

/**
 * This class contains all the logic to do with selecting from and updating
 * the buttonmen database tables.
 *
 * It should be instantiated with a PDO database connection created by the caller
 * (which will be a TestDummyPDOConn in the case that the caller is unit testing).
 */
class BMDB {
    // properties

    /**
     * Connection to database
     *
     * @var PDO
     */
    protected static $conn = NULL;

    /**
     * Constructor
     *
     * @param PDO $conn
     */
    public function __construct($conn) {
        self::$conn = $conn;
    }

    /**
     * Execute a query which fetches a single value from the DB
     *
     * @param $conn
     * @param string $query
     * @param array $parameters
     * @param array $returnType
     * @return array
     */
    public function select_single_value($query, $parameters, $returnType) {
        $statement = self::$conn->prepare($query);
        $statement->execute($parameters);
        $result = $statement->fetch(PDO::FETCH_NUM);
        if (!$result) {
            throw new BMExceptionDatabase("DB select_single_value found no result");
        }
        if (count($result) != 1) {
            throw new BMExceptionDatabase("Expected 1 result from DB query, found " . count($result));
        }
        return ($this->cast_db_column($result[0], $returnType));
    }

    /**
     * Execute a query which fetches an arbitrary number of rows from the database
     *
     * @param $conn
     * @param string $query
     * @param array $parameters
     * @param array $columnReturnTypes
     * @return array
     */
    public function select_rows($query, $parameters, $columnReturnTypes) {
        $statement = self::$conn->prepare($query);
        $statement->execute($parameters);
        $rows = array();
        while ($result = $statement->fetch(PDO::FETCH_ASSOC)) {
            $row = array();
            foreach ($columnReturnTypes as $column => $returnType) {
                $row[$column] = $this->cast_db_column($result[$column], $returnType);
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Execute a query which performs a database update
     *
     * @param $conn
     * @param string $query
     * @param array $parameters
     * @return void
     */
    public function update($query, $parameters) {
        $statement = self::$conn->prepare($query);
        $statement->execute($parameters);
    }

    /**
     * Cast a value fetched from a DB to the specified return type
     *
     * @param $conn
     * @param string $query
     * @param array $returnType
     * @return array
     */
    protected function cast_db_column($column, $returnType) {
        if ($returnType == 'int') {
            if (!isset($column)) {
                throw new BMExceptionDatabase("Found non-set value in DB when expecting integer");
            }
            return (int)$column;
        }
        // Cast all values to int, including unset values - this returns 0 if the DB contains NULL
        if ($returnType == 'int_null_becomes_zero') {
            return (int)$column;
        }
        if ($returnType == 'bool') {
            return (bool)$column;
        }
        if ($returnType == 'str') {
            if (!isset($column)) {
                throw new BMExceptionDatabase("Found non-set value in DB when expecting string");
            }
            return (string)$column;
        }
        if ($returnType == 'int_or_null') {
            if (isset($column)) {
                return (int)$column;
            }
            return NULL;
        }
        if ($returnType == 'str_or_null') {
            if (isset($column)) {
                return (string)$column;
            }
            return NULL;
        }
        throw BMExceptionDatabase("Unknown column return type " . $returnType);
    }
}
