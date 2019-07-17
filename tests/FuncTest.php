<?php

namespace Jeremeamia\Iter8\Tests;

use Jeremeamia\Iter8\{Func, Iter};

/**
 * @covers \Jeremeamia\Iter8\Func
 */
class FuncTest extends TestCase
{
    public function testCanCreateFuncToCallClassMethod()
    {
        $fn = Func::method('greet', 'Bob');
        $object = new class() { function greet(string $name) { return "Hello, {$name}!"; } };
        $result = $fn($object);
        $this->assertEquals('Hello, Bob!', $result);
    }

    public function testCanCreateFuncToAccessClassProperty()
    {
        $fn = Func::property('foo');
        $object = new class() { public $foo = 'bar'; };
        $result = $fn($object);
        $this->assertEquals('bar', $result);
    }

    public function testCanCreateFuncToAccessClassPropertyAndReturnDefaultValueIfNotSet()
    {
        $fn = Func::property('foo', 'bar');
        $object = new class() { public $foo; };
        $result = $fn($object);
        $this->assertEquals('bar', $result);
    }

    public function testCanCreateFuncToAccessArrayIndex()
    {
        $fn = Func::index('foo');
        $array = ['foo' => 'bar'];
        $result = $fn($array);
        $this->assertEquals('bar', $result);
    }

    public function testCanCreateFuncToAccessArrayIndexAndReturnDefaultValueIfNotSet()
    {
        $fn = Func::index('foo', 'bar');
        $array = [];
        $result = $fn($array);
        $this->assertEquals('bar', $result);
    }

    public function testCanCreateFuncToCoerceNativeUnaryPhpFunctionIntoAcceptingMultipleArgs()
    {
        $fn = Func::unary('strtolower');
        $result = $fn('FOO', 'BAR');
        $this->assertEquals('foo', $result);
    }

    public function testCanCreateFuncToEvaluateThruthiness()
    {
        $fn = Func::truthy();
        $this->assertTrue($fn(true));
        $this->assertTrue($fn(1));
        $this->assertTrue($fn('foo'));
        $this->assertFalse($fn(false));
        $this->assertFalse($fn(0));
        $this->assertFalse($fn(null));
    }

    public function testCanCreateFuncToEvaluateFalsiness()
    {
        $fn = Func::falsey();
        $this->assertFalse($fn(true));
        $this->assertFalse($fn(1));
        $this->assertFalse($fn('foo'));
        $this->assertTrue($fn(false));
        $this->assertTrue($fn(0));
        $this->assertTrue($fn(null));
    }

    public function testCanCreateFuncToNegateTheResultOfAnotherFunc()
    {
        $fn = Func::not(Func::truthy());
        $this->assertFalse($fn(true));
        $this->assertFalse($fn(1));
        $this->assertFalse($fn('foo'));
        $this->assertTrue($fn(false));
        $this->assertTrue($fn(0));
        $this->assertTrue($fn(null));
    }

    public function testCanCreateFuncToDetermineIfNumberIsOdd()
    {
        $fn = Func::odd();
        $this->assertTrue($fn(3));
        $this->assertFalse($fn(4));
    }

    public function testCanCreateFuncToDetermineIfNumberIsEven()
    {
        $fn = Func::even();
        $this->assertTrue($fn(4));
        $this->assertFalse($fn(3));
    }

    public function testCanCreateFuncToWrapAStringValue()
    {
        $fn = Func::wrap('*');
        $result = $fn('foo');
        $this->assertEquals('*foo*', $result);
    }

    public function testCanCreateFuncToWrapAStringValueWithDifferentPrefixAndSuffix()
    {
        $fn = Func::wrap('<', '>');
        $result = $fn('foo');
        $this->assertEquals('<foo>', $result);
    }

    public function testCanCreateFuncToPrefixAStringValue()
    {
        $fn = Func::prefix('$');
        $result = $fn('foo');
        $this->assertEquals('$foo', $result);
    }

    public function testCanCreateFuncToSuffixAStringValue()
    {
        $fn = Func::suffix('$');
        $result = $fn('foo');
        $this->assertEquals('foo$', $result);
    }

    public function testCanCreateFuncToTestEquality()
    {
        $fn = Func::eq(5);
        $this->assertTrue($fn(2 + 3));
    }

    public function testCanComposeFuncsTogether()
    {
        $fn = Func::compose([Func::index('age'), Func::operator('>=', 20)]);
        $result = $fn(['name' => 'Jeremy', 'age' => 34]);
        $this->assertTrue($result);
    }

    public function testCanCreatePartiallyAppliedFunc()
    {
        $fn = Func::apply('explode', ['|', Func::PLACEHOLDER]);
        $result = $fn('a|b|c');
        $this->assertEquals(['a', 'b', 'c'], $result);
    }

    public function testCanCreateMemoizedFunc()
    {
        $timesCalled = 0;

        $fn = Func::memoize(function (int $n, int $m) use (&$timesCalled) {
            $timesCalled++;
            return $n + $m;
        });

        $this->assertEquals(5, $fn(2, 3));
        $this->assertEquals(9, $fn(2, 7));
        $this->assertEquals(10, $fn(5, 5));

        // Repeat
        $this->assertEquals(5, $fn(2, 3));
        $this->assertEquals(9, $fn(2, 7));
        $this->assertEquals(10, $fn(5, 5));

        $this->assertEquals(3, $timesCalled);
    }

    public function testCanCreateRecursiveMemoizedFunc()
    {
        $timesCalled = array_fill_keys(range(0, 6), 0);

        $fn = Func::memoize(function (int $n) use (&$timesCalled, &$fn) {
            $timesCalled[$n]++;
            if (is_callable($fn)) {
                return $n <= 1 ? 1 : $fn($n - 1) + $fn($n - 2);
            } else {
                $this->fail('Expected a callable. I had to check this for PHPStan.');
            }
        });

        $this->assertEquals(2, $fn(2));
        $this->assertEquals(5, $fn(4));
        $this->assertEquals(13, $fn(6));
        $this->assertEquals(13, $fn(6));

        $this->assertTrue(Iter::all($timesCalled, Func::eq(1)));
    }

    /**
     * @param string $operator
     * @param mixed $left
     * @param mixed $right
     * @param mixed $expected
     * @dataProvider provideOperatorUseCases
     */
    public function testCanCreateFuncsForOperators(string $operator, $left, $right, $expected)
    {
        $fn = Func::operator($operator);
        $result = $fn($left, $right);
        $this->assertEquals($expected, $result);
    }

    public function provideOperatorUseCases()
    {
        return Iter::reindex([
            ['+', 2, 3, 5],
            ['*', 2, 3, 6],
            ['-', 5, 3, 2],
            ['/', 9, 3, 3],
            ['%', 9, 5, 4],
            // @TODO other operators
        ], Func::index(0));
    }

    // @TODO two exception cases for Func::operator
}
