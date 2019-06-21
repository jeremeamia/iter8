<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use ArrayIterator;
use Countable;
use Iterator;
use OuterIterator;

/**
 * Wraps an iterator to make it rewindable.
 *
 * The first iteration "caches" its data for subsequent iterations into a new, internal ArrayIterator.
 */
class RewindableIterator implements OuterIterator, Countable
{
    /** @var ArrayIterator|Iterator */
    private $iterator;

    /** @var ArrayIterator|null */
    private $cache;

    public static function new(iterable $iter)
    {
        return new self(is_array($iter) ? new ArrayIterator($iter) : Iter::toIter($iter));
    }

    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
        $this->cache = $iterator instanceof ArrayIterator ? null : new ArrayIterator();
    }

    public function getInnerIterator(): ArrayIterator
    {
        while ($this->cache) {
            $this->rewind();
        }

        return $this->iterator;
    }

    public function current()#: mixed
    {
        $key = $this->iterator->key();
        $item = $this->iterator->current();

        if ($this->cache && !isset($this->cache[$key])) {
            $this->cache[$key] = $item;
        }

        return $item;
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function key()#: mixed
    {
        $key = $this->iterator->key();

        if ($this->cache && !isset($this->cache[$key])) {
            $this->cache[$key] = $this->iterator->current();
        }

        return $key;
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    public function rewind(): void
    {
        if ($this->cache) {
            while ($this->valid()) {
                $this->current();
                $this->next();
            }
            $this->iterator = $this->cache;
            $this->cache = null;
        }

        $this->iterator->rewind();
    }

    public function count(): int
    {
        return $this->getInnerIterator()->count();
    }
}
