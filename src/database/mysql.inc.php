<?php
/**
 * mysql.inc : definition of connection to the database
 */

/**
 * connect to the server
 *
 * @return PDO
 */
function conn() {
    if (isset($_SERVER['DB1_HOST'])) {
        // pagodabox connection details are stored in environment variables
        $host = $_SERVER['DB1_HOST'];
        $port = $_SERVER['DB1_PORT'];
        $name = $_SERVER['DB1_NAME'];
        $user = $_SERVER['DB1_USER'];
        $pass = $_SERVER['DB1_PASS'];
    } else {
        // set connection details for local test server
        $host = 'localhost';
        $port = 3306;
        $name = 'buttonmen';
        $user = 'bmuser';
        $pass = 'bmuserpass';
    }

    // Make a reasonable number of attempts to establish a DB connection
    for ($attempt = 1; $attempt <= 5; $attempt++) {
        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$name", $user, $pass);
            if (is_null($conn)) {
                throw new PDOException("Database connection failed after set");
            }

            // don't use PDO emulation for prepare statements, have
            // MySQL prepare statements natively
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            if (is_null($conn)) {
                throw new PDOException("Database connection failed after setAttribute(ATTR_EMULATE_PREPARES)");
            }

            // SQL errors should throw catchable exceptions
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if (is_null($conn)) {
                throw new PDOException("Database connection failed after setAttribute(ATTR_ERRMODE)");
            }

            // Make sure auto_increment_increment is 1
            $statement = $conn->prepare('SET AUTO_INCREMENT_INCREMENT=1');
            $statement->execute();
            if (is_null($conn)) {
                throw new PDOException("Database connection failed after SET AUTO_INCREMENT_INCREMENT");
            }

            return $conn;
        } catch(PDOException $e) {
            error_log("Caught exception in mysql.inc.php (attempt $attempt/5): " . $e->getMessage());
        }
    }

    // Failed to establish a connection
    throw new PDOException("Database connection failed");
}
