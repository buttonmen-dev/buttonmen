<?php
// check for required fields from the form
if ((!isset($_POST['username'])) || (!isset($_POST['password']))) {
    header('Location: login.html');
    exit;
}

session_start();
require 'api_core.php';
$login_success = login($_POST['username'], $_POST['password']);


// check if the username already exists
if ($login_success) {
    // create display string
    $display_block = '<p>Authorised!</p>';
    header('Location: overview.html');
} else {
    $display_block = '<p>Failed.</p>';
    // redirect back to login form if not authorised
    //header('Location: login.html');
}
?>

<html>
    <head>
        <title>
            Login
        </title>
    </head>
    <body>
        <?php echo "$display_block"; ?>
    </body>
</html>