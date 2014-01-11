<?php
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

        try {
            $conn = new PDO("mysql:host=$host;port=$port;dbname=$name", $user, $pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Make sure auto_increment_increment is 1
            $statement = $conn->prepare('SET AUTO_INCREMENT_INCREMENT=1');
            $statement->execute();
        } catch(PDOException $e) {
            error_log('Caught exception in mysql.inc.php: ' . $e->getMessage());
            echo 'ERROR: ' . $e->getMessage();
        }

        return $conn;
    }
?>
