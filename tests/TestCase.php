<?php

namespace Jeremeamia\Iter8\Tests;

use Jeremeamia\Iter8\Iter;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

/**
 * Base test case class for unit tests in the ApiClients package.
 */
abstract class TestCase extends PhpUnitTestCase
{
    protected function assertIterable(array $expected, iterable $iter, bool $preserveKeys = false): void
    {
        $actual = Iter::toArray($iter, $preserveKeys);
        $this->assertEquals($expected, $actual);
    }

    protected function consumeIterable(iterable $iter): void
    {
        foreach ($iter as $_);
    }
}
