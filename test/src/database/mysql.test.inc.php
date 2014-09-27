<?php
    function conn() {
        if (isset($_SERVER['DB2_HOST'])) {
            // pagodabox connection details are stored in environment variables
            $host = $_SERVER['DB2_HOST'];
            $port = $_SERVER['DB2_PORT'];
            $name = $_SERVER['DB2_NAME'];
            $user = $_SERVER['DB2_USER'];
            $pass = $_SERVER['DB2_PASS'];
        } else {
            // set connection details for local test server
            $host = 'localhost';
            $port = 3306;
            $name = 'buttonmen_test';
            $user = 'bmtest';
            $pass = 'bmtestpass';
        }

        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$name", $user, $pass);

            // don't use PDO emulation for prepare statements, have
            // MySQL prepare statements natively
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);

            // SQL errors should throw catchable exceptions
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Make sure auto_increment_increment is 1
            $statement = $conn->prepare('SET AUTO_INCREMENT_INCREMENT=1');
            $statement->execute();
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }

        return $conn;
    }
?>
