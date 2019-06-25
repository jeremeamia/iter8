<?php

namespace Jeremeamia\Iter8\Tests;

use Jeremeamia\Iter8\{Func, Gen, Iter, Pipe, Collection};

/**
 * @covers \Jeremeamia\Iter8\Iter
 * @covers \Jeremeamia\Iter8\Pipe
 * @covers \Jeremeamia\Iter8\Collection
 */
class MultiOperationTest extends TestCase
{
    const PEOPLE = [
        ['name' => 'Abby',  'age' => 19],
        ['name' => 'Benny', 'age' => 21],
        ['name' => 'Cally', 'age' => 22],
        ['name' => 'Danny', 'age' => 24],
        ['name' => 'Danny', 'age' => 23],
        ['name' => 'Eddy',  'age' => 18],
    ];

    public function testCanApplyMultipleOperationsUsingIterFlow()
    {
        $iter = Gen::from(self::PEOPLE);
        $iter = Iter::filter($iter, Func::compose([
            Func::index('age'),
            Func::operator('>=', 20),
        ]));
        $iter = Iter::map($iter, Func::index('name'));
        $iter = Iter::debounce($iter);

        $this->assertIterable(['Benny', 'Cally', 'Danny'], $iter);
    }

    public function testCanApplyMultipleOperationsUsingPipeFlow()
    {
        $iter = Iter::pipe(Gen::from(self::PEOPLE), [
            Pipe::filter(Func::compose([
                Func::index('age'),
                Func::operator('>=', 20),
            ])),
            Pipe::map(Func::index('name')),
            Pipe::debounce(),
        ]);

        $this->assertIterable(['Benny', 'Cally', 'Danny'], $iter);
    }

    public function testCanApplyMultipleOperationsUsingCollectionFlow()
    {
        $collection = Collection::from(self::PEOPLE)
            ->filter(Func::compose([
                Func::index('age'),
                Func::operator('>=', 20),
            ]))
            ->map(Func::index('name'))
            ->debounce();

        $this->assertIterable(['Benny', 'Cally', 'Danny'], $collection);
    }

    public function testCanApplyMultipleOperationsUsingPipeFlowWithSwitchMap()
    {
        $iter = Iter::pipe(Gen::from(self::PEOPLE), [
            Pipe::map(Func::index('name')),
            Pipe::first(),
            Pipe::switchMap(function (string $name) { return str_split($name); }),
            Pipe::debounce(),
            Pipe::map('strtoupper')
        ]);

        $this->assertIterable(['A', 'B', 'Y'], $iter);
    }
}
