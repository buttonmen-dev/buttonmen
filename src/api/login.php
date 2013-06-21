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
$sql = 'SELECT id FROM player_info
        WHERE name_ingame     = :username
        AND   password_hashed = :password';
$query = $conn->prepare($sql);
$query->execute(array(':username' => $_POST['username'],
                      ':password' => crypt($_POST['password'])));
$result = $query->fetchAll();

// check if the username and password already exist
if (1 == count($result)) {
    $user_id = $result['id'];
    // set authorisation cookie
    setcookie('auth', '1', 0, '/', 'pagodabox.com', 0);

    // create display string
    $display_block = '<p>'.$user_id.' is authorised!</p>';
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