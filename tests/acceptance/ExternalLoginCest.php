<?php

class ExternalDatabaseUserBuilder {
    private $username;
    private $hashedPassword;

    function __construct() {
        $this->username = _generateUsername("user-");
        $this->hashedPassword = $this->generatePasswordHash('password');
    }

    private function generatePasswordHash($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    function withUsername($username) {
        $this->username = $username;
        return $this;
    }

    function withPassword($password) {
        $this->hashedPassword = $this->generatePasswordHash($password);
        return $this;
    }

    function build() {
        return new ExternalDatabaseUser(
            $this->username,
            $this->hashedPassword,
            'John',
            'Smith',
            'admin',
            '1987-04-23',
            'active',
            'someuser@somedomain.com',
            '2019-01-03'
        );
    }
}

class ExternalDatabaseUser {
    public $username;
    public $hashedPassword;
    public $firstName;
    public $lastName;
    public $userType;
    public $dob;
    public $state;
    public $email;
    public $createdDate;

    function __construct($username, $hashedPassword, $firstName, $lastName, $userType, $dob, $state, $email, $createdDate) {
        $this->username = $username;
        $this->hashedPassword = $hashedPassword;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->userType = $userType;
        $this->dob = $dob;
        $this->state = $state;
        $this->email = $email;
        $this->createdDate = $createdDate;
    }

    static function create() {
        return new ExternalDatabaseUserBuilder();
    }
}

class ExternalLoginCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->amOnPage('/wp-login.php?action=logout');
    }

    public function shouldAllowLoginFromExternalDatabaseUser(AcceptanceTester $I)
    {
        $username = _generateUsername("user-");
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

    function _generateWordpressConnection() {
        return $this->_generateConnection(
            "mysql",
            '127.0.0.1',
            'wordpress',
            'wordpress',
            'wordpress',
            '3330'
        );
    }

    function _generateExternalMysqlConnection() {
        return $this->_generateConnection(
            "mysql",
            '127.0.0.1',
            'externalDbUser',
            'externalDbPassword',
            'externalDb',
            '3331'
        );
    }

    private function _generateConnection($driver, $host_name, $user_name, $password, $db_name, $port) {
        try {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES, false
            ];
            $connection = new PDO("$driver:host=$host_name:$port;dbname=$db_name", $user_name, $password, $options);
            echo 'Connected to database';
            return $connection;
        }
        catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
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

        $conn = $this->_generateExternalMysqlConnection();

        $query_string = "INSERT INTO `User` (`FirstName`, `LastName`, `NickName`, `DOB`, `UserType`, `Hash`, `cms_state`, `Email`, `CreatedDate`)";
        $query_string .= "VALUES (:firstName,:lastName,:username,:dob,:userType,:hashedPassword,:state,:email,:createdDate);";
        $pdoStatement  = $conn->prepare($query_string);
        $pdoStatement->execute($values);
        $conn = null;
    }

    function _deleteUserInExternalDatabase($user) {
        $query_string = "DELETE FROM `User` WHERE NickName = :username;";
        $conn = $this->_generateExternalMysqlConnection();
        $pdoStatement  = $conn->prepare($query_string);
        $pdoStatement->execute(array(':username' => $user->username));
        $conn = null;
    }

    function _deleteUserInWordpressDatabase($user) {
        $query_string = "DELETE FROM `wp_users` WHERE user_login = :username;";
        $conn = $this->_generateWordpressConnection();
        $pdoStatement  = $conn->prepare($query_string);
        $pdoStatement->execute(array(':username' => $user->username));
        $conn = null;
    }
}

function _generateUsername() {
    return substr(uniqid("user-"), 0, 20);
}
