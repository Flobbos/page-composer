<?php

use Flobbos\PageComposer\Livewire\ColumnComponent;
use Flobbos\PageComposer\Models\Element;
use Livewire\Livewire;

function emptyColumn(int $size = 6): array
{
    return [
        'column_items' => [],
        'column_size' => $size,
        'attributes' => [],
        'sorting' => 1,
        'active' => true,
    ];
}

function elementItem(string $name, string $component, int $sorting): array
{
    return [
        'element_id' => 1,
        'name' => $name,
        'icon' => '<svg></svg>',
        'component' => $component,
        'attributes' => [],
        'sorting' => $sorting,
        'active' => true,
        'content' => [],
    ];
}

it('appends an element when elementAdded is dispatched', function () {
    $element = seedElement('Text', 'text');

    $component = Livewire::test(ColumnComponent::class, [
        'column' => emptyColumn(),
        'columnKey' => 0,
        'previewMode' => false,
        'target' => 'row-source-1',
    ]);

    $sourceId = $component->get('source');

    $component->dispatch('elementAdded.' . $sourceId, element: $element->id);

    $items = $component->get('column.column_items');

    expect($items)->toHaveCount(1)
        ->and($items[0]['component'])->toBe('text')
        ->and($items[0]['sorting'])->toBe(1);
});

it('sorts an element down in a single action and exposes fresh state to the view', function () {
    // Regression for the #[Computed] stale-cache bug we fixed — two clicks
    // used to be needed, arrows mismatched the rendered order. One call must
    // produce a consistent, already-re-sorted state.
    $column = emptyColumn();
    $column['column_items'] = [
        elementItem('A', 'text', 1),
        elementItem('B', 'text', 2),
        elementItem('C', 'text', 3),
    ];

    $component = Livewire::test(ColumnComponent::class, [
        'column' => $column,
        'columnKey' => 0,
        'previewMode' => false,
        'target' => 'row-source-1',
    ])->call('sortElementDown', 0);

    $items = $component->get('column.column_items');

    expect($items[0]['sorting'])->toBe(2)
        ->and($items[1]['sorting'])->toBe(1)
        ->and($items[2]['sorting'])->toBe(3);
});

it('sorts an element up in a single action', function () {
    $column = emptyColumn();
    $column['column_items'] = [
        elementItem('A', 'text', 1),
        elementItem('B', 'text', 2),
        elementItem('C', 'text', 3),
    ];

    $component = Livewire::test(ColumnComponent::class, [
        'column' => $column,
        'columnKey' => 0,
        'previewMode' => false,
        'target' => 'row-source-1',
    ])->call('sortElementUp', 2);

    $items = $component->get('column.column_items');

    expect($items[0]['sorting'])->toBe(1)
        ->and($items[1]['sorting'])->toBe(3)
        ->and($items[2]['sorting'])->toBe(2);
});

it('removes an element and re-numbers remaining items', function () {
    $column = emptyColumn();
    $column['column_items'] = [
        elementItem('A', 'text', 1),
        elementItem('B', 'text', 2),
        elementItem('C', 'text', 3),
    ];

    $component = Livewire::test(ColumnComponent::class, [
        'column' => $column,
        'columnKey' => 0,
        'previewMode' => false,
        'target' => 'row-source-1',
    ])->call('deleteElement', 1);

    $items = $component->get('column.column_items');

    expect($items)->toHaveCount(2)
        ->and($items[0]['sorting'])->toBe(1)
        ->and($items[0]['name'])->toBe('A')
        ->and($items[2]['sorting'])->toBe(2)
        ->and($items[2]['name'])->toBe('C');
});

it('computes arrow visibility via getElementPositionArray', function () {
    $column = emptyColumn();
    $column['column_items'] = [
        elementItem('A', 'text', 1),
        elementItem('B', 'text', 2),
        elementItem('C', 'text', 3),
    ];

    $component = Livewire::test(ColumnComponent::class, [
        'column' => $column,
        'columnKey' => 0,
        'previewMode' => false,
        'target' => 'row-source-1',
    ]);

    $top = $component->instance()->getElementPositionArray(0);
    $middle = $component->instance()->getElementPositionArray(1);
    $bottom = $component->instance()->getElementPositionArray(2);

    expect($top)->toEqual(['up' => false, 'down' => true, 'position' => 1])
        ->and($middle)->toEqual(['up' => true, 'down' => true, 'position' => 2])
        ->and($bottom)->toEqual(['up' => true, 'down' => false, 'position' => 3]);
});
