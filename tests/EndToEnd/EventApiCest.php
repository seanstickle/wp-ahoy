<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

class EventApiCest
{
    protected function eventData(): array
    {
        return [
            'name' => 'test_event',
            'properties' => ['foo' => 'bar'],
        ];
    }

    public function test_it_rejects_event_api_without_nonce(EndToEndTester $I): void
    {
        $I->sendAjaxPostRequest('/wp-json/ahoy/v1/events', $this->eventData());
        $I->seeResponseCodeIs(401); // unauthorized
    }

    public function test_it_allows_event_api_with_nonce(EndToEndTester $I): void
    {
        $I->amOnPage('/sample-page');
        $nonce = $I->grabAttributeFrom('html head meta[name=ahoy]', 'content');
        $I->sendAjaxPostRequest('/wp-json/ahoy/v1/events', array_merge($this->eventData(), ['_wpnonce' => $nonce]));
        $I->seeResponseCodeIs(201); // created
    }
}
