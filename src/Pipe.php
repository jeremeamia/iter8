<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use Iterator;

/**
 * Pipe is a helper that provides callables for pipe-able Iter functions to be used with Iter::pipe().
 *
 * @method static callable any(callable $fn) Get pipe-able any operation.
 * @method static callable all(callable $fn) Get pipe-able all operation.
 * @method static callable chunk(int $size) Get pipe-able buffer operation.
 * @method static callable concat(iterable ...$iters) Get pipe-able concat operation.
 * @method static callable combineLatest(iterable ...$iters) Get pipe-able combineLatest operation.
 * @method static callable debounce() Get pipe-able debounce operation.
 * @method static callable distinct() Get pipe-able distinct operation.
 * @method static callable drop(int $n) Get pipe-able drop operation.
 * @method static callable dropWhile(callable $fn) Get pipe-able dropWhile operation.
 * @method static callable filter(callable $fn) Get pipe-able filter operation.
 * @method static callable filterEmpty() Get pipe-able filterEmpty operation.
 * @method static callable filterKeys(callable $fn) Get pipe-able filterKeys operation.
 * @method static callable filterNulls() Get pipe-able filterNulls operation.
 * @method static callable filterWithKeys() Get pipe-able filterWithKeys operation.
 * @method static callable first() Get pipe-able first operation.
 * @method static callable flatMap(callable $fn) Get pipe-able flatMap operation.
 * @method static callable flatten() Get pipe-able flatten operation.
 * @method static callable flip() Get pipe-able flip operation.
 * @method static callable fromKeyPairs() Get pipe-able mapFromKeyPairs operation.
 * @method static callable interpose($separator) Get pipe-able interpose operation.
 * @method static callable implode(string $separator = '') Get pipe-able implode operation.
 * @method static callable keys() Get pipe-able keys operation.
 * @method static callable last() Get pipe-able last operation.
 * @method static callable leaves() Get pipe-able leaves operation.
 * @method static callable map(callable $fn) Get pipe-able map operation.
 * @method static callable mapKeys(callable $fn) Get pipe-able mapKeys operation.
 * @method static callable mapWithKeys(callable $fn) Get pipe-able mapWithKeys operation.
 * @method static callable partition(int $count) Get pipe-able chunk operation.
 * @method static callable pluck(string $key) Get pipe-able pluck operation.
 * @method static callable normalize() Get pipe-able pluck operation.
 * @method static callable reindex(callable $fn) Get pipe-able reindex operation.
 * @method static callable reduce(callable $fn, $initialValue = null) Get pipe-able reduce operation.
 * @method static callable replaceKeys(iterable $keys) Get pipe-able replaceKeys operation.
 * @method static callable replaceValues(iterable $values) Get pipe-able replaceValues operation.
 * @method static callable replay(?int $times = null) Get pipe-able replay operation.
 * @method static callable resume() Get pipe-able resume operation.
 * @method static callable scan(callable $fn, $initialValue = null) Get pipe-able scan operation.
 * @method static callable search(callable $fn) Get pipe-able search operation.
 * @method static callable slice(int $offset, ?int $length = null) Get pipe-able slice operation.
 * @method static callable take(int $n) Get pipe-able take operation.
 * @method static callable takeWhile(callable $fn) Get pipe-able takeWhile operation.
 * @method static callable tap(callable $fn) Get pipe-able tap operation.
 * @method static callable toArray() Get pipe-able toArray operation.
 * @method static callable toIter() Get pipe-able toIter operation.
 * @method static callable toKeyPairs() Get pipe-able mapToKeyPairs operation.
 * @method static callable toStream() Get pipe-able toStream operation.
 * @method static callable toString() Get pipe-able toString operation.
 * @method static callable where(string $key, $value) Get pipe-able where operation.
 * @method static callable values() Get pipe-able values operation.
 * @method static callable validate(callable $fn) Get pipe-able validate operation.
 * @method static callable zip(iterable ...$iters) Get pipe-able zip operation.
 */
final class Pipe
{
    public static function __callStatic(string $method, array $args)
    {
        return function ($iter) use (&$method, &$args) {
            return Iter::{$method}($iter, ...$args);
        };
    }

    /**
     * TODO
     *
     * @param mixed $value
     * @param callable $fn Function to map the value to a new iterable.
     * @return Iterator
     */
    public static function switchMap($value, callable $fn): Iterator
    {
        $result = $fn($value);
        if (is_iterable($result)) {
            yield from $result;
        } else {
            yield $result;
        }
    }
}
