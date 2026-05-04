<?php

use Flobbos\PageComposer\Livewire\PageComposer;
use Flobbos\PageComposer\Models\Category;
use Flobbos\PageComposer\Models\Column;
use Flobbos\PageComposer\Models\ColumnItem;
use Flobbos\PageComposer\Models\Language;
use Flobbos\PageComposer\Models\Page;
use Flobbos\PageComposer\Models\PageTranslation;
use Flobbos\PageComposer\Models\Row;
use Livewire\Livewire;

function makePageWithContent(): Page
{
    seedLanguages(['en']);
    $element = seedElement();
    $english = Language::where('locale', 'en')->first();
    $category = Category::create([]);

    $page = new Page();
    $page->name = 'Test Page';
    $page->category_id = $category->id;
    $page->save();

    PageTranslation::create([
        'page_id' => $page->id,
        'language_id' => $english->id,
        'slug' => 'test-page',
        'content' => ['title' => 'Test Page'],
    ]);

    $row = Row::create([
        'page_id' => $page->id,
        'language_id' => $english->id,
        'alignment' => 'center',
        'attributes' => null,
        'expanded' => false,
        'active' => true,
        'sorting' => 1,
        'available_space' => 6,
    ]);

    $column = Column::create([
        'row_id' => $row->id,
        'column_size' => 6,
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

    return $page;
}

it('hydrates an existing page into component state on mount', function () {
    $page = makePageWithContent();

    $component = Livewire::test(PageComposer::class, ['page' => $page->id]);

    expect($component->get('pageId'))->toBe($page->id)
        ->and($component->get('page.name'))->toBe('Test Page');

    $rows = $component->get('rows.en.rows');
    expect($rows)->toHaveCount(1)
        ->and($rows[0]['sorting'])->toBe(1)
        ->and($rows[0]['columns'])->toHaveCount(1)
        ->and($rows[0]['columns'][0]['column_size'])->toBe(6)
        ->and($rows[0]['columns'][0]['column_items'])->toHaveCount(1)
        ->and($rows[0]['columns'][0]['column_items'][0]['content'])->toEqual(['text' => 'hello']);
});

it('loads the page translation into pageTranslations keyed by locale', function () {
    $page = makePageWithContent();

    $component = Livewire::test(PageComposer::class, ['page' => $page->id]);

    $translations = $component->get('pageTranslations');

    expect($translations)->toHaveKey('en')
        ->and($translations['en']['slug'])->toBe('test-page')
        ->and($translations['en']['content']['title'])->toBe('Test Page');
});

it('sets currentLanguage based on the loaded page translations', function () {
    $page = makePageWithContent();

    $component = Livewire::test(PageComposer::class, ['page' => $page->id]);

    expect($component->get('currentLanguage.locale'))->toBe('en');
});

it('mounts an existing page without the 1.0.0 incomplete-class cache bug', function () {
    // Regression for the __PHP_Incomplete_Class error that surfaced on edit
    // after the L12->L13 upgrade. Mounting PageComposer twice in a single
    // request exercises Cache::remember + rehydration for the lookup tables.
    $page = makePageWithContent();

    // Warm the caches, then mount again.
    Livewire::test(PageComposer::class, ['page' => $page->id]);
    $component = Livewire::test(PageComposer::class, ['page' => $page->id]);

    expect($component->get('pageId'))->toBe($page->id);
    $component->assertOk();
});
