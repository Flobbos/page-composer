<?php

use Flobbos\PageComposer\Models\Category;
use Flobbos\PageComposer\Models\Element;
use Flobbos\PageComposer\Models\Tag;
use Flobbos\PageComposer\Services\PageComposerCache;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    $this->cache = app(PageComposerCache::class);
});

it('hydrates cached elements as Element models', function () {
    seedElement('Text', 'text');
    seedElement('Photo', 'photo');

    $elements = $this->cache->elements();

    expect($elements)->toHaveCount(2);
    expect($elements->first())->toBeInstanceOf(Element::class);
    expect($elements->pluck('component')->all())->toEqualCanonicalizing(['text', 'photo']);
});

it('serves the second call from cache', function () {
    seedElement('Text', 'text');

    $first = $this->cache->elements();

    Element::create(['name' => 'Photo', 'component' => 'photo', 'icon' => '<svg></svg>']);

    $second = $this->cache->elements();

    expect($second)->toHaveCount($first->count());
});

it('refreshes when called with refresh=true', function () {
    seedElement('Text', 'text');
    $this->cache->elements();

    Element::create(['name' => 'Photo', 'component' => 'photo', 'icon' => '<svg></svg>']);

    expect($this->cache->elements(true))->toHaveCount(2);
});

it('hydrates languages as Language models', function () {
    seedLanguages(['en', 'de', 'fr']);

    $languages = $this->cache->languages();

    expect($languages)->toHaveCount(3);
    expect($languages->pluck('locale')->all())->toEqualCanonicalizing(['en', 'de', 'fr']);
});

it('hydrates categories with their translations relation restored', function () {
    seedLanguages(['en']);
    $category = Category::create([]);
    $category->translations()->create([
        'language_id' => 1,
        'name' => 'News',
        'slug' => 'news',
    ]);

    $categories = $this->cache->categories();

    expect($categories)->toHaveCount(1);
    expect($categories->first()->translations)->toHaveCount(1);
    expect($categories->first()->translations->first()->name)->toBe('News');
});

it('hydrates tags with their translations relation restored', function () {
    seedLanguages(['en']);
    $tag = Tag::create([]);
    $tag->translations()->create([
        'language_id' => 1,
        'name' => 'Featured',
        'slug' => 'featured',
    ]);

    $tags = $this->cache->tags();

    expect($tags)->toHaveCount(1);
    expect($tags->first()->translations->first()->name)->toBe('Featured');
});

it('forgets all four cache keys', function () {
    seedElement('Text', 'text');
    seedLanguages(['en']);

    $this->cache->elements();
    $this->cache->languages();
    $this->cache->categories();
    $this->cache->tags();

    expect(Cache::has('page-composer.elements'))->toBeTrue();
    expect(Cache::has('page-composer.languages'))->toBeTrue();
    expect(Cache::has('page-composer.categories'))->toBeTrue();
    expect(Cache::has('page-composer.tags'))->toBeTrue();

    $this->cache->forgetAll();

    expect(Cache::has('page-composer.elements'))->toBeFalse();
    expect(Cache::has('page-composer.languages'))->toBeFalse();
    expect(Cache::has('page-composer.categories'))->toBeFalse();
    expect(Cache::has('page-composer.tags'))->toBeFalse();
});
