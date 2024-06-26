<?php

namespace Tests;

class TrackerTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    public function test_visit_event_relationship(): void
    {
        $ahoy = new \Ahoy\Tracker();
        $ahoy->track("Some event", ['some_prop' => true]);

        $visit = \Ahoy\Visit::last();
        $event = \Ahoy\Event::last();

        $this->assertEquals($visit->id, $event->visit_id);
    }

    public function test_visit_with_no_request(): void
    {
        $ahoy = new \Ahoy\Tracker();
        $ahoy->track("Some event", ['some_prop' => true]);

        $event = \Ahoy\Event::last();
        $this->assertEquals("Some event", $event->name);
        $this->assertEquals(['some_prop' => true], (array) $event->properties);
        $this->assertEquals(0, $event->user_id);
    }

    public function test_visit_with_minimal_request(): void
    {
        $_SERVER['REQUEST_URI']     = '/';
        $_SERVER['HTTP_HOST']       = 'test.host';
        $_SERVER['REMOTE_ADDR']     = '0.0.0.0';
        $_SERVER['HTTP_USER_AGENT'] = 'WordPress Testing';

        $_GET['foo']                = 'bar';

        $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);

        $ahoy = new \Ahoy\Tracker(['block_bots' => false]);

        $ahoy->track("Some event", ['some_prop' => true]);

        $event = \Ahoy\Event::last();
        $this->assertEquals("Some event", $event->name);
        $this->assertEquals(['some_prop' => true], (array) $event->properties);
        $this->assertEquals(0, $event->user_id);
    }

    public function test_visit_with_detailed_request(): void
    {
        $_SERVER['REQUEST_URI']     = '/products/?utm_source=facebook&utm_medium=social&utm_term=keyword1&utm_content=ad1&utm_campaign=summer_sale';
        $_SERVER['HTTP_REFERER']    = 'http://facebook.com';
        $_SERVER['HTTP_HOST']       = 'test.host';
        $_SERVER['REMOTE_ADDR']     = '0.0.0.0';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Firefox/98.0';

        $_GET['utm_source']         = 'facebook';
        $_GET['utm_medium']         = 'social';
        $_GET['utm_term']           = 'keyword1';
        $_GET['utm_content']        = 'ad1';
        $_GET['utm_campaign']       = 'summer_sale';

        $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);

        $ahoy = new \Ahoy\Tracker();
        $ahoy->track("Some event", ['some_prop' => 'some_value']);

        $visit = \Ahoy\Visit::last();
        codecept_debug(print_r($visit, true));

        $event = \Ahoy\Event::last();
        codecept_debug(print_r($event, true));

        $this->assertEquals("Some event", $event->name);
        $this->assertEquals(['some_prop' => true], (array) $event->properties);
        $this->assertEquals(0, $event->user_id);
    }
}
