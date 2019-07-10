<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use InvalidArgumentException;
use Iterator;
use RuntimeException;

use function array_values, count, is_array, is_iterable, iterator_to_array;

use const INF;

/**
 * Iter is a helper that provides operations for iterables where the end result is always a new Generator.
 *
 * Iter includes mapping operations, filtering operations, restructuring operations, and a few misc. operations. All
 * included operations are pipe-able.
 */
final class Iter
{
    public const PRESERVE_KEYS = true;

    //------------------------------------------------------------------------------------------------------------------
    // MAPPING OPERATIONS
    //------------------------------------------------------------------------------------------------------------------

    /**
     * Creates a new iterable with items mapped from the source by the mapper function.
     *
     * @param iterable $iter Source data.
     * @param callable $fn Mapper function for values.
     * @return Iterator
     * @see array_map()
     */
    public static function map(iterable $iter, callable $fn): Iterator
    {
        foreach ($iter as $key => $value) {
            yield $key => $fn($value);
        }
    }

    /**
     * Creates a new iterable with items mapped from the source by applying the mapper function recursively.
     *
     * @param iterable $iter Source data.
     * @param callable $fn Mapper function for values.
     * @return Iterator
     * @see array_map()
     */
    public static function mapRecursive(iterable $iter, callable $fn): Iterator
    {
        foreach ($iter as $key => $value) {
            yield $key => is_iterable($value) ? self::mapRecursive($value, $fn) : $fn($value);
        }
    }

    /**
     * Creates a new iterable with items mapped by the mapper function, which is called with both the value and key.
     *
     * @param iterable $iter Source data.
     * @param callable $fn Mapper function for values.
     * @return Iterator
     * @see array_map()
     */
    public static function mapWithKeys(iterable $iter, callable $fn): Iterator
    {
        foreach ($iter as $key => $value) {
            yield $key => $fn($value, $key);
        }
    }

    /**
     * Creates a new iterable where the source keys are replaced by applying the mapper function to the source keys.
     *
     * @param iterable $iter Source data.
     * @param callable $fn Mapper function for keys.
     * @return Iterator
     */
    public static function mapKeys(iterable $iter, callable $fn): Iterator
    {
        foreach ($iter as $key => $value) {
            yield $fn($key) => $value;
        }
    }

    /**
     * Creates a new iterable where the source keys are replaced by applying the mapper function to the source values.
     *
     * Example:
     *
     *     $iter = Iter::reindex([['id' => 'a', 'name' => 'Alice'], ['id' => 'b', 'name' => 'Bob']], Func::index('id'));
     *     #> ['a' => ['id' => 'a', 'name' => 'Alice'], 'b' => ['id' => 'b', 'name' => 'Bob']]
     *
     * @param iterable $iter Source data.
     * @param callable $fn Mapper function for values.
     * @return Iterator
     * @see array_pluck()
     */
    public static function reindex(iterable $iter, callable $fn): Iterator
    {
        foreach ($iter as $key => $value) {
            yield $fn($value) => $value;
        }
    }

    /**
     * Creates a new iterable with items mapped from the source by plucking a key.
     *
     * Example:
     *
     *     $iter = Iter::pluck([['name' => 'Jeremy'], ['name' => 'Penny'], ['name' => 'Joey']], 'name');
     *     #> ['Jeremy', 'Penny', 'Joey']
     *
     * @param iterable $iter Source data. Assumes that the data is a list of homogeneous associative arrays.
     * @param string $key Key to pluck from each associative array item.
     * @return Iterator
     * @see array_pluck()
     */
    public static function pluck(iterable $iter, string $key): Iterator
    {
        return self::map($iter, Func::index($key));
    }

    /**
     * Creates a new iterable of key-value tuples mapped from the source.
     *
     * Example:
     *
     *     $iter = Iter::toKeyPairs(['a' => 1, 'b' => 2, 'c' => 3]);
     *     #> [['a', 1], ['b', 2], ['c', 3]]
     *
     * @param iterable $iter Source data.
     * @return Iterator
     */
    public static function toKeyPairs(iterable $iter): Iterator
    {
        foreach ($iter as $key => $value) {
            yield [$key, $value];
        }
    }

    /**
     * Creates a new associative-style iterable mapped from key-value tuples in the source.
     *
     * Example:
     *
     *     $iter = Iter::fromKeyPairs([['a', 1], ['b', 2], ['c', 3]]);
     *     #> ['a' => 1, 'b' => 2, 'c' => 3]
     *
     * @param iterable $iter Source data.
     * @return Iterator
     */
    public static function fromKeyPairs(iterable $iter): Iterator
    {
        foreach ($iter as [$key, $value]) {
            yield $key => $value;
        }
    }

