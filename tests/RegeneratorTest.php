<?php

namespace Jeremeamia\Iter8\Tests;

use Jeremeamia\Iter8\Gen;

/**
 * @covers \Jeremeamia\Iter8\Regenerator
 */
class RegeneratorTest extends TestCase
{
    public function testRegeneratorUsesProvidedCallableToEnableRewinding()
    {
        $timesCalled = 0;
        $genFunc = function (int $max) use (&$timesCalled) {
            $timesCalled++;
            for ($i = 1; $i <= $max; $i++) {
                yield $i;
            }
        };

        $iter = Gen::regen($genFunc, [3]);

        $results = [];
        for ($i = 0; $i < 5; $i++) {
            foreach ($iter as $n) {
                $results[] = $n;
            }
        }

        $this->assertEquals(5, $timesCalled);
        $this->assertEquals(str_repeat('123', 5), implode('', $results));
    }

    public function testRegeneratorSupportsKeys()
    {
        $iter = Gen::regen(function () {
            yield "a" => 1;
            yield "b" => 2;
            yield "c" => 3;
        });

        $results = ['a' => 0, 'b' => 0, 'c' => 0];
        for ($i = 0; $i < 3; $i++) {
            foreach ($iter as $k => $_) {
                $results[$k]++;
            }
        }

        $this->assertEquals(['a' => 3, 'b' => 3, 'c' => 3], $results);
    }
}
