<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

use function PHPUnit\Framework\assertEquals;

class VisitPropertiesCest
{

    public function test_standard(EndToEndTester $I): void
    {
        $referrer = "http://www.example.com";
        $I->setHeader('Referer', $referrer);
        $I->amOnPage('/sample-page');

        $visit = $I->grabLatestEntryByFromDatabase('wp_ahoy_visits');
        codecept_debug("Last Visit: " . print_r($visit, true));
        // assertEquals($referrer, $visit['referrer']);
        // assertEquals("www.example.com", $visit['referring_domain']);
        // assertEquals("http://www.example.com/sample-page", $visit['landing_page']);
        // assertEquals("127.0.0.1", $visit['ip']);
    }
}
