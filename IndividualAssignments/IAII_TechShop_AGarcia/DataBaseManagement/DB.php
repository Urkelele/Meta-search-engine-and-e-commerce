<?php

class DB {
    private static $mySqli = null;

    public static function get() {
        if (self::$mySqli === null) {
            $cfg = include __DIR__ . '/../config/config.php';

            try 
            {
                self::$mySqli = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_name']);
            }
            catch (Exception $exc)
            {
                die("DB connection error: " . $exc->getMessage());
            }
        }
        return self::$mySqli;
    }
}
?>