<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

class ActivationCest
{
    public function test_it_deactivates_and_activates_correctly(EndToEndTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPluginsPage();
        $I->activatePlugin('ahoy');

        $I->loginAsAdmin();
        $I->amOnPluginsPage();

        $I->seePluginActivated('ahoy');
        $I->deactivatePlugin('ahoy');

        $I->seePluginDeactivated('ahoy');
        $I->activatePlugin('ahoy');

        $I->seePluginActivated('ahoy');
    }

    // public function test_it_tracks_page_views(EndToEndTester $I): void
    // {
    //     $I->amOnPage('/sample-page'); # post ID = 2
    //     $properties = json_encode(["postId" => 2]);
    //     $I->seeInDatabase('wp_ahoy_events', ['properties' => $properties]);
    // }

}
