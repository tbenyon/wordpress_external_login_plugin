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

        $I->amOnPage('/wp-login.php');
        $I->see('Log In');
        $I->fillField('#user_login', $username);
        $I->fillField('#user_pass', $password);
        $I->click('#wp-submit');
        $I->seeInCurrentUrl('wp-admin');

        $this->_deleteUserInExternalDatabase($user);
        $this->_deleteUserInWordpressDatabase($user);
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
}
