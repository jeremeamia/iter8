<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use EmptyIterator;
use InvalidArgumentException;
use Iterator;

use const INF;

/**
 * Gen is a helper that provides operations for creating iterables of generated data.
 */
final class Gen
{
    /**
     * @param int $start
     * @param int $end
     * @param int $step
     * @return Iterator
     */
    public static function range(int $start, int $end, int $step = 1): Iterator
    {
        if ($step <= 0) {
            throw new InvalidArgumentException('The step must be greater than zero.');
        }

        if ($start === $end) {
            yield $start;
        } elseif ($start < $end) {
            for ($i = $start; $i <= $end; $i += $step) {
                yield $i;
            }
        } else {
            for ($i = $start; $i >= $end; $i -= $step) {
                yield $i;
            }
        }
    }

    /**
     * @param mixed $value
     * @param int|null $times
     * @return Iterator
     */
    public static function repeat($value, ?int $times = null): Iterator
    {
        $times = $times ?? INF;
        for ($i = 0; $i < $times; $i++) {
            yield $value;
        }
    }

    /**
     * @param iterable $keys
     * @param mixed $value
     * @return Iterator
     */
    public static function repeatForKeys(iterable $keys, $value): Iterator
    {
        foreach ($keys as $key) {
            yield $key => $value;
        }
    }

    /**
     * @return Iterator
     */
    public static function empty(): Iterator
    {
        return new EmptyIterator();
    }

    /**
     * @return Iterator
     */
    public static function value($value): Iterator
    {
        return Iter::toIter([$value]);
    }

    /**
     * @param string $source
     * @param string $delim
     * @return Iterator
     */
    public static function explode(string $source, string $delim): Iterator
    {
        if (strlen($delim) > 1 || strlen($source) < 256) {
            yield from explode($delim, $source);
        } else {
            $tok = strtok($source, $delim);
            while ($tok !== false) {
                yield $tok;
                $tok = strtok($delim);
            }
        }
    }
}
