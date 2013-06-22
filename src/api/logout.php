<?php
require 'api_core.php';
logout();

// create display string
$display_block = '<p>Authorisation cookie cleared!</p>';
?>

<html>
    <head>
        <title>
            Logout
        </title>
    </head>
    <body>
        <?php echo "$display_block"; ?>
    </body>
</html>