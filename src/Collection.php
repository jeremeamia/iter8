<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use Iterator;

/**
 * Collection object that encapsulates an iterable and exposes chainable transformation operations.
 *
 * This class uses __call() and __callStatic() to delegate operations to the Iter and Gen classes, respectively.
 *
 * @method static Collection defer(callable $fn, array $args = []) Create a collection using the Gen::defer operation.
 * @method static Collection empty() Create a collection using the Gen::empty operation.
 * @method static Collection explode(string $source, string $delimiter, int $threshold = 1024) Create a collection using the Gen::explode operation.
 * @method static Collection from($value) Create a collection using the Gen::from operation.
 * @method static Collection fromStream(resource $stream, int $bufferLength = 8192) Create a collection using the Gen::stream operation.
 * @method static Collection just($value) Create a collection using the Gen::just operation.
 * @method static Collection range(int $start, int $end) Create a collection using the Gen::range operation.
 * @method static Collection repeat($value, ?int $times = null) Create a collection using the Gen::repeat operation.
 * @method static Collection repeatForKeys(iterable $keys, $value) Create a collection using the Gen::repeatForKeys operation.
 * @method static Collection stream(resource $stream, int $bufferLength = 8192) Create a collection using the Gen::stream operation.
 * @method Collection chunk(int $size) Transform the collection using the Iter::buffer operation.
 * @method Collection concat(iterable $iter, iterable ...$iters) Transform the collection using the Iter::concat operation.
 * @method Collection combineLatest(iterable $iter, iterable ...$iters) Transform the collection using the Iter::combineLatest operation.
 * @method Collection debounce() Transform the collection using the Iter::debounce operation.
 * @method Collection distinct() Transform the collection using the Iter::distinct operation.
 * @method Collection drop(int $n) Transform the collection using the Iter::drop operation.
 * @method Collection dropWhile(callable $fn) Transform the collection using the Iter::dropWhile operation.
 * @method Collection filter(callable $fn) Transform the collection using the Iter::filter operation.
 * @method Collection filterKeys(callable $fn) Transform the collection using the Iter::filterKeys operation.
 * @method Collection filterWithKeys() Transform the collection using the Iter::filterWithKeys operation.
 * @method Collection flatMap(callable $fn) Transform the collection using the Iter::flatMap operation.
 * @method Collection flatten(int $levels = 1) Transform the collection using the Iter::flatten operation.
 * @method Collection flip() Transform the collection using the Iter::flip operation.
 * @method Collection fromKeyPairs() Transform the collection using the Iter::fromKeyPairs operation.
 * @method Collection keys() Transform the collection using the Iter::keys operation.
 * @method Collection interpose($separator) Transform the collection using the Iter::interpose operation.
 * @method Collection leaves() Transform the collection using the Iter::leaves operation.
 * @method Collection map(callable $fn) Transform the collection using the Iter::map operation.
 * @method Collection mapKeys(callable $fn) Transform the collection using the Iter::mapKeys operation.
 * @method Collection mapWithKeys(callable $fn) Transform the collection using the Iter::mapWithKeys operation.
 * @method Collection mapRecursive(callable $fn) Transform the collection using the Iter::map operation.
 * @method Collection normalize() Transform the collection using the Iter::normalize operation.
 * @method Collection partition(int $count) Transform the collection using the Iter::partition operation.
 * @method Collection pipe(iterable $operations) Transform the collection using the Iter::pipe operation.
 * @method Collection pluck(string $key) Transform the collection using the Iter::pluck operation.
 * @method Collection reindex(callable $fn) Transform the collection using the Iter::reindex operation.
 * @method Collection removeEmpty() Transform the collection using the Iter::removeEmpty operation.
 * @method Collection removeNulls() Transform the collection using the Iter::removeNulls operation.
 * @method Collection replaceKeys(iterable $keys) Transform the collection using the Iter::replaceKeys operation.
 * @method Collection replaceValues(iterable $values) Transform the collection using the Iter::replaceValues operation.
 * @method Collection replay(iterable $values, ?int $times = null) Transform the collection using the Iter::replay operation.
 * @method Collection scan(callable $fn, $initialValue = null) Transform the collection using the Iter::scan operation.
 * @method Collection slice(int $offset, ?int $length = null) Transform the collection using the Iter::slice operation.
 * @method Collection take(int $n) Transform the collection using the Iter::take operation.
 * @method Collection takeWhile(callable $fn) Transform the collection using the Iter::takeWhile operation.
 * @method Collection tap(callable $fn) Transform the collection using the Iter::tap operation.
 * @method Collection toKeyPairs() Transform the collection using the Iter::toKeyPairs operation.
 * @method Collection validate(callable $fn) Transform the collection using the Iter::validate operation.
 * @method Collection values() Transform the collection using the Iter::values operation.
 * @method Collection where(string $key, $value) Transform the collection using the Iter::where operation.
 * @method Collection zip(iterable $iter, iterable ...$iters) Transform the collection using the Iter::zip operation.
 * @method mixed all(callable $fn) Perform the Iter::all operation on the collection to calculate a result.
 * @method mixed any(callable $fn) Perform the Iter::any operation on the collection to calculate a result.
 * @method mixed first() Perform the Iter::first operation on the collection to calculate a result.
 * @method string implode(string $separator = '') Perform the Iter::implode operation on the collection to calculate a result.
 * @method mixed last() Perform the Iter::last operation on the collection to calculate a result.
 * @method mixed reduce(callable $fn, $initialValue = null) Perform the Iter::reduce operation on the collection to calculate a result.
 * @method mixed reduceRecursive(callable $fn, $initialValue = null) Perform the Iter::reduceRecursive operation on the collection to calculate a result.
 * @method mixed search(callable $fn) Perform the Iter::search operation on the collection to calculate a result.
 * @method void apply(callable $fn, array $args = []) Perform the Iter::apply operation on the collection.
 * @method void applyRecursive(callable $fn, array $args = []) Perform the Iter::applyRecursive operation on the collection.
 * @method void print() Perform the Iter::print operation on the collection.
 * @method int streamTo(iterable $iter, resource &$stream) Perform the Iter::streamTo operation on the collection.
 * @method mixed rewindable() Convert the collection to another format using the Iter::rewindable operation.
 * @method array toArray() Convert the collection to another format using the Iter::toArray operation.
 * @method array toArrayRecursive() Convert the collection to another format using the Iter::toArrayRecursive operation.
 * @method resource toStream() Convert the collection to another format using the Iter::toStream operation.
 * @method string toString() Convert the collection to another format using the Iter::toString operation.
 */
class Collection extends RewindableIterator
{
    /**
     * Delegates the creation of an iterable to the `Gen` class and wraps it in a collection.
     *
     * @param string $method Method name available in the Gen class.
     * @param array $args Method args
     * @return Collection
     */
    public static function __callStatic(string $method, array $args)
    {
        return new static(Gen::$method(...$args));
    }

    /**
     * Delegates an operation on the internal iterable to the `Iter` class.
     *
     * If the operation results in a new iterable, it is wrapped in a collection to support method chaining. If the
     * value is terminal (not an iterable), then it is return as-is.
     *
     * @param string $method Method name available in the Iter class.
     * @param array $args Method args
     * @return Collection
     */
    public function __call(string $method, array $args)
    {
        $result = Iter::{$method}($this, ...$args);

        return $result instanceof Iterator ? new static($result) : $result;
    }

    /**
     * Supports use of the object as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return Iter::toString($this);
    }

    /**
     * Supports use of var_dump() on the object.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return ['data' => Iter::toArray($this)];
    }
}
