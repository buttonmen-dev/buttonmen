<?php
// check for required fields from the form
if ((!isset($_POST['username'])) || (!isset($_POST['password']))) {
    header('Location: create_user.html');
    exit;
}

// connect to server
// connection is $conn
require '../database/mysql.inc.php';

// check if username already exists
$sql = 'SELECT id FROM player_info
        WHERE name_ingame = :username';
$query = $conn->prepare($sql);
$query->execute(array(':username' => $_POST['username']));
$result = $query->fetchAll();

if (count($result) > 0) {
    $user_id = $result[0]['id'];

    // create display string
    $display_block = '<p>'.$_POST['username'].' already exists at id '.$user_id.'</p>';
} else {
    // create user
    $sql = 'INSERT INTO player_info (name_ingame, password_hashed)
            VALUES (:username, :password)';
    $query = $conn->prepare($sql);
    $query->execute(array(':username' => $_POST['username'],
                          ':password' => crypt($_POST['password'])));
    var_dump($_POST['password']);
    var_dump(crypt($_POST['password']));
    //$result = $query->fetchAll();
    $display_block = '<p>User '.$_POST['username'].' created successfully!</p>';
}
?>

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title>
            Create user
        </title>
    </head>
    <body>
        <?php echo "$display_block"; ?>
    </body>
</html>