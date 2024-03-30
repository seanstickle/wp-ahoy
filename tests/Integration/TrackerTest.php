<?php

namespace Tests;

class TrackerTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    protected $tester;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_no_request(): void
    {
        $ahoy = new \Ahoy\Tracker();
        $ahoy->track("Some event", ['some_prop' => true]);
        $event = \Ahoy\Event::last();
        $this->assertEquals("Some event", $event->name);
        $this->assertEquals(['some_prop' => true], (array) $event->properties);
        $this->assertEquals(0, $event->user_id);
    }

    public function test_user_option(): void
    {
        $user = new \stdClass();
        $user->id = 123;
        $ahoy = new \Ahoy\Tracker(['user' => $user]);
        $this->assertEquals($user->id, $ahoy->user->id);
        $ahoy->track("Some event", ['some_prop' => true]);
        $event = \Ahoy\Event::last();
        $this->assertEquals($user->id, $event->user_id);
    }
}
