<?php

namespace Jeremeamia\Iter8\Tests;

use ArrayIterator;
use IteratorAggregate;
use Jeremeamia\Iter8\{Gen, Iter, RewindableIterator};
use NoRewindIterator;

/**
 * @covers \Jeremeamia\Iter8\RewindableIterator
 */
class RewindableIteratorTest extends TestCase
{
    public function testRewindableAllowsRewindingOfGenerators()
    {
        $iter = Iter::rewindable(Gen::range(1, 3));
        $results = '';
        foreach ($iter as $key => $val) {
            $results .= $val;
        }
        foreach ($iter as $key => $val) {
            $results .= $val;
        }
        $this->assertEquals('123123', $results);

        $this->assertIterable([1, 2, 3], $iter);
    }

    public function testRewindableEagerlyRewindsWhenGettingInnerIterator()
    {
        /** @var RewindableIterator $iter */
        $iter = Iter::rewindable(Gen::range(1, 3));
        $this->assertSame(0, $iter->key());

        $iterator = $iter->getInnerIterator();
        $this->assertEquals([[0, 1], [1, 2], [2, 3]], iterator_to_array($iterator));
    }

    public function testRewindableIsCountable()
    {
        $iter = Iter::rewindable(Gen::range(1, 5));
        $this->assertEquals(5, count($iter));
    }

    public function testRewindableIsSeekable()
    {
        $iter = Iter::rewindable(Gen::range(1, 5))->seek(2);
        $this->assertEquals(3, $iter->current());
    }

    /**
     * @param iterable $iterable
     * @dataProvider providesRewindableTestCases
     */
    public function testRewindableWorksWithAllIterables(iterable $iterable)
    {
        $rewindable = Iter::rewindable($iterable);
        $this->assertEquals(['a', 'b', 'c'], Iter::toArray($rewindable));
    }

    public function providesRewindableTestCases()
    {
        $data = ['a', 'b', 'c'];

        yield 'array' => [$data];

        yield 'iterator' => [new ArrayIterator($data)];

        yield 'generator' => [Gen::from($data)];

        yield 'no-rewind-iterator' => [new NoRewindIterator(new ArrayIterator($data))];

        yield 'iterator-aggregate' => [new class($data) implements IteratorAggregate {
            private $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function getIterator()
            {
                return new ArrayIterator($this->data);
            }
        }];
    }

    public function testRewindableWithNonFlatGenerator()
    {
        $rewindable = Iter::rewindable((function () {
            yield 1;
            yield from [2, 3];
            yield 4;
            yield from [5];
        })());
        $this->assertIterable([1, 2, 3, 4, 5], $rewindable);
        $this->assertIterable([0, 0, 1, 1, 0], Iter::keys($rewindable));
    }

    public function testRewindableCanSort()
    {
        $rewindable = Iter::rewindable(Gen::range(1, 5));
        $this->assertIterable([5, 4, 3, 2, 1], $rewindable->sort(function ($a, $b) {
            return $b <=> $a;
        }));
    }

    public function testRewindableCanBeInstantiatedWithAnotherRewindableAndItUsesOthersCache()
    {
        $rewindable1 = Iter::rewindable(Gen::range(1, 5));
        $inner1 = $rewindable1->getInnerIterator();
        $rewindable2 = Iter::rewindable($rewindable1);
        $inner2 = $rewindable2->getInnerIterator();

        $this->assertSame($inner1, $inner2);
    }
}
