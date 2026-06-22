<?php

use Flobbos\PageComposer\Livewire\ColumnComponent;
use Flobbos\PageComposer\Livewire\PageComposer;
use Flobbos\PageComposer\Livewire\RowComponent;
use Flobbos\PageComposer\Models\Category;
use Flobbos\PageComposer\Models\Column;
use Flobbos\PageComposer\Models\ColumnItem;
use Flobbos\PageComposer\Models\Language;
use Flobbos\PageComposer\Models\Page;
use Flobbos\PageComposer\Models\PageTranslation;
use Flobbos\PageComposer\Models\Row;
use Livewire\Livewire;

function makeEditablePage(): Page
{
    seedLanguages(['en']);
    $element = seedElement();
    $english = Language::where('locale', 'en')->first();
    $category = Category::create([]);

    $page = new Page();
    $page->name = 'Editable Page';
    $page->photo = 'cover.jpg';
    $page->category_id = $category->id;
    $page->save();

    PageTranslation::create([
        'page_id' => $page->id,
        'language_id' => $english->id,
        'slug' => 'editable-page',
        'content' => ['title' => 'Editable Page'],
    ]);

    $row = Row::create([
        'page_id' => $page->id,
        'language_id' => $english->id,
        'alignment' => 'center',
        'attributes' => null,
        'expanded' => false,
        'active' => true,
        'sorting' => 1,
        'available_space' => 0,
    ]);

    $column = Column::create([
        'row_id' => $row->id,
        'column_size' => 12,
        'attributes' => null,
        'sorting' => 1,
        'active' => true,
    ]);

    ColumnItem::create([
        'column_id' => $column->id,
        'element_id' => $element->id,
        'sorting' => 1,
        'active' => true,
        'attributes' => null,
        'content' => ['text' => 'hello'],
    ]);

    return $page->fresh();
}

it('stages row removal in memory without deleting from the database', function () {
    $page = makeEditablePage();

    expect(Row::count())->toBe(1);

    Livewire::test(PageComposer::class, ['page' => $page->id])
        ->call('deleteRow', 0);

    expect(Row::count())->toBe(1);
});

it('deletes removed rows from the database when the page is saved', function () {
    $page = makeEditablePage();
    $rowId = Row::value('id');

    Livewire::test(PageComposer::class, ['page' => $page->id])
        ->call('deleteRow', 0)
        ->call('updateContent', false)
        ->assertHasNoErrors()
        ->assertSet('showErrorMessage', false);

    expect(Row::whereKey($rowId)->exists())->toBeFalse();
});

it('restores removed rows after a refresh before save', function () {
    $page = makeEditablePage();

    Livewire::test(PageComposer::class, ['page' => $page->id])
        ->call('deleteRow', 0);

    $component = Livewire::test(PageComposer::class, ['page' => $page->id]);

    expect($component->get('rows.en.rows'))->toHaveCount(1)
        ->and(Row::count())->toBe(1);
});

it('stages column removal until the page is saved', function () {
    $page = makeEditablePage();
    $row = $page->rows()->with('columns.column_items.element')->first();
    $columnId = $row->columns->first()->id;

    $rowState = $row->toArray();
    $rowState['columns'] = $row->columns->map(function ($column) {
        $data = $column->toArray();
        $data['column_items'] = $column->column_items->map(fn ($item) => [
            'element_id' => $item->element->id,
            'id' => $item->id,
            'name' => $item->element->name,
            'component' => $item->element->component,
            'icon' => $item->element->icon,
            'content' => $item->content,
            'attributes' => $item->attributes,
            'sorting' => $item->sorting,
            'active' => $item->active,
        ])->all();

        return $data;
    })->all();

    Livewire::test(RowComponent::class, [
        'row' => $rowState,
        'rowKey' => 0,
        'previewMode' => false,
    ])->call('deleteColumn', 0);

    expect(Column::whereKey($columnId)->exists())->toBeTrue();
});

it('stages element removal until the page is saved', function () {
    $page = makeEditablePage();
    $column = $page->rows()->first()->columns()->with('column_items.element')->first();
    $item = $column->column_items->first();

    $columnState = $column->toArray();
    $columnState['column_items'] = [
        0 => [
            'element_id' => $item->element->id,
            'id' => $item->id,
            'name' => $item->element->name,
            'component' => $item->element->component,
            'icon' => $item->element->icon,
            'content' => $item->content,
            'attributes' => $item->attributes,
            'sorting' => $item->sorting,
            'active' => $item->active,
        ],
    ];

    Livewire::test(ColumnComponent::class, [
        'column' => $columnState,
        'columnKey' => 0,
        'previewMode' => false,
    ])->call('deleteElement', 0);

    expect(ColumnItem::whereKey($item->id)->exists())->toBeTrue();
});

it('purges removed columns when the page is saved', function () {
    $page = makeEditablePage();
    $columnId = Column::value('id');

    $component = Livewire::test(PageComposer::class, ['page' => $page->id]);
    $rows = $component->get('rows');
    $rows['en']['rows'][0]['columns'] = [];
    $rows['en']['rows'][0]['available_space'] = 12;

    $component
        ->set('rows', $rows)
        ->call('updateContent', false)
        ->assertHasNoErrors()
        ->assertSet('showErrorMessage', false);

    expect(Column::whereKey($columnId)->exists())->toBeFalse()
        ->and(Row::count())->toBe(1);
});
