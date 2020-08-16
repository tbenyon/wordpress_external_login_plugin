<?php

include './tests/acceptance/SettingsBuilder.php';

class LocalLoginCest
{
    public function _before(AcceptanceTester $I)
    {
        SettingsBuilder::tempSetSetting();
        $I->amOnPage('/wp-login.php?action=logout');
    }

    public function loginWithCorrectCredentials(AcceptanceTester $I)
    {
        $I->amOnPage('/wp-login.php');
        $I->see('Log In');
        $I->fillField('#user_login', 'admin');
        $I->fillField('#user_pass', 'admin');
        $I->click('#wp-submit');
        $I->seeInCurrentUrl('wp-admin');
    }

    public function loginWithIncorrectCredentials(AcceptanceTester $I)
    {
        $I->amOnPage('/wp-login.php');
        $I->see('Log In');
        $I->fillField('#user_login', 'admin');
        $I->fillField('#user_pass', 'wrongPassword');
        $I->click('#wp-submit');
        $I->seeInCurrentUrl('wp-login');
    }
}
