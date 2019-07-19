# Iter8

[![Made with Love](https://img.shields.io/badge/made_with-♥-ff69b4.svg)](https://github.com/jeremeamia/iter8/graphs/contributors)
[![Coded in PHP](https://img.shields.io/badge/code-php-8892bf.svg)](http://php.net/)
[![CircleCI](https://circleci.com/gh/jeremeamia/iter8/tree/master.svg?style=svg)](https://circleci.com/gh/jeremeamia/iter8/tree/master)

## Introduction

Iter8 is a PHP library for iterable and functional operations (e.g., map, filter, reduce) implemented using generators.

Iter8 provides ways to create and transform any `iterable`s (e.g., generators, iterators, arrays, etc.) easily in order
to deal with data sets that fit the `Iterator` pattern use case (i.e., typically data of large, paginated, infinite, or
unknown length). Using iterators/generators generally provides benefits like lower memory consumption and lazy
evaluation. Complex transformations can be defined via functional composition.

## Usage

Iter8's core implementations reside as static methods in 3 classes:

- `Iter` – Operations for iterable values. Some transform (e.g., map, filter) and some evaluate (e.g., reduce).
- `Gen` – Factories for creating iterables from other values.
- `Func` – Utilities for creating or transforming callables to be used with iterable operations.

There are 3 usage patterns for working with iterable values available in Iter8. Which you use is mostly a matter of
preference:

- `Iter` functions – Standard function-oriented usage with the `Iter` functions.
- `Pipe` composition – Use of `Iter::pipe()` and the `Pipe::*` functions to compose a set of iterable transformations.
- `Collection` object - An OOP interface to Iter8, that exposes the `Iter::*` and `Gen::*` functions as chainable
  methods on an a collection-type object.
  
The examples in the next section will demonstrate each of these usage patterns.

## Examples

Given the following data set...

```php
const PEOPLE = [
    ['name' => 'Abby',  'age' => 19],
    ['name' => 'Benny', 'age' => 21],
    ['name' => 'Cally', 'age' => 22],
    ['name' => 'Danny', 'age' => 24],
    ['name' => 'Danny', 'age' => 24],
    ['name' => 'Eddy',  'age' => 18],
];
```

### Iter Functions

With this usage pattern, operations are applied procedurally and one-at-a-time.

```php
$iter = Gen::from(PEOPLE);
$iter = Iter::filter($iter, Func::compose([
    Func::index('age'),
    Func::operator('>=', 20),
]));
$iter = Iter::map($iter, Func::index('name'));
$iter = Iter::debounce($iter);

Iter::print($iter);
#> ['Benny', 'Cally', 'Danny']
```

### Pipe Composition

With this usage pattern, operations are "piped" or composed together. The `Pipe` class delegates its operations back to
the `Iter` class, but manages the iterable value.

```php
$iter = Iter::pipe(Gen::from(PEOPLE), [
    Pipe::filter(Func::compose([
        Func::index('age'),
        Func::operator('>=', 20),
    ])),
    Pipe::map(Func::index('name')),
    Pipe::debounce(),
]);

Iter::print($iter);
#> ['Benny', 'Cally', 'Danny']
```

You can "switch" the context of the iterable you are transforming in the middle of a pipe. This example evaluates the
max age from the iterable of people, and then switches to a new iterable using that max age value.

```php
$iter = Iter::pipe(Gen::from(PEOPLE), [
    Pipe::map(Func::index('age')),
    Pipe::reduce('max'),
    Pipe::switch(function (int $maxAge) {
        return Gen::range(1, $maxAge);
    }),
]);

Iter::print($iter);
#> [1, 2, 3, ..., 22, 23, 24]
```

## Rewindability

Generators are not rewindable (i.e., calling `rewind()` on them explicitly or trying `foreach` them again will cause an
error). Iter8 provides two ways to make generators/iterables rewindable.

### Deferred Generators

If you are in control of the function that produces the generator (i.e., the one containing the `yield` statements),
then you can use the `Gen::defer()` function to wrap that generating function.

```php
$items = Gen::defer(function () use ($apiClient) {
    $apiResult = $apiClient->getItems();
    foreach ($apiResult['items'] as $data) {
        yield Models\Item::fromArray($data);
    }
});

// ...
// First iteration
foreach ($items as $item) { /* ... */ }
// ...
// Another iteration
foreach ($items as $item) { /* ... */ }
```

`Gen::defer()` returns a `DeferredGenerator` iterator, that defers producing the actual generator until the time of
iteration. If you rewind or iterate again, then the generating function is re-executed.

### Rewindable Iterator

If you don't control the generating function, then you can retroactively make the iterable rewindable by using the
`Iter::rewindable()` function.

```php
$apiResult = $apiClient->getItems();
$items = Iter::map($apiResult['items'], function (array $data) {
    return Models\Item::fromArray($data);
});
$items = Iter::rewindable($items);

// ...
// First iteration
foreach ($items as $item) { /* ... */ }
// ...
// Another iteration
foreach ($items as $item) { /* ... */ }
```

`Iter::rewindable()` wraps the provided iterable in a `RewindableIterator`, which caches items during the first
iteration, such that they can be re-emitted in later iterations.

### Collection Object

With this usage pattern, the iterable is encapsulated as a `Collection` object. Calling methods on the collection object
delegate back to the `Iter` class, but the iterable is managed internally. Collections are immutable, so each
transformation returns a new instance. Also, unlike regular generators, collections can be rewound. Static method calls
on `Collection` are delegated to `Gen`, so the `Collection` object actually exposes the breadth of Iter8's functionality
from one interface.

```php
$collection = Collection::from(PEOPLE)
    ->filter(Func::compose([
        Func::index('age'),
        Func::operator('>=', 20),
    ]))
    ->map(Func::index('name'))
    ->debounce();

$collection->print();
#> ['Benny', 'Cally', 'Danny']
```

## Inspiration

A lot of my recent work in PHP has dealt with iterators and generators, and fiddling about with large API result sets,
so I wanted to put something like this together to share.

However, some work like this has been done before in libraries like [nikic/iter][iter]. You'll notice that I have some
similar functions and implementations. Some of my work here is inspired by that library, and some is straight borrowed.

In addition, I've taken some inspiration from the [ReactiveX][] project. Though generators and observables are not
identical concepts, they are similar, so some of the operations and names of operations have been borrowed when they
apply equally well to both generators and observables. Also, the concept of "pipe" from [RxJS][], the JavaScript
implementation of ReactiveX, is implemented in this project for composing transformations. In a way, this is also
similar to how the [mtdowling/transducers.php][transducers] library works with its "composable algorithmic
transformations". The idea of "transducers" themselves are [borrowed from Clojure][clojure], so I've looked to several
sources for ideas.

Finally, I've also taken ideas from the [Laravel Collections][laravel] library, and though I also have some similar
functions, my implementations vary greatly as they are founded upon generators, not arrays. This means that random
array access of values in Iter8 collections is not supported.

[iter]: https://github.com/nikic/iter
[ReactiveX]: http://reactivex.io/
[RxJS]: https://github.com/ReactiveX/rxjs
[transducers]: https://github.com/mtdowling/transducers.php
[clojure]: https://clojure.org/reference/transducers
[laravel]: https://laravel.com/docs/5.8/collections
