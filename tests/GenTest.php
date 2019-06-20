<?php

namespace Jeremeamia\Iter8\Tests;

use InvalidArgumentException;
use Jeremeamia\Iter8\{Gen, Iter};

/**
 * @covers \Jeremeamia\Iter8\Gen
 */
class GenTest extends TestCase
{
    public function testRangeWithNormalStep()
    {
        $iter = Gen::range(3, 7);
        $this->assertIterable([3, 4, 5, 6, 7], $iter);
    }

    public function testRangeWithStepOf2()
    {
        $iter = Gen::range(3, 7, 2);
        $this->assertIterable([3, 5, 7], $iter);
    }

    public function testRangeThatGoesBackwards()
    {
        $iter = Gen::range(7, 3);
        $this->assertIterable([7, 6, 5, 4, 3], $iter);
    }

    public function testRangeWithSameStartAndEnd()
    {
        $iter = Gen::range(3, 3);
        $this->assertIterable([3], $iter);
    }

    public function testRangeRequiresPositiveIntegerForStep()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->consumeIterable(Gen::range(1, 5, 0));
    }

    public function testRepeatWithFiniteLength()
    {
        $iter = Gen::repeat('a', 3);
        $this->assertIterable(['a', 'a', 'a'], $iter);
    }

    public function testRepeatWithInfiniteLength()
    {
        $iter = Iter::take(Gen::repeat('a'), 5);
        $this->assertIterable(['a', 'a', 'a', 'a', 'a'], $iter);
    }

    public function testRepeatForKeys()
    {
        $iter = Gen::repeatForKeys(['a', 'b', 'c'], 2);
        $this->assertIterable(['a' => 2, 'b' => 2, 'c' => 2], $iter, Iter::PRESERVE_KEYS);
    }

    public function testEmpty()
    {
        $iter = Gen::empty();
        $this->assertIterable([], $iter);
    }

    public function testJustWithScalarValue()
    {
        $iter = Gen::just('a');
        $this->assertIterable(['a'], $iter);
    }

    public function testJustWithIterableValue()
    {
        $iter = Gen::just(['a', 'b']);
        $this->assertIterable([['a', 'b']], $iter);
    }

    public function testFromWithScalarValue()
    {
        $iter = Gen::from('a');
        $this->assertIterable(['a'], $iter);
    }

    public function testFromWithIterableValue()
    {
        $iter = Gen::from(['a', 'b']);
        $this->assertIterable(['a', 'b'], $iter);
    }

    public function testDefer()
    {
        $iter = Gen::defer(function () { return ['a', 'b', 'c']; });
        $this->assertIterable(['a', 'b', 'c'], $iter);
    }

    public function testExplodeWithSmallString()
    {
        $iter = Gen::explode('a,b,c', ',');
        $this->assertIterable(['a', 'b', 'c'], $iter);
    }

    public function testExplodeWithLongString()
    {
        $iter = Gen::explode('a,b,c,d,e', ',', 4);
        $this->assertIterable(['a', 'b', 'c', 'd', 'e'], $iter);
    }

    public function testExplodeWithMultiCharDelimiter()
    {
        $iter = Gen::explode('a, b, c', ', ');
        $this->assertIterable(['a', 'b', 'c'], $iter);
    }
}
