<?php
include_once './utils/SettingsBuilder.php';

class OptionsPageCest
{
    public function _before(AcceptanceTester $I)
    {
        SettingsBuilder::setDefaultSettings();
        $I->amOnPage('/wp-login.php?action=logout');
    }

    // Commented this out for now with the underscore as WIP
    public function _seeProBannerIfAsAProUser(AcceptanceTester $I)
    {
        $I->setCookie('exlog_test_plan', 'pro');
        $I->amOnPage('/wp-login.php');
        $I->fillField('#user_login', 'admin');
        $I->fillField('#user_pass', 'admin');
        $I->click('#wp-submit');
        $I->seeInCurrentUrl('wp-admin');
        $I->see('Dashboard');
        $I->seeInCurrentUrl('wp-admin');
        $I->see('Dashboard');
        $I->amOnPage('/wp-admin/edit.php');
        $I->see('Dashboard');

        $I->see('Search Posts');
//        $I->see('Thanks for buy the pro version of the plugin.');

    }
}
