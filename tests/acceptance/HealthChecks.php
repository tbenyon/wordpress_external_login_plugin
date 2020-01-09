<?php
require_once './tests/acceptance/ExternalLoginCest.php';
require_once './tests/acceptance/DatabaseTools.php';

class HealthChecks {
    private $delayBetweenAttempts = 1;

    function __construct($attempts = 30, $delayBetweenAttempts = false) {
        if ($delayBetweenAttempts) {
            $this->$delayBetweenAttempts = $delayBetweenAttempts;
        }

        $dbHealthChecks = array(
            array(
                "name" => 'WordPress MySQL',
                "generateConnectionFunction" => 'DatabaseTools::_generateWordpressConnection',
                "tableToFind" => 'wp_options'
            ),
            array(
                "name" => 'External MySQL',
                "generateConnectionFunction" => 'DatabaseTools::_generateExternalMysqlConnection',
                "tableToFind" => 'User'
            ),
        );

        $currentCheck = $dbHealthChecks[0];
        $firstCheck = true;
        while ($attempts - 1) {
            if(!$firstCheck) {
                error_log('Retrying...');
            } else {
                $firstCheck = false;
            }

            $currentCheck = $dbHealthChecks[0];
            $connection = call_user_func($currentCheck['generateConnectionFunction']);
            if (!$connection) {
                $this->databaseCheckFailed('Failed connecting to ' . $currentCheck['name'], $attempts);
            } elseif (!$this->isDbReady(call_user_func($currentCheck['generateConnectionFunction']), $currentCheck['tableToFind'])) {
                $this->databaseCheckFailed('Data not ready in ' . $currentCheck['name'], $attempts);
            } else {
                error_log($currentCheck['name'] . ' Database ready!');
                array_shift($dbHealthChecks);
                $firstCheck = true;
            };

            if (count($dbHealthChecks) == 0) {
                error_log('All Databases ready!');
                return true;
            }
        }
        die('Unable to connect to DB "' . $currentCheck['name'] . '".');
    }

    private function databaseCheckFailed($errorMessage, &$attempts) {
        sleep($this->delayBetweenAttempts);
        $attempts -= 1;
        error_log($errorMessage);
    }

    function isDbReady($conn, $tableToFind) {
        if ($conn == false) {
            return false;
        }
        $query_string = "SHOW tables;";
        $pdoStatement = $conn->prepare($query_string);
        $pdoStatement->execute();
        $tables = $pdoStatement->fetchAll(PDO::FETCH_NUM);
        $conn = null;
        return HealthChecks::doesTableExist($tables, $tableToFind);
    }

    function doesTableExist($tables, $tableToFind) {
        foreach ($tables as $table) {
            if (in_array($tableToFind, $table)) {
                return true;
            };
        }
        return false;
    }
}

new HealthChecks();
