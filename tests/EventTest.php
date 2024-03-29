<?php

namespace Ahoy;

require "lib/ahoy/event.php";

use PHPUnit\Framework\TestCase;
use Ahoy\Event;

final class EventTest extends TestCase
{
    public function testEvent()
    {
        $event = new Event();
        $this->assertInstanceOf(Event::class, $event);
    }
}
