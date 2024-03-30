<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

class VisitApiCest
{
    protected function visitData(): array
    {
        return [
            'visit_token' => 'visitId',
            'visitor_token' => 'visitorId',
            'platform' => 'config.platform',
            'landing_page' => 'window.location.href',
            'screen_width' => 'window.screen.width',
            'screen_height' => 'window.screen.height',
            'js' => 'true',
        ];
    }

    public function test_it_rejects_visit_api_without_nonce(EndToEndTester $I): void
    {
        $I->sendAjaxPostRequest('/wp-json/ahoy/v1/visits', $this->visitData());
        $I->seeResponseCodeIs(401); // unauthorized
    }

    public function test_it_allows_visit_api_with_nonce(EndToEndTester $I): void
    {
        $I->amOnPage('/sample-page'); # post ID = 2
        $nonce = $I->grabAttributeFrom('html head meta[name=ahoy]', 'content');
        $I->sendAjaxPostRequest('/wp-json/ahoy/v1/visits', array_merge($this->visitData(), ['_wpnonce' => $nonce]));
        $I->seeResponseCodeIs(201); // created
    }

    public function test_it_creates_a_visit(EndToEndTester $I): void
    {
        $I->amOnPage('/sample-page');
        $nonce = $I->grabAttributeFrom('html head meta[name=ahoy]', 'content');
        $I->sendAjaxPostRequest('/wp-json/ahoy/v1/visits', array_merge($this->visitData(), ['_wpnonce' => $nonce]));
        $I->seeResponseCodeIs(201); // created
        $I->seeInDatabase('wp_ahoy_visits', ['visit_token' => 'visitId']);
    }
}
