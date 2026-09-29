<?php

use Flobbos\PageComposer\Livewire\PageIndex;
use Flobbos\PageComposer\Livewire\RowComponent;
use Flobbos\PageComposer\Models\Category;
use Flobbos\PageComposer\Models\Page;
use Flobbos\PageComposer\Models\PageTranslation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\Livewire;

function makePage(string $name, array $attributes = []): Page
{
    $page = new Page();
    $page->name = $name;
    $page->category_id = Category::create([])->id;
    $page->forceFill($attributes)->save();

    return $page;
}

it('registers package components under the page-composer namespace', function () {
    expect(Blade::render('<livewire:page-composer::date-picker />'))->toContain('wire:');
});

it('no longer claims bare global component names', function () {
    Blade::render('<livewire:date-picker />');
})->throws(ComponentNotFoundException::class);

it('stores package tables under the configured prefix', function () {
    expect((new Page())->getTable())->toBe('pc_pages')
        ->and(Schema::hasTable('pc_pages'))->toBeTrue()
        ->and(Schema::hasTable('pc_rows'))->toBeTrue()
        ->and(Schema::hasTable('pc_page_tag'))->toBeTrue()
        ->and(Schema::hasTable('pages'))->toBeFalse()
        ->and(Schema::hasTable('rows'))->toBeFalse();
});

it('keeps relations working across prefixed tables', function () {
    $language = seedLanguages(['en'])->first();
    $page = makePage('Tagged');
    $tag = \Flobbos\PageComposer\Models\Tag::create([]);
    $page->tags()->attach($tag);
    $page->translations()->create(['language_id' => $language->id, 'slug' => 'tagged', 'content' => []]);

    expect($page->fresh()->tags)->toHaveCount(1)
        ->and($page->fresh()->translations)->toHaveCount(1);
});

it('keeps foreign keys pointing at the renamed tables', function () {
    $category = Category::create([]);
    $page = makePage('Survivor', ['category_id' => $category->id]);

    $category->delete();

    expect($page->fresh()->category_id)->toBeNull();
});

it('enforces unique slugs per language', function () {
    $language = seedLanguages(['en'])->first();
    makePage('One')->translations()->create(['language_id' => $language->id, 'slug' => 'same', 'content' => []]);

    makePage('Two')->translations()->create(['language_id' => $language->id, 'slug' => 'same', 'content' => []]);
})->throws(\Illuminate\Database\UniqueConstraintViolationException::class);

it('resolves existing duplicate slugs when the unique index is added', function () {
    $migration = require __DIR__ . '/../../src/database/migrations/2026_09_30_100100_unique_page_translation_slugs.php';
    $migration->down();

    $language = seedLanguages(['en'])->first();
    $pages = collect(['A', 'B', 'C', 'D'])->map(fn($name) => makePage($name));
    $table = (new PageTranslation())->getTable();

    foreach ([['same', 0], ['same', 1], ['same-2', 2], ['', 3]] as [$slug, $i]) {
        DB::table($table)->insert(['page_id' => $pages[$i]->id, 'language_id' => $language->id, 'slug' => $slug, 'content' => '{}']);
    }

    $migration->up();

    expect(DB::table($table)->orderBy('id')->pluck('slug')->all())->toBe(['same', 'same-3', 'same-2', null]);
});

it('searches by exact id for numeric input and ignores short searches', function () {
    $hello = makePage('Hello world');
    $other = makePage('Other page');

    $ids = fn($search) => Livewire::test(PageIndex::class)->set('search', $search)
        ->viewData('pages')->pluck('id')->all();

    expect($ids((string) $hello->id))->toBe([$hello->id])
        ->and($ids('Hel'))->toEqualCanonicalizing([$hello->id, $other->id])
        ->and($ids('Hello'))->toBe([$hello->id]);
});

it('removes a page\'s images from the public disk on hard delete', function () {
    Storage::fake('public');
    Storage::disk('public')->put('photos/main.jpg', 'x');
    Storage::disk('public')->put('photos/slider.jpg', 'x');
    $page = makePage('Doomed', ['photo' => 'photos/main.jpg', 'slider_image' => 'photos/slider.jpg']);
    $page->delete();

    Livewire::test(PageIndex::class)
        ->call('hardDeletePage', $page->id)
        ->set('confirmHardDelete', true);

    expect(Page::withTrashed()->count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('numbers new columns after the existing ones', function () {
    $row = ['columns' => [], 'attributes' => [], 'alignment' => 'center', 'expanded' => false, 'active' => true, 'sorting' => 1, 'available_space' => 12];

    $component = Livewire::test(RowComponent::class, ['row' => $row, 'rowKey' => 0])
        ->call('addColumn', 6)
        ->call('addColumn', 6);

    expect(collect($component->get('row.columns'))->pluck('sorting')->all())->toBe([1, 2]);
});
