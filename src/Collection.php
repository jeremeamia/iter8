<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use Iterator;
use IteratorIterator;

/**
 * Collection object that represents an iterable and exposes iterable and function operations.
 *
 * @method static Collection defer(callable $fn, array $args = []) Create a collection using the Gen::defer operation.
 * @method static Collection empty() Create a collection using the Gen::empty operation.
 * @method static Collection explode(string $source, string $delimiter, int $threshold = 1024) Create a collection using the Gen::explode operation.
 * @method static Collection from($value) Create a collection using the Gen::from operation.
 * @method static Collection just($value) Create a collection using the Gen::just operation.
 * @method static Collection range(int $start, int $end) Create a collection using the Gen::range operation.
 * @method static Collection repeat($value, ?int $times = null) Create a collection using the Gen::repeat operation.
 * @method static Collection repeatForKeys(iterable $keys, $value) Create a collection using the Gen::repeatForKeys operation.
 * @method static Collection stream(resource $stream, int $bufferLength = 8192) Create a collection using the Gen::stream operation.
 * @method Collection buffer(int $size) Transform the collection using the Iter::buffer operation.
 * @method Collection concat(iterable $iter, iterable ...$iters) Transform the collection using the Iter::concat operation.
 * @method Collection combineLatest(iterable $iter, iterable ...$iters) Transform the collection using the Iter::combineLatest operation.
 * @method Collection debounce() Transform the collection using the Iter::debounce operation.
 * @method Collection distinct() Transform the collection using the Iter::distinct operation.
 * @method Collection drop(int $n) Transform the collection using the Iter::drop operation.
 * @method Collection dropWhile(callable $fn) Transform the collection using the Iter::dropWhile operation.
 * @method Collection filter(callable $fn) Transform the collection using the Iter::filter operation.
 * @method Collection filterEmpty() Transform the collection using the Iter::filterEmpty operation.
 * @method Collection filterKeys(callable $fn) Transform the collection using the Iter::filterKeys operation.
 * @method Collection filterNulls() Transform the collection using the Iter::filterNulls operation.
 * @method Collection flatMap(callable $fn) Transform the collection using the Iter::flatMap operation.
 * @method Collection flatten(int $levels = 1) Transform the collection using the Iter::flatten operation.
 * @method Collection flip() Transform the collection using the Iter::flip operation.
 * @method Collection fromKeyPairs() Transform the collection using the Iter::fromKeyPairs operation.
 * @method Collection keys() Transform the collection using the Iter::keys operation.
 * @method Collection interpose($separator) Transform the collection using the Iter::interpose operation.
 * @method Collection leaves() Transform the collection using the Iter::leaves operation.
 * @method Collection map(callable $fn) Transform the collection using the Iter::map operation.
 * @method Collection mapKeys(callable $fn) Transform the collection using the Iter::mapKeys operation.
 * @method Collection normalize() Transform the collection using the Iter::normalize operation.
 * @method Collection partition(int $count) Transform the collection using the Iter::partition operation.
 * @method Collection pipe(iterable $operations) Transform the collection using the Iter::pipe operation.
 * @method Collection pluck(string $key) Transform the collection using the Iter::pluck operation.
 * @method Collection reindex(callable $fn) Transform the collection using the Iter::reindex operation.
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
 * @method mixed search(callable $fn) Perform the Iter::search operation on the collection to calculate a result.
 * @method void apply(callable $fn, array $args = []) Perform the Iter::apply operation on the collection.
 * @method int streamTo(iterable $iter, resource &$stream) Perform the Iter::apply operation on the collection.
 * @method mixed rewindable() Convert the collection to another format using the Iter::rewindable operation.
 * @method array toArray() Convert the collection to another format using the Iter::toArray operation.
 * @method Iterator toIter() Convert the collection to another format using the Iter::toIter operation.
 * @method resource toStream() Convert the collection to another format using the Iter::toStream operation.
 * @method string toString() Convert the collection to another format using the Iter::toString operation.
 */
class Collection extends IteratorIterator
{
    /** @var bool Keeps track of whether the Collection has already been consumed. */
    private $consumed = false;

    /**
     * @param iterable $data
     * @return Collection
     */
    public static function new(iterable $data): self
    {
        return new static($data instanceof self ? $data->getInnerIterator() : Iter::toIter($data));
    }

    /**
     * @param Iterator $data
     */
    public function __construct(Iterator $data)
    {
        parent::__construct($data);
    }

    public function rewind(): void
    {
        $this->errorIfConsumed();
        parent::rewind();
        $this->markAsConsumed();
    }

    public static function __callStatic(string $method, array $args)
    {
        return new static(Gen::$method(...$args));
    }

    public function __call(string $method, array $args)
    {
        $this->errorIfConsumed();
        $result = Iter::$method($this->getInnerIterator(), ...$args);

        if ($result instanceof Iterator) {
            $result = new static($result);
        } else {
            $this->markAsConsumed();
        }

        return $result;
    }

    public function __toString(): string
    {
        $this->errorIfConsumed();
        $this->markAsConsumed();

        return Iter::toString($this->getInnerIterator());
    }

    public function __debugInfo(): array
    {
        $this->errorIfConsumed();
        $this->markAsConsumed();

        return Iter::toArray($this->getInnerIterator());
    }

    /**
     * Used to throw an exception if the collection has already been consumed (e.g., iterated).
     *
     * This is to prevent calling rewind on the underlying generator.
     *
     * @throws AlreadyConsumedException
     */
    private function errorIfConsumed(): void
    {
        if ($this->consumed) {
            throw AlreadyConsumedException::new();
        }
    }

    /**
     * Marks this collection as being consumed.
     */
    private function markAsConsumed(): void
    {
        $this->consumed = true;
    }
}
