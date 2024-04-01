<?php

namespace Tests;

class TrustedTimeTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    public function test_with_no_time(): void
    {
        $ahoy = new \Ahoy\Tracker(['api' => true]);

        $trustedTime = $ahoy->getTrustedTime();
        $this->assertGreaterThan(0, $trustedTime);
    }

    public function test_with_api_and_early_time(): void
    {
        $ahoy = new \Ahoy\Tracker(['api' => true]);
        $ts = strtotime('-1 day');

        $trustedTime = $ahoy->getTrustedTime($ts);
        $this->assertGreaterThan($ts, $trustedTime);
    }

    public function test_without_api_and_early_time(): void
    {
        $ahoy = new \Ahoy\Tracker(['api' => false]);
        $ts = strtotime('-1 day');

        $trustedTime = $ahoy->getTrustedTime($ts);
        $this->assertEquals($ts, $trustedTime);
    }

    public function test_with_api_and_current_time(): void
    {
        $ahoy = new \Ahoy\Tracker(['api' => true]);
        $ts = time();

        $trustedTime = $ahoy->getTrustedTime($ts);
        $this->assertEquals($ts, $trustedTime);
    }

    public function test_without_api_and_current_time(): void
    {
        $ahoy = new \Ahoy\Tracker(['api' => false]);
        $ts = time();

        $trustedTime = $ahoy->getTrustedTime($ts);
        $this->assertEquals($ts, $trustedTime);
    }

    public function test_with_api_and_late_time(): void
    {
        $ahoy = new \Ahoy\Tracker(['api' => true]);
        $ts = strtotime('+1 day');

        $trustedTime = $ahoy->getTrustedTime($ts);
        $this->assertLessThan($ts, $trustedTime);
    }

    public function test_without_api_and_late_time(): void
    {
        $ahoy = new \Ahoy\Tracker(['api' => false]);
        $ts = strtotime('+1 day');

        $trustedTime = $ahoy->getTrustedTime($ts);
        $this->assertEquals($ts, $trustedTime);
    }
}
