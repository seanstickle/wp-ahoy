<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

class ActivationCest
{
    public function test_it_deactivates_activates_correctly(EndToEndTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPluginsPage();

        $I->seePluginActivated('ahoy');

        $I->deactivatePlugin('ahoy');

        $I->seePluginDeactivated('ahoy');

        $I->activatePlugin('ahoy');

        $I->seePluginActivated('ahoy');
    }
}
