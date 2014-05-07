<?php

/**
 * BMInterfaceBase: parent class for interfaces between GUI and BMGame
 *
 * @author Jota
 *
 * This class should contain all interface functions which may be
 * called by both BMInterface methods and BMInterfaceNewuser methods.
 *
 */
abstract class BMInterfaceBase {
    // properties
    protected static $conn = NULL;    // connection to database

    protected function get_config($conf_key) {
        try {
            $query = 'SELECT conf_value FROM config WHERE conf_key = :conf_key';
            $statement = self::$conn->prepare($query);
            $statement->execute(array(':conf_key' => $conf_key));
            $fetchResult = $statement->fetchAll();

            if (count($fetchResult) != 1) {
                error_log("Wrong number of config values with key " . $conf_key);
                return NULL;
            }
            return $fetchResult[0]['conf_value'];
        } catch (Exception $e) {
            error_log(
                "Caught exception in BMInterface::get_config: " .
                $e->getMessage()
            );
            return NULL;
        }
    }
}
