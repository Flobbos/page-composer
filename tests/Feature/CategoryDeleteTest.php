<?php

use Flobbos\PageComposer\Livewire\CategoryComponent;
use Flobbos\PageComposer\Models\Category;
use Flobbos\PageComposer\Models\Page;
use Livewire\Livewire;

it('keeps pages when their category is deleted', function () {
    $category = Category::create([]);

    $page = new Page();
    $page->name = 'Survivor';
    $page->category_id = $category->id;
    $page->save();

    Livewire::test(CategoryComponent::class)->call('deleteCategory', $category->id);

    $page->refresh();

    expect(Category::count())->toBe(0)
        ->and($page->exists)->toBeTrue()
        ->and($page->category_id)->toBeNull();
});
