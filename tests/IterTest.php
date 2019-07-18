<?php

namespace Jeremeamia\Iter8\Tests;

use Jeremeamia\Iter8\{Func, Gen, Iter, ValidationException};

/**
 * @covers \Jeremeamia\Iter8\Iter
 * @covers \Jeremeamia\Iter8\ValidationException
 */
class IterTest extends TestCase
{
    public function testCanValidateAnIterable()
    {
        $iter = Iter::toIter(['Jeremy', 'Penny', 'Joey']);
        $iter = Iter::validate($iter, function ($name) {
            return ctype_upper(substr($name, 0, 1));
        });

        $this->assertIterable(['Jeremy', 'Penny', 'Joey'], $iter);
    }

    public function testThrowsValidationErrorIfIterableValueIsInvalid()
    {
        $iter = Iter::toIter(['Jeremy', 'penny', 'Joey']);
        $iter = Iter::validate($iter, function ($name) {
            return ctype_upper(substr($name, 0, 1));
        });

        $this->expectException(ValidationException::class);
        Iter::toArray($iter);
    }

    public function testCanWriteIterableToStream()
    {
        $iter = Iter::toIter(['a', 'b', 'c', 'd', 'e']);
        $stream = Iter::toStream(Iter::map($iter, Func::suffix("\n")));
        $this->assertIsResource($stream);
        $iter = Gen::fromStream($stream, 4, "\n");
        $this->assertIterable(['a', 'b', 'c', 'd', 'e'], $iter);
    }
}
