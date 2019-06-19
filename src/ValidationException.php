<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use RuntimeException;

final class ValidationException extends RuntimeException
{
    public static function for($key): self
    {
        return new self("The value for key {$key} in the iterable was invalid.");
    }
}
