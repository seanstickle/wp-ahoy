<?php

namespace Ahoy;

require "lib/ahoy/VisitProperties.php";

use PHPUnit\Framework\TestCase;
use Ahoy\VisitProperties;

final class VisitPropertiesTest extends TestCase
{
    public $visitProperties;
    public $apiRequest;

    protected function setUp(): void
    {
        $this->apiRequest = [
            'params' => [
                'utm_source' => 'email',
                'utm_medium' => 'newsletter',
                'utm_term' => 'ahoy',
                'utm_content' => 'header',
                'utm_campaign' => 'promo',
                'app_version' => '1.0.0',
                'landing_page' => 'https://example.com',
                'platform' => 'web',
                'os_version' => '10.15.6',
                'referrer' => 'https://ahoy.dev',
                'screen_height' => '720',
                'screen_width' => '1280',
            ],
        ];
    }

    public function testVisitProperties(): void
    {
        $visitProperties = new VisitProperties($this->apiRequest, true);
        print_r($visitProperties);
        $this->assertTrue(true);
    }
}
