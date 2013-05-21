<?php
    try {
        $conn = new PDO('mysql:host=127.0.0.1;port=3306;dbname=buttonmen', 'karlene', 'Xb4jBUSs');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
?>
