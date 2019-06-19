<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use RuntimeException;

final class AlreadyConsumedException extends RuntimeException
{
    public static function new(): self
    {
        return new self('The collection has already been consumed and cannot be re-iterated.');
    }
}
