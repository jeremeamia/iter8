<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use EmptyIterator;
use InvalidArgumentException;
use Iterator;

use const INF;

/**
 * Gen is provides operations for creating generators/iterables from data.
 */
final class Gen
{
    const KB = 2 ** 10;

    /**
     * Creates an iterable containing all the integers between the provided start and end values, inclusively.
     *
     * Example:
     *
     *     $iter = Gen::range(2, 7);
     *     #> [2, 3, 4, 5, 6, 7]
     *
     * @param int $start Start of the integer range.
     * @param int $end End of the integer range.
     * @param int $step The step/interval between numbers (Default: 1).
     * @return Iterator
     * @see range()
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
     * Creates an iterable that repeats the provided value for the specified number of times.
     *
     * Example:
     *
     *     $iter = Gen::repeat('hello', 4);
     *     #> ['hello', 'hello', 'hello', 'hello']
     *
     * @param mixed $value Value to repeat.
     * @param int|null $times The number of times to repeat (Default: INF).
     * @return Iterator
     * @see array_fill()
     */
    public static function repeat($value, ?int $times = null): Iterator
    {
        $times = $times ?? INF;
        for ($i = 0; $i < $times; $i++) {
            yield $value;
        }
    }

    /**
     * Creates an iterable from the provided keys where the provided value is the value for each key.
     *
     * Example:
     *
     *     $iter = Gen::repeatForKeys(['a', 'b', 'c'], 'hello');
     *     #> ['a' => 'hello', 'b' => 'hello', 'c' => 'hello']
     *
     * @param iterable $keys Keys to use for iterable keys.
     * @param mixed $value Value to use for iterable values.
     * @return Iterator
     * @see array_fill_keys()
     */
    public static function repeatForKeys(iterable $keys, $value): Iterator
    {
        foreach ($keys as $key) {
            yield $key => $value;
        }
    }

    /**
     * Creates an iterable that yields no values.
     *
     * Example:
     *
     *     $iter = Gen::empty();
     *     #> [ ]
     *
     * @return Iterator
     */
    public static function empty(): Iterator
    {
        return new EmptyIterator();
    }

    /**
     * Creates an iterable that contains the provided value.
     *
     * Example:
     *
     *     $iter = Gen::just('foo');
     *     #> ['foo']
     *
     * @param mixed $value Value to emit in the iterable.
     * @return Iterator
     */
    public static function just($value): Iterator
    {
        return Iter::toIter([$value]);
    }

    /**
     * Creates an iterable that contains the provided value, or if the value is an iterable, it contains all its values.
     *
     * Example:
     *
     *     $iter = Gen::from(['a', 'b', 'c']);
     *     #> ['a', 'b', 'c']
     *
     * @param mixed $value Value/values to emit in the iterable.
     * @return Iterator
     */
    public static function from($value): Iterator
    {
        return Iter::toIter(is_iterable($value) ? $value : [$value]);
    }

    /**
     * Creates an iterable that contains the values lazily created by the provided callable.
     *
     * Example:
     *
     *     $iter = Gen::defer(function () {yield 1; yield 2, yield 3;});
     *     #> [1, 2, 3]
     *
     * @param callable $fn Factory function to lazily create the iterable.
     * @param array $args Arguments to provide to the factory function.
     * @return Iterator
     */
    public static function defer(callable $fn, array $args = []): Iterator
    {
        yield from self::from($fn(...$args));
    }

    /**
     * Creates an iterable that contains the values exploded from the string.
     *
     * Notes:
     * - Uses explode() for strings smaller than the threshold and strtok() for longer strings.
     * - Always uses explode() when the delimiter is more than a single character.
     *
     * Example:
     *
     *     $iter = Gen::explode('a,b,c', ',');
     *     #> ['a', 'b', 'c']
     *
     * @param string $source Source string to explode.
     * @param string $delimiter Delimiter character(s) to explode on.
     * @param int $threshold The size limit before using strtok().
     * @return Iterator
     * @see explode()
     * @see strtok()
     */
    public static function explode(string $source, string $delimiter, int $threshold = 1 * self::KB): Iterator
    {
        if (strlen($delimiter) > 1 || strlen($source) < $threshold) {
            yield from explode($delimiter, $source);
        } else {
            $tok = strtok($source, $delimiter);
            while ($tok !== false) {
                yield $tok;
                $tok = strtok($delimiter);
            }
        }
    }
}
