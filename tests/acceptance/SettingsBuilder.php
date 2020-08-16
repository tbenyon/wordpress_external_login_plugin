<?php


class SettingsBuilder {
    private $settings;

    function __construct() {
        $this->settings = $this->getSettings();
    }

    function tempSetSetting() {
        $conn = DatabaseTools::_generateWordpressConnection();
        $query_string = "INSERT INTO wp_options (option_name,option_value) VALUES ('external_login_option_enable_external_login','example') ON DUPLICATE KEY UPDATE option_value='example'";
        $pdoStatement  = $conn->prepare($query_string);
        foreach (SettingsBuilder::getSettings() as $settingName) {
            error_log($settingName);
            $pdoStatement->execute(array(':optionName' => $settingName));
        }
        $conn = null;
    }

    function deleteAllPluginSettings() {
        $conn = DatabaseTools::_generateWordpressConnection();
        $query_string = "DELETE FROM `wp_options` WHERE option_name =:optionName;";
        $pdoStatement  = $conn->prepare($query_string);
        foreach (SettingsBuilder::getSettings() as $settingName) {
            error_log($settingName);
            $pdoStatement->execute(array(':optionName' => $settingName));
        }
        $conn = null;
    }

    function getSettings() {
        return array(
            'external_login_option_enable_external_login',
            'external_login_option_migration_mode',
            'external_login_option_redirection_type',
            'external_login_option_redirection_location_internal',
            'external_login_option_redirection_location_external',
            'external_login_option_disable_local_login',
            'external_login_option_delete_plugin_settings',
            'external_login_option_db_name',
            'external_login_option_db_host',
            'external_login_option_db_port',
            'external_login_option_db_username',
            'external_login_option_db_password',
            'external_login_option_db_type',
            'external_login_option_hash_algorithm',
            'external_login_option_db_salting_method',
            'external_login_option_db_salt_location',
            'external_login_option_db_salt',
            'exlog_dbstructure_table',
            'exlog_dbstructure_username',
            'exlog_dbstructure_password',
            'exlog_dbstructure_salt',
            'exlog_dbstructure_email',
            'exlog_dbstructure_first_name',
            'exlog_dbstructure_last_name',
            'exlog_dbstructure_role',
            'exlog_multiple_roles_toggle',
            'exlog_multiple_roles_delimiter',
            'exlog_unspecified_role',
            'exlog_roles_custom_fields',
            'external_login_option_enable_exclude_users',
            'exlog_exclude_users_field_name_repeater'
        );
    }
}
