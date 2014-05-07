<?php

/**
 * BMInterfaceBase: parent class for interfaces between GUI and BMGame
 *
 * @author Jota
 *
 * This class should contain all interface functions which may be
 * called by both BMInterface methods and BMInterfaceNewuser methods.
 *
 * NOTE: Since BMInterfaceNewuser inherits from this class, any methods in it
 * are exposed to unauthenticated clients.
 *
 */
abstract class BMInterfaceBase {
    // properties
    protected static $conn = NULL;    // connection to database
}
