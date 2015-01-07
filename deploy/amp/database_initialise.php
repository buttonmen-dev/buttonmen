<html>
<head>
    <title>Initialise database</title>
</head>
<body>
    <?php

        $conn = db_connect('buttonmen');
        run_all_sql_files($conn);

        $conn = db_connect('buttonmen_test');
        run_all_sql_files($conn);

        function run_all_sql_files($conn) {
            run_sql_file($conn, "../database/drop_all_tables_and_views.sql");
            run_sql_file($conn, "../database/schema.config.sql");
            run_sql_file($conn, "../database/schema.button.sql");
            run_sql_file($conn, "../database/schema.player.sql");
            run_sql_file($conn, "../database/schema.game.sql");
            run_sql_file($conn, "../database/schema.forum.sql");
            run_sql_file($conn, "../database/views.config.sql");
            run_sql_file($conn, "../database/views.button.sql");
            run_sql_file($conn, "../database/views.player.sql");
            run_sql_file($conn, "../database/views.game.sql");
            run_sql_file($conn, "../database/views.forum.sql");
            run_sql_file($conn, "../database/data.config.sql");
            run_sql_file($conn, "../database/data.button.sql");
            run_sql_file($conn, "../database/data.game.sql");
            run_sql_file($conn, "../database/data.player.sql");
            run_sql_file($conn, "../database/data.forum.sql");
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

            $conn = new PDO("mysql:host=$host;port=$port;dbname=$name", $user, $pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Make sure auto_increment_increment is 1
            $statement = $conn->prepare('SET AUTO_INCREMENT_INCREMENT=1');
            $statement->execute();
            return $conn;
        }
    ?>
</body>
</html>
