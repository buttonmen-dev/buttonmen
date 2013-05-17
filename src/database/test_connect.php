<!DOCTYPE html>
<html>
<!--
    This document should be written in polyglot HTML5/XHTML5, and therefore
    should run when served either as text/html or application/xhtml+xml.
-->
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>

    <meta charset="utf-8" />
    <title>Retrieve button data from database</title>
    <link rel="stylesheet" href="gui.css" />

</head>

<body>
    <?php
        $id = 1;
        require_once('mysql.inc.php');
        $statement = $conn->prepare('SELECT * FROM button_definitions WHERE button_id = :id');
        $statement->execute(array('id' => $id));

        while($row = $statement->fetch()) {
            print_r($row);
        }
    ?>

</body>
</html>
