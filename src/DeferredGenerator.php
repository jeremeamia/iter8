<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use Iterator;
use OuterIterator;

/**
 * Encapsulates a lazily-created generator.
 *
 * The generator is lazily-created by the provided function and argument list. The generator is recreated every time
 * the iterator is rewound such that even though the data source is technically a generator, the iterator can still
 * support rewinding.
 */
class DeferredGenerator implements OuterIterator
{
    /** @var callable */
    private $fn;

    /** @var array */
    private $args;

    /** @var Iterator|null */
    private $iter;

    /**
     * @param callable $fn
     * @param array $args
     */
    public function __construct(callable $fn, array $args = [])
    {
        $this->fn = $fn;
        $this->args = $args;
        $this->iter = null;
    }

    public function getInnerIterator(): Iterator
    {
        if ($this->iter === null) {
            $this->iter = Gen::from(($this->fn)(...$this->args));
        }

        return $this->iter;
    }

    public function rewind()
    {
        $this->iter = null;
        $this->getInnerIterator()->rewind();
    }

    public function next()
    {
        $this->getInnerIterator()->next();
    }

    public function valid()
    {
        return $this->getInnerIterator()->valid();
    }

    public function key()
    {
        return $this->getInnerIterator()->key();
    }

    public function current()
    {
        return $this->getInnerIterator()->current();
    }
}
