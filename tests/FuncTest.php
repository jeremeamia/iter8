<?php

namespace Jeremeamia\Iter8\Tests;

use Jeremeamia\Iter8\Func;

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

    public function testCanCreatePartiallyAppliedFunc()
    {
        $fn = Func::apply('explode', ['|', Func::PLACEHOLDER]);
        $result = $fn('a|b|c');
        $this->assertEquals(['a', 'b', 'c'], $result);
    }
}
