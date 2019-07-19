<?php declare(strict_types=1);

namespace Jeremeamia\Iter8;

use UnexpectedValueException;

/**
 * Provides helpers for creating common callables used in map, filter, and reduce operations.
 */
final class Func
{
    public const PLACEHOLDER = "\0…\0";

    /**
     * Creates a callable that will call the specified method (with the specified args) for a provided object.
     *
     * Example:
     *
     *     $fn = Func::method('getNestedValue', 'users.*.name');
     *     $fn($apiResult);
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
     *     $fn($person);
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
     *     $fn($person);
     *     # Equivalent to: $person['name']
     *
     * @param int|string $index Index/key of the array to access.
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
     *     $fn('VALUE', 'KEY');
     *     #> "value"
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
     *     $fn(1);
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
     *     $fn(0);
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
     *     $fn(true);
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
     *     $fn(3);
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
     *     $fn(4);
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
     * Creates a callable that wraps a string value with other string values.
     *
     * Example:
     *
     *     $fn = Func::wrap('{', '}');
     *     $fn('foo');
     *     #> "{foo}"
     *
     *     $fn = Func::wrap('*');
     *     $fn('foo')
     *     #> "*foo*"
     *
     * @param string $prefix
     * @param null|string $suffix
     * @return callable
     */
    public static function wrap(string $prefix, ?string $suffix = null): callable
    {
        $suffix = $suffix ?? $prefix;

        return function ($value) use ($prefix, $suffix) {
            return "{$prefix}{$value}{$suffix}";
        };
    }

    /**
     * Creates a callable that prefixes a string value with another string value.
     *
     * Example:
     *
     *     $fn = Func::prefix('$');
     *     $fn('foo');
     *     #> "$foo"
     *
     * @param string $prefix
     * @return callable
     */
    public static function prefix(string $prefix): callable
    {
        return self::wrap($prefix, '');
    }

    /**
     * Creates a callable that suffixes a string value with another string value.
     *
     * Example:
     *
     *     $fn = Func::suffix('$');
     *     $fn('foo');
     *     #> "foo$"
     *
     * @param string $suffix
     * @return callable
     */
    public static function suffix(string $suffix): callable
    {
        return self::wrap('', $suffix);
    }

    /**
     * Creates a callable that evaluates the equality of the provided value and the functions input value.
     *
     * Example:
     *
     *     $fn = Func::eq(5);
     *     $fn(2 + 3);
     *     #> true
     *
     * @param mixed $expected
     * @return callable
     */
    public static function eq($expected): callable
    {
        return function ($actual) use (&$expected) {
            return $actual === $expected;
        };
    }

    /**
     * Creates a callable that checks if the input object is an instance of the provided class.
     *
     * Example:
     *
     *     $fn = Func::instanceOf(Exception::class);
     *     $fn(new RuntimeException());
     *     #> true
     *
     * @param string $class
     * @return callable
     */
    public static function instanceOf(string $class): callable
    {
        return function ($object) use (&$class) {
            return $object instanceof $class;
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
     * @param iterable|callable[] $operations List of callables to compose together.
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
     * Performs a partial function application of the provided callable.
     *
     * The args list should include the set of fixed args and variable arg placeholders (i.e., the `Func::PLACEHOLDER`
     * constant) to mark where the variable argument should be injected. Any leftover variable arguments are added to
     * the end of the argument list, such that trailing placeholders are not needed.
     *
     * Example:
     *
     *     $explodeOnPipe = Func::apply('explode', '|');
     *     $explodeOnPipe('a|b|c');
     *     #> ['a', 'b', 'c']
     *
     *     $trimAngleBrackets = Func::apply('trim', Func::PLACEHOLDER, '<>');
     *     $trimAngleBrackets('<foo>');
     *     #> 'foo'
     *
     * @param callable $fn
     * @param array ...$fixedArgs
     * @return callable
     */
    public static function apply(callable $fn, ...$fixedArgs): callable
    {
        return function (...$varArgs) use ($fn, $fixedArgs) {
            foreach ($fixedArgs as &$arg) {
                if ($arg === Func::PLACEHOLDER) {
                    $arg = array_shift($varArgs);
                }
            }

            return $fn(...array_merge($fixedArgs, $varArgs));
        };
    }

    /**
     * Creates a callable that memoizes the results of the provided callable.
     *
     * Note: This general memoization technique requires serialization of the arguments on each call, which fairly slow.
     * Unless the function being memoized does something much slower, then it might not be worth it.
     *
     * Example:
     *
     *     $getUser = Func::memoize([$userRepository, 'getUser']);
     *     $user = $getUser($id);
     *     // Does not access data source again on second call.
     *     $userAgain = $getUser($id);
     *
     * @param callable $fn
     * @return callable
     */
    public static function memoize(callable $fn): callable
    {
        $results = [];

        return function(...$args) use ($fn, &$results) {
            $hash = md5(serialize(array_map(function ($v) {
                return is_object($v) ? spl_object_hash($v) : $v;
            }, $args)));

            if (!isset($results[$hash])) {
                $results[$hash] = $fn(...$args);
            }

            return $results[$hash];
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
     *     $fn(3, 7);
     *     #> 10
     *
     * Example (map):
     *
     *     $fn = Func::operator('+', 7);
     *     $fn(3);
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

                default: throw new UnexpectedValueException("Unexpected operator: {$operator}");
            }
        };
    }
}
