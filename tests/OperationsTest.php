<?php

namespace Jeremeamia\Iter8\Tests;

use Jeremeamia\Iter8\{Func, Iter, Pipe, Collection};

/**
 * @covers \Jeremeamia\Iter8\Iter
 * @covers \Jeremeamia\Iter8\Pipe
 * @covers \Jeremeamia\Iter8\Collection
 */
class IterTest extends TestCase
{
    /**
     * @param string $operation
     * @param iterable $inputIter
     * @param array $inputArgs
     * @param array $expectedOutput
     * @param bool $preserveKeys
     * @dataProvider provideOperationTestCases
     */
    public function testCanApplyTransformativeOperationsViaIter(
        string $operation,
        iterable $inputIter,
        array $inputArgs,
        array $expectedOutput,
        bool $preserveKeys = false
    ) {
        $actualOutput = Iter::{$operation}($inputIter, ...$inputArgs);
        $this->assertIterable($expectedOutput, $actualOutput, $preserveKeys);
    }

    /**
     * @param string $operation
     * @param iterable $inputIter
     * @param array $inputArgs
     * @param array $expectedOutput
     * @param bool $preserveKeys
     * @dataProvider provideOperationTestCases
     */
    public function testCanApplyTransformativeOperationsViaPipe(
        string $operation,
        iterable $inputIter,
        array $inputArgs,
        array $expectedOutput,
        bool $preserveKeys = false
    ) {
        $actualOutput = (Pipe::{$operation}(...$inputArgs))($inputIter);
        $this->assertIterable($expectedOutput, $actualOutput, $preserveKeys);
    }

    /**
     * @param string $operation
     * @param iterable $inputIter
     * @param array $inputArgs
     * @param array $expectedOutput
     * @param bool $preserveKeys
     * @dataProvider provideOperationTestCases
     */
    public function testCanApplyTransformativeOperationsViaCollection(
        string $operation,
        iterable $inputIter,
        array $inputArgs,
        array $expectedOutput,
        bool $preserveKeys = false
    ) {
        $actualOutput = Collection::new($inputIter)->{$operation}(...$inputArgs);
        $this->assertInstanceOf(Collection::class, $actualOutput);
        $this->assertIterable($expectedOutput, $actualOutput, $preserveKeys);
    }

    public function provideOperationTestCases()
    {
        yield 'chunk' => [
            'chunk',
            Iter::toIter([1, 2, 3, 4, 5, 6, 7, 8]),
            [3],
            [[1, 2, 3], [4, 5, 6], [7, 8]]
        ];

        yield 'drop' => [
            'drop',
            Iter::toIter([1, 2, 3, 4, 5]),
            [2],
            [3, 4, 5]
        ];

        yield 'filter' => [
            'filter',
            Iter::toIter([1, 2, 3, 4, 5]),
            [Func::even()],
            [2, 4]
        ];

        yield 'map' => [
            'map',
            Iter::toIter([1, 2, 3]),
            [Func::operator('*', 2)],
            [2, 4, 6]
        ];

        yield 'mapWithKeys' => [
            'mapWithKeys',
            Iter::toIter(['a' => 1, 'b' => 2, 'c' => 3]),
            [Func::operator('*', 2)],
            ['a' => 2, 'b' => 4, 'c' => 6],
            Iter::PRESERVE_KEYS
        ];

        yield 'mapKeys' => [
            'mapKeys',
            Iter::toIter(['a' => 1, 'b' => 2, 'c' => 3]),
            [Func::unary('strtoupper')],
            ['A' => 1, 'B' => 2, 'C' => 3],
            Iter::PRESERVE_KEYS
        ];

        yield 'reindex' => [
            'reindex',
            Iter::toIter([
                ['id' => 'a', 'name' => 'Alice'],
                ['id' => 'b', 'name' => 'Bob'],
            ]),
            [Func::index('id')],
            [
                'a' => ['id' => 'a', 'name' => 'Alice'],
                'b' => ['id' => 'b', 'name' => 'Bob'],
            ],
            Iter::PRESERVE_KEYS
        ];

        yield 'pluck' => [
            'pluck',
            Iter::toIter([
                ['id' => 'a', 'name' => 'Alice'],
                ['id' => 'b', 'name' => 'Bob'],
            ]),
            ['name'],
            ['Alice', 'Bob']
        ];

        yield 'toKeyPairs' => [
            'toKeyPairs',
            Iter::toIter(['a' => 1, 'b' => 2, 'c' => 3]),
            [],
            [['a', 1], ['b', 2], ['c', 3]]
        ];

        yield 'fromKeyPairs' => [
            'fromKeyPairs',
            Iter::toIter([['a', 1], ['b', 2], ['c', 3]]),
            [],
            ['a' => 1, 'b' => 2, 'c' => 3],
            Iter::PRESERVE_KEYS
        ];

        yield 'keys' => [
            'keys',
            Iter::toIter(['a' => 1, 'b' => 2, 'c' => 3]),
            [],
            ['a', 'b', 'c']
        ];

        yield 'values' => [
            'values',
            Iter::toIter(['a' => 1, 'b' => 2, 'c' => 3]),
            [],
            [1, 2, 3],
            Iter::PRESERVE_KEYS
        ];

        yield 'flip' => [
            'flip',
            Iter::toIter(['a' => 1, 'b' => 2, 'c' => 3]),
            [],
            [1 => 'a', 2 => 'b', 3 => 'c'],
            Iter::PRESERVE_KEYS
        ];

        yield 'replay' => [
            'replay',
            Iter::toIter([1, 2, 3]),
            [3],
            [1, 2, 3, 1, 2, 3, 1, 2, 3]
        ];

        yield 'scan' => [
            'scan',
            Iter::toIter([1, 2, 3, 4]),
            [Func::operator('*'), 1],
            [1, 2, 6, 24]
        ];

        yield 'take' => [
            'take',
            Iter::toIter([1, 2, 3, 4, 5]),
            [3],
            [1, 2, 3]
        ];

        yield 'concat' => [
            'concat',
            Iter::toIter([1, 2, 3]),
            [Iter::toIter([4, 5, 6]), Iter::toIter([7, 8, 9])],
            [1, 2, 3, 4, 5, 6, 7, 8, 9]
        ];

        yield 'zip' => [
            'zip',
            Iter::toIter([1, 4, 7]),
            [Iter::toIter([2, 5, 8]), Iter::toIter([3, 6, 9])],
            [1, 2, 3, 4, 5, 6, 7, 8, 9]
        ];
    }
}
