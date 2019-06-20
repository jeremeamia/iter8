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
    public function testTransformativeOperationsViaIter(
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
    public function testTransformativeOperationsViaPipe(
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
    public function testTransformativeOperationsViaCollection(
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
        yield 'buffer' => [
            'buffer',
            Iter::toIter([1, 2, 3, 4, 5, 6, 7]),
            [3],
            [[1, 2, 3], [4, 5, 6], [7]]
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

        yield 'map (with keys)' => [
            'map',
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
    }
}
