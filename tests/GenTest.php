<?php

namespace Jeremeamia\Iter8\Tests;

use InvalidArgumentException;
use Jeremeamia\Iter8\{Gen, Iter};
use RuntimeException;

/**
 * @covers \Jeremeamia\Iter8\Gen
 */
class GenTest extends TestCase
{
    public function testCanCreateIterableWithRangeUsingNormalStep()
    {
        $iter = Gen::range(3, 7);
        $this->assertIterable([3, 4, 5, 6, 7], $iter);
    }

    public function testCanCreateIterableWithRangeUsingStepOf2()
    {
        $iter = Gen::range(3, 7, 2);
        $this->assertIterable([3, 5, 7], $iter);
    }

    public function testCanCreateIterableWithRangeThatGoesBackwards()
    {
        $iter = Gen::range(7, 3);
        $this->assertIterable([7, 6, 5, 4, 3], $iter);
    }

    public function testCanCreateIterableWithRangeWithSameStartAndEnd()
    {
        $iter = Gen::range(3, 3);
        $this->assertIterable([3], $iter);
    }

    public function testRangeRequiresPositiveIntegerForStep()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->consumeIterable(Gen::range(1, 5, 0));
    }

    public function testCanCreateIterableWithRepeatUsingFiniteLength()
    {
        $iter = Gen::repeat('a', 3);
        $this->assertIterable(['a', 'a', 'a'], $iter);
    }

    public function testCanCreateIterableWithRepeatUsingInfiniteLength()
    {
        $iter = Iter::take(Gen::repeat('a'), 5);
        $this->assertIterable(['a', 'a', 'a', 'a', 'a'], $iter);
    }

    public function testCanCreateIterableWithRepeatForKeys()
    {
        $iter = Gen::repeatForKeys(['a', 'b', 'c'], 2);
        $this->assertIterable(['a' => 2, 'b' => 2, 'c' => 2], $iter, Iter::PRESERVE_KEYS);
    }

    public function testCanCreateEmptyIterable()
    {
        $iter = Gen::empty();
        $this->assertIterable([], $iter);
    }

    public function testCanCreateIterableWithJustUsingScalarValue()
    {
        $iter = Gen::just('a');
        $this->assertIterable(['a'], $iter);
    }

    public function testCanCreateIterableWithJustUsingIterableValue()
    {
        $iter = Gen::just(['a', 'b']);
        $this->assertIterable([['a', 'b']], $iter);
    }

    public function testCanCreateIterableWithFromUsingScalarValue()
    {
        $iter = Gen::from('a');
        $this->assertIterable(['a'], $iter);
    }

    public function testCanCreateIterableWithFromUsingIterableValue()
    {
        $iter = Gen::from(['a', 'b']);
        $this->assertIterable(['a', 'b'], $iter);
    }

    public function testCanCreateIterableWithDefer()
    {
        $iter = Gen::defer(function () { return ['a', 'b', 'c']; });
        $this->assertIterable(['a', 'b', 'c'], $iter);
    }

    public function testCanCreateIterableWithExplodeUsingSmallString()
    {
        $iter = Gen::explode('a,b,c', ',');
        $this->assertIterable(['a', 'b', 'c'], $iter);
    }

    public function testCanCreateIterableWithExplodeUsingLongString()
    {
        $iter = Gen::explode('a,b,c,d,e', ',', 4);
        $this->assertIterable(['a', 'b', 'c', 'd', 'e'], $iter);
    }

    public function testCanCreateIterableWithExplodeUsingMultiCharDelimiter()
    {
        $iter = Gen::explode('a, b, c', ', ');
        $this->assertIterable(['a', 'b', 'c'], $iter);
    }

    public function testCanCreateIterableByReadingFromStream()
    {
        $stream = fopen('php://temp', 'w+');
        if ($stream === false) {
            throw new RuntimeException('Failed to open stream.');
        }

        fwrite($stream, 'There is data here.');
        fseek($stream, 0);

        $iter = Gen::fromStream($stream, 4);
        $this->assertIterable(['Ther', 'e is', ' dat', 'a he', 're.'], $iter);
    }
}
