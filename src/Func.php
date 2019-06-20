<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use UnexpectedValueException;

final class Func
{
    public static function method(string $method, ...$args): callable
    {
        return function (object $object) use ($method, $args) {
            return $object->{$method}(...$args);
        };
    }

    public static function property(string $property): callable
    {
        return function (object $object) use ($property) {
            return $object->{$property};
        };
    }

    public static function index($index, $default = null): callable
    {
        return function ($array) use ($index, $default) {
            return $array[$index] ?? $default;
        };
    }

    public static function unary(callable $fn): callable
    {
        return function (...$args) use ($fn) {
            return $fn($args[0]);
        };
    }

    public static function truthy(): callable
    {
        return function ($value) {
            return !empty($value);
        };
    }

    public static function falsey(): callable
    {
        return function ($value) {
            return empty($value);
        };
    }

    public static function not(callable $fn): callable
    {
        return function (...$args) use ($fn) {
            return !$fn(...$args);
        };
    }

    public static function odd(): callable
    {
        return function ($value) {
            return $value % 2 === 1;
        };
    }

    public static function even(): callable
    {
        return function ($value) {
            return $value % 2 === 0;
        };
    }

    public static function dump(?string $prefix = null): callable
    {
        return function ($value) use ($prefix) {
            if ($prefix !== null) {
                echo "{$prefix}: ";
            }
            var_dump($value);
        };
    }

    public static function operator(string $operator, $rightOperand = null): callable
    {
        return function ($leftOperand, $reduceOperand = null) use ($operator, $rightOperand) {
            $rightOperand = $rightOperand ?? $reduceOperand;
            if ($rightOperand === null) {
                throw new UnexpectedValueException("Missing right operand for operator");
            }

            switch ($operator) {
                case '+': return $leftOperand + $rightOperand;
                case '-': return $leftOperand - $rightOperand;
                case '*': return $leftOperand * $rightOperand;
                case '/': return $leftOperand / $rightOperand;
                case '%': return $leftOperand % $rightOperand;
                default: throw new UnexpectedValueException("Unexpected operator: {$operator}");
            }
        };
    }
}
