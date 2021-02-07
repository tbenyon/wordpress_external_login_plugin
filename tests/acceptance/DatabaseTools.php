<?php

class DatabaseTools {
    static function _generateWordpressConnection() {
        return DatabaseTools::_generateConnection(
            "mysql",
            '127.0.0.1',
            'wordpress',
            'wordpress',
            'wordpress',
            '3330'
        );
    }

    static function _generateExternalMysqlConnection() {
        return DatabaseTools::_generateConnection(
            "mysql",
            '127.0.0.1',
            'externalDbUser',
            'externalDbPassword',
            'externalDb',
            '3331'
        );
    }

    static private function _generateConnection($driver, $host_name, $user_name, $password, $db_name, $port) {
        try {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES, false
            ];
            $connection = new PDO("$driver:host=$host_name:$port;dbname=$db_name", $user_name, $password, $options);
            return $connection;
        }
        catch(PDOException $e) {
            return false;
        }
    }
}
