<?php

namespace Ahoy;

require "lib/ahoy/store.php";

use PHPUnit\Framework\TestCase;
use Ahoy\Store;

final class StoreTest extends TestCase
{
    public function testStore()
    {
        $store = new Store();
        $this->assertInstanceOf(Store::class, $store);
    }
}
