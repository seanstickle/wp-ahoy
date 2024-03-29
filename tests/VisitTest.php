<?php

namespace Ahoy;

require "lib/ahoy/visit.php";

use PHPUnit\Framework\TestCase;
use Ahoy\Visit;

final class VisitTest extends TestCase
{
    public function testVisit()
    {
        $visit = new Visit();
        $this->assertInstanceOf(Visit::class, $visit);
    }
}
