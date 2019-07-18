# Iter8

[![Made with Love](https://img.shields.io/badge/made_with-♥-ff69b4.svg)](https://github.com/jeremeamia/iter8/graphs/contributors)
[![Coded in PHP](https://img.shields.io/badge/code-php-8892bf.svg)](http://php.net/)
[![CircleCI](https://circleci.com/gh/jeremeamia/iter8/tree/master.svg?style=svg)](https://circleci.com/gh/jeremeamia/iter8/tree/master)

## Introduction

Iter8 is a PHP library for generator and functional operations.

Iter8 provides ways to create and transform generators easily in order to deal with data sets that fit the Iterator
pattern use case (i.e., those of large, paginated, infinite, or unknown length). Using iterators/generators generally
provides benefits like lower memory consumption and lazy evaluation. It also allows easy extension via functional
composition or the OOP Decorator pattern.

## Usage

Iter8 has 3 different usage patterns to choose from:

- `Iter` functions – Individual functions that perform operations on iterable values.
- `Pipe` composition – The use of `Iter::pipe()` and the `Pipe::*` functions to compose an iterable transformation.
- `Collection` object - An OOP encapsulation of Iter8, that exposes the `Iter::*` and `Gen::*` functions as chainable
  methods on an `Iterator`-type object.
  
The examples in the next section will demonstrate each of these usage patterns. You will also see to other classes in
action:

- `Gen` - Collection of functions for creating generators from some kind of source data or parameters.
- `Func` - Collection of functions for preparing callables used for map, filter, and reduce operations.

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

### Collection Object

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

Finally, I've also taken ideas from the [Laravel Collections][laravel] library, and though I also have similar
functions, my implementations vary greatly as they are founded upon generators, not arrays. This means that random
array access of values in Iter8 collections is not supported.

[iter]: https://github.com/nikic/iter
[ReactiveX]: http://reactivex.io/
[RxJS]: https://github.com/ReactiveX/rxjs
[transducers]: https://github.com/mtdowling/transducers.php
[clojure]: https://clojure.org/reference/transducers
[laravel]: https://laravel.com/docs/5.8/collections