    /**
     * Creates a new iterable that discards values and emits only the keys from the source.
     *
     * Example:
     *
     *     $iter = Iter::toIter(['a' => 1, 'b' => 2, 'c' => 3]);
     *     #> ['a', 'b', 'c']
     *
     * @param iterable $iter Source data.
     * @return Iterator
     * @see array_keys()
     */
    public static function keys(iterable $iter): Iterator
    {
        foreach ($iter as $key => $_) {
            yield $key;
        }
    }

    /**
     * Creates a new iterable that discards keys and emits only the values from the source.
     *
     * Example:
     *
     *     $iter = Iter::toIter(['a' => 1, 'b' => 2, 'c' => 3]);
     *     #> [1, 2, 3]
     *
     * @param iterable $iter Source data.
     * @return Iterator
     * @see array_values()
     */
    public static function values(iterable $iter): Iterator
    {
        foreach ($iter as $value) {
            yield $value;
        }
    }

    /**
     * Creates a new iterable where the values and keys from the source data are exchanged.
     *
     * Example:
     *
     *     $iter = Iter::toIter(['a' => 1, 'b' => 2, 'c' => 3]);
     *     #> [1 => 'a', 2 => 'b', 3 => 'c']
     *
     * @param iterable $iter Source data.
     * @return Iterator
     * @see array_flip()
     */
    public static function flip(iterable $iter): Iterator
    {
        foreach ($iter as $key => $value) {
            yield $value => $key;
        }
    }

    //------------------------------------------------------------------------------------------------------------------
    // FILTERING OPERATIONS
    //------------------------------------------------------------------------------------------------------------------

    /**
     * Returns a new iterable with items filtered out from the source by the filter function.
     *
     * @param iterable $iter Source data.
     * @param callable $fn Filter function (i.e., predicate).
     * @return Iterator
     */
    public static function filter(iterable $iter, callable $fn): Iterator
    {
        foreach ($iter as $key => $value) {
            if ($fn($value)) {
                yield $key => $value;
            }
        }
    }

    /**
     * Returns a new iterable with items filtered out by the filter function, which is called with both the value & key.
     *
     * @param iterable $iter Source data.
     * @param callable $fn Filter function (i.e., predicate).
     * @return Iterator
     */
    public static function filterWithKeys(iterable $iter, callable $fn): Iterator
    {
        foreach ($iter as $key => $value) {
            if ($fn($value, $key)) {
                yield $key => $value;
            }
        }
    }

