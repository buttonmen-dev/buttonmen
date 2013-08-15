<?php

    $conn = db_connect();
    run_sql_file($conn, "deploy/database/schema.button.sql");
    run_sql_file($conn, "deploy/database/schema.player.sql");
    run_sql_file($conn, "deploy/database/schema.game.sql");
    run_sql_file($conn, "deploy/database/views.button.sql");
    run_sql_file($conn, "deploy/database/views.player.sql");
    run_sql_file($conn, "deploy/database/views.game.sql");
    run_sql_file($conn, "deploy/database/data.button.sql");
    run_sql_file($conn, "deploy/database/data.game.sql");
    run_sql_file($conn, "deploy/database/data.player.sql");

    // Courtesy: http://stackoverflow.com/questions/4027769/running-mysql-sql-files-in-php/10209702#10209702
    function run_sql_file($conn, $location) {
        print "Running SQL commands from file: $location\n";
        //load file
        $commands = file_get_contents($location);
    
        //delete comments
        $lines = explode("\n", $commands);
        $commands = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line && strncmp($line, '#', strlen('#'))) {
                $commands .= $line . "\n";
            }
        }
    
        //convert to array
        $commands = explode(";", $commands);
    
        //run commands
        $total = $success = 0;
        foreach ($commands as $command) {
            if (trim($command)) {
                $statement = $conn->prepare($command);
                if ($statement->execute()) {
                    $success += 1;
                }
                $total += 1;
            }
        }
    
        //return number of successful queries and total number of queries found
        return array(
            "success" => $success,
            "total" => $total
        );
    }

    function db_connect() {
        // pagodabox connection details are stored in environment variables
        // test database is DB2
        $host = $_SERVER['DB2_HOST'];
        $port = $_SERVER['DB2_PORT'];
        $name = $_SERVER['DB2_NAME'];
        $user = $_SERVER['DB2_USER'];
        $pass = $_SERVER['DB2_PASS'];
    
        $conn = new PDO("mysql:host=$host;port=$port;dbname=$name", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        // Make sure auto_increment_increment is 1 
        $statement = $conn->prepare('SET AUTO_INCREMENT_INCREMENT=1');
        $statement->execute();
        return $conn;
    }
?>
