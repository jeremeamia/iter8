<?php

namespace Jeremeamia\Iter8\Tests;

use Jeremeamia\Iter8\{Collection, Func};

/**
 * @covers \Jeremeamia\Iter8\Collection
 */
class CollectionTest extends TestCase
{
    public function testCanUseACollectionWithAFluentInterfaceAndProxyToGenAndIterFunctions()
    {
        // Create a collection from a Gen operation and apply an Iter operation.
        $collection = Collection::range(1, 9)->filter(Func::even());

        // Fluently apply an Iter operation and cast to string.
        $this->assertEquals('2|4|6|8', (string) $collection->interpose('|'));

        // Check debugInfo that would be used for var_dump.
        $this->assertEquals(['data' => [2, 4, 6, 8]], $collection->__debugInfo());

        // Use a terminal Iter operation (should return the terminal value, not another collection).
        $this->assertEquals([2, 4, 6, 8], $collection->toArray());
    }
}
