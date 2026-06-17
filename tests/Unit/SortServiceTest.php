<?php

use Flobbos\PageComposer\Services\SortService;

uses()->group('unit');

beforeEach(function () {
    $this->service = new SortService();
});

it('returns the input untouched when no items are provided', function () {
    $result = $this->service->reorder([], fn() => '', 0, 0);

    expect($result)->toBe([]);
});

it('returns the input untouched when the source key cannot be resolved', function () {
    $items = [
        ['id' => 'a', 'sorting' => 1],
        ['id' => 'b', 'sorting' => 2],
    ];

    $result = $this->service->reorder(
        $items,
        fn(array $item) => $item['id'],
        'missing',
        1,
    );

    expect($result)->toBe($items);
});

it('moves the source item to the requested zero-based position', function () {
    $items = [
        ['id' => 'a', 'sorting' => 1],
        ['id' => 'b', 'sorting' => 2],
        ['id' => 'c', 'sorting' => 3],
    ];

    $result = $this->service->reorder(
        $items,
        fn(array $item) => $item['id'],
        'a',
        2,
    );

    expect(collect($result)->sortBy('sorting')->pluck('id')->all())
        ->toBe(['b', 'c', 'a']);
});

it('rewrites sortings to a 1-based contiguous sequence', function () {
    $items = [
        ['id' => 'a', 'sorting' => 10],
        ['id' => 'b', 'sorting' => 20],
        ['id' => 'c', 'sorting' => 30],
    ];

    $result = $this->service->reorder(
        $items,
        fn(array $item) => $item['id'],
        'c',
        0,
    );

    expect(collect($result)->pluck('sorting')->sort()->values()->all())
        ->toBe([1, 2, 3]);
});

it('preserves original array keys', function () {
    $items = [
        7 => ['id' => 'a', 'sorting' => 1],
        9 => ['id' => 'b', 'sorting' => 2],
        4 => ['id' => 'c', 'sorting' => 3],
    ];

    $result = $this->service->reorder(
        $items,
        fn(array $item) => $item['id'],
        'b',
        0,
    );

    expect(array_keys($result))->toBe([7, 9, 4]);
});

it('clamps a negative position to zero', function () {
    $items = [
        ['id' => 'a', 'sorting' => 1],
        ['id' => 'b', 'sorting' => 2],
        ['id' => 'c', 'sorting' => 3],
    ];

    $result = $this->service->reorder(
        $items,
        fn(array $item) => $item['id'],
        'c',
        -10,
    );

    expect(collect($result)->sortBy('sorting')->pluck('id')->first())->toBe('c');
});

it('places the source at the end when the position exceeds the count', function () {
    $items = [
        ['id' => 'a', 'sorting' => 1],
        ['id' => 'b', 'sorting' => 2],
        ['id' => 'c', 'sorting' => 3],
    ];

    $result = $this->service->reorder(
        $items,
        fn(array $item) => $item['id'],
        'a',
        99,
    );

    expect(collect($result)->sortBy('sorting')->pluck('id')->last())->toBe('a');
});

it('uses the array key when the resolver returns it', function () {
    $items = [
        ['id' => 'a', 'sorting' => 1],
        ['id' => 'b', 'sorting' => 2],
        ['id' => 'c', 'sorting' => 3],
    ];

    $result = $this->service->reorder(
        $items,
        fn(array $item, $key) => (string) $key,
        '2',
        0,
    );

    expect(collect($result)->sortBy('sorting')->pluck('id')->first())->toBe('c');
});
