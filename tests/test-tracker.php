<?php

namespace Ahoy;

use PHPUnit\Framework\TestCase;

final class TrackerTest extends TestCase
{
    public function testNoRequest(): void
    {
        $ahoy = new Tracker();
        $ahoy->track("Some event", ['some_prop' => true]);
        $event = Event::last();
        $this->assertEquals("Some event", $event->name);
        $this->assertEquals(['some_prop' => true], (array) $event->properties);
        $this->assertEquals(0, $event->user_id);
    }

    // TODO: implement testNoCookies

    //   def test_no_cookies
    //     request = ActionDispatch::TestRequest.create

    //     with_options(cookies: :none) do
    //       ahoy = Ahoy::Tracker.new(request: request)
    //       ahoy.track("Some event", some_prop: true)
    //     end

    //     event = Ahoy::Event.last
    //     assert_equal "Some event", event.name
    //     assert_equal({"some_prop" => true}, event.properties)
    //     assert_nil event.user_id
    //   end

    public function testUserOption(): void
    {
        $user = new \stdClass();
        $user->id = 123;
        $ahoy = new Tracker(['user' => $user]);
        $this->assertEquals($user->id, $ahoy->user->id);
        $ahoy->track("Some event", ['some_prop' => true]);
        $event = Event::last();
        $this->assertEquals($user->id, $event->user_id);
    }
}