    /**
     * Returns a new iterable with items filtered out from the source by applying the filter function to the keys.
     *
     * @param iterable $iter Source data.
     * @param callable $fn Filter function (i.e., predicate).
     * @return Iterator
     */
    public static function filterKeys(iterable $iter, callable $fn): Iterator
    {
        foreach ($iter as $key => $value) {
            if ($fn($key)) {
                yield $key => $value;
            }
        }
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @return Iterator
     */
    public static function removeNulls(iterable $iter): Iterator
    {
        return self::filter($iter, Func::not('is_null'));
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @return Iterator
     */
    public static function removeEmpty(iterable $iter): Iterator
    {
        return self::filter($iter, Func::truthy());
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @param string $key
     * @param mixed $value
     * @return Iterator
     */
    public static function where(iterable $iter, string $key, $value): Iterator
    {
        return self::filter($iter, function ($array) use ($key, $value) {
            return array_key_exists($key, $array) && $array[$key] === $value;
        });
    }

    /**
     * Returns a new iterable that takes only the first $n items from the source.
     *
     * @param iterable $iter Source data.
     * @param int $n Number of items to take.
     * @return Iterator
     */
    public static function take(iterable $iter, int $n): Iterator
    {
        return self::slice($iter, 0, $n);
    }

    /**
     * Returns a new iterable that takes only items from the source after the first $n items are skipped.
     *
     * @param iterable $iter Source data.
     * @param int $n Number of items to drop/skip.
     * @return Iterator
     */
    public static function drop(iterable $iter, int $n): Iterator
    {
        return self::slice($iter, $n);
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @param int $offset
     * @param int|null $length
     * @return Iterator
     */
    public static function slice(iterable $iter, int $offset, ?int $length = null): Iterator
    {
        if ($offset < 0) {
            throw new InvalidArgumentException('Starting offset must be non-negative');
        }

        if ($length < 0) {
            throw new InvalidArgumentException('Length must be non-negative');
        }

        if ($length === 0) {
            return;
        }

        $i = 0;
        foreach ($iter as $key => $value) {
            // Skip everything before the offset.
            if ($i++ < $offset) {
                continue;
            }

            yield $key => $value;

            // Skip everything once the length is reached.
            if ($length && $i >= $offset + $length) {
                break;
            }
        }
    }

    /**
     * Returns a new iterable that takes the first items from the source until the predicate function returns false.
     *
     * @param iterable $iter Source data.
     * @param callable $fn predicate function.
     * @return Iterator
     */
    public static function takeWhile(iterable $iter, callable $fn): Iterator
    {
        foreach ($iter as $key => $value) {
            if (!$fn($value, $key)) {
                return;
            }

            yield $key => $value;
        }
    }

    /**
     * Returns a new iterable that drops the first items from the source until the predicate function returns false.
     *
     * @param iterable $iter Source data.
     * @param callable $fn predicate function.
     * @return Iterator
     */
    public static function dropWhile(iterable $iter, callable $fn): Iterator
    {
        $iter = Iter::toIter($iter);
        foreach ($iter as $key => $value) {
            if (!$fn($value, $key)) {
                break;
            }
        }

        yield from self::resume($iter);
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @return Iterator
     */
    public static function debounce(iterable $iter): Iterator
    {
        $prev = null;
        foreach ($iter as $key => $value) {
            if ($value !== $prev) {
                yield $key => $value;
                $prev = $value;
            }
        }
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @return Iterator
     */
    public static function distinct(iterable $iter): Iterator
    {
        $prev = [];
        foreach ($iter as $key => $value) {
            if (!in_array($value, $prev, true)) {
                yield $key => $value;
                $prev[] = $value;
            }
        }
    }

    //------------------------------------------------------------------------------------------------------------------
    // RESTRUCTURING OPERATIONS
    //------------------------------------------------------------------------------------------------------------------

    /**
     * Creates a new iterable of iterables where the source has been divided into chunks of the specified size.
     *
     * Example:
     *
     *     $iter = Iter::chunk([1, 2, 3, 4, 5, 6, 7], 2);
     *     #> [[1, 2], [3, 4], [5, 6], [7]]
     *
     * @param iterable $iter Source data.
     * @param int $size The desired chunk size.
     * @return Iterator
     * @see array_chunk()
     */
    public static function chunk(iterable $iter, int $size): Iterator
    {
        $chunk = [];
        foreach ($iter as $item) {
            $chunk[] = $item;
            if (count($chunk) === $size) {
                yield $chunk;
                $chunk = [];
            }
        }

        if (count($chunk) > 0) {
            yield $chunk;
        }
    }

    /**
     * Creates a new iterable of iterables where the source has been divided into the specified number of partitions.
     *
     * Note: The partitioning is done like dealing cards.
     *
     * Example:
     *
     *     $iter = Iter::partition([1, 2, 3, 4, 5, 6, 7], 3);
     *     #> [[1, 4, 7], [2, 5], [3, 6]]
     *
     * @param iterable $iter Source data.
     * @param int $count The desired number of $partitions.
     * @return Iterator
     */
    public static function partition(iterable $iter, int $count): Iterator
    {
        $partitions = [];

        $i = 0;
        foreach ($iter as $value) {
            $partitions[$i % $count][] = $value;
            $i++;
        }

        foreach ($partitions as $partition) {
            yield $partition;
        }
    }

    /**
     * Creates a new iterable that replays the entire source iterable for the specified number of times.
     *
     * Example:
     *
     *     $iter = Iter::replay([1, 2, 3], 3);
     *     #> [1, 2, 3, 1, 2, 3, 1, 2, 3]
     *
     * @param iterable $iter Source data.
     * @param int|null $times
     * @return Iterator
     */
    public static function replay(iterable $iter, ?int $times = null): Iterator
    {
        $times = $times ?? INF;
        $iter = self::rewindable($iter);
        for ($i = 0; $i < $times; $i++, $iter->rewind()) {
            yield from $iter;
        }
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @param iterable ...$iters Additional iterables of source data.
     * @return Iterator
     */
    public static function concat(iterable $iter, iterable ...$iters): Iterator
    {
        yield from $iter;
        foreach ($iters as $iter) {
            yield from $iter;
        }
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @param iterable ...$iters Additional iterables of source data.
     * @return Iterator
     */
    public static function combineLatest(iterable $iter, iterable ...$iters): Iterator
    {
        yield self::last($iter);
        foreach ($iters as $iter) {
            yield self::last($iter);
        }
    }

    /**
     * Returns a new iterable where a mapper function is applied that produces iterables that get flattened back up.
     *
     * @param iterable $iter Source data. Assumed to be an iterable of iterables.
     * @param callable $fn Mapping function.
     * @return Iterator
     */
    public static function flatMap(iterable $iter, callable $fn): Iterator
    {
        foreach ($iter as $value) {
            yield from $fn($value);
        }
    }

    /**
     * Flattens nested iterables in the source iterable down into a new iterable.
     *
     * @param iterable $iter Source data. Assumes that some items may also be iterable.
     * @param int $levels The number of levels to recurse.
     * @return Iterator
     */
    public static function flatten(iterable $iter, int $levels = 1): Iterator
    {
        if ($levels < 0) {
            throw new InvalidArgumentException('Levels must be non-negative');
        } elseif ($levels === 0) {
            yield from $iter;
        } else {
            foreach ($iter as $value) {
                if (is_iterable($value)) {
                    yield from self::flatten($value, $levels - 1);
                } else {
                    yield $value;
                }
            }
        }
    }

    /**
     * Flattens all the leaves recursively in a tree-structured source iterable down into a new iterable.
     *
     * @param iterable $iter Source data. Assumes that some items (included deeply nested ones) may also be iterable.
     * @return Iterator
     */
    public static function leaves(iterable $iter): Iterator
    {
        foreach ($iter as $value) {
            if (is_iterable($value)) {
                yield from self::leaves($value);
            } else {
                yield $value;
            }
        }
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @param iterable ...$iters
     * @return Iterator
     */
    public static function zip(iterable $iter, iterable ...$iters): Iterator
    {
        array_unshift($iters, $iter);
        $iters = array_map([self::class, 'toIter'], $iters);

        for (
            self::apply($iters, Func::method('rewind'));
            self::all($iters, Func::method('valid'));
            self::apply($iters, Func::method('next'))
        ) {
            yield from array_map(Func::method('current'), $iters);
        }
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @param mixed $separator
     * @return Iterator
     */
    public static function interpose(iterable $iter, $separator): Iterator
    {
        $iter = self::toIter($iter);
        yield from self::take($iter, 1);
        foreach (self::resume($iter) as $value) {
            yield $separator;
            yield $value;
        }
    }

    /**
     * TODO
     *
     * Note: The length of the resulting Iterator will be the minimum length of the two input iterables.
     *
     * @param iterable $iter Source data.
     * @param iterable $keys Replacement keys.
     * @return Iterator
     */
    public static function replaceKeys(iterable $iter, iterable $keys): Iterator
    {
        $iter = self::toIter($iter);
        $keys = self::toIter($keys);
        for (
            $iter->rewind(), $keys->rewind();
            $iter->valid() && $keys->valid();
            $iter->next(), $keys->next()
        ) {
            yield $keys->current() => $iter->current();
        }
    }

    /**
     * TODO
     *
     * Note: The length of the resulting Iterator will be the minimum length of the two input iterables.
     *
     * @param iterable $iter Source data.
     * @param iterable $values Replacement $values.
     * @return Iterator
     */
    public static function replaceValues(iterable $iter, iterable $values): Iterator
    {
        $iter = self::toIter($iter);
        $values = self::toIter($values);
        for (
            $iter->rewind(), $values->rewind();
            $iter->valid() && $values->valid();
            $iter->next(), $values->next()
        ) {
            yield $iter->key() => $values->current();
        }
    }

    //------------------------------------------------------------------------------------------------------------------
    // MISC OPERATIONS
    //------------------------------------------------------------------------------------------------------------------

    /**
     * Adapts any iterable value to the Iterator interface.
     *
     * This allows Iterator decorators (like those in SPL) that work *only* with the Iterator interface to be used
     * easily with any iterable value (e.g., array, IteratorAggregate).
     *
     * @param iterable $iter Source data.
     * @return Iterator
     */
    public static function normalize(iterable $iter): Iterator
    {
        yield from $iter;
    }

    /**
     * Creates a new iterable where source data is piped through one or more transformative operations.
     *
     * Piping allows for the a more natural reading order of operations when multiple operations are being applied to an
     * iterable. Instead of consecutive wrappings of iterables that result in an "inside-out" order of operations,
     * piping allows the transformations to be written in the order they are performed.
     *
     * @param iterable $iter
     * @param iterable|callable[] $operations
     * @return Iterator
     */
    public static function pipe(iterable $iter, iterable $operations): Iterator
    {
        return Gen::from(Func::compose($operations)($iter));
    }

    /**
     * Creates a new iterable where the provided callback function is executed for each item during iteration.
     *
     * @param iterable $iter Source data.
     * @param callable $fn Function to apply for each item.
     * @return Iterator
     */
    public static function tap(iterable $iter, callable $fn): Iterator
    {
        foreach ($iter as $key => $value) {
            $fn($value, $key);
            yield $key => $value;
        }
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @param callable $fn Predicate function.
     * @return Iterator
     * @throws \Exception
     */
    public static function validate(iterable $iter, callable $fn): Iterator
    {
        foreach ($iter as $key => $value) {
            if ($fn($value)) {
                yield $key => $value;
            } else {
                throw ValidationException::for($key);
            }
        }
    }

    /**
     * TODO
     *
     * aka "reductions"
     *
     * @param iterable $iter Source data.
     * @param callable $fn Reducer function.
     * @param mixed|null $initialValue The initial carry value that values are reduced onto.
     * @return Iterator
     */
    public static function scan(iterable $iter, callable $fn, $initialValue = null): Iterator
    {
        $accumulator = $initialValue;
        foreach ($iter as $key => $value) {
            $accumulator = $fn($accumulator, $value, $key);
            yield $accumulator;
        }
    }

    /**
     * Creates a new iterator that resumes from from a partially-consumed iterator without rewinding.
     *
     * @param Iterator $iter
     * @return Iterator
     */
    public static function resume(Iterator $iter): Iterator
    {
        $iter->next();
        while ($iter->valid()) {
            yield $iter->key() => $iter->current();
            $iter->next();
        }
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @param callable $fn Function to apply for each item.
     * @param array $args
     * @return void
     */
    public static function apply(iterable $iter, callable $fn, array $args = []): void
    {
        foreach ($iter as $key => $value) {
            $fn($value, $key, ...$args);
        }
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @param callable $fn Function to apply for each item.
     * @param array $args
     * @return void
     */
    public static function applyRecursive(iterable $iter, callable $fn, array $args = []): void
    {
        foreach ($iter as $key => $value) {
            if (is_iterable($value)) {
                self::applyRecursive($value, $fn, $args);
            } else {
                $fn($value, $key, ...$args);
            }
        }
    }

    /**
     * Prints the iterable using print_r(), but recursively converts the iterable and any sub-iterables to arrays first.
     *
     * @param iterable $iter Source data.
     * @param bool $preserveKeys Set to true to keep the keys from the source.
     * @return void
     */
    public static function print(iterable $iter, bool $preserveKeys = false): void
    {
        print_r(self::toArrayRecursive($iter, $preserveKeys));
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @param resource $stream
     * @return int
     */
    public static function streamTo(iterable $iter, &$stream): int
    {
        $bytes = 0;
        foreach ($iter as $value) {
            $bytes += fwrite($stream, (string) $value);
        }

        return $bytes;
    }

    //------------------------------------------------------------------------------------------------------------------
    // EVALUATION OPERATIONS
    //------------------------------------------------------------------------------------------------------------------

    /**
     * Returns a single value by applying the provided reducer function to all items from the source.
     *
     * @param iterable $iter Source data.
     * @param callable $fn Reducer function.
     * @param mixed|null $initialValue The initial carry value that values are reduced onto.
     * @return mixed
     */
    public static function reduce(iterable $iter, callable $fn, $initialValue = null)#: mixed
    {
        return self::last(self::scan($iter, $fn, $initialValue));
    }

    /**
     * Returns a single value by recursively applying the provided reducer function to all items from the source.
     *
     * @param iterable $iter Source data.
     * @param callable $fn Reducer function.
     * @param mixed|null $initialValue The initial carry value that values are reduced onto.
     * @return mixed
     */
    public static function reduceRecursive(iterable $iter, callable $fn, $initialValue = null)#: mixed
    {
        $accumulator = $initialValue;
        foreach ($iter as $key => $value) {
            $value = is_iterable($value) ? self::reduceRecursive($value, $fn, $initialValue) : $value;
            $accumulator = $fn($accumulator, $value, $key);
        }

        return $accumulator;
    }

    /**
     * TODO
     *
     * @param iterable $iter
     * @param string $separator
     * @return string
     */
    public static function implode(iterable $iter, string $separator = ''): string
    {
        return self::toString(self::interpose($iter, $separator));
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @param callable $fn search function (i.e., predicate).
     * @return mixed|null
     */
    public static function search(iterable $iter, callable $fn)#: ?mixed
    {
        return self::first(self::filter($iter, $fn));
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @return mixed|null
     */
    public static function first(iterable $iter)#: ?mixed
    {
        return self::toArray(self::take($iter, 1))[0] ?? null;
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @return mixed
     */
    public static function last(iterable $iter)#: mixed
    {
        $last = null;
        foreach ($iter as $last);

        return $last;
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @param callable $fn Predicate function.
     * @return bool
     */
    public static function any(iterable $iter, callable $fn): bool
    {
        foreach ($iter as $key => $value) {
            if ($fn($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * TODO
     *
     * @param iterable $iter Source data.
     * @param callable $fn Predicate function.
     * @return bool
     */
    public static function all(iterable $iter, callable $fn): bool
    {
        foreach ($iter as $key => $value) {
            if (!$fn($value)) {
                return false;
            }
        }

        return true;
    }

    //------------------------------------------------------------------------------------------------------------------
    // CONVERSION OPERATIONS
    //------------------------------------------------------------------------------------------------------------------

    /**
     * Creates an array from the provided iterable.
     *
     * By default, the items are re-indexed with numerical keys.
     *
     * You can use Iter::PRESERVE_KEYS as the second argument to preserve the iterables keys. When doing this, you
     * should be conscious of the structure of the iterable. Flattened iterables can contain duplicate keys, and some
     * generators can yield non-scalar keys. Preserving the keys in these cases may cause errors or data loss.
     *
     * @param iterable $iter Source data.
     * @param bool $preserveKeys Set to true to keep the keys from the source.
     * @return array
     */
    public static function toArray(iterable $iter, bool $preserveKeys = false): array
    {
        return is_array($iter)
            ? ($preserveKeys ? $iter : array_values($iter))
            : iterator_to_array($iter, $preserveKeys);
    }

    /**
     * Recursively creates an array from the provided iterable (and any sub-iterables).
     *
     * @param iterable $iter Source data.
     * @param bool $preserveKeys Set to true to keep the keys from the source.
     * @return array
     */
    public static function toArrayRecursive(iterable $iter, bool $preserveKeys = false): array
    {
        $array = [];
        foreach ($iter as $key => $value) {
            $value = is_iterable($value) ? self::toArrayRecursive($value, $preserveKeys) : $value;
            if ($preserveKeys) {
                $array[$key] = $value;
            } else {
                $array[] = $value;
            }
        }

        return $array;
    }

    /**
     * Creates an Iterator from the provided iterable.
     *
     * @param iterable $iter Source data.
     * @return Iterator
     */
    public static function toIter(iterable $iter): Iterator
    {
        return $iter instanceof Iterator ? $iter : self::normalize($iter);
    }

    /**
     * Creates a string from the provided iterable.
     *
     * @param iterable $iter Source data.
     * @param bool $buffer
     * @return string
     */
    public static function toString(iterable $iter, bool $buffer = false): string
    {
        if ($buffer) {
            $buffer = self::toStream($iter);
            $string = stream_get_contents($buffer);
            fclose($buffer);

            if ($string === false) {
                throw new RuntimeException('Failed to get stream contents.');
            }

            return $string;
        }

        return self::implode($iter, '');
    }

    /**
     * Creates a stream from the provided iterable.
     *
     * @param iterable $iter Source data.
     * @return resource
     */
    public static function toStream(iterable $iter)#: resource
    {
        $stream = fopen('php://temp', 'w+');
        if ($stream === false) {
            throw new RuntimeException('Failed to open stream.');
        }

        self::streamTo($iter, $stream);
        fseek($stream, 0);

        return $stream;
    }

    /**
     * Creates a rewindable version of the provided iterable.
     *
     * @param iterable $iter Source data.
     * @return RewindableIterator
     */
    public static function rewindable(iterable $iter): RewindableIterator
    {
        return RewindableIterator::new($iter);
    }

    /**
     * Creates a collection from the provided iterable.
     *
     * @param iterable $iter Source data.
     * @return Iterator
     */
    public static function collection(iterable $iter): Iterator
    {
        return Collection::new($iter);
    }
}
