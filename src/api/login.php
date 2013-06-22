<?php
// check for required fields from the form
if ((!isset($_POST['username'])) || (!isset($_POST['password']))) {
    header('Location: login.html');
    exit;
}

// connect to server
// connection is $conn
require '../database/mysql.inc.php';

// query the player id
$sql = 'SELECT password_hashed FROM player_info
        WHERE name_ingame     = :username';
$query = $conn->prepare($sql);
$query->execute(array(':username' => $_POST['username']));

$result = $query->fetchAll();

// check if the username and password already exist
if (1 == count($result)) {
    $password_hashed = $result[0]['password_hashed'];

    if ($password_hashed == crypt($_POST['password'], $password_hashed)) {
        // set authorisation cookie
        setcookie('auth', $_POST['username'], 0, '/', '', FALSE);

        // create display string
        $display_block = '<p>Authorised!</p>';
    } else {
        $display_block = '<p>Password incorrect</p>';
    }
} else {
    // redirect back to login form if not authorised
    header('Location: login.html');
//    exit;
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