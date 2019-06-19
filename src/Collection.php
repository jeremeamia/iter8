<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use Iterator;
use IteratorIterator;

/**
 * TODO
 *
 * @method static Collection range(int $start, int $end)
 * @method Collection partition(int $size)
 * @method Collection concat(iterable ...$iters)
 * @method Collection drop(int $n)
 * @method Collection filter(callable $fn)
 * @method Collection filterKeys(callable $fn)
 * @method Collection flatMap(callable $fn)
 * @method Collection flatten()
 * @method Collection flip()
 * @method Collection keys()
 * @method Collection map(callable $fn)
 * @method Collection mapKeys(callable $fn)
 * @method Collection pluck(string $key)
 * @method Collection reindex(callable $fn)
 * @method Collection replaceKeys(iterable $keys)
 * @method Collection replaceValues(iterable $values)
 * @method Collection slice(int $offset, ?int $length = null)
 * @method Collection take(int $n)
 * @method Collection tap(callable $fn)
 * @method Collection values()
 * @method array toArray()
 * @method string toString()
 * @method resource toStream()
 * @method Iterator toIter()
 */
class Collection extends IteratorIterator
{
    private $consumed = false;

    public static function new(iterable $data): self
    {
        return new static($data instanceof self ? $data->getInnerIterator() : Iter::toIter($data));
    }

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
     * @throws AlreadyConsumedException
     */
    private function errorIfConsumed(): void
    {
        if ($this->consumed) {
            throw AlreadyConsumedException::new();
        }
    }

    private function markAsConsumed(): void
    {
        $this->consumed = true;
    }
}
