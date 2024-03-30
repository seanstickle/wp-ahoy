<?php

namespace Tests\EndToEnd;

use Tests\Support\EndToEndTester;

class VisitPropertiesCest
{
    public function test_it_deactivates_activates_correctly(EndToEndTester $I): void
    {
        $referrer = "http://www.example.com";

        $I->setHeader("Referer", $referrer);
        $I->amOnPage('/sample-page');
        $I->seeResponseCodeIs(200);
        $I->amOnPage('/fake-page');
        $I->seeResponseCodeIs(404);

        $last = \Ahoy\Event::last();
    }
}
