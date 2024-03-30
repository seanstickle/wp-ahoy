<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

class VisitPropertiesCest
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

    public function test_it_tracks_page_views(EndToEndTester $I): void
    {
        $I->amOnPage('/sample-page'); # post ID = 2

        $properties = json_encode(["postId" => 2]);
        $I->seeInDatabase('wp_ahoy_events', ['properties' => $properties]);
    }

    public function test_it_rejects_event_api_without_nonce(EndToEndTester $I): void
    {
        $I->amOnPage('/sample-page'); # post ID = 2

        $I->sendAjaxPostRequest('/wp-json/ahoy/v1/events', [
            'name' => 'test_event',
            'properties' => ['foo' => 'bar'],
        ]);

        $I->seeResponseCodeIs(401); // unauthorized
    }

    public function test_it_allows_event_api_with_nonce(EndToEndTester $I): void
    {
        $I->amOnPage('/sample-page'); # post ID = 2

        $csrf = $I->grabAttributeFrom('meta[name=ahoy]', 'content');

        $I->sendAjaxPostRequest('/wp-json/ahoy/v1/events', [
            'name' => 'test_event',
            'properties' => ['foo' => 'bar'],
            '_wpnonce' => $csrf,
        ]);

        $I->seeResponseCodeIs(201); // created
    }
}
