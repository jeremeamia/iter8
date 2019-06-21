<?php

namespace Jeremeamia\Iter8\Tests;

use Jeremeamia\Iter8\{Func, Gen, Iter, Pipe, Collection};

class Person
{
    /** @var string */
    public $name;

    /** @var int */
    public $age;

    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->age = $data['age'];
    }
}

/**
 * @covers \Jeremeamia\Iter8\Iter
 * @covers \Jeremeamia\Iter8\Pipe
 * @covers \Jeremeamia\Iter8\Collection
 */
class MultiOperationTest extends TestCase
{
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

    public function testCanApplyMultipleOperationsUsingIterFlow()
    {
        $iter = Gen::from(self::PEOPLE);
        $iter = Iter::flatten($iter);
        $iter = Iter::map($iter, function (array $data) { return new Person($data); });
        $iter = Iter::filter($iter, function (Person $person) { return $person->age >= 20; });
        $iter = Iter::map($iter, Func::property('name'));
        $iter = Iter::drop($iter, 1);

        $this->assertIterable(['Cally', 'Danny', 'Tommy'], $iter);
    }

    public function testCanApplyMultipleOperationsUsingPipeFlow()
    {
        $iter = Iter::pipe(Gen::from(self::PEOPLE), [
            Pipe::flatten(),
            Pipe::map(function (array $data) { return new Person($data); }),
            Pipe::filter(function (Person $person) { return $person->age >= 20; }),
            Pipe::map(Func::property('name')),
            Pipe::drop(1),
        ]);

        $this->assertIterable(['Cally', 'Danny', 'Tommy'], $iter);
    }

    public function testCanApplyMultipleOperationsUsingCollectionFlow()
    {
        $collection = Collection::from(self::PEOPLE)
            ->flatten()
            ->map(function (array $data) { return new Person($data); })
            ->filter(function (Person $person) { return $person->age >= 20; })
            ->map(Func::property('name'))
            ->drop(1);

        $this->assertIterable(['Cally', 'Danny', 'Tommy'], $collection);
    }
}
