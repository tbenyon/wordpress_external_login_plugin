<?php

class SettingsBuilder {

    function __construct() {}

    function setDefaultSettings() {
        $conn = DatabaseTools::_generateWordpressConnection();
        foreach (SettingsBuilder::defaultSettings() as $settingName => $settingValue) {
            SettingsBuilder::setSetting($settingName, $settingValue, $conn);
        }
        $conn = null;
    }

    function setSetting($settingName, $settingValue, $externalConnection = false) {
        $conn = $externalConnection ? $externalConnection : DatabaseTools::_generateWordpressConnection();
        $query_string = "INSERT INTO wp_options (option_name,option_value) VALUES ( :settingName, :settingValue ) ON DUPLICATE KEY UPDATE option_value=:settingValue";
        $pdoStatement = $conn->prepare($query_string);
        $pdoStatement->execute(array(':settingName' => $settingName, ':settingValue' => $settingValue));
        if (!$externalConnection) $conn = null;
    }

    function deleteAllPluginSettings() {
        $conn = DatabaseTools::_generateWordpressConnection();
        $query_string = "DELETE FROM `wp_options` WHERE option_name =:optionName;";
        $pdoStatement  = $conn->prepare($query_string);
        foreach (SettingsBuilder::defaultSettings() as $settingName => $settingValue) {
            $pdoStatement->execute(array(':optionName' => $settingName));
        }
        $conn = null;
    }

    function defaultSettings() {
        return array(
            'external_login_option_enable_external_login' => 'on',
            'external_login_option_migration_mode' => '',
            'external_login_option_redirection_type' => '',
            'external_login_option_redirection_location_internal' => '',
            'external_login_option_redirection_location_external' => '',
            'external_login_option_disable_local_login' => '',
            'external_login_option_delete_plugin_settings' => '',
            'external_login_option_db_name' => 'externalDb',
            'external_login_option_db_host' => 'externalDbMySql5.7',
            'external_login_option_db_port' => '3306',
            'external_login_option_db_username' => 'externalDbUser',
            'external_login_option_db_password' => 'externalDbPassword',
            'external_login_option_db_type' => 'mysql',
            'external_login_option_hash_algorithm' => 'bcrypt',
            'external_login_option_db_salting_method' => '',
            'external_login_option_db_salt_location' => '',
            'external_login_option_db_salt' => '',
            'exlog_dbstructure_table' => 'User',
            'exlog_dbstructure_username' => 'NickName',
            'exlog_dbstructure_password' => 'Hash',
            'exlog_dbstructure_salt' => '',
            'exlog_dbstructure_email' => 'Email',
            'exlog_dbstructure_first_name' => 'FirstName',
            'exlog_dbstructure_last_name' => 'LastName',
            'exlog_dbstructure_role' => 'UserType',
            'exlog_multiple_roles_toggle' => '',
            'exlog_multiple_roles_delimiter' => '',
            'exlog_unspecified_role' => '',
            'exlog_roles_custom_fields' => '',
            'external_login_option_enable_exclude_users' => '',
            'exlog_exclude_users_field_name_repeater' => ''
        );
    }
}
