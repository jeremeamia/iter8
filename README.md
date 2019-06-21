# Iter8

PHP library for Generator operations.

License: MIT

## Examples

Given the following data set...

```php
const PEOPLE = [
    'students' => [
        ['name' => 'Abby',  'age' => 19],
        ['name' => 'Benny', 'age' => 21],
        ['name' => 'Cally', 'age' => 22],
        ['name' => 'Danny', 'age' => 24],
        ['name' => 'Eddy',  'age' => 18],
    ],
    'teachers' => [
        ['name' => 'Tommy', 'age' => 58],
    ],
];
```

### Iter Functions

You can use the `Iter` functions to transform and operate on the data via generators.

```php
$iter = Gen::from(self::PEOPLE);
$iter = Iter::flatten($iter);
$iter = Iter::map($iter, function (array $data) { return new Person($data); });
$iter = Iter::filter($iter, function (Person $person) { return $person->age >= 20; });
$iter = Iter::map($iter, Func::property('name'));
$iter = Iter::drop($iter, 1);

print_r(Iter::toArray($iter));
#> ['Cally', 'Danny', 'Tommy']
```

### Pipe Functions

You can also use a "pipe"-style list of functions to transform and operate on the data via generators.
        
```php
$iter = Iter::pipe(Gen::from(self::PEOPLE), [
    Pipe::flatten(),
    Pipe::map(function (array $data) { return new Person($data); }),
    Pipe::filter(function (Person $person) { return $person->age >= 20; }),
    Pipe::map(Func::property('name')),
    Pipe::drop(1),
]);

print_r(Iter::toArray($iter));
#> ['Cally', 'Danny', 'Tommy']
```

### Collection Functions

Finally, you can use an OOP fluent Collection object to transform and operate on the data via generators.

```php
$collection = Collection::from(self::PEOPLE)
    ->flatten()
    ->map(function (array $data) { return new Person($data); })
    ->filter(function (Person $person) { return $person->age >= 20; })
    ->map(Func::property('name'))
    ->drop(1);

print_r(Iter::toArray($iter));
#> ['Cally', 'Danny', 'Tommy']
```
