<?php
    try {
        $conn = new PDO('mysql:host=localhost;unix_socket=/tmp/mysql/buttonmen.sock;dbname=buttonmen', 'karlene', 'Xb4jBUSs');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
?>
