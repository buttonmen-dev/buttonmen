<html>
<head>
    <title>Initialise database</title>
</head>
<body>
    <?php

// james: uncomment the following lines to also initialise the 
//        primary 'buttonmen' database
//
//        recreate_database('buttonmen');
//        $conn = db_connect('buttonmen');
//        run_all_sql_files($conn);

        recreate_database('buttonmen_test');
        $conn = db_connect('buttonmen_test');
        run_all_sql_files($conn);

        function recreate_database($dbname) {
            try {
                $conn = db_connect($dbname);

                try {
                    // drop database
                    print "Deleting database $dbname<br>";
                    $query = "DROP DATABASE IF EXISTS `$dbname`;";
                    $statement = $conn->prepare($query);
                    $statement->execute();
                } catch (PDOException $e) {
                    var_dump("drop of database $dbname failed");
                }

                // recreate database
                print "Creating database $dbname<br>";
                $query = "CREATE DATABASE `$dbname`;".
                         "CREATE USER :user@:host IDENTIFIED BY :pass;".
                         "GRANT ALL ON `$dbname`.* TO :user@:host;".
                         "FLUSH PRIVILEGES;";
                $statement = $conn->prepare($query);
                $statement->execute(array(':user' => 'root',
                                          ':pass' => 'root',
                                          ':host' => 'localhost'));
            } catch (PDOException $e) {
                var_dump($e);
            }
        }

        function run_all_sql_files($conn) {
            run_sql_file($conn, "../database/schema.config.sql");
            run_sql_file($conn, "../database/schema.button.sql");
            run_sql_file($conn, "../database/schema.player.sql");
            run_sql_file($conn, "../database/schema.game.sql");
            run_sql_file($conn, "../database/schema.forum.sql");
            run_sql_file($conn, "../database/schema.stats.sql");
            run_sql_file($conn, "../database/views.config.sql");
            run_sql_file($conn, "../database/views.button.sql");
            run_sql_file($conn, "../database/views.player.sql");
            run_sql_file($conn, "../database/views.game.sql");
            run_sql_file($conn, "../database/views.forum.sql");
            run_sql_file($conn, "../database/views.stats.sql");
            run_sql_file($conn, "../database/data.config.sql");
            run_sql_file($conn, "../database/data.button.sql");
            run_sql_file($conn, "../database/data.game.sql");
            run_sql_file($conn, "../database/data.player.sql");
            run_sql_file($conn, "../database/data.forum.sql");
            run_sql_file($conn, "../database/data.stats.sql");
        }

        // Courtesy: http://stackoverflow.com/questions/4027769/running-mysql-sql-files-in-php/10209702#10209702
        function run_sql_file($conn, $location) {
            print "Running SQL commands from file: $location<br>";
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

        function db_connect($name) {
            print "Connecting to database $name<br>";
            $host = 'localhost';
            $port = 8178;
            $user = 'root';
            $pass = 'root';

            try {
                // try to connect to the specific database
                $conn = new PDO("mysql:host=$host;port=$port;dbname=$name", $user, $pass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Make sure auto_increment_increment is 1
                $statement = $conn->prepare('SET AUTO_INCREMENT_INCREMENT=1');
                $statement->execute();
            } catch (PDOException $ex) {
                // connect generically, without connecting to a specific database
                $conn = new PDO("mysql:host=$host;port=$port", $user, $pass);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $statement = $conn->prepare('SET AUTO_INCREMENT_INCREMENT=1');
                $statement->execute();
            }

            return $conn;
        }
    ?>
</body>
</html>
