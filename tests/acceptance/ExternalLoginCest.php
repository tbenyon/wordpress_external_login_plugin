<?php

require_once './tests/acceptance/DatabaseTools.php';
require_once './tests/acceptance/ExternalDatabaseUserBuilder.php';
include_once './utils/SettingsBuilder.php';


class ExternalLoginCest
{
    public function _before(AcceptanceTester $I)
    {
        SettingsBuilder::setDefaultSettings();
        $I->amOnPage('/wp-login.php?action=logout');
    }

    public function shouldAllowLoginFromExternalDatabaseUser(AcceptanceTester $I)
    {
        $username = ExternalDatabaseUserBuilder::_generateUsername("user-");
        $password = "pass";

        $user = ExternalDatabaseUser::create()
            ->withUsername($username)
            ->withPassword($password)
            ->build();

        $this->_givenUserExistsInExternalMysqlDb($user);

        $this->_attemptLogin($I, $username, $password);
        $I->seeInCurrentUrl('wp-admin');

        $this->_deleteUserFromDatabases($user);
    }

    public function shouldExcludeUsersAsSpecifiedInSettingsPage(AcceptanceTester $I)
    {
        SettingsBuilder::setSetting(
            'exlog_exclude_users_field_name_repeater',
            $this->_buildExcludeUsersData()
        );

        $username = ExternalDatabaseUserBuilder::_generateUsername("user-");
        $password = "pass";

        $user = ExternalDatabaseUser::create()
            ->withUsername($username)
            ->withPassword($password)
            ->withState('block')
            ->build();

        $this->_givenUserExistsInExternalMysqlDb($user);
        $this->_attemptLogin($I, $username, $password);
        $I->see('Invalid username or password');

        $this->_deleteUserFromDatabases($user);
    }

    function _attemptLogin($I, $username, $password) {
        $I->amOnPage('/wp-login.php');
        $I->see('Log In');
        $I->fillField('#user_login', $username);
        $I->fillField('#user_pass', $password);
        $I->click('#wp-submit');
    }

    function _givenUserExistsInExternalMysqlDb($user) {
        $values = array(
            ':firstName' => $user->firstName,
            ':lastName' => $user->lastName,
            ':username' => $user->username,
            ':dob' => $user->dob,
            ':userType' => $user->userType,
            ':hashedPassword' => $user->hashedPassword,
            ':state' => $user->state,
            ':email' => $user->email,
            ':createdDate' => $user->createdDate,
        );

        $conn = DatabaseTools::_generateExternalMysqlConnection();

        $query_string = "INSERT INTO `User` (`FirstName`, `LastName`, `NickName`, `DOB`, `UserType`, `Hash`, `cms_state`, `Email`, `CreatedDate`)";
        $query_string .= "VALUES (:firstName,:lastName,:username,:dob,:userType,:hashedPassword,:state,:email,:createdDate);";
        $pdoStatement  = $conn->prepare($query_string);
        $pdoStatement->execute($values);
        $conn = null;
    }

    function _deleteUserInExternalDatabase($user) {
        $query_string = "DELETE FROM `User` WHERE NickName = :username;";
        $conn = DatabaseTools::_generateExternalMysqlConnection();
        $pdoStatement  = $conn->prepare($query_string);
        $pdoStatement->execute(array(':username' => $user->username));
        $conn = null;
    }

    function _deleteUserInWordpressDatabase($user) {
        $query_string = "DELETE FROM `wp_users` WHERE user_login = :username;";
        $conn = DatabaseTools::_generateWordpressConnection();
        $pdoStatement  = $conn->prepare($query_string);
        $pdoStatement->execute(array(':username' => $user->username));
        $conn = null;
    }

    function _deleteUserFromDatabases($user) {
        $this->_deleteUserInWordpressDatabase($user);
        $this->_deleteUserInExternalDatabase($user);
    }

    function _buildExcludeUsersData() {
        $excludeField = 'cms_state';
        $excludeValue = 'block';
        $excludeUsersData = array(
            array(
                array(
                    'name' => 'exlog_exclude_users_field_name',
                    "repeater_field" => false,
                    "value" => $excludeField
                ),
                array(
                    "name" => "exlog_exclude_users_field_value_repeater",
                    "repeater_field" => true,
                    "value" => array(
                        array(
                            array(
                                "name" => "exlog_exclude_users_field_value",
                                "repeater_field" => false,
                                "value" => $excludeValue
                            )
                        )
                    )
                )
            )
        );

        return base64_encode(json_encode($excludeUsersData));
    }
}
