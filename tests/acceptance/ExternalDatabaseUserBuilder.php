<?php


class ExternalDatabaseUserBuilder {
    private $username;
    private $hashedPassword;
    private $state;

    function __construct() {
        $this->username = $this->_generateUsername("user-");
        $this->hashedPassword = $this->generatePasswordHash('password');
        $this->state = 'active';
    }

    private function generatePasswordHash($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    function _generateUsername() {
        return substr(uniqid("user-"), 0, 20);
    }

    function withUsername($username) {
        $this->username = $username;
        return $this;
    }

    function withPassword($password) {
        $this->hashedPassword = $this->generatePasswordHash($password);
        return $this;
    }

    function withState($state) {
        $this->state = $state;
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
            $this->state,
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

