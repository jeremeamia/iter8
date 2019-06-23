<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use UnexpectedValueException;

/**
 * Provides helpers for creating common callables used in map, filter, and reduce operations.
 */
final class Func
{
    /**
     * Creates a callable that will call the specified method (with the specified args) for a provided object.
     *
     * Example:
     *
     *     $fn = Func::method('getNestedValue', 'users.*.name');
     *     $fn($apiResult)
     *     # Equivalent to: $apiResult->getNestedValue('users.*.name')
     *
     * @param string $method Name of the method to call.
     * @param mixed ...$args Arguments to call the method with.
     * @return callable
     */
    public static function method(string $method, ...$args): callable
    {
        return function (object $object) use ($method, $args) {
            return $object->{$method}(...$args);
        };
    }

    /**
     * Creates a callable that will access the specified property for a provided object.
     *
     * Example:
     *
     *     $fn = Func::property('name');
     *     $fn($person)
     *     # Equivalent to: $person->name
     *
     * @param string $property Name of the property to access.
     * @param mixed|null $default A default value to return if the property is not set.
     * @return callable
     */
    public static function property(string $property, $default = null): callable
    {
        return function (object $object) use ($property, $default) {
            return $object->{$property} ?? $default;
        };
    }

    /**
     * Creates a callable that will access the specified index/key for a provided array.
     *
     * Example:
     *
     *     $fn = Func::index('name');
     *     $fn($person)
     *     # Equivalent to: $person['name']
     *
     * @param string $index Index/key of the array to access.
     * @param mixed|null $default A default value to return if the property is not set.
     * @return callable
     */
    public static function index($index, $default = null): callable
    {
        return function ($array) use ($index, $default) {
            return $array[$index] ?? $default;
        };
    }

    /**
     * Creates a callable for unary functions (i.e., arity of 1) that would otherwise error if multiple args are passed.
     *
     * This can be particularly helpful when using native PHP functions with Iter::map, which passes the value and key.
     * In these cases, the additional arguments are discarded.
     *
     * Example:
     *
     *     $fn = Func::unary('strtolower');
     *     $fn('VALUE', 'KEY')
     *     #> 'value'
     *
     * @param callable $fn
     * @return callable
     */
    public static function unary(callable $fn): callable
    {
        return function (...$args) use ($fn) {
            return $fn($args[0]);
        };
    }

    /**
     * Creates a callable that returns true for any loosely-typed truthy value.
     *
     * Example:
     *
     *     $fn = Func::truthy();
     *     $fn(1)
     *     #> true
     *
     * @return callable
     */
    public static function truthy(): callable
    {
        return function ($value) {
            return !empty($value);
        };
    }

    /**
     * Creates a callable that returns true for any loosely-typed falsey value.
     *
     * Example:
     *
     *     $fn = Func::falsey();
     *     $fn(0)
     *     #> true
     *
     * @return callable
     */
    public static function falsey(): callable
    {
        return function ($value) {
            return empty($value);
        };
    }

    /**
     * Creates a callable that returns the opposite of the result of the provided callable.
     *
     * Example:
     *
     *     $fn = Func::not(function (bool $isTrue) { return $isTrue; });
     *     $fn(true)
     *     #> false
     *
     * @param callable $fn
     * @return callable
     */
    public static function not(callable $fn): callable
    {
        return function (...$args) use ($fn) {
            return !$fn(...$args);
        };
    }

    /**
     * Creates a callable that returns whether the provided value is odd.
     *
     * This is primarily useful for filtering.
     *
     * Example:
     *
     *     $fn = Func::odd();
     *     $fn(3)
     *     #> true
     *
     * @return callable
     */
    public static function odd(): callable
    {
        return function ($value) {
            return $value % 2 === 1;
        };
    }

    /**
     * Creates a callable that returns whether the provided value is even.
     *
     * This is primarily useful for filtering.
     *
     * Example:
     *
     *     $fn = Func::even();
     *     $fn(4)
     *     #> true
     *
     * @return callable
     */
    public static function even(): callable
    {
        return function ($value) {
            return $value % 2 === 0;
        };
    }

    /**
     * Creates a callable that is a composition of the provided callable unary functions.
     *
     * The main use case in this lib is for iterable transformation functions, but it's technically generic.
     *
     * Example:
     *
     *     $fn = Func::compose([
     *         Pipe::debounce(),
     *         Pipe::map(Func::operator('*', 2)),
     *         Pipe::filter(Func::operator('>', 10)),
     *     ]);
     *
     *     $iter = $fn([2, 2, 6, 3, 8, 8]);
     *     #> [12, 16]
     *
     * @param iterable $operations
     * @return callable
     */
    public static function compose(iterable $operations): callable
    {
        return function ($data) use (&$operations) {
            foreach ($operations as $operation) {
                $data = $operation($data);
            }

            return $data;
        };
    }

    /**
     * Creates a callable that returns the result of a standard PHP mathematical, bitwise, or boolean operator.
     *
     * This can be used for more mapping or reducing.
     *
     * Example (reduce):
     *
     *     $fn = Func::operator('+');
     *     $fn(3, 7)
     *     #> 10
     *
     * Example (map):
     *
     *     $fn = Func::operator('+', 7);
     *     $fn(3)
     *     #> 10
     *
     * @param string $operator
     * @param mixed|null $rightOperand
     * @return callable
     */
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
                case '|': return $leftOperand | $rightOperand;
                case '&': return $leftOperand & $rightOperand;
                case '^': return $leftOperand ^ $rightOperand;
                case '>': return $leftOperand > $rightOperand;
                case '<': return $leftOperand < $rightOperand;
                case '.': return $leftOperand . $rightOperand;

                case '==': return $leftOperand == $rightOperand;
                case '!=': return $leftOperand != $rightOperand;
                case '>=': return $leftOperand >= $rightOperand;
                case '<=': return $leftOperand <= $rightOperand;
                case '||': return $leftOperand || $rightOperand;
                case '&&': return $leftOperand && $rightOperand;
                case '**': return $leftOperand ** $rightOperand;
                case '>>': return $leftOperand >> $rightOperand;
                case '<<': return $leftOperand << $rightOperand;

                case '===': return $leftOperand === $rightOperand;
                case '!==': return $leftOperand !== $rightOperand;
                case '<=>': return $leftOperand <=> $rightOperand;

                case 'instanceof': return $leftOperand instanceof $rightOperand;

                default: throw new UnexpectedValueException("Unexpected operator: {$operator}");
            }
        };
    }
}
