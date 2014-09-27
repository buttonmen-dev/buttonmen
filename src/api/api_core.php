<?php
function login($username, $password) {
    require_once '../database/mysql.inc.php';
    $conn = conn();

    $sql = 'SELECT id, name_ingame, password_hashed, status FROM player
            WHERE name_ingame = :username';
    $query = $conn->prepare($sql);
    $query->execute(array(':username' => $username));

    $resultArray = $query->fetchAll();

    $returnValue = FALSE;

    // check if the username already exists
    if (1 == count($resultArray)) {
        $result = $resultArray[0];
        $password_hashed = $result['password_hashed'];
        $status = $result['status'];

        // check if the password is correct and if the account is in active status
        if (($password_hashed == crypt($password, $password_hashed) && ($status == 'active'))) {

            // if the user has too many active logins (allow 6), delete the oldest
            $sql = 'SELECT id FROM player_auth WHERE player_id = :id ORDER BY login_time';
            $query = $conn->prepare($sql);
            $query->execute(array(':id'       => $result['id']));
            $resultArray = $query->fetchAll();
            for ($i=0; $i < (count($resultArray) - 5); $i++) {
                $sql = 'DELETE FROM player_auth WHERE id = :id';
                $query = $conn->prepare($sql);
                $query->execute(array(':id' => $resultArray[$i]['id']));
            }

            // create authorisation key
            $auth_key = crypt(substr(sha1(rand()), 0, 10).$username);

            // write authorisation key to database
            $sql = 'INSERT INTO player_auth (player_id, auth_key) VALUES (:id, :auth_key)';
            $query = $conn->prepare($sql);
            $query->execute(array(':id'       => $result['id'],
                                  ':auth_key' => $auth_key));

            // set authorisation cookie
            setcookie('auth_userid', $result['id'], 0, '/', '', FALSE);
            setcookie('auth_key', $auth_key, 0, '/', '', FALSE);
            session_regenerate_id(TRUE);
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['user_name'] = $result['name_ingame'];
            $_SESSION['user_lastactive'] = time();
            $returnValue = TRUE;
        }
    }

    return $returnValue;
}

// If the user is logged in, make sure a valid session exists.
// Otherwise, return false.
function auth_session_exists() {

    // there's an existing session, nothing to do
    if (array_key_exists('user_name', $_SESSION)) {
        return TRUE;
    }

    // There's not an existing session, but the user has an auth_key
    // cookie, so see if it matches the database.
    // If it does, create a new session
    if (array_key_exists('auth_userid', $_COOKIE) && array_key_exists('auth_key', $_COOKIE)) {
        require_once '../database/mysql.inc.php';
        $conn = conn();

        $auth_userid = $_COOKIE['auth_userid'];
        $auth_key = $_COOKIE['auth_key'];
        $sql = 'SELECT p.name_ingame as name_ingame ' .
               'FROM player_auth as a, player as p ' .
               'WHERE a.player_id = :id AND a.auth_key = :auth_key AND a.player_id = p.id';
        $query = $conn->prepare($sql);
        $query->execute(array(':id'       => $auth_userid,
                              ':auth_key' => $auth_key));
        $resultArray = $query->fetchAll();
        if (count($resultArray) == 1) {
            $name_ingame = $resultArray[0]['name_ingame'];
            session_regenerate_id(TRUE);
            $_SESSION['user_id'] = $auth_userid;
            $_SESSION['user_name'] = $name_ingame;
            $_SESSION['user_lastactive'] = time();
            return TRUE;
        }
    }

    // neither session nor cookie lookup worked, so the user is not logged in
    return FALSE;
}

function logout() {
    if (array_key_exists('auth_userid', $_COOKIE) &&
        array_key_exists('auth_key', $_COOKIE) &&
        array_key_exists('user_id', $_SESSION) &&
        $_SESSION['user_id'] == $_COOKIE['auth_userid']) {

        require_once '../database/mysql.inc.php';
        $conn = conn();

        $sql = 'DELETE FROM player_auth
                WHERE player_id = :id AND auth_key = :auth_key';
        $query = $conn->prepare($sql);
        $query->execute(array(
            ':id' => $_SESSION['user_id'],
            ':auth_key' => $_COOKIE['auth_key'],
        ));

        $_SESSION = array();

        setcookie('auth_key', '', time()-3600, '/');
        setcookie('auth_userid', '', time()-3600, '/');

        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time()-3600,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );

        session_destroy();
        session_write_close();
    } else {
        return NULL;
    }
}
