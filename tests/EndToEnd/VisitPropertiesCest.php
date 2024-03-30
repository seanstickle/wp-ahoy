<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

class VisitPropertiesCest
{
    public function test_it_deactivates_activates_correctly(EndToEndTester $I): void
    {
        $I->loginAsAdmin();
        $I->amOnPluginsPage();
        $I->activatePlugin('ahoy');

        $I->amOnPage('/sample-page');
        $I->see('Sample Page');

        $properties = json_encode(["postId" => 123]);
        $I->seeInDatabase('wp_ahoy_events', ['properties' => $properties]);
    }
}
