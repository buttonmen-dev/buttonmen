<?php
// clear authorisation cookie
setcookie('auth', '1', time()-3600, '/', '', FALSE);

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