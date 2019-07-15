<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use Iterator;
use OuterIterator;

/**
 * A "Regenerator" is a rewindable generator that rewinds by regenerating the values.
 */
class Regenerator implements OuterIterator
{
    /** @var callable */
    private $fn;

    /** @var array */
    private $args;

    /** @var Iterator */
    private $iter;

    /**
     * @param callable $fn
     * @param array $args
     */
    public function __construct(callable $fn, array $args = [])
    {
        $this->fn = $fn;
        $this->args = $args;
    }

    public function getInnerIterator(): Iterator
    {
        if (!$this->iter) {
            $this->iter = ($this->fn)(...$this->args);
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
