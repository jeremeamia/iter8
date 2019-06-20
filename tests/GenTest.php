<?php

namespace Jeremeamia\Iter8\Tests;

use Exception;
use InvalidArgumentException;
use Jeremeamia\Iter8\{Gen, Iter};

/**
 * @covers \Jeremeamia\Iter8\Gen
 */
class GenTest extends TestCase
{
    /**
     * @param string $operation
     * @param array $inputArgs
     * @param array|Exception $expectedOutput
     * @param bool $preserveKeys
     * @dataProvider provideOperationTestCases
     */
    public function testGenOperations(
        string $operation,
        array $inputArgs,
        array $expectedOutput,
        bool $preserveKeys = false
    ) {
        $actualOutput = Gen::{$operation}(...$inputArgs);
        $this->assertIterable($expectedOutput, $actualOutput, $preserveKeys);
    }

    public function provideOperationTestCases()
    {
        yield 'range (step: 1)' => [
            'range',
            [3, 7],
            [3, 4, 5, 6, 7]
        ];

        yield 'range (step: 2)' => [
            'range',
            [3, 7, 2],
            [3, 5, 7]
        ];

        yield 'range (negative)' => [
            'range',
            [5, 1],
            [5, 4, 3, 2, 1]
        ];

        yield 'range (length: 0)' => [
            'range',
            [3, 3],
            [3]
        ];

        yield 'repeat (length: 5)' => [
            'repeat',
            ['a', 3],
            ['a', 'a', 'a']
        ];

        yield 'repeatForKeys' => [
            'repeatForKeys',
            [['a', 'b', 'c'], 2],
            ['a' => 2, 'b' => 2, 'c' => 2],
            true
        ];

        yield 'empty' => [
            'empty',
            [],
            [],
        ];

        yield 'just (scalar)' => [
            'just',
            ['a'],
            ['a'],
        ];

        yield 'just (iterable)' => [
            'just',
            [['a', 'b']],
            [['a', 'b']],
        ];

        yield 'from (scalar)' => [
            'from',
            ['a'],
            ['a'],
        ];

        yield 'from (iterable)' => [
            'from',
            [['a', 'b']],
            ['a', 'b'],
        ];

        yield 'defer' => [
            'defer',
            [function () {return ['a', 'b', 'c'];}],
            ['a', 'b', 'c'],
        ];

        yield 'explode (small)' => [
            'explode',
            ['a,b,c', ','],
            ['a', 'b', 'c'],
        ];

        yield 'explode (multi-char delimiter)' => [
            'explode',
            ['a, b, c', ', '],
            ['a', 'b', 'c'],
        ];

        yield 'explode (long)' => [
            'explode',
            ['a,b,c,d,e', ','],
            ['a', 'b', 'c', 'd', 'e'],
        ];
    }

    public function testRangeRequiresPositiveIntegerForStep()
    {
        $this->expectException(InvalidArgumentException::class);
        Iter::toArray(Gen::range(1, 5, 0));
    }
}
