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

    public function __construct(iterable $iter)
    {
        if ($iter instanceof static) {
            $this->iterator = $iter->getInnerIterator();
        } else {
            $this->iterator = Iter::toKeyPairs($iter);
            $this->cache = new ArrayIterator();
        }
    }

    public function getInnerIterator(): ArrayIterator
    {
        if ($this->cache) {
            $this->rewind();
        }

        /** @var ArrayIterator $inner */
        $inner = $this->iterator;

        return $inner;
    }

    public function current()#: mixed
    {
        $tuple = $this->iterator->current();

        if ($this->cache) {
            $this->cache[$this->iterator->key()] = $tuple;
        }

        return $tuple[1];
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function key()#: mixed
    {
        $tuple = $this->iterator->current();

        if ($this->cache) {
            $this->cache[$this->iterator->key()] = $tuple;
        }

        return $tuple[0];
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
