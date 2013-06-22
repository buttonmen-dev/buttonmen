<?php
function login($username, $password) {
    require '../database/mysql.inc.php';

    $sql = 'SELECT id, password_hashed FROM player_info
            WHERE name_ingame = :username';
    $query = $conn->prepare($sql);
    $query->execute(array(':username' => $username));

    $resultArray = $query->fetchAll();

    $returnValue = FALSE;

    // check if the username already exists
    if (1 == count($resultArray)) {
        $result = $resultArray[0];
        $password_hashed = $result['password_hashed'];

        // check if the password is correct
        if ($password_hashed == crypt($_POST['password'], $password_hashed)) {
            // create authorisation key
            $auth_key = crypt(substr(sha1(rand()), 0, 10).$username);

            // write authorisation key to database
            $sql = 'INSERT INTO player_auth (id, auth_key) VALUES (:id, :auth_key)
                    ON DUPLICATE KEY UPDATE auth_key = :auth_key';
            $query = $conn->prepare($sql);
            $query->execute(array(':id'       => $result['id'],
                                  ':auth_key' => $auth_key));

            // set authorisation cookie
            setcookie('auth_key', $auth_key, 0, '/', '', FALSE);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['user_name'] = $username;
            $_SESSION['user_lastactive'] = time();
            $returnValue = TRUE;
        }
    }

    return $returnValue;
}

function logout() {
    session_start();
    require '../database/mysql.inc.php';

    $sql = 'DELETE FROM player_auth
            WHERE id = :id';
    $query = $conn->prepare($sql);
    $query->execute(array(':id' => $_SESSION['user_id']));

    $_SESSION = array();

    setcookie('auth_key',      '', time()-3600, '/');

    $params = session_get_cookie_params();
    setcookie(session_name(),  '', time()-3600,
              $params["path"],   $params["domain"],
              $params["secure"], $params["httponly"]);

    session_destroy();
    session_write_close();
}
?>
